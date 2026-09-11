# ERD — Modul Retur Pembelian

## 1. Diagram Relasi (ASCII)

```
                       ┌────────────────────────────┐
                       │      master_supplier       │
                       │ PK id_suplier              │
                       │    name_suplier            │
                       └──────────────┬─────────────┘
                                      │ id_supplier
                                      ▼
┌───────────────────────────────────────────────────────────────┐
│                    tr_retur_pembelian  (HEADER)                 │
│ PK id                                                           │
│    no_surat            (nomor retur, unik)                      │
│    id_supplier ──► master_supplier.id_suplier                   │
│    nm_supplier                                                  │
│    no_po               (CSV no_po; alur lama)                   │
│    id_rec_inv_ap ──► tr_receive_invoice_ap_header.id_rec_inv_ap │
│    tgl_retur, no_ng_report, alasan_retur, file_ba               │
│    no_ref_invoice, tgl_invoice, matauang                        │
│    subtotal, ppn_persen, nilai_ppn, grand_total                 │
│    input_by/date, updated_by/date, deleted_by/date              │
└───────────────┬─────────────────────────────────────┬──────────┘
                │ no_surat = id_header                  │ id_retur
                ▼                                       ▼
┌────────────────────────────────────┐    ┌───────────────────────────┐
│      dt_retur_pembelian (DETAIL)    │    │    tr_dn_retur_pmb (DN)    │
│ PK id                               │    │ PK id                      │
│    id_header ──► tr_retur.no_surat  │    │    id_retur ──► tr_retur.id│
│    id_detail_po ──► dt_trans_po.id  │    │    ...                     │
│    no_po, id_pr, id_material        │    └───────────────────────────┘
│    lotno, nama_material, width      │        (menentukan status DN)
│    qty_order                        │
│    qty_receive   (kg terima)        │
│    qty_sheet     (lembar terima)    │
│    jumlah_retur  (kg diretur)       │
│    qty_sheet_retur (lembar diretur) │
│    harga_satuan  (per kg / per sheet)│
│    grand_total   (total per item)   │
│    matauang                         │
└─────────────┬───────────────────────┘
              │ id_material = id_category3
              ▼
┌──────────────────────────────────────────┐
│         ms_inventory_category3            │
│ PK id / id_category3                      │
│    id_bentuk   (B2000002 = Sheet)         │
│    total_weight (berat per lembar, kg)    │  ◄── kunci konversi harga sheet
│    nama                                    │
└──────────────────────────────────────────┘
```

## 2. Sumber Data Detail (join chain via Receive Invoice AP)

```
tr_receive_invoice_ap_header ──1:N──► tr_receive_invoice_ap_detail
                                             │ id_incoming
                                             ▼
                                        tr_incoming ──► dt_incoming
                                                            │ id_dt_po
                                                            ▼
                                                        dt_trans_po ──► ms_inventory_category3
                                                            │              (id_bentuk, total_weight)
                                                            ▼
                                                        tr_purchase_order (matauang)
```

## 3. Struktur Tabel

### 3.1 tr_retur_pembelian (Header)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK AI | Primary key |
| no_surat | VARCHAR | Nomor retur (RTR-PMB/...) |
| id_supplier | VARCHAR | FK → master_supplier.id_suplier |
| nm_supplier | VARCHAR | Snapshot nama supplier |
| no_po | VARCHAR | CSV daftar no_po (alur lama) |
| id_rec_inv_ap | VARCHAR(50) | FK → tr_receive_invoice_ap_header |
| tgl_retur | DATE | Tanggal retur |
| no_ng_report | VARCHAR | Nomor NG report |
| alasan_retur | TEXT | Alasan retur |
| file_ba | VARCHAR | Path file NCR / Berita Acara |
| no_ref_invoice | VARCHAR | No. referensi invoice |
| tgl_invoice | DATE | Tanggal invoice |
| matauang | VARCHAR(20) | Mata uang (default IDR) |
| subtotal | DOUBLE | Σ grand_total item |
| ppn_persen | DOUBLE | Persentase PPN |
| nilai_ppn | DOUBLE | Nilai PPN |
| grand_total | DOUBLE | subtotal + nilai_ppn |
| input_by / input_date | INT / DATETIME | Audit create |
| updated_by / updated_date | INT / DATETIME | Audit update |
| deleted_by / deleted_date | INT / DATETIME | Soft delete |

