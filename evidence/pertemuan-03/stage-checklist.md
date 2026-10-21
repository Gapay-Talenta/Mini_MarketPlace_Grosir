# Stage Checklist — Pertemuan 03

| Tahap | Aktivitas | Status | Keterangan Verifikasi |
|---|---|---|---|
| **Tahap 1** | Audit ERD & urutan dependensi foreign key | [x] Selesai | Pemetaan 26 tabel baseline dibagi ke 7 kelompok migrasi terstruktur |
| **Tahap 2** | Migrasi Toko, Tenancy, Role, Rekening Bank | [x] Selesai | Tabel `stores`, `store_admins`, `commission_rates`, `store_shipping_rates`, `store_bank_accounts`, `users.phone` |
| **Tahap 3** | Migrasi Katalog, Tier Harga, Pergerakan Stok | [x] Selesai | Tabel `products`, `product_prices`, `stock_movements` dengan Composite FK `(store_id, product_id)` |
| **Tahap 4** | Migrasi Order, Sub-Order, Items, Pembayaran | [x] Selesai | Tabel `orders`, `shipping_addresses`, `sub_orders`, `order_items`, `payments`, `payment_attempts`, `payment_events` |
| **Tahap 5** | Migrasi Double-Entry Ledger, Refund, Withdrawal, Audit | [x] Selesai | Tabel `accounts`, `ledger_entries`, `balance_projections`, `refund_requests`, `refund_proofs`, `withdrawals`, `audit_logs`, `outbox_events`, `push_subscriptions`, `report_exports` |
| **Tahap 6** | Eloquent Models, Casts, Relasi, dan Seeder | [x] Selesai | 26 Model Eloquent dengan cast integer Rupiah & encrypted; Seeder deterministik idempoten (`AccountSeeder` + `DemoGrosirSeeder`) |
| **Tahap 7** | Verifikasi Constraint MariaDB & Dokumentasi | [x] Selesai | 8 Automated tests MariaDB constraint (Error 1452/1062); EXPLAIN query; `schema-dump.sql`; `docs/data-dictionary.md` |
