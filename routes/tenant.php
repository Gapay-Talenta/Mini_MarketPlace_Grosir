<?php

use App\Models\Store;
use Illuminate\Support\Facades\Route;

/**
 * Konteks OPERATOR TOKO GROSIR (internal).
 * Prefix: tenant/{tenant:slug}, Route name: tenant.*
 */
Route::get('/dashboard', function (Store $tenant) {
    return view('tenant.dashboard', ['tenant' => $tenant]);
})->name('dashboard');
