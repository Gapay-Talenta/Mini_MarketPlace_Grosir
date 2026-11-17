# Stage Checklist — Pertemuan 05 (Tahap 1–7)

- [x] **Tahap 1: Rancang Workflow Admin**
  - State transition `stores.status` (`aktif` ↔ `nonaktif`, tanpa hard delete).
  - `app/Policies/StorePolicy.php` memetakan kemampuan Admin Hub vs pengelola toko (`owner`, `admin`, `operator`).
  - `app/Modules/Admin/Services/AuditLogger.php` append-only dengan fail-fast actor & sanitasi field sensitif.
- [x] **Tahap 2: CRUD Toko Grosir dengan Form Request**
  - `StoreStoreRequest`, `CreateStore` (transaksi atomik: toko + komisi awal + ongkir per kg + audit), `AdminStoreController`, rute `admin.stores.*`, dan view Blade `admin::stores.*`.
- [x] **Tahap 3: Penugasan User dan Role Toko (`store_admins`)**
  - `AssignStoreRole` (melindungi owner terakhir dari pencabutan maupun downgrade), `AdminStoreRoleController`, rute `admin.stores.roles.*`.
- [x] **Tahap 4: Skema Komisi Effective-Dated**
  - `ScheduleCommissionRequest` (`after:now`), `ChangeCommissionSchedule` (`lockForUpdate` + interval setengah-terbuka), `CommissionRate::scopeEffectiveAt`, migrasi DB guard `uq_commission_active_per_store`, dan `AdminCommissionController`.
- [x] **Tahap 5: Rekening Toko Terenkripsi & Audit Trail**
  - `StoreBankAccountRequest`, `ManageBankAccount` (`account_number_encrypted` + `account_last4`, workflow `pending` → `verified`/`rejected`), `AdminBankAccountController` (dengan guard lintas toko `abort_unless`), dan kartu Rekening Bank di `edit.blade.php`.
- [x] **Tahap 6: Uji UI dan Aturan Bisnis**
  - `tests/Feature/AdminManagementTest.php` dan `tests/Feature/CommissionScheduleBoundaryTest.php` (12 test lulus).
- [x] **Tahap 7: Dokumentasi dan Commit**
  - Evidence pertemuan 5 tersimpan, seluruh quality gate hijau, dan commit tahap 1–7 dibuat.
