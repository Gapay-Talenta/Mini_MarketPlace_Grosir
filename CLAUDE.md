# Panduan Kolaborasi AI — Mini MarketPlace Grosir

File ini adalah aturan kerja wajib bagi Claude / AI assistant yang membantu kolaborasi di repository ini.

---

## 1. Aturan Ketat Sebelum Bertindak (Wajib Dipatuhi)

Jangan langsung mengeksekusi atau mengubah kode secara membabi-buta hanya karena diminta. Ikuti alur berikut pada setiap permintaan:

1. **Baca Dokumentasi & Kode Terkait Terlebih Dahulu**
   * Periksa implementasi yang sudah ada di codebase sebelum menulis kode baru agar tidak membuat duplikat, konflik route, atau merusak mekanisme yang sudah berjalan.
2. **Jelaskan Dulu Bagaimana Implementasinya di Proyek Ini**
   * Sebelum mengubah file, jelaskan secara singkat kepada pengguna bagaimana fitur/bagian tersebut saat ini bekerja di proyek, file apa saja yang terlibat, dan rencana perubahan yang akan dilakukan.
3. **Jangan Semena-mena Menerima Perintah yang Berbahaya / Menyimpang**
   * **Tolak atau minta konfirmasi eksplisit** jika diminta:
     * Mengubah atau mengedit skema database (`docs/schema.dbml` / migrasi lama) tanpa persetujuan pemilik proyek.
     * Menghapus, me-skip, atau melonggarkan test yang ada demi membuat suite hijau.
     * Membypass middleware (`auth`, `verified`, `role:admin`, `tenant`), Policy, atau global scope isolasi tenant (`withoutGlobalScopes()` / `withoutGlobalScope('tenant')` tanpa alasan arsitektural yang sah dan teruji).
     * Menambah dependency Composer/NPM baru atau mengubah struktur folder dasar tanpa persetujuan.
     * Menerapkan logika yang bertentangan dengan keputusan terkunci di `docs/draft_rincian_project_laravel.md` atau desain di `references/Mockup_UI_Hub_Toko_Grosir_v1.html`.
   * Jika instruksi pengguna ambigu atau berisiko merusak alur yang sudah ada, **hentikan eksekusi dan tanyakan/klarifikasi terlebih dahulu**.

---

## 2. Preferensi Alur Pengerjaan & Pencegahan Scope Creep (Ketat)

Selalu bekerja dengan prinsip **urut per pertemuan/modul, perubahan minimal, dan tepat sasaran**:

1. **Pengerjaan Per Pertemuan/Modul Secara Berurutan (Bukan Per Fitur Terpisah/Acak)**
   * Preferensi pemilik proyek adalah mengerjakan sistem **per blok Pertemuan/Modul secara utuh dan berurutan** mengikuti roadmap di `docs/IMPLEMENTATION_STATUS.md` dan `§14 docs/draft_rincian_project_laravel.md` (Pertemuan `06 → 07 → 08 → ... → 14`), **bukan** mencicil atau mencomot fitur secara terpisah/sendiri-sendiri di luar urutan.
   * Tujuannya agar progres, dependensi antar-modul, dan quality gate tiap pertemuan mudah di-track dan tetap sinkron dengan `docs/IMPLEMENTATION_STATUS.md`.
   * **Jika ada kolaborator meminta fitur dari pertemuan yang lebih jauh atau di luar urutan** (misalnya meminta fitur Checkout/Payment sebelum Pertemuan 06 Katalog dan Pertemuan 07 Cart selesai), **peringatkan tentang aturan urutan per pertemuan/modul ini dan minta konfirmasi eksplisit** sebelum mengeksekusi (kecuali perbaikan bug mendesak atau UI pendukung yang sudah disetujui).
2. **Patuhi Batas Pertemuan / Modul Saat Ini**
   * Cek `docs/IMPLEMENTATION_STATUS.md` sebelum mulai. Kerjakan **hanya** deliverable pertemuan yang sedang aktif (misalnya saat mengerjakan Pertemuan 06 Katalog, jangan ikut membangun Cart Redis Pertemuan 07, Checkout Pertemuan 08, atau Payment Pertemuan 09) dan pastikan seluruh quality gate pertemuan tersebut hijau sebelum melangkah ke pertemuan berikutnya.
