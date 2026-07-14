# Design Document - Modul Penerimaan Invoice

## 1. Arsitektur Sistem

### 1.1 Technology Stack

| Layer         | Technology            | Version  | Keterangan                 |
| ------------- | --------------------- | -------- | -------------------------- |
| Framework     | CodeIgniter 3         | 3.x      | HMVC pattern via MY_Router |
| PHP           | PHP                   | 7.x      | Server-side                |
| Database      | MySQL                 | 5.7      | Docker container           |
| Frontend JS   | jQuery                | 3.x      | DOM manipulation, AJAX     |
| DataTable     | DataTables            | 2.3.7    | Server-side processing     |
| UI Framework  | AdminLTE              | 2.x      | Bootstrap 3 based          |
| CSS           | Bootstrap             | 3.x      | Responsive grid            |
| Select        | Select2               | 4.x      | Enhanced dropdown          |
| Alert         | SweetAlert            | 1.x      | Popup konfirmasi           |
| PDF           | mPDF                  | 5.7/7.17 | PDF generation             |
| Number Format | number-divider.min.js | -        | Format angka ribuan        |
| Date          | Datepicker            | -        | Bootstrap datepicker       |

### 1.2 Architecture Pattern

```
┌─────────────────────────────────────────────────────────────┐
│                    BROWSER (Client)                          │
│  ┌─────────┐  ┌──────────┐  ┌───────────┐  ┌───────────┐  │
│  │ jQuery  │  │DataTables│  │ Select2   │  │SweetAlert │  │
│  └────┬────┘  └─────┬────┘  └─────┬─────┘  └─────┬─────┘  │
│       │              │             │              │          │
│       └──────────────┼─────────────┼──────────────┘          │
│                      │ AJAX POST                             │
└──────────────────────┼───────────────────────────────────────┘
                       │
┌──────────────────────┼───────────────────────────────────────┐
│                 APPLICATION SERVER                            │
│  ┌───────────────────┼─────────────────────────────────┐    │
│  │         CodeIgniter 3 (HMVC)                         │    │
│  │                                                      │    │
│  │  ┌──────────────────────────────────────────────┐   │    │
│  │  │        Admin_Controller (Base)                │   │    │
│  │  │  - Auth check                                │   │    │
│  │  │  - Session management                        │   │    │
│  │  │  - Permission validation                     │   │    │
│  │  └──────────────────┬───────────────────────────┘   │    │
│  │                     │                                │    │
│  │  ┌──────────────────┼───────────────────────────┐   │    │
│  │  │     Penerimaan Controller                     │   │    │
│  │  │  - 20+ public methods                        │   │    │
│  │  │  - Business logic                            │   │    │
│  │  │  - Validation                                │   │    │
│  │  └───────┬──────────────────────────────┬───────┘   │    │
│  │          │                              │            │    │
│  │  ┌───────┼────────┐          ┌─────────┼────────┐  │    │
│  │  │ Penerimaan     │          │ External Models  │  │    │
│  │  │ _model         │          │ - Acc_model      │  │    │
│  │  │ - Server-side  │          │ - Jurnal_model   │  │    │
│  │  │ - Generate PN  │          │ - Wt_invoicing   │  │    │
│  │  └───────┬────────┘          └─────────┬────────┘  │    │
│  │          │                              │            │    │
│  └──────────┼──────────────────────────────┼────────────┘    │
│             │                              │                  │
└─────────────┼──────────────────────────────┼──────────────────┘
              │                              │
┌─────────────┼──────────────────────────────┼──────────────────┐
│             │      DATABASE LAYER          │                  │
│  ┌──────────┴────────────┐    ┌────────────┴──────────────┐  │
│  │  metalsindo_live      │    │  gl_metalsindo_live       │  │
│  │  (Operational DB)     │    │  (Accounting/GL DB)       │  │
│  │                       │    │                           │  │
│  │  - tr_invoice_payment │    │  - jarh                   │  │
│  │  - tr_invoice_payment │    │  - jurnal                 │  │
│  │    _detail            │    │  - coa_master             │  │
│  │  - tr_cn_cross        │    │  - ar                     │  │
│  │  - tr_unlocated_bank  │    │  - pastibisa_tb_cabang    │  │
│  │  - tr_invoice         │    │                           │  │
│  │  - tr_kartu_piutang   │    │                           │  │
│  │  - master_customers   │    │                           │  │
│  │  - tr_retur_penjualan │    │                           │  │
│  │  - tr_spk_marketing   │    │                           │  │
│  └───────────────────────┘    └───────────────────────────┘  │
└───────────────────────────────────────────────────────────────┘
```

