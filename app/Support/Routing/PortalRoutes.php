<?php

namespace App\Support\Routing;

use Closure;
use Illuminate\Support\Facades\Route;

/**
 * Definisi TUNGGAL grup route per portal (prefix + name + middleware).
 * Dipakai oleh routes/{customer,tenant,admin}.php dan file route modul
 * (app/Modules/{Modul}/routes/{portal}.php).
 */
final class PortalRoutes
{
    /**
     * Portal pelanggan (publik, anonim).
     */
    public static function customer(Closure|string $routes): void
    {
        Route::middleware('web')
            ->prefix('toko/{store}')
            ->name('customer.')
            ->group($routes);
    }

    /**
     * Portal operator toko grosir / tenant (internal).
     */
    public static function tenant(Closure|string $routes): void
    {
        Route::middleware(['web', 'auth', 'verified', 'role:tenant'])
            ->prefix('tenant/{tenant}')
            ->name('tenant.')
            ->group($routes);
    }

    /**
     * Portal pengelola platform admin (internal).
     */
    public static function admin(Closure|string $routes): void
    {
        Route::middleware(['web', 'auth', 'verified', 'role:admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group($routes);
    }
}