### 3.2 dt_retur_pembelian (Detail)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT PK AI | Primary key |
| id_header | VARCHAR | FK → tr_retur_pembelian.no_surat |
| id_detail_po | VARCHAR | FK → dt_trans_po.id |
| no_po | VARCHAR | Nomor PO |
| id_pr | VARCHAR | Id detail PR |
| id_material | VARCHAR | FK → ms_inventory_category3.id_category3 |
| lotno | VARCHAR(100) | Lot number incoming |
| nama_material | VARCHAR | Snapshot nama material |
| width | DOUBLE | Lebar |
| qty_order | DOUBLE | Qty order (totalwidth) |
| qty_receive | DOUBLE | Qty terima (kg) |
| qty_sheet | DOUBLE | Qty terima (lembar) |
| jumlah_retur | DOUBLE | Qty retur (kg) — non-sheet |
| qty_sheet_retur | DOUBLE | Qty retur (lembar) — sheet |
| harga_satuan | DOUBLE | **Per kg (non-sheet) / per sheet (sheet)** |
| grand_total | DOUBLE | Total per item = qty_retur × harga_satuan |
| matauang | VARCHAR(20) | Mata uang |
| input_by / input_date | INT / DATETIME | Audit |

### 3.3 ms_inventory_category3 (Referensi Material)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id / id_category3 | VARCHAR PK | Id material |
| id_bentuk | VARCHAR | `B2000002` = Sheet, lainnya non-sheet |
| total_weight | DOUBLE | **Berat per lembar (kg/sheet)** — dipakai konversi harga sheet |
| nama | VARCHAR | Nama material |

## 4. Catatan Konversi Harga Sheet

```
harga_satuan (sheet) = dt_trans_po.hargasatuan (per kg)
                       × ms_inventory_category3.total_weight (kg per sheet)

grand_total (sheet)  = dt_retur_pembelian.qty_sheet_retur
                       × harga_satuan (per sheet)
```

## 5. Catatan Retur Parsial & Pelacakan Sisa Kuantitas

1. **Penyimpanan Detail Item Parsial**:
   - `dt_retur_pembelian` hanya menyimpan baris material yang dicentang dan memiliki `qty_retur > 0`. Baris yang tidak diretur tidak disimpan.
   - Kolom `tr_retur_pembelian.no_po` hanya mencantumkan nomor PO yang materialnya benar-benar ada di dalam detail retur tersebut (distinct CSV).

2. **Kueri Agregasi Retur Terdahulu (Active Returns)**:
   Sisa kuantitas incoming yang masih dapat diretur dihitung dengan mengakumulasi retur dari dokumen yang aktif (`tr_retur_pembelian.deleted_by IS NULL`):
   ```sql
   SELECT dt.id_detail_po, dt.lotno,
          SUM(dt.jumlah_retur) AS qty_already_retur_kg,
          SUM(dt.qty_sheet_retur) AS qty_already_retur_sheet
   FROM dt_retur_pembelian dt
   JOIN tr_retur_pembelian tr ON tr.no_surat = dt.id_header
   WHERE tr.deleted_by IS NULL
   GROUP BY dt.id_detail_po, dt.lotno
   ```
   - Sisa non-sheet (KG) = `qty_receive - qty_already_retur_kg`
   - Sisa sheet (lembar) = `qty_sheet - qty_already_retur_sheet`
   - Pada form **Edit**, parameter `$exclude_no_surat` diteruskan agar retur yang sedang diedit tidak dihitung sebagai pengurang kuantitas dirinya sendiri.

