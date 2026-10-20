<x-layouts.customer :title="'Toko: '.($store ?? $canteen ?? 'Mitra Grosir')">
    <div class="space-y-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2">
                <span class="rounded-xs bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                    TOKO GROSIR
                </span>
                <span class="text-xs font-mono text-slate-500">{{ $store ?? $canteen ?? 'toko-demo' }}</span>
            </div>
            <h1 class="mt-2 text-lg font-black tracking-tight text-slate-900 dark:text-slate-100">
                Katalog Produk Grosir
            </h1>
            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                Pemesanan grosir dengan tier harga kuantiti bertingkat dan kalkulasi ongkos kirim berbasis berat.
            </p>
        </div>

        <x-empty-state title="Katalog Produk Segera Hadir"
            description="Daftar produk dengan satuan jual dus/karung dan 4 tier harga grosir akan ditampilkan pada Modul 7." />
    </div>
</x-layouts.customer>
