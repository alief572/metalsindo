# Filter satu tanggal — lima gudang

## Hasil dan keputusan

- WIP (2), Finish Good (3), Scrap (4), Subcont (5), NCR (6) mendapat input tanggal, Terapkan, Reset, status waktu snapshot, kolom tanggal dan ekspor seluruh hasil.
- Hari ini memakai stock_material; lampau memakai batch terakhir di stock_material_backup pada tanggal yang sama. Aktif=Y saja, tanpa fallback antarhari. Total/pencarian/width/paging menggunakan tanggal dan gudang yang sama.
- FG mempertahankan Total sheet. NULL saldo tidak diganti totalweight. Master dikelompokkan agar stok tidak berlipat; agregasi satu scan dan LIMIT sebelum pengayaan material/bentuk.
- Stock Booking ditunda atas jawaban user. Stock_material_customer memiliki relasi customer/SPK tetapi backup relasinya tidak ditemukan di database lokal. ID backup stok bukan ID stok aktif dan tidak dipakai untuk menggabungkan relasi booking terkini ke snapshot.
- Model baru mewarisi normalisasi tanggal/resolver/serializer Raw Material, tanpa mengubah model Raw Material. Dua endpoint baru dibatasi Warehouse_material.View. XLSX memakai SQLite3 cache, batch 1000 dan string eksplisit; PCLZIP saat ZipArchive tidak ada.
- UI mengikuti panel/font/warna yang ada, kontrol 44px dan CSS scoped. Plugin ekspor client/CDN tidak lagi dibutuhkan pada lima halaman ini. Halaman Booking tetap menggunakan JS dan endpoint semula.
- Revisi akhir: tombol Terapkan dikelompokkan di kanan bersama Reset filter dan Export Excel. Header Excel Raw Material/lima gudang hanya memuat tanggal stok, tanpa teks atau waktu snapshot.

## Berkas

- Diubah: application/modules/stock_material/controllers/Stock_material.php (hanya dua endpoint baru), views/gudang_wip.php, gudang_fg.php, gudang_scrap.php, gudang_subcont.php, gudang_ncr.php.
- Ditambahkan di modul: models/Warehouse_stock_date_model.php, libraries/Warehouse_stock_date_excel.php, views/date_filter.php.
- Ditambahkan aset: assets/js/stock_material_date.js, assets/css/stock_material_date.css.
- Ditambahkan pengujian: tests/regression/warehouse_stock_date.php, warehouse_stock_date_performance.php, warehouse_stock_date_ui.js.
- Dokumentasi: SPEC_WAREHOUSE_DATE.md dan checkpoint ini. Perubahan workspace lain dipertahankan; tidak membuat branch/commit/push.

## Verifikasi

- PHP 5.6 lint seluruh 11 berkas PHP task: lulus.
- Regresi temporary-table: whitelist gudang; stok aktif/historis; batch terakhir; status N tidak terhitung; snapshot hilang dan snapshot tersedia tanpa stok; isolasi gudang; duplicate masters; width/customer/nama gudang/pencarian literal; total/paging/descending; FG sheet; RBAC dan HTTP 400: lulus.
- Ekspor kelima gudang: tanggal/timestamp, semua halaman, total, FG sheet, teks formula dan XML XLSX nyata: lulus. Batch 1001 baris dengan descending juga lulus.
- Node menjalankan handler JS yang dipakai kelima halaman: Terapkan, validasi tanggal masa depan, snapshot hilang, status timestamp, ekspor, Reset/debounce, error, state kolom lama; jumlah header/footer sesuai: lulus. Syntax JS lulus. Tidak melakukan browser test sesuai arahan user.
- Regresi Raw Material dan tests/stock_booking/regression.php: lulus.
- SELECT-only data lokal: aktif WIP 0.0214s, FG 0.0193s, Scrap 0.0192s, Subcont 0.0424s, NCR 0.0191s. Historis 11 Oktober: WIP 0.0241s, FG 0.0634s, Scrap 0.0087s, Subcont 0.0015s, NCR 0.0057s. Semua <3 detik; EXPLAIN historis memakai idx_raw_snapshot_date_warehouse.
- WIP lokal memiliki total berat terukur 0 untuk baris yang memenuhi kriteria; rumus lama sisa_spk*qty dipertahankan, tidak diganti totalweight.

## Batasan

- Tidak ada data/skema/indeks database live yang diubah oleh task ini. Angka performa di atas hanya Docker metalsindo_dev.
- Ketersediaan tanggal bergantung snapshot; data master supplier/bentuk/customer mengikuti master sekarang, seperti Raw Material.
- Booking membutuhkan sumber snapshot relasi customer/SPK yang benar sebelum filter historis dapat dibuat.

Perintah regresi: docker exec dengan BOOKING_DB_HOST=metalsindo_db dan kredensial lokal, php tests/regression/warehouse_stock_date.php; benchmark warehouse_stock_date_performance.php; node tests/regression/warehouse_stock_date_ui.js.

## Perbaikan kompatibilitas ekspor tanpa SQLite3

- Exporter Raw Material dan lima gudang memakai PHPTemp bawaan PHP, ambang cache sel 2097152 byte sebelum beralih ke temporary file. Pemilihan cache tetap dilakukan sebelum workbook dibuat. Tidak memasang ekstensi, mengubah php.ini, atau mengubah database/server.
- Regresi diperkuat untuk memeriksa metode cache PHPTemp. Kegagalan lama direproduksi dengan `php -d disable_classes=SQLite3`; setelah perbaikan, ekspor semua gudang dan Raw Material lulus pada proses CLI yang sama, termasuk batch lebih dari 1000 baris, total, tanggal, dan teks formula.
- Berkas perbaikan: dua libraries exporter, dua tes regresi, dua SPEC dan checkpoint ini. Header/format workbook, query, filter, permission dan formula safety dipertahankan.
