<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Modules\Admin\Services\ChangeStoreStatus;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * UC-21 alur 3a: aktif/nonaktifkan toko grosir oleh Admin Hub.
 */
final class AdminStoreStatusController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private ChangeStoreStatus $service) {}

    public function __invoke(Request $request, Store $store): RedirectResponse
    {
        $this->authorize('changeStatus', $store);

        $data = $request->validate([
            'status' => ['required', Rule::in(ChangeStoreStatus::ALLOWED)],
        ]);

        $this->service->handle($store, $data['status']);

        return redirect()
            ->route('admin.stores.index')
            ->with('status', $data['status'] === 'aktif'
                ? "Toko {$store->name} berhasil diaktifkan kembali."
                : "Toko {$store->name} dinonaktifkan dan disembunyikan dari katalog publik.");
    }
}
