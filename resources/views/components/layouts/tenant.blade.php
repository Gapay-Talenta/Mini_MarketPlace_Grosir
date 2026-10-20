@props(['title' => 'Portal Toko Grosir'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div x-data="{ open: false }" class="flex min-h-screen">
        <!-- Sidebar Mitra Toko (Industrial Slate) -->
        <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-20 flex w-64 flex-col border-r border-slate-800 bg-slate-900 p-4 text-white transition-transform duration-200 lg:static lg:translate-x-0">
            <div class="mb-6 flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center gap-2">
                    <div class="h-4 w-4 rounded-xs bg-amber-500"></div>
                    <span class="text-xs font-black tracking-wider uppercase text-amber-400">HUB GROSIR</span>
                </div>
                <span class="rounded-xs bg-amber-500 px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider text-slate-900">MITRA TOKO</span>
            </div>

            <nav class="flex flex-1 flex-col gap-1 text-sm font-semibold">
                <a href="#" class="flex items-center gap-2 rounded-md border-l-2 border-amber-500 bg-slate-800 px-3 py-2 text-amber-400">
                    <span>Dashboard Toko</span>
                </a>
                <span class="flex items-center gap-2 rounded-md px-3 py-2 text-slate-400">
                    <span>Katalog Produk (Modul 7)</span>
                </span>
                <span class="flex items-center gap-2 rounded-md px-3 py-2 text-slate-400">
                    <span>Pesanan / Fulfillment (Modul 12)</span>
                </span>
            </nav>

            <div class="border-t border-slate-800 pt-3 text-[11px] text-slate-400">
                <span>Tenant Portal • Industrial UI</span>
            </div>
        </aside>

        <!-- Main Body -->
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex min-h-14 items-center gap-3 border-b border-slate-200 bg-white px-6 dark:border-slate-800 dark:bg-slate-900">
                <button type="button" @click="open = !open" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Toggle sidebar">
                    ☰
                </button>
                <div class="flex items-baseline gap-2">
                    <span class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $title }}</span>
                </div>
                @isset($headerRight)
                    <div class="ms-auto flex items-center gap-3">{{ $headerRight }}</div>
                @endisset
            </header>

            <main class="flex-1 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
    @fluxScripts
</body>
</html>
