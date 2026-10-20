<x-layouts.admin title="Dashboard Platform">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-slate-200 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-slate-100">Pusat Kendali Platform Grosir</h1>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Otoritas tata kelola kemitraan toko, tarif komisi platform bertingkat, dan pengawasan ledger akun.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-xs bg-slate-900 px-2.5 py-1 text-xs font-mono font-bold text-amber-400 border border-slate-800">
                    ENV: {{ app()->environment() }}
                </span>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-2xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                    Daftar Mitra Toko Grosir
                </h2>
                <span class="text-xs font-mono text-slate-500">Effective-Dated Interval</span>
            </div>

            <div class="overflow-x-auto rounded-md border border-slate-200 dark:border-slate-800">
                <table class="w-full min-w-[40rem] text-left text-sm">
                    <thead class="bg-slate-900 text-xs font-bold uppercase tracking-wider text-slate-300">
                        <tr>
                            <th class="px-4 py-3">Nama Toko Grosir</th>
                            <th class="px-4 py-3">Penanggung Jawab / Kontak</th>
                            <th class="px-4 py-3">Tarif Komisi (%)</th>
                            <th class="px-4 py-3">Status Mitra</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                        <tr>
                            <td class="px-4 py-6" colspan="4">
                                <x-empty-state title="Belum Ada Toko Terdaftar"
                                    description="Administrasi verifikasi toko grosir dan konfigurasi komisi interval akan diimplementasikan pada Modul 5." />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
