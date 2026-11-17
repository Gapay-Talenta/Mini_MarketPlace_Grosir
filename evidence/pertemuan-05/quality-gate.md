# Quality Gate — Pertemuan 05 (Tahap 1–8)

## 1. Ringkasan Eksekusi Test (Pest)
- **Status:** PASS (100% Lolos)
- **Total Suite Test:** 92 tests passed (0 failed, 0 skipped)
- **Total Assertions:** 377 assertions
- **Test Pertemuan 05:**
  - `tests/Feature/AdminManagementTest.php` (9 tests): otorisasi admin hub, pembuatan toko atomik, validasi slug unik, penjadwalan komisi effective-dated & anti-overlap, DB guard `uq_commission_active_per_store`, enkripsi rekening + `last4` + sanitasi audit, verifikasi/penolakan rekening & blokir IDOR lintas toko (404), penugasan role & perlindungan owner terakhir.
  - `tests/Feature/CommissionScheduleBoundaryTest.php` (3 tests): batas interval setengah-terbuka `[valid_from, valid_to)` hingga presisi mikrodetik (`subMicrosecond`).
  - `tests/Feature/Auth/LoginLockoutTest.php` (9 tests — UC-19): pesan generik, penguncian akun 5x gagal/10 menit selama 15 menit, akun nonaktif ditolak, redirect dasbor sesuai peran, halaman login berbahasa Indonesia, dan masa sesi 8 jam (`480` menit).
  - `tests/Feature/StoreOnboardingTest.php` (6 tests — UC-21): onboarding lengkap (owner + tautan atur kata sandi + rekening terenkripsi), tampilan daftar toko, penonaktifan toko dari katalog publik tanpa hapus histori, dan riwayat aktor pengubah komisi.

## 2. Pengecekan Gaya Kode (Laravel Pint)
- **Status:** PASSED (`vendor/bin/pint --test`)

## 3. Pengecekan Analisis Statis (PHPStan / Larastan)
- **Status:** PASSED (0 errors)
- **Perintah:** `vendor/bin/phpstan analyse --memory-limit=512M`

## 4. Frontend Bundler (Vite)
- **Status:** PASSED (`npm run build`)
