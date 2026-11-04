<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

/**
 * Kebijakan akses resource produk toko grosir.
 * Memastikan produk yang diakses terdaftar di bawah toko yang sedang aktif.
 */
class ProductPolicy
{
    private function ownedByActiveStore(Product $product): bool
    {
        return $product->store_id === app(TenantContext::class)->idOrNull();
    }

    public function view(User $user, Product $product): bool
    {
        return $this->ownedByActiveStore($product);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->ownedByActiveStore($product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->ownedByActiveStore($product);
    }
}
