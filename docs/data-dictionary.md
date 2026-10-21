# Data Dictionary — Baseline Skema Grosir (Modul 03)

Diturunkan dari `docs/schema.dbml` (26 tabel LOCKED), `docs/ERD_Mini_Marketplace_Grosir.svg`, dan `docs/draft_rincian_project_laravel.md`.

---

## 1. Konvensi & Invariant Skema

- **Scope:**
  - **P (Platform-Scoped)**: Sumber daya tingkat hub, tidak memiliki kolom `store_id` (misalnya: `orders`, `payments`, `users`, `accounts`).
  - **T (Tenant-Owned)**: Sumber daya milik toko tertentu, wajib memiliki `store_id` dan dilindungi oleh composite unique/FK.
  - **M (Mixed / Support)**: Sumber daya operasional/keuangan yang mencatat transaksi platform maupun toko (misalnya: `ledger_entries`, `balance_projections`, `audit_logs`).
- **Tipe Uang**: `unsignedBigInteger` (integer Rupiah). Tidak menggunakan `FLOAT` / `DOUBLE` untuk mencegah galat pembulatan desimal biner.
- **Tipe Tarif / Rate**: `decimal(5,2)` untuk komisi persen (misal `10.00` = 10%).
- **Presisi Timestamp**: `timestamp(6)` UTC (microsecond precision) untuk menjaga urutan event transaksi yang tiba berdekatan.
- **Anti Lintas-Toko (Cross-Tenant Leakage Prevention)**:
  Penegakan via **Composite Foreign Key** MariaDB:
  `(store_id, resource_id) REFERENCES parent_table(store_id, id)`.
  Jika aplikasi mengalami bug/bypass logika, database MariaDB akan langsung menolak operasi secara atomik dengan error `1452`.
- **Append-Only Principles**:
  Tabel `ledger_entries`, `payment_events`, `stock_movements`, dan `audit_logs` bersifat *append-only*. Perubahan data dilakukan melalui transaksi pembalik (*reversal entry*), bukan `UPDATE` atau `DELETE`.

---

## 2. Pemetaan 26 Tabel Baseline ke Model Eloquent

| # | Nama Tabel | Scope | Model Eloquent | Kunci & Invariant Utama |
|---|---|:---:|---|---|
| 1 | `users` | P | `User` | PK `id`, UQ `email`, kolom `phone`, role `customer` / `admin_hub` / `admin` |
| 2 | `stores` | T | `Store` | PK `id`, UQ `slug`, status `aktif` / `nonaktif` |
| 3 | `store_admins` | T | `StoreAdmin` | UQ `(store_id, user_id)`, role `owner` / `admin` / `operator` |
| 4 | `commission_rates` | T | `CommissionRate` | Effective-dated (`valid_from`, `valid_to`), decimal rate |
| 5 | `store_shipping_rates` | T | `StoreShippingRate` | UQ `store_id` (1 tarif aktif per toko), integer Rupiah/kg |
| 6 | `store_bank_accounts` | T | `StoreBankAccount` | UQ `(store_id, id)` target FK composite withdrawal, `account_number_encrypted` (cast encrypted), last4 |
| 7 | `products` | T | `Product` | UQ `(store_id, id)` target FK anak, kategori fixed enum, satuan jual fixed enum, berat gram, stok |
| 8 | `product_prices` | T | `ProductPrice` | Composite FK `(store_id, product_id)`, UQ `(store_id, product_id, price_type, min_qty)` (1 normal + maks 4 tier grosir) |
| 9 | `stock_movements` | T | `StockMovement` | Append-only, Composite FK `(store_id, product_id)`, delta kuantitas stok |
| 10 | `orders` | P | `Order` | **TANPA store_id**, UQ `checkout_idempotency_key`, pemilik `user_id` |
| 11 | `shipping_addresses` | P | `ShippingAddress` | UQ `order_id` (1 alamat per order induk) |
| 12 | `sub_orders` | T | `SubOrder` | FK `order_id`, FK `store_id`, UQ `(store_id, id)` target FK items & refund, snapshot komisi & ongkir |
| 13 | `order_items` | T | `OrderItem` | Composite FK `(store_id, sub_order_id)` & `(store_id, product_id)`, snapshot harga, satuan, dan berat |
| 14 | `payments` | P | `Payment` | UQ `order_id`, setara `grand_total`, status `pending` / `settled` / `expired` / `failed` |
| 15 | `payment_attempts` | P | `PaymentAttempt` | UQ `(payment_id, attempt_no)` (maks 3 attempt QR), UQ `provider_reference` |
| 16 | `payment_events` | P | `PaymentEvent` | Append-only webhook log, UQ `provider_event_id` (idempotency guard replay webhook) |
| 17 | `accounts` | P | `Account` | PK `code` (5 akun COA: 1000, 2000, 2100, 2110, 3000) |
| 18 | `ledger_entries` | M | `LedgerEntry` | Append-only double-entry, UQ `idempotency_key`, FK `account_code`, delta debit & credit |
| 19 | `balance_projections` | M | `BalanceProjection` | Proyeksi saldo akun per toko / platform, dapat direkonsiliasi ulang dari ledger |
| 20 | `refund_requests` | T | `RefundRequest` | Composite FK `(store_id, sub_order_id)`, pemilik `user_id`, refund penuh per sub-order |
| 21 | `refund_proofs` | T | `RefundProof` | FK `refund_request_id`, bukti foto (maks 3 file per request) |
| 22 | `withdrawals` | T | `Withdrawal` | FK `store_id`, Composite FK `(store_id, bank_account_id)` ke rekening terverifikasi |
| 23 | `audit_logs` | M | `AuditLog` | Append-only jejak keputusan admin, polymorphic `subject_type` & `subject_id` |
| 24 | `outbox_events` | M | `OutboxEvent` | Transactional outbox pattern untuk event broadcast Reverb |
| 25 | `push_subscriptions` | P | `PushSubscription` | UQ `(user_id, endpoint)` langganan Web Push browser |
| 26 | `report_exports` | T | `ReportExport` | Antrean job ekspor CSV/XLSX/PDF per toko / requester dengan signed URL |

---

## 3. Chart of Accounts (COA 5 Akun)

| Kode | Nama Akun | Tipe | Peruntukan |
|---|---|---|---|
| `1000` | Kas / Payment Gateway | Aset | Rekening penampungan dana masuk dari gateway pembayaran |
| `2000` | Saldo Toko (available) | Liabilitas | Saldo toko yang siap ditarik (tersedia setelah order tuntas) |
| `2100` | Saldo Hold Refund | Liabilitas | Saldo sub-order yang ditahan selama proses pengiriman/komplain |
| `2110` | Saldo Hold Withdrawal | Liabilitas | Saldo toko yang sedang dalam proses verifikasi payout |
| `3000` | Pendapatan Komisi Platform | Pendapatan | Pendapatan komisi platform dari subtotal pesanan produk |
