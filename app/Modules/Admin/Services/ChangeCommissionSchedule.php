<?php

namespace App\Modules\Admin\Services;

use App\Models\CommissionRate;
use App\Models\Store;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk menjadwalkan versi tarif komisi baru (effective-dated).
 * Menutup versi lama tepat di titik waktu versi baru dimulai (interval setengah-terbuka).
 */
final class ChangeCommissionSchedule
{
    public function __construct(private AuditLogger $audit) {}

    /**
     * Eksekusi pergantian jadwal komisi dalam transaksi atomik.
     */
    public function handle(Store $store, int|float|string $rate, CarbonInterface $effectiveAt): CommissionRate
    {
        return DB::transaction(function () use ($store, $rate, $effectiveAt): CommissionRate {
            // 1. Kunci versi komisi yang sedang aktif (valid_to null)
            $active = CommissionRate::query()
                ->withoutGlobalScope('tenant')
                ->where('store_id', $store->id)
                ->whereNull('valid_to')
                ->lockForUpdate()
                ->first();

            // 2. Cegah waktu efektif yang mendahului atau sama dengan versi aktif (anti-overlap)
            if ($active !== null && $active->valid_from >= $effectiveAt) {
                throw new DomainException('Waktu berlaku tarif baru harus setelah tanggal mulai versi aktif.');
            }

            $before = $active?->only(['id', 'rate_percent', 'valid_from', 'valid_to']);

            // 3. Tutup versi aktif lama TEPAT pada waktu efektif versi baru
            $active?->forceFill(['valid_to' => $effectiveAt])->save();

            // 4. Terbitkan versi komisi baru
            $new = (new CommissionRate)->forceFill([
                'store_id' => $store->id,
                'rate_percent' => $rate,
                'valid_from' => $effectiveAt,
                'valid_to' => null,
            ]);
            $new->save();

            // 5. Catat jejak audit tindakan administratif
            $this->audit->record(
                action: 'commission_scheduled',
                subjectType: CommissionRate::class,
                subjectId: $new->id,
                before: $before,
                after: $new->only(['rate_percent', 'valid_from']),
                note: "Tarif komisi baru {$rate}% dijadwalkan berlaku mulai {$effectiveAt->toIso8601String()}.",
            );

            return $new;
        });
    }
}
