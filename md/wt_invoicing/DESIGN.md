# Dokumen Desain Teknis (Technical Design Document)

# Modul: WT Invoicing (Enterprise Billing, AR Management & CoreTax Integration)

## 1. Arsitektur Modul & Pola Desain

Modul **WT Invoicing** dibangun menggunakan pola arsitektur **HMVC (Hierarchical Model-View-Controller)** pada framework PHP CodeIgniter 3.

```
application/modules/wt_invoicing/
├── controllers/
│   └── Wt_invoicing.php              # Main Controller (3.484 lines)
├── models/
│   └── Wt_invoicing_model.php        # Core Model Layer (1.035 lines)
└── views/
    ├── index_invoice.php             # Halaman utama daftar invoice resmi
    ├── index_proforma_invoice.php    # Daftar proforma invoice
    ├── index_monitoring.php          # Dasbor monitoring piutang (AR Aging)
    ├── index_delivery_order.php      # Daftar Delivery Order siap invoice
    ├── index_spk_marketing.php       # Daftar SPK Marketing siap invoice
    ├── index_efaktur.php             # Antarmuka generate e-Faktur CoreTax
    ├── index_efaktur_list.php        # Riwayat batch ekspor e-Faktur
    ├── index_close.php               # Daftar invoice yang telah closed/lunas
    ├── index_jurnal_piutang.php      # Daftar invoice siap jurnal akuntansi
    ├── index_planning.php            # Daftar planning penagihan termin
    ├── create_invoice.php            # Form pembuatan invoice dari SPK
    ├── create_invoice_do.php         # Form pembuatan invoice dari DO Reguler
    ├── create_invoice_do_slitting.php# Form invoice DO Slitting + Scrap
    ├── create_invoice_proforma.php   # Form penerbitan proforma invoice
    ├── deal_invoice.php              # Form finalisasi invoice dari proforma
    ├── deal_so.php                   # Form deal penawaran ke SO
    ├── preview_invoice.php           # Preview invoice sebelum commit
    ├── createFollowup.php            # Form input follow-up penagihan
    ├── historyfu.php                 # Modal kronologis follow-up invoice
    ├── export_excel_monitoring.php   # Template ekspor Excel monitoring piutang
    ├── PrintInvoice.php              # Template PDF cetak invoice resmi
    ├── PrintPackinglist.php          # Template PDF packing list reguler
    ├── PrintPackinglistSlitting.php  # Template PDF packing list slitting
    ├── PrintProformaInvoice.php      # Template PDF proforma invoice
    ├── PrintHeader2.php              # Template PDF surat jalan reguler
    ├── PrintHeader2Slitting.php      # Template PDF surat jalan slitting
    ├── index_approval.php            # Antarmuka request approval penawaran
    ├── formapproval.php              # Form pengajuan approval penawaran
    ├── formprosesapproval.php        # Form pemrosesan persetujuan penawaran
    ├── index_so.php                  # Antarmuka monitoring status Sales Order
    ├── statusso.php                  # Form update status SO
    ├── statusterkirim.php            # Form update status barang terkirim
    ├── index_loss.php                # Antarmuka monitoring penawaran loss
    ├── formloss.php                  # Form input keterangan loss penawaran
    ├── statusloss.php                # Form update status loss penawaran
    ├── revisipenawaran.php           # Form revisi data penawaran
    ├── editpenawaran.php             # Form edit penawaran harga
    ├── addpenawaran.php              # Form penambahan penawaran harga
    ├── ajukanpenawaran.php           # Form pengajuan penawaran harga
    ├── history.php                   # Riwayat penawaran harga
    └── viewhistory.php               # Detail riwayat penawaran harga
```

### 1.1 Diagram Dependensi Modul

```mermaid
graph TD
    subgraph Controller_Layer
        CTRL[Wt_invoicing Controller]
    end

    subgraph Base_Framework
        AC[Admin_Controller]
        AUTH[Auth Library / RBAC]
        TMPL[Template Library]
    end

    subgraph External_Libraries
        PDF[HTML2PDF Engine]
        MPDF[Mpdf Engine]
        XLS[PHPExcel Library]
        UPL[CI_Upload Library]
        IMG[CI_Image_lib / GD2]
    end

    subgraph Model_Layer
        M_INV[Wt_invoicing_model]
        M_SPK[Inventory_4_model]
        M_DO[Delivery_order_model]
        M_ACT[Aktifitas_model]
    end

    subgraph Database_Tables
        DB[(MySQL Database)]
    end

    CTRL -->|Extends| AC
    AC --> AUTH & TMPL
    CTRL --> M_INV & M_SPK & M_DO & M_ACT
    CTRL --> PDF & XLS & UPL & IMG & MPDF
    M_INV --> DB
```

