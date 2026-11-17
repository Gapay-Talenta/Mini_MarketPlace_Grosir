# Demo Script — Pertemuan 05: Administrasi Hub & Toko Grosir

1. Masuk ke aplikasi sebagai Admin Hub (`admin@grosir.test` / `password`).
2. Buka `/admin/stores`, klik **+ Daftarkan Toko**.
3. Isi nama toko, slug unik, tarif komisi platform awal (%), tarif ongkir per kg (Rp), lalu simpan.
4. Pada halaman kelola toko (`/admin/stores/{store}`):
   - Cek **Skema Komisi Platform**: jadwalkan tarif komisi baru dengan tanggal masa depan; pastikan versi lama otomatis tertutup tepat di waktu efektif baru.
   - Cek **Rekening Bank Pencairan**: tambah rekening baru (nomor 6–20 digit), pastikan hanya 4 digit terakhir yang tampil dengan status `PENDING`, lalu klik **Verifikasi** (`VERIFIED`).
   - Cek **Admin & Staf Operator Toko**: tugaskan pengguna terdaftar dengan peran `owner` / `admin` / `operator`, dan uji penolakan saat mencoba mencabut `owner` terakhir.
5. Jalankan pengujian otomatis:
   ```powershell
   php artisan test --compact tests\Feature\AdminManagementTest.php tests\Feature\CommissionScheduleBoundaryTest.php
   ```
