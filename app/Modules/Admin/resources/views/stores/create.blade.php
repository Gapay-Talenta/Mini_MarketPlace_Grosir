<x-layouts.admin title="Daftarkan Toko Grosir">
    <div class="mb-6">
        <a href="{{ route('admin.stores.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
            &larr; Kembali ke Daftar Toko
        </a>
        <h1 class="mt-2 text-2xl font-black text-slate-900 dark:text-slate-100">Daftarkan Toko Grosir Baru</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Lengkapi identitas toko, akun penanggung jawab (owner), dan rekening bank pencairan.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-md bg-rose-100 border border-rose-300 p-4 text-sm text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800">
            <ul class="list-disc ps-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.stores.store') }}" class="grid max-w-4xl grid-cols-1 gap-6 md:grid-cols-2">
        @csrf

        <!-- Kolom Kiri: Identitas Toko & Tarif -->
        <fieldset class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <legend class="px-2 text-xs font-bold uppercase tracking-wider text-slate-500">Identitas Toko &amp; Tarif</legend>

            <x-input name="name" label="Nama Toko Grosir" placeholder="Contoh: Sembako Berkah Grosir" value="{{ old('name') }}" required />
            <x-input name="slug" label="Slug URL" placeholder="contoh: sembako-berkah" value="{{ old('slug') }}" required />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-input name="commission_rate" label="Komisi Awal (%)" type="number" step="0.01" min="0" max="100" value="{{ old('commission_rate', '10.00') }}" required />
                <x-input name="rate_per_kg" label="Ongkir / kg (Rp)" type="number" min="0" step="500" value="{{ old('rate_per_kg', '8000') }}" required />
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="description" class="text-sm font-semibold text-slate-800 dark:text-slate-200">Deskripsi Toko</label>
                <textarea id="description" name="description" rows="3"
                    class="rounded-md border border-slate-300 bg-white p-3 text-sm text-slate-900 placeholder-slate-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                    placeholder="Keterangan singkat komoditas atau lokasi toko...">{{ old('description') }}</textarea>
            </div>
        </fieldset>

        <!-- Kolom Kanan: Penanggung Jawab & Rekening Bank -->
        <div class="space-y-6">
            <fieldset class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <legend class="px-2 text-xs font-bold uppercase tracking-wider text-slate-500">Penanggung Jawab (Owner)</legend>

                <x-input name="pic_name" label="Nama Penanggung Jawab" placeholder="Nama lengkap owner toko" value="{{ old('pic_name') }}" required />
                <x-input name="pic_email" label="Surel (Menerima Tautan Atur Kata Sandi)" type="email" placeholder="owner@tokogrosir.test" value="{{ old('pic_email') }}" required />
            </fieldset>

            <fieldset class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <legend class="px-2 text-xs font-bold uppercase tracking-wider text-slate-500">Rekening Bank Pencairan</legend>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-input name="bank_name" label="Nama Bank" placeholder="BCA / BRI / Mandiri" value="{{ old('bank_name') }}" required />
                    <x-input name="account_holder_name" label="Nama Pemilik Rekening" value="{{ old('account_holder_name') }}" required />
                </div>
                <x-input name="account_number" label="Nomor Rekening" inputmode="numeric" placeholder="6 - 20 digit angka" required />
            </fieldset>
        </div>

        <div class="md:col-span-2 flex items-center justify-end gap-3">
            <a href="{{ route('admin.stores.index') }}">
                <x-button variant="secondary">Batal</x-button>
            </a>
            <x-button type="submit" variant="primary">Simpan &amp; Kirim Kredensial Awal</x-button>
        </div>
    </form>
</x-layouts.admin>
