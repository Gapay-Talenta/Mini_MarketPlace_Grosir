<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreAdmin;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * UC-19 langkah 5: setelah masuk, arahkan pengguna ke dasbor sesuai perannya.
 * Admin Hub ke portal admin; operator/admin toko ke dasbor toko aktif pertama miliknya.
 * Pembeli (customer) atau pengguna tanpa peran internal melihat dasbor umum.
 */
class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user?->isTenantOperator()) {
            $storeSlug = Store::query()
                ->whereIn('id', StoreAdmin::query()->where('user_id', $user->id)->select('store_id'))
                ->where('status', 'aktif')
                ->orderBy('id')
                ->value('slug');

            if ($storeSlug !== null) {
                return redirect()->route('tenant.dashboard', ['tenant' => $storeSlug]);
            }
        }

        return view('dashboard');
    }
}