### 1.3 Module File Structure

```
application/modules/penerimaan/
├── controllers/
│   └── Penerimaan.php              # 1360 lines - Main controller
├── models/
│   └── Penerimaan_model.php        # ~300 lines - Data access
├── views/
│   ├── list_payment.php            # Index - DataTable server-side list pembayaran
│   ├── invoice_siap_terima.php     # DataTable - invoice yang ready untuk dibayar
│   ├── create_penerimaan_new.php   # Form utama input penerimaan (1050 lines)
│   ├── view_penerimaan_new.php     # View detail read-only (current)
│   ├── view_penerimaan.php         # View detail read-only (legacy)
│   ├── invoice.php                 # Modal DataTable pilih invoice per customer
│   ├── print_penerimaan.php        # Template PDF Bukti Uang Masuk
│   ├── create_unlocated.php        # Form input deposit/unlocated
│   ├── create_lebihbayar.php       # Form input lebih bayar
│   ├── lebihbayar.php              # List lebih bayar per customer
│   ├── form_buktipotong.php        # Form + list bukti potong PPH
│   └── index_jurnal_penerimaan.php # List jurnal pending approval
└── sql/
    └── migration_cn_cross.sql      # DDL: ALTER tr_retur_penjualan + CREATE tr_cn_cross
```

---

## 2. Sequence Diagram

### 2.1 Create Penerimaan (Full Flow)

