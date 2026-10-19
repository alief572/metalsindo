# Filter tanggal Stock Raw Material

Tier 3 terbatas. Hanya Gudang 1; gudang lain tetap memakai kontrak lama.

- Input `stock_date`: tanggal kalender YYYY-MM-DD, default hari ini Asia/Jakarta; tanggal masa depan/invalid ditolak (HTTP 400).
- Hari ini: stock_material. Tanggal lampau: batch MAX(tgl) dalam [tanggal 00:00:00, esok 00:00:00) dari stock_material_backup. Batch dipilih sebelum filter gudang/width/search agar snapshot kosong tidak tergantikan batch lama.
- Tidak ada batch: data kosong, metadata available=false, tanpa fallback. Ada batch tetapi stok tidak memenuhi filter: available=true.
- Grain satu baris stok sumber; ID backup tidak dikaitkan ke ID stok aktif. Kolom stok dari sumber, supplier/customer/bentuk dari master sekarang. Lookup master dikelompokkan satu baris per key untuk mencegah penggandaan.
- Kriteria aktif=Y, id_gudang=1, sisa_spk>0 dan sisa_spk>COALESCE(delivery_book,0). Berat=sisa_spk*qty. NULL tetap kosong, tidak diganti saldo lain.
- Tabel dan XLSX memakai resolver/filter/formatter yang sama. Respons DataTables lama ditambah stock_date, snapshot_at, available, source; tanggal menjadi kolom terakhir khusus Raw Material.
- recordsTotal sebelum width/search; recordsFiltered dan recordsAset setelah filter. Urutan deterministik ID stok; arah di-whitelist. Ekspor semua hasil secara batch dengan PHPTemp cache (ambang 2 MB sebelum memakai temporary file) dan teks eksplisit; tidak memerlukan SQLite3 atau perubahan konfigurasi server.
- Indeks backup (tgl,id_gudang), installer CLI preflight/apply/rollback hanya host lokal dan metalsindo_dev. Manifest mencatat indeks yang dibuat. DDL MySQL tidak dianggap transactional.
- UI input tanggal/Terapkan/Reset, status waktu snapshot, error inline; state kolom lama dibatalkan bila jumlah kolom berubah. Tanggal default tidak dipulihkan dari state lama.

Acceptance: hari ini, lampau, batch ganda, backup hilang/kosong, input invalid, filter, pagination, total, seluruh XLSX, formula safety, akses permission; PHP 5.6 lint, JS syntax, SQL EXPLAIN dan latensi <3 detik. Fixture temporary-only; tidak mengubah live. Tidak membutuhkan menu/permission baru.
