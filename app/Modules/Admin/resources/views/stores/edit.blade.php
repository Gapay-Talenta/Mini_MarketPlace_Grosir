<x-layouts.admin title="Kelola Toko — {{ $store->name }}">
    @if (session('status'))
        <div class="mb-4 rounded-md bg-emerald-100 border border-emerald-300 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-rose-100 border border-rose-300 px-4 py-3 text-sm text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800">
            <ul class="list-disc ps-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ $store->name }}</h1>
                <x-status-badge :status="$store->status === 'aktif' ? 'active' : 'suspended'">
                    {{ strtoupper($store->status) }}
                </x-status-badge>
            </div>
            <p class="mt-1 font-mono text-xs text-slate-500 dark:text-slate-400">Slug: {{ $store->slug }} · Tarif Ongkir: Rp {{ number_format($store->shippingRate?->rate_per_kg ?? 0, 0, ',', '.') }}/kg</p>
        </div>
        <a href="{{ route('admin.stores.index') }}">
            <x-button variant="secondary">&larr; Kembali</x-button>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Kartu Komisi Platform -->
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Skema Komisi Platform (Effective-Dated)</h2>
            <div class="mt-3 p-3 rounded-md bg-amber-50 border border-amber-200 text-xs text-amber-900 dark:bg-amber-950/30 dark:border-amber-800/60 dark:text-amber-200">
                Komisi aktif saat ini: 
                <span class="font-extrabold text-sm text-amber-700 dark:text-amber-300">
                    {{ $activeCommission ? number_format((float) $activeCommission->rate_percent, 2, ',', '.') . '%' : 'Belum ditetapkan' }}
                </span>
            </div>

            <div class="mt-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Riwayat Versi Tarif:</h3>
                <ul class="mt-2 divide-y divide-slate-100 text-xs dark:divide-slate-800" data-test="commission-history">
                    @foreach ($commissions as $comm)
                        <li class="py-2 flex flex-wrap items-center justify-between gap-2">
                            <span class="font-bold text-slate-800 dark:text-slate-200">
                                {{ rtrim(rtrim(number_format((float) $comm->rate_percent, 2, ',', '.'), '0'), ',') }}%
                                <span class="font-normal text-slate-500">&middot; oleh {{ $commissionActors[$comm->id] ?? 'sistem' }}</span>
                            </span>
                            <span class="text-slate-500">
                                {{ $comm->valid_from->format('d M Y H:i') }} &rarr; {{ $comm->valid_to ? $comm->valid_to->format('d M Y H:i') : 'sekarang' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
                        <!-- Form Jadwal Komisi Baru -->
            <form method="POST" action="{{ route('admin.stores.commission.store', $store) }}" class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                @csrf
                <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Jadwalkan Tarif Baru</div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <x-input name="commission_rate" label="Tarif Baru (%)" type="number" step="0.01" min="0" max="100" placeholder="Contoh: 7.50" required />
                    <x-input name="effective_at" label="Mulai Berlaku" type="datetime-local" min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}" required />
                </div>

                <p class="text-[11px] text-amber-800 bg-amber-50 p-2.5 rounded border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                    <strong>Proteksi Anti-Overlap:</strong> Tarif lama otomatis ditutup pada waktu efektif tarif baru. Nilai sub-order lama tetap konsisten.
                </p>

                <x-button type="submit" variant="slate" class="w-full">Jadwalkan Versi Komisi</x-button>
            </form>
        </div>

        <!-- Kolom Kanan: Rekening Bank & Anggota -->
        <div class="space-y-6">
            <!-- Kartu Rekening Bank Pencairan -->
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Rekening Bank Pencairan</h2>
                <p class="mt-1 text-xs text-slate-500">Nomor rekening disimpan terenkripsi; hanya 4 digit terakhir yang ditampilkan.</p>

                <ul class="mt-4 divide-y divide-slate-100 text-xs dark:divide-slate-800">
                    @forelse ($bankAccounts as $acc)
                        @php
                            $badgeStatus = match ($acc->status) {
                                'verified' => 'active',
                                'rejected' => 'suspended',
                                default => 'pending',
                            };
                        @endphp
                        <li class="py-3 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-slate-900 dark:text-slate-100">
                                    {{ $acc->bank_name }} <span class="font-mono text-slate-600 dark:text-slate-300">&bull;&bull;&bull;&bull; {{ $acc->account_last4 }}</span>
                                </div>
                                <div class="text-slate-500 text-[11px]">a.n. {{ $acc->account_holder_name }}</div>
                            </div>

                            <div class="flex items-center gap-2">
                                <x-status-badge :status="$badgeStatus">{{ strtoupper($acc->status) }}</x-status-badge>

                                @can('verifyBank', $store)
                                    @if ($acc->status !== 'verified')
                                        <form method="POST" action="{{ route('admin.stores.bank.verify', [$store, $acc]) }}">
                                            @csrf
                                            <input type="hidden" name="approve" value="1">
                                            <button type="submit" class="rounded bg-emerald-600 px-2 py-1 text-[11px] font-bold text-white hover:bg-emerald-700">
                                                Verifikasi
                                            </button>
                                        </form>
                                    @endif
                                    @if ($acc->status !== 'rejected')
                                        <form method="POST" action="{{ route('admin.stores.bank.verify', [$store, $acc]) }}">
                                            @csrf
                                            <input type="hidden" name="approve" value="0">
                                            <button type="submit" class="rounded bg-rose-600 px-2 py-1 text-[11px] font-bold text-white hover:bg-rose-700">
                                                Tolak
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </li>
                    @empty
                        <li class="py-3 text-slate-400 italic">Belum ada rekening bank terdaftar.</li>
                    @endforelse
                </ul>

                <form method="POST" action="{{ route('admin.stores.bank.store', $store) }}" class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                    @csrf
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Tambah Rekening Baru</div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <x-input name="bank_name" label="Nama Bank" placeholder="Contoh: BCA / Mandiri" required />
                        <x-input name="account_holder_name" label="Nama Pemilik Rekening" placeholder="Sesuai buku tabungan" required />
                    </div>
                    <x-input name="account_number" label="Nomor Rekening" inputmode="numeric" placeholder="6 - 20 digit angka" required />

                    <x-button type="submit" variant="slate" class="w-full">Tambah Rekening</x-button>
                </form>
            </div>

            <!-- Admin & Staf Operator Toko -->
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Admin &amp; Staf Operator Toko</h2>
                <p class="mt-1 text-xs text-slate-500">Daftar pengguna yang memiliki akses operasional ke portal toko grosir ini.</p>

                <ul class="mt-4 divide-y divide-slate-100 text-xs dark:divide-slate-800">
                    @forelse ($members as $member)
                        <li class="py-2.5 flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $member->user?->name ?? 'User Tidak Diketahui' }}</div>
                                <div class="font-mono text-slate-500 text-[11px]">{{ $member->user?->email }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-status-badge :status="$member->role === 'owner' ? 'active' : 'default'">
                                    {{ strtoupper($member->role) }}
                                </x-status-badge>

                                <form method="POST" action="{{ route('admin.stores.roles.destroy', [$store, $member->user_id]) }}"
                                      onsubmit="return confirm('Cabut peran {{ $member->role }} dari {{ $member->user?->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="role" value="{{ $member->role }}">
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 hover:underline text-xs font-semibold p-1">
                                        Cabut
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="py-3 text-slate-400 italic">Belum ada staf yang ditugaskan ke toko ini.</li>
                    @endforelse
                </ul>

                <form method="POST" action="{{ route('admin.stores.roles.store', $store) }}" class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                    @csrf
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Tugaskan Pengguna Baru</div>
                    
                    <x-input name="email" label="Surel Pengguna Terdaftar" type="email" placeholder="nama@grosir.test" required />

                    <div class="flex flex-col gap-1.5">
                        <label for="role" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Peran Toko</label>
                        <select id="role" name="role" class="min-h-11 rounded-md border border-slate-300 bg-white px-3.5 text-sm text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" required>
                            <option value="operator">Operator (Kelola Pesanan &amp; Stok)</option>
                            <option value="admin">Admin (Kelola Produk, Stok &amp; Pesanan)</option>
                            <option value="owner">Owner (Hak Penuh Toko &amp; Penarikan Saldo)</option>
                        </select>
                    </div>

                    <x-button type="submit" variant="slate" class="w-full">Tugaskan ke Toko</x-button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>