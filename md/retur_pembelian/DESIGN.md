# DESIGN — Modul Retur Pembelian

## 1. Arsitektur

Framework: **CodeIgniter 3 (HMVC / Modular Extensions)**.

```
application/modules/retur_pembelian/
├── controllers/Retur_pembelian.php     # Controller utama (extends Admin_Controller)
├── models/Retur_pembelian_model.php     # Model (extends BF_Model)
└── views/
    ├── index.php        # List retur (DataTables)
    ├── add_retur.php    # Form tambah retur
    ├── edit_retur.php   # Form edit retur
    └── view_retur.php   # Detail retur (read-only)
```

## 2. Endpoint (Controller Methods)

| Method | Tipe | Deskripsi |
|---|---|---|
| `index()` | Page | Halaman list retur |
| `get_datatable_retur()` | AJAX/JSON | Data server-side DataTables |
| `add()` | Page | Form tambah retur |
| `getPO()` | AJAX/JSON | Daftar No. PO per supplier (alur lama) |
| `getReceiveInvoiceAP()` | AJAX/JSON | Daftar Receive Invoice AP per supplier |
| `getDetailReceiveInvoiceAP()` | AJAX/HTML | Render tabel detail dari Receive Invoice AP |
| `getDetailPO()` | AJAX/HTML | Render tabel detail dari PO (alur lama) |
| `save_retur_pembelian()` | POST/JSON | Simpan retur baru |
| `edit_retur($id)` | Page | Form edit retur |
| `update_retur_pembelian()` | POST/JSON | Update retur |
| `view_retur($id)` | Page | Detail retur |
| `del_retur()` | POST/JSON | Soft delete retur |

## 3. Alur Data Detail Material

### 3.1 Alur Utama — via Receive Invoice AP

```
tr_receive_invoice_ap_detail (riad)
   └─ join tr_incoming (ti)         ON ti.id_incoming = riad.id_incoming
       └─ join dt_incoming (di)     ON di.id_incoming = ti.id_incoming
           └─ join dt_trans_po (dtp) ON dtp.id_dt_po = di.id_dt_po
               ├─ join ms_inventory_category3 (mic3) ON mic3.id_category3 = dtp.idmaterial
               └─ join tr_purchase_order (tpo)       ON tpo.no_po = dtp.no_po
```

Field kunci yang ditarik: `hargasatuan` (per kg), `qty_sheet`, `width_recive`,
`mic3.id_bentuk`, **`mic3.total_weight`** (berat/sheet), `tpo.matauang`.

### 3.2 Alur Lama — via Purchase Order (backward-compat)

```
tr_purchase_order → dt_trans_po (get_po_detail)
   + lookup ms_inventory_category3 (id_bentuk, total_weight)
   + lookup dt_incoming (lotno, qty_sheet)
```

## 4. Logika Perhitungan Harga (inti perbaikan)

Pseudocode yang diterapkan di `getDetailReceiveInvoiceAP()`, `getDetailPO()`,
`save_retur_pembelian()`, dan `update_retur_pembelian()`:

```php
$is_sheet = ($id_bentuk == 'B2000002');

if ($is_sheet) {
    $harga_satuan   = (float)$hargasatuan * (float)$total_weight; // harga per SHEET
    $qty_retur      = $qty_sheet_retur;
    $total_item     = $qty_retur * $harga_satuan;
} else {
    $harga_satuan   = (float)$hargasatuan;                        // harga per KG
    $qty_retur      = $jumlah_retur;
    $total_item     = $qty_retur * $harga_satuan;
}
```

Nilai `harga_satuan` (per sheet untuk sheet, per kg untuk non-sheet) inilah yang:
- Ditampilkan di kolom **Harga Satuan** pada form (readonly).
- Dikirim via field `dt_{no_po}[n][harga]`.
- Disimpan ke `dt_retur_pembelian.harga_satuan`.

JS `hitungFooter()` menghitung `total = qty_retur × harga` — konsisten karena
`harga` sudah per satuan retur (sheet/kg), **tanpa konversi ganda**.

### 4.1 Logika Retur Parsial & Sisa Kuantitas

1. **Kalkulasi Sisa Kuantitas Tersedia**:
   Di `Retur_pembelian_model::get_detail_by_receive_invoice_ap($id_rec_inv_ap, $exclude_no_surat)`, dilakukan subquery join ke `dt_retur_pembelian` + `tr_retur_pembelian` (`deleted_by IS NULL`):
   ```sql
   LEFT JOIN (
       SELECT dt.id_detail_po, dt.lotno,
              SUM(dt.jumlah_retur) AS qty_already_retur_kg,
              SUM(dt.qty_sheet_retur) AS qty_already_retur_sheet
       FROM dt_retur_pembelian dt
       JOIN tr_retur_pembelian tr ON tr.no_surat = dt.id_header
       WHERE tr.deleted_by IS NULL [AND tr.no_surat != $exclude_no_surat]
       GROUP BY dt.id_detail_po, dt.lotno
   ) prev_retur ON prev_retur.id_detail_po = di.id_dt_po AND prev_retur.lotno = di.lotno
   ```
   Sisa kuantitas:
   ```php
   $sisa_kg = max(0, (float)$item->qty_receive - (float)$item->qty_already_retur_kg);
   $sisa_sheet = max(0, (int)$item->qty_sheet - (int)$item->qty_already_retur_sheet);
   ```

