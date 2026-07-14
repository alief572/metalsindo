# ERD - Modul Penerimaan Invoice (Detailed)

## Diagram Visual: [ERD.html](./ERD.html)

---

## 1. Tabel Utama Modul Penerimaan

### 1.1 `tr_invoice_payment` — Header Transaksi Penerimaan

**Database:** `metalsindo_live`  
**Engine:** InnoDB  
**Charset:** utf8  
**Deskripsi:** Menyimpan data header setiap transaksi penerimaan pembayaran dari customer.

| #   | Column                | Type                          | Null | Key | Default | Extra          | Deskripsi                                          |
| --- | --------------------- | ----------------------------- | ---- | --- | ------- | -------------- | -------------------------------------------------- |
| 1   | id                    | INT(11)                       | NO   | PRI | -       | AUTO_INCREMENT | Primary key                                        |
| 2   | kd_pembayaran         | VARCHAR(50)                   | NO   | UNI | -       | -              | Kode unik penerimaan (PN-YYXNNNNN)                 |
| 3   | no_invoice            | VARCHAR(50)                   | YES  | -   | NULL    | -              | No invoice referensi awal (legacy)                 |
| 4   | jenis_reff            | VARCHAR(50)                   | YES  | -   | NULL    | -              | Jenis referensi (default: '-')                     |
| 5   | no_reff               | VARCHAR(50)                   | YES  | -   | NULL    | -              | Nomor referensi (default: '-')                     |
| 6   | tgl_pembayaran        | DATE                          | YES  | -   | NULL    | -              | Tanggal pembayaran diterima                        |
| 7   | kurs_bayar            | DOUBLE                        | YES  | -   | NULL    | -              | Kurs konversi (default 1 untuk IDR)                |
| 8   | jumlah_piutang        | DOUBLE                        | YES  | -   | NULL    | -              | Total piutang (mata uang asli)                     |
| 9   | jumlah_piutang_idr    | DOUBLE                        | YES  | -   | NULL    | -              | Total piutang (dalam IDR)                          |
| 10  | jumlah_bank           | DOUBLE                        | YES  | -   | NULL    | -              | Jumlah masuk di bank (mata uang asli)              |
| 11  | jumlah_bank_idr       | DOUBLE                        | YES  | -   | NULL    | -              | Jumlah masuk di bank (IDR)                         |
| 12  | jumlah_pembayaran     | DOUBLE                        | YES  | -   | NULL    | -              | Total pembayaran bersih (mata uang asli)           |
| 13  | jumlah_pembayaran_idr | DOUBLE                        | YES  | -   | NULL    | -              | Total pembayaran bersih (IDR)                      |
| 14  | pembayaran_ke         | INT(11)                       | YES  | -   | NULL    | -              | Pembayaran ke-n untuk invoice ini                  |
| 15  | kd_bank               | VARCHAR(15)                   | YES  | -   | NULL    | -              | Kode COA bank tujuan (e.g. 1102-01-01)             |
| 16  | nm_bank               | VARCHAR(30)                   | YES  | -   | NULL    | -              | Nama bank (legacy, jarang diisi)                   |
| 17  | biaya_admin           | DECIMAL(10,2)                 | YES  | -   | 0.00    | -              | Biaya administrasi bank (valas)                    |
| 18  | biaya_admin_idr       | DECIMAL(10,2)                 | YES  | -   | 0.00    | -              | Biaya administrasi bank (IDR)                      |
| 19  | jenis_pph             | VARCHAR(100)                  | YES  | -   | NULL    | -              | COA jenis PPH (e.g. 1102-01-03)                    |
| 20  | biaya_pph             | DECIMAL(10,2)                 | YES  | -   | 0.00    | -              | Nilai PPH (valas)                                  |
| 21  | biaya_pph_idr         | DECIMAL(10,2)                 | YES  | -   | 0.00    | -              | Nilai PPH (IDR)                                    |
| 22  | is_cancel             | VARCHAR(3)                    | YES  | -   | NULL    | -              | Flag pembatalan                                    |
| 23  | kd_bayar_cancel       | VARCHAR(30)                   | YES  | -   | NULL    | -              | Referensi kd_pembayaran yang dibatalkan            |
| 24  | status_bayar          | ENUM('LUNAS','GIRO','CANCEL') | YES  | -   | NULL    | -              | Status pembayaran                                  |
| 25  | cancel_on             | DATETIME                      | YES  | -   | NULL    | -              | Timestamp pembatalan                               |
| 26  | cancel_by             | INT(11)                       | YES  | -   | NULL    | -              | User ID yang membatalkan                           |
| 27  | created_on            | DATETIME                      | YES  | -   | NULL    | -              | Timestamp pembuatan                                |
| 28  | created_by            | INT(11)                       | YES  | -   | NULL    | -              | User ID yang membuat                               |
| 29  | selisih               | DECIMAL(10,2)                 | YES  | -   | NULL    | -              | Selisih pembayaran (legacy)                        |
| 30  | selisih_idr           | DECIMAL(10,2)                 | YES  | -   | NULL    | -              | Selisih IDR (legacy)                               |
| 31  | no_account            | VARCHAR(30)                   | YES  | -   | NULL    | -              | COA untuk selisih (legacy, default '-')            |
| 32  | keterangan            | VARCHAR(255)                  | YES  | -   | NULL    | -              | Keterangan/catatan pembayaran                      |
| 33  | status_jurnal         | ENUM('0','1')                 | YES  | -   | '0'     | -              | 0=belum dijurnal, 1=sudah dijurnal                 |
| 34  | nm_customer           | VARCHAR(255)                  | NO   | -   | -       | -              | Nama customer                                      |
| 35  | bukti_potong          | VARCHAR(45)                   | YES  | -   | NULL    | -              | Nomor bukti potong (legacy)                        |
| 36  | lebih_bayar           | DECIMAL(50,2)                 | YES  | -   | NULL    | -              | Dana deposit yang dipakai                          |
| 37  | tambah_lebih_bayar    | DECIMAL(50,2)                 | YES  | -   | NULL    | -              | Kelebihan bayar yang disimpan sebagai deposit baru |
| 38  | id_customer           | VARCHAR(255)                  | NO   | -   | -       | -              | ID customer (FK ke master_customers)               |