```
User           Browser/JS         Controller              Model              DB(metalsindo)      DB(GL)
 │                │                    │                    │                      │                │
 │  Navigate      │                    │                    │                      │                │
 │───────────────▶│ GET /penerimaan/   │                    │                      │                │
 │                │   modal_detail_    │                    │                      │                │
 │                │   invoice/{inv}    │                    │                      │                │
 │                │───────────────────▶│                    │                      │                │
 │                │                    │ modal_detail_      │                      │                │
 │                │                    │  invoice($id)      │                      │                │
 │                │                    │───────────────────▶│                      │                │
 │                │                    │                    │ SELECT tr_invoice     │                │
 │                │                    │                    │─────────────────────▶│                │
 │                │                    │                    │◀─────────────────────│                │
 │                │                    │                    │ get_Coa_Bank_Aja     │                │
 │                │                    │                    │─────────────────────▶│(GL)────────────▶│
 │                │                    │                    │◀────────────────────────────────────── │
 │                │                    │◀───────────────────│                      │                │
 │                │◀───────────────────│ render view        │                      │                │
 │◀───────────────│ Show form          │                    │                      │                │
 │                │                    │                    │                      │                │
 │  Select Cust   │                    │                    │                      │                │
 │───────────────▶│                    │                    │                      │                │
 │  Click "Add    │                    │                    │                      │                │
 │  Invoice"      │                    │                    │                      │                │
 │───────────────▶│ POST /penerimaan/  │                    │                      │                │
 │                │ get_invoice_cn_    │                    │                      │                │
 │                │ serverside         │                    │                      │                │
 │                │───────────────────▶│                    │                      │                │
 │                │                    │ UNION ALL query    │                      │                │
 │                │                    │ (invoice + CN)     │                      │                │
 │                │                    │─────────────────────────────────────────▶│                │
 │                │                    │◀─────────────────────────────────────────│                │
 │                │◀───────────────────│ JSON DataTable     │                      │                │
 │◀───────────────│ Show modal table   │                    │                      │                │
 │                │                    │                    │                      │                │
 │  Pick invoices │                    │                    │                      │                │
 │  & CN rows     │                    │                    │                      │                │
 │───────────────▶│ JS: startmutasi()  │                    │                      │                │
 │                │ adds rows to table │                    │                      │                │
 │                │                    │                    │                      │                │
 │  Fill amounts  │                    │                    │                      │                │
 │  Check Kontrol │                    │                    │                      │                │
 │───────────────▶│ JS: cekall()       │                    │                      │                │
 │                │ JS: validateCn()   │                    │                      │                │
 │                │                    │                    │                      │                │
 │  Click "Simpan"│                    │                    │                      │                │
 │───────────────▶│ JS: savemutasi()   │                    │                      │                │
 │                │ Validate client    │                    │                      │                │
 │                │ SweetAlert confirm │                    │                      │                │
 │                │                    │                    │                      │                │
 │  Confirm "Ya"  │                    │                    │                      │                │
 │───────────────▶│ POST /penerimaan/  │                    │                      │                │
 │                │ save_penerimaan    │                    │                      │                │
 │                │ (form serialize)   │                    │                      │                │
 │                │───────────────────▶│                    │                      │                │
 │                │                    │ SERVER VALIDATION  │                      │                │
 │                │                    │ 1. Kontrol = 0?    │                      │                │
 │                │                    │ 2. CN balance?     │                      │                │
 │                │                    │                    │                      │                │
 │                │                    │ generate_nopn(tgl) │                      │                │
 │                │                    │───────────────────▶│                      │                │
 │                │                    │◀───────────────────│ PN-YYXNNNNN         │                │
 │                │                    │                    │                      │                │
 │                │                    │ INSERT tr_invoice_payment (header)        │                │
 │                │                    │─────────────────────────────────────────▶│                │
 │                │                    │                    │                      │                │
 │                │                    │ LOOP detail rows:  │                      │                │
 │                │                    │ IF type=cn:        │                      │                │
 │                │                    │   INSERT payment_detail (negatif)         │                │
 │                │                    │   INSERT tr_cn_cross                      │                │
 │                │                    │ ELSE (invoice):    │                      │                │
 │                │                    │   INSERT payment_detail                   │                │
 │                │                    │   UPDATE tr_invoice (sisa -= bayar)       │                │
 │                │                    │   UPDATE tr_spk_marketing (bayar += )     │                │
 │                │                    │─────────────────────────────────────────▶│                │
 │                │                    │                    │                      │                │
 │                │                    │ IF tambah_lebih_bayar > 0:                │                │
 │                │                    │   INSERT tr_unlocated_bank                │                │
 │                │                    │─────────────────────────────────────────▶│                │
 │                │                    │                    │                      │                │
 │                │                    │ IF id_unlocated != '':                    │                │
 │                │                    │   UPDATE tr_unlocated_bank.saldo          │                │
 │                │                    │─────────────────────────────────────────▶│                │
 │                │                    │                    │                      │                │
 │                │◀───────────────────│ JSON {status:1}    │                      │                │
 │◀───────────────│ SweetAlert success │                    │                      │                │
 │                │ redirect           │                    │                      │                │
```

### 2.2 Approval Jurnal BUM