---

## 2. Desain Controller (`Wt_invoicing.php`)

### 2.1 Katalog Endpoint Lengkap

#### A. View Rendering Endpoints (GET / Route Request)

| Method | HTTP | URL Endpoint | Otorisasi | View Template yang Dirender | Deskripsi Fungsional |
| :--- | :---: | :--- | :--- | :--- | :--- |
| `index()` | GET | `/wt_invoicing` | `Invoicing.View` | `index_invoice` | Menampilkan halaman utama daftar invoice resmi. |
| `index_proforma()` | GET | `/wt_invoicing/index_proforma` | `Invoicing.View` | `index_proforma_invoice` | Menampilkan daftar proforma invoice. |
| `spk_marketing()` | GET | `/wt_invoicing/spk_marketing` | `Invoicing.View` | `index_spk_marketing` | Menampilkan daftar SPK Marketing siap invoice. |
| `delivery_order()` | GET | `/wt_invoicing/delivery_order` | `Invoicing.View` | `index_delivery_order` | Menampilkan daftar DO yang belum di-invoice. |
| `index_monitoring()` | GET | `/wt_invoicing/index_monitoring`| `Invoicing.View` | `index_monitoring` | Menampilkan dasbor monitoring piutang (AR aging). |
| `index_close()` | GET | `/wt_invoicing/index_close` | `Invoicing.View` | `index_close` | Menampilkan daftar invoice yang telah ditutup/lunas. |
| `plan_tagih()` | GET | `/wt_invoicing/plan_tagih` | `Invoicing.View` | `index_planning` | Menampilkan jadwal planning penagihan termin. |
| `jurnal_invoicing()` | GET | `/wt_invoicing/jurnal_invoicing`| `Invoicing.View` | `index_jurnal_piutang` | Menampilkan invoice status jurnal `OPN`. |
| `e_faktur()` | GET | `/wt_invoicing/e_faktur` | `Invoicing.View` | `index_efaktur` | Menampilkan antarmuka ekspor e-Faktur CoreTax. |
| `e_faktur_list()` | GET | `/wt_invoicing/e_faktur_list` | `Invoicing.View` | `index_efaktur_list` | Menampilkan riwayat batch ekspor e-Faktur. |
| `createInvoice($id)` | GET | `/wt_invoicing/createInvoice/{id_spk}` | `Invoicing.Add` | `create_invoice` | Form pembuatan invoice langsung dari SPK. |
| `createInvoiceDo($iddo)`| GET | `/wt_invoicing/createInvoiceDo/{id_do}` | `Invoicing.Add` | `create_invoice_do` | Form pembuatan invoice dari Delivery Order reguler. |
| `createInvoiceDoSlitting($iddo)` | GET | `/wt_invoicing/createInvoiceDoSlitting/{id_do}` | `Invoicing.Add` | `create_invoice_do_slitting` | Form invoice DO Slitting (cek scrap control DO). |
| `createProformaInvoice($id)` | GET | `/wt_invoicing/createProformaInvoice/{id_spk}` | `Invoicing.Add` | `create_invoice_proforma`| Form pembuatan proforma invoice dari SPK. |
| `createDealInvoice($id)`| GET | `/wt_invoicing/createDealInvoice/{id_inv}` | `Invoicing.Add` | `deal_invoice` | Form finalisasi deal invoice dari proforma. |
| `previewInvoice($id)` | GET | `/wt_invoicing/previewInvoice/{id_plan}` | `Invoicing.View` | `preview_invoice` | Preview rincian invoice sebelum commit. |
| `FollowUp($id)` | GET | `/wt_invoicing/FollowUp/{no_inv}` | `Invoicing.Manage` | `createFollowup` | Form pencatatan follow-up penagihan. |
| `historyfu($id)` | GET | `/wt_invoicing/historyfu/{no_inv}` | `Invoicing.View` | `historyfu` | Render HTML modal riwayat follow-up invoice. |

#### B. API & Form Mutation Endpoints (POST - JSON Response)

