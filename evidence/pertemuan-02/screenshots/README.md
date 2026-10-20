# Panduan Pengambilan Screenshot Evidence — Pertemuan 02

Simpan file tangkapan layar (format PNG / JPG) ke dalam folder ini (`evidence/pertemuan-02/screenshots/`).

Gunakan pintasan keyboard Windows: **Win + Shift + S** untuk mengambil gambar layar.

---

## Daftar Screenshot yang Perlu Diambil:

1. **`01-customer-mobile.png`**  
   - **Halaman:** `http://127.0.0.1:8000/toko/grosir-makmur`
   - **Kondisi:** Tekan `F12` di browser, aktifkan mode perangkat ponsel (*Responsive View* lebar 375px atau 390px).
   - **Tujuan:** Membuktikan layout pembeli bersifat *mobile-first* dengan topbar gelap beraksen amber.

2. **`02-tenant-dashboard.png`**  
   - **Halaman:** `http://127.0.0.1:8000/tenant/grosir-makmur/dashboard`
   - **Kondisi:** Setelah login dengan akun `tenant@grosir.test` / `password`.
   - **Tujuan:** Membuktikan layout operator toko dengan sidebar hitam, badge status aktif, kartu ringkasan, dan tombol amber.

3. **`03-security-403-forbidden.png`**  
   - **Halaman:** `http://127.0.0.1:8000/admin/dashboard`
   - **Kondisi:** Saat masih login sebagai `tenant@grosir.test` (mencoba tembus akses admin platform).
   - **Tujuan:** Membuktikan server menolak akses dengan halaman `403 Forbidden`.

4. **`04-admin-dashboard.png`**  
   - **Halaman:** `http://127.0.0.1:8000/admin/dashboard`
   - **Kondisi:** Setelah login dengan akun `admin@grosir.test` / `password`.
   - **Tujuan:** Membuktikan dashboard platform dengan tabel daftar mitra toko grosir.

5. **`05-terminal-test-passed.png`**  
   - **Layar:** Jendela Terminal / Command Prompt
   - **Kondisi:** Hasil eksekusi `php artisan test --compact` yang menunjukkan **47 passed (140 assertions)** berwarna hijau.
   - **Tujuan:** Bukti bahwa seluruh automated test berhasil 100%.
