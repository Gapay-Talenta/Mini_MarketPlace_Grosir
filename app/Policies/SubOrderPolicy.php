<?php

namespace App\Policies;

use App\Models\SubOrder;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

/**
 * Kebijakan akses sub-order (pesanan yang dialokasikan ke toko).
 */
class SubOrderPolicy
{
    private function ownedByActiveStore(SubOrder $subOrder): bool
    {
        return $subOrder->store_id === app(TenantContext::class)->idOrNull();
    }

    public function view(User $user, SubOrder $subOrder): bool
    {
        return $this->ownedByActiveStore($subOrder);
    }

    public function update(User $user, SubOrder $subOrder): bool
    {
        return $this->ownedByActiveStore($subOrder);
    }
}
