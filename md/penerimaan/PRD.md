# PRD - Modul Penerimaan Invoice (Account Receivable)

## 1. Ringkasan Produk

Modul Penerimaan adalah fitur pada sistem ERP PT Metalsindo (berbasis CodeIgniter 3 HMVC) yang berfungsi untuk mencatat dan mengelola **penerimaan pembayaran piutang (Account Receivable)** dari customer berdasarkan invoice yang telah diterbitkan dan dicetak. Modul ini digunakan oleh tim **Accounting** untuk merekam setiap transaksi pembayaran masuk, mengelola multi-invoice payment, Credit Note (CN) crossing, deposit/unlocated payment, dan menghasilkan jurnal akuntansi otomatis (Bukti Uang Masuk/BUM).

**Konteks Bisnis:** PT Metalsindo adalah perusahaan manufaktur metal yang memiliki proses bisnis: Penawaran → SPK Marketing → Produksi → Delivery Order → Invoice → **Penerimaan Pembayaran** → Jurnal GL.

---

## 2. Tujuan & Objektif

| #   | Objektif                                                           | Ukuran Keberhasilan                                                    | Prioritas |
| --- | ------------------------------------------------------------------ | ---------------------------------------------------------------------- | --------- |
| 1   | Mencatat penerimaan pembayaran piutang dari customer secara akurat | Setiap transaksi tercatat dengan kode unik PN-YYXNNNNN dan audit trail | High      |
| 2   | Mendukung pelunasan multi-invoice dalam 1 transaksi                | User dapat pilih > 1 invoice dari customer yang sama                   | High      |
| 3   | Mendukung pembayaran parsial                                       | User dapat bayar sebagian dari sisa invoice                            | High      |
| 4   | Mendukung CN (Credit Note) Crossing                                | CN dari retur penjualan bisa offset dengan piutang invoice             | High      |
| 5   | Mengelola deposit/unlocated payment                                | Dana masuk yang belum teralokasi tersimpan dan bisa digunakan nanti    | Medium    |
| 6   | Mengelola kelebihan bayar (lebih bayar)                            | Kelebihan pembayaran otomatis tercatat sebagai saldo                   | Medium    |
| 7   | Otomatisasi jurnal BUM ke General Ledger                           | Posting ke database accounting setelah approval                        | High      |
| 8   | Mencetak Bukti Uang Masuk (BUM)                                    | Output PDF format A5-L yang bisa dicetak                               | Medium    |
| 9   | Mendukung pencatatan Bukti Potong PPH                              | Upload/input data bukti potong dari customer                           | Low       |
| 10  | Kontrol keuangan real-time                                         | Formula Kontrol = 0 menjamin keseimbangan data                         | High      |

---

## 3. Target User & Persona

### 3.1 Staff Accounting (Primary User)

- **Tanggung Jawab:** Input data penerimaan harian, alokasi pembayaran ke invoice
- **Aktivitas Utama:**
  - Membuat penerimaan baru berdasarkan transfer masuk dari bank
  - Memilih invoice yang akan dilunasi (bisa multi-invoice)
  - Menambahkan CN crossing jika ada retur
  - Mengisi biaya admin, PPH, dan selisih
  - Memastikan Kontrol = 0 sebelum simpan
  - Menginput bukti potong PPH dari customer
  - Menginput deposit/unlocated jika dana belum bisa dialokasi
- **Permission:** Penerimaan.View, Penerimaan.Add

### 3.2 Supervisor Accounting (Approver)

- **Tanggung Jawab:** Approval jurnal penerimaan ke GL
- **Aktivitas Utama:**
  - Review data penerimaan yang pending jurnal
  - Approval posting jurnal BUM ke database accounting
  - Verifikasi kelengkapan data sebelum jurnal
- **Permission:** Penerimaan.View, Penerimaan.Add, Penerimaan.Manage

---

## 4. Fitur Utama (Detail)

### 4.1 List Payment (Halaman Utama/Index)

**URL:** `/penerimaan`  
**Permission:** Penerimaan.View  
**View File:** `list_payment.php`

