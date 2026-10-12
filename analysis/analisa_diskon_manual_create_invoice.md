# Analisa Perubahan: Diskon Manual pada Create Invoice

## Informasi Dokumen

| Item            | Detail                                                            |
| --------------- | ----------------------------------------------------------------- |
| Modul           | WT Invoicing (`wt_invoicing/createInvoice`)                       |
| Tanggal Analisa | 24 Juli 2026                                                      |
| Dibuat Oleh     | Development Team                                                  |
| Status          | Draft — Menunggu Review BA                                        |
| Scope           | Form Create Invoice (dari SPK & DO), Print Invoice, Halaman Index |

---

## 1. Latar Belakang

Saat ini, kolom **diskon** pada form Create Invoice bersifat **readonly** (tidak bisa diedit oleh user).
Nilai diskon otomatis diambil dari tabel master `ms_diskon` berdasarkan:

- **Tipe produk** (`id_type` dari `ms_inventory_category3`)
- **Term of Payment** (`id_top` dari `ms_top`)

### Request Perubahan

User ingin kolom diskon bisa **diinput secara manual** (free text nominal Rupiah),
tanpa harus terikat dengan master diskon (`ms_diskon`).

Validasi yang diminta hanya: **nilai diskon tidak boleh melebihi total Amount**.

---

## 2. Kondisi Sistem Saat Ini

### 2.1 Flow Diskon di Form Create Invoice

```
┌─────────────────────────────────────────────────────────────────┐
│ FLOW SAAT INI (READONLY)                                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Master ms_diskon ──→ CariDiskon() via AJAX ──→ Nilai Diskon %   │
│       (per tipe produk + TOP)                    (auto-fill)      │
│                                                                   │
│  Per Item:                                                        │
│    Total Harga = Qty × Harga Satuan                               │
│    Nilai Diskon = Total Harga × (Diskon % / 100)                  │
│    Amount Item = Total Harga - Nilai Diskon + Freight             │
│                                                                   │
│  Footer:                                                          │
│    Amount     = SUM(Amount Item)     ← readonly                   │
│    Discount   = SUM(Nilai Diskon)    ← readonly (dari master)     │
│    Total      = Amount - Discount    ← readonly                   │
│    Persentase = % invoice            ← editable                   │
│    Grand Total= Total × Persentase%  ← readonly                   │
│    DPP        = Grand Total          ← readonly                   │
│    PPN        = DPP × PPN%           ← readonly                   │
│    Total Inv  = Grand Total + PPN    ← readonly                   │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 Tabel Database yang Terlibat

| Tabel               | Kolom                          | Keterangan                               |
| ------------------- | ------------------------------ | ---------------------------------------- |
| `tr_invoice`        | `diskon` (double 40,2)         | Total diskon header (nominal Rp)         |
| `tr_invoice`        | `total` (double 40,2)          | Total setelah diskon (Amount - Discount) |
| `tr_invoice`        | `nilai_invoice` (double 40,2)  | Nilai tagihan final (incl. PPN)          |
| `tr_invoice_detail` | `diskon` (double 40,2)         | Persentase diskon per item               |
| `tr_invoice_detail` | `nilai_diskon` (double 50,2)   | Nilai diskon per item (Rp)               |
| `tr_invoice_detail` | `diskon_compare` (double 40,2) | Diskon pembanding (dari master)          |
| `ms_diskon`         | `nilai_diskon`                 | Master diskon per tipe produk + TOP      |

### 2.3 File yang Terkait

| File                                                         | Fungsi                                  |
| ------------------------------------------------------------ | --------------------------------------- |
| `views/create_invoice.php`                                   | Form create invoice dari SPK Marketing  |
| `views/create_invoice_do.php`                                | Form create invoice dari Delivery Order |
| `views/create_invoice_do_slitting.php`                       | Form create invoice DO Slitting         |
| `controllers/Wt_invoicing.php` → `SaveNewInvoice()`          | Simpan data invoice                     |
| `controllers/Wt_invoicing.php` → `CariDiskon()`              | AJAX ambil diskon dari master           |
| `models/Wt_invoicing_model.php` → `get_invoicing()`          | Data untuk halaman index                |
| `models/Wt_invoicing_model.php` → `get_monitoring_invoice()` | Data monitoring                         |
| `views/PrintInvoice.php`                                     | Cetak PDF invoice                       |

---

## 3. Masalah yang Ditemukan (Problem Statement)

### 3.1 Masalah Utama: Perbedaan Basis Hitung (KG vs Sheet)

Ini adalah masalah **paling krusial** yang harus dipahami sebelum implementasi.

#### Konteks

Produk di Metalsindo ada 2 jenis berdasarkan bentuk material:

- **Reguler (Coil/Plate)** → satuan **KG**
- **Sheet** (`id_bentuk = 'B2000002'`) → satuan **Lembar (Sheets)**

#### Di Form Create Invoice:

Semua produk dihitung dengan basis **KG**:

```
Qty     = total_kirim (KG)     → misal: 2.500 KG
Per Unit = harga_deal (per KG)  → misal: Rp 10.000/KG
Amount  = 2.500 × 10.000       = Rp 25.000.000
```

#### Di Print Invoice (PDF):

Untuk produk **Sheet**, sistem menghitung ulang dengan basis **Lembar**:

```
Qty     = qty_sheet (lembar)    → misal: 500 Sheets
Per Unit = harga_satuan (per sheet) → misal: Rp 50.000/sheet
Amount  = 500 × 50.000          = Rp 25.000.000
```

#### Di Halaman Index & Monitoring:

Untuk produk **Sheet**, `nilai_invoice` juga **dihitung ulang** menggunakan `qty_sheet`:

```php
$nilai_invoice += ($item_sheet->harga_satuan * $qty_sheet)
                + (($item_sheet->harga_satuan * $qty_sheet) * 11 / 100);
