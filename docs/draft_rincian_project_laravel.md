# Checkpoint — Rangkuman Keputusan Project

## 1. Konteks project

- **Nama kerja:** Hub Toko Grosir (marketplace banyak toko grosir).
- **Basis:** Modul Praktikum Laravel 13 — Kantin Multi-Tenant, versi 8.0.
- **Target:** MVP yang deploy ke staging dan diuji. Bukan produksi. Demo final minggu 14 dengan script terlatih.
- **Durasi:** 14 minggu × 3,5 jam efektif kelas + tugas mandiri.
- **Stack:** Laravel 13, Blade, Livewire 4, Reverb, MariaDB, Redis, scheduler (`schedule:work`/cron).
- **Arsitektur:** Modular monolith, shared database, isolasi tenant berlapis.

## 2. Model bisnis

- **Hub** = platform. **Toko grosir** = tenant. Satu hub, banyak toko.
- **Pembeli:** terdaftar, wajib login. Registrasi memakai nama, email, password, dan **nomor HP (tanpa OTP)** — nomor HP juga jadi target notifikasi WhatsApp. Identitas order/refund/push terikat **`user_id`**. Konsekuensinya: tidak ada sesi anonim, tidak ada tracking token; keranjang milik akun.
- **Login wajib untuk belanja:** add-to-cart, checkout, lihat order, ajukan refund, opt-in Web Push — semua butuh sesi login. **Katalog publik** tetap bisa dibaca tanpa login.
- **Peran `users.role`:** `customer` (pembeli) dan `admin_hub` (petugas platform). Admin toko bukan role di tabel users — admin toko = user yang punya baris membership di `store_admins` (owner | admin | operator).
- **Katalog publik:** lintas toko, bisa difilter per toko & kategori, search, detail produk.
- **Produk:** tiap produk punya **kategori** (daftar tetap global: `bahan_pangan, minuman, alat_tulis, kebersihan, rumah_tangga, lainnya`), **satuan jual** (daftar tetap: `pcs, kardus, karung, pack, rim, kg, liter` — dipilih admin, 1 per produk), **1 foto** (jpg/png/webp maks 2MB, disk public), dan **deskripsi**. Satuan jual jadi acuan qty, tier `min_qty`, harga, berat, dan stok.
- Query katalog lintas toko memakai `withoutGlobalScopes()` **eksplisit** + filter pengganti: produk & toko `aktif` saja (pengecualian yang direview, bukan celah).
- **Checkout:** lintas toko. Satu order induk, banyak sub-order per toko.
- **Pembayaran:** satu pembayaran gabungan untuk seluruh order induk.
- **Pengiriman:** dikirim, bukan pickup. Alamat disimpan saat checkout.
- **Ongkir:** dihitung per sub-order (per toko) berdasarkan **berat** — tarif per kg milik toko × total berat item sub-order. Ditotal jadi satu angka saat ditampilkan ke pembeli di checkout. Disimpan granular per sub-order di database.
- **Stok:** dikurangi **saat checkout** (dalam transaksi atomik, `lockForUpdate` + revalidasi) untuk mencegah oversell. Stok dikembalikan otomatis saat payment `expired` atau order dibatalkan.

## 3. Keputusan harga

- **Harga normal + tier grosir per produk:** satu harga normal + maksimal **4 tier grosir** — pasangan `min_qty` → harga yang di-set **manual oleh admin toko per produk** (`min_qty` unik dan menaik). Bukan rumus diskon otomatis.
- **Resolver tier:** katalog menampilkan "≥ min_qty → Rp X"; revalidasi cart dan checkout mengambil tier dengan `min_qty` tertinggi yang ≤ qty pembeli. Qty dihitung dalam **satuan jual** produk.
- Tidak ada harga per pelanggan, tidak ada kontrak B2B.
- Snapshot harga saat checkout: simpan harga final + qty + tier (`min_qty`) yang terpakai.

## 4. Keputusan komisi & ongkir

