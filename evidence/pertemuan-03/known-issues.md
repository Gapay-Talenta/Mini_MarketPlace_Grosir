# Known Issues & Settlement Decisions — Pertemuan 03

Daftar isu yang diantisipasi dan keputusan arsitektur database yang diambil pada modul 03.

---

## 1. Exclusion Constraint pada Effective-Dated Commission Rates
- **Isu**: MariaDB tidak memiliki native *temporal exclusion constraint* seperti PostgreSQL untuk mencegah overlap rentang tanggal (`valid_from` - `valid_to`) pada level database schema.
- **Keputusan**: Penegakan anti-overlap di-handle pada service layer (`AdminModule` / `CommissionService` di Modul 05) sebelum `INSERT`/`UPDATE`, sedangkan database menyediakan indeks pendukung `(store_id, valid_from)`.

## 2. Nullable Unique Index pada Balance Projections
- **Isu**: Pada standar SQL MariaDB/MySQL, nilai `NULL` tidak dianggap duplikat pada unique index, sehingga `(account_code, store_id)` dengan `store_id = NULL` (milik kas/komisi platform) tidak dapat dicegah duplikasi native oleh database jika ada banyak baris.
- **Keputusan**: Skema menggunakan indeks `(account_code, store_id)` dan keunikan platform-level baris ditegakkan secara idempoten oleh seeder dan service layer menggunakan query `updateOrCreate(['account_code' => $code, 'store_id' => null])`.

## 3. Presisi Microsecond pada Timestamps
- **Isu**: Event pergerakan stok dan ledger entries dapat terjadi pada detik yang sama dalam satu transaksi.
- **Keputusan**: Seluruh tabel transaksi keuangan dan audit menggunakan `timestamps(6)` (microsecond precision) guna menjamin pengurutan audit trail deterministik.
