# PRD - Modul Invoicing (Wt_invoicing)

## 1. Ringkasan Produk

Modul Invoicing adalah fitur inti pada sistem ERP PT Metalsindo yang berfungsi untuk **membuat, mengelola, mencetak, dan memonitor invoice penjualan** kepada customer. Modul ini mencakup pembuatan invoice berdasarkan Delivery Order (DO), pengelolaan proforma invoice, pencetakan dokumen (Invoice & Packing List), monitoring piutang, follow-up penagihan, close invoice, serta integrasi E-Faktur (Coretax) untuk pelaporan pajak.

**Konteks Bisnis:** PT Metalsindo Pacific adalah perusahaan manufaktur metal (aluminium) di Cikarang Industrial Estate. Flow bisnis utama: Penawaran → SPK Marketing → Produksi → Delivery Order → **Invoice** → Penerimaan.

---

## 2. Tujuan & Objektif

| # | Objektif | Ukuran Keberhasilan | Prioritas |
|---|----------|---------------------|-----------|
| 1 | Membuat invoice penjualan berdasarkan DO | Invoice terbit dengan nomor unik INV-MP/YY/BLN/NNNN | High |
| 2 | Mendukung 2 tipe: reguler (sales) dan slitting (jasa) | Kalkulasi harga berbeda (per-kg vs per-sheet) | High |
| 3 | Mendukung proforma invoice sebelum invoice final | Proforma bisa dikonversi ke invoice resmi | Medium |
| 4 | Cetak Invoice & Packing List dalam format PDF | Dokumen terformat rapi sesuai standar perusahaan | High |
| 5 | Monitoring piutang real-time | Umur piutang, janji bayar, history follow-up | High |
| 6 | Follow-up penagihan | Tracking tanggal terima, follow-up, janji bayar | High |
| 7 | Export E-Faktur untuk Coretax | Generate Excel sesuai format DJP | High |
| 8 | Close invoice yang sudah selesai | Pisahkan invoice aktif dan closed | Medium |
| 9 | Jurnal piutang untuk accounting | List invoice dengan status_jurnal = OPN | Medium |
| 10 | Update otomatis ke SPK Marketing & DO | percent_invoice, total_invoice, status_invoice | High |

---

## 3. Target User & Persona

### 3.1 Staff Finance/Accounting (Primary)
- **Tanggung Jawab:** Membuat invoice, cetak, kirim ke customer
- **Permission:** Invoicing.View, Invoicing.Add

### 3.2 Manager Finance (Approver/Monitor)
- **Tanggung Jawab:** Monitoring piutang, follow-up, close invoice, E-Faktur
- **Permission:** Invoicing.View, Invoicing.Add, Invoicing.Manage

### 3.3 Marketing Staff (Read-only monitoring)
- **Permission:** Invoicing.View

---

## 4. Fitur Utama

### 4.1 List Invoice (Index)
- **URL:** `/wt_invoicing`
- **View:** `index_invoice.php`
- DataTable server-side dengan kolom: No Invoice, Customer, Term, No DO, Nilai Invoice, Tgl Invoice, Action
- Action buttons: Print Invoice, Print Proforma, Print Packing List, Print Packing List Slitting

### 4.2 Create Invoice dari Delivery Order
- **URL:** `/wt_invoicing/createInvoiceDo/{id_do}` (reguler) atau `/wt_invoicing/createInvoiceDoSlitting/{id_do}` (slitting)
- **View:** `create_invoice_do.php`, `create_invoice_do_slitting.php`
- **Syarat:** Untuk slitting, scrap harus sudah dikonfirmasi di Control DO
- **Form Fields:**
  - No Invoice (auto-generate)
  - Tgl Invoice
  - Customer (dari DO)
  - No DO, Tgl DO (readonly)
  - No PO Customer, Tgl PO
  - Terms (payment_term dari customer)
  - Detail produk: Quantity, Units, Width, Product, Original Size, Tobe Size, Per Unit, Amount
  - Summary: Amount, Discount, Total, Persentase Invoice %, DPP, PPN, Total Invoice

### 4.3 Kalkulasi Harga (Sheet vs Coil)
- **Sheet** (`id_bentuk = 'B2000002'`): qty dari `stock_material.qty_sheet`, satuan = Sheets/Kgs
- **Coil/Non-sheet**: qty dari `tr_invoice_detail.qty_invoice`, satuan = Kgs
- **PPN:** DPP Nilai Lain = ceil(11/12 * total), PPN = DPP * 12%, Grand Total = total + PPN

### 4.4 Proforma Invoice
- **URL:** `/wt_invoicing/createProformaInvoice/{id_spk}` atau `/wt_invoicing/index_proforma`
- Create proforma → Later convert ke invoice via `createDealInvoice`
- Nomor format: `PROF-INV-MP/YY/BLN/NNNN`
- Tidak mengupdate SPK Marketing dan DO

### 4.5 Monitoring Invoice
- **URL:** `/wt_invoicing/index_monitoring`
- **View:** `index_monitoring.php`
- DataTable server-side dengan filter tanggal (awal-akhir)
- Kolom: No Invoice, Customer, Marketing, Top, Payment, Nilai Invoice, Total Bayar, Tgl Invoice, Janji Bayar, Umur Piutang, Action
- Export ke Excel
- Action: History Follow-up, Follow-up baru, Close Invoice

