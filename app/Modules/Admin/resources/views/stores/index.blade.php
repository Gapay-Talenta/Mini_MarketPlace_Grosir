<x-layouts.admin title="Toko Grosir &amp; Skema Komisi">
    @if (session('status'))
        <div class="mb-4 rounded-md bg-emerald-100 border border-emerald-300 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-slate-100">Toko Grosir &amp; Skema Komisi</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola mitra toko grosir, penanggung jawab, tarif komisi platform, dan status operasional.</p>
        </div>
        <a href="{{ route('admin.stores.create') }}">
            <x-button variant="primary">+ Daftarkan Toko</x-button>
        </a>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-600 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                <tr>
                    <th class="px-5 py-3.5">Toko Grosir</th>
                    <th class="px-5 py-3.5">Penanggung Jawab &amp; Rekening</th>
                    <th class="px-5 py-3.5">Komisi Aktif</th>
                    <th class="px-5 py-3.5">Ongkir / kg</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($stores as $store)
                    @php
                        $owner = $store->admins->first()?->user;
                        $bank = $store->bankAccounts->first();
                        $activeComm = $store->commissionRates->first();
                    @endphp
                    <tr @class([
                        'hover:bg-slate-50/50 dark:hover:bg-slate-800/40',
                        'opacity-60' => $store->status !== 'aktif',
                    ])>
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $store->name }}</div>
                            <div class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ $store->slug }}</div>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-700 dark:text-slate-300">
                            <div class="font-semibold">{{ $owner?->name ?? '—' }}</div>
                            @if ($bank)
                                <div class="font-mono text-slate-500">{{ $bank->bank_name }} &bull;&bull;{{ $bank->account_last4 }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-bold text-amber-600 dark:text-amber-400">
                            {{ $activeComm ? rtrim(rtrim(number_format((float) $activeComm->rate_percent, 2, ',', '.'), '0'), ',') . '%' : '—' }}
                        </td>
                        <td class="px-5 py-4 font-medium text-slate-600 dark:text-slate-300">
                            Rp {{ number_format($store->shippingRate?->rate_per_kg ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-4">
                            <x-status-badge :status="$store->status === 'aktif' ? 'active' : 'suspended'">
                                {{ strtoupper($store->status) }}
                            </x-status-badge>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.stores.edit', $store) }}" class="font-bold text-amber-600 hover:text-amber-700 hover:underline dark:text-amber-400">
                                    Kelola
                                </a>

                                <form method="POST" action="{{ route('admin.stores.status', $store) }}"
                                      onsubmit="return confirm('{{ $store->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }} {{ $store->name }}?')">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $store->status === 'aktif' ? 'nonaktif' : 'aktif' }}">
                                    <button type="submit" class="text-xs font-bold {{ $store->status === 'aktif' ? 'text-rose-600 hover:text-rose-800' : 'text-emerald-600 hover:text-emerald-800' }} hover:underline">
                                        {{ $store->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6">
                            <x-empty-state title="Belum ada toko grosir" description="Klik tombol di atas untuk mendaftarkan mitra toko grosir pertama.">
                                <x-slot:action>
                                    <a href="{{ route('admin.stores.create') }}">
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
    <p class="mt-3 text-xs text-slate-500">Toko nonaktif otomatis disembunyikan dari katalog publik tanpa menghapus data historis.</p>
</x-layouts.admin>