**Deskripsi:** Halaman utama yang menampilkan seluruh transaksi penerimaan yang sudah diinput dalam bentuk DataTable server-side.

**Kolom Tabel:**

| #   | Kolom            | Sumber Data                                                   | Keterangan                        |
| --- | ---------------- | ------------------------------------------------------------- | --------------------------------- |
| 1   | No               | Auto-increment                                                | Nomor urut                        |
| 2   | Tgl Penerimaan   | `tr_invoice_payment.tgl_pembayaran`                           | Format: YYYY-MM-DD                |
| 3   | Kode Penerimaan  | `tr_invoice_payment.kd_pembayaran`                            | Format: PN-YYXNNNNN               |
| 4   | Nama Customer    | `tr_invoice_payment.nm_customer`                              | Nama customer                     |
| 5   | Keterangan       | `tr_invoice_payment.keterangan`                               | Catatan pembayaran                |
| 6   | No Invoice       | GROUP_CONCAT dari `tr_invoice_payment_detail` + `tr_cn_cross` | Gabungan semua nomor invoice & CN |
| 7   | Total Invoice    | SUM(`tr_invoice_payment_detail.total_bayar_idr`)              | Total yang dibayarkan             |
| 8   | PPH              | `tr_invoice_payment.biaya_pph_idr`                            | Potongan PPH                      |
| 9   | Biaya Admin      | `tr_invoice_payment.biaya_admin_idr`                          | Biaya administrasi bank           |
| 10  | Total Penerimaan | `tr_invoice_payment.jumlah_pembayaran_idr`                    | Jumlah bersih diterima            |
| 11  | Option           | Action buttons                                                | View, Jurnal, Bukti Potong        |

**Tombol Aksi per Baris:**

- **View** (kuning, icon eye): Navigasi ke halaman detail penerimaan (`view_penerimaan`)
- **Jurnal** (biru, icon check): Approval jurnal BUM — hanya muncul jika `status_jurnal = 0`
- **Bukti Potong** (hijau, icon cloud-upload): Upload bukti potong PPH — hanya muncul jika `biaya_pph_idr > 0` DAN `bukti_potong` kosong

**Tombol Utama:**

- **Buat Penerimaan**: Navigasi ke form create penerimaan (`modal_detail_invoice`)

---

### 4.2 Create Penerimaan (Form Input Utama)

**URL:** `/penerimaan/modal_detail_invoice/{no_invoice?}`  
**Permission:** Penerimaan.View  
**View File:** `create_penerimaan_new.php`

**Deskripsi:** Form utama untuk menginput transaksi penerimaan pembayaran. Ini adalah fitur inti dari modul.

#### 4.2.1 Header Form

| Field                 | Type        | Required | Editable | Default       | Keterangan                                      |
| --------------------- | ----------- | -------- | -------- | ------------- | ----------------------------------------------- |
| Tgl Bayar             | date        | Ya       | Ya       | Today (Y-m-d) | Tanggal pembayaran diterima                     |
| Keterangan Pembayaran | textarea    | Tidak    | Ya       | Kosong        | Catatan/deskripsi pembayaran                    |
| Jenis PPH             | select      | Tidak    | Ya       | 1102-01-03    | COA PPH (dropdown dari `combo_pph_penjualan`)   |
| Kurs                  | hidden      | Ya       | Tidak    | 1             | Kurs pembayaran (default IDR = 1)               |
| Nama Customer         | select2     | Ya       | Ya       | -             | Dropdown customer (dari tr_invoice grouped)     |
| Pilih Bank            | select      | Ya       | Ya       | -             | COA bank tujuan penerimaan                      |
| Penerimaan Bank       | number/text | Ya       | Ya       | 0             | Jumlah uang masuk dari bank (amount sebenarnya) |
| ID Unlocated          | hidden      | -        | Auto     | -             | ID deposit yang dipakai                         |
| ID Lebih Bayar        | hidden      | -        | Auto     | -             | ID lebih bayar yang dipakai                     |

