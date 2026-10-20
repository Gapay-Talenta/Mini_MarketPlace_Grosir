@props(['title' => 'Admin Platform'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="flex min-h-screen flex-col">
        <!-- Topbar Admin Hub -->
        <header class="sticky top-0 z-10 flex min-h-14 items-center gap-3 border-b-2 border-amber-500 bg-slate-900 px-6 text-white">
            <div class="flex items-center gap-2">
                <div class="h-4 w-4 rounded-xs bg-amber-500"></div>
                <span class="text-sm font-black tracking-wider uppercase text-amber-400">HUB TOKO GROSIR</span>
            </div>
            <span class="rounded-xs bg-amber-500 px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider text-slate-900">ADMIN HUB</span>
            <span class="text-xs text-slate-500">/</span>
            <span class="text-sm font-semibold text-slate-200">{{ $title }}</span>

            @isset($headerRight)
                <div class="ms-auto flex items-center gap-3">{{ $headerRight }}</div>
            @endisset
        </header>

        <!-- Main Body -->
        <main class="mx-auto w-full max-w-6xl flex-1 p-6">
            {{ $slot }}
        </main>
    </div>
    @fluxScripts
</body>
</html>
