# Report Production Time

Modul baca saja pada `/report_production_time`, dengan permission `Report_Production_Time.View`.
Satu kelompok adalah satu SPK produksi yang memiliki progress di `dt_spk_produksi_lph`.
Info dan Summary ditampilkan sekali; pagination menghitung SPK, bukan tahap pekerjaan.

Tanggal adalah tanggal aktual valid terbaru dari LHP reguler dan finishgood (`date_production`, fallback `tgl_produksi`).
SPK tanpa tanggal aktual tetap muncul pada semua periode, tetapi tidak pada filter tanggal.
Marketing/customer berasal dari pasangan detail `checked=1`, tanpa menggandakan progress.
Dimensi/nama mother coil memakai stage input LHP yang cocok dengan `id_tr_spk_produksi`; fallback ke header SPK jika stage tidak tersedia.
Progress standar memakai sebelas ID tahap; baris tambahan/duplikat tetap dipertahankan sesudah tahap standar.

Total menggunakan menit tersimpan, bukan selisih jam. Kosong berbeda dari nol.
Nilai nonnumerik/negatif ditandai; seluruh summary SPK terkait menjadi tidak tersedia.
Report tidak menjumlahkan semua SPK menjadi total waktu mesin karena pencatatan LHP dapat disalin ke SPK yang memakai lot sama.

Tampilan mengikuti AdminLTE/warna report Metalsindo. Header tiga kelompok dan garis antar-SPK memperjelas hierarki;
scroll horizontal menjaga keterbacaan 18 kolom; Info/Summary menggunakan rowspan agar tidak terkesan sebagai total per tahap.
Tanpa penambahan font, aset, animasi, atau dependensi frontend.

## Pengujian CLI

Jalankan dari container web yang memasang repo ke `/var/www`:

```text
php /var/www/tests/report_production_time/unit.php
php /var/www/tests/report_production_time/controller_excel.php
php /var/www/tests/report_production_time/migration.php
php /var/www/tests/report_production_time/existing_data.php
php /var/www/tests/report_production_time/existing_data.php --export
php /var/www/tests/report_production_time/sidebar.php
node --check assets/js/report_production_time.js
```

Regression/controller/migration membuat tabel TEMPORARY di koneksi test; data aplikasi tidak diubah.
Pemeriksaan existing_data memakai SELECT pada database development.
Export lengkap hanya menulis file sementara dan menghapusnya selesai pemeriksaan.
XLSX diperiksa sebagai ZIP/XML untuk tipe nilai, formula-looking text, filter, dan angka hasil export.
Tidak ada pengujian browser.

## Registrasi dan rollback

```text
php scripts/register_report_production_time.php apply --app-config=development
php scripts/register_report_production_time.php rollback --app-config=development
```

Untuk deployment gunakan konfigurasi environment tujuan atau `REPORT_INSTALL_*` mengikuti skrip registrasi.
Apply/rollback transaksional dan idempotent, memakai Report Weight Production sebagai anchor.
Administrator mengikuti akses bawaan; role lain perlu diberi permission melalui pengaturan aplikasi.

## Batasan operasional

Export semua periode menggunakan PHPExcel dan dapat memerlukan waktu/memori besar; gunakan filter untuk export harian/bulanan.
Endpoint export mengizinkan waktu proses panjang. Jika ZipArchive tidak tersedia, exporter memakai PCLZip bawaan dan mengunci writer
modul ini untuk menghindari benturan nama file XML sementara saat beberapa export berjalan bersamaan.
Durasi legacy yang tidak valid perlu diperbaiki pada pencatatan LHP. Report tidak mengubahnya.
Progress tanpa header SPK yang cocok tidak dapat menjadi kelompok report; audit data sumber diperlukan untuk catatan orphan tersebut.
