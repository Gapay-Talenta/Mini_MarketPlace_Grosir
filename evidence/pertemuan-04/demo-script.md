# Demo Script — Pertemuan 04: Isolasi Multi-Tenant Berlapis

## 1. Menjalankan Suite Uji Isolasi
```powershell
php artisan test --filter=TenantIsolationTest
```
- Menunjukkan 10 skenario matriks allow/deny (Toko A vs Toko B) berjalan dan lulus.

## 2. Membuktikan Penolakan Lintas-Toko (404 Scoped Binding)
- Mencoba mengakses rute `/tenant/{storeA:slug}/products/{productB->id}` sebagai admin Toko A menghasilkan **404 Not Found**.

## 3. Membuktikan Sanitasi Memory Worker
```powershell
php artisan test --filter=test_job_sets_and_clears_context_per_store
```
- Membuktikan background job membersihkan `TenantContext` di blok `finally`, sehingga eksekusi berikutnya pada proses worker yang sama tidak terkontaminasi toko sebelumnya.
