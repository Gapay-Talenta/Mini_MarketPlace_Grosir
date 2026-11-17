<?php

namespace App\Modules\Admin\Services;

use App\Models\Store;
use App\Models\StoreAdmin;
use App\Models\User;
use DomainException;

/**
 * Service untuk menugaskan atau mencabut membership peran admin/operator toko grosir.
 * Invariant bisnis: Toko tidak boleh kehilangan owner terakhirnya.
 */
final class AssignStoreRole
{
    public function __construct(private AuditLogger $audit) {}

    /**
     * Menugaskan user ke toko dengan role tertentu (owner|admin|operator).
     *
     * @throws DomainException jika mencoba menurunkan peran owner terakhir toko.
     */
    public function assign(Store $store, User $user, string $role): StoreAdmin
    {
        $existing = StoreAdmin::query()
            ->where('store_id', $store->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing !== null && $existing->role === 'owner' && $role !== 'owner') {
            $this->ensureNotLastOwner($store);
        }

        $membership = StoreAdmin::query()->updateOrCreate(
            ['store_id' => $store->id, 'user_id' => $user->id],
            ['role' => $role],
        );

        $this->audit->record(
            action: 'store_role_assigned',
            subjectType: StoreAdmin::class,
            subjectId: $membership->id,
            before: $existing !== null ? ['user_id' => $user->id, 'role' => $existing->role] : null,
            after: ['user_id' => $user->id, 'role' => $role],
            note: "Peran '{$role}' ditugaskan kepada user '{$user->name}'.",
        );

        return $membership;
    }

    /**
     * Mencabut peran anggota dari toko grosir.
     *
     * @throws DomainException jika mencoba mencabut owner terakhir toko.
     */
    public function remove(Store $store, User $user, string $role): void
    {
        if ($role === 'owner') {
            $this->ensureNotLastOwner($store);
        }

        $deleted = StoreAdmin::query()
            ->where('store_id', $store->id)
            ->where('user_id', $user->id)
            ->where('role', $role)
            ->delete();

        if ($deleted) {
            $this->audit->record(
                action: 'store_role_removed',
                subjectType: StoreAdmin::class,
                subjectId: $user->id,
                before: ['user_id' => $user->id, 'role' => $role],
                after: null,
                note: "Peran '{$role}' dicabut dari user '{$user->name}'.",
            );
        }
    }

    /**
     * Memastikan toko masih memiliki lebih dari satu owner sebelum penghapusan atau downgrade.
     *
     * @throws DomainException
     */
    private function ensureNotLastOwner(Store $store): void
    {
        $ownersCount = StoreAdmin::query()
            ->where('store_id', $store->id)
            ->where('role', 'owner')
            ->count();

        if ($ownersCount <= 1) {
            throw new DomainException('Tidak boleh menghapus atau menurunkan peran owner terakhir toko.');
        }
    }
}