**Daftar Bank (Hardcoded dalam view):**

| COA        | Nama Bank                               |
| ---------- | --------------------------------------- |
| 1102-01-01 | OCBC NISP - IDR (Mcy) 103-8100-4848-0   |
| 1102-01-02 | OCBC NISP - USD (Mcy) 103-8100-4848-0   |
| 1102-01-03 | OCBC NISP - JPY (Mcy) 103-8100-4848-0   |
| 1102-01-04 | OCBC NISP IDR (GIRO) 103-8000-0040-0    |
| 1102-01-05 | BANK OF CENTRAL ASIA IN RUPIAH          |
| 1102-01-06 | BANK OF CENTRAL ASIA IN US DOLLAR       |
| 1102-01-07 | PERMATA BANK - IDR 8808-0017-156-1780-7 |
| 1102-01-08 | MAY BANK - IDR 2001000198               |
| 1102-01-09 | MAY BANK - USD 2001000093               |
| 1101-02-10 | BANK MANDIRI GIRO (IDR) 1730020202850   |
| 1101-02-11 | BANK MANDIRI (TABUNGAN) 1730099911886   |
| 2101-07-01 | DEPOSIT CUSTOMER                        |

**Catatan:** Jika bank "DEPOSIT CUSTOMER" dipilih, tombol "Deposit" muncul untuk membuka modal unlocated.

#### 4.2.2 Detail Invoice (Tabel Dinamis)

User menambah baris invoice/CN melalui tombol "Add Invoice" yang membuka modal DataTable server-side (`get_invoice_cn_serverside`).

**Kolom Tabel Detail:**

| #   | Kolom         | Name Attr         | Keterangan                                  |
| --- | ------------- | ----------------- | ------------------------------------------- |
| 1   | Code          | `kode_produk[]`   | no_invoice (invoice) atau id_retur (CN)     |
| 2   | No Invoice    | `no_surat[]`      | No surat jalan / No CN                      |
| 3   | Nama Customer | `nm_customer2[]`  | Nama customer                               |
| 4   | Total Invoice | `jml_invoice[]`   | Total invoice (negatif untuk CN)            |
| 5   | Sisa Invoice  | `sisa_invoice[]`  | Sisa yang belum dibayar (negatif untuk CN)  |
| 6   | Total Bayar   | `jml_bayar[]`     | **Editable** - jumlah yang dibayar kali ini |
| 7   | PPH           | `pph[]`           | PPH per baris (hidden, default 0)           |
| 8   | Action        | -                 | Tombol Hapus                                |
| -   | Type          | `type[]` (hidden) | 'invoice' atau 'cn'                         |

**Perilaku CN Row:**

- Total Invoice ditampilkan sebagai nilai negatif (-Rp xxx)
- Sisa Invoice ditampilkan sebagai nilai negatif
- Total Bayar diisi otomatis sebagai nilai negatif (mengurangi total)
- Badge "CN" berwarna biru, badge "Invoice" berwarna hijau

#### 4.2.3 Modal Pilih Invoice + CN (Server-side DataTable)

**Endpoint:** `POST /penerimaan/get_invoice_cn_serverside`  
**Parameter:** `id_customer`, `filter_type` (invoice/cn/all), `search`, `draw`, `start`, `length`

**Logika UNION:**

```sql
-- Invoice: dari tr_invoice WHERE id_customer = X AND sisa_invoice_idr > 0 AND printed_on IS NOT NULL
-- CN: dari tr_retur_penjualan WHERE id_customer = X AND no_cn IS NOT NULL
--     AND (SUM(total_harga) - SUM(amount_crossed)) > 0
```

**Kolom Modal:**

| #   | Kolom         | Keterangan                         |
| --- | ------------- | ---------------------------------- |
| 1   | Type          | Badge: Invoice (grey) / CN (blue)  |
| 2   | Code          | no_invoice atau id_retur           |
| 3   | No Surat      | no_surat (invoice) atau no_cn (CN) |
| 4   | Customer      | nama customer                      |
| 5   | Total Invoice | Rp xxx (invoice) atau -Rp xxx (CN) |
| 6   | Sisa          | Sisa piutang / sisa CN balance     |
| 7   | Action        | Tombol "Pilih"                     |

