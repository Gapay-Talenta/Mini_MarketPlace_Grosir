# Status Implementasi — Mini MarketPlace Grosir

Dokumen pelacak progres mingguan kumulatif pengembangan aplikasi **Mini MarketPlace Grosir** (_modular monolith_) selama 14 pertemuan.

> **Sumber Kebenaran:**
>
> - Produk & Keputusan Bisnis: `docs/draft_rincian_project_laravel.md` (LOCKED)
> - Skema Database (26 tabel): `docs/schema.dbml` & `docs/ERD_Mini_Marketplace_Grosir.svg`
> - Panduan Arsitektur & Aturan Main: `docs/direksi_pengerjaan.md`

---

## 1. Ringkasan Eksekutif

| Komponen                        | Status Saat Ini                                                        |
| ------------------------------- | ---------------------------------------------------------------------- |
| **Fase**                        | Fase 1 — Fondasi Teknis                                                |
| **Pertemuan Terakhir Selesai**  | **Pertemuan 01** (Setup Toolchain, Laravel 13, MariaDB, Redis, Reverb) |
| **Tag Git Terakhir**            | `pertemuan-01`                                                         |
| **Quality Gate**                | **PASS** (31 test passing, Pint clean, Vite build clean)               |
| **Database Dev / Test**         | `project_sem3` / `project_sem3_test` (MariaDB 11.4+)                   |
| **Cache, Queue, Session, Cart** | Redis 7.x (`127.0.0.1:6379`)                                           |
| **Broadcasting / Realtime**     | Laravel Reverb (`0.0.0.0:8080`)                                        |

---

## 2. Matriks Status 14 Pertemuan

|   Mg   | Topik / Fokus                                               | Target Output Utama                                                                                                                    | Quality Gate / Verifikasi                                             |     Status      |
| :----: | ----------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------- | :-------------: |
| **01** | **Setup toolchain, Laravel 13, Livewire 4, MariaDB, Redis** | Repo bersih, health check, Reverb terpasang & terhubung, DB dev & test terpisah                                                        | `php artisan test` (31 passed), Pint PASS, `npm run build` PASS       | ✅ **SELESAI**  |
| **02** | Modular monolith, auth 3 konteks, layout                    | Struktur 6 modul, route group `PortalRoutes`, registrasi + login customer (name, email, password, phone), layout customer/tenant/admin | Route list valid, auth gate test, layout render test                  | ⏳ _Berikutnya_ |
| **03** | Migrasi baseline + tabel ledger                             | Tabel inti (26 tabel) + 5 akun COA + `ledger_entries` + `balance_projections`                                                          | `migrate:fresh` sukses, foreign key integrity                         |   ⬜ Rencana    |
| **04** | Isolasi multi-tenant                                        | `TenantContext`, `BelongsToTenant` scope, policy tenant, composite FK enforcement                                                      | Matrix allow/deny tenant isolation test                               |   ⬜ Rencana    |
| **05** | Admin hub + admin toko                                      | CRUD toko, role `store_admins`, komisi effective-dated, rekening toko terenkripsi, tarif ongkir/kg, audit log                          | Commission overlap guard test, encrypted bank test                    |   ⬜ Rencana    |
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
| 1   | Isolasi tenant (toko A tidak bisa akses resource toko B)                            |       Mg 4       |   ⬜   |
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
| 14  | Auth gate (guest ditolak add-to-cart/checkout/lihat order; non-admin ditolak panel) |       Mg 2       |   ⬜   |