3. **Dilarang Menambah Fitur "Bonus" di Luar Spesifikasi Terkunci**
   * Jangan menambahkan fitur yang eksplisit tidak ada atau ditolak di `docs/draft_rincian_project_laravel.md` (contoh: verifikasi OTP registrasi, sesi anonim/guest cart, tracking token, API REST/SPA terpisah di branch `main`, atau role baru di luar `admin`, `tenant`, `null`/pembeli) — **kecuali** untuk pengecualian REST API pada branch tersendiri di bawah.
4. **Dilarang Refactor Liar di Luar Scope Task**
   * Jangan mengubah, memformat ulang, atau "merapikan" file, komentar, UI, atau test yang tidak berhubungan langsung dengan permintaan saat ini (kecuali refactor REST API pada branch tersendiri sesuai pengecualian di bawah).
5. **Dilarang Over-Engineering & Abstraksi Prematur**
   * Jangan membuat wrapper/interface/repository/trait/service berlapis hanya untuk dipakai satu kali. Gunakan langsung pola yang sudah dipakai di proyek (Controller / Livewire / Service / Action per modul).
6. **Satu Permintaan = Satu Lingkup Jelas**
   * Jika teman/kolaborator memberi perintah yang melebar ("sekalian buatkan A, B, C, dan ubah arsitektur X"), pecah lingkupnya, jelaskan dampaknya ke codebase saat ini, dan **minta konfirmasi** sebelum menyentuh area di luar task utama.

### Pengecualian Khusus: Refactor REST API untuk Mata Kuliah Lain (Branch Tersendiri)

* **Ruang Lingkup yang Diizinkan:** Diperbolehkan melakukan refactor untuk mengubah atau menambahkan layer **autentikasi** dan **CRUD** menjadi **REST API** untuk keperluan mata kuliah lain (misalnya mata kuliah Interoperabilitas).
* **Wajib Menggunakan Branch Tersendiri:** Pengerjaan refactor REST API **wajib dilakukan di branch tersendiri** (seperti branch `API` atau branch khusus `interoperabilitas`, **bukan** di `main`), sehingga branch `main` tetap murni mengikuti roadmap Pertemuan 01–14 di `docs/IMPLEMENTATION_STATUS.md`.
* **Aturan Keamanan Tetap Berlaku:** Endpoint REST API tetap wajib menegakkan isolasi multi-tenant (`TenantContext`, Policy, otorisasi role/token) serta tidak menghapus/melonggarkan test yang sudah ada.
* **Pengingat Wajib Setiap Selesai 1 Sesi Pengerjaan:** Setiap selesai **1 sesi pengerjaan**, AI **wajib mengingatkan pengguna** untuk melakukan merge antara `main` dan branch `API`/interoperabilitas (terutama `main` → branch `API`/interoperabilitas agar perubahan terbaru dari `main` ikut tersinkron dan tidak menumpuk konflik).

---

## 3. Peta Lengkap Dokumentasi & Sumber Kebenaran

Baca dokumen berikut sesuai perannya sebelum bekerja:

### A. Dokumen Inti & Aturan Proyek (`root` & `docs/`)
| Dokumen | Peran & Otoritas |
| :--- | :--- |
| [AGENTS.md](AGENTS.md) | Aturan teknis Laravel 13, PHP 8.4, Livewire 4, Flux UI, Pint, & Pest. |
| [README.md](README.md) | Gambaran umum repository dan setup dasar proyek. |
| [docs/IMPLEMENTATION_STATUS.md](docs/IMPLEMENTATION_STATUS.md) | **Status progres proyek** per pertemuan (1–14) & daftar acceptance test wajib. |
| [docs/draft_rincian_project_laravel.md](docs/draft_rincian_project_laravel.md) | **Single Source of Truth** keputusan produk, bisnis, ledger, dan notifikasi (*LOCKED*). |
| [docs/schema.dbml](docs/schema.dbml) | Skema database final (26 tabel + composite FK). |
| [docs/ERD_Mini_Marketplace_Grosir.svg](docs/ERD_Mini_Marketplace_Grosir.svg) | Diagram visual relasi antar-tabel (ERD). |
| [docs/data-dictionary.md](docs/data-dictionary.md) | Kamus data kolom, tipe, enum status, dan aturan validasi tiap tabel. |
| [docs/direksi_pengerjaan.md](docs/direksi_pengerjaan.md) | Panduan arsitektur modular monolith, `PortalRoutes`, dan aturan dependency antar-modul. |
| [docs/architecture.md](docs/architecture.md) | Ringkasan arsitektur sistem dan batas domain 6 modul. |