**Indexes:**

- PRIMARY KEY: `id`
- UNIQUE KEY: `kd_pembayaran`

---

### 1.2 `tr_invoice_payment_detail` — Detail Penerimaan per Invoice

**Database:** `metalsindo_live`  
**Deskripsi:** Menyimpan detail per-invoice/CN yang dibayar dalam satu transaksi penerimaan.

| #   | Column            | Type          | Null | Key | Default        | Deskripsi                             |
| --- | ----------------- | ------------- | ---- | --- | -------------- | ------------------------------------- |
| 1   | id_payment_detail | INT(11)       | NO   | PRI | AUTO_INCREMENT | Primary key                           |
| 2   | kd_pembayaran     | VARCHAR(50)   | YES  | -   | NULL           | FK → tr_invoice_payment.kd_pembayaran |
| 3   | no_ipp            | VARCHAR(50)   | YES  | -   | NULL           | Nomor IPP/Quotation                   |
| 4   | so_number         | VARCHAR(50)   | YES  | -   | NULL           | Nomor Sales Order                     |
| 5   | no_invoice        | VARCHAR(255)  | YES  | -   | NULL           | No invoice ATAU id_retur (untuk CN)   |
| 6   | tgl_invoice       | DATE          | YES  | -   | NULL           | Tanggal invoice                       |
| 7   | id_customer       | VARCHAR(255)  | YES  | -   | NULL           | FK → master_customers.id_customer     |
| 8   | nm_customer       | VARCHAR(255)  | YES  | -   | NULL           | Nama customer                         |
| 9   | jenis_invoice     | VARCHAR(50)   | YES  | -   | NULL           | TR-01=UM, TR-02=Progress              |
| 10  | kurs_jual         | DECIMAL(50,2) | YES  | -   | 0.00           | Kurs jual                             |
| 11  | kurs_bayar        | DECIMAL(50,2) | YES  | -   | 0.00           | Kurs bayar                            |
| 12  | total_invoice_idr | DECIMAL(50,2) | YES  | -   | 0.00           | Total sisa invoice saat dibayar       |
| 13  | total_bayar_idr   | DECIMAL(50,2) | YES  | -   | 0.00           | Jumlah bayar (negatif untuk CN)       |
| 14  | sisa_invoice_idr  | DECIMAL(50,2) | YES  | -   | 0.00           | Sisa setelah bayar                    |
| 15  | total_pph_idr     | DECIMAL(50,2) | YES  | -   | 0.00           | PPH per invoice                       |
| 16  | created_on        | DATETIME      | YES  | -   | NULL           | Timestamp                             |
| 17  | created_by        | VARCHAR(100)  | YES  | -   | NULL           | User ID                               |