- **Komisi platform:** persentase dari **subtotal produk saja**. Ongkir tidak kena komisi.
- **Komisi effective-dated:** tarif berversi per toko (`valid_from`/`valid_to`, tanpa periode overlap). Rate yang berlaku di-**snapshot per sub-order saat checkout**, jadi transaksi dan laporan historis tidak berubah saat tarif baru diterbitkan.
- **Ongkir:** masuk ke **toko**/per sub-order, bukan platform, karena toko yang mengirim. Tiap toko punya **tarif ongkir per kg** sendiri; nilai ongkir final dihitung sejak checkout dari berat item, bukan hasil pembagian dari angka gabungan post-hoc.
- **Perhitungan ongkir:** total berat item dalam sub-order (gram → kg, dibulatkan ke atas, minimum 1 kg) × tarif per kg toko. Berat produk = **gram per satu satuan jual** (mis. 1 kardus = 12.000 g) × qty, wajib diisi (integer gram). Snapshot checkout menyimpan total berat, tarif per kg, dan ongkir final per sub-order.
- Saat refund: **komisi platform dikembalikan penuh untuk sub-order yang di-refund.**

## 5. Ledger — double-entry sederhana

**Chart of accounts (5 akun):**

| Kode | Akun                       | Tipe       |
| ---- | -------------------------- | ---------- |
| 1000 | Kas / Payment Gateway      | Aset       |
| 2000 | Saldo Toko (available)     | Liabilitas |
| 2100 | Saldo Hold Refund          | Liabilitas |
| 2110 | Saldo Hold Withdrawal      | Liabilitas |
| 3000 | Pendapatan Komisi Platform | Pendapatan |

**Aturan posting:**

- Setiap transaksi finansial menulis minimal dua baris (debit + kredit), total seimbang.
- Ledger **append-only**. Koreksi pakai reversal, bukan update/delete.
- Setiap entry punya `idempotency_key` unik.
- Setiap entry terikat `reference_type` + `reference_id`.
- `balance_projection` di-update dalam transaksi yang sama, tetap bisa direkonsiliasi ulang dari ledger.
- Semua nominal integer Rupiah.

**Alur hold:**

```
Saat settled:
Debit  1000 Kas
Credit 2100 Saldo Hold Refund (net per toko: produk + ongkir − komisi sub-order)
Credit 3000 Komisi Platform

Saat sub-order selesai (status `completed`):
Debit  2100 Saldo Hold Refund
Credit 2000 Saldo Toko (available)

Saat request withdrawal:
Debit  2000 Saldo Toko
Credit 2110 Saldo Hold Withdrawal

Saat payout:
Debit  2110 Saldo Hold Withdrawal
Credit 1000 Kas

Saat withdrawal ditolak:
Debit  2110 Saldo Hold Withdrawal
Credit 2000 Saldo Toko (available)

Saat refund approve (per sub-order, mirror reversal dari posting settled):
Debit  2100 Saldo Hold Refund  (hold sub-order: produk + ongkir − komisi)
Debit  3000 Komisi Platform    (komisi sub-order)
Credit 1000 Kas                (dana kembali ke pembeli via gateway)
```

Refund dicatat sebagai **reversal penuh** dari posting settled sub-order itu — tanpa akun refund terpisah, karena akun khusus hanya menambah redundansi terhadap prinsip reversal.

## 6. Daftar tabel inti — LOCKED

> Status: **locked**. Bahan ERD domain grosir, pengganti ERD kantin lama. ERD dibuat sebagai **skrip DBML (dbdiagram.io)**, bukan file SVG — skrip lebih mudah direvisi dan di-generate ulang saat spesifikasi menyesuaikan.

| Kelompok         | Tabel                                                                                                                  |
| ---------------- | ---------------------------------------------------------------------------------------------------------------------- |
| Toko & admin     | `stores`, `store_admins` (role), `commission_rates`, `store_shipping_rates` (tarif per kg), `store_bank_accounts`      |
| Katalog          | `products`, `product_prices` (normal + grosir min_qty), `stock_movements`                                              |
| Order            | `orders` (pemilik = user login), `sub_orders`, `order_items` (snapshot), `shipping_addresses`                          |
| Payment          | `payments`, `payment_attempts`, `payment_events` (log webhook append-only, dedup `provider_event_id`)                  |
| Ledger           | `accounts` (seeder 5 akun), `ledger_entries`, `balance_projections`                                                    |
| Refund           | `refund_requests` (pengaju = user), `refund_proofs`                                                                    |
| Withdrawal       | `withdrawals`                                                                                                          |
| Reporting        | `report_exports` (metadata job ekspor CSV/XLSX/PDF + signed URL)                                                       |
| Audit & realtime | `audit_logs` (tindakan admin, append-only), `outbox_events` (broadcast), `push_subscriptions` (web push, terikat user) |

