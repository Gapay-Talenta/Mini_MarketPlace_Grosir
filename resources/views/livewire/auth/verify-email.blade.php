<x-layouts::auth title="Verifikasi Email">
    <div class="flex flex-col gap-5">
        <x-auth-header
            title="Verifikasi Alamat Email"
            description="Silakan verifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan ke kotak masuk Anda."
        />

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-sm border border-emerald-500 bg-emerald-100 px-3 py-2.5 text-xs font-bold text-emerald-700 dark:border-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300" role="status">
                Tautan verifikasi baru telah dikirim ke alamat email yang Anda daftarkan.
            </div>
        @endif

        <div class="flex flex-col gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-button variant="primary" type="submit" class="w-full rounded-sm">
                    Kirim Ulang Email Verifikasi
                </x-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button variant="outline" type="submit" class="w-full rounded-sm" data-test="logout-button">
                    Keluar dari Akun
                </x-button>
            </form>
        </div>
    </div>
</x-layouts::auth>
