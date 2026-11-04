# Status Implementasi — Mini MarketPlace Grosir

Dokumen pelacak progres mingguan kumulatif pengembangan aplikasi **Mini MarketPlace Grosir** (_modular monolith_) selama 14 pertemuan.

> **Sumber Kebenaran:**
>
> - Produk & Keputusan Bisnis: `docs/draft_rincian_project_laravel.md` (LOCKED)
> - Skema Database (26 tabel): `docs/schema.dbml` & `docs/ERD_Mini_Marketplace_Grosir.svg`
> - Panduan Arsitektur & Aturan Main: `docs/direksi_pengerjaan.md`

---

## 1. Ringkasan Eksekutif

| Komponen                        | Status Saat Ini                                                                                         |
| ------------------------------- | ------------------------------------------------------------------------------------------------------- |
| **Fase**                        | Fase 1 — Keamanan Data & Isolasi Multi-Tenant                                                           |
| **Pertemuan Terakhir Selesai**  | **Pertemuan 04** (Isolasi Multi-Tenant Berlapis: Context, Scope, Policy, Scoped Binding & Controlled Bypass) |
| **Tag Git Terakhir**            | `pertemuan-04`                                                                                          |
| **Quality Gate**                | **PASS** (65 test passing, Pint clean, PHPStan clean, Vite clean)                                       |
| **Database Dev / Test**         | `project_sem3` / `project_sem3_test` (MariaDB 11.4+)                                                    |
| **Cache, Queue, Session, Cart** | Redis 7.x (`127.0.0.1:6379`)                                                                            |
| **Broadcasting / Realtime**     | Laravel Reverb (`0.0.0.0:8080`)                                                                         |

---

## 2. Matriks Status 14 Pertemuan

|   Mg   | Topik / Fokus                                               | Target Output Utama                                                                                                                    | Quality Gate / Verifikasi                                             |     Status      |
| :----: | ----------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------- | :-------------: |
| **01** | **Setup toolchain, Laravel 13, Livewire 4, MariaDB, Redis** | Repo bersih, health check, Reverb terpasang & terhubung, DB dev & test terpisah                                                        | `php artisan test` (31 passed), Pint PASS, `npm run build` PASS       | ✅ **SELESAI**  |
| **02** | **Modular monolith, auth 3 konteks, layout**                | Struktur 6 modul, route group `PortalRoutes`, registrasi + login customer (name, email, password, phone), layout customer/tenant/admin | Route list valid, auth gate test, layout render test (47 passed)      | ✅ **SELESAI**  |
| **03** | **Migrasi baseline + tabel ledger**                         | Tabel inti (26 tabel) + 5 akun COA + `ledger_entries` + `balance_projections` + model & seeder                                         | `migrate:fresh --seed` sukses, FK komposit MariaDB (55 passed)        | ✅ **SELESAI**  |
| **04** | **Isolasi multi-tenant berlapis**                           | `TenantContext`, `BelongsToTenant` scope, policy tenant, scoped binding, controlled bypass & test allow/deny                            | Matrix allow/deny tenant isolation test (65 passed)                   | ✅ **SELESAI**  |
| **05** | Admin hub + admin toko                                      | CRUD toko, role `store_admins`, komisi effective-dated, rekening toko terenkripsi, tarif ongkir/kg, audit log                          | Commission overlap guard test, encrypted bank test                    | ⏳ _Berikutnya_ |
| **06** | Katalog publik + katalog toko + tier harga                  | CRUD produk (kategori, satuan, foto, deskripsi, berat), 1 harga normal + maks 4 tier grosir, stok, katalog publik                      | Tier resolver test, withoutGlobalScopes review                        |   ⬜ Rencana    |
| **07** | Cart multi-toko di Redis (milik user)                       | `CartService` (key terikat `user_id`), revalidasi harga & stok saat buka cart, UI cart Livewire, login gate                            | Cart isolation per user test, stock/price revalidation test           |   ⬜ Rencana    |
| **08** | Checkout atomik multi-toko                                  | Order induk + sub-order, snapshot tier & satuan, ongkir per berat, deduksi stok atomik, idempotency key, riwayat order                 | Atomic checkout test, oversell lock test, idempotency test            |   ⬜ Rencana    |
| **09** | Payment gateway + QRIS sandbox                              | `PaymentGateway` contract, fake + sandbox adapter, halaman QR, contract test, auto-cancel expired                                      | Contract test adapter, auto-cancel scheduler test, stock release test |   ⬜ Rencana    |
| **10** | Webhook idempoten + settlement + ledger                     | Endpoint webhook, verifikasi signature, idempotency `provider_event_id`, posting double-entry 5 akun                                   | Webhook replay test, ledger balance invariant (Σ debit = Σ credit)    |   ⬜ Rencana    |
| **11** | Fulfillment + pengiriman + refund                           | State machine sub-order, alur refund per sub-order, reversal ledger posting, auto-selesai scheduler, hold release                      | Refund state transition test, ledger reversal test                    |   ⬜ Rencana    |
| **12** | Realtime + notifikasi multi-kanal                           | Reverb private channel `user.{id}`, Web Push opt-in, WhatsApp notification via Fonnte + fake adapter                                   | Channel authorization test, WhatsApp job test, push delivery test     |   ⬜ Rencana    |
| **13** | Laporan ledger + withdrawal + export                        | Dashboard laporan, saldo available toko, withdrawal review, export CSV/XLSX/PDF via job queue                                          | Balance projection reconciliation test, signed URL export test        |   ⬜ Rencana    |
| **14** | Hardening, test E2E, deployment staging                     | Full test suite green, E2E flow test, Docker Compose runbook, demo script siap                                                         | 100% acceptance checklist green, production build                     |   ⬜ Rencana    |

