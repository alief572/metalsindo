# 02 — Pemetaan ke kode aplikasi

Dibaca dari kode (tanpa perubahan) pada 2026-11-26. Nomor baris mengacu ke `application/modules/purchase_order_payment/controllers/Purchase_order_payment.php` kecuali disebut lain. Verifikasi ulang nomor baris di branch Anda karena file bisa berubah.

## A. Kondisi sekarang

**Alur Receive Invoice (modul `purchase_order_payment`)**

| Fungsi | Baris | Perilaku sekarang |
|---|---|---|
| `index` / `views/index_incoming.php` | 48 | Radio **dp** dan **pro** aktif; **inc** dan **ret** dikomentari. Konten panel dimuat lewat AJAX `checkbx`. |
| `checkbx` | 65 | Daftar per tipe: `dp` → `tr_top_po.group_top = 76`; `pro` → `77`; `ret` → `78`; selain itu (incoming) → `tr_invoice_po` dengan `no_po LIKE '%TR%'`. Hanya PO `status = 2` di `tr_purchase_order_non_material`. |
| `req_app` | 143 | Form DP/Progress/Retensi. Nilai diambil dari `tr_top_po` (persen = `progress`, `nilai`). PPN = `total_ppn` PO × persen. Membentuk baris jurnal (`$hasil_jurnal`) untuk ditampilkan di form. |
| `req_inc_app` / `check_list_inc` / `search_inc` | 690 / 1851 / 1320 | Form invoice berdasarkan incoming (dokumen `tr_incoming_check`, `warehouse_adjustment`). Pilihan dicatat di `tr_check_invoice`. |
| `save_invoice` | 915 | Satu fungsi untuk semua tipe (`tipe_req` = `dp` atau lainnya). Lihat B. |
| `search_dp`, `search_pro`, `search_ret` | 1464, 1589, 1721 | Data DataTables per tipe. |

**Pengaruh `save_invoice` (tipe `dp`)**
- Insert `tr_invoice_po`: `id` = `Pr_model::generate_no_invoice()` (format `PI-YYYY-MM-nnnnnn`), `no_po` = `no_surat` PO, `value_dp`, `persen_dp`, `total_pembelian`, `nilai_ppn`, `nilai_disc`, `total_invoice` (= `nilai_ppn` + `total_pembelian` dari form), `id_top`, data bank, `kurs`.
- Insert `tr_expense` (`status = 1`, `exp_inv_po = 1`, disetujui otomatis) dan `tr_expense_detail`; berbeda isi per `group_top` 76 / 77 / 98.
- Update `tr_purchase_order_non_material`: `uang_muka`, `uang_muka_idr`, `kurs_terima_invoice`, `po_inv_create = 1`.
- Insert `tr_jurnal` dari array `jurnal[]` pada form.

**Pengaruh `save_invoice` (tipe lain = Incoming / Progress)**
- Insert `tr_invoice_po` (`req_payment_po`, `total_invoice` dari form), `tr_expense` + detail, update `tr_incoming_check.inc_inv_create = 1`, `warehouse_adjustment.inc_inv_create = 1`, hapus baris `tr_check_invoice`, insert `tr_jurnal`.

**PO (modul `purchase_order_non_material`)**: TOP disimpan di `tr_top_po` (`group_top` = id `list_help` dengan `group_by='top'`: 76 uang muka, 77 progress, 101 pelunasan; 98 bernama "45 days"). Form `views/add_purchaseorder.php` baris ~974 dan `views/Edit.php` baris ~528 membangun dropdown group TOP.

**PR Departemen (modul `non_rutin`)**: `Non_rutin.php` baris ~228-239: `PRN` + `date('ym')` + urut 4 digit dibuat **hanya bila `tingkat_approval == '3'`** pada cabang approve. Sebelum itu PR dikenali dengan `no_pengajuan` (`PLN…`, kolom `varchar(10)`), dan daftar (baris ~778-781) menampilkan `no_pengajuan` merah bila `no_pr` kosong.

## B. Perubahan yang diminta (per titik)