Skrip DBML (`schema.dbml`, 26 tabel) sudah dibuat dari daftar ini — tabel, kolom inti, tipe, relasi, composite FK `(store_id, resource_id)` untuk tabel tenant-owned. Import ke dbdiagram.io untuk visualisasi; migrasi Laravel minggu 3 mengikuti skrip ini.

## 7. Refund

- **Per sub-order toko**, bukan order induk. Karena tiap toko punya status kirim sendiri.
- **Syarat pengajuan:**
  1. Sudah lewat estimasi pengiriman.
  2. Status sub-order masih `shipped`, bukan `completed`.
  3. Tidak ada pengajuan refund yang sedang aktif (pending review) untuk sub-order yang sama.
  4. Ada bukti screenshot (file upload, gambar jpg/png/webp saja, maks 2MB per file, maks 3 file per pengajuan).
- **Aturan tambahan:**
  - **Satu pengajuan aktif dalam satu waktu** per sub-order. Setelah pengajuan ditolak, boleh ajukan lagi (selama masih memenuhi syarat dan status masih `shipped`).
  - Refund **tidak otomatis cair**. Admin toko approve dulu.
  - Refund **tidak otomatis approve** kalau estimasi belum lewat, kecuali admin override dengan alasan.
  - Bukti disimpan di **disk private**, bukan public.
  - Batasi ukuran dan tipe file.
  - Rate limit pengajuan refund: maks 5 pengajuan per user per jam.
  - Refund diajukan oleh **user pemilik sub-order** (login wajib); service memvalidasi `refund_requests.user_id = orders.user_id`.
- **Alur:**
  ```
  paid → processing → shipped → (lewat estimasi) → pengajuan refund
  → admin review → approve/reject
  → approve: reversal ledger, status refunded
  → reject: status kembali ke shipped, alasan reject disimpan
  ```
- Komisi dan ongkir sub-order tersebut dikembalikan penuh saat refund di-approve — ongkir dan komisi sudah granular per sub-order, jadi refund 1 sub-order = refund penuh komisi + ongkir sub-order itu.
- **Dana balik ke pembeli via Midtrans Refund API** (sandbox). Karena pembayaran gabungan per order induk, refund per sub-order = **partial refund** di sisi gateway (amount = total sub-order: produk + ongkir).
- Urutan saat approve: panggil Midtrans Refund API dulu → sukses → posting reversal + status `refunded`. Gagal → tidak ada reversal, refund berstatus `payout_failed` untuk ditindaklanjuti admin secara manual (tanpa retry otomatis, konsisten §9). Ledger hanya mencatat dana yang benar-benar kembali.

## 8. Hold release (kapan saldo toko cair)

- Saldo masuk `2100 Saldo Hold Refund` saat settled.
- Hold dilepas ke `2000 Saldo Toko` saat sub-order berstatus **`completed`**.
- `completed` terjadi kalau salah satu:
  1. Pembeli konfirmasi lewat halaman order milik akunnya (login), atau
  2. Admin toko tandai completed, atau
  3. Otomatis 4 hari setelah estimasi pengiriman terlewati, tanpa refund — **scheduled job**.
- Kalau refund diajukan sebelum completed: hold ditahan.
- Kalau refund approve: hold dikurangi.
- Kalau refund reject: hold tetap, boleh ajukan refund lagi (satu pengajuan aktif dalam satu waktu).

## 9. Payment

- **Satu provider saja:** QRIS sandbox via Midtrans.
- **Satu interface:** `PaymentGateway`.
- **Dua adapter:** `FakeQrisGateway` (demo lokal & test, tanpa jaringan) + sandbox. Contract test **sama** untuk keduanya.
- **Webhook:** verifikasi signature, idempoten (dedup `provider_event_id`), transaksi atomik.
- **Settlement:** posting double-entry dalam satu transaksi dengan perubahan status order.
- **After-commit:** event/job diproses setelah commit, pakai `afterCommit()` atau `ShouldDispatchAfterCommit`.
- **Tidak pakai:** multi-provider, outbox penuh lintas domain, retry buta.

## 10. Realtime & notifikasi

