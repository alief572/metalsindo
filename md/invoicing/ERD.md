# ERD - Modul Invoicing (Wt_invoicing)

## Diagram Visual: [ERD.html](./ERD.html)

---

## 1. Tabel Utama

### 1.1 `tr_invoice` — Header Invoice

| # | Column | Type | Keterangan |
|---|--------|------|------------|
| 1 | id | INT(11) PK | AUTO_INCREMENT |
| 2 | no_invoice | VARCHAR(20) UK | Kode internal (I{YY}{NNNNN}) |
| 3 | no_surat | VARCHAR(255) | Nomor cetak (INV-MP/YY/BLN/NNNN) |
| 4 | no_proforma_invoice | VARCHAR(255) | Ref proforma (jika ada) |
| 5 | tgl_invoice | DATE | Tanggal invoice |
| 6 | no_so | VARCHAR(20) | FK → tr_spk_marketing.id_spkmarketing |
| 7 | id_customer | VARCHAR(20) | FK → master_customers |
| 8 | pic_customer | VARCHAR(255) | PIC customer |
| 9 | email_customer | VARCHAR(255) | Email customer |
| 10 | top | INT(11) | FK → ms_top.id_top |
| 11 | id_sales | VARCHAR(255) | FK → ms_karyawan |
| 12 | nama_sales | VARCHAR(255) | Nama marketing |
| 13 | nilai_produk | DOUBLE(50,2) | Total nilai produk |
| 14 | persentase | DOUBLE(50,0) | % invoice dari total |
| 15 | nilai_invoice | DOUBLE(50,2) | Nilai tagih (DPP) |
| 16 | ppn | DOUBLE(50,0) | Tarif PPN (%) |
| 17 | nilai_ppn | DOUBLE(50,2) | Nilai PPN (Rp) |
| 18 | grand_total | DOUBLE(50,2) | Grand total |
| 19 | sisa_invoice_idr | DOUBLE(40,2) | Sisa piutang (dikurangi penerimaan) |
| 20 | total_bayar_idr | DOUBLE(40,2) | Total yang sudah dibayar |
| 21 | total_bayar | DOUBLE(50,2) | Total bayar (legacy) |
| 22 | status | INT(11) | 0=draft, 1=printed |
| 23 | status_close | ENUM('0','1') | 0=open, 1=closed |
| 24 | status_jurnal | ENUM('OPN','CLS') | Status jurnal piutang |
| 25 | stat_efaktur | TINYINT(1) | 0=belum, 1=sudah export |
| 26 | type | ENUM('reguler','slitting') | Tipe invoice |
| 27 | no_do | VARCHAR(255) | Nomor surat DO |
| 28 | id_do | VARCHAR(255) | FK → tr_delivery_order.id_delivery_order |
| 29 | tgl_do | DATE | Tanggal DO |
| 30 | no_po | VARCHAR(255) | Nomor PO customer |
| 31 | tgl_po | DATE | Tanggal PO |
| 32 | note | VARCHAR(255) | Terms/payment note |
| 33 | alamat | VARCHAR(255) | Alamat invoice |
| 34 | total | DOUBLE(40,2) | Subtotal sebelum % |
| 35 | diskon | DOUBLE(40,2) | Diskon |
| 36 | dpp | DOUBLE(40,2) | DPP |
| 37 | referensi | VARCHAR(255) | Referensi |
| 38 | no_faktur | VARCHAR(255) | No faktur pajak |
| 39 | jatuh_tempo | DATE | Jatuh tempo |
| 40 | upload_po | VARCHAR(255) | Path file PO |
| 41 | upload_so | VARCHAR(255) | Path file SO |
| 42 | tgl_terima | DATE | Tanggal terima customer |
| 43 | tgl_followup | DATE | Tanggal follow-up terakhir |
| 44 | tgl_janji_bayar | DATE | Tanggal janji bayar |
| 45 | printed_on | DATETIME | Timestamp cetak pertama |
| 46 | printed_by | VARCHAR(35) | User cetak |
| 47 | created_on | DATETIME | Timestamp create |
| 48 | created_by | VARCHAR(35) | User create |
| 49 | tahun | DATE | Tahun referensi |

---

### 1.2 `tr_invoice_detail` — Detail Item Invoice

| # | Column | Type | Keterangan |
|---|--------|------|------------|
| 1 | id_invoice_detail | INT PK | AUTO_INCREMENT |
| 2 | no_invoice | VARCHAR | FK → tr_invoice.no_invoice |
| 3 | no_so | VARCHAR | Nomor SO |
| 4 | id_category3 | VARCHAR | FK → ms_inventory_category3 |
| 5 | nama_produk | VARCHAR | Nama produk |
| 6 | qty_invoice | DECIMAL | Quantity invoice (Kgs) |
| 7 | qty | DECIMAL | Quantity asli |
| 8 | harga_satuan | DECIMAL | Harga per unit |
| 9 | total_harga | DECIMAL | qty × harga_satuan |
| 10 | diskon | DECIMAL | Diskon (%) |
| 11 | nilai_diskon | DECIMAL | Nilai diskon (Rp) |
| 12 | freight_cost | DECIMAL | Biaya freight |
| 13 | original_size | VARCHAR | Ukuran asli material |
| 14 | tobe_size | VARCHAR | Ukuran hasil (slitting) |
| 15 | tgl_delivery | DATE | Tanggal delivery |
| 16 | created_on | DATETIME | Timestamp |
| 17 | created_by | VARCHAR | User |

