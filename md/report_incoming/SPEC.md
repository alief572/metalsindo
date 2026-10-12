# Report Incoming — kontrak implementasi

Disetujui 12 Oktober 2026. Modul read-only Incoming material supplier, satu `dt_incoming.id_dt_incoming` per baris. Incoming Customer dan PO tanpa penerimaan tidak termasuk.

| # | Kolom | Sumber |
|---|---|---|
| 1 | # | Urutan hasil, meneruskan offset halaman |
| 2 | No. PR | tr_purchase_order.no_pr |
| 3 | No. PO | tr_purchase_order.no_surat |
| 4 | Tanggal PO | tr_purchase_order.tanggal valid |
| 5 | Supplier | master_supplier.name_suplier berdasarkan supplier PO |
| 6 | Material | dt_incoming.nama_material |
| 7 | Width | dt_incoming.width |
| 8 | Qty Order (Kg) | dt_trans_po.totalwidth |
| 9 | Date Incoming | dt_incoming.tgl_datang valid |
| 10 | Kg / Lot Number | dt_incoming.width_recive, bukan actual_berat |
| 11 | Total Qty Receive (Kg) | dt_trans_po.berat_terima terkini |
| 12 | Balance | totalwidth - berat_terima; NULL tetap NULL |
| 13 | Status | close_po N = Open, Y = Closed, selainnya kosong |
| 14 | No. Incoming | dt_incoming.id_incoming |
| 15 | PIC | Header Incoming terverifikasi.pic |
| 16 | No. Surat Jalan (Supplier) | Header Incoming terverifikasi.sj_supplier |
| 17 | Gudang | ms_gudang.nama_gudang melalui dt_incoming.id_gudang |
| 18 | Lot.No | dt_incoming.lotno |
| 19 | Customer | stock_material.customer; beberapa nama berbeda digabung |

Header dipadankan dengan id_data, id_incoming, supplier PO, dan tanggal detail mentah (termasuk tanggal nol untuk identitas historis). Header yang sama pada semua informasi relevan digabung. Tidak cocok atau PIC/SJ saling bertentangan = kedua kolom 'Belum terverifikasi'. NULL dan string kosong dianggap kosong, bukan konflik. Relasi stok memakai id_incoming = detail.id_data, no_po = detail.id_dt_po, id_category3 = detail.id_material, lotno = detail.lotno; stok deleted/nonaktif tetap termasuk. Tidak mengambil customer dari lot berbeda. Customer berbeda digabung unik, urut alfabet, separator '; '.

Tanggal tidak valid ditampilkan kosong, tanpa menebak tanggal lain. Master/PO yang hilang tidak menghilangkan detail. Relasi lookup harus unik sebelum join; lookup konflik tidak boleh menggandakan detail.

GET report_incoming; POST report_incoming/get_data (DataTables); GET report_incoming/export_excel. Semua membutuhkan Report_Incoming.View. Filter start_date/end_date opsional YYYY-MM-DD inklusif, search literal PR/PO/supplier/material/Incoming/PIC/SJ/gudang/lot/customer. Default semua periode, 25 baris, tanggal Incoming descending lalu ID detail ascending. Pilihan 10/25/50/100; whitelist sorting. Filter hanya memilih baris, tidak menghitung ulang progress PO. Export semua hasil dengan filter aktif dan sorting sama. No grand total; nilai PO yang berulang dijelaskan pada halaman dan Excel. Nilai NULL berbeda dari nol; angka 2 desimal; tanggal dd-mm-yyyy. Teks Excel harus explicit string agar nomor dan teks berawalan '=' tidak menjadi formula.

Menu Warehouse Material tepat setelah kartu_stock, Administrator saja pada pemasangan awal. Installer apply/reapply/rollback transaksional dan hanya metalsindo_dev di Docker lokal, tidak membaca konfigurasi aplikasi nonlokal.

## Baseline studi (SELECT/SHOW saja)

Contoh IC-120/MP-09/2026: PR P2600999, PO PO-MP/26/IX/0263, tanggal PO 2026-09-11, Incoming 2026-09-23, Width 380, Order 264, lot 265, total receive 265, balance -1, Open; PIC ASEP, SJ 001088/MICS/09/26, Gudang Finish Good, lot CPD0661B-230926-12380-1. Customer stok PT. TRI CENTRUM FORTUNA Plant II ( TCF ), berbeda dari sel kosong konsep.

35949 detail; 30 ID header berulang, 15 pasangan id_data/id_incoming berulang. Pencocokan supplier/tanggal menyisakan 10 detail tidak terverifikasi. Dua kunci lot stok memiliki beberapa customer. 2974 item PO berbeda antara progress tersimpan dan jumlah detail; report sengaja mengikuti progress tersimpan. Lookup PO materialized menurunkan prototype count relasi 17.552s ke 0.14s tanpa index bisnis baru. Target count/page lokal ~36000 detail maksimal 3s per operasi.

## Fase dan gate

1. SPEC, fixture koneksi lokal, script test/checkpoint; verifikasi schema dan target.
2. Model; regresi mapping, cardinality, filter, sorting, pagination, angka/tanggal serta benchmark.
3. Controller/UI; guard semua endpoint, JSON/rendering, validasi, JS tanpa browser.
4. XLSX; roundtrip, tipe, filter/sort, empty export, judul/periode, kapasitas.
5. Registrasi lokal; apply/reapply/rollback fixture lalu apply lokal dan sidebar nyata.
6. Acceptance/review, mutation, lint, dokumentasi dan checkpoint final.

Setiap gate menyimpan catatan, pemeriksaan, risiko/TODO dan SHA256 cumulative. Verifikasi baseline terbaru sebelum lanjut; gagal = perbaiki dahulu. Snapshot lama riwayat, bukan baseline terkini. Tidak otomatis commit, push, atau memasang pada DB aktif nonlokal.
