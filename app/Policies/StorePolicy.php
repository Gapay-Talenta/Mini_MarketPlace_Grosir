<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\StoreAdmin;
use App\Models\User;

/**
 * Policy untuk tata kelola administratif Toko Grosir (Store).
 * Memisahkan hak akses antara Admin Hub (platform) dan Admin Toko (tenant).
 */
class StorePolicy
{
    /**
     * Helper untuk memeriksa apakah user merupakan owner atau admin dari toko tertentu.
     */
    private function managesStore(User $user, Store $store): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return StoreAdmin::query()
            ->where('user_id', $user->id)
            ->where('store_id', $store->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Melihat daftar seluruh toko: Hanya Admin Hub.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Melihat detail satu toko: Admin Hub atau pengelola toko tersebut.
     */
    public function view(User $user, Store $store): bool
    {
        return $this->managesStore($user, $store);
    }

    /**
     * Mendaftarkan/Onboarding toko baru: Hanya Admin Hub.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Memperbarui profil/status toko: Admin Hub atau pengelola toko.
     */
    public function update(User $user, Store $store): bool
    {
        return $this->managesStore($user, $store);
    }

    /**
     * Mengubah status aktif/nonaktif toko: Hanya Admin Hub.
     */
    public function changeStatus(User $user, Store $store): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menjadwalkan skema komisi platform: Hanya Admin Hub (wewenang platform).
     */
    public function scheduleCommission(User $user, Store $store): bool
    {
        return $user->isAdmin();
    }

    /**
     * Mengelola rekening bank toko (tambah/verifikasi/primary):
     * - Tambah rekening: Admin Hub atau Owner toko
     * - Verifikasi status (pending -> verified/rejected): Hanya Admin Hub
     */
    public function manageBank(User $user, Store $store): bool
    {
        return $this->managesStore($user, $store);
    }

    public function verifyBank(User $user, Store $store): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menugaskan atau mencabut peran anggota toko (store_admins):
     * Admin Hub atau Owner toko terkait.
     */
    public function assignRole(User $user, Store $store): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return StoreAdmin::query()
            ->where('user_id', $user->id)
            ->where('store_id', $store->id)
            ->where('role', 'owner')
            ->exists();
    }
}
