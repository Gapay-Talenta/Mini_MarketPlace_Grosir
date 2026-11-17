<?php

use App\Modules\Admin\Http\Controllers\AdminBankAccountController;
use App\Modules\Admin\Http\Controllers\AdminCommissionController;
use App\Modules\Admin\Http\Controllers\AdminStoreController;
use App\Modules\Admin\Http\Controllers\AdminStoreRoleController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

/**
 * Route portal admin milik modul Admin (prefix: /admin, name: admin., middleware: role:admin).
 */
PortalRoutes::admin(function (): void {
    Route::get('/stores', [AdminStoreController::class, 'index'])->name('stores.index');
    Route::get('/stores/create', [AdminStoreController::class, 'create'])->name('stores.create');
    Route::post('/stores', [AdminStoreController::class, 'store'])->name('stores.store');
    Route::get('/stores/{store}', [AdminStoreController::class, 'edit'])->name('stores.edit');

    // Penjadwalan komisi (Tahap 4)
    Route::post('/stores/{store}/commission', [AdminCommissionController::class, 'store'])->name('stores.commission.store');

    // Rekening bank toko & verifikasi (Tahap 5)
    Route::post('/stores/{store}/bank', [AdminBankAccountController::class, 'store'])->name('stores.bank.store');
    Route::post('/stores/{store}/bank/{account}/verify', [AdminBankAccountController::class, 'verify'])->name('stores.bank.verify');

    // Penugasan & pencabutan peran toko (Tahap 3)
    Route::post('/stores/{store}/roles', [AdminStoreRoleController::class, 'store'])->name('stores.roles.store');
    Route::delete('/stores/{store}/roles/{user}', [AdminStoreRoleController::class, 'destroy'])->name('stores.roles.destroy');
});