| Method | HTTP | URL Endpoint | Otorisasi | Request Payload | Response Schema & Efek |
| :--- | :---: | :--- | :--- | :--- | :--- |
| `SaveNewInvoice()` | POST | `/wt_invoicing/SaveNewInvoice` | `Invoicing.Add` | Form data header, detail `dt[]`, files `upload_po`, `upload_so` | `{"status": 1, "code": "I2400015", "pesan": "Success..."}` |
| `SaveNewProformaInvoice()` | POST | `/wt_invoicing/SaveNewProformaInvoice` | `Invoicing.Add` | Form data proforma, detail `dt[]`, file uploads | `{"status": 1, "code": "F2400003", "pesan": "Success..."}` |
| `SaveNewDealInvoice()` | POST | `/wt_invoicing/SaveNewDealInvoice` | `Invoicing.Add` | Data deal proforma to invoice | `{"status": 1, "code": "...", "pesan": "Success..."}` |
| `SavePreviewInvoice()` | POST | `/wt_invoicing/SavePreviewInvoice` | `Invoicing.Add` | Data temporary invoice draft | `{"status": 1, "pesan": "Success..."}` |
| `saveFollowUp()` | POST | `/wt_invoicing/saveFollowUp` | `Invoicing.Manage` | `no_invoice`, `received`, `tgl_terima`, `tgl_janji_bayar`, file `tanda_terima` | `{"status": 1, "pesan": "Success Save Item..."}` |
| `closeInvoice()` | POST | `/wt_invoicing/closeInvoice` | `Invoicing.Manage` | `id` (nomor invoice) | `{"status": 1, "pesan": "Success. Thanks ..."}` |
| `generate_efaktur()` | POST | `/wt_invoicing/generate_efaktur` | `Invoicing.Manage` | `id_generate[]` (array nomor surat invoice) | `{"status": "success"}` -> data disalin ke session |

#### C. DataTables Server-Side Processing Endpoints (POST / AJAX)

| Method | HTTP | URL Endpoint | Sumber Data / Model | Output Data |
| :--- | :---: | :--- | :--- | :--- |
| `get_invoicing()` | POST | `/wt_invoicing/get_invoicing` | `Wt_invoicing_model->get_invoicing()` | JSON DataTables daftar invoice terbit. |
| `get_monitoring_invoice()` | POST | `/wt_invoicing/get_monitoring_invoice` | `Wt_invoicing_model->get_monitoring_invoice()` | JSON DataTables monitoring piutang & aging. |
| `get_data_spk_marketing()` | POST | `/wt_invoicing/get_data_spk_marketing` | `Wt_invoicing_model->get_data_spk_marketing()` | JSON DataTables SPK Marketing siap tagih. |
| `get_efaktur()` | POST | `/wt_invoicing/get_efaktur` | `Wt_invoicing_model->get_efaktur()` | JSON DataTables faktur siap ekspor CoreTax. |
| `list_efaktur()` | POST | `/wt_invoicing/list_efaktur` | `Wt_invoicing_model->list_efaktur()` | JSON DataTables riwayat batch log e-Faktur. |
| `get_all_efaktur_id()` | POST | `/wt_invoicing/get_all_efaktur_id` | `Wt_invoicing_model->get_all_efaktur_id()` | Array JSON semua ID invoice untuk *Check All*. |

#### D. Document Export & Printing Endpoints (GET / Binary Stream)

| Method | HTTP | URL Endpoint | Engine | Tipe Konten & Format Output |
| :--- | :---: | :--- | :--- | :--- |
| `PrintInvoice($id)` | GET | `/wt_invoicing/PrintInvoice/{no_inv}` | HTML2PDF | `application/pdf` (Invoice Resmi PT Metalsindo) |
| `PrintPackinglist($id)` | GET | `/wt_invoicing/PrintPackinglist/{no_inv}` | HTML2PDF | `application/pdf` (Packing List Reguler) |
| `PrintPackinglistSlitting($id)`| GET | `/wt_invoicing/PrintPackinglistSlitting/{no_inv}` | HTML2PDF | `application/pdf` (Packing List Slitting + Scrap) |
| `PrintProformaInvoice($id)` | GET | `/wt_invoicing/PrintProformaInvoice/{id_inv}` | HTML2PDF | `application/pdf` (Proforma Invoice) |
| `PrintHeader()` | GET | `/wt_invoicing/PrintHeader/{id_do}` | HTML2PDF | `application/pdf` (Surat Jalan DO Reguler) |
| `PrintHeaderSlitting()` | GET | `/wt_invoicing/PrintHeaderSlitting/{id_do}` | HTML2PDF | `application/pdf` (Surat Jalan DO Slitting) |
| `export_data_mon_inv($t1,$t2)`| GET | `/wt_invoicing/export_data_mon_inv/{t1}/{t2}` | PHPExcel | `application/vnd.ms-excel` (Monitoring Piutang) |
| `export_coretax_excel()` | GET | `/wt_invoicing/export_coretax_excel` | PHPExcel | `application/vnd.openxmlformats` (CoreTax Batch Session) |
| `export_coretax_excel_row()` | GET | `/wt_invoicing/export_coretax_excel_row?getID=x` | PHPExcel | `application/vnd.openxmlformats` (CoreTax by Batch ID) |

