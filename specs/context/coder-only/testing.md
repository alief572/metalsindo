# Testing — database uji dan aturan DDL

Berlaku untuk pekerjaan kode (mulai FEAT-11 Claim Material, 2026-10-28).

## Database
- **Hanya `metalsindo_live` di MySQL lokal** (Laragon, MySQL 8.0.30, user `root` tanpa password, host `localhost`). Itu database yang ditunjuk `application/config/database.php` dan merupakan salinan lokal milik Rama (keputusan user 2026-11-26). Jangan terhubung ke server lain atau database produksi mana pun.
- Pastikan nama database di setiap perintah adalah `metalsindo_live` sebelum menjalankan apa pun. `metalsindo_dev` (kosong) dan `hfg_dev` (skema HFG, dipakai FEAT-11 Claim Material) bukan target kerja baru.
- Klien MySQL: `C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe -uroot metalsindo_live`.
- Agent hanya menjalankan query **baca-saja** (SELECT, SHOW, `information_schema`). Tidak ada INSERT / UPDATE / DELETE / DDL oleh agent.
- Catatan skema (2026-11-26): `metalsindo_live` belum memiliki tabel dasar modul pengadaan (`tr_invoice_po`, `tr_top_po`, `tr_purchase_order`, `tr_expense`, `tr_incoming_check`, `warehouse_adjustment`, `tr_jurnal`). Pembuatannya diurus programmer. Tabel yang ada berbeda charset (mis. `request_payment` latin1, `rutin_non_planning_header` utf8mb4_unicode_ci); periksa sebelum menulis join atau DDL.

## Aturan DDL (keputusan user 2026-10-28)
- **Tabel baru atau kolom baru: cukup tulis query-nya** sebagai file migrasi di `application/modules/<modul>/migrations/NNN_*.sql` (+ `.down.sql`). **Jangan dieksekusi**; user yang menjalankan.
- (Historis, FEAT-11 pada `hfg_dev`, tidak berlaku lagi:) pengecualian satu kali untuk kolom `kulit`, `clamp_ring`, `status_proses` pada `warehouse_stock_coil`. Tidak ada migrasi di repo untuk kolom itu.
- Jangan menjalankan migrasi lama yang berisi `DROP TABLE` (mis. `unpack_coil/001`) terhadap database mana pun tanpa izin eksplisit.

## Pengujian
- Belum ada infrastruktur uji PHP (tidak ada phpunit/CI3 test). Verifikasi dilakukan dengan skrip SQL baca-saja dan walkthrough manual di aplikasi (`metalsindo_live` lokal).
- Cek kesesuaian nama kolom migrasi dengan ERD: bandingkan kolom `CREATE TABLE` dengan entitas di `docs/diagrams/erd-revisi.drawio` (grup "Claim Material").
- Data contoh (historis, FEAT-11, `hfg_dev`): 268 coil di `hfg_dev` punya rantai lengkap `warehouse_stock_coil.no_ros` → `tr_ros_header.id` → `no_po`/`id_supplier` → `tr_purchase_order` (mis. coil `26011114A` → PO-001/MP/01/2026).
