# Design Document - Modul Invoicing (Wt_invoicing)

## 1. Technology Stack

| Layer | Technology |
|-------|-----------|
| Framework | CodeIgniter 3 (HMVC) |
| Database | MySQL 5.7 |
| Frontend | jQuery, DataTables 2.x, Select2, SweetAlert |
| PDF | HTML2PDF |
| Excel | PHPExcel |
| Template | AdminLTE (Bootstrap 3) |

## 2. Module Structure

```
application/modules/wt_invoicing/
├── controllers/
│   └── Wt_invoicing.php          # 3484 lines - Main controller
├── models/
│   └── Wt_invoicing_model.php    # 1022 lines - Data access
└── views/ (43 files)
    ├── index_invoice.php          # List invoice (DataTable server-side)
    ├── index_monitoring.php       # Monitoring piutang
    ├── index_efaktur.php          # E-Faktur selection
    ├── index_efaktur_list.php     # E-Faktur export history
    ├── create_invoice_do.php      # Form create dari DO (reguler)
    ├── create_invoice_do_slitting.php  # Form create (slitting)
    ├── create_invoice_proforma.php     # Proforma invoice
    ├── deal_invoice.php           # Convert proforma → invoice
    ├── PrintInvoice.php           # Template PDF invoice
    ├── PrintPackinglist.php       # Template PDF packing list
    ├── PrintPackinglistSlitting.php
    ├── PrintProformaInvoice.php
    ├── createFollowup.php         # Form follow-up penagihan
    ├── historyfu.php              # History follow-up
    ├── export_excel_monitoring.php # Excel export monitoring
    └── ... (30+ other views)
```

## 3. Flow Diagram

### 3.1 Main Invoice Creation Flow

```
┌──────────────┐     ┌──────────────────┐     ┌─────────────────┐
│ SPK Marketing│────▶│  Delivery Order  │────▶│  CREATE INVOICE │
│              │     │  (DO selesai)    │     │  (dari DO)      │
└──────────────┘     └──────────────────┘     └────────┬────────┘
                                                       │
                            ┌───────────────────────────┼────────────────┐
                            │                           │                │
                            ▼                           ▼                ▼
                   ┌─────────────────┐     ┌────────────────┐  ┌──────────────┐
                   │ INSERT tr_invoice│     │INSERT tr_invoice│  │UPDATE SPK    │
                   │ (header)        │     │_detail (items) │  │UPDATE DO     │
                   └────────┬────────┘     └────────────────┘  └──────────────┘
                            │
              ┌─────────────┼─────────────┐
              ▼             ▼             ▼
     ┌──────────────┐ ┌──────────┐ ┌──────────────┐
     │ PRINT INVOICE│ │MONITORING│ │  E-FAKTUR    │
     │ (PDF)        │ │& FOLLOW-UP│ │  (Coretax)   │
     └──────────────┘ └──────────┘ └──────────────┘
              │             │
              ▼             ▼
     ┌──────────────┐ ┌──────────────┐
     │ PENERIMAAN   │ │CLOSE INVOICE │
     │ (pembayaran) │ │              │
     └──────────────┘ └──────────────┘
```

### 3.2 E-Faktur Flow

```
[User selects invoices] → [Generate] → [Insert faktur_e_logs]
                                      → [Update stat_efaktur = 1]
                                      → [Session store data]
                                      → [Export Coretax Excel]
                                           ├── Sheet: Faktur (header per invoice)
                                           └── Sheet: DetailFaktur (items per invoice)
```

## 4. API Endpoints

### 4.1 Page Routes (GET)

| Method | URL | View | Deskripsi |
|--------|-----|------|-----------|
| `index()` | `/wt_invoicing` | index_invoice | List semua invoice |
| `index_monitoring()` | `/wt_invoicing/index_monitoring` | index_monitoring | Monitoring piutang |
| `index_close()` | `/wt_invoicing/index_close` | index_close | Invoice closed |
| `delivery_order()` | `/wt_invoicing/delivery_order` | index_delivery_order | DO siap invoice |
| `spk_marketing()` | `/wt_invoicing/spk_marketing` | index_spk_marketing | SPK siap invoice |
| `index_proforma()` | `/wt_invoicing/index_proforma` | index_proforma_invoice | List proforma |
| `createInvoiceDo($id)` | `/wt_invoicing/createInvoiceDo/{id}` | create_invoice_do | Form invoice dari DO |
| `createInvoiceDoSlitting($id)` | `/wt_invoicing/createInvoiceDoSlitting/{id}` | create_invoice_do_slitting | Form invoice slitting |
| `createInvoice($id)` | `/wt_invoicing/createInvoice/{id}` | create_invoice | Form invoice dari SPK |
| `createProformaInvoice($id)` | `/wt_invoicing/createProformaInvoice/{id}` | create_invoice_proforma | Form proforma |
| `createDealInvoice($id)` | `/wt_invoicing/createDealInvoice/{id}` | deal_invoice | Convert proforma |
| `FollowUp($id)` | `/wt_invoicing/FollowUp/{id}` | createFollowup | Form follow-up |
| `jurnal_invoicing()` | `/wt_invoicing/jurnal_invoicing` | index_jurnal_piutang | Jurnal piutang |
| `e_faktur()` | `/wt_invoicing/e_faktur` | index_efaktur | E-Faktur |
| `e_faktur_list()` | `/wt_invoicing/e_faktur_list` | index_efaktur_list | List export history |