#### 4.2.4 Summary / Footer Kalkulasi

| #   | Field               | Name Attr            | Formula                                                    | Editable                 |
| --- | ------------------- | -------------------- | ---------------------------------------------------------- | ------------------------ |
| 1   | Total Bayar Invoice | `total_invoice`      | SUM(semua jml_bayar) termasuk CN negatif                   | Readonly (auto)          |
| 2   | Selisih             | `selisih`            | Penerimaan Bank - Total Bayar Invoice                      | Readonly (auto)          |
| 3   | Biaya Administrasi  | `biaya_adm`          | Input manual                                               | Ya                       |
| 4   | PPH                 | `biaya_pph`          | Input manual                                               | Ya                       |
| 5   | Pakai Lebih Bayar   | `pakai_lebih_bayar`  | Dari modal lebih bayar                                     | Conditional              |
| 6   | Lebih Bayar         | `tambah_lebih_bayar` | Kelebihan yg mau disimpan                                  | Ya                       |
| 7   | Total Penerimaan    | `total_terima`       | total_invoice - biaya_adm - biaya_pph + tambah_lebih_bayar | Readonly (auto, hidden)  |
| 8   | **Kontrol**         | `control`            | Selisih + Biaya Adm + PPH - Lebih Bayar                    | **Readonly (HARUS = 0)** |

**Formula Kontrol:**

```
Kontrol = (Penerimaan_Bank - Total_Bayar_Invoice) + Biaya_Adm + PPH - Tambah_Lebih_Bayar
```

Kontrol HARUS = 0 agar data bisa disimpan.

#### 4.2.5 Validasi (Client + Server)

**Client-side (JavaScript):**

1. Tanggal Bayar tidak boleh kosong
2. Bank harus dipilih
3. Kontrol harus = 0
4. CN crossing: `0 < crossing_amount <= CN_Balance` (highlight border merah/hijau)

**Server-side (PHP):**

1. `round(Kontrol, 2) != 0` → reject
2. CN crossing per row: `crossing_amount <= 0` → reject
3. CN crossing per row: `crossing_amount > (SUM(total_harga) - SUM(amount_crossed))` → reject

---

### 4.3 View Penerimaan (Detail Read-Only)

**URL:** `/penerimaan/view_penerimaan/{kd_bayar}`  
**Permission:** Penerimaan.View  
**View File:** `view_penerimaan_new.php`

**Deskripsi:** Menampilkan detail transaksi penerimaan yang sudah tersimpan dalam mode read-only.

**Data yang ditampilkan:**

- Header: Tgl Bayar, Keterangan, Kurs, Customer, Bank, Penerimaan Bank
- Detail table: Code (dengan badge Invoice/CN), No Invoice/No CN, Customer, Total Invoice, Total Bayar, PPH
- Summary: Total Bayar Invoice, Selisih, Biaya Admin, PPH, Pakai Lebih Bayar, Lebih Bayar, Kontrol

**Logika Badge:**

- Jika `total_bayar_idr < 0` ATAU `no_invoice` ditemukan di `tr_cn_cross` → badge "CN" (kuning)
- Selain itu → badge "Invoice" (hijau)
- Untuk CN: display nomor dari `tr_retur_penjualan.no_cn`
- Untuk Invoice: display nomor dari `tr_invoice.no_surat`

---

### 4.4 Jurnal Penerimaan (Approval BUM)

**URL Index:** `/penerimaan/jurnal_bum`  
**URL Approval:** `/penerimaan/appr_jurnal/{kd_bayar}` (via modal jurnal_nomor)  
**Permission:** Penerimaan.Manage  
**View File:** `index_jurnal_penerimaan.php`

**Deskripsi:** Menampilkan daftar penerimaan dengan `status_jurnal = 0` (belum dijurnal) untuk di-approve.