```

#### Ilustrasi Masalah

```
┌─────────────────────────────────────────────────────────────────────┐
│                                                                       │
│  FORM CREATE (basis KG)           PRINT INVOICE (basis Sheet)         │
│  ─────────────────────           ───────────────────────────          │
│  Qty: 2.500 KG                   Qty: 500 Sheets                     │
│  Harga: Rp 10.000/KG             Harga: Rp 50.000/sheet              │
│  Amount: Rp 25.000.000           Amount: Rp 25.000.000               │
│                                                                       │
│  User input diskon:              Customer lihat di invoice:           │
│  → Rp 2.500.000                  → Amount: Rp 25.000.000             │
│                                  → Diskon: Rp 2.500.000              │
│  Pertanyaan:                                                          │
│  "User hitung diskon dari mana?  "Diskon Rp 2.500.000 itu           │
│   Dari KG atau dari Sheet?"       per sheet berapa?"                  │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

**Intinya:** User di form create melihat basis KG, tapi customer di invoice melihat basis Sheet. Jika user terbiasa menghitung diskon per-sheet (misal Rp 5.000/sheet × 500 = Rp 2.500.000), mereka tidak bisa langsung validasi angka tersebut di form create karena qty yang tampil adalah KG.

---

### 3.2 Masalah Kedua: Print Invoice Tidak Menampilkan Diskon

Saat ini, template `PrintInvoice.php` **TIDAK menampilkan baris Discount** sama sekali.

Footer di PDF invoice hanya menampilkan:

```
| Total          | Rp 25.000.000 |
| DPP Nilai Lain | Rp 22.916.667 |
| PPN            | Rp  2.750.000 |
| Grand Total    | Rp 27.750.000 |
```

Tidak ada baris "Discount" di antara Total dan DPP. Jadi meskipun diskon sudah di-input, **customer tidak akan melihat potongan diskon di invoice cetak**.

---

### 3.3 Masalah Ketiga: Halaman Index & Monitoring Tidak Memperhitungkan Diskon

Di model `get_invoicing()` dan `get_monitoring_invoice()`, nilai invoice yang ditampilkan di halaman daftar **TIDAK memperhitungkan diskon**.

**Untuk produk non-sheet:**

