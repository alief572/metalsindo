# 03 — Data dan migrasi

Status: **draf**. Tidak ada SQL di folder ini yang sudah dijalankan. Database target: `metalsindo_live` lokal. Tabel dasar modul pengadaan belum ada di sana dan akan dibuat programmer (lihat README, bagian "Database").

## 1. Skema yang diperiksa (read-only di `hfg_dev`, 2026-11-26)

> **Peringatan:** ini skema **HFG**, bukan skema Metalsindo. `metalsindo_live` belum memiliki `tr_invoice_po`, `tr_top_po`, `tr_purchase_order`, dan `tr_expense`; `request_payment` di sana berbeda (status smallint, tanpa kolom Management, latin1). Verifikasi nama dan tipe kolom saat tabel dasar dibuat (lihat README, bagian "Database"). Charset kolom `varchar` yang di-join harus disamakan; file SQL di folder ini sengaja tidak menyebut charset kolom agar mengikuti tabel tujuan.

`tr_invoice_po` (kunci `id` varchar(100)): `no_incoming`, `no_po`, `curr`, `invoice_date`, `value_dp`, `invoice_no`, `total_pembelian`, `no_faktur_pajak`, `persen_dp`, `link_doc`, `total_invoice`, `notes`, `req_payment_po`, `invoice_date_real`, `tanggal_faktur_pajak`, `id_supplier`, `nm_supplier`, `id_top`, `bank`, `no_bank`, `nm_acc_bank`, `nilai_ppn`, `nilai_disc`, `kurs`, `created_by`, `created_date`. Tabel kosong di `hfg_dev`; di database produksi mungkin berisi data, jadi **semua perubahan harus aman terhadap baris lama**.

`tr_top_po`: `id`, `no_po`, `group_top`, `progress` (persen), `nilai`, `keterangan`, `tipe_bayar`, `jatuh_tempo`, `status_bayar` enum(`receive_invoice`,`request_payment`,`payment`).

`request_payment` (PK `id` bigint): `no_doc`, `no_surat`, `tipe`, `jumlah`, `status` enum(`open`,`approve checker`,`reject`,`approve management`,`finish`), `tanggal`, `app_checker*`, `app_management*`, `currency`, `id_supplier`, `nm_supplier`, `keterangan`, `created_*`, `pay_*`, dsb. Contoh data: tipe `invoice_dp` dan `invoice_import` (PO Material). **Tidak ada** kolom alasan reject, tanggal rencana bayar, atau penanda request pengganti.

Tidak dapat diperiksa: `tr_purchase_order_non_material` dan `dt_trans_po_non_material` (tidak ada di `hfg_dev`). Kolom yang dipakai (`no_surat`, `no_po`, `status`, `id_suplier`, `hargatotal`, `total_ppn`, `uang_muka`, `uang_muka_idr`, `kurs_terima_invoice`, `po_inv_create`) dari pemakaian di kode.

## 2. Rancangan

### Fase 1 — `001_receive_invoice_dp_incoming.sql`
**Header `tr_invoice_po`** (kolom baru, semua boleh NULL / default aman agar data lama utuh):

| Kolom baru | Isi |
|---|---|
| `tipe_invoice` varchar(20) | `dp` / `incoming`. Nilai `legacy` hanya pengaman untuk baris lama yang tak terklasifikasi; client ini tidak punya data Progress/Retensi lama, jadi boleh dibuang |
| `nilai_dasar` decimal(20,2) | Nilai DP atau Incoming sebelum PPN |
| `potongan_dp` decimal(20,2) | Potongan DP proporsional (Incoming) |
| `nilai_dpp` decimal(20,2) | DPP = Nilai × 11/12 |
| `total_invoice_idr` decimal(20,2) | Nilai Invoice × kurs |
| `modified_by`, `modified_date` | Jejak edit invoice (edit saat Waiting Checker) |

**Pemetaan ke kolom lama** (agar laporan lama tetap bekerja; isi bersamaan saat simpan):

| Kolom lama | Isi baru |
|---|---|
| `total_pembelian` | Nilai (Nilai Dasar − Potongan DP) |
| `nilai_ppn` | PPn = DPP × 12% |
| `total_invoice` | Nilai Invoice = DPP + PPn |
| `value_dp`, `persen_dp` | Hanya tipe DP: Nilai DP dan persen DP |
| `req_payment_po` | Nilai Invoice (tipe Incoming), sama dengan `total_invoice` |
| `id_top` | Hanya tipe DP |