**Kolom Tabel:**

| #   | Kolom            | Keterangan                                      |
| --- | ---------------- | ----------------------------------------------- |
| 1   | No               | Urut                                            |
| 2   | Tgl Penerimaan   | Format: dd-MMMM-YYYY                            |
| 3   | Kode Penerimaan  | kd_pembayaran                                   |
| 4   | Nama Customer    | nm_customer                                     |
| 5   | Keterangan       | keterangan                                      |
| 6   | No Invoice       | GROUP_CONCAT invoiced                           |
| 7   | Total Invoice    | SUM totalinvoiced                               |
| 8   | PPH              | biaya_pph_idr                                   |
| 9   | Biaya Admin      | biaya_admin_idr                                 |
| 10  | Lebih Bayar      | tambah_lebih_bayar                              |
| 11  | Total Penerimaan | jumlah_pembayaran_idr                           |
| 12  | Option           | Tombol "Create Jurnal" (jika MANAGE permission) |

**Proses Approval Jurnal (method `appr_jurnal`):**

1. Ambil data header dari `tr_invoice_payment`
2. Generate nomor BUM via `Jurnal_model->get_Nomor_Jurnal_BUM('101', tgl)`
3. Compose keterangan: `PENERIMAAN MULTI INVOICE A/N {customer} INV NO. {kd_bayar} Keterangan: {ket}`
4. INSERT `jarh` (header jurnal) ke database `gl_metalsindo_live`
5. INSERT `jurnal` (detail) — multiple rows debit/kredit
6. UPDATE AR di database accounting
7. INSERT `tr_kartu_piutang` per invoice detail
8. UPDATE `tr_invoice_payment.status_jurnal = 1`
9. UPDATE counter `pastibisa_tb_cabang.nobum += 1`
10. Auto-redirect ke print BUM (PDF)

**Detail Jurnal yang Di-generate:**

| #   | COA                      | Debit                 | Kredit                      | Kondisi                         |
| --- | ------------------------ | --------------------- | --------------------------- | ------------------------------- |
| 1   | {kd_bank} (dari header)  | jumlah_bank_idr       | -                           | Selalu                          |
| 2   | 7205-01-01 (Biaya Admin) | biaya_admin_idr       | -                           | Jika biaya_admin != 0           |
| 3   | 2109-02-01 (Deposit)     | lebih_bayar           | -                           | Jika lebih_bayar != 0           |
| 4   | {jenis_pph} (PPH)        | biaya_pph per invoice | -                           | Jika biaya_pph != 0, per detail |
| 5   | 1102-01-01 (Piutang)     | -                     | total_bayar_idr per invoice | Per detail invoice              |

---

### 4.5 Penerimaan Unlocated (Deposit Customer)

**URL:** `/penerimaan/unlocated` atau `/penerimaan/createunlocated`  
**Permission:** Penerimaan.Add  
**View File:** `create_unlocated.php`

**Deskripsi:** Mencatat penerimaan bank yang belum bisa dialokasikan ke invoice spesifik (misal: transfer masuk tapi belum tahu untuk invoice mana).

**Form Fields:**

| Field      | Type   | Required | Keterangan         |
| ---------- | ------ | -------- | ------------------ |
| Pilih Bank | select | Ya       | COA bank tujuan    |
| Tanggal    | date   | Ya       | Tanggal penerimaan |

**Detail (Dynamic Rows):**

| Field            | Keterangan                        |
| ---------------- | --------------------------------- |
| Keterangan       | Nama customer / deskripsi deposit |
| Total Penerimaan | Jumlah uang masuk                 |

User bisa add multiple rows (multiple deposit sekaligus).

**Proses Save:**

1. INSERT ke `tr_unlocated_bank` (per row)
2. Generate nomor BUM
3. INSERT jurnal: Debit {bank} / Kredit 2101-08-01 (Deposit Customer)
4. UPDATE counter BUM di `pastibisa_tb_cabang`

**Penggunaan Deposit:**

