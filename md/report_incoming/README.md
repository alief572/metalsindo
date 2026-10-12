# Report Incoming

Modul read-only dengan 19 kolom sesuai konsep Excel. Satu detail Incoming material supplier = satu baris. Tampilan mengikuti Report LHP Slitting, memakai CSS scoped khusus dan font Geist lokal. Kontrak lengkap pada [SPEC.md](SPEC.md).

## Penggunaan dan angka

- Halaman `report_incoming`; POST `report_incoming/get_data`; GET `report_incoming/export_excel`. Ketiganya membutuhkan `Report_Incoming.View`.
- Filter Date Incoming mulai/sampai opsional dan inklusif, pencarian literal PR/PO/supplier/material/Incoming/PIC/SJ/gudang/lot/customer. Awal/Reset semua periode. Default 25 baris, tanggal Incoming terbaru, lalu ID detail ascending; pilihan 10/25/50/100. Export memakai filter yang sudah diterapkan dan sorting aktif, seluruh hasil, bukan halaman aktif.
- Order dari `dt_trans_po.totalwidth`; receive terkini dari `berat_terima`; balance = order - receive. Filter tanggal tidak menghitung ulang progress PO. Total/balance berulang di setiap lot item PO yang sama dan tidak boleh dijumlahkan antarbaris. Tidak ada grand total.
- Berat per lot = `dt_incoming.width_recive`, bukan actual_berat; Width = detail.width. Status mengikuti close_po N/Open, Y/Closed, bukan balance. Contoh order 264, receive 265, balance -1 tetap Open.
- PIC/SJ dicocokkan memakai id_data, nomor Incoming, tanggal detail dan supplier PO. Header identik digabung; informasi berbeda/tidak cocok = 'Belum terverifikasi'. Master/PO yang hilang tidak menghilangkan detail. PO lookup ambigu dikosongkan, bukan dipilih sembarang.
- Customer berasal dari stok dengan ID Incoming + item PO + material + lot, termasuk deleted/nonaktif. Nama unik diurutkan dan digabung '; '. Customer bisa berubah mengikuti stok. SET SESSION group_concat_max_len=16777216 hanya mengubah setting koneksi, tidak menulis tabel bisnis.
- Tanggal tidak valid/NULL kosong; nol tetap angka; angka 2 desimal. XLSX menyimpan nomor/lot sebagai teks literal (termasuk awalan '='), angka/date sebagai tipe Excel, judul/periode, freeze F4, autofilter dan catatan di luar data.

## Pemasangan lokal dan rollback

Menu sudah dipasang **hanya pada Docker metalsindo_dev**, tepat setelah Kartu Stock dalam Warehouse Material. Installer tidak membaca app-config; menolak database lain dan host nonlokal. Config database aplikasi tidak diubah. Karena localhost saat ini memakai DB nonlokal `metalsindo_live`, menu baru belum muncul di aplikasi dengan konfigurasi tersebut. Pemasangan DB aktif adalah pekerjaan terpisah.

```powershell
./scripts/install_report_incoming_local.ps1 -Action apply
./scripts/install_report_incoming_local.ps1 -Action rollback
```

Wrapper membaca credential container metalsindo_db di memori, tidak menyimpan/mencetak password. CLI manual memakai REPORT_INSTALL_HOST/USER/PASSWORD/DATABASE dan PORT opsional, hanya host loopback/Docker yang diizinkan dan DATABASE=metalsindo_dev.

Registrasi transaksional, tanpa ID menu tetap, memverifikasi parent Warehouse Material, ownership marker dan engine InnoDB. Reapply idempotent. Anchor hilang/duplikat, orphan permission atau ownership berbeda ditolak. Rollback hanya melepas registrasi modul dan grant-nya; permission yang dipakai menu lain tidak dihapus. Hindari mengubah administrasi menu bersamaan dengan pemasangan.

Administrator mendapat akses melalui bypass Auth existing; tidak ada grant non-admin otomatis. Pengguna non-admin dapat diberi Report_Incoming.View. Generator sidebar existing juga membutuhkan permission parent Warehouse Material agar submenu terlihat; tanpa parent permission pengguna dengan permission report masih dapat mengakses URL report secara langsung.

## Pemeriksaan dan checkpoint

```powershell
./scripts/test_report_incoming.ps1 -Test all
./scripts/test_report_incoming.ps1 -Test capacity
./scripts/report_incoming_checkpoint.ps1 -Mode verify -Phase 6
```

`all` menjalankan bootstrap, model, benchmark, controller, XLSX, migration, sidebar, acceptance dan mutation. UI syntax/state dijalankan melalui bundled Node.js pada `tests/report_incoming/ui_syntax.mjs` dan `ui_state.mjs`; state harness tidak menggunakan browser. Capacity terpisah karena berat. Fixture hanya CREATE TEMPORARY TABLE dalam koneksi lokal, otomatis hilang saat test selesai. Acceptance tambahan menggunakan SELECT-only pada snapshot lokal dan result API CI mysqli sebenarnya. Mutation memakai salinan class dalam memori, tidak mengubah file model.

Enam checkpoint MD/JSON mencatat perubahan/alasan, pemeriksaan, risiko dan SHA256 cumulative. Verifikasi baseline terbaru sebelum fase berikutnya. Snapshot fase lama dapat berbeda setelah perubahan terencana; Fase 6 adalah baseline final. Ini checkpoint file, bukan commit Git.

## Kapasitas dan batasan

- Benchmark fixture: 36007 detail + 36000 stok + 3600 lookup; count 4 ms, page 537 ms, search count 321 ms, search page 406 ms saat Fase 2. Batas test 3 detik per operasi.
- Export 36000 baris/684000 sel: 107.09 detik, peak PHP heap 232.5 MiB pada limit 512M, XLSX 2.07 MiB. Metrik PHP tidak mencakup alokasi native SQLite. Ukuran data/mesin dan request bersamaan memengaruhi waktu/RAM.
- Cache sel SQLite3 bawaan PHPExcel menghindari OOM DiscISAM pada stress test. Runtime membutuhkan extension SQLite3 (sudah tersedia di Docker existing). Writer XML memakai file temporary dan query export dibaca melalui generator. Tidak membuat workbook lewat CI loader sebelum cache dikonfigurasi.
- Export seluruh periode bisa lebih lama daripada timeout HTTP/server/proxy. Batasi rentang tanggal untuk export operasional; timeout tidak diubah dalam modul ini. Stress test kapasitas melalui CLI, bukan browser/HTTP.
- Audit sumber sebelum implementasi menemukan 2974 item berbeda antara progress tersimpan dan sum detail serta 10 detail tidak cocok header; report mempertahankan sumber yang disetujui, tanpa koreksi data historis.
- Runtime existing mengeluarkan warning parser `custom.ini` saat PHP CLI dimulai. Modul menetapkan timezone Asia/Jakarta; konfigurasi PHP existing tidak diubah.
- Tidak ada pengujian browser, input/edit penerimaan, PDF, schema/index bisnis baru, perubahan modul Incoming/Kartu Stock/LHP Slitting, atau pemasangan nonlokal.