```
Supervisor      Browser            Controller              Jurnal_model         DB(metalsindo)    DB(GL)
 │                │                    │                       │                    │               │
 │  Click Jurnal  │                    │                       │                    │               │
 │───────────────▶│ window.open(       │                       │                    │               │
 │                │  appr_jurnal/kd)   │                       │                    │               │
 │                │───────────────────▶│                       │                    │               │
 │                │                    │ SELECT payment header │                    │               │
 │                │                    │──────────────────────────────────────────▶│               │
 │                │                    │◀──────────────────────────────────────────│               │
 │                │                    │                       │                    │               │
 │                │                    │ get_Nomor_Jurnal_BUM  │                    │               │
 │                │                    │──────────────────────▶│                    │               │
 │                │                    │◀──────────────────────│ BUM-YYXNNNNN      │               │
 │                │                    │                       │                    │               │
 │                │                    │ INSERT jarh (header)  │                    │               │
 │                │                    │─────────────────────────────────────────────────────────▶│
 │                │                    │                       │                    │               │
 │                │                    │ INSERT jurnal[] (detail debit/kredit)      │               │
 │                │                    │─────────────────────────────────────────────────────────▶│
 │                │                    │                       │                    │               │
 │                │                    │ UPDATE ar (kredit, saldo)                  │               │
 │                │                    │─────────────────────────────────────────────────────────▶│
 │                │                    │                       │                    │               │
 │                │                    │ UPDATE cabang.nobum+1 │                    │               │
 │                │                    │─────────────────────────────────────────────────────────▶│
 │                │                    │                       │                    │               │
 │                │                    │ INSERT tr_kartu_piutang (per invoice)      │               │
 │                │                    │──────────────────────────────────────────▶│               │
 │                │                    │                       │                    │               │
 │                │                    │ UPDATE status_jurnal=1│                    │               │
 │                │                    │──────────────────────────────────────────▶│               │
 │                │                    │                       │                    │               │
 │                │                    │ print_penerimaan_fix()│                    │               │
 │                │                    │ (mPDF render)         │                    │               │
 │                │◀───────────────────│ PDF Output            │                    │               │
 │◀───────────────│ PDF in new tab     │                       │                    │               │
```

---

## 3. API Endpoints (Complete)

### 3.1 Page Routes (GET - Render View)

| #   | Method                     | URL                                             | View                    | Permission | Deskripsi                             |
| --- | -------------------------- | ----------------------------------------------- | ----------------------- | ---------- | ------------------------------------- |
| 1   | `index()`                  | `/penerimaan`                                   | list_payment            | View       | Halaman utama, DataTable list payment |
| 2   | `create_new()`             | `/penerimaan/create_new`                        | invoice_siap_terima     | View       | DataTable invoice yang siap dibayar   |
| 3   | `modal_detail_invoice()`   | `/penerimaan/modal_detail_invoice/{no_inv}`     | create_penerimaan_new   | View       | Form create penerimaan                |
| 4   | `view_penerimaan()`        | `/penerimaan/view_penerimaan/{kd_bayar}`        | view_penerimaan_new     | View       | Detail penerimaan (read-only)         |
| 5   | `jurnal_bum()`             | `/penerimaan/jurnal_bum`                        | index_jurnal_penerimaan | View       | List jurnal pending approval          |
| 6   | `unlocated()`              | `/penerimaan/unlocated`                         | create_unlocated        | Add        | Form deposit unlocated                |
| 7   | `createunlocated()`        | `/penerimaan/createunlocated`                   | create_unlocated        | Add        | Form create unlocated (alias)         |
| 8   | `lebihbayar()`             | `/penerimaan/lebihbayar`                        | create_lebihbayar       | Add        | Form lebih bayar                      |
| 9   | `TambahInvoice()`          | `/penerimaan/TambahInvoice/{customer}`          | invoice                 | View       | Modal list invoice per customer       |
| 10  | `TambahLebihBayar()`       | `/penerimaan/TambahLebihBayar/{customer}`       | lebihbayar              | View       | Modal list lebih bayar                |
| 11  | `penerimaan_buktipotong()` | `/penerimaan/penerimaan_buktipotong/{kd_bayar}` | form_buktipotong        | Manage     | Modal form bukti potong               |