---

## 3. Desain Model Layer (`Wt_invoicing_model.php`)

### 3.1 Spesifikasi Generator Penomoran

```mermaid
graph LR
    subgraph Number_Generators
        G1["generate_id()"] -->|Query MAX id_invoice| R1["Counter Integer"]
        G2["generate_code()"] -->|Query MAX no_invoice| R2["I2400015"]
        G3["BuatNomor(no_do)"] -->|Query MAX no_surat by Year| R3["INV-MP/24/VIII/0023"]
        G4["generate_code_proforma()"] -->|Query MAX no_invoice proforma| R4["F2400003"]
        G5["BuatNomorProforma()"] -->|Query MAX no_surat proforma| R5["PROF-INV-MP/24/VIII/0004"]
    end
```

#### Logika Generator Nomor Surat Resmi (`BuatNomor`):
1. Mengambil tanggal DO rujukan untuk menentukan bulan dan tahun dasar penagihan.
2. Mengonversi 2-digit bulan (`01`-`12`) menjadi angka Romawi (`I`-`XII`).
3. Menjalankan query pencarian nilai counter maksimum pada tahun berjalan:
   ```sql
   SELECT MAX(RIGHT(no_surat, 4)) as max_id
   FROM tr_invoice
   WHERE no_surat LIKE '%/24/%'
   ```
4. Menambahkan counter $+1$ dan memformat dengan 4 digit zero-padding (`sprintf("%04s", $counter)`).
5. Hasil akhir: `INV-MP/24/VIII/0023`.

---

## 4. Sequence Diagrams Alur Transaksi Kritis

### 4.1 Pembuatan Invoice dari Delivery Order Slitting (dengan Scrap Check)

```mermaid
sequenceDiagram
    autonumber
    actor User as Billing Staff
    participant C as Wt_invoicing Controller
    participant M as Wt_invoicing_model
    participant DB as MySQL Database

    User->>C: GET /createInvoiceDoSlitting/{id_do}
    C->>DB: Query dt_delivery_order_child_scrap (Status Konfirmasi di Control DO)
    alt Ada scrap belum dikonfirmasi
        DB-->>C: scrap_status == pending
        C-->>User: Flash Data Error & Redirect Back
    else Semua scrap terkonfirmasi
        DB-->>C: scrap_status == OK
        C->>M: CariSPKdo($id_do) & CariSPKdoscrap($id_do)
        M-->>C: Detail coil hasil slitting & scrap per thickness
        C-->>User: Render view create_invoice_do_slitting.php
    end

    User->>C: POST /SaveNewInvoice (Form Data + PO/SO Files)
    C->>DB: trans_begin()
    C->>M: generate_id(), generate_code(), BuatNomor($no_do)
    M-->>C: Return ID, Kode IYY#####, No Surat INV-MP/...
    C->>C: Handle file upload PO & SO via CI_Upload & Resize GD2
    C->>DB: INSERT INTO tr_invoice (Header)
    C->>DB: INSERT INTO tr_invoice_detail (Batch Items + Scrap)
    C->>DB: UPDATE tr_spk_marketing (percent_invoice, total_invoice)
    C->>DB: UPDATE tr_delivery_order SET status_invoice = 'CLS' WHERE id_delivery_order = id_do

    alt DB Transaction Status == FALSE
        C->>DB: trans_rollback()
        C-->>User: JSON {"status": 0, "pesan": "Gagal Save Item..."}
    else DB Transaction Status == TRUE
        C->>DB: trans_commit()
        C-->>User: JSON {"status": 1, "code": "I2400015", "pesan": "Success..."}
    end
```