- Saat create penerimaan biasa, jika bank dipilih = "DEPOSIT CUSTOMER" (2101-07-01), tombol Deposit muncul
- Modal menampilkan list `tr_unlocated_bank WHERE saldo != 0`
- User pilih deposit → `total_bank` terisi otomatis, `id_unlocated` tersimpan
- Setelah penerimaan disimpan: `UPDATE tr_unlocated_bank SET saldo = saldo - {amount} WHERE id = {id_unlocated}`

---

### 4.6 Penerimaan Lebih Bayar

**URL:** `/penerimaan/lebihbayar` (form) dan `/penerimaan/TambahLebihBayar/{customer}` (modal list)  
**Permission:** Penerimaan.Add  
**View File:** `create_lebihbayar.php`, `lebihbayar.php`

**Deskripsi:** Mengelola kelebihan pembayaran dari customer yang bisa digunakan di transaksi berikutnya.

**Mekanisme:**

- Saat create penerimaan, jika `tambah_lebih_bayar > 0`:
  - INSERT ke `tr_unlocated_bank` sebagai saldo baru
  - Generate jurnal: Debit {bank} / Kredit 2109-02-01
- Saat create penerimaan, user bisa "Pakai Lebih Bayar" dari modal:
  - Menampilkan `tr_lebihbayar_bank WHERE saldo != 0 AND id_customer = {customer}`
  - Saldo lebih bayar akan di-offset dengan total pembayaran

---

### 4.7 Upload Bukti Potong PPH

**URL:** `/penerimaan/penerimaan_buktipotong/{kd_bayar}` (modal via AJAX)  
**Permission:** Penerimaan.Manage  
**View File:** `form_buktipotong.php`

**Deskripsi:** Jika transaksi penerimaan memiliki PPH > 0 dan belum ada bukti potong, supervisor bisa menginput data bukti potong dari customer.

**Form Fields:**

| Field              | Type              | Required | Keterangan                                     |
| ------------------ | ----------------- | -------- | ---------------------------------------------- |
| Nomor Bukti Potong | text              | Ya       | Nomor dokumen bukti potong                     |
| Tanggal Terima     | date (datepicker) | Ya       | Tanggal terima bukti potong                    |
| No Invoice         | select            | Ya       | Pilih invoice terkait (dari detail pembayaran) |

**Data Tersimpan:** `tr_invoice_bukti_potong` (kd_pembayaran, no_invoice, no_bukti_potong, tgl_terima, created_by, created_date)

**Tampilan:** Selain form input, view juga menampilkan tabel history bukti potong yang sudah diinput untuk kd_pembayaran tersebut.

---

### 4.8 Cetak Bukti Uang Masuk (BUM)

**Triggered by:** Approval Jurnal (`appr_jurnal`)  
**Format:** PDF A5-Landscape via mPDF  
**View File:** `print_penerimaan.php`

**Konten Dokumen:**

**Header:**

- Logo perusahaan
- Judul: "BUKTI UANG MASUK"
- Kode Penerimaan | Customer
- Tgl Terima | Alamat Customer
- Bank | -
- Keterangan | -

**Body (Tabel):**

| Nomor Invoice | Customer | Total Invoice | Total PPH | Sisa Invoice | Total Bayar |
| ------------- | -------- | ------------- | --------- | ------------ | ----------- |
| ...           | ...      | ...           | ...       | ...          | ...         |

**Footer Summary:**

- Total Bayar (SUM)
- Administrasi Bank
- PPH {nama COA PPH}
- Total Penerimaan

**Footer Dokumen:**

- "PT IDEFAB CIPTA - Printed By {username} On {datetime}"

---

## 5. Aturan Bisnis (Business Rules)

### 5.1 Kode Pembayaran (Auto-Generate)

| Komponen  | Format                             | Contoh          |
| --------- | ---------------------------------- | --------------- |
| Prefix    | PN-                                | PN-             |
| Tahun     | 2 digit (YY)                       | 24              |
| Bulan     | 1 huruf (A=Jan, B=Feb, ..., L=Des) | A               |
| Sequence  | 5 digit zero-padded                | 00001           |
| **Hasil** | **PN-YYXNNNNN**                    | **PN-24A00001** |