### 3.2 AJAX Endpoints (POST - Return JSON)

| #   | Method                        | URL                                     | Input                                        | Output                | Deskripsi                 |
| --- | ----------------------------- | --------------------------------------- | -------------------------------------------- | --------------------- | ------------------------- |
| 1   | `server_side_payment()`       | `/penerimaan/server_side_payment`       | DataTable params                             | JSON DataTable        | List payment server-side  |
| 2   | `server_side_inv()`           | `/penerimaan/server_side_inv`           | DataTable params                             | JSON DataTable        | List invoice siap terima  |
| 3   | `get_invoice_serverside()`    | `/penerimaan/get_invoice_serverside`    | id_customer, search, pagination              | JSON DataTable        | List invoice per customer |
| 4   | `get_invoice_cn_serverside()` | `/penerimaan/get_invoice_cn_serverside` | id_customer, filter_type, search, pagination | JSON DataTable        | List invoice + CN (UNION) |
| 5   | `save_penerimaan()`           | `/penerimaan/save_penerimaan`           | Form data (serialized)                       | `{status:1/2, pesan}` | Simpan penerimaan         |
| 6   | `save_unlocated()`            | `/penerimaan/save_unlocated`            | Form data                                    | `{status:1/2, pesan}` | Simpan deposit            |
| 7   | `save_lebihbayar()`           | `/penerimaan/save_lebihbayar`           | Form data                                    | `{status:1/2, pesan}` | Simpan lebih bayar        |
| 8   | `save_buktipotong()`          | `/penerimaan/save_buktipotong`          | Form data                                    | `{status:1/2, pesan}` | Simpan bukti potong       |

### 3.3 Action Endpoints (GET - Execute & Redirect/Output)

| #   | Method                   | URL                                  | Output          | Permission | Deskripsi                   |
| --- | ------------------------ | ------------------------------------ | --------------- | ---------- | --------------------------- |
| 1   | `appr_jurnal()`          | `/penerimaan/appr_jurnal/{kd_bayar}` | PDF (via print) | Manage     | Approval jurnal + cetak BUM |
| 2   | `print_penerimaan_fix()` | Internal (called by appr_jurnal)     | PDF stream      | Manage     | Generate & output PDF BUM   |

---

## 4. Data Flow & State Machine

### 4.1 Status Lifecycle Penerimaan

```
┌─────────────┐     save_penerimaan()     ┌─────────────────┐     appr_jurnal()      ┌──────────────┐
│   (belum    │──────────────────────────▶│  status_jurnal  │──────────────────────▶ │ status_jurnal│
│    ada)     │                            │      = 0        │                         │     = 1      │
│             │                            │  (Saved, belum  │                         │  (Dijurnal,  │
│             │                            │   dijurnal)     │                         │   BUM cetak) │
└─────────────┘                            └─────────────────┘                         └──────────────┘
```

### 4.2 Data Mutations pada Save Penerimaan

```
save_penerimaan() triggers:
│
├── INSERT tr_invoice_payment (1 row - header)
│
├── LOOP per detail row:
│   ├── IF type = 'invoice':
│   │   ├── INSERT tr_invoice_payment_detail
│   │   ├── UPDATE tr_invoice SET total_bayar_idr += X, sisa_invoice_idr -= X
│   │   └── UPDATE tr_spk_marketing SET total_bayar_so += X
│   │
│   └── IF type = 'cn':
│       ├── INSERT tr_invoice_payment_detail (total_bayar_idr = negatif)
│       └── INSERT tr_cn_cross (amount_crossed = abs(X))
│
├── IF tambah_lebih_bayar > 0:
│   └── INSERT tr_unlocated_bank (saldo = tambah_lebih_bayar)
│
└── IF id_unlocated != '':
    └── UPDATE tr_unlocated_bank SET saldo -= X WHERE id = {id_unlocated}
```