```php
// Hitung dari detail: SUM(qty_invoice × harga_satuan)
$ttl_harga = $get_total_invoice->ttl_harga;
$nilai_invoice = $ttl_harga + $ppn;
// ← TIDAK ada pengurangan diskon
```

**Untuk produk sheet:**

```php
// Hitung dari qty_sheet × harga_satuan
$nilai_invoice += ($item_sheet->harga_satuan * $qty_sheet) + PPN;
// ← TIDAK ada pengurangan diskon
```

**Akibatnya:** Nilai invoice yang tampil di halaman index/monitoring bisa **lebih besar** dari yang seharusnya (karena belum dikurangi diskon).

---

### 3.4 Masalah Keempat: Dampak ke E-Faktur / CoreTax

Di fungsi `generate_efaktur()`, kolom **"Total Diskon"** di Sheet DetailFaktur diisi `0`. Perhitungan DPP dan PPN menggunakan:

```
DPP = Qty × Harga Satuan (tanpa kurangi diskon)
DPP Nilai Lain = ceil(11/12 × DPP)
PPN = DPP Nilai Lain × 12%
```

Jika diskon manual dimasukkan tapi DPP di e-Faktur tetap dihitung tanpa diskon, maka:

- Nilai DPP yang dilaporkan ke pajak **lebih besar** dari nilai sebenarnya yang ditagihkan
- Potensi lebih bayar PPN

---

### 3.5 Masalah Kelima: Update Persentase di SPK Marketing

Saat save invoice, ada update ke `tr_spk_marketing`:

```sql
UPDATE tr_spk_marketing
SET percent_invoice = percent_invoice + $percent,
    total_invoice = total_invoice + $dpp
WHERE id_spkmarketing = '$no_so'
```

Nilai `$dpp` diambil dari form. Jika diskon mengubah nilai DPP, maka akumulasi `total_invoice` di SPK Marketing harus konsisten. Jika tidak, monitoring persentase penyelesaian invoice akan salah.

---

### 3.6 Masalah Keenam: Tidak Ada Audit Trail Diskon Manual

Saat ini kolom `diskon_compare` di `tr_invoice_detail` tersedia tapi tidak digunakan secara aktif. Jika diskon bisa manual, tidak ada catatan:

- Siapa yang mengubah diskon
- Berapa diskon seharusnya (dari master)
- Alasan pemberian diskon khusus

---

## 4. Opsi Solusi

### Opsi A: Input Diskon sebagai Nominal Total (Rupiah) di Footer

**Deskripsi:**
Field diskon di footer form (yang saat ini readonly) dibuat editable. User menginput **nominal total Rupiah** diskon secara langsung.

**Cara Kerja:**

```
Amount (readonly)    = SUM dari semua item (auto-calculate)
Discount (EDITABLE)  = User input nominal Rp (misal: 2.500.000)
Total (readonly)     = Amount - Discount (auto-calculate)
... sisa perhitungan sama seperti sekarang ...
```

**Kelebihan:**

- ✅ Implementasi paling sederhana (ubah `readonly` jadi editable + tambah JS recalculate)
- ✅ Tidak perlu ubah struktur database
- ✅ User tidak perlu pusing soal basis KG atau Sheet — langsung input nominal akhir
- ✅ Berlaku universal untuk semua tipe produk (reguler, sheet, slitting)
- ✅ Controller `SaveNewInvoice()` tidak perlu diubah (sudah ambil dari `$post['diskon']`)

**Kekurangan:**

- ❌ Tidak ada breakdown diskon per item (hanya total)
- ❌ User harus hitung sendiri di luar sistem jika diskon-nya per-sheet/per-KG
- ❌ Jika invoice punya banyak item dengan diskon berbeda, susah di-trace

**Validasi:**

- Diskon tidak boleh > Amount
- Diskon tidak boleh negatif
- Diskon hanya angka (numeric validation)

---

### Opsi B: Input Diskon Per-Item (Nominal Rp per Baris)

**Deskripsi:**
Setiap baris item di tabel detail ditambahkan kolom "Diskon (Rp)" yang bisa di-input manual. Total diskon di footer = SUM diskon per item.