---

### 1.3 `tr_followup` — Follow-Up Penagihan

| # | Column | Type | Keterangan |
|---|--------|------|------------|
| 1 | id | INT PK | AUTO_INCREMENT |
| 2 | no_invoice | VARCHAR | FK → tr_invoice.no_invoice |
| 3 | received | VARCHAR | Nama penerima |
| 4 | tgl_terima | DATE | Tanggal terima |
| 5 | tgl_followup | DATE | Tanggal follow-up |
| 6 | tgl_janji_bayar | DATE | Tanggal janji bayar |
| 7 | keterangan_followup | TEXT | Keterangan |
| 8 | upload_tanda_terima | VARCHAR | Path file tanda terima |
| 9 | aktif | ENUM('Y','N') | Y = follow-up aktif terakhir |
| 10 | created_on | DATETIME | Timestamp |
| 11 | created_by | VARCHAR | User |

---

### 1.4 `faktur_e_logs` — Log Export E-Faktur

| # | Column | Type | Keterangan |
|---|--------|------|------------|
| 1 | id | INT PK | AUTO_INCREMENT |
| 2 | id_export | VARCHAR | Batch ID (format: yymmddHHMM) |
| 3 | date_export | DATE | Tanggal export |
| 4 | time_export | TIME | Waktu export |
| 5 | invoice_no | VARCHAR | No surat invoice (FK) |

---

## 2. Tabel Referensi

### 2.1 `tr_delivery_order`
| Column | Aksi | Keterangan |
|--------|------|------------|
| id_delivery_order | READ | Source untuk create invoice |
| no_surat | READ | Nomor DO |
| no_spk_marketing | READ | Link ke SPK |
| tgl_delivery_order | READ | Untuk bulan nomor invoice |
| status_invoice | UPDATE → 'CLS' | Setelah invoice dibuat |
| nilai_invoice | UPDATE | Set nilai invoice |

### 2.2 `tr_spk_marketing`
| Column | Aksi | Keterangan |
|--------|------|------------|
| id_spkmarketing | READ | Primary key |
| no_surat | READ | Nomor SPK |
| percent_invoice | UPDATE += % | Akumulasi % invoice |
| total_invoice | UPDATE += dpp | Akumulasi total invoice |

### 2.3 `dt_spkmarketing` (Detail SPK)
| Column | Aksi | Keterangan |
|--------|------|------------|
| id_material | READ | FK ms_inventory_category3 |
| harga_deal | READ | Harga deal per item |
| qty_produk | READ | Qty pesanan |

### 2.4 `stock_material`
| Column | Aksi | Keterangan |
|--------|------|------------|
| qty_sheet | READ | Qty sheet untuk sheet products |
| lotno | READ | Join ke DO child |
| no_kirim | READ | = id_delivery_order |
| id_category3 | READ | Filter per material |

### 2.5 `master_customers`
| Column | Aksi | Keterangan |
|--------|------|------------|
| id_customer | READ | Primary key |
| name_customer | READ | Display name |
| npwp | READ | Untuk E-Faktur |
| npwp_name | READ | Nama NPWP |
| npwp_address | READ | Alamat NPWP |
| payment_term | READ | Default terms |

### 2.6 `ms_inventory_category3` (Product Master)
| Column | Keterangan |
|--------|------------|
| id_category3 | Primary key |
| nama | Nama produk |
| id_bentuk | B2000002 = Sheet |
| thickness | Ketebalan |
| density | Density material |
| kode_coretax | Kode barang Coretax |

### 2.7 Database Views
| View | Keterangan |
|------|------------|
| `v_spk_marketing` | SPK Marketing dengan total nilai |
| `view_detail_delivery_order` | Detail DO (gabungan) |
| `view_detail_delivery_order_scrap` | Detail DO scrap (slitting) |
| `view_dodt_spkmarketing` | Join DO-SPK detail |
| `view_efaktur_invoice` | Pre-computed E-Faktur data |

---

## 3. Relasi

| Parent | Child | FK | Cardinality |
|--------|-------|-----------|-------------|
| tr_invoice | tr_invoice_detail | no_invoice | 1:N |
| tr_invoice | tr_followup | no_invoice | 1:N |
| tr_invoice | faktur_e_logs | no_surat = invoice_no | 1:N |
| master_customers | tr_invoice | id_customer | 1:N |
| tr_delivery_order | tr_invoice | id_do | 1:1 |
| tr_spk_marketing | tr_invoice | id_spkmarketing = no_so | 1:N |
| ms_inventory_category3 | tr_invoice_detail | id_category3 | 1:N |
| tr_invoice | tr_invoice_payment_detail (Penerimaan) | no_invoice | 1:N |
