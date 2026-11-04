<?php

namespace App\Support\Tenancy;

use App\Models\Store;
use RuntimeException;

/**
 * Menyimpan satu Store aktif selama SATU siklus request/job.
 * Didaftarkan sebagai scoped() binding di AppServiceProvider agar otomatis
 * di-reset antar request/job dan tidak bocor ke worker panjang (Queue/Reverb/Octane).
 */
final class TenantContext
{
    private ?Store $store = null;

    /**
     * Menetapkan toko aktif.
     */
    public function set(Store $store): void
    {
        $this->store = $store;
    }

    /**
     * Membersihkan toko aktif dari konteks memori.
     */
    public function clear(): void
    {
        $this->store = null;
    }

    /**
     * Memeriksa apakah ada toko yang sedang aktif.
     */
    public function has(): bool
    {
        return $this->store !== null;
    }

    /**
     * Mengambil instance Store aktif.
     * Menerapkan Fail-Fast: melempar exception jika context belum diisi.
     */
    public function store(): Store
    {
        return $this->store ?? throw new RuntimeException('TenantContext belum diisi.');
    }

    /**
     * Alias untuk konsistensi konvensi tenancy.
     */
    public function tenant(): Store
    {
        return $this->store();
    }

    /**
     * Mengambil ID toko aktif (fail-fast jika context kosong).
     */
    public function id(): int
    {
        return $this->store()->id;
    }

    /**
     * Mengambil ID toko aktif jika ada, atau null jika di luar konteks toko.
     * Digunakan oleh Policy atau Listener agar tidak melempar crash fatal.
     */
    public function idOrNull(): ?int
    {
        return $this->store?->id;
    }
}
