# ERD - Modul SPK Marketing

## 1. Tabel Header: `tr_spk_marketing`

| Kolom               | Tipe                       | Null    | Key | Default  | Keterangan                   |
| ------------------- | -------------------------- | ------- | --- | -------- | ---------------------------- |
| id_spkmarketing     | varchar(100)               | YES     |     | NULL     | ID unik SPK                  |
| no_surat            | varchar(100)               | YES     |     | NULL     | Nomor surat SPK              |
| id_customer         | varchar(100)               | YES     |     | NULL     | FK ke master_customers       |
| tgl_spk_marketing   | date                       | YES     |     | NULL     | Tanggal SPK dibuat           |
| no_penawaran        | varchar(100)               | YES     |     | NULL     | FK ke tr_penawaran           |
| nama_customer       | varchar(100)               | YES     |     | NULL     | Nama customer (denormalized) |
| status_approve      | varchar(100)               | YES     |     | NULL     | Status approval              |
| no_po               | varchar(100)               | YES     |     | NULL     | Nomor PO customer            |
| sample              | varchar(255)               | YES     |     | NULL     | Info sample                  |
| tgl_po              | date                       | YES     |     | NULL     | Tanggal PO                   |
| plan_cust           | date                       | YES     |     | NULL     | Tanggal plan customer        |
| note                | text                       | YES     |     | NULL     | Catatan                      |
| jenis               | varchar(255)               | YES     |     | NULL     | Jenis SPK                    |
| status_revisi       | int(1)                     | YES     |     | 0        | Status revisi                |
| total_spk_marketing | double(11,2)               | YES     |     | 0.00     | Total nilai SPK              |
| percent_invoice     | double(11,2)               | YES     |     | 0.00     | Persentase invoice           |
| total_invoice       | double(11,2)               | YES     |     | 0.00     | Total invoice                |
| tahun               | date                       | YES     |     | NULL     | Tahun SPK                    |
| total_bayar_so      | double(11,2)               | YES     |     | 0.00     | Total bayar SO               |
| retur               | varchar(255)               | YES     |     | NULL     | Info retur                   |
| type                | enum('reguler','slitting') | YES     |     | reguler  | Tipe SPK                     |
| **total_discount**  | **DECIMAL(20,2)**          | **YES** |     | **0.00** | **BARU - Total diskon SPK**  |
| created_by          | varchar(50)                | YES     |     | NULL     |                              |
| created_on          | datetime(6)                | YES     |     | NULL     |                              |
| modified_by         | varchar(50)                | YES     |     | NULL     |                              |
| modified_on         | datetime(6)                | YES     |     | NULL     |                              |
| deleted             | int(1)                     | YES     |     | NULL     |                              |
| deleted_by          | varchar(50)                | YES     |     | NULL     |                              |

## 2. Tabel Detail: `dt_spkmarketing`

