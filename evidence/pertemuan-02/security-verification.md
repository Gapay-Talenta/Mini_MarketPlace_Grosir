# Security Verification — Pertemuan 02

Pengujian server-side authorization untuk memastikan isolasi antar portal dan penegakan HTTP 403 Forbidden ketika terjadi akses tidak sah.

---

## Matriks Akses & Hasil Pengujian

| Skenario Pengujian | URL Target | Identitas Pengguna | Hasil yang Diharapkan | Hasil Aktual |
|---|---|---|---|---|
| Akses Katalog Publik | `/toko/demo` | Tamu (Guest / Anonim) | `200 OK` | `200 OK` (Lolos) |
| Tamu Akses Dashboard Tenant | `/tenant/demo/dashboard` | Tamu (Guest / Anonim) | `302 Redirect` ke `/login` | `302 Redirect` (Lolos) |
| Tamu Akses Dashboard Admin | `/admin/dashboard` | Tamu (Guest / Anonim) | `302 Redirect` ke `/login` | `302 Redirect` (Lolos) |
| Operator Tenant Akses Portal Sendiri | `/tenant/demo/dashboard` | User role `tenant` (Aktif) | `200 OK` | `200 OK` (Lolos) |
| Admin Platform Akses Portal Sendiri | `/admin/dashboard` | User role `admin` (Aktif) | `200 OK` | `200 OK` (Lolos) |
| **Operator Tenant Coba Masuk Admin** | `/admin/dashboard` | User role `tenant` (Aktif) | **`403 Forbidden`** | **`403 Forbidden`** (Lolos) |
| **Admin Coba Masuk Tenant** | `/tenant/demo/dashboard` | User role `admin` (Aktif) | **`403 Forbidden`** | **`403 Forbidden`** (Lolos) |
| Pembeli Biasa Akses Internal | `/tenant/demo/dashboard` | User role `null` (Aktif) | **`403 Forbidden`** | **`403 Forbidden`** (Lolos) |
| Akun Ditangguhkan (Suspended) | `/tenant/demo/dashboard` | User role `tenant` (`suspended`) | **`403 Forbidden`** | **`403 Forbidden`** (Lolos) |

*Seluruh skenario telah diverifikasi secara otomatis melalui `Tests\Feature\ModularFoundationTest`.*
