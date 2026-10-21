# Database Verification — Pertemuan 03

Verifikasi penegakan integritas skema basis data, foreign key komposit anti lintas-toko, serta hasil query plan EXPLAIN.

---

## 1. Migrasi yang Diterapkan (7 Berkas, 26 Tabel)

1. `2026_10_21_000101_create_stores_and_tenancy_tables.php`:
   - `stores`, `store_admins`, `commission_rates`, `store_shipping_rates`, `store_bank_accounts`, `users.phone`.
2. `2026_10_21_000102_create_catalog_tables.php`:
   - `products`, `product_prices`, `stock_movements`.
3. `2026_10_21_000103_create_order_tables.php`:
   - `orders`, `shipping_addresses`, `sub_orders`, `order_items`.
4. `2026_10_21_000104_create_payment_tables.php`:
   - `payments`, `payment_attempts`, `payment_events`.
5. `2026_10_21_000105_create_ledger_tables.php`:
   - `accounts`, `ledger_entries`, `balance_projections`.
6. `2026_10_21_000106_create_refund_and_withdrawal_tables.php`:
   - `refund_requests`, `refund_proofs`, `withdrawals`.
7. `2026_10_21_000107_create_audit_outbox_and_reporting_tables.php`:
   - `audit_logs`, `outbox_events`, `push_subscriptions`, `report_exports`.

---

## 2. Hasil Eksekusi EXPLAIN Query

### A. Query Katalog Toko
```sql
EXPLAIN SELECT * FROM products WHERE store_id = 1 AND status = 'aktif';
```
**Hasil MariaDB:**
- **type**: `ref`
- **possible_keys**: `products_store_id_id_unique,products_store_id_status_index`
- **key**: `products_store_id_id_unique` (memanfaatkan indeks komposit utama toko)
- **rows**: 3 (bukan full table scan)

### B. Query Antrean Sub-Order Toko
```sql
EXPLAIN SELECT * FROM sub_orders WHERE store_id = 1 AND status = 'pending_payment' ORDER BY created_at;
```
**Hasil MariaDB:**
- **type**: `ref`
- **possible_keys**: `sub_orders_store_id_id_unique,sub_orders_store_id_status_index`
- **key**: `sub_orders_store_id_id_unique`
- **rows**: 1

---

## 3. Penegakan Error Constraint MariaDB Nyata

| Skenario Uji | Error Code | Deskripsi Bukti |
|---|---|---|
| Memasukkan harga (`product_prices`) dengan `store_id` Toko A menunjuk produk Toko B | **Error 1452** | Foreign key constraint fails `(store_id, product_id)` menunjuk `products(store_id, id)` |
| Memasukkan order item (`order_items`) dengan `store_id` Toko A menunjuk produk Toko B | **Error 1452** | Foreign key constraint fails `(store_id, product_id)` menunjuk `products(store_id, id)` |
| Duplikasi slug toko (`stores.slug`) | **Error 1062** | Duplicate entry for key `stores_slug_unique` |
| Duplikasi idempotency key checkout (`orders.checkout_idempotency_key`) | **Error 1062** | Duplicate entry for key `orders_checkout_idempotency_key_unique` |
| Duplikasi idempotency key ledger (`ledger_entries.idempotency_key`) | **Error 1062** | Duplicate entry for key `ledger_entries_idempotency_key_unique` |
