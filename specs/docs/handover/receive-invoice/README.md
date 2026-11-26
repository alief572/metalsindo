# Handover Programmer — Receive Invoice (DP / Incoming), TOP PO, Nomor PR

Tanggal: 2026-11-26. Sumber kebenaran perilaku: **mockup** di `specs/ui-mockup/` (jalankan dengan membuka `purchase_order_payment.html`). Dokumen ini menerjemahkan mockup ke perubahan pada aplikasi CodeIgniter 3 HMVC.

> Aplikasi asli **belum diubah** oleh siapa pun pada tahap ini. File SQL di folder `migrations/` hanya draf; tidak ada yang sudah dieksekusi di database mana pun.

## Isi paket

| File | Isi |
|---|---|
| `01-spesifikasi.md` | Aturan bisnis, rumus DPP/PPn, validasi, status, daftar layar. Baca pertama. |
| `02-pemetaan-kode.md` | Kondisi kode sekarang (file dan fungsi), perubahan yang diminta per fungsi, temuan teknis di kode lama. |
| `03-data-dan-migrasi.md` | Rancangan data, pemetaan kolom, urutan migrasi, cara verifikasi, rollback. |
| `migrations/001_*.sql`, `001_*.down.sql` | Fase 1: kolom dan tabel baru untuk invoice DP/Incoming. |
| `migrations/002_*.sql`, `002_*.down.sql` | Fase 2: kait ke `request_payment` (Request Payment otomatis). |
| `04-pengujian.md` | Vektor uji perhitungan dan pointer ke dokumen FAT. |

## Ruang lingkup dan fase

| Fase | Isi | Status keputusan |
|---|---|---|
| **1** | Receive Invoice hanya tipe **DP** dan **Incoming**; DPP dan PPn; TOP PO hanya DP; potongan DP proporsional; batas total per PO | **Siap dikerjakan** (butir 1 dan 2 sudah dijawab user 2026-11-26) |
| **1b** | Nomor PR Departemen tetap sejak dibuat | Siap dikerjakan |
| **2** | Request Payment otomatis saat invoice disimpan, Checker/Management jalur PO, Dashboard Finance, request otomatis dokumen non-PO | **Butuh keputusan desain** (butir 5); jangan mulai sebelum dijawab |
| 3 | PO Material ikut di modul Receive Invoice | Belum dipetakan ke kode (butir 6) |

## Keputusan bisnis yang sudah final (dari user)

1. Receive Invoice dijadikan **satu modul** untuk semua PO. Tipe hanya **DP** dan **Incoming**. **Tipe Progress dan Retensi dihapus.** (2026-11-24 dan 2026-11-26)
2. TOP di PO hanya berisi **DP (opsional)**. Tidak wajib 100%. Sisa persen otomatis ditagih lewat Incoming.
3. Potongan DP pada invoice Incoming **proporsional**: Nilai Incoming × persen DP (bukan dihabiskan dulu).
4. Rumus pajak: **DPP = Nilai × 11/12; PPn = DPP × 12%; Nilai Invoice = DPP + PPn**. Nilai = nilai DP atau Incoming sebelum PPN. (2026-11-24; ditulis persis oleh user, hasil Nilai × 1,0267)
5. Total invoice (DP + Incoming) per PO tidak boleh melebihi nilai PO.
6. Invoice yang Request Payment-nya ditolak harus bisa **diperbaiki dan dikirim ulang**.
7. Nomor PR otomatis dibuat sekali saat PR dibuat dan **tidak berubah** setelah approval.
8. Checker dan Management **masih dipakai** untuk Request Payment.

## Pertanyaan terbuka (jawab sebelum kode terkait ditulis)

| No | Pertanyaan | Dampak | Usulan |
|---|---|---|---|
| 1 | ~~Konfirmasi rumus `Invoice = DPP + PPn`.~~ **DIJAWAB (user, 2026-11-26): rumus sudah benar.** Hasilnya Nilai × 1,0267 (bukan Nilai + PPN 11%), memang itu yang dimaksud. | – | Tetap tulis di satu fungsi agar mudah dirawat |
| 2 | ~~PO / invoice lama dengan TOP Progress atau Retensi.~~ **DIJAWAB (user, 2026-11-26): di kasus client ini belum ada PO atau invoice lama yang memakai Progress atau Retensi.** | – | Progress dan Retensi dapat dihapus langsung dari kode dan dropdown TOP; tidak perlu penanganan data lama. Nilai `legacy` pada migrasi tinggal sebagai pengaman, boleh dibuang |
| 3 | ~~Nilai DP di layar PO termasuk PPN, sedangkan di Receive Invoice sebelum PPN.~~ **DIJAWAB (user, 2026-11-26): samakan layar PO.** Mockup sudah disesuaikan. | Tampilan PO | Di layar PO (form, detail, approval, print) nilai TOP ditampilkan **sebelum PPN**: Nilai DP = round(persen × (Sub Total − Discount + Biaya Kirim)); baris Incoming otomatis = sisa persen |
| 4 | Jurnal `Receive Invoice` (`Jurnal_model`, tabel `tr_jurnal`) ikut berubah karena DPP dan PPn baru. Aturan jurnalnya belum dispesifikasikan. | Akuntansi | **Jangan ubah jurnal** tanpa spesifikasi akuntansi |
| 5 | Tempat menyimpan Request Payment jalur PO. Kode: invoice DP/Progress membuat `tr_expense` (`exp_inv_po=1`) yang langsung status 1. Di `metalsindo_live`, `request_payment` memakai status smallint dan hanya punya `app_checker*` (tanpa Management), tipe `po_material`/`expense`/`kasbon`. | Fase 2 | Perluas `request_payment` (status Checker + Management, kolom `app_management*`, alasan reject, tipe `invoice_po`) atau buat tabel khusus. Butuh keputusan |
| 6 | PO Material (`tr_purchase_order`) dan Receive Invoice AP lama ikut digabung ke modul ini. Modul `purchase_order_payment` sekarang hanya membaca `tr_purchase_order_non_material`. | Fase 3 | Pisahkan sebagai pekerjaan sendiri |
| 7 | Penomoran PR Stock: mockup memberi nomor sejak awal. Penerbit nomor PRS di kode asli belum ditemukan. | PR Stock | Petakan dulu sebelum mengubah |

