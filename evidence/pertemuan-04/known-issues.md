# Known Issues & Architectural Constraints — Pertemuan 04

## 1. Aturan Bypass Scope Global
- `withoutGlobalScope('tenant')` **hanya** diizinkan di dalam service domain khusus (`App\Modules\Catalog\Services\PublicCatalogQuery`) untuk keperluan pembeli publik.
- Dilarang memanggil `withoutGlobalScope` di controller atau komponen Livewire portal toko.

## 2. Akses Platform Admin ke Data Toko (Rencana Modul 05)
- Pada Modul 04, admin platform ditolak 403 saat mengakses portal internal toko jika tidak terdaftar di `store_admins` (portal toko strictly untuk operator toko).
- Di Modul 05, akses peninjauan toko oleh Admin Platform akan dilayani melalui rute portal admin (`/admin/stores/...`) menggunakan service terdedikasi dan wajib dicatat ke tabel `audit_logs`.
