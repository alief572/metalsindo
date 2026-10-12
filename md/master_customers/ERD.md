# ERD — Modul Master Customers

> Entity Relationship Diagram
> Sumber: query `DESCRIBE` pada database `metalsindo_live`
> Terakhir diperbarui: 2026-09-09

## 1. Diagram Relasi

```
                       +-----------------------------+
                       |  child_customer_category    |
                       |  (master kategori customer) |
                       +-----------------------------+
                       | PK id_category_customer     |
                       |    name_category_customer   |
                       |    customer_code            |
                       |    activation               |
                       +-----------------------------+
                                   ^
                                   | id_category_customer (FK logis)
                                   |
      +----------------------------+------------------------------+
      |                    master_customers                        |
      | PK id_customer                                             |
      |    id_category_customer  (ke child_customer_category)      |
      |    id_karyawan           (ke ms_karyawan)                  |
      |    id_prov / id_kota     (ke provinsi / kota)              |
      |    ... (identitas, bank, pajak, invoice, persyaratan)      |
      +------------------------------------------------------------+
            ^                                   ^
            | id_customer                       | id_customer
            |                                   |
  +---------------------+            +---------------------------+
  | child_customer_pic  |            | child_category_customer   |
  | PK id_pic           |            | PK id                     |
  |    id_customer (FK) |            |    id_customer (FK)       |
  |    name_pic         |            |    name_category_customer |
  |    phone_pic        |            +---------------------------+
  |    email_pic        |
  |    position_pic     |
  +---------------------+
```

> Catatan: relasi bersifat **logis** (tidak ada FK constraint fisik di DB). Relasi dijaga di level aplikasi.

## 2. Struktur Tabel

### 2.1 `master_customers`
Tabel header data customer.

| Kolom | Tipe | Null | Key | Keterangan |
|-------|------|------|-----|------------|
| id_customer | varchar(20) | NO | PRI | ID unik, format `MC` + YY + 5 digit |
| name_customer | varchar(255) | YES | | Nama customer |
| id_category_customer | varchar(20) | YES | | Ref ke `child_customer_category` |
| telephone | varchar(20) | YES | | Telepon utama |
| telephone_2 | varchar(20) | YES | | Telepon kedua |
| fax | varchar(255) | YES | | Nomor fax |
| email | varchar(255) | YES | | Email |
| start_date | date | YES | | Tanggal mulai |
| id_karyawan | int(11) | YES | | Ref ke `ms_karyawan` (marketing) |
| id_prov | varchar(255) | YES | | Ref provinsi |
| id_kota | varchar(255) | YES | | Ref kota |
| address_office | varchar(255) | YES | | Alamat kantor |
| zip_code | varchar(10) | YES | | Kode pos |
| longitude | varchar(255) | YES | | Koordinat |
| latitude | varchar(255) | YES | | Koordinat |
| activation | enum('aktif','inaktif') | YES | | Status |
| facility | enum('DPIL','Kawasan Berikat') | YES | | Fasilitas |
| name_bank | varchar(50) | YES | | Nama bank |
| no_rekening | varchar(255) | YES | | Nomor rekening |
| nama_rekening | varchar(255) | YES | | Nama pemilik rekening |
| alamat_bank | varchar(255) | YES | | Alamat bank |
| swift_code | varchar(255) | YES | | Swift code |
| npwp | varchar(255) | YES | | Nomor NPWP/PKP |
| npwp_name | varchar(255) | YES | | Nama NPWP |
| npwp_address | varchar(255) | YES | | Alamat NPWP |
| payment_term | varchar(70) | YES | | Term of payment |
| nominal_dp | varchar(255) | YES | | Nominal DP |
| sisa_pembayaran | varchar(255) | YES | | Sisa pembayaran |
| senin..minggu | enum('Y','N') | YES | | Hari terima invoice |
| start_recive | time | YES | | Jam mulai terima invoice |
| end_recive | time | YES | | Jam akhir terima invoice |
| adress_invoice | varchar(255) | YES | | Alamat invoice (catatan: typo `adress`, dikirim dari input `address_invoice`) |
| berita_acara..need_npwp | enum('N','Y') | YES | | Persyaratan dokumen pembayaran |
| created_by / created_on | varchar/datetime | YES | | Audit |
| modified_by / modified_on | varchar/datetime | YES | | Audit |
| deleted | int(1) | YES | | Soft delete flag |
| deleted_by | varchar(50) | YES | | Audit hapus |

### 2.2 `child_customer_category`
Master kategori customer.

| Kolom | Tipe | Null | Key | Keterangan |
|-------|------|------|-----|------------|
| id_category_customer | varchar(20) | YES | | ID kategori, format `CC` + YY + 5 digit |
| name_category_customer | varchar(100) | YES | | Nama kategori |
| customer_code | varchar(100) | YES | | Kode customer |
| activation | varchar(254) | YES | | `active` / `inactive` (soft delete) |
| created_by / created_on | int/date | YES | | Audit |
| modified_by / modified_on | int/date | YES | | Audit |
| deleted_by | varchar(255) | YES | | Audit hapus |

### 2.3 `child_category_customer`
Relasi many kategori per customer (dipilih saat input customer).

| Kolom | Tipe | Null | Key | Keterangan |
|-------|------|------|-----|------------|
| id | int(11) | NO | PRI | Auto increment |
| id_customer | varchar(100) | YES | | Ref `master_customers` |
| name_category_customer | varchar(100) | YES | | Nama kategori terpilih |

### 2.4 `child_customer_pic`
Data PIC (kontak) per customer.

| Kolom | Tipe | Null | Key | Keterangan |
|-------|------|------|-----|------------|
| id_pic | int(11) | NO | PRI | Auto increment |
| id_customer | varchar(100) | YES | | Ref `master_customers` |
| name_pic | varchar(100) | YES | | Nama PIC |
| phone_pic | varchar(20) | YES | | Telepon PIC |
| email_pic | varchar(200) | YES | | Email PIC |
| position_pic | varchar(20) | YES | | Jabatan PIC |
| religion_pic | int(11) | YES | | (tidak dipakai di form saat ini) |

## 3. Tabel Referensi Terkait

| Tabel | Dipakai untuk |
|-------|---------------|
| `ms_karyawan` | Dropdown marketing (filter `divisi = 12`, `deleted = 0`) |
| `provinsi` | Dropdown provinsi |
| `kota` | Dropdown kota (dependent pada provinsi) |