**Tabel baru `tr_invoice_po_detail`**: satu baris per PO atau per dokumen incoming dalam satu invoice (invoice Incoming bisa memuat banyak incoming dan PO). Kolom: `id_invoice`, `no_po`, `id_top`, `no_incoming`, `nilai_dasar`, `potongan_dp`, `nilai`, `nilai_dpp`, `nilai_ppn`, `nilai_invoice`, `created_*`. Dipakai untuk batas total per PO dan untuk menandai incoming yang sudah di-invoice. Pada header lama, `no_po` / `no_incoming` berisi daftar dipisah koma sehingga sulit dihitung.

**Backfill** (di file migrasi): `tipe_invoice` = `dp` bila `id_top` menunjuk `tr_top_po.group_top = 76`, selain itu `legacy`. `nilai_dasar`, `nilai_dpp`, dan `total_invoice_idr` baris lama dibiarkan NULL (bukan angka yang dihitung ulang). Invoice lama tidak dibaca ulang dengan rumus baru.

### Fase 2 — `002_request_payment_link.sql` (tunda sampai butir terbuka 5 dijawab)
- `request_payment`: `id_invoice_po`, `tgl_rencana_bayar`, `reject_reason`, `reject_by`, `reject_on`, `superseded_by`, index pada `id_invoice_po`.
- `tr_invoice_po`: `no_request_payment` varchar(50) (nomor `request_payment.no_doc` yang aktif).
- `request_payment.tipe` memakai nilai baru `invoice_po` (kolom `varchar(20)`, tanpa perubahan skema).

## 3. Urutan eksekusi (oleh programmer / user, bukan agent)

1. Cadangkan tabel `tr_invoice_po` dan `request_payment` (dump) di lingkungan tujuan.
2. Jalankan pemeriksaan pra-syarat (bagian 4) dan pastikan tidak ada kolom yang sudah ada.
3. Jalankan `001_*.sql` pada `hfg_dev`; verifikasi dengan query bagian 5.
4. Terapkan perubahan kode Fase 1; uji (FAT dan `04-pengujian.md`).
5. Setelah butir terbuka 5 dijawab: `002_*.sql` dan kode Fase 2.
6. Pindahkan file ke `application/modules/purchase_order_payment/migrations/` (nomor berikutnya yang tersedia di modul itu) saat siap di-commit. File `.down.sql` hanya dijalankan bila perlu membatalkan dan **hanya sebelum ada data baru** (menghapus kolom membuang data barunya).

> MySQL 8.0.30 tidak mendukung `ADD COLUMN IF NOT EXISTS`. Migrasi **tidak idempotent**; jalankan sekali. Pemeriksaan pra-syarat ada di bagian 4.

## 4. Pemeriksaan pra-syarat (read-only)

```sql
-- kolom baru belum boleh ada
SELECT table_name, column_name FROM information_schema.columns
WHERE table_schema = DATABASE() AND table_name = 'tr_invoice_po'
  AND column_name IN ('tipe_invoice','nilai_dasar','potongan_dp','nilai_dpp','total_invoice_idr','modified_by','modified_date','no_request_payment');
-- tabel baru belum boleh ada
SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tr_invoice_po_detail';
-- sebaran data lama (butir terbuka 2 sudah dijawab: tidak ada Progress/Retensi lama; query ini hanya untuk memastikan)
SELECT t.group_top, COUNT(*) n FROM tr_invoice_po i LEFT JOIN tr_top_po t ON t.id = i.id_top GROUP BY t.group_top;
SELECT group_top, COUNT(*) n FROM tr_top_po GROUP BY group_top;
SELECT id, name, sts FROM list_help WHERE group_by = 'top';
```

## 5. Verifikasi setelah migrasi

```sql
SHOW CREATE TABLE tr_invoice_po_detail;
SELECT tipe_invoice, COUNT(*) FROM tr_invoice_po GROUP BY tipe_invoice;   -- tidak boleh ada NULL
SELECT COUNT(*) FROM tr_invoice_po WHERE tipe_invoice IS NULL;            -- harus 0
```

## 6. ERD

Sesuai `CLAUDE.md`: ERD wajib mutakhir bersama perubahan skema. Setelah migrasi final, perbarui `specs/docs/diagrams/erd.drawio` (entitas `tr_invoice_po`, `tr_invoice_po_detail`, `request_payment`, hubungan ke `tr_top_po`, `tr_incoming_check`, PO). Gunakan agent `diagram-drawio-author`.
