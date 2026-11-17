@props(['title' => 'Admin Platform'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="flex min-h-screen flex-col">
        <!-- Topbar Admin Hub -->
        <header class="sticky top-0 z-10 flex min-h-14 flex-wrap items-center gap-3 border-b-2 border-amber-500 bg-slate-900 px-4 py-2 text-white sm:px-6">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2" wire:navigate>
                <div class="h-4 w-4 rounded-xs bg-amber-500"></div>
                <span class="text-sm font-black tracking-wider uppercase text-amber-400">HUB TOKO GROSIR</span>
            </a>
            <span class="rounded-xs bg-amber-500 px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider text-slate-900">ADMIN HUB</span>

            <nav class="ms-2 flex items-center gap-1 text-xs font-bold">
                <a
                    href="{{ route('admin.dashboard') }}"
                    wire:navigate
                    @class([
                        'rounded-sm px-2.5 py-1.5 transition-colors',
                        'bg-slate-800 text-amber-400' => request()->routeIs('admin.dashboard'),
                        'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.dashboard'),
                    ])
                >
                    Dashboard
                </a>
                <a
                    href="{{ route('admin.stores.index') }}"
                    wire:navigate
                    @class([
                        'rounded-sm px-2.5 py-1.5 transition-colors',
                        'bg-slate-800 text-amber-400' => request()->routeIs('admin.stores.*'),
                        'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.stores.*'),
                    ])
                >
                    Mitra Toko
                </a>
            </nav>

            <div class="ms-auto flex items-center gap-3">
                @isset($headerRight)
                    {{ $headerRight }}
                @endisset
                <x-account-menu variant="dark" />
            </div>
        </header>

        <!-- Main Body -->
        <main class="mx-auto w-full max-w-6xl flex-1 p-6">
            {{ $slot }}
        </main>
    </div>
    @fluxScripts
</body>
</html>