| Kolom                 | Tipe                       | Null    | Key | Default        | Keterangan                   |
| --------------------- | -------------------------- | ------- | --- | -------------- | ---------------------------- |
| id                    | int(11)                    | NO      | PRI | auto_increment | ID auto                      |
| id_dt_spkmarketing    | varchar(255)               | YES     |     | NULL           | ID detail                    |
| id_spkmarketing       | varchar(255)               | YES     |     | NULL           | FK ke tr_spk_marketing       |
| id_material           | varchar(255)               | YES     |     | NULL           | FK ke ms_inventory_category3 |
| no_alloy              | varchar(255)               | YES     |     | NULL           | Nama material                |
| thickness             | double(40,2)               | YES     |     | NULL           | Ketebalan                    |
| width                 | double(40,2)               | YES     |     | NULL           | Lebar                        |
| length                | double(40,2)               | YES     |     | 0.00           | Panjang                      |
| harga_penawaran       | double(40,2)               | YES     |     | NULL           | Harga dari penawaran         |
| harga_deal            | double(40,2)               | YES     |     | NULL           | Harga deal final             |
| qty_produk            | double(40,2)               | YES     |     | NULL           | Qty dalam KG                 |
| weight                | double(40,2)               | YES     |     | NULL           | Berat per coil               |
| total_weight          | double(40,2)               | YES     |     | NULL           | Total berat                  |
| total_harga           | double(40,2)               | YES     |     | NULL           | Total harga (deal × qty)     |
| **nominal_discount**  | **DECIMAL(20,2)**          | **YES** |     | **0.00**       | **BARU - Diskon per item**   |
| crcl                  | varchar(255)               | YES     |     | NULL           | FK ke CRCL                   |
| id_child_penawaran    | varchar(100)               | YES     |     | NULL           | FK ke child_penawaran        |
| deal                  | varchar(100)               | YES     |     | NULL           | Status deal (0/1)            |
| status_lanjutan       | varchar(255)               | YES     |     | 1              | Status lanjutan              |
| delivery              | date                       | YES     |     | NULL           | Tanggal delivery             |
| keterangan            | varchar(255)               | YES     |     | NULL           | Keterangan                   |
| part_number           | varchar(255)               | YES     |     | NULL           | Part number                  |
| close_do              | enum('Y','N')              | YES     |     | N              | Status close DO              |
| status_do             | enum('OPN','CLS')          | YES     |     | OPN            | Status DO                    |
| status_close_planning | enum('OPN','CLS')          | YES     |     | OPN            | Status close planning        |
| approved              | enum('0','1')              | YES     |     | 0              | Status approved              |
| retur                 | varchar(255)               | YES     |     | NULL           | Info retur                   |
| type                  | enum('reguler','slitting') | YES     |     | reguler        | Tipe                         |
| qty_slitting          | double(40,2)               | YES     |     | NULL           | Qty slitting                 |
| qty_coil              | double(40,2)               | YES     |     | NULL           | Qty coil                     |
| width_akhir           | double(40,2)               | YES     |     | NULL           | Width akhir slitting         |
| estimasi              | varchar(255)               | YES     |     | NULL           | Estimasi                     |
| created_by            | varchar(50)                | YES     |     | NULL           |                              |
| created_on            | datetime(6)                | YES     |     | NULL           |                              |
| modified_by           | varchar(50)                | YES     |     | NULL           |                              |
| modified_on           | datetime(6)                | YES     |     | NULL           |                              |
| deleted               | int(1)                     | YES     |     | NULL           |                              |
| deleted_by            | varchar(50)                | YES     |     | NULL           |                              |

## 3. Relasi Antar Tabel

```
+-------------------+       +------------------+       +---------------------+
| master_customers  |       | tr_penawaran     |       | child_penawaran     |
| - id_customer (PK)|<------| - id_customer    |       | - id_child_penawaran|
|                   |       | - no_penawaran(PK)|<-----| - no_penawaran (FK) |
+-------------------+       +------------------+       | - id_category3 (FK) |
                                    |                  +---------------------+
                                    |                           |
                                    v                           v
                            +------------------+       +---------------------+
                            | tr_spk_marketing |       | ms_inventory_cat3   |
                            | - id_spkmarketing|       | - id_category3 (PK) |
                            | - no_penawaran   |       +---------------------+
                            | - id_customer    |               ^
                            | - total_discount |               |
                            +--------+---------+               |
                                     |                         |
                                     v                         |
                            +------------------+               |
                            | dt_spkmarketing  |               |
                            | - id_spkmarketing|---------------+
                            | - id_material    |
                            | - nominal_discount|
                            +------------------+
                                     |
                                     v
                            +------------------+
                            | dt_spkmarketing_ |
                            |   loading        |
                            | (copy dari detail)|
                            +------------------+
```

## 4. SQL ALTER TABLE (Untuk Implementasi Diskon)

```sql
-- Tambah kolom nominal_discount di tabel detail
ALTER TABLE dt_spkmarketing
ADD COLUMN nominal_discount DECIMAL(20,2) DEFAULT 0.00;

-- Tambah kolom total_discount di tabel header
ALTER TABLE tr_spk_marketing
ADD COLUMN total_discount DECIMAL(20,2) DEFAULT 0.00;
```