### 4.3 Data Mutations pada Approval Jurnal

```
appr_jurnal() triggers:
│
├── INSERT gl_metalsindo_live.jarh (1 row - header jurnal)
│
├── INSERT gl_metalsindo_live.jurnal (N rows - detail debit/kredit)
│   ├── Debit: {kd_bank} = jumlah_bank_idr
│   ├── Debit: 7205-01-01 = biaya_admin_idr (if > 0)
│   ├── Debit: 2109-02-01 = lebih_bayar (if > 0)
│   ├── Debit: {jenis_pph} = biaya_pph (if > 0, per detail)
│   └── Kredit: 1102-01-01 = total_bayar_idr (per detail invoice)
│
├── UPDATE gl_metalsindo_live.ar SET kredit += X, saldo_akhir -= X
│
├── UPDATE gl_metalsindo_live.pastibisa_tb_cabang SET nobum += 1
│
├── INSERT tr_kartu_piutang (per detail invoice)
│
└── UPDATE tr_invoice_payment SET status_jurnal = '1'
```

---

## 5. Client-Side Architecture (JavaScript)

### 5.1 Key Functions in `create_penerimaan_new.php`

| Function                                      | Trigger                              | Deskripsi                                          |
| --------------------------------------------- | ------------------------------------ | -------------------------------------------------- |
| `savemutasi()`                                | Onclick "Simpan"                     | Validasi client-side → konfirmasi → AJAX POST save |
| `cekall()`                                    | Onchange any financial field         | Recalculate: Selisih, Kontrol, Total Terima        |
| `validateCnCrossing()`                        | Called by cekall() & savemutasi()    | Validate CN rows: 0 < amount <= balance            |
| `startmutasi(id, surat, nm, avl, real, type)` | Onclick "Pilih" di modal             | Add row ke detail table (invoice atau CN)          |
| `deleterow(tr, id)`                           | Onclick "Hapus" per row              | Remove row & recalculate                           |
| `sumchangebayar()`                            | After add/remove/change row          | Recalculate Total Bayar Invoice                    |
| `startunlocated(id, value)`                   | Onclick "Pilih" di modal deposit     | Set total_bank & id_unlocated                      |
| `startlebihbayar(id, value)`                  | Onclick "Pilih" di modal lebih bayar | Set pakai_lebih_bayar & id_lebihbayar              |
| `number_format(n, dec, dec_point, sep)`       | Utility                              | Format angka dengan separator ribuan               |
| `getNum(val)`                                 | Utility                              | Parse string to float (handle NaN)                 |

### 5.2 Calculation Logic (cekall)

```javascript
function cekall() {
  validateCnCrossing(); // Validate CN rows first

  var total_bank = getNum($("#total_bank").val().replace(/,/g, ""));
  var total_invoice = getNum($("#total_invoice").val().replace(/,/g, ""));
  var selisih = total_bank - total_invoice;

  $("#selisih").val(number_format(selisih));

  var biaya_adm = getNum($("#biaya_adm").val().replace(/,/g, ""));
  var biaya_pph = getNum($("#biaya_pph").val().replace(/,/g, ""));
  var tambah_lebih_bayar = getNum(
    $("#tambah_lebih_bayar").val().replace(/,/g, ""),
  );

  var control = selisih + biaya_adm + biaya_pph - tambah_lebih_bayar;
  var total_terima = total_invoice - biaya_adm - biaya_pph + tambah_lebih_bayar;

  $("#control").val(number_format(control));
  $("#total_terima").val(number_format(total_terima));
}
```

### 5.3 CN Crossing Validation (Client)

```javascript
function validateCnCrossing() {
  // For each CN row in detail table:
  //   1. Get absolute value of jml_bayar
  //   2. Get absolute value of sisa_invoice (= CN Balance)
  //   3. If jml_bayar <= 0 → border red, invalid
  //   4. If jml_bayar > sisa_invoice → border red, invalid
  //   5. Otherwise → border green, valid
  // Returns: true (all valid) / false (any invalid)
}
```

