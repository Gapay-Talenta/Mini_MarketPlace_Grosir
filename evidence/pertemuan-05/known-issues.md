# Known Issues & Catatan Teknis — Pertemuan 05

- **Formula Komisi Platform:** `komisi = subtotal_produk × (rate_percent / 100)` menggunakan snapshot tarif komisi pada saat checkout; tarif ongkir per kg tidak dikenai komisi platform.
- **Interval Komisi:** Memakai interval setengah-terbuka `[valid_from, valid_to)` berpresisi mikrodetik (`timestampTz(..., 6)`), sehingga tidak ada celah maupun tumpang tindih pada batas pergantian.
- **Memori PHPStan:** Analisis statis membutuhkan alokasi memori di atas 128 MB (`vendor/bin/phpstan analyse --memory-limit=512M`).
- **Tindak Lanjut Tahap 8:** Penguncian akun login (`LoginLockout` - UC-19), redirect dasbor per peran, onboarding toko lengkap dengan akun owner + tautan reset password + rekening awal, serta toggle status `aktif`/`nonaktif` toko (UC-21) dikerjakan pada Tahap 8.
