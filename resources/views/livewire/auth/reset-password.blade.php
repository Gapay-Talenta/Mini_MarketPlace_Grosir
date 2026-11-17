<x-layouts::auth title="Atur Ulang Kata Sandi">
    <div class="flex flex-col gap-5">
        <x-auth-session-status :status="session('status')" />

        <x-auth-header
            title="Atur Ulang Kata Sandi"
            description="Masukkan kata sandi baru Anda di bawah ini."
        />

        @if ($errors->any())
            <div class="rounded-sm border border-rose-500 bg-rose-100 px-3 py-2.5 text-xs font-semibold text-rose-700 dark:border-rose-700 dark:bg-rose-950/60 dark:text-rose-300" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-4">
            @csrf

            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <div class="flex flex-col gap-1.5">
                <label for="email" class="text-xs font-bold text-slate-700 dark:text-slate-200">
                    Email
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', request('email')) }}"
                    required
                    autocomplete="email"
                    class="min-h-11 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                />
            </div>

            <div class="flex flex-col gap-1.5" x-data="{ show: false }">
                <label for="password" class="text-xs font-bold text-slate-700 dark:text-slate-200">
                    Kata sandi baru
                </label>
                <div class="relative">
                    <input
                        id="password"
                        name="password"
                        :type="show ? 'text' : 'password'"
                        required
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        class="min-h-11 w-full rounded-sm border border-slate-300 bg-white pe-12 ps-3 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                    />
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 end-0 flex items-center px-3 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200"
                    >
                        <span x-text="show ? 'Tutup' : 'Lihat'">Lihat</span>
                    </button>
                </div>
            </div>

            <div class="flex flex-col gap-1.5" x-data="{ show: false }">
                <label for="password_confirmation" class="text-xs font-bold text-slate-700 dark:text-slate-200">
                    Konfirmasi kata sandi baru
                </label>
                <div class="relative">
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        :type="show ? 'text' : 'password'"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi kata sandi baru"
                        class="min-h-11 w-full rounded-sm border border-slate-300 bg-white pe-12 ps-3 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                    />
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute inset-y-0 end-0 flex items-center px-3 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200"
                    >
                        <span x-text="show ? 'Tutup' : 'Lihat'">Lihat</span>
                    </button>
                </div>
            </div>

            <x-button variant="primary" type="submit" class="mt-1 w-full rounded-sm" data-test="reset-password-button">
                Simpan Kata Sandi Baru
            </x-button>
        </form>
    </div>
</x-layouts::auth>