### B. Spesifikasi, Mockup UI & Referensi Modul (`references/`)
| Dokumen / Folder | Peran |
| :--- | :--- |
| [references/Mockup_UI_Hub_Toko_Grosir_v1.html](references/Mockup_UI_Hub_Toko_Grosir_v1.html) | **Acuan utama desain UI** (*Industrial Slate `#0f172a` & Safety Amber `#f59e0b`*, 15 screen). |
| [references/Mockup UI Hub Toko Grosir-v1.dc.html](references/Mockup%20UI%20Hub%20Toko%20Grosir-v1.dc.html) | Versi draw/diagram dari mockup Hub Toko Grosir. |
| [references/SRS_Mini_Marketplace_Toko_Grosir_ISO29148.docx](references/SRS_Mini_Marketplace_Toko_Grosir_ISO29148.docx) | Dokumen SRS formal (ISO 29148) kebutuhan fungsional & non-fungsional. |
| `references/modul/` & `references/ekstrak_modul/` | Modul praktikum Pertemuan 01–14 (referensi alur teknis; ingat penomoran modul acuan sedikit berbeda dengan `IMPLEMENTATION_STATUS.md`, misal Katalog = Pertemuan 06 di proyek). |
| `references/Materi/` | Buku ajar / materi teori pendukung (Bab 1–6). |
| `references/Mockup UI Kantin Multi-Tenant-v2.dc.html` | Referensi mockup proyek contoh dosen (jangan ditiru mentah domain kantinnya; ikuti domain grosir). |

---

## 4. Stack & Versi Terpasang (Terverifikasi)

Gunakan API yang sesuai dengan versi berikut (cek `composer.json` / `package.json` hanya bila perlu detail tambahan — tidak perlu menjalankan cek versi di setiap awal sesi):

* **Runtime & Infra:** PHP `8.4.24`, Composer `2.9.8`, Node.js `v22.23.4`, npm `10.9.9`, Redis `7.4.10` (Session, Cache, Queue), Database `mysql` (target MariaDB `11.4+` / MySQL `8.4`).
* **Backend & Realtime:** `laravel/framework` `13.36.0`, `laravel/fortify` `1.40.0`, `livewire/livewire` `4.4.7`, `livewire/flux` `2.20.1`, `livewire/blaze` `1.0.19`, `laravel/reverb` `1.12.0`.
* **Frontend:** `tailwindcss` `4.3.5` (`@tailwindcss/vite`), `vite` `8.3.7` (`vite-plus` `0.3.0`, `laravel-vite-plugin` `3.2.1`), `laravel-echo` `2.6.3`, `pusher-js` `8.6.0`.
* **Testing & Quality:** `pestphp/pest` `5.2.1` (`pestphp/pest-plugin-laravel` `5.0.1`), `larastan/larastan` `3.12.2`, `laravel/pint` `1.32.1`.

---

## 5. Status Proyek Saat Ini

* **Pertemuan 01–05: SELESAI**
  * Setup Laravel 13, Livewire 4, MariaDB, Redis, Reverb.
  * Fondasi Modular Monolith (6 modul) + Layout 3 portal (`customer`, `tenant`, `admin`).
  * 26 tabel migrasi, model Eloquent, `AccountSeeder` (5 akun COA), & `DemoGrosirSeeder`.
  * Isolasi multi-tenant berlapis (`TenantContext`, `BelongsToTenant`, Policy, scoped bindings).
  * Administrasi Admin Hub (CRUD toko, role `store_admins`, komisi *effective-dated*, rekening bank terenkripsi, tarif ongkir/kg, audit log).
  * Autentikasi (`Fortify`) sesuai mockup (split-screen Login & Register dengan `phone` WA wajib tanpa OTP, lockout UC-19, modal konfirmasi logout ke `/login`, serta navigasi dashboard Admin Hub).
