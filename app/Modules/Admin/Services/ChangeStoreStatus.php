<?php

namespace App\Modules\Admin\Services;

use App\Models\Store;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * UC-21 alur 3a: menonaktifkan/mengaktifkan toko grosir.
 * Toko nonaktif otomatis disembunyikan dari katalog publik (PublicCatalogQuery),
 * sementara data historis (produk, sub-order, ledger) tetap utuh tanpa penghapusan.
 */
final class ChangeStoreStatus
{
    public const ALLOWED = ['aktif', 'nonaktif'];

    public function __construct(private AuditLogger $audit) {}

    public function handle(Store $store, string $status): Store
    {
        if (! in_array($status, self::ALLOWED, true)) {
            throw new DomainException('Status toko tidak dikenal.');
        }

        return DB::transaction(function () use ($store, $status): Store {
            $locked = Store::query()->lockForUpdate()->findOrFail($store->id);
            $before = $locked->status;

            if ($before === $status) {
                return $locked;
            }

            $locked->forceFill(['status' => $status])->save();

            $this->audit->record(
                action: $status === 'aktif' ? 'store_activated' : 'store_deactivated',
                subjectType: Store::class,
                subjectId: $locked->id,
                before: ['status' => $before],
                after: ['status' => $status],
                note: "Status toko {$locked->name} diubah dari {$before} menjadi {$status}.",
            );

            return $locked;
        });
    }
}
