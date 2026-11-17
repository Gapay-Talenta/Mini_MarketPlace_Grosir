@props(['variant' => 'dark'])

@auth
    @php
        $user = auth()->user();
        $roleLabel = match (true) {
            $user->isAdmin() => 'Admin Hub',
            $user->isTenantOperator() => 'Mitra Toko',
            default => 'Pembeli',
        };

        $triggerClasses = $variant === 'light'
            ? 'inline-flex min-h-9 items-center gap-2 rounded-sm border border-slate-300 bg-white px-2.5 py-1 text-xs font-bold text-slate-800 shadow-2xs transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700'
            : 'inline-flex min-h-9 items-center gap-2 rounded-sm border border-slate-700 bg-slate-800 px-2.5 py-1 text-xs font-bold text-slate-100 transition-colors hover:border-amber-500/70 hover:bg-slate-800/80';
    @endphp

    <div
        x-data="{ open: false, confirmLogout: false }"
        @keydown.escape.window="open = false; confirmLogout = false"
        class="relative"
    >
        <button
            type="button"
            @click="open = !open"
            :aria-expanded="open"
            data-test="account-menu-button"
            class="{{ $triggerClasses }}"
        >
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-500 text-[10px] font-black text-slate-900">
                {{ $user->initials() }}
            </span>
            <span class="hidden max-w-28 truncate sm:inline">{{ $user->name }}</span>
            <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Dropdown Menu Akun -->
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.150ms
            @click.outside="open = false"
            class="absolute end-0 z-30 mt-2 w-60 overflow-hidden rounded-md border border-slate-300 bg-white text-slate-800 shadow-xl dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
        >
            <div class="border-b border-slate-200 bg-slate-50 px-3.5 py-3 dark:border-slate-800 dark:bg-slate-950/60">
                <div class="flex items-center justify-between gap-2">
                    <span class="truncate text-xs font-extrabold text-slate-900 dark:text-slate-100">{{ $user->name }}</span>
                    <span class="shrink-0 rounded-xs bg-amber-100 px-1.5 py-0.5 text-[9.5px] font-bold uppercase text-amber-800 dark:bg-amber-950/70 dark:text-amber-300">
                        {{ $roleLabel }}
                    </span>
                </div>
                <div class="mt-0.5 truncate font-mono text-[10.5px] text-slate-500 dark:text-slate-400">
                    {{ $user->email }}
                </div>
            </div>

            <div class="flex flex-col gap-0.5 p-1.5 text-xs font-semibold">
                <a
                    href="{{ route('dashboard') }}"
                    wire:navigate
                    @click="open = false"
                    class="rounded-sm px-2.5 py-2 text-slate-700 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    Dasbor Akun
                </a>
                <a
                    href="{{ route('profile.edit') }}"
                    wire:navigate
                    @click="open = false"
                    class="rounded-sm px-2.5 py-2 text-slate-700 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    Profil Saya
                </a>
                <a
                    href="{{ route('security.edit') }}"
                    wire:navigate
                    @click="open = false"
                    class="rounded-sm px-2.5 py-2 text-slate-700 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    Keamanan Akun
                </a>
            </div>

            <div class="border-t border-slate-200 p-1.5 dark:border-slate-800">
                <button
                    type="button"
                    @click="open = false; confirmLogout = true"
                    data-test="open-logout-modal-button"
                    class="flex w-full cursor-pointer items-center justify-between rounded-sm bg-rose-100 px-2.5 py-2 text-left text-xs font-extrabold text-rose-600 transition-colors hover:bg-rose-200 dark:bg-rose-950/60 dark:text-rose-400 dark:hover:bg-rose-950"
                >
                    <span>Keluar</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </div>

        <!-- Modal Konfirmasi Logout (SCREEN-AUTH-03) -->
        <x-logout-confirmation-modal title-id="account-logout-modal-title" />
    </div>
@else
    <div class="flex items-center gap-2">
        <a
            href="{{ route('login') }}"
            wire:navigate
            class="{{ $variant === 'light'
                ? 'inline-flex min-h-9 items-center rounded-sm border border-slate-300 px-3 text-xs font-bold text-slate-700 transition-colors hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200'
                : 'inline-flex min-h-9 items-center rounded-sm border border-slate-700 px-3 text-xs font-bold text-slate-200 transition-colors hover:border-amber-500 hover:text-amber-400' }}"
        >
            Masuk
        </a>
        @if (Route::has('register'))
            <a
                href="{{ route('register') }}"
                wire:navigate
                class="inline-flex min-h-9 items-center rounded-sm bg-amber-500 px-3 text-xs font-extrabold text-slate-900 transition-colors hover:bg-amber-600 hover:text-white"
            >
                Daftar
            </a>
        @endif
    </div>
@endauth