### B1. Receive Invoice — hanya DP dan Incoming
1. `index_incoming.php`: hapus radio Progress; tampilkan radio **DP** dan **Incoming** (aktifkan yang dikomentari). Tambah pilihan "Daftar Invoice" bila ingin edit/hapus (Fase 2).
2. `checkbx`: hapus cabang `pro` dan `ret` (dan view `list_pro`, `list_ret`, `add_pro`, `add_ret`, `view_pro`, `view_ret`, `list_pro_inv` bila tidak lagi dipakai; tidak ada data lama Progress/Retensi di client ini (konfirmasi user 2026-11-26), jadi aman dihapus. Hapus juga pilihan group TOP Progress (77) dan Retensi (78) dari dropdown (`list_help`, grup `top`; boleh set `sts = 'N'` atau hapus barisnya)).
3. Cabang `dp`: sumber tetap `tr_top_po.group_top = 76`. **Satu PO satu invoice DP** (sekarang bisa banyak karena `SUM(persen_dp)`/`ttl_persen_dp`, `req_app` baris 158).
4. Cabang incoming: dasar nilai = incoming yang belum di-invoice; tampilkan Nominal Incoming, Potongan DP, Nilai Invoice.
5. `req_app` / `req_inc_app`: ganti hitungan PPN (`total_ppn` × persen) dengan fungsi pajak bersama (rumus di `01-spesifikasi.md` bagian 3). Tampilkan Nilai, DPP, PPn, Nilai Invoice.
6. `save_invoice`: **hitung ulang di server** (jangan memakai `total_invoice`, `nilai_ppn`, `value_dp`, `req_payment_po` dari form), validasi batas total per PO, isi kolom baru (`tipe_invoice`, `nilai_dasar`, `potongan_dp`, `nilai_dpp`, `total_invoice_idr`) dan tulis baris ke `tr_invoice_po_detail` per PO / incoming. Buang percabangan `group_top` 77 / 98.
7. Penyatuan hitungan potongan DP proporsional untuk incoming ada di `01-spesifikasi.md` bagian 3 dan 5.

### B2. TOP pada PO
- Form tambah dan edit PO: ganti tabel baris TOP dengan isian **DP (%)** + keterangan (opsional). Simpan satu baris `tr_top_po` dengan `group_top = 76` bila DP > 0; **tidak menyimpan baris Incoming/pelunasan** (sisa otomatis). Hapus pilihan `group_top` Progress dan Retensi dari dropdown. Validasi: 0 ≤ DP ≤ 100; tidak lagi wajib 100% total.
- Tampilan PO yang membaca TOP (view, print, approval) menambahkan baris Incoming otomatis (sisa persen).
- Nilai TOP yang tampil di layar PO (form, view, approval, print) **sebelum PPN**: round(persen × (Sub Total − Discount + Biaya Kirim) / 100), sama dengan Nilai DP di Receive Invoice (butir terbuka 3, sudah dijawab). Label kolom: "Value (sebelum PPN)".

### B3. Nomor PR Departemen tetap
- Opsi yang direkomendasikan: **bangkitkan `no_pr` (`PRN…`) pada penyimpanan PR baru** (cabang bukan approve), memakai logika `MAX(no_pr)` yang sama dengan baris ~232-239 lalu simpan di `rutin_non_planning_header.no_pr` dan detail. Cabang approve tingkat 3 **tidak lagi membuat nomor**; ia memakai `no_pr` yang sudah ada (baris ~228-239 diganti membaca nomor tersimpan) dan tetap mengisi tabel PR (`ArrHeaderPR`, `ArrDetailPR`) seperti sekarang.
- Pertahankan `no_pengajuan` sebagai kunci internal (kolom `varchar(10)` tidak muat `PRN` + `yymm` + 4 digit = 11 karakter). Tampilkan `no_pr` di semua layar (daftar baris ~778-781, view, print, approval).
- Hati-hati: seluruh kode yang menyaring PR dengan `no_pr IS NOT NULL` atau `<> ''` untuk menandai "sudah approved" akan berubah artinya. Cari penggunaannya sebelum mengubah (modul `metode_pembelian`, `purchase_order_non_material`, `incoming_departemen`, `non_rutin`). Gunakan status approval (`app_3`, `sts_app`) sebagai penanda approved.
- Penomoran harus aman dari duplikat saat dua pengguna menyimpan bersamaan (lihat C5).
- PR Stock: lihat butir terbuka 7.

