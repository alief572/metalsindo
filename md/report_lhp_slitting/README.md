# Report LHP Slitting

Modul read-only LHP Slitting Finishgood, sesuai 19 kolom tab `Report LHP Slitting (Final)`. Menu Production berada tepat setelah Progress Order. Akses awal hanya Administrator, menggunakan bypass admin pada Auth existing; grup/pengguna lain dapat diberikan `Report_LHP_Slitting.View` lewat pengaturan permission.

## Perilaku

- Halaman: `report_lhp_slitting`; data tabel: POST `report_lhp_slitting/get_data`; export: GET `report_lhp_slitting/export_excel`.
- `start_date`, `end_date`: opsional, YYYY-MM-DD, batas inklusif; `search`: pencarian literal nomor SPK produksi/marketing, customer, product, lot slitting. Filter awal/reset = semua periode.
- DataTables `draw`, `start`, `length`, `order[0][column]`, `order[0][dir]`; panjang halaman dibatasi 1–100. Default 25, tanggal terbaru, kemudian ID detail ascending. Sorting satu kolom. Export seluruh hasil dengan filter yang sudah diterapkan dan sorting aktif.
- Tanggal memakai `date_production` yang valid, fallback `tgl_produksi` yang valid. Jika keduanya tidak valid, tanggal kosong. Tampilan tanggal dd-mm-yyyy; angka dua desimal. NULL adalah em dash di halaman dan kosong di Excel, berbeda dari nol.
- Satu `dt_spk_aktual_detail_fg` = satu baris. Header `tr_spk_aktual_fg` harus cocok pada `id_spk_aktual` dan `id_dt_spkaktualdetail`; data tanpa pasangan tidak ditampilkan. Tidak ada filter approval.
- Width Slitting = detail.weight; Weight Slitting = detail.width; Actual Weight Slitting = detail.totalaktual, sesuai keputusan pengguna untuk mempertahankan mapping lama. Customer menggunakan nama tersimpan, termasuk NONSPK. Product menggunakan detail.nmmaterial, fallback header.nama_material bila kosong.
- Summary dan penimbangan memakai nilai header tersimpan yang diulang pada setiap detail LHP. Tidak ada grand total untuk mencegah penjumlahan ganda. Tidak ada penghitungan ulang/perubahan hasil produksi.

## Pemasangan / rollback

Jalankan dari CLI PHP 5.6 yang memiliki PDO MySQL. Set variabel `REPORT_INSTALL_HOST`, `REPORT_INSTALL_USER`, `REPORT_INSTALL_PASSWORD`, `REPORT_INSTALL_DATABASE` dari konfigurasi database lingkungan tujuan; jangan simpan kredensial dalam repository.

```sh
php scripts/register_report_lhp_slitting.php apply
php scripts/register_report_lhp_slitting.php rollback
# Ikuti konfigurasi yang dipakai aplikasi (base + override environment), tanpa menyalin kredensial:
php scripts/register_report_lhp_slitting.php apply --app-config=development
php scripts/register_report_lhp_slitting.php rollback --app-config=development
```

Script mensyaratkan tabel menus, permissions, group_permissions, user_permissions menggunakan InnoDB. Pemasangan menggunakan transaksi dan posisi menu Progress Order, tanpa ID tetap. Tidak membuat tabel/index produksi baru. Apply berulang tidak menggeser menu lagi. Script menolak anchor hilang/duplikat, menu yang tidak sesuai, atau permission yang sudah ada tanpa menu agar tidak mengambil alih konfigurasi existing.

Rollback menghapus registrasi milik modul dan grant report yang diberikan kemudian, serta menurunkan kembali urutan menu setelahnya. Rollback menolak permission yang digunakan menu lain atau marker kepemilikan yang berubah. Jalankan pemasangan/rollback ketika administrasi menu tidak sedang diubah. Modul sudah dipasang pada database Docker lokal saat Fase 4.

## Pemeriksaan

Set `REPORT_TEST_HOST`, `REPORT_TEST_USER`, `REPORT_TEST_PASSWORD`, `REPORT_TEST_DATABASE`. Akun fixture memerlukan SELECT dan CREATE TEMPORARY TABLES. Fixture menggunakan struktur tabel target dan membuat tabel temporary yang hanya hidup dalam koneksi test; data produksi tidak diubah.