### 5.4 Event Bindings

| Event    | Selector                    | Action                                                 |
| -------- | --------------------------- | ------------------------------------------------------ |
| `change` | `#bank`                     | Show/hide "Deposit" button (if COA = 2101-07-01)       |
| `click`  | `.add` (Add Invoice tab)    | Validate customer → open modal `TambahInvoice`         |
| `click`  | `.createunlocated`          | Open modal form unlocated                              |
| `click`  | `#lebihbayar`               | Show pakai_lebih_bayar → open modal `TambahLebihBayar` |
| `click`  | `#incomplete` (Deposit btn) | Open modal unlocated list                              |
| `keyup`  | `.sum_change_bayar`         | Recalculate total_invoice                              |
| `change` | `#customer`                 | Update hidden id_customer                              |
| `click`  | `#simpanpenerimaan`         | Trigger savemutasi()                                   |

---

## 6. Server-Side Validation Detail

### 6.1 Kontrol Validation

```php
// Extract values (strip commas)
$val_total_bank = floatval(str_replace(",", "", $this->input->post('total_bank')));
$val_total_invoice = floatval(str_replace(",", "", $this->input->post('total_invoice')));
$val_biaya_adm = floatval(str_replace(",", "", $this->input->post('biaya_adm')));
$val_biaya_pph = floatval(str_replace(",", "", $this->input->post('biaya_pph')));
$val_tambah_lebih_bayar = floatval(str_replace(",", "", $this->input->post('tambah_lebih_bayar')));

$val_selisih = $val_total_bank - $val_total_invoice;
$val_kontrol = $val_selisih + $val_biaya_adm + $val_biaya_pph - $val_tambah_lebih_bayar;

if (round($val_kontrol, 2) != 0) → REJECT "Kontrol harus 0!"
```

### 6.2 CN Crossing Validation

```php
// For each row where type[] == 'cn':
$crossing_amount = abs(floatval(str_replace(",", "", $jml_bayar[$v])));

// Rule 1: Must be > 0
if ($crossing_amount <= 0) → REJECT "Nilai crossing harus lebih dari 0!"

// Rule 2: Calculate CN Balance
$cn_total = SELECT COALESCE(SUM(total_harga), 0) FROM dt_returpenjualan WHERE id_retur = ?
$cn_crossed = SELECT COALESCE(SUM(amount_crossed), 0) FROM tr_cn_cross WHERE id_retur = ?
$cn_balance = $cn_total - $cn_crossed;

// Rule 3: Must not exceed balance
if ($crossing_amount > round($cn_balance, 2)) → REJECT "Nilai crossing melebihi sisa CN!"
```

---

## 7. Jurnal Mapping (COA Detail)

### 7.1 Normal Penerimaan (BUM)

| Posisi | COA         | Nama                          | Debit             | Kredit                  | Kondisi            |
| ------ | ----------- | ----------------------------- | ----------------- | ----------------------- | ------------------ |
| D      | {kd_bank}   | Bank tujuan (sesuai pilihan)  | jumlah_bank_idr   | -                       | Always             |
| D      | 7205-01-01  | Biaya Administrasi Bank       | biaya_admin_idr   | -                       | If biaya_admin > 0 |
| D      | 2109-02-01  | Deposit Customer (pakai)      | lebih_bayar       | -                       | If lebih_bayar > 0 |
| D      | {jenis_pph} | PPH (sesuai pilihan, per inv) | total_pph per inv | -                       | If biaya_pph > 0   |
| K      | 1102-01-01  | Piutang Dagang                | -                 | total_bayar_idr per inv | Per detail invoice |

### 7.2 Lebih Bayar (Tambahan jika tambah_lebih_bayar > 0)

