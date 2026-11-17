<x-layouts::auth title="Masuk ke Akun">
    <x-slot:aside>
        <div class="space-y-2.5">
            <h2 class="text-2xl font-extrabold leading-snug tracking-tight text-white">
                Belanja grosir lintas toko dalam satu keranjang.
            </h2>
            <p class="text-xs leading-relaxed text-slate-300">
                Harga tier otomatis, ongkir per toko berdasarkan berat, dan pelacakan kiriman dari satu akun.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center rounded-sm border border-amber-400 bg-amber-100 px-2 py-1 text-[10.5px] font-bold text-amber-700">
                Tier Grosir
            </span>
            <span class="inline-flex items-center rounded-sm border border-amber-400 bg-amber-100 px-2 py-1 text-[10.5px] font-bold text-amber-700">
                Multi-Toko
            </span>
            <span class="inline-flex items-center rounded-sm border border-amber-400 bg-amber-100 px-2 py-1 text-[10.5px] font-bold text-amber-700">
                Notifikasi WhatsApp
            </span>
        </div>
    </x-slot:aside>

    <div class="flex flex-col gap-5">
        <x-auth-session-status :status="session('status')" />

        <x-auth-header
            title="Masuk ke Akun"
            description="Gunakan email dan kata sandi akun pembeli, toko grosir, atau admin hub Anda."
        />

        @if ($errors->any())
            <div class="rounded-sm border border-rose-500 bg-rose-100 px-3 py-2.5 text-xs font-semibold text-rose-700 dark:border-rose-700 dark:bg-rose-950/60 dark:text-rose-300" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
            @csrf

            <div class="flex flex-col gap-1.5">
                <label for="email" class="text-xs font-bold text-slate-700 dark:text-slate-200">
                    Email
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="nama@grosir.test"
                    class="min-h-11 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500"
                />
            </div>

            <div class="flex flex-col gap-1.5" x-data="{ show: false }">
                <div class="flex items-center justify-between gap-2">
                    <label for="password" class="text-xs font-bold text-slate-700 dark:text-slate-200">
                        Kata sandi
                    </label>

                    @if (Route::has('password.request'))
                        <a
                            href="{{ route('password.request') }}"
                            wire:navigate
                            class="text-xs font-bold text-amber-600 hover:text-amber-700 hover:underline dark:text-amber-400"
                        >
                            Lupa kata sandi?
                        </a>
                    @endif
                </div>

                <div class="relative">
                    <input
                        id="password"
                        name="password"
                        :type="show ? 'text' : 'password'"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi"
                        class="min-h-11 w-full rounded-sm border border-slate-300 bg-white pe-12 ps-3 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500"
                    />
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 end-0 flex items-center px-3 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200"
                        :aria-label="show ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'"
                    >
                        <span x-text="show ? 'Tutup' : 'Lihat'">Lihat</span>
                    </button>
                </div>
            </div>

            <label class="inline-flex cursor-pointer items-center gap-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    @checked(old('remember'))
                    class="h-4 w-4 rounded-xs border-2 border-slate-800 text-amber-500 accent-amber-500 focus:ring-amber-500 dark:border-slate-500"
                />
                <span>Ingat saya di perangkat ini</span>
            </label>

            <!-- Kebijakan UC-19 ditampilkan agar pengguna tahu konsekuensi percobaan gagal. -->
            <p class="rounded-sm border-l-2 border-amber-500 bg-slate-100 px-3 py-2 text-[11px] leading-relaxed text-slate-600 dark:bg-slate-800/70 dark:text-slate-400" data-test="lockout-policy">
                5× gagal dalam 10 menit → akun terkunci 15 menit. Sesi berakhir setelah 8 jam tidak aktif.
            </p>

            <x-button variant="primary" type="submit" class="mt-1 w-full rounded-sm" data-test="login-button">
                Masuk &rarr;
            </x-button>
        </form>

        @if (Route::has('register'))
            <div class="border-t border-slate-200 pt-4 text-center text-xs text-slate-600 dark:border-slate-800 dark:text-slate-400">
                <span>Belum punya akun pembeli?</span>
                <a
                    href="{{ route('register') }}"
                    wire:navigate
                    class="ms-1 font-bold text-amber-600 hover:text-amber-700 hover:underline dark:text-amber-400"
                >
                    Daftar sebagai pembeli
                </a>
            </div>
        @endif
    </div>
</x-layouts::auth>