### B4. Fase 2 — Request Payment otomatis (jangan mulai sebelum butir terbuka 5 dijawab)
- Invoice disimpan → buat satu baris `request_payment` (tipe baru `invoice_po`, `jumlah` = Nilai Invoice dalam Rupiah, `tanggal`/`tgl_rencana_bayar` wajib, `id_invoice_po`), status `open`.
- Checker dan Management butuh status tahap Checker dan Management. Skema HFG sudah punya (`open`, `approve checker`, `approve management`, `reject`, `finish`); skema Metalsindo (`status` smallint 0/1/2, hanya `app_checker*`) **perlu diperluas**. Tab Pembayaran PO ditambahkan pada daftar Checker/Management.
- Reject: simpan alasan dan operator (kolom alasan belum ada; lihat `002_*.sql`). Perbaikan membuat request baru dan menandai yang lama `superseded_by`.
- `request_payment/index` menjadi dashboard baca-saja (kartu status, jatuh tempo, matriks kategori × status) yang menggabungkan `request_payment` dan jalur PO lama (`purchase_order_request_payment(_nm)`).
- Dokumen non-PO (Kasbon, Expense, Transport, Periodik, PR Cash, Direct Payment) membuat request otomatis saat Approved di modul asal.
- Yang **tidak** boleh diteruskan dari kode lama: `tr_expense` dengan `status = 1` dan `exp_inv_po = 1` yang membuat dokumen langsung "approved" tanpa Checker/Management.

## C. Temuan teknis di `save_invoice` (perbaiki sambil lewat)

| No | Temuan | Baris |
|---|---|---|
| 1 | Total dan pajak dihitung di klien dan dipercaya server (`total_invoice`, `nilai_ppn`, `value_dp`, `req_payment_po`). | 953-969, 995-1001 |
| 2 | Nomor PO disambung langsung ke string SQL (`IN ('…')`) → risiko SQL injection. Gunakan `where_in`. | 979, 1174 |
| 3 | `foreach ($arr_nm_supplier …)` mengisi array dari dirinya sendiri (selalu kosong) sehingga nama supplier pada `tr_expense` kosong. | 984-988 |
| 4 | Transaksi dibuka (`trans_begin`) tetapi `throw new Exception` tidak ada `try/catch`, sehingga transaksi bisa menggantung dan respons JSON tidak terkirim. | 928, 975, 1017, … |
| 5 | `generate_no_invoice` memakai `MAX(id)+1` tanpa kunci: dua simpan bersamaan bisa bentrok (`id` adalah primary key). | `models/Pr_model.php` 66 |
| 6 | Upload `allowed_types = '*'` dan `max_size` sangat besar. Batasi tipe (pdf, jpg, png) dan ukuran. | 919-923 |
| 7 | Dua nama field berbeda untuk nomor dokumen (`nomor_po` dan `no_po`) dipakai di cabang yang sama. | 936, 1174 |
| 8 | ID `group_top` untuk Retensi tidak konsisten: 78 di `checkbx`, 98 di `save_invoice`. Di `metalsindo_live`, `list_help` grup `top` berisi 76 uang muka, 77 progress, 78 retensi; 98 tidak ada. | 112, 1133 |
| 9 | `tr_expense` dibuat dengan `status = 1` dan disetujui oleh pembuat sendiri (`approved_by` = pembuat). Melewati approval. | 1029-1049 |

## D. Tidak diubah pada Fase 1

Jurnal (`Jurnal_model`, `tr_jurnal`), modul Payment (`pembayaran_material`), PO Material (`purchase_order`). Keduanya hanya perlu diketahui terdampak bila nominal invoice berubah.
