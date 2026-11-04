# Security Verification — Pertemuan 04: Isolasi Multi-Tenant Berlapis

## 1. Matriks Otorisasi & Pencegahan Akses Lintas-Toko (Allow / Deny)

| Skenario Uji | Aktor & Konteks | Target Resource | Hasil Ekspektasi | Hasil Aktual | Status |
|---|---|---|---|---|---|
| **Akses Dashboard Toko Sendiri** | Operator Toko A | `tenant/{storeA:slug}/dashboard` | 200 OK | 200 OK | PASS |
| **Akses Dashboard Toko Lain** | Operator Toko B | `tenant/{storeA:slug}/dashboard` | 403 Forbidden | 403 Forbidden | PASS |
| **Akses Admin Hub ke Toko Non-Anggota** | Admin Platform | `tenant/{storeA:slug}/dashboard` | 403 Forbidden | 403 Forbidden | PASS |
| **Akses Toko Non-Aktif / Suspend** | Pengelola Toko Terkait | `tenant/{suspendedStore:slug}/dashboard` | 403 Forbidden | 403 Forbidden | PASS |
| **Tampering ID Produk pada URL** | Operator Toko A | `tenant/{storeA:slug}/products/{productB->id}` | 404 Not Found (Scoped Binding) | 404 Not Found | PASS |
| **Manipulasi Eksekusi Update Produk** | Operator Toko A via Policy | Produk Toko B | Otorisasi Ditolak (false) | Ditolak (false) | PASS |
| **Query Katalog Publik Pembeli** | Tamu / Pembeli Umum | `PublicCatalogQuery` | Hanya Produk Aktif dari Toko Aktif | Toko suspended & produk arsip dieliminasi | PASS |
| **Isolasi Long-Running Worker** | Background Job Toko A lalu Toko B | `TenantContext` & `CountStoreProducts` | Reset context di `finally` | Context steril (has() = false) | PASS |

## 2. Invarian Pertahanan Berlapis (Defense-in-Depth)
1. **Container Scoped:** Instance `TenantContext` di-reset otomatis setiap pergantian request/job antrean via container scoped lifecycle.
2. **Fail-Closed Persistence:** Penulisan data model tanpa konteks toko ditolak oleh database constraint `store_id NOT NULL`.
3. **Mass-Assignment Guard:** Kolom `store_id` tidak dapat diinjeksi melalui form/JSON request client.
4. **Controlled Bypass:** Penerobosan scope dibatasi ketat hanya di `PublicCatalogQuery` dengan filter status toko aktif dan produk aktif.