**Mapping Bulan:**

```
Januari=A, Februari=B, Maret=C, April=D, Mei=E, Juni=F,
Juli=G, Agustus=H, September=I, Oktober=J, November=K, Desember=L
```

**Logika Generate:**

1. Cek MAX(kd_pembayaran) WHERE kd_pembayaran LIKE 'PN-{YY}{BLN}%'
2. Jika tidak ada → mulai dari 00001
3. Jika ada → ambil 5 digit terakhir + 1, zero-pad

### 5.2 Aturan Pembayaran

| #   | Rule                 | Detail                                                                                          |
| --- | -------------------- | ----------------------------------------------------------------------------------------------- |
| 1   | Multi-Invoice        | Satu transaksi penerimaan bisa melunasi lebih dari 1 invoice dari customer yang sama            |
| 2   | Parsial Payment      | User boleh bayar sebagian dari sisa invoice (tidak harus lunas)                                 |
| 3   | Kontrol = 0          | Formula: (Bank - Total_Invoice) + Biaya_Adm + PPH - Lebih_Bayar HARUS = 0                       |
| 4   | Invoice syarat cetak | Hanya invoice yang sudah `printed_on IS NOT NULL` yang muncul di list pilihan                   |
| 5   | Sisa invoice update  | Setelah save: `tr_invoice.sisa_invoice_idr -= total_bayar` dan `total_bayar_idr += total_bayar` |
| 6   | SPK Marketing update | Setelah save: `tr_spk_marketing.total_bayar_so += total_bayar`                                  |
| 7   | Jurnal terpisah      | Jurnal BUM TIDAK otomatis saat save — harus melalui proses approval terpisah                    |
| 8   | Status jurnal        | `0` = belum dijurnal, `1` = sudah dijurnal                                                      |

### 5.3 Aturan CN Crossing

| #   | Rule                    | Detail                                                                              |
| --- | ----------------------- | ----------------------------------------------------------------------------------- |
| 1   | CN Balance              | `CN_Balance = SUM(dt_returpenjualan.total_harga) - SUM(tr_cn_cross.amount_crossed)` |
| 2   | Crossing amount         | Harus > 0 DAN <= CN_Balance                                                         |
| 3   | CN tampil negatif       | Di detail tabel, CN ditampilkan dan dihitung sebagai nilai negatif                  |
| 4   | Insert tr_cn_cross      | `amount_crossed` disimpan sebagai positif (abs)                                     |
| 5   | Insert payment_detail   | Untuk CN row, `total_bayar_idr` disimpan sebagai nilai negatif                      |
| 6   | Tidak update tr_invoice | CN row TIDAK mengupdate sisa invoice di tr_invoice                                  |

### 5.4 Aturan Deposit/Unlocated

| #   | Rule                    | Detail                                                         |
| --- | ----------------------- | -------------------------------------------------------------- |
| 1   | Simpan deposit          | INSERT ke `tr_unlocated_bank` dengan `saldo = totalpenerimaan` |
| 2   | Pakai deposit           | Saat penerimaan: `saldo -= amount_dipakai`                     |
| 3   | Generate jurnal deposit | Debit Bank / Kredit 2101-08-01                                 |
| 4   | Filter deposit          | Hanya yang `saldo != 0` yang muncul di list                    |

### 5.5 Aturan Lebih Bayar

| #   | Rule              | Detail                                             |
| --- | ----------------- | -------------------------------------------------- |
| 1   | Terjadi jika      | `tambah_lebih_bayar > 0` pada saat save penerimaan |
| 2   | Simpan            | INSERT ke `tr_unlocated_bank` sebagai saldo baru   |
| 3   | Generate jurnal   | Debit Bank / Kredit 2109-02-01                     |
| 4   | Pakai lebih bayar | User pilih dari modal per customer                 |

---

## 6. Permissions & Access Control

