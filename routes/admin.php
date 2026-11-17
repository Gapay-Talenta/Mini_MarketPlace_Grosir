<?php

use App\Models\Store;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    $stores = Store::query()
        ->with([
            'admins' => fn ($q) => $q->where('role', 'owner')->with('user'),
            'commissionRates' => fn ($q) => $q->withoutGlobalScope('tenant')->effectiveAt(now()),
            'shippingRate',
        ])
        ->orderBy('name')
        ->get();

    return view('admin.dashboard', [
        'stores' => $stores,
        'totalStores' => $stores->count(),
        'activeStores' => $stores->where('status', 'aktif')->count(),
        'inactiveStores' => $stores->where('status', '!=', 'aktif')->count(),
    ]);
})->name('dashboard');
