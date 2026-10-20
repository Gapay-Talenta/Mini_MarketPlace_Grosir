# Known Issues & Technical Debt — Pertemuan 02

Catatan batasan teknis saat ini dan rencana penyelesaiannya pada pertemuan berikutnya:

1. **Parameter Binding URL Tenant/Store Masih Berupa String Placeholder:**
   - Parameter `{store}` pada customer dan `{tenant}` pada tenant saat ini masih diterima sebagai string biasa (`function (string $tenant)`).
   - *Rencana Modul 4:* Model Eloquent `Tenant` / `Store` akan dibuat dan dihubungkan via Scoped Route Model Binding (`{tenant:slug}`).

2. **Skema Role Interim pada Tabel `users`:**
   - Saat ini role disimpan sebagai kolom sederhana `users.role` dan `users.status`.
   - *Rencana Modul 4 & 5:* Hubungan role akan diformalkan ke relasi keanggotaan tenant (*tenant membership*) dan skema otorisasi berbasis Gate & Policy Laravel penuh.

3. **Komponen Livewire Modul Masih Bersifat Probe (Mock):**
   - Komponen Livewire fitur nyata di dalam masing-masing modul baru akan diisi mulai Modul 7 (Katalog) dan Modul 12 (Fulfillment). Modul tiruan `Probe` telah membuktikan bahwa auto-discovery Livewire component bekerja.