### 4.6 Follow-Up Penagihan
- **URL:** `/wt_invoicing/FollowUp/{no_invoice}`
- **View:** `createFollowup.php`
- Input: Received (penerima), Tgl Terima, Tgl Follow-up, Tgl Janji Bayar, Keterangan, Upload Tanda Terima
- Non-aktifkan follow-up lama, simpan yang baru di `tr_followup`
- Update `tr_invoice` (tgl_terima, tgl_followup, tgl_janji_bayar)

### 4.7 Close Invoice
- **URL:** POST `/wt_invoicing/closeInvoice`
- Set `status_close = '1'`
- Invoice dipindah dari monitoring ke list closed

### 4.8 Print Invoice (PDF)
- **URL:** `/wt_invoicing/PrintInvoice/{no_invoice}`
- Output PDF format Letter via HTML2PDF
- Layout: Header (logo, company info), Invoice details (Sold To, Delivered To, Invoice No, Date, DO No, PO No, Terms), Detail tabel, Summary (Total, DPP Nilai Lain, PPN, Grand Total), Bank info, Signature
- Saat cetak pertama: set `status = 1`, `printed_on`, `printed_by`

### 4.9 Print Packing List (PDF)
- **URL:** `/wt_invoicing/PrintPackinglist/{no_invoice}` atau `/wt_invoicing/PrintPackinglistSlitting/{no_invoice}`

### 4.10 E-Faktur (Coretax Integration)
- **URL:** `/wt_invoicing/e_faktur`
- **View:** `index_efaktur.php`
- Filter: hanya invoice dengan `stat_efaktur = 0` dan NPWP valid
- Checkbox select (individual atau check-all)
- Generate: batch processing → export Excel (2 sheet: Faktur + DetailFaktur)
- Format Excel sesuai template import Coretax DJP
- Log export di `faktur_e_logs`
- Update `stat_efaktur = 1` setelah generate
- List E-Faktur history: `/wt_invoicing/e_faktur_list`

### 4.11 Jurnal Piutang
- **URL:** `/wt_invoicing/jurnal_invoicing`
- List invoice dengan `status_jurnal = 'OPN'`
- Untuk approval jurnal piutang ke GL

### 4.12 SPK Marketing (Source for Invoice)
- **URL:** `/wt_invoicing/spk_marketing`
- List SPK Marketing yang bisa di-invoice langsung

---

## 5. Aturan Bisnis

### 5.1 Penomoran Invoice
- Format: `INV-MP/{YY}/{ROMAWI}/{NNNN}`
- Contoh: `INV-MP/24/II/0001` = Invoice pertama bulan Februari 2024
- Sequence reset per tahun
- Bulan diambil dari tanggal DO (bukan tanggal input)

### 5.2 Penomoran Proforma
- Format: `PROF-INV-MP/{YY}/{ROMAWI}/{NNNN}`

### 5.3 Kode Internal Invoice
- `no_invoice`: internal code `I{YY}{NNNNN}` (e.g., I2400001)
- `no_surat`: nomor cetak yang ditampilkan ke customer

### 5.4 Kalkulasi PPN (DPP Nilai Lain - sesuai regulasi 2024)
```
DPP_Nilai_Lain = ceil(11/12 * Total_Harga)
PPN = DPP_Nilai_Lain * 12/100
Grand_Total = Total_Harga + PPN
```

### 5.5 Slitting Invoice Rules
- Sebelum create invoice slitting: cek `dt_delivery_order_child_scrap` → semua `qty_in > 0`
- Jika ada scrap belum confirm → redirect dengan error message
- Tipe disimpan sebagai `type = 'slitting'` di `tr_invoice`
- Print dengan judul "Slitting Service" bukan "Sales"
- Satuan: `UM.0033` (jasa)
- Kode barang Coretax: `290000`

### 5.6 Update Modul Terkait saat Save Invoice
1. `tr_spk_marketing.percent_invoice += persentase`
2. `tr_spk_marketing.total_invoice += dpp`
3. `tr_delivery_order.status_invoice = 'CLS'`
4. `tr_delivery_order.nilai_invoice = dpp`

---

## 6. Permissions

| Key | Deskripsi | Fitur |
|-----|-----------|-------|
| Invoicing.View | Lihat data | List, Monitoring, History, SPK List |
| Invoicing.Add | Buat invoice | Create, Save, Follow-up |
| Invoicing.Manage | Kelola | Print, Close, E-Faktur, Jurnal |
| Invoicing.Delete | Hapus | (belum fully implemented) |

---

## 7. Integrasi Modul

| Modul | Relasi | Detail |
|-------|--------|--------|
| Delivery Order | Source | Sumber utama pembuatan invoice |
| SPK Marketing | Source + Update | Sumber data & update percent_invoice |
| Penerimaan | Downstream | Invoice → pembayaran |
| Customer Master | Reference | Data customer, alamat, NPWP |
| Stock Material | Reference | Qty sheet untuk produk sheet |
| Penawaran | Legacy | Referensi harga (quotation) |

---

## 8. Non-Functional Requirements

| Aspek | Detail |
|-------|--------|
| Performa | DataTable server-side, lazy loading |
| Keamanan | Permission-based, CSRF via CI |
| Audit Trail | created_by/on, modified_by/on, printed_by/on |
| Multi-currency | Kurs field (default IDR) |
| Tax Compliance | E-Faktur Coretax format, NPWP validation |
| Output | PDF (HTML2PDF), Excel (PHPExcel) |
| File Upload | PO & SO documents |