**Cara Kerja:**

```
Per Item:
  Amount Item  = Qty × Harga Satuan
  Diskon Item  = User input nominal Rp
  Net Item     = Amount Item - Diskon Item

Footer:
  Amount    = SUM(Amount Item)
  Discount  = SUM(Diskon Item)
  Total     = SUM(Net Item)
```

**Kelebihan:**

- ✅ Granular — bisa trace diskon per produk
- ✅ Lebih akurat untuk pelaporan
- ✅ Bisa digunakan untuk e-Faktur (diskon per item)

**Kekurangan:**

- ❌ Lebih complex (perlu tambah kolom di tabel, ubah UI per row)
- ❌ Tetap ada masalah sheet vs KG (user lihat qty KG tapi hitung diskon per sheet)
- ❌ Perlu ubah JavaScript `HitungTotal()` secara signifikan
- ❌ Perlu ubah `SaveNewInvoice()` untuk handle nominal (saat ini kolom `diskon` di detail = persentase)

---

### Opsi C: Input Diskon Per-Item (Persentase %) — Editable

**Deskripsi:**
Sama seperti sekarang (diskon dalam persentase per item), tapi field-nya dibuat **editable** (tidak lagi dari master).

**Cara Kerja:**

```
Per Item:
  Amount Item   = Qty × Harga Satuan
  Diskon %      = User input (misal: 10%)  ← saat ini auto dari master
  Nilai Diskon  = Amount Item × Diskon% / 100
  Net Item      = Amount Item - Nilai Diskon

Footer:
  Amount    = SUM(Amount Item)
  Discount  = SUM(Nilai Diskon)
  Total     = Amount - Discount
```

**Kelebihan:**

- ✅ Perubahan minimal dari kondisi saat ini (hanya hapus `readonly`)
- ✅ JavaScript `HitungTotal()` sudah support format ini
- ✅ Struktur database tidak perlu diubah

**Kekurangan:**

- ❌ User harus convert diskon nominal ke persentase (tidak intuitif)
- ❌ Masalah sheet: persentase dihitung dari Amount basis KG, tapi print basis Sheet
- ❌ Untuk diskon flat nominal (bukan persentase), user kesulitan

---

### Opsi D: Hybrid — Diskon Total (Nominal) + Override per Item (Opsional)

**Deskripsi:**

- Field diskon di **footer** bisa di-input manual (nominal Rp) — ini yang utama
- Opsional: user bisa klik expand untuk isi diskon per item (untuk kebutuhan detail)
- Jika diskon footer di-input, otomatis distribute rata ke semua item (atau dibiarkan di header saja)

**Kelebihan:**

- ✅ Fleksibel — bisa simple (total) atau detail (per item)
- ✅ Solve masalah sheet vs KG (user input total aja)

**Kekurangan:**

- ❌ Paling complex untuk diimplementasikan
- ❌ UX bisa membingungkan jika tidak di-design dengan baik

---

## 5. Analisa Dampak Per Opsi

| Aspek                  | Opsi A (Total Rp)   | Opsi B (Per Item Rp)              | Opsi C (Per Item %) | Opsi D (Hybrid)    |
| ---------------------- | ------------------- | --------------------------------- | ------------------- | ------------------ |
| Effort Development     | 🟢 Rendah           | 🟡 Sedang                         | 🟢 Rendah           | 🔴 Tinggi          |
| Perubahan Database     | Tidak perlu         | Perlu (kolom baru atau ubah tipe) | Tidak perlu         | Tergantung         |
| Solve masalah Sheet/KG | ✅ Ya               | ❌ Tidak langsung                 | ❌ Tidak            | ✅ Ya              |
| Intuitif untuk User    | ✅ Sangat           | 🟡 Cukup                          | ❌ Kurang           | 🟡 Cukup           |
| Akurasi E-Faktur       | 🟡 Perlu adjustment | ✅ Akurat per item                | ✅ Akurat per item  | ✅ Akurat          |
| Print Invoice          | Perlu tambah baris  | Perlu tambah baris                | Perlu tambah baris  | Perlu tambah baris |
| Halaman Index          | Perlu adjustment    | Perlu adjustment                  | Perlu adjustment    | Perlu adjustment   |
| Risiko Bugs            | 🟢 Rendah           | 🟡 Sedang                         | 🟢 Rendah           | 🔴 Tinggi          |

