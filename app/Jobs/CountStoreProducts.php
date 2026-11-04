<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Store;
use App\Support\Tenancy\TenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Contoh job tenant-scoped: membawa storeId (tipe primitif, bukan model context penuh),
 * membentuk context di handle(), lalu MEMBERSIHKANNYA pada blok finally agar
 * worker berumur panjang (Redis Queue) tidak membawa residu toko sebelumnya.
 */
class CountStoreProducts implements ShouldQueue
{
    use Queueable;

    public ?int $result = null;

    public function __construct(public int $storeId) {}

    public function handle(TenantContext $context): void
    {
        $store = Store::query()->findOrFail($this->storeId);
        $context->set($store);

        try {
            // Query akan mematuhi global scope toko aktif (setelah trait dipasang di Tahap 4).
            $this->result = Product::query()->count();
        } finally {
            $context->clear();
        }
    }
}