**Catatan:** Tabel ini memiliki banyak kolom tambahan (material, product, bq, engineering, packing, trucking, dll) yang merupakan warisan dari tabel invoice detail asli, tapi untuk modul penerimaan hanya kolom di atas yang aktif digunakan.

---

### 1.3 `tr_cn_cross` — Crossing Credit Note

**Database:** `metalsindo_live`  
**Engine:** InnoDB  
**Deskripsi:** Mencatat setiap crossing (offset) Credit Note dengan transaksi penerimaan. Satu CN bisa di-cross bertahap (partial crossing).

| #   | Column         | Type          | Null | Key | Default        | Deskripsi                             |
| --- | -------------- | ------------- | ---- | --- | -------------- | ------------------------------------- |
| 1   | id_cn_cross    | INT(11)       | NO   | PRI | AUTO_INCREMENT | Primary key                           |
| 2   | id_retur       | VARCHAR(20)   | NO   | MUL | -              | FK → tr_retur_penjualan.id_retur      |
| 3   | no_cn          | VARCHAR(50)   | NO   | -   | -              | Nomor Credit Note                     |
| 4   | kd_pembayaran  | VARCHAR(50)   | NO   | MUL | -              | FK → tr_invoice_payment.kd_pembayaran |
| 5   | amount_crossed | DECIMAL(15,2) | NO   | -   | -              | Jumlah crossing (selalu positif)      |
| 6   | tgl_cross      | DATE          | NO   | -   | -              | Tanggal crossing                      |
| 7   | created_by     | INT(11)       | NO   | -   | -              | User ID                               |
| 8   | created_on     | DATETIME      | NO   | -   | -              | Timestamp                             |

**Indexes:**

- PRIMARY KEY: `id_cn_cross`
- INDEX `idx_id_retur`: `id_retur`
- INDEX `idx_kd_pembayaran`: `kd_pembayaran`

**CN Balance Calculation:**

```sql
CN_Balance = (SELECT SUM(total_harga) FROM dt_returpenjualan WHERE id_retur = ?)
           - (SELECT COALESCE(SUM(amount_crossed), 0) FROM tr_cn_cross WHERE id_retur = ?)
```

---

### 1.4 `tr_unlocated_bank` — Deposit / Dana Belum Teralokasi

**Database:** `metalsindo_live`  
**Deskripsi:** Menyimpan dana yang masuk ke bank tapi belum bisa dialokasikan ke invoice tertentu, atau kelebihan bayar yang disimpan sebagai deposit.

| #   | Column          | Type          | Null | Key | Default        | Deskripsi                          |
| --- | --------------- | ------------- | ---- | --- | -------------- | ---------------------------------- |
| 1   | id              | INT(11)       | NO   | PRI | AUTO_INCREMENT | Primary key                        |
| 2   | tgl             | DATE          | YES  | -   | NULL           | Tanggal penerimaan                 |
| 3   | keterangan      | VARCHAR(255)  | YES  | -   | NULL           | Deskripsi (biasanya nama customer) |
| 4   | totalpenerimaan | DOUBLE(255,0) | YES  | -   | NULL           | Jumlah total yang diterima         |
| 5   | saldo           | DOUBLE(255,0) | YES  | -   | NULL           | Sisa saldo yang bisa dipakai       |
| 6   | created_on      | DATETIME(6)   | YES  | -   | NULL           | Timestamp                          |
| 7   | created_by      | VARCHAR(255)  | YES  | -   | NULL           | User ID                            |

