<?php

namespace App\Modules\Admin\Services;

use App\Models\CommissionRate;
use App\Models\Store;
use App\Models\StoreShippingRate;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk mendaftarkan toko grosir baru secara atomik:
 * Menyimpan toko, komisi aktif awal, tarif ongkir, dan jejak audit.
 */
final class CreateStore
{
    public function __construct(private AuditLogger $audit) {}

    /**
     * Eksekusi transaksi pembuatan toko.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Store
    {
        return DB::transaction(function () use ($data): Store {
            // 1. Simpan data master toko
            $store = (new Store)->forceFill([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'status' => 'aktif',
            ]);
            $store->save();

            // 2. Buat skema komisi aktif awal (valid_to null = aktif)
            (new CommissionRate)->forceFill([
                'store_id' => $store->id,
                'rate_percent' => $data['commission_rate'],
                'valid_from' => now(),
                'valid_to' => null,
            ])->save();

            // 3. Buat tarif ongkir flat per kg per toko
            (new StoreShippingRate)->forceFill([
                'store_id' => $store->id,
                'rate_per_kg' => $data['rate_per_kg'] ?? 8000,
            ])->save();

            // 4. Catat ke audit log
            $this->audit->record(
                action: 'store_created',
                subjectType: Store::class,
                subjectId: $store->id,
                before: null,
                after: $store->only(['name', 'slug', 'status']),
                note: 'Toko grosir baru berhasil didaftarkan oleh admin hub.',
            );

            return $store;
        });
    }
}
