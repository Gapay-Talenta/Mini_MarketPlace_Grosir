<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Withdrawal;
use App\Support\Tenancy\TenantContext;

/**
 * Kebijakan akses penarikan dana (withdrawal) toko.
 */
class WithdrawalPolicy
{
    private function ownedByActiveStore(Withdrawal $withdrawal): bool
    {
        return $withdrawal->store_id === app(TenantContext::class)->idOrNull();
    }

    public function view(User $user, Withdrawal $withdrawal): bool
    {
        return $this->ownedByActiveStore($withdrawal);
    }

    public function update(User $user, Withdrawal $withdrawal): bool
    {
        return $this->ownedByActiveStore($withdrawal);
    }
}