**Lifecycle:**

1. INSERT: saldo = totalpenerimaan (saat create unlocated / lebih bayar)
2. UPDATE: saldo -= X (saat dipakai di penerimaan berikutnya)
3. Muncul di list deposit jika saldo != 0

---

### 1.5 `tr_kartu_piutang` — Kartu Piutang

**Database:** `metalsindo_live`  
**Deskripsi:** Mencatat mutasi piutang per customer per invoice setelah jurnal di-approve.

| #   | Column        | Type         | Null | Key | Default        | Deskripsi                     |
| --- | ------------- | ------------ | ---- | --- | -------------- | ----------------------------- |
| 1   | id            | BIGINT(20)   | NO   | PRI | AUTO_INCREMENT | Primary key                   |
| 2   | tipe          | CHAR(3)      | NO   | -   | -              | Tipe jurnal (BUM)             |
| 3   | nomor         | VARCHAR(25)  | NO   | -   | -              | Nomor BUM                     |
| 4   | tanggal       | DATE         | YES  | -   | NULL           | Tanggal transaksi             |
| 5   | no_perkiraan  | VARCHAR(10)  | NO   | -   | NULL           | COA piutang (1103-01-01)      |
| 6   | keterangan    | VARCHAR(150) | YES  | -   | NULL           | Deskripsi transaksi           |
| 7   | jenis_trans   | VARCHAR(20)  | NO   | -   | -              | Jenis transaksi               |
| 8   | no_reff       | VARCHAR(25)  | NO   | -   | -              | No invoice referensi          |
| 9   | debet         | DOUBLE       | YES  | -   | NULL           | Debet                         |
| 10  | kredit        | DOUBLE       | YES  | -   | NULL           | Kredit (jumlah bayar)         |
| 11  | nocust        | VARCHAR(15)  | YES  | -   | NULL           | Kode customer                 |
| 12  | valid         | CHAR(1)      | YES  | -   | NULL           | Flag validasi                 |
| 13  | waktu_valid   | DATETIME     | YES  | -   | NULL           | Timestamp validasi            |
| 14  | stspos        | CHAR(1)      | NO   | -   | '0'            | Status posting                |
| 15  | jenis_jurnal  | VARCHAR(255) | YES  | -   | NULL           | Jenis jurnal                  |
| 16  | id_supplier   | VARCHAR(255) | NO   | -   | -              | ID customer (naming legacy)   |
| 17  | nama_supplier | VARCHAR(255) | NO   | -   | -              | Nama customer (naming legacy) |

---

## 2. Tabel Referensi (READ)

### 2.1 `tr_invoice` — Master Invoice

**Database:** `metalsindo_live`  
**Deskripsi:** Tabel invoice utama. Modul penerimaan membaca dan mengupdate tabel ini.

**Kolom yang Dibaca/Diupdate oleh Modul Penerimaan:**

| Column           | Type          | Aksi          | Keterangan                                 |
| ---------------- | ------------- | ------------- | ------------------------------------------ |
| no_invoice       | VARCHAR(20)   | READ          | Nomor invoice (key)                        |
| no_so            | VARCHAR(20)   | READ          | Nomor SO (untuk update spk_marketing)      |
| no_surat         | VARCHAR(255)  | READ          | Nomor surat untuk display                  |
| tgl_invoice      | DATE          | READ          | Tanggal invoice                            |
| id_customer      | VARCHAR(20)   | READ          | Filter per customer                        |
| nm_customer      | VARCHAR(255)  | READ          | Display nama                               |
| sisa_invoice_idr | DOUBLE(40,2)  | READ + UPDATE | **Dikurangi** saat save penerimaan         |
| total_bayar_idr  | DOUBLE(40,2)  | READ + UPDATE | **Ditambah** saat save penerimaan          |
| printed_on       | DATETIME      | READ          | Syarat: harus NOT NULL agar muncul di list |
| proses_print     | ENUM('0','1') | READ          | Filter: harus '1' di invoice_siap_terima   |
| total_invoice    | DOUBLE(50,2)  | READ          | Total nilai invoice                        |

