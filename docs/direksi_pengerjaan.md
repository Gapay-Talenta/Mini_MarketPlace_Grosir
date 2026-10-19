# Direksi Pengerjaan (utamanya untuk AI, agar konsisten karena memuat logika domain/bisnis yang lebih lengkap daripada sebatas spesifikasi) — Mini MarketPlace Grosir

> Dokumen CARA MENGERJAKAN (bukan produk). Rincian produk ada di
> `draft_rincian_project_laravel.md` (keputusan, LOCKED) + `schema.dbml` (tabel).
> Urutan baca: draft → schema.dbml → dokumen ini.

---

## 1. Sumber kebenaran & aturan main

1. **`draft_rincian_project_laravel.md`** = satu-satunya sumber kebenaran keputusan produk (fitur, aturan bisnis, ledger, status, test wajib, timeline).
2. **`schema.dbml`** = skema database final (26 tabel). Migrasi Laravel minggu 3 mengikuti skrip ini, termasuk composite FK `(store_id, resource_id)`. Pembeli = user login (`orders.user_id`), tanpa sesi anonim/tracking token.
3. **Jangan ubah draft/schema tanpa konfirmasi eksplisit** dari pemilik project.
4. Setiap minggu mengikuti **§14 draft** (fokus + output); **§12 draft** (test wajib) = quality gate — semua hijau sebelum lanjut.
5. Stack: Laravel 13 + Livewire 4 + Reverb + MariaDB + Redis + scheduler. Notifikasi: Web Push (VAPID) + WhatsApp via Fonnte (§10 draft).

---

## 2. Arsitektur: ikuti repo referensi dosen (BLUEPRINT, bukan fork)

Referensi: `github.com/devitsuwardiyanto/kantin-multi-tenant` (final, 14/14 LULUS, 304 test).
Kita **menulis kode fresh** untuk domain grosir, tapi **meniru arsitekturnya** — tidak fork.

Pola inti yang ditiru:

1. **ModuleServiceProvider abstrak** (`app/Modules/ModuleServiceProvider.php`): tiap modul
   extends-nya, set `moduleAlias()`, dan otomatis dapat:
    - `loadViewsFrom(resources/views, alias)` → view `catalog::...`
    - `Livewire::addNamespace(alias, views/livewire)` → `<livewire:catalog::...>`
    - auto-load semua `routes/*.php` milik modul via glob.
2. **Vertical slice per modul**: file fitur milik modul ada di dalam folder modul.
3. **Shared kernel di LUAR modul**: model Eloquent (satu database dipakai lintas modul),
   policy, middleware, dan kelas `Support\*` — semua di `app/` bawah, TIDAK di dalam modul.
4. **PortalRoutes terpusat** (detail §4 di bawah): middleware portal didefinisikan SEKALI.
5. **Fail-fast + kebersihan lifecycle**: TenantContext scoped (bukan singleton), dibersihkan
   di `finally` middleware; fail cepat kalau context belum diisi. Hal yang sama berlaku untuk
   job/listener yang berjalan atas nama toko: isi context sendiri dan bersihkan di `finally`
   (queue worker berumur panjang, tidak ada request yang membersihkan).

---

## 3. Struktur modul — 6 modul

Per keputusan pemilik project: **6 modul mengikuti repo dosen** (panel admin toko TIDAK
menjadi modul terpisah; UI tenant milik masing-masing modul fitur, mis. `catalog::menu-manager`).

| Modul         | Alias         | Tanggung jawab (domain grosir)                                                                                                                       |
| ------------- | ------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Admin`       | `admin`       | CRUD toko, role, komisi effective-dated, rekening toko (encrypted+last4+verified), tarif ongkir per kg, audit log, review withdrawal                 |
| `Catalog`     | `catalog`     | CRUD produk (kategori, satuan jual, foto, deskripsi, berat, stok), tier harga (maks 4, resolver), katalog publik                                     |
| `Ordering`    | `ordering`    | Cart Redis per user + revalidasi, checkout atomik + snapshot + idempotency, status pesanan, riwayat order user, auto-cancel unpaid                   |
| `Payments`    | `payments`    | PaymentGateway contract + fake/sandbox adapter, webhook + settlement, ledger posting (semua event), withdrawal hold/payout, Midtrans Refund API call |
| `Fulfillment` | `fulfillment` | Pengiriman + estimasi, state machine refund (workflow + bukti), hold release, auto-selesai (scheduler)                                               |
| `Reporting`   | `reporting`   | Laporan dari ledger, rekonsiliasi, export CSV/XLSX/PDF via job (`report_exports`), dashboard                                                         |

Template folder per modul (meniru repo dosen — buat hanya yang dipakai):

```
app/Modules/{Modul}/
├── {Modul}ServiceProvider.php      # extends ModuleServiceProvider, moduleAlias()
├── routes/{portal}.php             # wajib dibungkus PortalRoutes::{portal}()
├── Http/Controllers/  Http/Requests/
├── Services/                       # logika domain (SettlePayment, CheckoutService, ...)
├── Actions/  Data/                 # action terisolasi + DTO (CheckoutDetails, ...)
├── Jobs/  Events/  Exceptions/  Contracts/  Console/  Realtime/
└── resources/views/{portal}/  resources/views/livewire/
```

**Aturan dependency antar modul (ketat):**

- Modul TIDAK boleh mengakses controller/model internal modul lain secara sembarangan.
- Lintas modul = lewat **service yang disepakati / event / contract**. Contoh:
  Fulfillment minta reversal ledger → panggil service ledger milik Payments (atau event).
- Model Eloquent boleh dipakai lintas modul (shared kernel) — tapi tulis query lewat
  service milik modul pemilik domain bila logikanya kompleks.

---

## 4. Skema route — PortalRoutes (adaptasi domain grosir)

Buat `app/Support/Routing/PortalRoutes.php` meniru dosen, dengan adaptasi:

```php
PortalRoutes::web($routes);        // publik TANPA prefix: katalog global, webhook
                                   // (signature + limiter per route)
