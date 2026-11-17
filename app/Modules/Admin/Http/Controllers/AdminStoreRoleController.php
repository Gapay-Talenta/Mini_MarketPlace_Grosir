<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Modules\Admin\Services\AssignStoreRole;
use DomainException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminStoreRoleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private AssignStoreRole $service) {}

    /**
     * Menugaskan peran admin/operator toko berdasarkan email user.
     */
    public function store(Request $request, Store $store): RedirectResponse
    {
        $this->authorize('assignRole', $store);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'in:owner,admin,operator'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if ($user === null) {
            return redirect()
                ->route('admin.stores.edit', $store)
                ->withErrors(['email' => "User dengan surel '{$data['email']}' tidak ditemukan di sistem."]);
        }

        try {
            $this->service->assign($store, $user, $data['role']);
        } catch (DomainException $e) {
            return redirect()
                ->route('admin.stores.edit', $store)
                ->withErrors(['role' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.stores.edit', $store)
            ->with('status', "Peran '{$data['role']}' berhasil ditugaskan kepada {$user->name}.");
    }

    /**
     * Mencabut peran anggota dari toko.
     */
    public function destroy(Request $request, Store $store, User $user): RedirectResponse
    {
        $this->authorize('assignRole', $store);

        $data = $request->validate([
            'role' => ['required', 'in:owner,admin,operator'],
        ]);

        try {
            $this->service->remove($store, $user, $data['role']);
        } catch (DomainException $e) {
            return redirect()
                ->route('admin.stores.edit', $store)
                ->withErrors(['role' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.stores.edit', $store)
            ->with('status', "Peran '{$data['role']}' untuk {$user->name} berhasil dicabut.");
    }
}