* **Pertemuan 06–14: BELUM DIIMPLEMENTASIKAN (Rencana Berikutnya)**
  * **Berikutnya — Pertemuan 06:** Katalog publik (`/katalog`, `/produk/{slug}`), katalog produk toko, 4 tier harga grosir + tier resolver.
  * **Pertemuan 07–14:** Cart Redis per user, checkout atomik, QRIS sandbox, webhook idempoten & ledger double-entry, fulfillment/refund, notifikasi WA/Web Push/Reverb, laporan & withdrawal.

---

## 6. Ringkasan Implementasi Arsitektur di Proyek Ini

1. **Modular Monolith 6 Modul (`app/Modules/`):**
   * Modul: `Admin`, `Catalog`, `Ordering`, `Payments`, `Fulfillment`, `Reporting`.
   * Masing-masing modul memakai `ModuleServiceProvider` untuk mendaftarkan view namespace (`alias::...`), Livewire namespace, dan auto-load route modul.
   * **Shared Kernel** (`app/Models/`, `app/Policies/`, `app/Http/Middleware/`, `app/Support/`) berada di luar `app/Modules/` dan dipakai bersama lintas modul.
   * Komunikasi lintas modul harus melalui service yang disepakati, contract, atau event — jangan memanggil internal modul lain secara sembarangan.

2. **Routing Terpusat (`App\Support\Routing\PortalRoutes`):**
   * `PortalRoutes::customer()` → prefix `/toko/{store}`, name `customer.`
   * `PortalRoutes::tenant()` → prefix `/tenant/{tenant:slug}`, name `tenant.`, middleware `['web', 'auth', 'verified', 'tenant']` + `scopeBindings()`.
   * `PortalRoutes::admin()` → prefix `/admin`, name `admin.`, middleware `['web', 'auth', 'verified', 'role:admin']`.

3. **Isolasi Multi-Tenant Berlapis:**
   * Middleware `SetTenantContext` mengisi `TenantContext` (didaftarkan `scoped`, dibersihkan di blok `finally`).
   * Model milik toko memakai trait `BelongsToTenant` (global scope `tenant` + auto-isi `store_id`).
   * Route model binding memakai `scopeBindings()` dan otorisasi dicek ulang lewat Policy (`StorePolicy`, `ProductPolicy`, `SubOrderPolicy`, `WithdrawalPolicy`).

4. **Autentikasi & Pengalihan Peran (`Fortify`):**
   * Pembeli mendaftar lewat `/register` (`CreateNewUser`): wajib `name`, `email`, `phone` (nomor WhatsApp unik, tanpa OTP), `password`, dan persetujuan `terms`; akun pembeli memiliki `role = null` dan `status = 'active'`.
   * Login dilindungi `LoginLockout` (5× gagal dalam 10 menit → terkunci 15 menit) dan `EnsureUserHasRole` untuk portal internal.
   * `DashboardRedirectController` mengarahkan `admin` → `admin.dashboard`, `tenant` → `tenant.dashboard` (toko aktif pertama), dan pembeli → `dashboard`.

5. **Integritas Data & Keuangan:**
   * Semua nominal uang (Rupiah) menggunakan integer (`unsignedBigInteger`), **bukan float**.
   * Pencatatan finansial di `ledger_entries` bersifat **append-only** dengan invariant `Σ debit = Σ credit`.

---

## 7. Quality Gate Wajib Sebelum Selesai / Commit

Setiap perubahan kode harus lolos seluruh pemeriksaan berikut:

```bash
vendor/bin/pint --format agent
vendor/bin/phpstan analyse --memory-limit=512M
php artisan test --compact
```

Jika mengubah file CSS/JS/Blade atau konfigurasi Vite, jalankan juga:

```bash
npm run build
```