PortalRoutes::customer($routes);   // middleware ['web','auth'] — TAMBAHAN vs repo dosen
                                   // (dosen anonim; kita pembeli login): cart, checkout,
                                   // order user, refund, push opt-in
PortalRoutes::tenant($routes);     // middleware ['web','auth','verified','tenant']
                                   // prefix 'toko/{store:slug}', name 'tenant.',
                                   // scopeBindings() + SetTenantContext
PortalRoutes::admin($routes);      // middleware ['web','auth','verified','role:admin']
                                   // prefix 'admin', name 'admin.'
```

- `bootstrap/app.php`: `withRouting(then: ...)` memuat `routes/{customer,tenant,admin}.php`
  inti; file route modul menyumbang route fitur lewat `PortalRoutes::{portal}()` yang sama
  (middleware portal tetap terpusat, tidak mungkin terlupa).
- Middleware alias: `role` (EnsureUserHasRole), `tenant` (SetTenantContext — setelah auth,
  cek membership `store_admins`, isi TenantContext, bersihkan di `finally`).
- `SecurityHeaders` global (append), limiter per modul didaftarkan di provider pemiliknya
  (mis. `refund-submit`, `qris-webhook`), CSRF exception `webhooks/*` di provider Payments.
- **Registrasi customer**: name, email, password, **phone (wajib, tanpa OTP)** — phone = target
  notifikasi WhatsApp. `users.role` membedakan `customer` vs `admin_hub`; admin toko tetap
  via membership `store_admins`.
- **Catatan bug scaffold lokal**: `bootstrap/app.php` lokal menunjuk `customers.php`/
  `tenants.php` (jamak) sedangkan disk ada `customer.php`/`tenant.php` (tunggal, kosong) —
  aplikasi error saat boot. Kalau melanjutkan scaffold lokal, perbaiki dulu; kalau mulai
  fresh dari blueprint, tidak relevan.

---

## 5. Shared kernel (di `app/`, luar modul)

| Kelas                                                                        | Peran                                                                                                     |
| ---------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------- |
| `Support/Tenancy/TenantContext.php`                                          | scoped binding, fail-fast, clear di finally — isi = Store aktif                                           |
| `Support/Routing/PortalRoutes.php`                                           | definisi tunggal grup portal (§4, 4 grup: web/customer/tenant/admin)                                      |
| `Support/Notify/WhatsAppGateway.php` (contract, `App\Contracts`)             | kirim WA: `send(phone, message): WhatsAppResult`; adapter `FonnteGateway` + `FakeWhatsAppGateway` (mg 12) |
| `Http/Middleware/{EnsureUserHasRole, SetTenantContext, SecurityHeaders}.php` | role, resolver tenant, header keamanan                                                                    |
| `Models/*`                                                                   | semua model (mapping §6) + `Concerns/BelongsToTenant.php` (trait global scope)                            |
| `Policies/*`                                                                 | kebijakan tenant-scope per resource                                                                       |

Catatan: `Support/Tokens/OpaqueToken.php` dari versi lama dihapus — tidak ada lagi
tracking token/cookie sesi anonim; identitas pembeli = user login (`orders.user_id`).

---

## 6. Mapping model repo dosen → skema kita

| Repo dosen (kantin)                                 | Kita (grosir)                                                                               | Catatan                                                         |
| --------------------------------------------------- | ------------------------------------------------------------------------------------------- | --------------------------------------------------------------- |
| `Tenant`                                            | `stores`                                                                                    | tenant = toko                                                   |
| `UserTenantRole`                                    | `store_admins`                                                                              | membership + role                                               |
| `CommissionScheme`                                  | `commission_rates`                                                                          | effective-dated, interval setengah-terbuka + guard DB           |
| `TenantBankAccount`                                 | `store_bank_accounts`                                                                       | encrypted + last4 + one-primary                                 |
| `TenantOperatingHour`                               | `store_shipping_rates`                                                                      | diganti: tarif ongkir per kg (tidak ada jam buka)               |
| `Menu`                                              | `products` + `product_prices`                                                               | tier harga = tabel terpisah (dosen single price)                |
| `MenuCategory`                                      | kolom `products.category`                                                                   | daftar tetap, tanpa tabel                                       |
| `ModifierGroup/Option`                              | —                                                                                           | tidak ada di grosir                                             |
| `DiningTable`, `TableQrToken`                       | —                                                                                           | QR meja dihapus; entry point = akun pembeli (login)             |
| `CustomerSession`                                   | — (tidak diadopsi)                                                                          | dosen anonim via sesi; kita pembeli login, cart milik `user_id` |
| `Order`/`TenantOrder`/`OrderItem`                   | `orders`/`sub_orders`/`order_items`                                                         | order_items + snapshot satuan/tier; orders.user_id              |
| `Payment`/`PaymentAttempt`/`PaymentEvent`           | `payments`/`payment_attempts`/`payment_events`                                              | log event webhook, dedup `provider_event_id`                    |
| `PaymentAllocation`                                 | —                                                                                           | split implisit di `sub_orders` (granular sejak checkout)        |
| `LedgerEntry`/`PlatformLedgerEntry`/`TenantBalance` | `ledger_entries`/`balance_projections`                                                      | 5 akun, reversal                                                |
| `Withdrawal`                                        | `withdrawals`                                                                               | + hold 2110                                                     |
| `ReportExport`                                      | `report_exports`                                                                            | job ekspor + signed URL                                         |
| `AuditLog`                                          | `audit_logs`                                                                                | polymorphic append-only                                         |
| — (tidak ada)                                       | `refund_requests`/`refund_proofs`, `outbox_events`, `push_subscriptions`, `stock_movements` | fitur grosir tambahan                                           |

---

## 7. Yang TIDAK diadopsi dari repo dosen (+ penggantinya)

| Dosen                                     | Kita                                                                             | Alasan                                                               |
| ----------------------------------------- | -------------------------------------------------------------------------------- | -------------------------------------------------------------------- |
| QR meja + rotasi token                    | akun pembeli (registrasi + login)                                                | domain grosir-kirim, bukan dine-in; identitas order = user_id        |
| Sesi pelanggan anonim (`CustomerSession`) | tidak diadopsi                                                                   | cart milik user login di Redis (key per user)                        |
| WhatsApp gateway (`NotificationGateway`)  | **Web Push** (`push_subscriptions`, VAPID) **+ WhatsApp via Fonnte** (§10 draft) | dua kanal notifikasi; WA dipilih pemilik project untuk reach pembeli |
| Pre-order + slot + `release-scheduled`    | dihapus (Ditunda)                                                                | bentrok deduksi stok saat checkout                                   |
| Modifier/customizer                       | dihapus                                                                          | grosir tanpa kustomisasi item                                        |
| Jam operasional + tutup buka              | dihapus                                                                          | toko grosir tidak per toko-jam untuk MVP                             |
| —                                         | refund workflow + Midtrans Refund API                                            | fitur baru milik kita (dosen tidak ada refund)                       |
| —                                         | ongkir per berat, tier harga                                                     | fitur grosir                                                         |

---

## 8. Adopsi infrastruktur (copy dari repo dosen, domain-neutral)

1. `composer test` = `config:clear + pint + phpstan + test`; file `phpstan.neon`, `pint.json`.
2. CI `.github/workflows/tests.yml`: PHP 8.3 + Node 22, service MariaDB 11.4 + Redis 7,
   DB test terpisah (`kantin_test` pola), required check `ci`.
3. `SecurityHeaders` middleware global.
4. Limiter per modul di provider + CSRF exception webhook via provider.
5. Pola `Console\CancelUnpaidOrder` (Ordering) → auto-cancel expired kita (mg 9).
6. Pola `Reporting\Exports\{SalesReportXlsx (ZipArchive), SalesReportPdf (dompdf)}` +
   job + signed URL 24 jam → export kita (mg 13), simpan metadata di `report_exports`.
7. Pola `RequirementsTraceabilityTest` → test yang menjaga tabel ketertelusuran sinkron.
8. `evidence/pertemuan-NN/`, `docs/adr/`, `docs/IMPLEMENTATION_STATUS.md`, data-dictionary.

---

## 9. Proses mingguan (meniru repo dosen)

1. Branch `feat/pertemuan-NN-slug` dari `main` (bukan dari `develop`).
2. Kerjakan output minggu itu sesuai §14 draft; commit kecil `type(scope): message`.
3. Quality gate: `composer test` hijau + `npm run build` + working tree bersih.
4. Pull request → required check CI hijau → merge ke `main`.
5. Tag annotated `pertemuan-NN` + simpan evidence (output test, screenshot, nomor PR, merge SHA).

**Checklist selesai mingguan:**

- [ ] Output fitur dapat didemokan pada data demo.
- [ ] Test otomatis untuk jalur sukses + penolakan utama (termasuk butir §12 yang terkait).
- [ ] Pint + phpstan + build lulus; tidak ada secret di working tree.
- [ ] ADR baru bila ada keputusan arsitektur baru; data-dictionary diperbarui bila ada tabel baru.
- [ ] Evidence tersimpan.
