# PRD — Modul Retur Pembelian

## 1. Ringkasan

Modul **Retur Pembelian** digunakan untuk mencatat pengembalian (retur) barang/material
kepada supplier atas material yang sudah diterima (incoming) namun ditemukan
Not-Good (NG) / tidak sesuai. Retur dibuat berdasarkan **Receive Invoice AP**
(alur utama) atau berdasarkan **Purchase Order** (alur lama / backward-compat).

Dokumen retur menjadi dasar penerbitan **Debit Note (DN)** ke supplier.

## 2. Stakeholder

| Peran | Kepentingan |
|---|---|
| Bagian Purchasing / Finance AP | Membuat & mengelola retur pembelian |
| Supplier | Penerima Debit Note atas barang yang diretur |
| QC / Gudang | Sumber informasi NG report & material yang diretur |

## 3. Fitur Utama

1. **List Retur** — daftar seluruh retur pembelian (DataTables server-side), lengkap
   dengan status DN (Waiting DN / DN Created).
2. **Add Retur** — membuat retur baru:
   - Pilih Supplier.
   - Pilih **Receive Invoice AP** (alur utama) → sistem menarik detail material dari
     rantai `Receive Invoice AP → Incoming → Detail PO`.
   - (Alur lama) Pilih **No. PO** → sistem menarik detail dari PO.
   - **Retur Parsial (Partial Return)**:
     - Terdapat checkbox per baris dan checkbox master "Pilih Semua".
     - Pengguna dapat meretur **sebagian item** saja (misal hanya 2 dari 10 material).
     - Pengguna dapat meretur **sebagian kuantitas** (partial quantity) dari kuantitas yang diterima.
     - Item yang tidak dicentang atau qty retur = 0 dilewati secara otomatis tanpa menyebabkan error validasi.
     - Minimal 1 item harus dicentang dengan kuantitas > 0.
   - Input qty retur per item, harga satuan (readonly), PPN %.
   - Menampilkan informasi **Sisa Kuantitas** yang tersedia (setelah dikurangi retur aktif sebelumnya).
   - Upload dokumen NCR / Berita Acara (multi-file).
3. **Edit Retur** — mengubah retur selama belum ada DN:
   - Menampilkan seluruh item dari invoice referensi dengan item yang sebelumnya tersimpan tetap tercentang.
   - Pengguna dapat menambah item retur baru dari invoice yang sama atau membatalkan item retur.
4. **View Retur** — melihat detail material yang secara nyata diretur pada transaksi tersebut.
5. **Delete Retur** — soft delete (selama belum ada DN).

## 4. Aturan Bisnis

### 4.1 Jenis Barang (Bentuk)

Material dibedakan berdasarkan `ms_inventory_category3.id_bentuk`:

- **Sheet**: `id_bentuk = 'B2000002'` → satuan **Sheet** (lembar).
- **Non-sheet (Coil/lainnya)**: satuan **KGS** (kilogram).

### 4.2 Perhitungan Harga

Harga dasar material tersimpan di `dt_trans_po.hargasatuan` dalam satuan **per KG**.

| Jenis | Harga Satuan yang Ditampilkan/Disimpan | Total Harga per Item |
|---|---|---|
| Non-sheet (KGS) | `hargasatuan` (per kg) | `jumlah_retur (kg) × hargasatuan` |
| **Sheet** | `harga_sheet = hargasatuan × total_weight` | `qty_sheet_retur × harga_sheet` |

`total_weight` = berat per lembar (kg/sheet), diambil dari
`ms_inventory_category3.total_weight`. Aturan ini konsisten dengan modul
Purchase Order (`purchase_order/views/print2.php`) dan Receive Invoice AP.

> **Catatan penting:** Untuk item **Sheet**, nilai yang disimpan di
> `dt_retur_pembelian.harga_satuan` adalah **harga per sheet** (sudah dikali
> `total_weight`), bukan harga per kg.

### 4.3 PPN & Grand Total (level header)

```
subtotal    = Σ (grand_total per item terpilih)
nilai_ppn   = subtotal × ppn_persen / 100        (default ppn_persen = 11)
grand_total = subtotal + nilai_ppn
```

### 4.4 Validasi Qty Retur & Retur Parsial

1. **Retur Parsial Item**:
   - Baris item yang checkbox-nya tidak dicentang atau nilai retur = 0 tidak akan disimpan ke database `dt_retur_pembelian` dan tidak memicu pesan error.
   - Wajib ada **minimal 1 item** yang dicentang dengan kuantitas retur > 0 saat submit.
2. **Kuantitas Maksimum (Sisa Kuantitas Tersedia)**:
   - Sisa kuantitas dihitung dinamis dari kuantitas terima dikurangi seluruh retur aktif (`deleted_by IS NULL`) yang sudah pernah dibuat untuk item tersebut:
     - `sisa_qty = qty_receive - sum(active_previous_returns)`
   - Sheet: `qty_sheet_retur > 0` dan `≤ sisa_sheet`.
   - Non-sheet: `jumlah_retur > 0` dan `≤ sisa_kg`.
   - Jika sisa kuantitas sudah 0 (habis diretur sebelumnya), input dinonaktifkan (`disabled`) dan diberi label badge "Habis Diretur".
   - Jika user menginput nilai melebihi sisa yang tersedia, sistem memberikan peringatan modal dan secara otomatis mengembalikan nilai ke batas sisa maksimum.
3. **Penyimpanan Header PO**:
   - Kolom `tr_retur_pembelian.no_po` hanya diisi oleh daftar nomor PO yang item-itemnya terpilih/diretur pada transaksi tersebut (tidak memasukkan nomor PO dari item yang tidak diretur).

### 4.5 Status / Debit Note

- Retur baru berstatus **Waiting DN**.
- Setelah ada baris di `tr_dn_retur_pmb` untuk retur tersebut → **DN Created**.
- Retur yang sudah punya DN tidak bisa di-edit / dihapus.

## 5. Penomoran

Format nomor retur: `RTR-PMB/{YY}/{ROMAWI_BULAN}/{URUT4DIGIT}`
Contoh: `RTR-PMB/26/IX/0001`.

## 6. Data Historis

Retur yang dibuat sebelum penerapan aturan harga sheet (§4.2) menyimpan harga sheet
secara historis (apa adanya saat itu). Data lama **tidak dimigrasi** dan ditampilkan
sesuai nilai tersimpan.
