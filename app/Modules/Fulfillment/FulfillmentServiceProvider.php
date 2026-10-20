<?php

namespace App\Modules\Fulfillment;

use App\Modules\ModuleServiceProvider;

/**
 * Modul: Fulfillment (alias `fulfillment`).
 * Tanggung jawab: Kitchen Display System / Pemrosesan Gudang realtime, state machine order, notifikasi (Modul 12).
 *
 * Titik perakitan modul: binding container di register(); route (routes/*.php), view
 * (`fulfillment::`) dan komponen Livewire (`<livewire:fulfillment::...>`) dimuat oleh
 * ModuleServiceProvider::boot(). Batas antarmodul ditegakkan lewat kontrak & event,
 * bukan akses langsung tabel/controller modul lain.
 */
final class FulfillmentServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        // Binding kontrak -> implementasi ditambahkan saat modul diimplementasikan.
    }

    protected function moduleAlias(): string
    {
        return 'fulfillment';
    }
}