### 4.2 AJAX Endpoints (POST → JSON)

| Method | URL | Deskripsi |
|--------|-----|-----------|
| `get_invoicing()` | `/wt_invoicing/get_invoicing` | DataTable list invoice |
| `get_monitoring_invoice()` | `/wt_invoicing/get_monitoring_invoice` | DataTable monitoring |
| `get_data_spk_marketing()` | `/wt_invoicing/get_data_spk_marketing` | DataTable SPK |
| `get_efaktur()` | `/wt_invoicing/get_efaktur` | DataTable E-Faktur |
| `get_all_efaktur_id()` | `/wt_invoicing/get_all_efaktur_id` | All selectable IDs |
| `list_efaktur()` | `/wt_invoicing/list_efaktur` | DataTable export logs |
| `SaveNewInvoice()` | `/wt_invoicing/SaveNewInvoice` | Save invoice baru |
| `SaveNewProformaInvoice()` | `/wt_invoicing/SaveNewProformaInvoice` | Save proforma |
| `SaveNewDealInvoice()` | `/wt_invoicing/SaveNewDealInvoice` | Convert proforma→invoice |
| `saveFollowUp()` | `/wt_invoicing/saveFollowUp` | Save follow-up |
| `closeInvoice()` | `/wt_invoicing/closeInvoice` | Close invoice |
| `generate_efaktur()` | `/wt_invoicing/generate_efaktur` | Generate + log E-Faktur |

### 4.3 Print/Export (GET → PDF/Excel)

| Method | URL | Output |
|--------|-----|--------|
| `PrintInvoice($id)` | `/wt_invoicing/PrintInvoice/{no_inv}` | PDF Invoice |
| `PrintPackinglist($id)` | `/wt_invoicing/PrintPackinglist/{no_inv}` | PDF Packing List |
| `PrintPackinglistSlitting($id)` | `/wt_invoicing/PrintPackinglistSlitting/{no_inv}` | PDF Slitting PL |
| `PrintProformaInvoice($id)` | `/wt_invoicing/PrintProformaInvoice/{id}` | PDF Proforma |
| `PrintPreviewInvoice()` | `/wt_invoicing/PrintPreviewInvoice` | PDF Preview |
| `export_coretax_excel()` | `/wt_invoicing/export_coretax_excel` | Excel Coretax |
| `export_coretax_excel_row()` | `/wt_invoicing/export_coretax_excel_row?getID=x` | Excel per batch |
| `export_data_mon_inv($a,$b)` | `/wt_invoicing/export_data_mon_inv/{tgl_awal}/{tgl_akhir}` | Excel monitoring |

## 5. Data Mutations

### 5.1 Save Invoice (`SaveNewInvoice`)
```
1. Generate id (MAX+1), code (I{YY}{NNNNN}), no_surat (INV-MP/YY/BLN/NNNN)
2. Upload PO & SO files
3. INSERT tr_invoice (header)
4. INSERT BATCH tr_invoice_detail (items)
5. UPDATE tr_spk_marketing: percent_invoice += %, total_invoice += dpp
6. UPDATE tr_delivery_order: status_invoice = 'CLS', nilai_invoice = dpp
```

### 5.2 Print Invoice (`PrintInvoice`)
```
1. UPDATE tr_invoice: status = 1, printed_on = now(), printed_by = user
2. Query header + detail
3. Render view → HTML2PDF → Output PDF
```

### 5.3 Follow-Up (`saveFollowUp`)
```
1. UPDATE tr_followup: aktif = 'N' (semua existing per no_invoice)
2. Upload tanda_terima file
3. INSERT tr_followup (new active record)
4. UPDATE tr_invoice: tgl_terima, tgl_followup, tgl_janji_bayar
```

### 5.4 Generate E-Faktur (`generate_efaktur`)
```
1. Query invoices by no_surat (selected IDs)
2. Calculate DPP/PPN per detail item (sheet vs coil logic)
3. Compose export data structure
4. INSERT BATCH faktur_e_logs (per invoice)
5. UPDATE tr_invoice: stat_efaktur = 1 WHERE no_surat IN (selected)
6. Store data in session
7. Redirect → export_coretax_excel (read from session, output Excel)
```

