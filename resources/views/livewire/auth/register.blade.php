<x-layouts::auth title="Buat Akun Pembeli">
    <x-slot:aside>
        <div class="space-y-3">
            <h2 class="text-2xl font-extrabold leading-snug tracking-tight text-white">
                Daftar sekali, belanja dari banyak toko grosir.
            </h2>
            <p class="text-xs leading-relaxed text-slate-300">
                Nomor WhatsApp dipakai untuk notifikasi pembayaran, pengiriman, dan resi. Tanpa OTP pada versi MVP.
            </p>
        </div>

        <div class="flex flex-col gap-2.5 text-xs text-slate-300">
            <div class="flex items-center gap-2.5">
                <span class="h-2 w-2 shrink-0 rounded-xs bg-amber-500"></span>
                <span>Harga tier otomatis sesuai jumlah</span>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="h-2 w-2 shrink-0 rounded-xs bg-amber-500"></span>
                <span>Satu order induk, banyak sub-order toko</span>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="h-2 w-2 shrink-0 rounded-xs bg-amber-500"></span>
                <span>Pantau status kiriman real-time</span>
            </div>
        </div>
    </x-slot:aside>

    <div class="flex flex-col gap-4">
        <x-auth-session-status :status="session('status')" />

        <x-auth-header
            title="Buat Akun Pembeli"
            description="Isi data berikut untuk mulai berbelanja harga tier grosir lintas toko."
        />

        @if ($errors->any())
            <div class="rounded-sm border border-rose-500 bg-rose-100 px-3 py-2.5 text-xs font-semibold text-rose-700 dark:border-rose-700 dark:bg-rose-950/60 dark:text-rose-300" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-3.5">
            @csrf

            <div class="flex flex-col gap-1">
                <label for="name" class="text-xs font-bold text-slate-700 dark:text-slate-200">
                    Nama Pemilik / Toko Kelontong
                </label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Contoh: Budi Santoso (Toko Berkah)"
                    class="min-h-10.5 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500"
                />
                @error('name')
                    <span class="text-[11px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col gap-1">
                <label for="email" class="text-xs font-bold text-slate-700 dark:text-slate-200">
                    Email
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    placeholder="budi.berkah@gmail.com"
                    class="min-h-10.5 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500"
                />
                @error('email')
                    <span class="text-[11px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between gap-2">
                    <label for="phone" class="text-xs font-bold text-slate-700 dark:text-slate-200">
                        Nomor WhatsApp (Wajib)
                    </label>
                    <span class="inline-flex items-center rounded-sm border border-amber-400 bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-700 dark:border-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                        Target Notifikasi
                    </span>
                </div>
                <input
                    id="phone"
                    name="phone"
                    type="tel"
                    inputmode="tel"
                    value="{{ old('phone') }}"
                    required
                    autocomplete="tel"
                    placeholder="081234567890"
                    class="min-h-10.5 w-full rounded-sm border-2 border-slate-800 bg-white px-3 font-mono text-sm font-bold text-slate-900 placeholder-slate-400 transition-colors focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-slate-500 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500"
                />
                <span class="text-[11px] text-slate-500 dark:text-slate-400">
                    Digunakan Fonnte Gateway untuk notifikasi order &amp; resi kiriman (tanpa OTP).
                </span>
                @error('phone')
                    <span class="text-[11px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="flex flex-col gap-1" x-data="{ show: false }">
                    <label for="password" class="text-xs font-bold text-slate-700 dark:text-slate-200">
                        Kata sandi
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            :type="show ? 'text' : 'password'"
                            required
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                            class="min-h-10.5 w-full rounded-sm border border-slate-300 bg-white pe-11 ps-3 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500"
                        />
                        <button
                            type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 end-0 flex items-center px-2.5 text-[11px] font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200"
                        >
                            <span x-text="show ? 'Tutup' : 'Lihat'">Lihat</span>
                        </button>
                    </div>
                    @error('password')
                        <span class="text-[11px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex flex-col gap-1" x-data="{ show: false }">
                    <label for="password_confirmation" class="text-xs font-bold text-slate-700 dark:text-slate-200">
                        Konfirmasi Kata sandi
                    </label>
                    <div class="relative">
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            :type="show ? 'text' : 'password'"
                            required
                            autocomplete="new-password"
                            placeholder="Ulangi kata sandi"
                            class="min-h-10.5 w-full rounded-sm border border-slate-300 bg-white pe-11 ps-3 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500"
                        />
                        <button
                            type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 end-0 flex items-center px-2.5 text-[11px] font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200"
                        >
                            <span x-text="show ? 'Tutup' : 'Lihat'">Lihat</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="inline-flex cursor-pointer items-center gap-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <input
                        type="checkbox"
                        name="terms"
                        value="1"
                        @checked(old('terms'))
                        required
                        class="h-4 w-4 rounded-xs border-2 border-slate-800 text-amber-500 accent-amber-500 focus:ring-amber-500 dark:border-slate-500"
                    />
                    <span>Saya menyetujui syarat dan ketentuan platform</span>
                </label>
                @error('terms')
                    <span class="text-[11px] font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</span>
                @enderror
            </div>

            <x-button variant="primary" type="submit" class="mt-1 w-full rounded-sm" data-test="register-user-button">
                Daftar &amp; Mulai Belanja
            </x-button>
        </form>

        <div class="border-t border-slate-200 pt-3.5 text-center text-xs text-slate-600 dark:border-slate-800 dark:text-slate-400">
            <span>Sudah punya akun?</span>
            <a
                href="{{ route('login') }}"
                wire:navigate
                class="ms-1 font-bold text-amber-600 hover:text-amber-700 hover:underline dark:text-amber-400"
            >
                Masuk di sini
            </a>
        </div>
    </div>
</x-layouts::auth>