### 4.2 Siklus Follow-Up Piutang & Close Invoice

```mermaid
sequenceDiagram
    autonumber
    actor AR as AR Staff / Collector
    participant C as Wt_invoicing Controller
    participant DB as MySQL Database

    AR->>C: POST /saveFollowUp (no_invoice, received, tgl_terima, tgl_janji_bayar, tanda_terima)
    C->>DB: trans_begin()
    C->>DB: UPDATE tr_followup SET aktif = 'N' WHERE no_invoice = no_invoice
    C->>C: Upload berkas bukti tanda terima
    C->>DB: INSERT INTO tr_followup (no_invoice, received, tgl_terima, tgl_janji, upload, aktif='Y')
    C->>DB: UPDATE tr_invoice SET tgl_terima = tgl_terima, tgl_janji_bayar = tgl_janji, tgl_followup = NOW()
    C->>DB: trans_commit()
    C-->>AR: JSON {"status": 1, "pesan": "Success Save Item..."}

    Note over AR,C: Setelah pembayaran customer lunas 100%
    AR->>C: POST /closeInvoice (id = no_invoice)
    C->>DB: trans_begin()
    C->>DB: UPDATE tr_invoice SET status_close = '1' WHERE no_invoice = id
    C->>DB: trans_commit()
    C-->>AR: JSON {"status": 1, "pesan": "Success. Thanks ..."}
```

---

## 5. Mesin Ekspor DJP CoreTax (Tax Engine Specification)

### 5.1 Struktur Spreadsheet Dua Worksheet (`PHPExcel`)

```mermaid
graph TD
    subgraph Sheet_1_Faktur["Sheet 1: Faktur"]
        H1["NPWP Penjual: 0210982047414000"]
        H2["ID TKU Penjual: 0210982047414000000000"]
        H3["Kode Transaksi: 04 (DPP Nilai Lain)"]
        H4["Baris: Index 1..N"]
        H5["NPWP Pembeli: 16 Digit Padded"]
        H6["ID TKU Pembeli: NPWP + 000000"]
        H7["Penutup: END Baris Terakhir"]
    end

    subgraph Sheet_2_DetailFaktur["Sheet 2: DetailFaktur"]
        D1["Baris: Foreign Key ke Sheet Faktur"]
        D2["Barang/Jasa: A (Barang) / B (Jasa Slitting)"]
        D3["Kode Barang: kode_coretax / 290000"]
        D4["Satuan: UM.0003 (Kg) / UM.0020 (Sheet) / UM.0033 (Jasa)"]
        D5["DPP Nilai Lain: ceil(11/12 * Total)"]
        D6["Tarif PPN: 12% | PPN: DPP Nilai Lain * 12%"]
    end

    H4 -.->|Index Link 1:N| D1
```

### 5.2 Format Kolom Sheet 1 (`Faktur`)
- **Cell A1:B1 (Merged):** `"NPWP Penjual"` (Bold, Centered)
- **Cell C1:** Explicit String `"0210982047414000"`
- **Header Kolom (Row 3, A3:R3, Bold):**
  `Baris` | `Tanggal Faktur` | `Jenis Faktur` | `Kode Transaksi` | `Keterangan` | `Dokumen Pendukung` | `Period Dok Pendukung` | `Referensi` | `Cap Fasilitas` | `ID TKU Penjual` | `NPWP/NIK Pembeli` | `Jenis ID Pembeli` | `Negara Pembeli` | `Nomor Dokumen Pembeli` | `Nama Pembeli` | `Alamat Pembeli` | `Email Pembeli` | `ID TKU Pembeli`
- **Baris Penutup:** String `"END"` pada Kolom A di baris $N+1$.

### 5.3 Format Kolom Sheet 2 (`DetailFaktur`)
- **Header Kolom (Row 1, A1:N1, Bold):**
  `Baris` | `Barang/Jasa` | `Kode Barang Jasa` | `Nama Barang/Jasa` | `Nama Satuan Ukur` | `Harga Satuan` | `Jumlah Barang Jasa` | `Total Diskon` | `DPP` | `DPP Nilai Lain` | `Tarif PPN` | `PPN` | `Tarif PPnBM` | `PPnBM`
