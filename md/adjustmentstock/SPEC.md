# Adjustment Stock: Qty dan Berat

Status: diimplementasikan berdasarkan rencana pengguna, Tier 3. Timezone Asia/Jakarta.

## Kontrak
- SavePlus POST tetap memakai total_qty (integer >= 0), total_jumlah_berat (kg, dua desimal >= 0), adjustment PLUS/MINUS/MUTASI, note wajib. Tidak boleh keduanya nol.
- PLUS lot existing memakai id_stock; lot baru memakai plus_lot=new, lotno, id_gudang, length, width. Bentuk, nama dan thickness diambil dari master.
- MUTASI memakai id_gudang_baru dan opsional id_stock_tujuan. Qty nol wajib tujuan existing dengan material/lotno sama. Gudang harus berbeda. Lot baru wajib qty dan berat positif.
- Respons tetap status/pesan, sukses menyertakan code. Endpoint form_options GET mengembalikan material, warehouses, lots dengan field whitelist, permission Add.
- Pilihan lot menampilkan semua lot bernomor, termasuk Qty nol/nonaktif, dengan alasan tidak tersedia. Lot tidak valid dinonaktifkan di pilihan; guard penyimpanan tetap memerlukan lot aktif dan saldo valid.

## Saldo dan cardinality
- Satu id_stock adalah identitas lot fisik; lotno sendiri tidak unik. Lookup material/bentuk/gudang harus tepat satu, bukan join yang menggandakan hasil.
- totalweight adalah saldo gross; saldo operasional = sisa_spk * qty. Keduanya berubah sebesar delta kg yang sama. weight = gross akhir / qty akhir; sisa_spk = operasional akhir / qty akhir.
- Kolom per Qty DOUBLE(40,2): tolak hasil yang tidak dapat mewakili total secara tepat sampai sen kg. Tidak mengubah skema atau membulatkan total secara diam-diam.
- Saldo awal NULL/negatif, qty noninteger, saldo negatif, qty nol dengan berat positif, atau saldo per Qty tidak representable ditolak. Lot kosong dipertahankan nonaktif (tanpa menghapus referensi historis).
- MINUS/MUTASI menolak source dengan booking atau delivery_book positif; PLUS menjaga total kg booking yang ada saat qty berubah. Target mutasi existing harus kompatibel material, lotno, dimensi, bentuk dan kepemilikan/customer; metadata lain tidak ditimpa.
- warehouse_stock harus tepat satu per material/gudang; baris ganda ditolak, bukan dijumlahkan/diedit serampangan. Saldo negatif/NULL ditolak. Baris belum ada hanya boleh dibuat untuk penambahan.
- warehouse_history mencatat perubahan kg, saldo awal/akhir, kode transaksi, user, timestamp; mutasi memiliki baris (-) dan (+). adjustment_stock mencatat delta qty/kg dan snapshot source. ID target dicatat dalam note mutasi karena tidak ada kolom khusus.

## Atomicity dan kompatibilitas
- Semua write dalam satu transaksi; lock lot dan saldo gudang FOR UPDATE. GET_LOCK per database serializes adjustment saves, melindungi MAX sequence dan pembuatan saldo baru tanpa unique index. Timeout menghasilkan pesan retry.
- Query bind params, metadata server-side, permission Add untuk save/options/modal, View untuk riwayat. Tidak mengubah izin/menu, system/, data historis atau live DB.
- UI AdminLTE/Geist, antislop during; scoped CSS, kontrol >=44px, kontras teks >=4.5 dan border >=3. Tidak ada export baru sehingga gate XLSX/menu tidak berlaku.

## Acceptance
Regresi CLI memakai TEMPORARY TABLE InnoDB metalsindo_dev, fungsi hitung, input invalid/forged, source/target salah, booking, saldo, rollback, ledger, dan lock serialization. PHP lint di metalsindo_web, JS syntax dan regresi state form. Regresi browser terisolasi memakai fixture sintetis dan memblokir endpoint aplikasi/live; memeriksa DataTables/Select2/Bootstrap, keyboard, payload, responsive layout dan lebar tabel. Tidak menjalankan pengujian pada halaman aplikasi dengan data produksi. Checkpoint merekam hasil dan batas precision/duplikasi.