**Update saat Save Penerimaan:**

```sql
UPDATE tr_invoice
SET total_bayar_idr = total_bayar_idr + {jml_bayar},
    sisa_invoice_idr = sisa_invoice_idr - {jml_bayar}
WHERE no_invoice = '{invoice}'
```

---

### 2.2 `master_customers` — Master Customer

**Database:** `metalsindo_live`

**Kolom yang Digunakan:**

| Column         | Type           | Keterangan                             |
| -------------- | -------------- | -------------------------------------- |
| id_customer    | VARCHAR(20) PK | Primary key                            |
| name_customer  | VARCHAR(255)   | Nama customer (untuk display & filter) |
| address_office | VARCHAR(255)   | Alamat (untuk print BUM)               |
| npwp           | VARCHAR(255)   | NPWP customer                          |
| payment_term   | VARCHAR(70)    | Term pembayaran                        |

---

### 2.3 `tr_retur_penjualan` — Master Retur Penjualan (Credit Note)

**Database:** `metalsindo_live`

**Kolom yang Digunakan:**

| Column        | Type            | Keterangan                                         |
| ------------- | --------------- | -------------------------------------------------- |
| id_retur      | VARCHAR(255) PK | Primary key, digunakan sebagai kode di CN crossing |
| no_retur      | VARCHAR(255)    | Nomor retur (legacy)                               |
| no_cn         | VARCHAR(50)     | **Nomor Credit Note** (ditambah via migration)     |
| id_customer   | VARCHAR(100)    | FK, filter CN per customer                         |
| nama_customer | VARCHAR(100)    | Nama customer                                      |
| tgl_retur     | DATE            | Tanggal retur                                      |

---

### 2.4 `dt_returpenjualan` — Detail Retur Penjualan

**Database:** `metalsindo_live`

**Kolom yang Digunakan:**

| Column      | Type         | Keterangan                      |
| ----------- | ------------ | ------------------------------- |
| id_retur    | VARCHAR (FK) | Referensi ke tr_retur_penjualan |
| total_harga | DOUBLE       | Nilai per item retur            |

**Penggunaan:** `SUM(total_harga) WHERE id_retur = ?` → Total nilai CN

---

### 2.5 `tr_spk_marketing` — SPK Marketing / Sales Order

**Database:** `metalsindo_live`

**Kolom yang Digunakan:**

| Column          | Type         | Keterangan                                      |
| --------------- | ------------ | ----------------------------------------------- |
| id_spkmarketing | VARCHAR(100) | Primary key (= tr_invoice.no_so)                |
| total_bayar_so  | DOUBLE(11,2) | **Diupdate:** += jml_bayar saat save penerimaan |

**Update:**

```sql
UPDATE tr_spk_marketing
SET total_bayar_so = total_bayar_so + {jml_bayar}
WHERE id_spkmarketing = '{no_so}'
```

---

## 3. Tabel Accounting (Database: `gl_metalsindo_live`)

### 3.1 `jarh` — Journal AR Header (Bukti Uang Masuk)

**Deskripsi:** Header jurnal penerimaan yang di-post saat approval.

