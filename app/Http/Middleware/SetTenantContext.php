<?php

namespace App\Http\Middleware;

use App\Models\Store;
use App\Models\StoreAdmin;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolver toko (tenant) aktif untuk portal internal toko grosir.
 * Dijalankan SETELAH auth dan verified.
 *
 * Aturan Otorisasi:
 * 1. Toko tidak ditemukan -> 404 Not Found.
 * 2. Toko tidak aktif (status != 'aktif') -> 403 Forbidden.
 * 3. Pengguna bukan anggota terdaftar di store_admins -> 403 Forbidden.
 * 4. Mengisi TenantContext sebelum request masuk dan MEMBERSIHKANNYA pada finally.
 */
final class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        // Mendukung parameter {tenant} atau {store}
        $store = $request->route('tenant') ?? $request->route('store');

        // Jika route model binding belum menghasilkan instance Store, cari berdasarkan slug
        if (! $store instanceof Store) {
            $store = Store::query()->where('slug', (string) $store)->first();
        }

        if (! $store instanceof Store) {
            abort(404);
        }

        if (! $store->isActive()) {
            abort(403, 'Toko sedang dinonaktifkan.');
        }

        $user = $request->user();
        if ($user === null) {
            abort(403);
        }

        // Verifikasi relasional: Pengguna wajib terdaftar di tabel store_admins toko ini
        $isMember = StoreAdmin::query()
            ->where('user_id', $user->id)
            ->where('store_id', $store->id)
            ->exists();

        if (! $isMember) {
            abort(403, 'Anda bukan pengelola toko ini.');
        }

        // Isi context toko aktif untuk siklus request ini
        $context = app(TenantContext::class);
        $context->set($store);

        try {
            return $next($request);
        } finally {
            // Jaminan anti-kebocoran data antar-request
            $context->clear();
        }
    }
}
