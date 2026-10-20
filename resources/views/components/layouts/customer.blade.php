@props(['title' => 'Pembeli Grosir'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col bg-slate-50 shadow-xl dark:bg-slate-900 border-x border-slate-200 dark:border-slate-800">
        <header class="sticky top-0 z-10 flex min-h-14 items-center gap-3 border-b-2 border-amber-500 bg-slate-900 px-4 text-white">
            <div class="flex items-center gap-2">
                <div class="h-4 w-4 rounded-xs bg-amber-500"></div>
                <span class="text-xs font-black tracking-wider uppercase text-amber-400">HUB GROSIR</span>
            </div>
            <span class="text-xs text-slate-500">/</span>
            <span class="text-sm font-bold text-slate-100 truncate">{{ $title }}</span>
            @isset($headerRight)
                <div class="ms-auto flex items-center gap-2">{{ $headerRight }}</div>
            @endisset
        </header>

        <main class="flex-1 p-4">
            {{ $slot }}
        </main>

        <footer class="border-t border-slate-200 bg-white p-4 text-center text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
            <div class="font-semibold text-slate-700 dark:text-slate-300">{{ config('app.name', 'Hub Toko Grosir') }}</div>
            <div class="mt-0.5 text-[11px] text-slate-400">Platform Grosir Multi-Tenant Terpadu</div>
        </footer>
    </div>
    @fluxScripts
</body>
</html>