| #   | Column        | Type         | Null | Key | Default | Deskripsi               |
| --- | ------------- | ------------ | ---- | --- | ------- | ----------------------- |
| 1   | nomor         | VARCHAR(20)  | NO   | PRI | -       | Nomor BUM (generated)   |
| 2   | kd_pembayaran | VARCHAR(30)  | YES  | -   | NULL    | FK → tr_invoice_payment |
| 3   | tgl           | DATE         | NO   | -   | NULL    | Tanggal jurnal          |
| 4   | jml           | DOUBLE       | NO   | -   | 0       | Jumlah total            |
| 5   | kdcab         | VARCHAR(7)   | NO   | -   | -       | Kode cabang (101)       |
| 6   | jenis_reff    | VARCHAR(15)  | NO   | -   | -       | Jenis referensi         |
| 7   | no_reff       | VARCHAR(25)  | YES  | -   | NULL    | Nomor referensi         |
| 8   | customer      | VARCHAR(30)  | NO   | -   | -       | Nama customer           |
| 9   | terima_dari   | VARCHAR(350) | YES  | -   | NULL    | Diterima dari           |
| 10  | jenis_ar      | VARCHAR(20)  | NO   | -   | -       | Jenis AR (V = Voucher)  |
| 11  | note          | VARCHAR(350) | YES  | -   | NULL    | Keterangan lengkap      |
| 12  | valid         | CHAR(1)      | NO   | -   | -       | User validator          |
| 13  | tgl_valid     | DATE         | YES  | -   | NULL    | Tanggal validasi        |
| 14  | user_id       | VARCHAR(20)  | YES  | -   | NULL    | User ID                 |
| 15  | tgl_invoice   | DATE         | YES  | -   | NULL    | Tanggal invoice ref     |
| 16  | ho_valid      | CHAR(1)      | YES  | -   | NULL    | HO validation flag      |
| 17  | batal         | CHAR(1)      | NO   | -   | '0'     | Flag batal              |

---

### 3.2 `jurnal` — Journal Detail (Debit/Kredit)

**Deskripsi:** Detail baris jurnal (setiap debit/kredit satu baris).

| #   | Column       | Type         | Null | Key | Default        | Deskripsi                                 |
| --- | ------------ | ------------ | ---- | --- | -------------- | ----------------------------------------- |
| 1   | id           | BIGINT(20)   | NO   | PRI | AUTO_INCREMENT | Primary key                               |
| 2   | tipe         | CHAR(3)      | NO   | -   | -              | Tipe (BUM/JV)                             |
| 3   | nomor        | VARCHAR(25)  | NO   | -   | -              | FK → jarh.nomor                           |
| 4   | tanggal      | DATE         | YES  | -   | NULL           | Tanggal                                   |
| 5   | no_perkiraan | VARCHAR(10)  | NO   | -   | NULL           | FK → coa_master.no_perkiraan              |
| 6   | keterangan   | VARCHAR(350) | YES  | -   | NULL           | Deskripsi baris                           |
| 7   | jenis_trans  | VARCHAR(20)  | NO   | -   | -              | Jenis transaksi                           |
| 8   | no_reff      | VARCHAR(25)  | NO   | -   | -              | No referensi (no_invoice / kd_pembayaran) |
| 9   | debet        | BIGINT(20)   | YES  | -   | NULL           | Nilai debet                               |
| 10  | kredit       | BIGINT(20)   | YES  | -   | NULL           | Nilai kredit                              |
| 11  | nocust       | VARCHAR(15)  | YES  | -   | NULL           | Kode customer                             |
| 12  | valid        | CHAR(1)      | YES  | -   | NULL           | Flag validasi                             |
| 13  | waktu_valid  | DATETIME     | YES  | -   | NULL           | Timestamp validasi                        |
| 14  | stspos       | CHAR(1)      | NO   | -   | '0'            | Status posting                            |
| 15  | created_on   | DATETIME     | YES  | -   | NULL           | Timestamp create                          |
| 16  | created_by   | VARCHAR(255) | YES  | -   | NULL           | User create                               |

---

### 3.3 `coa_master` — Chart of Account Master

**Deskripsi:** Master daftar akun/perkiraan.