---

## 3. Rincian Capaian Pertemuan 01

### A. Toolchain & Runtime Terverifikasi

- **PHP**: 8.4.24 (ekstensi aktif: `pdo_mysql`, `mbstring`, `openssl`, `ctype`, `curl`, `fileinfo`, `xml`, `tokenizer`, `redis`, `bcmath`)
- **Composer**: 2.10.2
- **Node.js / npm**: v23.11.1 / 10.9.2 (Vite bundler berjalan mulus `✓ built in 2.38s`)
- **MariaDB**: 11.4.13
- **Redis**: 7.4.11 (`redis-cli ping` → `PONG`)

### B. Konfigurasi Lingkungan

- Variabel lingkungan disetel di `.env`:
    - `DB_CONNECTION=mysql`
    - `DB_HOST=127.0.0.1` / `DB_PORT=3306`
    - `DB_DATABASE=project_sem3` (development)
    - `SESSION_DRIVER=redis`, `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`
    - `BROADCAST_CONNECTION=reverb`
- Database pengujian terisolasi di `phpunit.xml`:
    - `DB_DATABASE=project_sem3_test`
- Keamanan:
    - File `.env` terverifikasi diabaikan oleh Git (`git check-ignore -v .env`).
    - `.env.example` disinkronkan dengan placeholder aman tanpa membocorkan credential.

### C. Quality Gate

- `php artisan test --compact`: **31 passed (73 assertions)**, 0 failed, 0 skipped.
- `vendor\bin\pint --test`: **PASS** (52 files checked, clean code style).
- `npm run build`: **PASS** (semua bundle manifest, font, CSS, dan JS berhasil dikompilasi).

---

## 4. 14 Acceptance Test Wajib (§12 Draft) — Tracking Gate

| #   | Skenario Uji Wajib                                                                  | Pertemuan Target | Status |
| --- | ----------------------------------------------------------------------------------- | :--------------: | :----: |
| 1   | Isolasi tenant (toko A tidak bisa akses resource toko B)                            |       Mg 4       |   ✅   |
| 2   | Idempotency checkout (retry key sama tidak mendobelkan order)                       |       Mg 8       |   ⬜   |
| 3   | Idempotency webhook (`provider_event_id` sama diputar ulang hanya settle 1x)        |      Mg 10       |   ⬜   |
| 4   | Idempotency ledger (posting key sama di-dedup; append-only)                         |      Mg 10       |   ⬜   |
| 5   | Invariant ledger (Σ debit = Σ credit per ref; rekonsiliasi saldo)                   |      Mg 10       |   ⬜   |
| 6   | Pencegahan oversell (dua checkout paralel stok terakhir, 1 berhasil 1 gagal)        |       Mg 8       |   ⬜   |
| 7   | Release stok saat expired / cancelled                                               |     Mg 8 & 9     |   ⬜   |
| 8   | State machine refund (reject → kembali `shipped`, approve → reversal)               |      Mg 11       |   ⬜   |
| 9   | Otorisasi channel realtime (user A ditolak dengar channel user B)                   |      Mg 12       |   ⬜   |
| 10  | Guard komisi effective-dated (overlap ditolak, snapshot rate konsisten)             |       Mg 5       |   ⬜   |
| 11  | Resolver tier harga grosir (qty cocok tier, snapshot harga & satuan aman)           |       Mg 6       |   ⬜   |
| 12  | Web Push subscription lifecycle (bind `user_id`, cleanup saat order tuntas)         |      Mg 12       |   ⬜   |
| 13  | Notifikasi WhatsApp via Fonnte (target `users.phone`, fail-safe order)              |      Mg 12       |   ⬜   |
| 14  | Auth gate (guest ditolak add-to-cart/checkout/lihat order; non-admin ditolak panel) |       Mg 2       |   ✅   |

