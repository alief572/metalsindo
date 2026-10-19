# Filter tanggal lima gudang

Tier 3 terbatas. WIP=2, FG=3, Scrap=4, Subcont=5, NCR=6. Stock Booking ditunda atas pilihan user: relasi customer/SPK tidak mempunyai snapshot yang terverifikasi.

- Satu tanggal YYYY-MM-DD; hari ini Asia/Jakarta menggunakan stock_material, tanggal lampau menggunakan batch MAX(tgl) pada tanggal yang sama dari stock_material_backup. Tidak ada fallback antarhari.
- Kriteria stok tetap: gudang sesuai whitelist, aktif=Y, sisa_spk>0 dan sisa_spk>COALESCE(delivery_book,0). Berat=sisa_spk*qty; NULL tidak diganti saldo lain. ID backup tidak digabung ke stock_material aktif.
- Endpoint baru data_warehouse_date (POST) dan export_warehouse_date (GET), permission Warehouse_material.View. Parameter stock_date, gudang, series, search[value], paging/order. Respons DataTables plus metadata sumber/snapshot seperti Raw Material.
- Kolom existing dipertahankan; tanggal ditambahkan terakhir. FG mempertahankan Total sheet dari totalweight / total_weight master (fallback divisor 1) hanya untuk bentuk sheet. Supplier, bentuk dan customer memakai master sekarang; join master didedup sebelum pengayaan.
- Normalisasi tanggal/resolver dan serializer tabel memanfaatkan model Raw Material lewat subclass tanpa mengubah model/endpoint Raw Material. Agregasi satu scan; pagination sebelum pengayaan master material/bentuk.
- XLSX semua hasil, batch 1000, SQLite3 cache, teks eksplisit, tanggal stok dan filter; rincian snapshot tidak dicantumkan di header Excel. Tidak ada perubahan tabel/menu/permission/database live.
- UI mempertahankan desain lima halaman, memakai input tanggal dan grup tombol Terapkan/Reset/Export di kanan, status snapshot serta scoped CSS dan JS bersama; Booking mempertahankan view/JS/endpoint lama.

Acceptance: isolasi lima gudang, aktif Y saja, snapshot terakhir/hilang/kosong, validasi tanggal, width/search/sorting/paging/total, FG sheet, XLSX lengkap dan aman, RBAC; regresi Raw Material dan Booking tetap lulus. Uji PHP5.6/JS/CLI temporary tables, EXPLAIN/latensi lokal <3 detik; browser tidak diwajibkan.