| #   | Column       | Type         | Null | Key | Default        | Deskripsi                         |
| --- | ------------ | ------------ | ---- | --- | -------------- | --------------------------------- |
| 1   | id           | INT(11)      | NO   | PRI | AUTO_INCREMENT | Primary key                       |
| 2   | no_perkiraan | VARCHAR(10)  | NO   | MUL | -              | Nomor perkiraan (e.g. 1102-01-01) |
| 3   | nama         | VARCHAR(50)  | NO   | -   | -              | Nama akun                         |
| 4   | kdcab        | VARCHAR(5)   | NO   | MUL | -              | Kode cabang                       |
| 5   | saldoawal    | DOUBLE       | NO   | -   | 0              | Saldo awal                        |
| 6   | bln          | SMALLINT(6)  | NO   | -   | 0              | Bulan                             |
| 7   | thn          | INT(11)      | NO   | -   | 0              | Tahun                             |
| 8   | debet        | DOUBLE       | YES  | -   | 0              | Total debet                       |
| 9   | kredit       | DOUBLE       | YES  | -   | 0              | Total kredit                      |
| 10  | tipe         | CHAR(3)      | YES  | -   | '0'            | Tipe akun                         |
| 11  | level        | CHAR(1)      | YES  | -   | NULL           | Level hierarki                    |
| 12  | grup         | VARCHAR(20)  | YES  | -   | NULL           | Grup akun                         |
| 13  | kode_bank    | VARCHAR(100) | YES  | -   | NULL           | Kode bank (jika tipe bank)        |

---

### 3.4 `pastibisa_tb_cabang` — Counter Nomor Jurnal per Cabang

**Deskripsi:** Menyimpan counter sequence untuk generate nomor jurnal.

**Kolom yang Digunakan:**

| Column  | Aksi   | Keterangan                        |
| ------- | ------ | --------------------------------- |
| nocab   | WHERE  | Filter cabang (101)               |
| nobum   | UPDATE | Counter BUM (+=1 setiap approval) |
| nomorJC | UPDATE | Counter JC (untuk jurnal koreksi) |

---

## 4. Relasi Antar Tabel (Detail)

### 4.1 Diagram Relasi Tekstual

```
                                    ┌─────────────────────────────┐
                                    │      master_customers       │
                                    │  PK: id_customer            │
                                    └──────────┬──────────────────┘
                                               │
                        ┌──────────────────────┼──────────────────────┐
                        │ 1:N                  │ 1:N                  │ 1:N
                        ▼                      ▼                      ▼
          ┌─────────────────────┐  ┌──────────────────────┐  ┌─────────────────┐
          │    tr_invoice       │  │ tr_retur_penjualan   │  │ tr_invoice      │
          │ PK: id (no_invoice) │  │ PK: id_retur         │  │ _payment        │
          │ FK: id_customer     │  │ FK: id_customer      │  │ FK: id_customer │
          │                     │  │ + no_cn              │  │ PK: id          │
          │ Updated fields:     │  │                      │  │ UK: kd_pembayar │
          │ - sisa_invoice_idr  │  │                      │  │                 │
          │ - total_bayar_idr   │  │                      │  │                 │
          └─────────┬───────────┘  └──────────┬───────────┘  └───┬─────────┬──┘
                    │                          │                   │         │
                    │ 1:N                      │ 1:N               │ 1:N     │ 1:N
                    ▼                          ▼                   ▼         ▼
     ┌──────────────────────────┐   ┌──────────────────┐  ┌────────────┐ ┌────────┐
     │ tr_invoice_payment       │   │ dt_returpenjualan│  │tr_invoice_ │ │tr_cn_  │
     │ _detail                  │   │ FK: id_retur     │  │payment_    │ │cross   │
     │ PK: id_payment_detail    │   │ + total_harga    │  │detail      │ │FK:     │
     │ FK: kd_pembayaran        │   └──────────────────┘  │FK: kd_pemb │ │kd_pemb │
     │ FK: no_invoice           │                         │FK: no_inv  │ │id_retur│
     └──────────────────────────┘                         └────────────┘ └────────┘
                                                                              │
                                                                              │ 1:N
                                                                              ▼
                                                                    ┌──────────────────┐
                                                                    │ tr_retur_        │
                                                                    │ penjualan        │
                                                                    └──────────────────┘

     tr_invoice_payment ──(1:1)──▶ jarh (gl_metalsindo_live) ──(1:N)──▶ jurnal
                                                                           │
                                                                           │ N:1
                                                                           ▼
                                                                      coa_master
```

