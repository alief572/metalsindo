# Receive Invoice AP: optimasi index (20 Oktober 2026)

Klasifikasi: Tier 1 untuk query index; skrip indeks database opsional, dengan preflight sebelum apply.

## Perubahan query

Query sebelumnya mengagregasi semua detail invoice dan relasi incoming/PO sebelum LIMIT. Versi baru mengambil header halaman aktif lebih dahulu untuk pembukaan index tanpa search dan sorting berbasis header. Nominal dan mata uang dihitung lewat lookup berdasarkan ID invoice tersebut. Jumlah query tetap dua pada pembukaan biasa: COUNT header dan query halaman.

Search tetap mencakup ID receive invoice, nomor invoice, supplier, nominal, dan simbol mata uang. Search serta sorting nominal harus mengevaluasi invoice di luar halaman aktif agar hasilnya benar; lookup tetap memakai ID invoice, tanpa agregasi global tabel detail. Total nominal dipisahkan dari relasi PO agar join tidak melipatgandakan jumlah. Konversi UTF-8 dan guard material/supplier dipertahankan.

## Hasil verifikasi

MariaDB 10.11 lokal sementara, PHP 7.4, koneksi UTF-8, tabel fixture TEMPORARY latin1. Fixture berisi 5.005 header dan 5.006 detail invoice beserta relasi incoming/PO. Benchmark memakai pemanasan satu kali dan median tiga pengukuran; hasil query dibandingkan dengan baseline sebelum optimasi.

| Skenario | Sebelum | Sesudah |
|---|---:|---:|
| Index 100 baris, fixture dan indeks sama | 4.720,73 ms | 20,38 ms |
| Search ID, pengukuran pembanding awal | 27.342,62 ms | 330,62 ms |

Pengujian akhir menggunakan indeks minimal yang disarankan: index 100 baris 26,85 ms, search ID 405,79 ms, search simbol 467,58 ms, sorting nominal 37,40 ms. Variasi angka berasal dari run terpisah. Semua di bawah target fixture 3 detik. EXPLAIN lookup amount_detail/d/incoming/inc/detail_po/po menggunakan `ref` dengan indeks, tanpa `ALL` pada tabel terkait.

Regresi mencakup search, total/filtered count, offset pagination, sorting angka, All entries, karakter wildcard literal, SQL injection, HTML escaping, PPN, mata uang kosong/campuran, dan simbol Unicode.

Angka fixture di atas terpisah dari pengukuran database aplikasi setelah apply, yang dicatat di bawah. Container PHP 5.6 dan alur browser belum diuji. Search luas tetap lebih mahal daripada pembukaan halaman biasa.

## Apply pada metalsindo_live lokal

Setelah preflight dan konfirmasi eksplisit user, lima indeks diterapkan pada `127.0.0.1:3307/metalsindo_live`, MySQL 5.7.44, server `c73f06b78ec4`, tanggal 20 Oktober 2026. Primary key header invoice dan PO sudah mencakup dua kandidat lain, sehingga tidak ditambahkan ulang.

Indeks yang ditambahkan: `idx_riap_header_created`, `idx_riap_detail_invoice`, `idx_riap_incoming_header`, `idx_riap_incoming_detail`, dan `idx_riap_po_detail`.

Percobaan pertama berhenti pada timeout metadata lock (1205); pemeriksaan sesudahnya memastikan belum ada indeks baru. Penyebabnya query SELECT daftar invoice, process ID 6, yang sudah berjalan lebih dari dua menit. Setelah diverifikasi sebagai SELECT modul ini dan transaksi tidak memodifikasi baris, hanya query itu dibatalkan menggunakan `KILL QUERY`. Tidak ada transaksi tulis atau koneksi lain yang dibatalkan. Apply ulang selesai dalam 240,55 ms.

Jumlah baris sebelum/sesudah identik: header invoice 62, detail invoice 561, incoming 2.760, detail incoming 36.346, detail PO 4.520, dan header PO 997. Tidak ada koreksi data transaksi. DDL memakai `ALGORITHM=INPLACE, LOCK=NONE`; tidak dibungkus transaksi karena ALTER TABLE MySQL melakukan implicit commit. Snapshot struktur/indeks awal dan dukungan rollback menjadi pengaman DDL.

Benchmark query model pada data aplikasi sebenarnya, melalui PHP CLI 7.4 dan koneksi read-only, median tiga run setelah pemanasan:

| Skenario | Median |
|---|---:|
| Index limit 100 (62 baris dikembalikan) | 31,37 ms |
| Search ID receive invoice | 118,63 ms |
| Search US$ | 46,82 ms |
| Sorting nominal | 21,34 ms |

EXPLAIN lookup detail menggunakan `ref` pada indeks baru; header PO menggunakan `eq_ref` pada PRIMARY. Pengulangan skrip mode apply tidak mengubah metadata indeks: pemeriksaan idempotensi lulus. Ini waktu query model, bukan pengukuran lengkap HTTP/browser.

Invoice `INV-AP-20241200004` masih mengembalikan `Rp / US$ 109,016.42` dari relasi PO yang saat ini tersimpan. Penambahan indeks tidak memilih satu mata uang atau mengubah data tersebut; penentuan sumber mata uang yang benar masih memerlukan pemeriksaan detail transaksi.

Audit:

- `audit/20261020-155012-live-local-index-preflight.json`: struktur dan indeks sebelum apply.
- `audit/20261020-155300-live-local-index-apply.json`: percobaan yang terkena metadata lock.
- `audit/20261020-live-local-blocking-select.json`: bukti pemeriksaan query baca yang dibatalkan.
- `audit/20261020-155413-live-local-index-apply.json`: apply berhasil dan verifikasi jumlah baris.
- `audit/20261020-155450-live-local-index-verification.json`: waktu query, EXPLAIN, dan idempotensi.

## Skrip MySQL opsional

### Paket untuk production

File mandiri: `scripts/production/receive_invoice_ap_indexes.sql`. File ini hanya memuat lima indeks yang dibutuhkan pada deployment lokal, memakai schema `metalsindo_live`, dan tidak bergantung pada file SQL lain.

1. Buka file pada SQL editor yang terhubung ke server production. Pastikan hasil `server_host`, `server_port`, dan `target_database` sesuai tujuan.
2. Jalankan **seluruh file** dengan baris `SET @riap_prod_mode = 'preflight';` tetap seperti semula. Simpan semua hasil `SHOW CREATE TABLE`, daftar indeks, dan rencana tindakan sebagai snapshot sebelum apply.
3. Review rencana dan panjang/tipe kolom pada production. Ubah baris mode menjadi `SET @riap_prod_mode = 'apply';`, lalu jalankan seluruh file lagi. Indeks setara dengan nama lain juga akan dilewati. Nama indeks migrasi yang sudah dipakai untuk definisi berbeda ditandai `BLOCKED`.
4. Periksa hasil akhir daftar indeks. Untuk rollback, cocokkan snapshot awal terlebih dahulu, lalu ubah mode menjadi `rollback` dan jalankan seluruh file. Rollback hanya menghapus lima nama indeks migrasi dengan definisi yang cocok. Jangan menjalankan rollback untuk indeks yang sudah ada sebelum deployment.

Semua tabel target harus tersedia dan menggunakan InnoDB. Skrip tidak membuat stored procedure atau tabel audit. Default preflight tidak menjalankan ALTER. Mode apply/rollback menggunakan `ALGORITHM=INPLACE, LOCK=NONE`; waktu tunggu metadata lock dibatasi 10 detik dan nilai session sebelumnya dipulihkan bila file selesai. Batas ini bukan batas durasi pembangunan indeks. Bila terjadi error/timeout, hentikan batch dan periksa perubahan yang sudah berhasil serta query aktif; jangan gunakan opsi client yang melanjutkan otomatis setelah error. DDL tidak transaksional.

Validasi file pada MySQL 5.7.44 lokal: preflight dieksekusi dalam koneksi read-only dan mengenali kelima indeks yang sudah ada; jalur apply/rollback diuji melalui PREPARE tanpa menjalankan ALTER; mode invalid ditolak; metadata indeks sebelum/sesudah identik. Tidak ada eksekusi ke server production.

### Skrip lokal/general sebelumnya

File: `scripts/optimize_receive_invoice_ap_indexes.sql`.

Skrip kompatibel sintaks MySQL 5.7. Tidak mengubah data transaksi atau collation tabel. Kandidat indeks hanya dibuat jika tidak ada indeks BTREE/full-column setara dengan urutan kolom awal yang dibutuhkan. Review tipe/panjang kolom dan kapasitas storage pada database tujuan sebelum apply; pembuatan indeks membutuhkan waktu dan metadata lock walaupun menggunakan `ALGORITHM=INPLACE, LOCK=NONE`.

Di mysql client dari root repository:

```sql
USE metalsindo_dev;
SOURCE scripts/optimize_receive_invoice_ap_indexes.sql;
```

Default adalah preflight read-only: tampilkan lingkungan dan indeks saat ini. Simpan output ini untuk audit. Setelah diperiksa, apply pada database lokal:

```sql
SET @riap_mode = 'apply';
SOURCE scripts/optimize_receive_invoice_ap_indexes.sql;
```

Apply melewati indeks setara dan hanya memproses tabel InnoDB. Skrip menolak perubahan bila database terpilih tidak cocok dengan `@riap_expected_database` (default `metalsindo_dev`). Apply pada `metalsindo_live` lokal telah dijalankan dengan `@riap_expected_database = 'metalsindo_live'` setelah persetujuan user. Deployment non-lokal tetap membutuhkan persetujuan terpisah.

Rollback indeks bernama `idx_riap_*` yang tercantum di skrip:

```sql
SET @riap_mode = 'rollback';
SOURCE scripts/optimize_receive_invoice_ap_indexes.sql;
SET @riap_mode = 'preflight';
```

Rollback tidak menghapus indeks dengan nama lain. Cocokkan dengan snapshot preflight: jangan menghapus indeks `idx_riap_*` yang sudah ada sebelum deployment. DDL MySQL tidak transaksional; bila sebagian apply gagal, periksa output sebelum melanjutkan.

Preflight skrip dan guard database sudah diuji. Apply dan pengulangan apply pada metadata tabel aplikasi lokal telah diverifikasi. Rollback tersedia tetapi tidak dijalankan karena indeks masih dibutuhkan.

Pengujian SQLite:

```text
php tests/regression/receive_invoice_ap_currency.php
```

Pengujian MariaDB mengharapkan server uji disposable pada 127.0.0.1:18762, root tanpa password, menggunakan tabel TEMPORARY dalam schema mysql. Tidak membaca kredensial database aplikasi:

```text
php tests/regression/receive_invoice_ap_currency.php --mysql-collation --benchmark
```
