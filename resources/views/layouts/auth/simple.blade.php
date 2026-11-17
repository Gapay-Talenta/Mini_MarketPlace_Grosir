@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div class="flex min-h-svh flex-col items-center justify-center p-4 sm:p-6 md:p-10">
            <div class="grid w-full max-w-[860px] grid-cols-1 overflow-hidden rounded-lg border-2 border-slate-800 bg-white shadow-2xl lg:min-h-[540px] lg:grid-cols-12 dark:border-slate-700 dark:bg-slate-900">
                <!-- Panel Kiri: Branding Industrial Slate & Safety Amber -->
                <aside class="hidden flex-col justify-between gap-6 border-e-[3px] border-amber-500 bg-slate-900 p-8 text-white lg:col-span-5 lg:flex">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 group" wire:navigate>
                        <span class="h-5 w-5 shrink-0 rounded-xs bg-amber-500"></span>
                        <span class="text-sm font-extrabold tracking-wide uppercase text-white transition-colors group-hover:text-amber-400">
                            HUB TOKO GROSIR
                        </span>
                    </a>

                    @isset($aside)
                        {{ $aside }}
                    @else
                        <div class="space-y-3">
                            <h2 class="text-2xl font-extrabold leading-tight tracking-tight text-white">
                                Platform grosir multi-toko yang aman dan terintegrasi.
                            </h2>
                            <p class="text-xs leading-relaxed text-slate-300">
                                Kelola akun pembeli, toko grosir mitra, dan pemantauan transaksi dalam satu sistem terpadu.
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
                    @endisset
                </aside>

                <!-- Panel Kanan: Form Autentikasi -->
                <main class="flex flex-col bg-slate-50 p-6 sm:p-8 lg:col-span-7 dark:bg-slate-900">
                    <div class="mb-5 flex items-center justify-between lg:hidden">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5" wire:navigate>
                            <span class="h-5 w-5 shrink-0 rounded-xs bg-amber-500"></span>
                            <span class="text-sm font-extrabold tracking-wide uppercase text-slate-900 dark:text-white">
                                HUB TOKO GROSIR
                            </span>
                        </a>
                    </div>

                    <div class="flex flex-1 flex-col justify-center">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
