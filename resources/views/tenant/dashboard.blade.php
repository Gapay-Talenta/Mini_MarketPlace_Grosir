<x-layouts.tenant title="Dashboard Toko Grosir">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-slate-200 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-black text-slate-900 dark:text-slate-100">Panel Operasional Toko</h1>
                    <x-status-badge status="active">Mitra Aktif</x-status-badge>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    ID Toko / Tenant Slug: <span class="font-mono font-bold text-amber-600 dark:text-amber-400">{{ $tenant }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <x-button variant="primary">Tambah Produk</x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Total SKU Aktif</div>
                <div class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">0</div>
                <div class="mt-1 text-[11px] text-slate-400">Modul 7: Katalog Produk</div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Antrean Siap Kirim</div>
                <div class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">0</div>
                <div class="mt-1 text-[11px] text-slate-400">Modul 12: Fulfillment & Resi</div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Saldo Tertahan (Hold)</div>
                <div class="mt-2 text-2xl font-black text-amber-600 dark:text-amber-400">Rp 0</div>
                <div class="mt-1 text-[11px] text-slate-400">Modul 10-11: Ledger Akun 2100</div>
            </div>
        </div>

        <x-empty-state title="Belum Ada Aktivitas Transaksi"
            description="Katalog produk, manajemen pesanan grosir, dan input resi kargo akan aktif di Modul 7 & 12." />
    </div>
</x-layouts.tenant>
