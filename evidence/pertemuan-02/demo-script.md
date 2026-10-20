# Demo Script — Pertemuan 02 (Praktikum & Penilaian Dosen)

Panduan langkah demi langkah saat mendemonstrasikan hasil capaian Pertemuan 02 di hadapan dosen pengampu.

---

## Persiapan
1. Jalankan aplikasi di terminal:
   ```bash
   composer run dev
   ```
2. Pastikan database telah di-seed:
   ```bash
   php artisan migrate:fresh --seed
   ```

---

## Langkah Demonstrasi

### Bagian 1: Portal Publik Pembeli (Mobile-First)
1. Buka browser: `http://127.0.0.1:8000/toko/grosir-makmur`
2. Tekan `F12` lalu klik icon **Toggle Device Toolbar** (pilih ukuran ponsel seperti iPhone 14 / 390px - 414px).
3. **Poin yang Dijelaskan:**
   - Tunjukkan tampilan mobile-first dengan kontainer `max-w-md` di tengah.
   - Perlihatkan identitas brand *Industrial Slate & Safety Amber* (topbar gelap dengan garis aksen kuning amber).
   - Tunjukkan komponen `<x-empty-state>` yang rapi menampung informasi bahwa katalog produk akan hadir di Modul 7.

### Bagian 2: Portal Operator Toko (Tenant)
1. Buka `http://127.0.0.1:8000/login` lalu login dengan akun:
   - Email: `tenant@grosir.test`
   - Password: `password`
2. Buka URL: `http://127.0.0.1:8000/tenant/grosir-makmur/dashboard`
3. **Poin yang Dijelaskan:**
   - Tunjukkan layout sidebar khas operasional toko grosir (`bg-slate-900`) dengan badge "MITRA TOKO".
   - Tunjukkan komponen `<x-status-badge status="active">` (hijau industrial dengan border rapi).
   - Tunjukkan tombol `<x-button variant="primary">Tambah Produk</x-button>` yang menggunakan warna kuning amber khas safety/industrial toko grosir.

### Bagian 3: Pembuktian Keamanan Server-Side (403 Forbidden)
1. Masih dalam kondisi login sebagai operator toko di atas, ketik langsung URL admin di browser:
   `http://127.0.0.1:8000/admin/dashboard`
2. **Poin yang Dijelaskan:**
   - Tunjukkan bahwa layar langsung memunculkan **403 Forbidden**.
   - Jelaskan ke dosen: *"Menu tersembunyi bukanlah keamanan; middleware `EnsureUserHasRole` di sisi server menolak request secara langsung jika role pengguna tidak cocok."*

### Bagian 4: Portal Admin Platform
1. Logout atau buka mode incognito, lalu login sebagai admin:
   - Email: `admin@grosir.test`
   - Password: `password`
2. Akses `http://127.0.0.1:8000/admin/dashboard`
3. **Poin yang Dijelaskan:**
   - Tunjukkan header desktop platform dengan badge "ADMIN HUB".
   - Tunjukkan tabel responsif daftar mitra toko yang sudah dilengkapi pembungkus overflow horizontal.

### Bagian 5: Pembuktian Automated Tests (Terminal)
1. Di terminal jalankan:
   ```bash
   php artisan test --compact
   ```
2. **Poin yang Dijelaskan:**
   - Tunjukkan 47 test lolos (hijau), termasuk `ModularFoundationTest`, `ModuleConventionTest` (modul tiruan Probe), dan `UserRoleTest`.
