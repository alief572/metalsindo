# DESIGN - Modul SPK Marketing

## 1. Arsitektur

Framework: CodeIgniter 3 (HMVC dengan Modules)

```
application/modules/spk_marketing/
├── controllers/
│   ├── Spk_marketing.php      — Controller utama
│   ├── Penawaran.php           — Controller penawaran
│   └── Pricelist.php           — Controller pricelist
├── models/
│   └── Inventory_4_model.php   — Model utama
└── views/
    ├── AddHeader.php           — Form tambah SPK (Coil/Reguler)
    ├── addHeader_Sheet.php     — Form tambah SPK (Sheet)
    ├── AddHeaderSlitting.php   — Form tambah SPK (Slitting)
    ├── EditHeader.php          — Form edit SPK
    ├── RevisiHeader.php        — Form revisi SPK
    ├── ViewHeader.php          — View detail SPK
    ├── PrintHeader.php         — Print SPK (PDF)
    └── index.php               — List SPK
```

## 2. Endpoint Terkait SPK Header

| Method | URL                                    | Fungsi                     |
| ------ | -------------------------------------- | -------------------------- |
| GET    | `/spk_marketing/addHeader`             | Form tambah SPK reguler    |
| GET    | `/spk_marketing/addHeader_Sheet`       | Form tambah SPK sheet      |
| GET    | `/spk_marketing/addHeaderSlitting`     | Form tambah SPK slitting   |
| POST   | `/spk_marketing/SaveNewHeader`         | Simpan SPK baru (reguler)  |
| POST   | `/spk_marketing/SaveNewHeaderSlitting` | Simpan SPK baru (slitting) |
| GET    | `/spk_marketing/EditHeader/{id}`       | Form edit SPK              |
| POST   | `/spk_marketing/SaveEditHeader`        | Simpan edit SPK            |
| GET    | `/spk_marketing/revisiHeader/{id}`     | Form revisi SPK            |
| POST   | `/spk_marketing/SaveRevisiHeader`      | Simpan revisi SPK          |
| GET    | `/spk_marketing/ViewHeader/{id}`       | View detail SPK            |
| GET    | `/spk_marketing/PrintHeader/{id}`      | Print SPK PDF              |

## 3. Endpoint AJAX

| Method | URL                            | Fungsi                                     |
| ------ | ------------------------------ | ------------------------------------------ |
| GET    | `/spk_marketing/GetPenawaran`  | Ambil detail item penawaran                |
| GET    | `/spk_marketing/GetCustomer`   | Ambil info customer dari penawaran         |
| GET    | `/spk_marketing/GetTipe`       | Ambil tipe dari penawaran                  |
| GET    | `/spk_marketing/totalhg`       | Hitung total harga per item (hgdeal × qty) |
| GET    | `/spk_marketing/totalw`        | Hitung total weight                        |
| POST   | `/spk_marketing/get_penawaran` | Ambil daftar penawaran per customer        |

## 4. Sequence Diagram - Save SPK Baru

```
User → [Form AddHeader] → Submit
    → POST /spk_marketing/SaveNewHeader
    → Generate ID (generate_code)
    → Generate No Surat (BuatNomor)
    → BEGIN TRANSACTION
    → INSERT tr_spk_marketing (header)
    → LOOP: INSERT dt_spkmarketing (detail per item)
    → LOOP: INSERT dt_spkmarketing_loading (copy detail)
    → COMMIT / ROLLBACK
    → Return JSON status
```

## 5. Sequence Diagram - Kalkulasi Total Harga (Existing)

```
User input Harga Deal / Qty
    → onkeyup AksiDetail(id)
    → AJAX GET /spk_marketing/totalhg?hgdeal=X&qty=Y&id=Z
    → Server: total = qty × hgdeal
    → Response: hidden input (raw value) + formatted display
```

## 6. Desain Fitur Diskon (BARU)

### 6.1 Perubahan Database

**Tabel `dt_spkmarketing`:**

- Tambah kolom: `nominal_discount DECIMAL(20,2) DEFAULT 0.00`

**Tabel `tr_spk_marketing`:**

- Tambah kolom: `total_discount DECIMAL(20,2) DEFAULT 0.00`

### 6.2 Perubahan View (AddHeader.php)

**Kolom tabel baru:** Tambah kolom "Disc" setelah "Harga Deal / Kg"

- Input type number untuk nominal diskon per item

**Footer tabel baru:**

```
| Total Harga  | [auto-sum dari semua total_harga item]  |
| Discount     | [input manual ATAU auto-sum dari disc per item] |
| Grand Total  | [Total Harga - Discount]                |
```

### 6.3 JavaScript Logic

```
Saat user input diskon per item:
  → Hitung sum semua diskon per item
  → Set footer Discount = sum
  → Hitung Grand Total = Total Harga - Discount
  → Mark mode = "per_item"

Saat user input diskon di footer:
  → Reset semua diskon per item ke 0
  → Hitung Grand Total = Total Harga - Discount footer
  → Mark mode = "keseluruhan"

Validasi sebelum save:
  → Jika total discount > total harga → tampilkan warning, block save
```

### 6.4 Perubahan Controller (SaveNewHeader)

- Ambil `total_discount` dari POST data
- Simpan ke `tr_spk_marketing.total_discount`
- Per item: ambil `nominal_discount` dari array detail
- Simpan ke `dt_spkmarketing.nominal_discount`

### 6.5 Perubahan GetPenawaran (Render Row)

- Tambah kolom input `dp_discount_{loop}` setelah harga deal
- Onkeyup trigger recalculate discount

## 7. File yang Perlu Dimodifikasi

1. **Database** — ALTER TABLE (2 tabel)
2. **AddHeader.php** — Tambah kolom Disc + footer
3. **Spk_marketing.php** (Controller) — Update `SaveNewHeader`, `GetPenawaran`
4. **EditHeader.php** — Tambah kolom Disc + footer (untuk konsistensi)
5. **ViewHeader.php** — Tambah kolom Disc + footer (readonly)
