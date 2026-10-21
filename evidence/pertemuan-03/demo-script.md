# Demo Script — Pertemuan 03

Panduan demonstrasi baseline migrasi, verifikasi integritas data, dan reset database pengembangan.

---

## 1. Prosedur Reset Database Pengembangan (Aman)

> [!CAUTION]
> Perintah `migrate:fresh` **HANYA** boleh dijalankan di lingkungan development/testing lokal. Jangan pernah dijalankan pada lingkungan produksi!

Jalankan perintah berikut dari root repositori:

```bash
# 1. Reset database dan isi data demo deterministik
php artisan migrate:fresh --seed

# 2. Verifikasi status migrasi
php artisan migrate:status

# 3. Jalankan seluruh automated test suite
composer test
```

---

## 2. Verifikasi Data Demo di Database

Setelah seeding, data berikut telah tersedia dan siap untuk demonstrasi:

1. **Akun Pengguna Internal & Demo:**
   - Admin Hub: `admin@grosir.test` (password: `password`)
   - Operator Toko 1: `tenant@grosir.test` (password: `password`)
   - Operator Toko 2: `plastik@grosir.test` (password: `password`)
   - Pembeli Grosir: `customer@grosir.test` (password: `password`)
2. **Toko Grosir Aktif:**
   - `Sembako Berkah Grosir` (`/toko/sembako-berkah`) — Tarif ongkir Rp 8.000/kg, komisi 10%, 3 produk dengan tier grosir.
   - `Sumber Plastik & Packing Hub` (`/toko/sumber-plastik`) — Tarif ongkir Rp 10.000/kg, komisi 12%, 2 produk dengan tier grosir.
3. **Buku Besar Finansial (Chart of Accounts):**
   - 5 Akun baku (1000, 2000, 2100, 2110, 3000) dan inisialisasi saldo proyeksi Rp 0.