---

## 6. Rekomendasi: Opsi A (Input Diskon Nominal Total di Footer)

### Alasan Pemilihan

1. **Solve masalah KG vs Sheet** — User tidak perlu bingung basis hitungnya apa. Tinggal input nominal final yang sudah mereka hitung sendiri (mau dari KG, Sheet, atau negosiasi langsung).

2. **Effort minimal** — Hanya perlu:
   - Hapus `readonly` dari field diskon
   - Tambah JavaScript recalculate
   - Tambah validasi (diskon ≤ Amount)
   - Tambah baris "Discount" di PrintInvoice.php

3. **Tidak merusak yang sudah jalan** — Controller `SaveNewInvoice()` sudah mengambil `$post['diskon']` dan menyimpannya. Tidak perlu ubah logic save.

4. **Konsisten** — Berlaku sama untuk semua tipe invoice (reguler, sheet, slitting).

---

## 7. Detail Implementasi (Opsi A)

### 7.1 Perubahan di View (Form Create Invoice)

**File:** `create_invoice_do.php`, `create_invoice.php`, `create_invoice_do_slitting.php`

**Sebelum:**

```html
<input
  type="text"
  class="form-control diskon"
  id="diskon"
  name="diskon"
  readonly
  value="<?= number_format($diskon, 2) ?>"
/>
```

**Sesudah:**

```html
<input
  type="text"
  class="form-control diskon"
  id="diskon"
  name="diskon"
  onblur="hitungDiskon()"
  value="<?= number_format($diskon, 2) ?>"
/>
```

### 7.2 Perubahan JavaScript

**Tambah fungsi baru:**

```javascript
function hitungDiskon() {
  var amount = getNum($("#totalproduk").val().split(",").join(""));
  var diskon = getNum($("#diskon").val().split(",").join(""));

  // Validasi: diskon tidak boleh > amount
  if (diskon > amount) {
    swal("Warning", "Diskon tidak boleh melebihi total Amount!", "error");
    $("#diskon").val("0.00");
    diskon = 0;
  }

  // Validasi: diskon tidak boleh negatif
  if (diskon < 0) {
    swal("Warning", "Diskon tidak boleh negatif!", "error");
    $("#diskon").val("0.00");
    diskon = 0;
  }

  var total = amount - diskon;
  $("#total").val(number_format(total, 2));
  $("#diskon").val(number_format(diskon, 2));

  hitungPpn();
}
```

**Ubah fungsi `totalBalanced()`:**

```javascript
function totalBalanced() {
  var SUMx = 0;
  $(".total").each(function () {
    SUMx += Number($(this).val().split(",").join(""));
  });

  $("#totalproduk").val(number_format(SUMx, 2));

  // Recalculate dengan diskon yang ada
  hitungDiskon();
}
```

**Ubah fungsi `hitungPpn()`:**

```javascript
function hitungPpn() {
  var total = getNum($("#total").val().split(",").join("")); // ← pakai #total (setelah diskon)
  var ppn = $("#ppn").val();
  var persentase = $("#persentase").val();

  var nilai_tagih = (getNum(total) * getNum(persentase)) / 100;
  var nilai_ppn = (getNum(nilai_tagih) * getNum(ppn)) / 100;
  var nilai_invoice = getNum(nilai_tagih) + getNum(nilai_ppn);

  $("#grandtotal").val(number_format(nilai_tagih, 2));
  $("#dpp").val(number_format(nilai_tagih, 2));
  $("#totalppn").val(number_format(nilai_ppn, 2));
  $("#nilai_tagih").val(number_format(nilai_invoice, 2));
}
```

### 7.3 Perubahan di Print Invoice

**File:** `views/PrintInvoice.php`

