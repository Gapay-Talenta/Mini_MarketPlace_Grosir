<?php

namespace App\Modules\Admin\Services;

use App\Models\Store;
use App\Models\StoreBankAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Service untuk manajemen rekening bank toko:
 * Menyimpan nomor terenkripsi + last4, serta memproses workflow verifikasi pending -> verified/rejected.
 */
final class ManageBankAccount
{
    public function __construct(private AuditLogger $audit) {}

    /**
     * Tambah rekening bank baru untuk toko dengan status awal 'pending'.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(Store $store, array $data): StoreBankAccount
    {
        return DB::transaction(function () use ($store, $data): StoreBankAccount {
            $accountNumber = (string) $data['account_number'];

            $account = (new StoreBankAccount)->forceFill([
                'store_id' => $store->id,
                'bank_name' => $data['bank_name'],
                'account_holder_name' => $data['account_holder_name'],
                'account_last4' => Str::substr($accountNumber, -4),
                'account_number_encrypted' => $accountNumber, // terenkripsi via model cast
                'status' => 'pending',
            ]);
            $account->save();

            // Defense-in-depth: hanya kirim field non-sensitif ke AuditLogger
            $this->audit->record(
                action: 'bank_account_added',
                subjectType: StoreBankAccount::class,
                subjectId: $account->id,
                before: null,
                after: [
                    'store_id' => $store->id,
                    'bank_name' => $account->bank_name,
                    'account_last4' => $account->account_last4,
                    'status' => $account->status,
                ],
                note: "Rekening {$account->bank_name} ••••{$account->account_last4} ditambahkan untuk toko {$store->name}.",
            );

            return $account;
        });
    }

    /**
     * Verifikasi atau tolak rekening bank toko.
     */
    public function verify(StoreBankAccount $account, bool $approve, ?string $note = null): StoreBankAccount
    {
        return DB::transaction(function () use ($account, $approve, $note): StoreBankAccount {
            $locked = StoreBankAccount::query()
                ->withoutGlobalScope('tenant')
                ->lockForUpdate()
                ->findOrFail($account->id);

            $beforeStatus = $locked->status;
            $newStatus = $approve ? 'verified' : 'rejected';

            $locked->forceFill(['status' => $newStatus])->save();

            $this->audit->record(
                action: $approve ? 'verify_bank_account' : 'reject_bank_account',
                subjectType: StoreBankAccount::class,
                subjectId: $locked->id,
                before: ['status' => $beforeStatus],
                after: ['status' => $newStatus],
                note: $note ?? "Rekening {$locked->bank_name} ••••{$locked->account_last4} diubah menjadi {$newStatus}.",
            );

            return $locked;
        });
    }
}