2. **Frontend UI/UX (Checkbox & Filter)**:
   - Kolom Checkbox di header (`#check_all_detail`) dan di setiap baris (`.check_item`).
   - Ketika user mengetik kuantitas retur $> 0$, baris checkbox otomatis tercentang (`checked`).
   - Ketika kuantitas diisi 0, checkbox otomatis uncheck.
   - Input kuantitas divalidasi tidak boleh melebihi `data-max_retur` (sisa qty). Jika melebihi, dimunculkan Swal warning dan nilai di-clamp ke batas maksimum.
   - `hitungFooter()` hanya menjumlahkan baris yang dicentang (`isChecked`).
   - Sebelum submit, JS memvalidasi bahwa minimal ada 1 item yang dicentang dengan kuantitas $> 0$.

3. **Backend Filtering & Validation**:
   - Di `save_retur_pembelian()` & `update_retur_pembelian()`:
     ```php
     if (empty($check) || (int)$check === 0 || $qty <= 0) {
         continue; // Gracefully skip unreturned / unchecked item
     }
     ```
   - Validasi sisa kuantitas di server: `if ($qty > $sisa) throw Exception`.
   - Menghasilkan error jika tidak ada satupun item yang dipilih untuk diretur.
   - Mengumpulkan nomor PO unik hanya dari item yang dipilih untuk disimpan di `tr_retur_pembelian.no_po`.

## 5. State Machine (Status DN)

```
        create retur
   ─────────────────────►  ┌──────────────┐
                           │  Waiting DN  │
                           └──────┬───────┘
                                  │ ada baris tr_dn_retur_pmb.id_retur
                                  ▼
                           ┌──────────────┐
                           │  DN Created  │  (edit & delete dinonaktifkan)
                           └──────────────┘
```

## 6. Penyimpanan

- Header → `tr_retur_pembelian` (insert saat save, update saat update).
- Detail → `dt_retur_pembelian` (insert_batch hanya untuk item yang dicentang/diretur). Pada update, detail lama
  dihapus (`delete by id_header`) lalu di-insert ulang.
- Kolom self-healing: `check_table_columns()` di model menambah kolom yang
  belum ada (`id_rec_inv_ap`, `subtotal`, `ppn_persen`, `nilai_ppn`,
  `grand_total`, `matauang`, `lotno`, `qty_sheet`, `qty_sheet_retur`).

## 7. Sequence — Add Retur (via Receive Invoice AP)

```
User → add() → pilih supplier
   → AJAX getReceiveInvoiceAP(supplier) → list invoice AP
User → pilih invoice AP
   → AJAX getDetailReceiveInvoiceAP(id_rec_inv_ap)
        → model get_detail_by_receive_invoice_ap()  [+ total_weight + sisa_qty]
        → controller hitung harga (sheet: ×total_weight) & render checkbox + sisa
        → render tabel detail
User → centang item yang ingin diretur & input qty retur (≤ sisa), input ppn → submit
   → JS validasi minimal 1 item terpilih dengan qty > 0
   → save_retur_pembelian()
        → loop item: skip yang uncheck / qty <= 0
        → validasi qty <= sisa kuantitas aktif
        → hitung grand_total per item terpilih
        → insert header (no_po dari item terpilih) + insert_batch detail terpilih
```

## 8. Riwayat Perubahan

- **2026-09**:
  - **Perbaikan harga per sheet**: Sebelumnya `hargasatuan` (per kg) ditampilkan langsung sebagai harga /Sheet. Kini harga sheet = `hargasatuan × total_weight`.
  - **Retur Pembelian Parsial (Partial Return)**:
    - User dapat meretur sebagian item dari invoice/PO tanpa terkena validasi blocking.
    - Checkbox per baris & master checkbox untuk seleksi item.
    - Perhitungan sisa kuantitas aktif secara real-time (`qty_receive - sum(active_returns)`).
    - Otomatis clamp/warning jika input melebihi sisa.
    - Detail yang disimpan ke database hanya item yang diretur, dan header `no_po` mencatat PO item bersangkutan.
    - Halaman edit memuat seluruh item invoice dengan item terdaftar tetap dicentang untuk fleksibilitas modifikasi.