Tambahkan baris **Discount** di footer tabel, antara Total dan DPP:

```html
<!-- Setelah baris Total -->
<tr>
  <th></th>
  <th></th>
  <th></th>
  <th></th>
  <th align="center">Discount</th>
  <th align="right" width="110"><?= number_format($header->diskon, 2) ?></th>
</tr>
<!-- Lalu baris Sub Total (setelah diskon) -->
<tr>
  <th></th>
  <th></th>
  <th></th>
  <th></th>
  <th align="center">Sub Total</th>
  <th align="right" width="110">
    <?= number_format($totharga - $header->diskon, 2) ?>
  </th>
</tr>
<!-- Baru DPP Nilai Lain dihitung dari Sub Total -->
```

**Catatan:** Baris Discount hanya ditampilkan jika `$header->diskon > 0` agar invoice tanpa diskon tetap clean.

### 7.4 Perubahan di Halaman Index & Monitoring

**File:** `models/Wt_invoicing_model.php` → `get_invoicing()` dan `get_monitoring_invoice()`

Tambahkan pengurangan diskon pada perhitungan `nilai_invoice`:

```php
// Setelah kalkulasi selesai, kurangi diskon dari header
$diskon_header = $item['diskon']; // dari tr_invoice.diskon

// Untuk non-sheet:
$ttl_harga = $get_total_invoice->ttl_harga - $diskon_header;

// Untuk sheet:
$nilai_total = $nilai_total - $diskon_header;
```

### 7.5 Perubahan di E-Faktur (Opsional — Perlu Diskusi)

**Pertanyaan ke BA:**
Apakah diskon manual perlu dilaporkan di e-Faktur? Jika ya, maka:

- Kolom "Total Diskon" di DetailFaktur harus diisi
- DPP dihitung dari (Amount - Diskon), bukan Amount mentah
- Ini berpengaruh ke nilai PPN yang dilaporkan

Jika tidak, maka e-Faktur tetap seperti sekarang (diskon di-net ke harga).

### 7.6 Controller — Tidak Perlu Diubah

`SaveNewInvoice()` sudah mengambil dan menyimpan:

```php
'diskon' => str_replace(',', '', $post['diskon']),
'total'  => str_replace(',', '', $post['total']),
```

---

## 8. Skenario Penggunaan (User Story)

### Skenario 1: Produk Reguler (KG) dengan Diskon Negosiasi

```
Situasi: Customer PT ABC beli coil 5.000 KG @ Rp 15.000/KG
         Negosiasi dapat diskon Rp 5.000.000

Di Form Create Invoice:
  Amount    = 5.000 × 15.000 = Rp 75.000.000
  Discount  = Rp 5.000.000 (user ketik manual)
  Total     = Rp 70.000.000
  PPN       = ...

Di Print Invoice:
  Qty: 5,000.00 Kgs
  Per Unit: 15,000.00
  Amount: 75,000,000.00
  ─────────────────────
  Total:      75,000,000.00
  Discount:    5,000,000.00
  Sub Total:  70,000,000.00
  DPP:        ...
  PPN:        ...
  Grand Total: ...
```

### Skenario 2: Produk Sheet dengan Diskon Per-Lembar

```
Situasi: Customer PT XYZ beli sheet 500 lembar (total berat 2.500 KG)
         Harga per KG = Rp 10.000 (di form create)
         Harga per Sheet = Rp 50.000 (di print invoice)
         Diskon dari sales: Rp 2.000/sheet = 500 × 2.000 = Rp 1.000.000

Di Form Create Invoice:
  Amount (basis KG) = 2.500 × 10.000 = Rp 25.000.000
  Discount = Rp 1.000.000 (user input nominal langsung)
  Total    = Rp 24.000.000

Di Print Invoice:
  Qty: 500.00 Sheets
  Per Unit: 50,000.00
  Amount: 25,000,000.00
  ─────────────────────
  Total:      25,000,000.00
  Discount:    1,000,000.00
  Sub Total:  24,000,000.00
  ...

→ Customer tetap lihat diskon sebagai nominal total.
  Tidak ada konflik KG vs Sheet karena diskon = angka final.
```

