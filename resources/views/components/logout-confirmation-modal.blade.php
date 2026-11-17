@props(['titleId' => 'logout-modal-title'])

<template x-teleport="body">
    <div
        x-cloak
        x-show="confirmLogout"
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/65 p-4 backdrop-blur-[1px]"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $titleId }}"
    >
        <div
            @click.outside="confirmLogout = false"
            class="w-full max-w-sm rounded-lg border border-slate-200 border-t-4 border-t-amber-500 bg-white p-6 shadow-2xl dark:border-slate-700 dark:border-t-amber-500 dark:bg-slate-900"
        >
            <h3 id="{{ $titleId }}" class="text-base font-extrabold text-slate-900 dark:text-slate-100">
                Keluar dari akun?
            </h3>
            <p class="mt-2 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                Sesi Anda akan diakhiri di perangkat ini. Isi keranjang tetap tersimpan di akun dan dapat dilanjutkan setelah login kembali.
            </p>

            <div class="mt-5 flex items-center gap-2.5">
                <x-button
                    type="button"
                    variant="outline"
                    class="flex-1 rounded-sm"
                    @click="confirmLogout = false"
                >
                    Batal
                </x-button>

                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <x-button
                        type="submit"
                        variant="slate"
                        class="w-full rounded-sm"
                        data-test="logout-button"
                    >
                        Ya, Keluar
                    </x-button>
                </form>
            </div>
        </div>
    </div>
</template>
