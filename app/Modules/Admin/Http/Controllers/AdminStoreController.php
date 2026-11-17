<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Modules\Admin\Http\Requests\StoreStoreRequest;
use App\Modules\Admin\Services\CreateStore;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminStoreController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private CreateStore $createStore) {}

    /**
     * Menampilkan daftar seluruh toko grosir dan komisi aktifnya.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Store::class);

        $stores = Store::query()
            ->with([
                'commissionRates' => fn ($q) => $q->whereNull('valid_to'),
                'shippingRate',
            ])
            ->orderBy('name')
            ->get();

        return view('admin::stores.index', compact('stores'));
    }

    /**
     * Menampilkan form pendaftaran toko grosir baru.
     */
    public function create(): View
    {
        $this->authorize('create', Store::class);

        return view('admin::stores.create');
    }

    /**
     * Memproses penyimpanan toko baru.
     */
    public function store(StoreStoreRequest $request): RedirectResponse
    {
        $store = $this->createStore->handle($request->validated());

        return redirect()
            ->route('admin.stores.edit', $store)
            ->with('status', "Toko Grosir '{$store->name}' berhasil didaftarkan dengan komisi aktif dan tarif ongkir.");
    }

    /**
     * Menampilkan detail kelola toko grosir.
     */
    public function edit(Store $store): View
    {
        $this->authorize('view', $store);

        $store->load(['shippingRate']);
        $commissions = $store->commissionRates()->orderByDesc('valid_from')->get();
        $activeCommission = $commissions->firstWhere('valid_to', null);
        $members = $store->admins()->with('user')->get();
        $bankAccounts = $store->bankAccounts()->latest()->get();

        return view('admin::stores.edit', compact(
            'store',
            'commissions',
            'activeCommission',
            'members',
            'bankAccounts',
        ));
    }
}