```sh
php tests/report_lhp_slitting/excel.php
php tests/report_lhp_slitting/migration.php
php tests/report_lhp_slitting/acceptance.php
php tests/report_lhp_slitting/existing_data.php
php tests/report_lhp_slitting/mutation.php
node tests/report_lhp_slitting/ui_syntax.mjs
# Pemeriksaan SELECT-only pada database development aplikasi aktif, bukan fixture Docker:
php tests/report_lhp_slitting/sidebar.php
```

Excel test mencakup model/controller, write/read XLSX, angka/tanggal/text/NULL, kelompok header, export terfilter/sorted, empty export, dan permission pada ketiga endpoint. Migration test mencakup apply/reapply/rollback dan penolakan anchor hilang. Acceptance memeriksa semua mapping dengan angka berbeda, tanggal mustahil, input kontrol malformed dan hasil rendering PHP. Existing-data menggunakan koneksi kedua dengan query SELECT untuk memeriksa count/page/export dan satu sampel sumber aktual. Mutation test melemahkan kondisi join pada salinan class di memory dan memastikan assertion regression gagal, tanpa mengubah file model. Inline JavaScript diperiksa sintaksnya melalui Node. Browser tidak diuji sesuai permintaan pengguna.

## Checkpoint fase

Folder `checkpoints` berisi catatan dan manifest SHA256 setiap fase. Sebelum fase baru, checkpoint fase sebelumnya dibaca ulang dan semua hash-nya dicocokkan.

```powershell
./scripts/report_lhp_slitting_checkpoint.ps1 -Mode verify -Phase 7
```

Checkpoint bersifat snapshot pada akhir fase. Perubahan controller pada Fase 3 menggantikan versi Fase 2; perbaikan footer Excel pada Fase 5 menggantikan versi exporter/test pada Fase 3. Manifest Fase 5 menjadi baseline lengkap hasil akhir. Verifikasi snapshot lama setelah perubahan lanjutan yang disengaja dapat menemukan hash berbeda; lihat catatan fase yang lebih baru. Checkpoint ini berupa file dokumentasi dan hash, bukan commit Git.

## Risiko / batasan

Fase 7 memperbarui UI mengikuti warna Production dan panel report sekitar: stylesheet khusus dengan font Geist lokal, filter responsif, tombol berikon SVG, kelompok header, angka rata kanan, informasi hasil dan catatan pembacaan. Seluruh CSS dibatasi pada `.lhp-report`. Filter, sorting, pagination dan export tetap menggunakan alur yang sama; label kontrol tabel menggunakan bahasa Indonesia. Checkpoint Fase 6 sudah diverifikasi sebelum perubahan; Fase 7 menjadi baseline terbaru, menggantikan snapshot view dan dokumentasi sebelumnya. Tampilan visual belum diuji melalui browser sesuai permintaan pengguna.

Koreksi setelah Fase 5: pemasangan awal hanya dilakukan di Docker `metalsindo_dev`, sedangkan aplikasi localhost menggunakan override development ke `metalsindo_live`. Gunakan `--app-config=development` untuk target aplikasi tersebut. Checkpoint Fase 6 mencatat perbaikan target pemasangan dan verifikasi sidebar backend; menjadi baseline file terbaru. Konfigurasi database aplikasi tidak diubah.

- Export semua periode menggunakan memory sebanding jumlah baris karena PHPExcel existing. Untuk volume sangat besar, persempit tanggal; belum ada streaming exporter baru.
- Nilai historis yang kosong/salah tetap mengikuti sumber; report tidak melakukan koreksi data. Label Weight Slitting mempertahankan arti field lama sesuai pilihan pengguna.
- Tidak ada PDF, input/edit LHP, atau grand total. Tidak ada pengujian browser maupun otomasi Microsoft Excel native; XLSX diverifikasi melalui roundtrip PHPExcel.
- Perubahan lain yang sudah ada di workspace tidak termasuk pekerjaan modul ini.
