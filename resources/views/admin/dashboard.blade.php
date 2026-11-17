<x-layouts.admin title="Dashboard Platform">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-slate-200 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-slate-100">Pusat Kendali Platform Grosir</h1>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Otoritas tata kelola kemitraan toko, tarif komisi platform bertingkat, dan pengawasan ledger akun.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.stores.index') }}" wire:navigate>
                    <x-button variant="secondary">Kelola Semua Toko</x-button>
                </a>
                <a href="{{ route('admin.stores.create') }}" wire:navigate>
                    <x-button variant="primary">+ Daftarkan Toko</x-button>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Mitra Toko</div>
                <div class="mt-2 font-mono text-2xl font-black text-slate-900 dark:text-slate-100">{{ $totalStores }}</div>
                <div class="mt-1 text-[11px] text-slate-400">Terdaftar pada platform</div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Mitra Aktif</div>
                <div class="mt-2 font-mono text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $activeStores }}</div>
                <div class="mt-1 text-[11px] text-slate-400">Tampil di katalog publik</div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Mitra Nonaktif</div>
                <div class="mt-2 font-mono text-2xl font-black text-rose-600 dark:text-rose-400">{{ $inactiveStores }}</div>
                <div class="mt-1 text-[11px] text-slate-400">Disembunyikan sementara</div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                    Daftar Mitra Toko Grosir
                </h2>
                <span class="text-xs font-mono text-slate-500">Effective-Dated Interval</span>
            </div>

            <div class="overflow-x-auto rounded-md border border-slate-200 dark:border-slate-800">
                <table class="w-full min-w-[44rem] text-left text-sm">
                    <thead class="bg-slate-900 text-xs font-bold uppercase tracking-wider text-slate-300">
                        <tr>
                            <th class="px-4 py-3">Nama Toko Grosir</th>
                            <th class="px-4 py-3">Penanggung Jawab / Kontak</th>
                            <th class="px-4 py-3">Tarif Komisi (%)</th>
                            <th class="px-4 py-3">Ongkir / kg</th>
                            <th class="px-4 py-3">Status Mitra</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                        @forelse ($stores as $store)
                            @php
                                $owner = $store->admins->first()?->user;
                                $activeComm = $store->commissionRates->first();
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ $store->name }}</div>
                                    <div class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ $store->slug }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-xs text-slate-700 dark:text-slate-300">
                                    <div class="font-semibold">{{ $owner?->name ?? '—' }}</div>
                                    @if ($owner?->email)
                                        <div class="font-mono text-slate-500">{{ $owner->email }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 font-mono font-bold text-amber-600 dark:text-amber-400">
                                    {{ $activeComm ? rtrim(rtrim(number_format((float) $activeComm->rate_percent, 2, ',', '.'), '0'), ',') . '%' : '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                                    Rp {{ number_format($store->shippingRate?->rate_per_kg ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <x-status-badge :status="$store->status === 'aktif' ? 'active' : 'suspended'">
                                        {{ strtoupper($store->status) }}
                                    </x-status-badge>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <a href="{{ route('admin.stores.edit', $store) }}" wire:navigate class="text-xs font-bold text-amber-600 hover:text-amber-700 hover:underline dark:text-amber-400">
                                        Kelola &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-6" colspan="6">
                                    <x-empty-state title="Belum Ada Toko Terdaftar"
                                        description="Daftarkan mitra toko grosir pertama untuk mulai mengatur tarif komisi, rekening pencairan, dan operator toko.">
                                        <x-slot:action>
                                            <a href="{{ route('admin.stores.create') }}" wire:navigate>
                                                <x-button variant="primary">+ Daftarkan Toko</x-button>
                                            </a>
                                        </x-slot:action>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