| Permission Key    | Deskripsi               | Fitur yang Dibuka                                           |
| ----------------- | ----------------------- | ----------------------------------------------------------- |
| Penerimaan.View   | Melihat data penerimaan | List, View Detail, DataTable, Invoice Siap Terima           |
| Penerimaan.Add    | Membuat penerimaan baru | Create Penerimaan, Save, Create Unlocated, Save Lebih Bayar |
| Penerimaan.Manage | Kelola & approval       | Approval Jurnal BUM, Upload Bukti Potong                    |
| Penerimaan.Delete | Hapus/cancel data       | Cancel penerimaan (belum diimplementasi penuh)              |

---

## 7. Integrasi Modul & Dependensi

### 7.1 Modul Internal

| Modul           | Relasi    | Detail                                              |
| --------------- | --------- | --------------------------------------------------- |
| Wt_invoicing    | Input     | Sumber data invoice (tr_invoice, tr_invoice_detail) |
| SPK Marketing   | Update    | Update total_bayar_so setelah penerimaan            |
| Retur Penjualan | Input     | Sumber CN (tr_retur_penjualan, dt_returpenjualan)   |
| Jurnal Nomor    | Generate  | Generate nomor BUM, Get COA Bank, combo PPH         |
| Delivery Order  | Referensi | No DO pada invoice                                  |

### 7.2 Database External

| Database           | Tabel               | Aksi                              |
| ------------------ | ------------------- | --------------------------------- |
| gl_metalsindo_live | jarh                | INSERT header jurnal              |
| gl_metalsindo_live | jurnal              | INSERT detail jurnal debit/kredit |
| gl_metalsindo_live | ar                  | UPDATE kredit & saldo_akhir       |
| gl_metalsindo_live | pastibisa_tb_cabang | UPDATE counter nobum              |
| gl_metalsindo_live | coa_master          | READ nama bank/akun               |

### 7.3 Library External

| Library   | Fungsi                             |
| --------- | ---------------------------------- |
| mPDF      | Generate PDF Bukti Uang Masuk      |
| Template  | Render view dengan layout AdminLTE |
| Image_lib | (loaded tapi tidak dipakai aktif)  |
| Upload    | (loaded tapi tidak dipakai aktif)  |

---

## 8. Non-Functional Requirements

| Aspek            | Requirement                   | Implementasi                                              |
| ---------------- | ----------------------------- | --------------------------------------------------------- |
| Performa         | Handling data besar tanpa lag | DataTable server-side processing                          |
| Keamanan         | Role-based access             | Permission check per method via `$this->auth->restrict()` |
| Audit Trail      | Traceability                  | `created_by`, `created_on` pada setiap INSERT             |
| Konsistensi Data | Balance check                 | Kontrol = 0 validation (client + server)                  |
| Availability     | Multi-currency support        | Kurs field (default 1 untuk IDR)                          |
| Output           | Cetak dokumen                 | PDF A5-L via mPDF                                         |
| UX               | Responsive                    | Bootstrap 3 + AdminLTE + DataTables responsive            |
| Data Integrity   | Transaction safety            | `db->trans_status()` check setelah batch operations       |

---

## 9. Known Limitations & Technical Debt

| #   | Item                           | Keterangan                                                            |
| --- | ------------------------------ | --------------------------------------------------------------------- |
| 1   | Bank hardcoded                 | Daftar bank di-hardcode di view, bukan dari database                  |
| 2   | No transaction wrapping        | `db->trans_start()` tidak dipanggil, hanya `trans_status()` di akhir  |
| 3   | Legacy views                   | Ada `view_penerimaan.php` (lama) dan `view_penerimaan_new.php` (baru) |
| 4   | Cancel belum full              | Permission Delete ada tapi fitur cancel belum fully implemented       |
| 5   | Jurnal approval tidak rollback | Jika jurnal gagal di GL, tidak ada mekanisme rollback otomatis        |
| 6   | SQL Injection risk             | Beberapa query masih pakai string concatenation tanpa parameterized   |