### 4.2 Tabel Relasi Lengkap

| #   | Parent Table       | Child Table               | FK Column               | Cardinality | On Delete  | Keterangan                     |
| --- | ------------------ | ------------------------- | ----------------------- | ----------- | ---------- | ------------------------------ |
| 1   | tr_invoice_payment | tr_invoice_payment_detail | kd_pembayaran           | 1:N         | No cascade | Detail per invoice             |
| 2   | tr_invoice_payment | tr_cn_cross               | kd_pembayaran           | 1:N         | No cascade | CN crossing per penerimaan     |
| 3   | tr_invoice         | tr_invoice_payment_detail | no_invoice              | 1:N         | No cascade | Invoice dibayar                |
| 4   | master_customers   | tr_invoice                | id_customer             | 1:N         | No cascade | Customer punya banyak invoice  |
| 5   | master_customers   | tr_retur_penjualan        | id_customer             | 1:N         | No cascade | Customer punya banyak retur    |
| 6   | master_customers   | tr_invoice_payment        | id_customer             | 1:N         | No cascade | Customer punya banyak payment  |
| 7   | tr_retur_penjualan | dt_returpenjualan         | id_retur                | 1:N         | No cascade | Detail item retur              |
| 8   | tr_retur_penjualan | tr_cn_cross               | id_retur                | 1:N         | No cascade | CN bisa di-cross bertahap      |
| 9   | tr_invoice_payment | jarh (GL)                 | kd_pembayaran           | 1:0..1      | No cascade | Header jurnal (after approval) |
| 10  | jarh               | jurnal (GL)               | nomor                   | 1:N         | No cascade | Detail debit/kredit            |
| 11  | coa_master         | jurnal (GL)               | no_perkiraan            | 1:N         | No cascade | Referensi COA                  |
| 12  | tr_invoice         | tr_spk_marketing          | no_so = id_spkmarketing | N:1         | No cascade | Invoice dari SO                |

---

## 5. Migration Script (CN Cross Feature)

```sql
-- File: application/modules/penerimaan/sql/migration_cn_cross.sql

-- Add no_cn column to tr_retur_penjualan
ALTER TABLE tr_retur_penjualan
ADD COLUMN no_cn VARCHAR(50) DEFAULT NULL AFTER no_retur;

-- Create tr_cn_cross table
CREATE TABLE tr_cn_cross (
    id_cn_cross INT AUTO_INCREMENT PRIMARY KEY,
    id_retur VARCHAR(20) NOT NULL,
    no_cn VARCHAR(50) NOT NULL,
    kd_pembayaran VARCHAR(50) NOT NULL,
    amount_crossed DECIMAL(15,2) NOT NULL,
    tgl_cross DATE NOT NULL,
    created_by INT NOT NULL,
    created_on DATETIME NOT NULL,
    INDEX idx_id_retur (id_retur),
    INDEX idx_kd_pembayaran (kd_pembayaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

---

## 6. View/Stored Procedure

### 6.1 `view_tr_invoice_payment` (VIEW)

**Digunakan di:** `get_data_pn()`, `get_data_pn_jurnal()`

**Probable Definition:**

```sql
CREATE VIEW view_tr_invoice_payment AS
SELECT
    d.kd_pembayaran,
    i.no_surat,
    d.total_bayar_idr
FROM tr_invoice_payment_detail d
LEFT JOIN tr_invoice i ON i.no_invoice = d.no_invoice;
```

**Penggunaan:**

```sql
SELECT kd_pembayaran,
       GROUP_CONCAT(no_surat SEPARATOR ',') as invoiced,
       SUM(total_bayar_idr) as totalinvoiced
FROM view_tr_invoice_payment
GROUP BY kd_pembayaran
```
