# Verifikasi Keamanan — Pertemuan 05

1. **Otorisasi Berlapis (Middleware + Policy):**
   - Portal `/admin/stores/*` dilindungi `PortalRoutes::admin()` (`auth`, `verified`, `role:admin`).
   - Non-admin (`customer` maupun `tenant`) menerima `403 Forbidden`; guest diarahkan ke `/login`.
   - Setiap aksi sensitif pada controller memeriksa kemampuan melalui `StorePolicy`.
2. **Enkripsi & Masking Rekening Bank:**
   - Nomor rekening disimpan menggunakan cast `encrypted` pada `StoreBankAccount::$account_number_encrypted`.
   - Kolom `store_bank_accounts.account_number_encrypted` di database tidak berisi nomor mentah; UI hanya menampilkan `bank_name` dan `•••• account_last4`.
   - Verifikasi rekening lintas toko (`storeA` memverifikasi rekening milik `storeB`) ditolak dengan `404 Not Found`.
3. **Audit Trail Append-Only & Sanitasi:**
   - `AuditLogger` menghapus `account_number`, `account_number_encrypted`, `password`, dan token rahasia sebelum menulis ke `audit_logs`.
   - `AuditLogger` menolak penulisan tanpa aktor terautentikasi/valid (`RuntimeException`).
4. **Integritas Komisi Effective-Dated:**
   - `lockForUpdate()` mencegah race condition saat penjadwalan komisi.
   - Kolom virtual `active_lock` + indeks unik `uq_commission_active_per_store` di MariaDB menjamin maksimal satu versi komisi aktif per toko.
