<?php

use App\Modules\Catalog\Http\Controllers\TenantProductController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

/**
 * Rute portal tenant milik modul Catalog.
 * Parameter {product} otomatis ter-scope di bawah {tenant:slug} via scopeBindings().
 */
PortalRoutes::tenant(function (): void {
    Route::get('/products', [TenantProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [TenantProductController::class, 'show'])->name('products.show');
    Route::patch('/products/{product}', [TenantProductController::class, 'update'])->name('products.update');
});
