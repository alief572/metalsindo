# PRD - Modul SPK Marketing

## 1. Ringkasan

Modul SPK Marketing (Surat Perintah Kerja Marketing) digunakan untuk membuat, mengelola, dan memproses order dari customer berdasarkan penawaran yang sudah dibuat sebelumnya. SPK Marketing merupakan dokumen perintah kerja internal yang menjembatani antara penawaran ke proses produksi/pengiriman.

## 2. Stakeholder

- **Marketing** — Membuat dan mengelola SPK
- **Admin** — Approval SPK
- **Produksi/Warehouse** — Menerima SPK sebagai perintah kerja

## 3. Fitur Utama

### 3.1 CRUD SPK Marketing

- **Add SPK Marketing (Coil/Reguler)** — Membuat SPK baru berdasarkan penawaran
- **Add SPK Marketing (Sheet)** — Membuat SPK khusus bentuk Sheet
- **Add SPK Marketing (Slitting)** — Membuat SPK khusus Slitting
- **Edit SPK Marketing** — Mengedit SPK yang sudah dibuat
- **Revisi SPK Marketing** — Merevisi SPK (menambah item dari penawaran)
- **View SPK Marketing** — Melihat detail SPK
- **Print SPK Marketing** — Cetak SPK dalam format PDF

### 3.2 Alur Bisnis

1. Marketing memilih Customer
2. Sistem menampilkan penawaran yang tersedia untuk customer tersebut (status = 'N')
3. Marketing memilih No. Penawaran
4. Sistem menampilkan item-item dari penawaran tersebut
5. Marketing mengisi: Harga Deal, Qty, Delivery Date, CRCL, dan centang Deal
6. Marketing menyimpan SPK

### 3.3 Data Header SPK

- No. SPK (auto-generate)
- Tanggal SPK
- Customer
- No. Penawaran
- No. PO
- Sample
- Tgl PO
- Date Plan by Customer
- Note
- Tipe (reguler/slitting)

### 3.4 Data Detail SPK (Per Item)

- Nama Material (dari penawaran)
- Thickness, Width, Length
- Part Number
- Harga Penawaran
- Harga Deal / Kg
- Qty (KG)
- Total Harga (Harga Deal × Qty)
- Delivery Date
- CRCL
- Keterangan
- Deal (checkbox)

### 3.5 Fitur Diskon (BARU - Akan Diimplementasi)

#### Diskon Per Item

- Kolom "Disc" ditambahkan setelah kolom "Harga Deal / Kg"
- User dapat input nominal diskon per item
- Diskon per item disimpan di tabel `dt_spkmarketing` kolom `nominal_discount`

#### Diskon Keseluruhan SPK

- Di footer tabel ditampilkan: Total Harga, Discount, Grand Total
- Discount di footer bisa diisi manual ATAU otomatis ter-sum dari diskon per item
- Diskon keseluruhan disimpan di tabel `tr_spk_marketing` kolom `total_discount`

#### Logika Interaksi Diskon

- Jika user input diskon **per item** → footer Discount otomatis = sum semua diskon per item
- Jika user input diskon **di footer** → semua diskon per item di-reset ke 0
- Total Harga per baris = Harga Deal × Qty (tanpa dikurangi diskon)
- Grand Total = Total Harga (sum) - Discount

#### Validasi

- Diskon tidak boleh melebihi total harga
- Jika diskon > total harga, tampilkan warning saat user mau save

## 4. Permission

- `SPK_marketing.View` — Melihat data
- `SPK_marketing.Add` — Menambah data
- `SPK_marketing.Manage` — Mengelola data
- `SPK_marketing.Delete` — Menghapus data
