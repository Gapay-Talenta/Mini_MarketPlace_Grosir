# Security Verification — Pertemuan 03

Verifikasi keamanan arsitektur data, isolasi multi-tenant pada lapisan engine database, dan perlindungan privasi data finansial.

---

## 1. Isolasi Multi-Tenant di Lapisan Basis Data (Defense in Depth)

Lapisan aplikasi dapat mengalami kelalaian (*missed tenant filter* dalam query Eloquent). Oleh karena itu, modul 03 menegakkan **Defense in Depth**:
- Foreign Key Komposit `(store_id, resource_id)` memastikan relasi anak tidak dapat menunjuk data induk toko lain.
- Terbukti melalui pengujian unit dan fitur bahwa MariaDB langsung menggagalkan request manipulasi data lintas tenant dengan melempar `QueryException` (error code 1452).

---

## 2. Perlindungan Privasi Data Rekening Bank Toko

- Nomor rekening toko disimpan pada kolom `store_bank_accounts.account_number_encrypted`.
- Model Eloquent `StoreBankAccount` mengaktifkan attribute cast `'account_number_encrypted' => 'encrypted'`.
- Data tersimpan dalam bentuk ciphertext terenkripsi AES-256-CBC pada database fisik.
- Kolom `account_last4` (4 digit terakhir) disediakan secara publik untuk tampilan antarmuka (UI masking), mencegah dekripsi nomor rekening secara tidak perlu.

---

## 3. Pencegahan Race Condition & Double Posting Finansial

- Penegakan `UNIQUE` constraint pada seluruh operasi kritis:
  - `orders.checkout_idempotency_key` mencegah tagihan ganda saat checkout paralel atau retry jaringan.
  - `ledger_entries.idempotency_key` mencegah pencatatan jurnal ganda.
  - `payment_events.provider_event_id` mencegah replay webhook oleh payment gateway.