## 6. Sheet vs Coil Calculation Logic

```php
// SHEET PRODUCT (id_bentuk = 'B2000002'):
$qty = SUM(stock_material.qty_sheet) WHERE no_kirim = {id_do} AND id_category3 = {item}
$total = harga_satuan * qty_sheet
$dpp = ceil(11/12 * total)
$ppn = dpp * 12/100
$grand_total = total + ppn

// COIL/REGULAR PRODUCT:
$qty = tr_invoice_detail.qty_invoice
$total = qty * harga_satuan
$dpp = ceil(11/12 * total)
$ppn = dpp * 12/100
$grand_total = total + ppn
```

## 7. Invoice Number Generation

```php
// Format: INV-MP/{YY}/{ROMAWI}/{NNNN}
// Bulan diambil dari tgl_delivery_order (bukan hari ini)
$bulan = date('m', strtotime($tgl_delivery_order));
$romawi = ['01'=>'I', '02'=>'II', ..., '12'=>'XII'][$bulan];
$counter = MAX(RIGHT(no_surat, 4)) FROM tr_invoice WHERE no_surat LIKE '%/{YY}/%' + 1;
$result = "INV-MP/{$tahun_short}/{$romawi}/" . sprintf("%04s", $counter);
```

## 8. Print Invoice Layout (PDF)

```
┌──────────────────────────────────────────────────────┐
│ [Logo Metalsindo]  PT METALSINDO PACIFIC  [Logo ISO] │
│ Jl. Jababeka XIV, Blok J no. 10H                    │
│ NPWP: 21.098.204.7-414.000                          │
│                                          INVOICE     │
│                                        (Sales/Jasa)  │
├──────────────────────────────────────────────────────┤
│ Sold To: {Customer}          Invoice No: {no_surat}  │
│          {Address}           Invoice Date: {date}    │
│          Att. Keuangan       Our Delivery No: {DO}   │
│                              Delivery Date: {tgl_do} │
│ Delivered To: As An Order    Your Order No: {PO}     │
│                              Terms: {payment_term}   │
├──────────────────────────────────────────────────────┤
│ No│Quantity│Units│Description        │Per Unit│Amount │
│  1│  xxx   │ Kgs│ Product, size      │  xxx  │  xxx  │
│...│  ...   │ ...│ ...                │  ...  │  ...  │
├──────────────────────────────────────────────────────┤
│                              Total         │ xxx.xx  │
│                              DPP Nilai Lain│ xxx     │
│                              PPN           │ xxx     │
│                              Grand Total   │ xxx     │
├──────────────────────────────────────────────────────┤
│ TRANSFER TO: PT. BANK OCBC NISP             │        │
│ A/C: 103810048480 (Multi Currency)          │BEST    │
│                                             │REGARDS │
│                                             │        │
│                                             │FINANCE │
└──────────────────────────────────────────────────────┘
```

## 9. E-Faktur Excel Format (Coretax)

### Sheet 1: Faktur
| Column | Content |
|--------|---------|
| A | Baris (row index penghubung) |
| B | Tanggal Faktur (dd/mm/yyyy) |
| C | Jenis Faktur ("Normal") |
| D | Kode Transaksi ("04") |
| H | Referensi (no_invoice/no_surat) |
| J | ID TKU Penjual (NPWP perusahaan 22 digit) |
| K | NPWP Pembeli (16 digit) |
| L | Jenis ID ("TIN") |
| M | Negara ("IDN") |
| O | Nama Pembeli |
| P | Alamat Pembeli |
| R | ID TKU Pembeli (NPWP + "000000") |

### Sheet 2: DetailFaktur
| Column | Content |
|--------|---------|
| A | Baris (link ke Sheet 1) |
| B | Barang/Jasa (A=Barang, B=Jasa) |
| C | Kode Barang (kode_coretax / "290000" untuk jasa) |
| D | Nama Barang/Jasa |
| E | Satuan (UM.0003=Kg, UM.0020=Sheet, UM.0033=Jasa) |
| F | Harga Satuan |
| G | Jumlah |
| H | Diskon |
| I | DPP |
| J | DPP Nilai Lain |
| K | Tarif PPN (12) |
| L | PPN |
| M | Tarif PPnBM (0) |
| N | PPnBM (0) |

## 10. Security & Error Handling

| Aspek | Implementasi |
|-------|--------------|
| Auth | `$this->auth->restrict()` per method |
| Permission | `has_permission()` untuk conditional UI |
| Transaction | `trans_begin/commit/rollback` |
| Input Sanitize | `str_replace(',','',...)` untuk angka |
| File Upload | Extension whitelist, path configured |
| Scrap Check | Block invoice creation if scrap unconfirmed |
| NPWP Validation | E-Faktur skip empty NPWP, visual warning |