- **Reverb** + **private channel**.
- **Channel tenant:** `tenant.{id}.orders` untuk admin toko.
- **Channel pelanggan:** `user.{id}` — private channel standar Laravel, otorisasi via session auth (pemilik channel = user login). Terpisah dari channel tenant.
- **Otorisasi channel:** endpoint channel authorization Laravel memvalidasi sesi login; user A tidak bisa subscribe `user.{id}` milik user B.
- **Broadcast lewat outbox minimal:** event realtime ditulis ke tabel `outbox_events` dalam transaksi yang sama dengan perubahan state; publisher job mengirim ke Reverb setelah commit, dengan retry terbatas. Selaras materi outbox Modul 11, tanpa outbox penuh lintas domain.
- **Event setelah commit** (`ShouldDispatchAfterCommit`).
- **Payload minimal:** ID, status, timestamp. Bukan model penuh.
- **Web Push (opt-in pembeli login):**
  - Opt-in di halaman order (tombol eksplisit, bukan prompt otomatis). Browser menghasilkan `subscription` yang dikirim ke backend dan disimpan di `push_subscriptions`, **terikat `user_id`**.
  - Service worker minimal (1 file) + VAPID key di `.env`/config. Hanya HTTPS.
  - Trigger = event status yang sama dengan payload Reverb (`shipped`, refund approved, `completed`); kirim via queue worker.
  - Subscription dihapus saat user tidak punya order aktif lagi (semua `completed`/`cancelled`) — data minimization.
- **WhatsApp (kanal notifikasi order, via Fonnte):**
  - **Kontrak:** `App\Contracts\WhatsAppGateway::send(string $phone, string $message): WhatsAppResult`.
  - **Dua adapter:** `FonnteGateway` (HTTP POST `https://api.fonnte.com/send`, header `Authorization: <FONNTE_TOKEN>`) + `FakeWhatsAppGateway` (demo lokal & test, tanpa jaringan). Contract test **sama** untuk keduanya.
  - **Kanal Laravel Notification:** `WhatsAppChannel` + notifikasi status order (`OrderPaid`, `OrderShipped`, `RefundApproved`, `OrderCompleted`); route notifikasi = `users.phone`.
  - **Trigger** = event status sama dengan Reverb; dikirim via job queue (`ShouldDispatchAfterCommit`). Kegagalan gateway **tidak pernah** menggagalkan transaksi order — dicatat log, retry terbatas.
  - Token & config: `FONNTE_TOKEN` di `.env`, baca via `config/services.php`. Rate limit pengiriman per user (anti-spam).
  - Tanpa OTP dan tanpa opt-out per user di MVP: notif otomatis untuk 4 event di atas.
- **Reconnect:** ambil snapshot terbaru dari server.
- **Catatan ADR:** Fonnte = gateway tidak resmi (risiko nomor ter-ban — diterima untuk MVP/staging); phone tanpa OTP → salah input = notif gagal (log, tanpa retry buta).

## 11. Status pesanan

Order induk dan sub-order punya status terpisah:

**Order induk:**

```
pending_payment → paid → completed
      ↘ expired → cancelled
```

- QR expired → regenerasi payment attempt (maks 3) → kalau tetap tidak dibayar, order `cancelled` + stok dikembalikan + cart dibersihkan.
- Auto-cancel order `pending_payment` yang lewat batas waktu bayar + release stok: **scheduled job**.
- Order induk `completed` saat semua sub-order berstatus `completed` atau `refunded`.

**Sub-order toko:**

```
pending_payment → paid → processing → shipped → completed
                                        ↕ refund_requested ↔ (shipped saat reject)
                                        ↘ refunded
```

`refund_requested` bukan penerus `shipped`: saat reject, status kembali ke `shipped` (alasan reject tersimpan di data refund, tidak ada state `rejected`).

- Kalau order induk `expired`/`cancelled`, semua sub-order di dalamnya ikut `expired`/`cancelled` (stok dilepas via `stock_movements`; tanpa hold ledger karena belum `settled`).

## 12. Test wajib

Acceptance checklist — semua wajib otomatis dan hijau sebelum minggu berikutnya:

1. **Isolasi tenant:** admin/user toko A tidak bisa baca/ubah resource toko B (matrix allow/deny per modul).
2. **Idempotency checkout:** retry dengan `checkout_idempotency_key` sama mengembalikan order yang sama, tidak dobel.
3. **Idempotency webhook:** event provider sama (`provider_event_id`) diputar ulang → settlement hanya terjadi sekali.
4. **Idempotency ledger:** posting dengan `idempotency_key` sama ditolak/di-dedup; append-only (tidak ada update/delete).
5. **Invariant ledger:** per reference, Σ debit = Σ kredit; saldo `balance_projections` bisa direkonsiliasi ulang dari `ledger_entries`.
6. **Oversell:** dua checkout paralel untuk stok terakhir → satu berhasil, satu gagal terkontrol.
7. **Release stok:** payment expired/cancel → stok kembali via `stock_movements`.
8. **State refund:** syarat pengajuan, satu pengajuan aktif, reject → kembali `shipped`, approve → reversal + `refunded`; API gagal → `payout_failed`, tanpa posting.
9. **Otorisasi channel:** subscribe channel tenant/`user.{id}` tanpa credential sah → ditolak; user A tidak bisa subscribe channel user B.
10. **Komisi:** overlap periode tarif ditolak; snapshot rate tidak berubah saat tarif baru terbit.
11. **Resolver tier harga:** qty tepat di `min_qty`, di antara dua tier, dan di atas tier tertinggi → tier terpilih benar; snapshot menyimpan tier yang terpakai; tier tidak bisa diubah retroaktif oleh edit harga.
12. **Web Push:** subscription tersimpan terikat `user_id`; perubahan status memicu push (sender difake di test); subscription dihapus saat user tidak punya order aktif lagi (semua `completed`/`cancelled`); tanpa izin user → tidak ada subscription tersimpan.
13. **WhatsApp:** event status memicu job WA via `FakeWhatsAppGateway` (contract test sama dengan `FonnteGateway`); penerima = `users.phone` pemilik order; kegagalan gateway tidak mengubah status order dan tidak gagal-kan transaksi.
14. **Auth gate:** guest tidak bisa add-to-cart/checkout/lihat order/ajukan refund; user tanpa membership tidak bisa akses panel toko; user biasa tidak bisa akses admin hub.

## 13. Scope MVP final

**Diangkat:**

1. Modular monolith, **6 modul mengikuti repo referensi dosen**: `Admin`, `Catalog`, `Ordering`, `Payments`, `Fulfillment` (pengganti Kitchen), `Reporting` — panel admin toko bukan modul terpisah, UI tenant milik modul fitur (mis. `catalog::menu-manager`). Struktur modul & skema route mengikuti pola repo dosen — detail di `direksi_pengerjaan.md`.
2. Multi-tenant shared database + isolasi berlapis (TenantContext, global scope, policy, scoped binding, composite FK).
3. **Pembeli terdaftar** (registrasi + login wajib untuk belanja, phone tanpa OTP) + refund ketat.
4. Katalog multi-toko: kategori, satuan jual, foto, deskripsi, tier harga grosir (maks 4 tier per produk), berat produk.
5. Cart multi-tenant di Redis, milik user login.
6. Checkout atomik multi-tenant, order induk + sub-order, snapshot, idempotency.
7. Payment gateway contract + QRIS sandbox.
8. Webhook idempoten + settlement + split komisi.
9. Double-entry ledger 5 akun, append-only.
10. Fulfillment + pengiriman + refund + hold release.
11. Realtime status pesanan (Reverb, private channel `user.{id}`) + **WhatsApp notifikasi via Fonnte** + Web Push opt-in.
12. Laporan dari ledger + withdrawal + export CSV/XLSX/PDF.
13. Deployment staging: Docker Compose + MariaDB + Redis + queue worker + Reverb.

**Ditunda:**

> Keputusan kanal notifikasi: **WhatsApp** via Fonnte (kontrak + fake adapter, kanal notifikasi status order) dan **Web Push** sebagai kanal kedua, keduanya terikat `user_id`. Identitas pembeli = **akun login** (tanpa sesi anonim/tracking token — tabel `anon_sessions`/`tracking_tokens` tidak ada di skema). XLSX/PDF diadopsi via job queue (materi ekspor-asinkron modul tetap tercakup).

