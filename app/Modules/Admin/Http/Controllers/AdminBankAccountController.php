<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StoreBankAccount;
use App\Modules\Admin\Http\Requests\StoreBankAccountRequest;
use App\Modules\Admin\Services\ManageBankAccount;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminBankAccountController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private ManageBankAccount $service) {}

    /**
     * Menambahkan rekening bank baru untuk toko (status awal: pending).
     */
    public function store(StoreBankAccountRequest $request, Store $store): RedirectResponse
    {
        $this->service->store($store, $request->validated());

        return redirect()
            ->route('admin.stores.edit', $store)
            ->with('status', 'Rekening bank berhasil ditambahkan dan menunggu verifikasi.');
    }

    /**
     * Memverifikasi atau menolak rekening bank toko (Hanya Admin Hub).
     */
    public function verify(Request $request, Store $store, StoreBankAccount $account): RedirectResponse
    {
        $this->authorize('verifyBank', $store);

        // Cegah akses silang toko melalui manipulasi ID rekening di URL
        abort_unless($account->store_id === $store->id, 404);

        $data = $request->validate([
            'approve' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $this->service->verify($account, (bool) $data['approve'], $data['note'] ?? null);

        $label = $data['approve'] ? 'diverifikasi (verified)' : 'ditolak (rejected)';

        return redirect()
            ->route('admin.stores.edit', $store)
            ->with('status', "Status rekening ••••{$account->account_last4} berhasil {$label}.");
    }
}