- **Format Angka Kolom H, I, J, L, N:** Number Format `0.00` (*Explicit Double Precision*).

---

## 6. Desain State Machine & Transisi Status Transaksi

```mermaid
stateDiagram-v2
    [*] --> DRAFT : User Input Penawaran / Planning
    DRAFT --> PROFORMA : SaveNewProformaInvoice()
    DRAFT --> ISSUED : SaveNewInvoice() dari DO/SPK
    PROFORMA --> ISSUED : SaveNewDealInvoice() (Deal Proforma)

    ISSUED --> PRINTED : PrintInvoice() (status = 1)
    PRINTED --> FOLLOW_UP : saveFollowUp() (tgl_terima, janji bayar)
    FOLLOW_UP --> FOLLOW_UP : Update Follow-Up Berkala
    FOLLOW_UP --> CLOSED : closeInvoice() (status_close = '1')

    state ISSUED {
        [*] --> Unexported_Tax : stat_efaktur = 0
        Unexported_Tax --> Exported_CoreTax : generate_efaktur() (stat_efaktur = 1)
    }

    state ISSUED {
        [*] --> Unjournaled : status_jurnal = 'OPN'
        Unjournaled --> Journaled_GL : Posted ke Accounting (status_jurnal = 'CLS')
    }

    CLOSED --> [*]
```

---

## 7. Desain Frontend & Mekanisme Kalkulasi Client-Side

### 7.1 Kalkulasi Real-Time JavaScript (Form Invoicing)

Pada view `create_invoice_do.php` dan `create_invoice_do_slitting.php`, interaksi antar field input dikendalikan oleh listener event jQuery:

```javascript
// Kalkulasi PPN & Grand Total Dinamis
function hitungPpn() {
    var totalproduk = parseFloat($('#total').val().replace(/,/g, '')) || 0;
    var persentase  = parseFloat($('#persentase').val().replace(/,/g, '')) || 0;
    var ppn_rate    = parseFloat($('#ppn').val().replace(/,/g, '')) || 0;

    // 1. Hitung Nilai Tagihan Bersih Berdasarkan Persentase Termin
    var tagih = Math.round((totalproduk * persentase) / 100);
    $('#grandtotal').val(number_format(tagih, 2));

    // 2. Hitung DPP Nilai Lain (11/12)
    var dpp = Math.ceil((11 / 12) * tagih);
    $('#dpp').val(number_format(dpp, 2));

    // 3. Hitung PPN 12%
    var totalppn = Math.round((dpp * ppn_rate) / 100);
    $('#totalppn').val(number_format(totalppn, 2));

    // 4. Hitung Nilai Tagih Akhir (Grand Total)
    var grand_total = tagih + totalppn;
    $('#nilai_tagih').val(number_format(grand_total, 2));
}
```

### 7.2 Konfigurasi DataTables Server-Side AJAX

```javascript
$('#table_invoice').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: base_url + 'wt_invoicing/get_invoicing',
        type: 'POST',
        data: function (d) {
            d.custom_filter = $('#filter_status').val();
        }
    },
    order: [[0, 'desc']],
    columnDefs: [
        { targets: [-1], orderable: false, className: 'text-center' }
    ]
});
```

---

## 8. Desain Keamanan, Integritas Data & Penanganan Error

| Layer Keamanan | Mekanisme Implementasi |
| :--- | :--- |
| **Authentication & RBAC** | Pemeriksaan hak akses per method melalui `$this->auth->restrict($this->managePermission)`. Jika gagal, sistem memutus eksekusi dan redirect ke unauthorized page. |
| **Database Transaction ACID** | Seluruh transaksi multi-tabel (`tr_invoice`, `tr_invoice_detail`, `tr_spk_marketing`, `tr_delivery_order`) dilindungi blok `$this->db->trans_begin()`, rollback otomatis jika status `FALSE`. |
| **Pencegahan Timeout & Memory Exhaustion** | Pada method ekspor file besar (`export_coretax_excel`), sistem memanggil `ini_set('memory_limit', '1024M')` dan `set_time_limit(0)` serta membersihkan buffer output `while (ob_get_level()) { ob_end_clean(); }`. |
| **Sanitasi File Upload** | Validasi whitelist ekstensi (`pdf|jpg|png|doc|docx|xls|xlsx`), pembatasan ukuran berkas, dan sanitasi nama file untuk mencegah eksekusi *Remote Code Execution* (RCE). |