| Posisi | COA        | Nama                    | Debit              | Kredit             |
| ------ | ---------- | ----------------------- | ------------------ | ------------------ |
| D      | {kd_bank}  | Bank                    | tambah_lebih_bayar | -                  |
| K      | 2109-02-01 | Hutang Deposit Customer | -                  | tambah_lebih_bayar |

### 7.3 Unlocated/Deposit (save_unlocated)

| Posisi | COA        | Nama                           | Debit           | Kredit          |
| ------ | ---------- | ------------------------------ | --------------- | --------------- |
| D      | {kd_bank}  | Bank                           | totalpenerimaan | -               |
| K      | 2101-08-01 | Deposit Customer (Liabilities) | -               | totalpenerimaan |

### 7.4 COA Reference

| COA               | Nama                         | Tipe               |
| ----------------- | ---------------------------- | ------------------ |
| 1101-02-10        | BANK MANDIRI GIRO (IDR)      | Asset - Bank       |
| 1101-02-11        | BANK MANDIRI TABUNGAN        | Asset - Bank       |
| 1102-01-01        | Piutang Dagang               | Asset - Receivable |
| 1102-01-02 s/d 09 | OCBC/BCA/Permata/Maybank     | Asset - Bank       |
| 1103-01-01        | Piutang (Kartu Piutang)      | Asset - Receivable |
| 2101-07-01        | Deposit Customer (Bank)      | Liability          |
| 2101-08-01        | Deposit Customer (Unlocated) | Liability          |
| 2109-02-01        | Hutang Deposit Customer      | Liability          |
| 7205-01-01        | Biaya Administrasi Bank      | Expense            |

---

## 8. Error Handling

### 8.1 Client-Side

| Kondisi                | Handling                        | UI                                       |
| ---------------------- | ------------------------------- | ---------------------------------------- |
| Tgl Bayar kosong       | SweetAlert warning              | Timer 3s, auto-close                     |
| Bank kosong            | SweetAlert warning              | Timer 3s                                 |
| Kontrol != 0           | SweetAlert warning              | "Kontrol harus 0!"                       |
| CN amount invalid      | SweetAlert warning + border red | Specific message                         |
| AJAX error             | SweetAlert error                | "Batal Proses, Data bisa diproses nanti" |
| Save success           | SweetAlert success              | Timer 15s, redirect                      |
| Save failed (status=2) | SweetAlert warning              | Show server message                      |

### 8.2 Server-Side

| Kondisi               | Response                                                 | HTTP |
| --------------------- | -------------------------------------------------------- | ---- |
| Kontrol != 0          | `{status:2, pesan:"Kontrol harus 0!"}`                   | 200  |
| CN amount <= 0        | `{status:2, pesan:"Nilai crossing harus lebih dari 0!"}` | 200  |
| CN amount > balance   | `{status:2, pesan:"Nilai crossing melebihi sisa CN!"}`   | 200  |
| DB trans_status FALSE | `{status:2, pesan:"Save Process Failed..."}`             | 200  |
| Success               | `{status:1, pesan:"Save Process Success."}`              | 200  |

---

## 9. Security Considerations

| Aspek                 | Implementasi                               | Status                       |
| --------------------- | ------------------------------------------ | ---------------------------- |
| Authentication        | Session-based (app_session)                | ✅ Implemented               |
| Authorization         | Permission per method (`auth->restrict()`) | ✅ Implemented               |
| CSRF                  | CI default CSRF (if enabled in config)     | ⚠️ Depends on config         |
| SQL Injection         | `escape_like_str()` di search              | ⚠️ Partial (some raw concat) |
| XSS                   | CI default output encoding                 | ⚠️ Partial                   |
| Input Validation      | Server-side for Kontrol & CN               | ✅ Critical paths            |
| Audit Trail           | created_by, created_on                     | ✅ Implemented               |
| Transaction Integrity | trans_status check                         | ⚠️ No explicit trans_start() |