- Refund otomatis, refund sebagian, refund lintas toko dalam satu pengajuan.
- OTP/verifikasi nomor WA; opt-out notifikasi per user; balasan dua arah WA.
- Pre-order.
- Konversi satuan (mis. 1 kardus = 24 pcs) — satu produk hanya punya satu satuan jual; konversi = resolver qty ganda + snapshot price-per-what baru.
- Kedaluwarsa/FEFO untuk bahan pangan — butuh sistem rotasi stok (batch, picking order); di luar scope MVP.
- QR meja per meja.
- Multi-provider payment.
- Outbox penuh lintas domain (outbox minimal untuk broadcast tetap dibangun).
- Akuntansi penuh: tutup buku, pajak, multi-currency, neraca lengkap.

## 14. Timeline 14 minggu

| Mg  | Fokus                                                   | Output                                                                                                                                                          |
| --- | ------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | Setup toolchain, Laravel 13, Livewire 4, MariaDB, Redis | Repo bersih, health check, Reverb terpasang & terhubung                                                                                                         |
| 2   | Modular monolith, auth, layout 3 konteks                | Struktur modul, route group, registrasi + login customer (name/email/password/phone), middleware role, layout customer/tenant/admin                             |
| 3   | Migrasi baseline + tabel ledger                         | Tabel inti + 5 akun + ledger_entries + balance_projection                                                                                                       |
| 4   | Isolasi multi-tenant                                    | TenantContext, scope, policy, test isolasi                                                                                                                      |
| 5   | Admin hub + admin toko                                  | CRUD toko, role, komisi effective-dated, rekening toko (terenkripsi, workflow verified), tarif ongkir per kg, audit log tindakan admin                          |
| 6   | Katalog publik + katalog toko + tier harga              | CRUD produk (kategori, satuan jual, foto, deskripsi), 1 harga normal + maks 4 tier grosir, stok, berat, katalog publik                                          |
| 7   | Cart multi-toko di Redis (milik user)                   | CartService (key per user), revalidasi, UI cart, gate login add-to-cart                                                                                         |
| 8   | Checkout atomik multi-toko                              | Order induk + sub-order (pemilik = user), ongkir per berat, deduksi stok + release saat expired, snapshot (termasuk satuan), idempotency, halaman riwayat order |
| 9   | Payment gateway + QRIS sandbox                          | Interface, fake + sandbox adapter, halaman QR, contract test sama untuk keduanya, auto-cancel expired (scheduler)                                               |
| 10  | Webhook idempoten + settlement + ledger posting         | Endpoint webhook, settle, posting double-entry                                                                                                                  |
| 11  | Fulfillment + pengiriman + refund + release hold        | Status pesanan, estimasi, posting refund (reversal), auto-selesai (scheduler), hold release, approve refund (pengaju = user)                                    |
| 12  | Realtime + notifikasi                                   | Reverb, channel tenant + `user.{id}`, Web Push opt-in (bind user_id), **WhatsAppChannel + FonnteGateway + fake adapter + notification worker**                  |
| 13  | Laporan dari ledger + withdrawal + export CSV/XLSX/PDF  | Dashboard, saldo, withdrawal, CSV + XLSX/PDF via job queue                                                                                                      |
| 14  | Hardening, test E2E, deployment staging                 | Test suite, runbook (termasuk catatan deviasi Docker Compose), demo script + known issues, deploy staging                                                       |

## 15. Keputusan yang masih terbuka

1. **Aturan posting ledger per event** — ✅ sudah tercakup di §5 (settled, hold release, withdrawal, payout, withdrawal ditolak, refund).
2. **Struktur modul `app/Modules/*`** — ✅ diputuskan: 6 modul mengikuti repo referensi dosen (blueprint); dituangkan di `direksi_pengerjaan.md` (dokumen pengerjaan, bukan produk).
3. **Skema route** — ✅ diputuskan: pola `PortalRoutes` repo dosen (4 grup terpusat: web/tenant/admin/customer); detail di `direksi_pengerjaan.md`.
4. **Daftar test wajib** — ✅ sudah tercakup di §12.
5. **Mekanisme dana balik refund** — ✅ diputuskan: Midtrans Refund API, partial refund per sub-order (lihat §7).
6. **Identitas pembeli** — ✅ diputuskan: pembeli terdaftar (login wajib untuk belanja), tanpa sesi anonim/tracking token; detail §2.
7. **Kanal notifikasi** — ✅ diputuskan: WhatsApp via Fonnte + Web Push, keduanya terikat `user_id`; detail §10.
