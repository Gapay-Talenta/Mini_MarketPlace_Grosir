# Arsitektur — Mini Marketplace Toko Grosir (Modular Monolith)

Satu aplikasi Laravel, satu database, satu proses deployment. Batas domain dibuat eksplisit di level kode (`app/Modules/*`) mengikuti pola *Vertical Slice Architecture*. Diperkenalkan pada Pertemuan 2 (FR-TEN-01 / UC-19).

---

## 1. Modul & Tanggung Jawab Domain

| Modul | Tanggung Jawab Utama | Rencana Realisasi |
|---|---|---|
| `Admin` | Tata kelola platform grosir, verifikasi toko/tenant, skema tarif komisi bertingkat (*effective-dated interval*), pemantauan ledger akun. Pemilik route `admin.*`. | Modul 5 |
| `Catalog` | Manajemen produk grosir, varian, satuan jual (dus, bal, karung, pack), konfigurasi 4 tier harga grosir bertingkat, stok toko. | Modul 7 |
| `Ordering` | Sesi keranjang multi-toko (Redis), checkout atomik, order induk, sub-order per toko, snapshot harga grosir. Pemilik route `customer.*`. | Modul 8–9 |
| `Payments` | Kontrak `PaymentGateway`, integrasi Midtrans QRIS Dinamis / Virtual Account, penanganan webhook idempotensi, split settlement, pencatatan ledger akun `1000, 2000, 2100, 2110, 3000`. | Modul 10–11 |
| `Fulfillment` | Pemrosesan barang di gudang toko (*order packing*), nomor resi kargo/logistik, status serah-terima barang, notifikasi status pesanan via Reverb & Fonnte WhatsApp. | Modul 12 |
| `Reporting` | Rekapitulasi omzet & penjualan scoped tenant/admin, rekonsiliasi pembayaran, ekspor laporan async (Excel/CSV), penarikan dana (*withdrawal*). | Modul 13 |

---

## 2. Struktur Direktori Per Modul (Vertical Slice)

Setiap modul **memiliki semua file fiturnya** di dalam satu folder modul sehingga satu fitur terpusat:

```text
app/Modules/{Modul}/
├── {Modul}ServiceProvider.php   ← extends App\Modules\ModuleServiceProvider (alias: huruf kecil)
├── Http/Controllers/            ← controller milik fitur modul
├── Actions/  Data/  Services/   ← use case & logika aplikasi/domain
├── routes/                      ← file route fitur per portal: {customer,tenant,admin}.php
└── resources/views/             ← view('{alias}::...')
    └── livewire/                ← <livewire:{alias}::nama-komponen />
```

`ModuleServiceProvider::boot()` secara otomatis memuat:
- `routes/*.php`
- Namespace View: `{alias}::`
- Namespace Komponen Livewire: `<livewire:{alias}::... />`

Semua provider didaftarkan di `bootstrap/providers.php`.

### Shared Kernel (Tetap di Lokasi Standar Laravel)
Bagian yang dipakai bersama lintas modul tetap diletakkan di struktur bawaan:
- Model Eloquent (`app/Models`) — satu database bersama
- Middleware (`app/Http/Middleware`)
- Komponen & Layout UI Bersama (`resources/views/components`)
- Definisi Portal Routes (`app/Support/Routing/PortalRoutes.php`)

---

## 3. Aturan Dependensi (Arah Menuju Kontrak/Domain)

```text
UI (routes, Livewire, Blade)  ──>  Application (Actions / Services)  ──>  Domain & Contracts
                                                                               ^
Infrastructure (Eloquent, Gateway, Redis, Vendor)  ────────────────────────────│
```

- Modul lain **dilarang** memanggil controller atau memanipulasi langsung tabel internal modul lain.
- Komunikasi lintas modul yang bersifat "pemberitahuan kejadian" dilakukan melalui **Event Dispatch** (misal: `PaymentCompleted` disimak oleh `Ordering` dan `Fulfillment`).
- Komunikasi kontraktual menggunakan **Interface/Service Contract** (misal: `PaymentGatewayInterface`).

---

## 4. Routing per Konteks Portal

| Konteks Portal | File Route Dasar | Prefix URI | Route Name | Middleware Lapisan Keamanan |
|---|---|---|---|---|
| **Pelanggan (Publik)** | `routes/customer.php` + modul `routes/customer.php` | `toko/{store}` | `customer.*` | `web` |
| **Mitra Toko (Tenant)** | `routes/tenant.php` + modul `routes/tenant.php` | `tenant/{tenant}` | `tenant.*` | `web, auth, verified, role:tenant` |
| **Admin Platform** | `routes/admin.php` + modul `routes/admin.php` | `admin` | `admin.*` | `web, auth, verified, role:admin` |

Grup portal didefinisikan secara **tunggal** di `App\Support\Routing\PortalRoutes`.
File route milik modul memanggil pembungkus `PortalRoutes::{portal}()` sehingga tidak ada rute internal yang lupa dilindungi middleware autentikasi dan otorisasi.

---

## 5. Keamanan & Otorisasi Server-Side

- Middleware `auth` memastikan pengguna sudah login, dan `verified` memastikan email aktif.
- Middleware `role:{tenant|admin}` (`EnsureUserHasRole`) menolak akses peran yang tidak sesuai dengan respons **HTTP 403 Forbidden**.
- Menyembunyikan tombol navigasi di Blade hanya bagian dari User Experience (UX), bukan sistem keamanan. Validasi otorisasi selalu ditegakkan di level server.

---

## 6. Desain Sistem & Komponen UI (Industrial Slate & Safety Amber)

- Desain mengadopsi palet **Industrial Slate & Safety Amber** sesuai dokumen spesifikasi mockup.
- Komponen bersama:
  - `<x-layouts.customer>`: Mobile-first container (`max-w-md`) dengan topbar slate & aksen garis amber.
  - `<x-layouts.tenant>`: Dashboard sidebar toko yang dapat diciutkan (*responsive drawer*) via Alpine.js.
  - `<x-layouts.admin>`: Desktop platform dengan header gelap dan kontainer tabel responsif.
  - `<x-button>`: Tombol standar aksesibilitas tinggi minimal 44px (`min-h-11`) dengan varian `primary` (amber), `slate`, dan `secondary`.
  - `<x-input>`: Input form dengan focus ring amber.
  - `<x-status-badge>`: Badge penanda status (*active*, *pending*, *suspended*, *default*).
  - `<x-empty-state>`: Penampung visual bergaris putus-putus (*dashed border*) saat tabel/list belum terisi data.