### Skenario 3: Tanpa Diskon (Default)

```
Situasi: Invoice tanpa diskon

Di Form Create Invoice:
  Amount   = Rp 50.000.000
  Discount = 0.00 (user tidak ubah, atau hapus isi default dari master)
  Total    = Rp 50.000.000

Di Print Invoice:
  (Baris Discount TIDAK ditampilkan jika = 0)
  Total:       50,000,000.00
  DPP:         ...
  Grand Total: ...
```

---

## 9. Risiko & Mitigasi

| No  | Risiko                                       | Dampak                             | Probabilitas | Mitigasi                                                        |
| --- | -------------------------------------------- | ---------------------------------- | ------------ | --------------------------------------------------------------- |
| 1   | User salah input nominal (typo)              | Nilai invoice salah, piutang salah | Sedang       | Tampilkan confirmation dialog sebelum save                      |
| 2   | Diskon diberikan tanpa approval              | Kerugian perusahaan                | Rendah       | Simpan di `diskon_compare` nilai diskon dari master untuk audit |
| 3   | E-Faktur tidak match dengan diskon           | Lebih/kurang bayar PPN             | Rendah       | Diskusi dengan tim pajak sebelum implementasi                   |
| 4   | Nilai di index/monitoring berbeda dari print | Confusion user finance             | Sedang       | Pastikan semua tampilan konsisten pakai formula yang sama       |
| 5   | Diskon > Amount lolos validasi               | Nilai negatif                      | Rendah       | Double validation: frontend (JS) + backend (controller)         |

---

## 10. Checklist Implementasi

| No  | Task                                                               | File         | Priority |
| --- | ------------------------------------------------------------------ | ------------ | -------- |
| 1   | Hapus `readonly` dari field diskon di form                         | 3 view files | High     |
| 2   | Tambah fungsi JS `hitungDiskon()`                                  | 3 view files | High     |
| 3   | Update fungsi JS `totalBalanced()`                                 | 3 view files | High     |
| 4   | Update fungsi JS `hitungPpn()` agar basis = Total (setelah diskon) | 3 view files | High     |
| 5   | Tambah validasi JS (diskon ≤ Amount, ≥ 0)                          | 3 view files | High     |
| 6   | Tambah validasi backend di `SaveNewInvoice()`                      | Controller   | Medium   |
| 7   | Tambah baris "Discount" di `PrintInvoice.php`                      | View print   | High     |
| 8   | Update kalkulasi di `get_invoicing()`                              | Model        | Medium   |
| 9   | Update kalkulasi di `get_monitoring_invoice()`                     | Model        | Medium   |
| 10  | Simpan diskon master ke `diskon_compare` untuk audit               | Controller   | Low      |
| 11  | Update DPP di e-Faktur (jika disetujui tim pajak)                  | Controller   | Low      |
| 12  | Testing: invoice reguler tanpa diskon                              | -            | High     |
| 13  | Testing: invoice reguler dengan diskon                             | -            | High     |
| 14  | Testing: invoice sheet dengan diskon                               | -            | High     |
| 15  | Testing: print invoice dengan/tanpa diskon                         | -            | High     |
| 16  | Testing: halaman index & monitoring                                | -            | Medium   |

---

## 11. Pertanyaan untuk BA (Butuh Keputusan)

| No  | Pertanyaan                                                      | Konteks                                           | Opsi                                             |
| --- | --------------------------------------------------------------- | ------------------------------------------------- | ------------------------------------------------ |
| 1   | Apakah diskon harus tampil di print invoice?                    | Saat ini tidak ada baris Discount di PDF          | Ya / Tidak                                       |
| 2   | Jika ya, format tampilannya bagaimana?                          | Satu baris "Discount" saja, atau per item?        | Total saja / Per item                            |
| 3   | Apakah DPP untuk e-Faktur dihitung sebelum atau sesudah diskon? | Ini mempengaruhi pelaporan pajak                  | Sebelum diskon / Sesudah diskon                  |
| 4   | Apakah perlu approval jika diskon melebihi threshold tertentu?  | Misal: diskon > 10% perlu approval manager        | Ya (berapa %) / Tidak                            |
| 5   | Nilai default diskon saat form dibuka?                          | Saat ini = nilai dari master `ms_diskon`          | Tetap dari master / Mulai dari 0                 |
| 6   | Apakah halaman index perlu tampilkan kolom diskon?              | Saat ini tidak ada kolom diskon di daftar invoice | Ya / Tidak                                       |
| 7   | Scope view yang diubah?                                         | Ada 3 form create invoice                         | Semua / Hanya `create_invoice_do.php` / Tertentu |