## Cara menjalankan mockup

Buka `specs/ui-mockup/index.html` di browser (tidak perlu server). Alur utama: **Peta Alur Pengadaan** → PR Departemen → … → Receive Invoice → Request Payment → Payment List. Menu **Reset data mockup** mengembalikan data awal.

Ringkasan keputusan dan sejarah perubahan mockup: `specs/ui-mockup/PROGRESS_pengadaan.md`.

## Aturan kerja repo yang berlaku

- Database target: **`metalsindo_live`** (salinan lokal Rama; dipakai `application/config/database.php`). Aturan repo sudah disesuaikan (`specs/CLAUDE.md`, `specs/context/coder-only/testing.md`, 2026-11-26). Lihat bagian "Database" di akhir dokumen ini untuk kondisi skemanya.
- Tabel atau kolom baru ditulis sebagai file migrasi `application/modules/<modul>/migrations/NNN_*.sql` + `.down.sql`; **tidak dieksekusi oleh agent**, user yang menjalankan (`specs/context/coder-only/testing.md`).
- Nama dan tipe kolom tabel `tr_purchase_order_non_material` dan `dt_trans_po_non_material` pada dokumen ini diambil dari pemakaian di kode; tabelnya tidak ada di database mana pun yang diperiksa. Verifikasi saat tabel dasar dibuat.
- Dokumen uji FAT: `specs/input/PR To Payment/FAT_PR-To-Payment HFG (26 Nov 2026).xlsx`. Case 1 sudah dijalankan pada mockup.

## Database

Database target **`metalsindo_live`** (MySQL 8.0.30 lokal). Diperiksa 2026-11-26 dengan query baca-saja (nama tabel dan skema; tidak ada data yang diubah).

**Belum ada di `metalsindo_live`** (dipakai kode modul ini): `tr_invoice_po`, `tr_top_po`, `tr_purchase_order`, `tr_purchase_order_non_material`, `dt_trans_po_non_material`, `tr_expense`, `tr_expense_detail`, `tr_incoming_check`, `tr_incoming_check_detail`, `warehouse_adjustment`, `tr_check_invoice`, `tr_jurnal`. Yang ada: `dt_trans_po`, `list_help`, `master_supplier`, `mata_uang`, `request_payment`, `payment_approve`, `rutin_non_planning_header/detail`.

**Perbedaan skema yang ada (penting):**
- `request_payment` di Metalsindo: `status` **smallint** (0 baru, 1 approve, 2 selesai); hanya kolom `app_checker*`; **tidak ada** `app_management*`, `no_surat`, `id_supplier`, `nm_supplier`, `id_ros`; charset **latin1**. Tipe yang ada: `expense`, `kasbon`, `po_material`. Jadi alur Checker + Management untuk Fase 2 **belum didukung skema ini**.
- `list_help` grup `top`: 76 uang muka, 77 progress, **78 retensi** (kode `save_invoice` memakai 98 untuk retensi, itu salah untuk database ini).
- `rutin_non_planning_header`: `no_pengajuan` varchar(10), `no_pr` varchar(20) (collation `utf8mb4_unicode_ci`). `list_help`, `master_supplier`, `request_payment` memakai `latin1_swedish_ci`. Default database: `utf8mb4_0900_ai_ci`. Samakan charset/collation kolom `varchar` yang akan di-join agar tidak terjadi "Illegal mix of collations".

Akibatnya:
- Seluruh pembahasan skema `tr_invoice_po`, `tr_top_po`, dan `request_payment` versi lengkap di `03-data-dan-migrasi.md` dibaca dari **`hfg_dev` (skema HFG)**. Gunakan sebagai gambaran, bukan sebagai fakta untuk Metalsindo.
- `001_*.sql` dan `002_*.sql` memakai `ALTER TABLE` pada tabel yang belum ada di `metalsindo_live`; tidak bisa dijalankan sebelum tabel dasar modul pengadaan dibuat. **Pembuatan tabel dasar diurus programmer** (keputusan user 2026-11-26).
- `002_*.sql` mengasumsikan bentuk `request_payment` versi HFG; perlu disesuaikan (lihat catatan di file itu).
