<?php

namespace App\Modules\Catalog\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service query lintas-toko yang SAH untuk katalog publik pembeli.
 *
 * Aturan Keamanan:
 * 1. Global scope 'tenant' di-bypass secara eksplisit (bukan diam-diam).
 * 2. Wajib menerapkan Filter Pengganti:
 *    - Hanya produk yang aktif (is_active = true).
 *    - Hanya produk dari toko yang berstatus 'aktif'.
 * 3. Tidak membocorkan data toko yang di-suspend atau produk non-aktif ke publik.
 */
final class PublicCatalogQuery
{
    /**
     * Mengambil seluruh produk aktif di marketplace publik (agregasi lintas toko).
     *
     * @return Collection<int, Product>
     */
    public function forMarketplace(?string $category = null): Collection
    {
        return Product::query()
            ->withoutGlobalScope('tenant') // Bypass eksplisit dan terisolasi
            ->where('status', 'aktif')
            ->whereHas('store', function (Builder $query): void {
                $query->where('status', 'aktif');
            })
            ->when($category !== null, function (Builder $query) use ($category): void {
                $query->where('category', $category);
            })
            ->with(['store:id,name,slug', 'prices'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Mengambil katalog produk publik milik satu toko tertentu (halaman etalase toko).
     *
     * @return Collection<int, Product>
     */
    public function forStore(Store $store): Collection
    {
        if (! $store->isActive()) {
            return new Collection;
        }

        return Product::query()
            ->withoutGlobalScope('tenant')
            ->where('store_id', $store->id)
            ->where('status', 'aktif')
            ->with('prices')
            ->orderBy('name')
            ->get();
    }
}
