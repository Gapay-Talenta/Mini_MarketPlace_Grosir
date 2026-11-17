<x-layouts::auth title="Lupa Kata Sandi">
    <div class="flex flex-col gap-5">
        <x-auth-session-status :status="session('status')" />

        <x-auth-header
            title="Lupa Kata Sandi"
            description="Masukkan alamat email akun Anda untuk menerima tautan pengaturan ulang kata sandi."
        />

        @if ($errors->any())
            <div class="rounded-sm border border-rose-500 bg-rose-100 px-3 py-2.5 text-xs font-semibold text-rose-700 dark:border-rose-700 dark:bg-rose-950/60 dark:text-rose-300" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
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

            <x-button variant="primary" type="submit" class="w-full rounded-sm" data-test="email-password-reset-link-button">
                Kirim Tautan Reset Kata Sandi
            </x-button>
        </form>

        <div class="border-t border-slate-200 pt-4 text-center text-xs text-slate-600 dark:border-slate-800 dark:text-slate-400">
            <span>Atau kembali ke halaman</span>
            <a
                href="{{ route('login') }}"
                wire:navigate
                class="ms-1 font-bold text-amber-600 hover:text-amber-700 hover:underline dark:text-amber-400"
            >
                Masuk
            </a>
        </div>
    </div>
</x-layouts::auth>