---

## 12. Diagram Alur Setelah Perubahan

```
┌────────────────────────────────────────────────────────────────────────┐
│ FLOW SETELAH PERUBAHAN (DISKON MANUAL)                                  │
├────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  User buka form Create Invoice                                           │
│       │                                                                  │
│       ▼                                                                  │
│  Sistem load data dari DO/SPK                                            │
│  Amount = SUM(Qty × Harga Satuan) per item                              │
│  Diskon = Pre-fill dari SUM(nilai_diskon) yang ada di SPK/DO             │
│       │                                                                  │
│       ▼                                                                  │
│  ┌─────────────────────────────────────┐                                 │
│  │ User BISA edit field Diskon (Rp)    │                                 │
│  │ → Ketik nominal baru               │                                 │
│  │ → Atau biarkan default             │                                 │
│  └─────────────┬───────────────────────┘                                 │
│                │                                                         │
│                ▼                                                         │
│  JavaScript validasi:                                                    │
│    - Diskon ≤ Amount? ✓                                                  │
│    - Diskon ≥ 0? ✓                                                       │
│                │                                                         │
│                ▼                                                         │
│  Auto-recalculate:                                                       │
│    Total = Amount - Diskon                                               │
│    Grand Total = Total × Persentase%                                     │
│    DPP = Grand Total                                                     │
│    PPN = DPP × PPN%                                                      │
│    Total Invoice = Grand Total + PPN                                     │
│                │                                                         │
│                ▼                                                         │
│  User klik "Create Invoice"                                              │
│                │                                                         │
│                ▼                                                         │
│  Backend save:                                                           │
│    tr_invoice.diskon = nominal diskon                                    │
│    tr_invoice.total = amount - diskon                                    │
│    tr_invoice.nilai_invoice = total invoice                              │
│                │                                                         │
│                ▼                                                         │
│  ┌──────────────────────────┐                                            │
│  │ Tampil di halaman Index  │──→ Nilai sudah dikurangi diskon            │
│  │ Print Invoice PDF        │──→ Ada baris "Discount" (jika > 0)        │
│  │ Monitoring Piutang       │──→ Sisa tagihan = nilai setelah diskon    │
│  └──────────────────────────┘                                            │
│                                                                          │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 13. Kesimpulan

### Perubahan ini LAYAK untuk diimplementasikan dengan catatan:

1. **Gunakan Opsi A** (diskon total nominal Rp di footer) — paling efisien dan solve masalah KG vs Sheet.
2. **Wajib update Print Invoice** — tambahkan baris Discount agar customer aware ada potongan.
3. **Wajib update halaman Index & Monitoring** — agar nilai yang tampil konsisten dengan invoice.
4. **Diskusi E-Faktur** dengan tim pajak terlebih dahulu — keputusan ini berdampak compliance.
5. **Simpan diskon master** di `diskon_compare` untuk audit trail.

### Estimasi Effort

| Task                                  | Waktu       |
| ------------------------------------- | ----------- |
| Perubahan view (3 files) + JavaScript | 2-3 jam     |
| Perubahan PrintInvoice                | 1 jam       |
| Perubahan Model (index + monitoring)  | 1-2 jam     |
| Testing (semua skenario)              | 2-3 jam     |
| **Total**                             | **6-9 jam** |

---

_Dokumen ini dibuat sebagai bahan diskusi. Implementasi akan dilakukan setelah mendapat approval dari BA dan stakeholder terkait._
