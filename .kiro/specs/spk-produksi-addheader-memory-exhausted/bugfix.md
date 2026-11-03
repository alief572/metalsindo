# Bugfix Requirements Document

## Introduction

Halaman `spk_produksi/addHeader/` menghasilkan fatal error PHP "Allowed memory size of 134217728 bytes exhausted" ketika dibuka. Penyebabnya adalah pengambilan data stok material tanpa filter apa pun di dalam method `addHeader()` pada controller `application/modules/spk_produksi/controllers/Spk_produksi.php`.

Query `SELECT a.* FROM stock_material a` memuat SELURUH baris tabel `stock_material` ke dalam memori sekaligus. Saat tabel sudah besar, proses MySQLi menyusun result set melebihi batas memori PHP (128MB) sehingga halaman gagal dimuat dan menampilkan fatal error.

Method pembanding `addHeader_new()` pada file yang sama sudah menggunakan query yang terfilter (`WHERE a.totalweight > 0 AND id_gudang = 1`), sehingga hanya memuat stok yang relevan. Bugfix ini menyelaraskan query pada `addHeader()` agar memuat subset data yang relevan dan tidak lagi menghabiskan memori.

Dampak: pengguna tidak dapat membuka halaman tambah header SPK Produksi sama sekali selama tabel `stock_material` besar, sehingga proses pembuatan SPK Produksi terhenti.

## Bug Analysis

### Current Behavior (Defect)

Saat ini, method `addHeader()` menjalankan query `SELECT a.* FROM stock_material a` tanpa filter, memuat seluruh isi tabel `stock_material` ke memori untuk ditampilkan pada dropdown pemilihan stok (`#id_stock`).

1.1 WHEN halaman `spk_produksi/addHeader/` dibuka sementara tabel `stock_material` berisi banyak baris THEN sistem menjalankan `SELECT a.* FROM stock_material a` tanpa filter dan memuat seluruh tabel ke memori
1.2 WHEN penyusunan result set `stock_material` melebihi batas memori PHP (128MB) THEN sistem menghentikan eksekusi dengan fatal error "Allowed memory size of 134217728 bytes exhausted" di `mysqli_result.php` dan halaman gagal dimuat

### Expected Behavior (Correct)

Method `addHeader()` seharusnya hanya memuat stok material yang relevan untuk dropdown, yaitu stok dengan `totalweight > 0` dan `id_gudang = 1`, selaras dengan method `addHeader_new()`.

2.1 WHEN halaman `spk_produksi/addHeader/` dibuka sementara tabel `stock_material` berisi banyak baris THEN sistem SHALL menjalankan query stok yang terfilter (`WHERE a.totalweight > 0 AND id_gudang = 1`) sehingga hanya subset data relevan yang dimuat ke memori
2.2 WHEN jumlah baris `stock_material` yang lolos filter berada dalam batas wajar THEN sistem SHALL memuat halaman `addHeader` tanpa fatal error memory exhausted dan menampilkan dropdown pemilihan stok

### Unchanged Behavior (Regression Prevention)

Seluruh fungsi lain pada halaman `addHeader` serta method-method lain harus tetap berperilaku seperti sebelumnya.

3.1 WHEN dropdown pemilihan stok (`#id_stock`) dirender THEN sistem SHALL CONTINUE TO menampilkan setiap opsi menggunakan `id_stock` sebagai value dan `lotno` sebagai label seperti sebelumnya
3.2 WHEN sebuah header SPK Produksi sudah tersimpan dan direferensikan oleh `id` pada URL THEN sistem SHALL CONTINUE TO memuat data header, detail, dan stok terkait (`$header`, `$detail`, `$stok`) tanpa perubahan perilaku
3.3 WHEN method lain yang memuat stok seperti `addHeader_new()` dan `addHeader_view()` dipanggil THEN sistem SHALL CONTINUE TO berperilaku seperti sebelumnya tanpa terdampak oleh perubahan pada `addHeader()`
3.4 WHEN data stok dengan `totalweight > 0` dan `id_gudang = 1` tersedia THEN sistem SHALL CONTINUE TO menyediakan stok tersebut sebagai pilihan pada dropdown
