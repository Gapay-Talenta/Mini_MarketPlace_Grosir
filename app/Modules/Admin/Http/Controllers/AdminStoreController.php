<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CommissionRate;
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
     * Menampilkan daftar seluruh toko grosir beserta penanggung jawab, rekening, komisi aktif, dan status.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Store::class);

        $stores = Store::query()
            ->with([
                'admins' => fn ($q) => $q->where('role', 'owner')->with('user'),
                'bankAccounts' => fn ($q) => $q->latest(),
                'commissionRates' => fn ($q) => $q->withoutGlobalScope('tenant')->effectiveAt(now()),
                'shippingRate',
            ])
            ->orderBy('name')
            ->get();

        return view('admin::stores.index', compact('stores'));
    }

    /**
     * Menampilkan form onboarding pendaftaran toko grosir baru.
     */
    public function create(): View
    {
        $this->authorize('create', Store::class);

        return view('admin::stores.create');
    }

    /**
     * Memproses onboarding toko baru beserta akun owner dan rekening awal.
     */
    public function store(StoreStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $store = $this->createStore->handle($data);

        return redirect()
            ->route('admin.stores.edit', $store)
            ->with('status', "Toko Grosir '{$store->name}' berhasil didaftarkan. Tautan atur kata sandi dikirim ke {$data['pic_email']}.");
    }

    /**
     * Menampilkan detail kelola toko grosir dan riwayat versi komisi beserta aktor pengubahnya.
     */
    public function edit(Store $store): View
    {
        $this->authorize('view', $store);

        $store->load(['shippingRate']);
        $commissions = $store->commissionRates()->withoutGlobalScope('tenant')->orderByDesc('valid_from')->get();
        $activeCommission = CommissionRate::query()
            ->withoutGlobalScope('tenant')
            ->where('store_id', $store->id)
            ->effectiveAt(now())
            ->first();

        $commissionActors = AuditLog::query()
            ->where('subject_type', CommissionRate::class)
            ->whereIn('subject_id', $commissions->pluck('id'))
            ->with('user')
            ->get()
            ->mapWithKeys(fn (AuditLog $log): array => [$log->subject_id => $log->user?->name]);

        $members = $store->admins()->with('user')->get();
        $bankAccounts = $store->bankAccounts()->latest()->get();

        return view('admin::stores.edit', compact(
            'store',
            'commissions',
            'activeCommission',
            'commissionActors',
            'members',
            'bankAccounts',
        ));
    }
}
