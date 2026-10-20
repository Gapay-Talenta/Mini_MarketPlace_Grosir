# Database Verification — Pertemuan 02

Verifikasi skema tabel dan integritas data pengguna per peran.

---

## 1. Migrasi yang Dijalankan
- File migrasi: `database/migrations/2026_10_20_180921_add_role_and_status_to_users_table.php`
- Kolom yang ditambahkan pada tabel `users`:
  - `role`: VARCHAR(255), NULLABLE (nilai: `'admin'`, `'tenant'`, atau `NULL` untuk pembeli/customer biasa).
  - `status`: VARCHAR(255), DEFAULT `'active'` (nilai: `'active'` atau `'suspended'`).

---

## 2. Akun Demo Deterministik (`database/seeders/DatabaseSeeder.php`)
Dihasilkan melalui perintah `php artisan migrate:fresh --seed`:

| Nama User | Email | Role | Status | Password |
|---|---|---|---|---|
| Admin Hub Platform | `admin@grosir.test` | `admin` | `active` | `password` |
| Operator Toko Makmur | `tenant@grosir.test` | `tenant` | `active` | `password` |
| Pembeli Grosir | `customer@grosir.test` | `null` | `active` | `password` |
