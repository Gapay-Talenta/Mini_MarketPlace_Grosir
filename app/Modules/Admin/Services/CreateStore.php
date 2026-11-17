<?php

namespace App\Modules\Admin\Services;

use App\Models\CommissionRate;
use App\Models\Store;
use App\Models\StoreShippingRate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Service untuk mendaftarkan toko grosir baru secara atomik (UC-21):
 * Menyimpan toko, komisi aktif awal, tarif ongkir, akun owner (beserta tautan atur kata sandi),
 * rekening bank awal terenkripsi, dan jejak audit.
 */
final class CreateStore
{
    public function __construct(
        private AuditLogger $audit,
        private ManageBankAccount $bankAccounts,
        private AssignStoreRole $roles,
    ) {}

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
            $commission = (new CommissionRate)->forceFill([
                'store_id' => $store->id,
                'rate_percent' => $data['commission_rate'],
                'valid_from' => now(),
                'valid_to' => null,
            ]);
            $commission->save();

            // 3. Buat tarif ongkir flat per kg per toko
            (new StoreShippingRate)->forceFill([
                'store_id' => $store->id,
                'rate_per_kg' => $data['rate_per_kg'] ?? 8000,
            ])->save();

            // 4. Catat audit pembuatan toko & komisi awal
            $this->audit->record(
                action: 'store_created',
                subjectType: Store::class,
                subjectId: $store->id,
                before: null,
                after: $store->only(['name', 'slug', 'status']),
                note: 'Toko grosir baru berhasil didaftarkan oleh admin hub.',
            );

            $this->audit->record(
                action: 'commission_created',
                subjectType: CommissionRate::class,
                subjectId: $commission->id,
                before: null,
                after: $commission->only(['rate_percent', 'valid_from']),
                note: "Komisi awal {$data['commission_rate']}% ditetapkan saat onboarding toko.",
            );

            // 5. Buat akun penanggung jawab (owner awal) & kirim tautan atur kata sandi setelah commit
            if (isset($data['pic_email'])) {
                $owner = (new User)->forceFill([
                    'name' => $data['pic_name'],
                    'email' => $data['pic_email'],
                    'password' => Str::password(32),
                    'role' => 'tenant',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
                $owner->save();

                $this->roles->assign($store, $owner, 'owner');

                DB::afterCommit(fn () => Password::broker()->sendResetLink(['email' => $owner->email]));
            }

            // 6. Simpan rekening bank awal secara terenkripsi
            if (isset($data['account_number'])) {
                $this->bankAccounts->store($store, $data);
            }

            return $store;
        });
    }
}