---

## 5. Rincian Capaian Pertemuan 03

### A. Skema 26 Tabel Baseline Terintegrasi
- Berkas migrasi 7 grup (`database/migrations/2026_10_21_000101_*` s/d `*000107_*`).
- Penegakan integritas data via composite foreign key `(store_id, resource_id)`.
- Akurasi nominal menggunakan `unsignedBigInteger` (integer Rupiah).
- Presisi waktu transaksi menggunakan `timestamps(6)` (microsecond precision).
- Enkripsi native untuk nomor rekening bank toko (`encrypted` cast).

### B. 26 Model Eloquent Lengkap & Type-Safe
- Seluruh 26 model dibuat di `app/Models/` dengan casting yang presisi dan relasi antar agregat.
- PHPStan analyse lulus dengan **0 errors**.

### C. Seeder Deterministik & Idempoten
- `AccountSeeder`: Mengisi 5 akun baku Chart of Accounts (1000, 2000, 2100, 2110, 3000).
- `DemoGrosirSeeder`: Membangun 2 toko demo grosir (`Sembako Berkah Grosir` dan `Sumber Plastik & Packing Hub`) beserta produk, tier harga, komisi, tarif ongkir per kg, rekening bank, dan proyeksi saldo.
- Idempoten penuh via `updateOrCreate` dan `firstOrNew`.

### D. Quality Gate & Evidence
- Automated test constraint MariaDB: `tests/Feature/DatabaseConstraintTest.php` (8 passed).
- Total test suite: **55 passed (156 assertions)**.
- Kamus data lengkap: `docs/data-dictionary.md`.
- Evidence pengujian, query plan EXPLAIN, dan `schema-dump.sql` tersimpan di `evidence/pertemuan-03/`.

---

## 6. Rincian Capaian Pertemuan 04

### A. Lapisan Keamanan & Isolasi Multi-Tenant Berlapis
- **`TenantContext` (`App\Support\Tenancy\TenantContext`):** Didaftarkan sebagai container binding `scoped()` di `AppServiceProvider`. Mengelola state `Store` aktif dengan prinsip *fail-fast*, otomatis di-reset antar siklus request HTTP dan job queue (mencegah kebocoran memori pada long-running worker).
- **Middleware Resolver `SetTenantContext`:** Mengikat parameter `{tenant:slug}`, memvalidasi status toko `$store->isActive()` (403 jika non-aktif), memverifikasi keanggotaan pengguna di `store_admins` (403 jika bukan admin toko), serta membersihkan context pada blok `finally`.
- **Trait `BelongsToTenant` (`App\Models\Concerns\BelongsToTenant`):** Menerapkan global scope `WHERE store_id = ?` berkualifikasi tabel (`qualifyColumn`) dan auto-fill `store_id` saat create. Dipasang pada 11 model *store-owned*.
- **Scoped Route Binding:** Rute portal tenant di `PortalRoutes::tenant()` mengaktifkan `scopeBindings()`, memastikan child model (seperti `/products/{product}`) ditolak 404 jika bukan milik toko pada URL.
- **Eloquent Policies:** `ProductPolicy`, `SubOrderPolicy`, dan `WithdrawalPolicy` memvalidasi kepemilikan store pada level ability aksi pengguna.
- **Pola Background Queue Job (`CountStoreProducts`):** Menerapkan pola deserialisasi primitif `$storeId`, pengisian context di method `handle()`, dan sanitasi mutlak di blok `finally`.

### B. Controlled Bypass untuk Katalog Publik Pembeli
- **`PublicCatalogQuery` (`App\Modules\Catalog\Services\PublicCatalogQuery`):** Satu-satunya jalur resmi penerobosan scope (`withoutGlobalScope('tenant')`) dengan filter pengganti ketat: hanya menampilkan produk berstatus `'aktif'` dari toko yang berstatus `'aktif'`.
- Audit keamanan: Penerobosan scope diisolasi dan dilarang muncul di controller/Livewire.

### C. Quality Gate & Acceptance Test
- **Acceptance Test Wajib #1 (§12 Draft) TERPENUHI:** Test matriks allow/deny Toko A vs Toko B di `tests/Feature/TenantIsolationTest.php` (10 test passing).
- Regresi routing dan modular probe terverifikasi di `ModularFoundationTest.php` dan `ModuleConventionTest.php`.
- **Total Test Suite:** **65 passed (188 assertions)**, 0 failed, 0 skipped.
- **Pint:** PASS (clean code style).
- **PHPStan:** PASS (Level 5, 0 errors).
- **Vite Build:** PASS (`✓ built in 4.03s`).
- Evidence pengujian tersimpan di `evidence/pertemuan-04/`.
