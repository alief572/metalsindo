# DESIGN — Modul Master Customers

> Dokumen Desain Teknis
> Modul: `application/modules/master_customers`
> Terakhir diperbarui: 2026-09-09

## 1. Arsitektur

- Framework: CodeIgniter (HMVC / modular).
- UI: AdminLTE + Bootstrap 3.
- Plugin tersedia: DataTables, Select2, SweetAlert (v1 & v2), iCheck.
- Pola: Controller `Master_customers` → Model `Customer_model` → Views (list + form via AJAX ke modal).

### Struktur File
```
application/modules/master_customers/
├── controllers/Master_customers.php
├── models/Customer_model.php
└── views/
    ├── index.php            # list customer + kategori (2 tab)
    ├── add_customer.php      # form tambah customer
    ├── edit_customer.php     # form edit customer
    ├── add_category.php      # form tambah kategori
    ├── edit_category.php     # form edit kategori
    └── (view_*, *_suplier, *_international — sebagian milik modul lain)
```

## 2. Endpoint (Controller Method)

| Method | Fungsi | Dipanggil dari |
|--------|--------|----------------|
| `index()` | Halaman list | menu |
| `addCustomer()` | Render form tambah (AJAX) | tombol Tambah |
| `editCustomer($id)` | Render form edit (AJAX) | tombol Edit |
| `saveNewcustomer()` | Simpan customer baru | submit add |
| `saveEditcustomer()` | Simpan perubahan customer | submit edit |
| `addCategory()` / `EditCategory($id)` | Render form kategori | tombol |
| `saveNewCategory()` / `saveEditCategory()` | Simpan kategori | submit |
| `deletelokal()` | Soft delete customer | tombol hapus |
| `deleteCategory()` | Soft delete kategori | tombol hapus |
| `getkota()` | Dependent dropdown kota | onchange provinsi |

> **PENTING (kontrak yang dijaga saat upgrade UI):** nama atribut `name` pada input, struktur array `data1[...]` (PIC) & `data2[...]` (kategori), dan URL AJAX di atas TIDAK diubah agar backend tetap kompatibel.

## 3. Desain UI/UX Baru (Upgrade)

### 3.1 Halaman Index
- Box/panel AdminLTE modern dengan toolbar konsisten.
- Badge status memakai `label`/`badge` yang lebih halus.
- Aksi berupa btn-group ikon (View/Edit/Delete) dengan tooltip.
- DataTables: setiap tabel memakai **id unik** (bukan `example1` dobel) agar init benar di dua tab.

### 3.2 Form Customer — Wizard 4 Step
Form panjang dipecah menjadi wizard berbasis nav Bootstrap 3 + JS ringan (tanpa plugin baru):

| Step | Judul | Isi field |
|------|-------|-----------|
| 1 | Identitas | id_customer, name_customer, telephone, telephone_2, fax, email, start_date, id_karyawan, id_prov, id_kota, address_office, zip_code, longitude, latitude, activation, facility |
| 2 | Kategori & PIC | tabel dinamis kategori (`data2[]`) & PIC (`data1[]`) |
| 3 | Pembayaran & Pajak | name_bank, no_rekening, nama_rekening, alamat_bank, swift_code, npwp, npwp_name, npwp_address, payment_term, nominal_dp, sisa_pembayaran |
| 4 | Invoice & Persyaratan | hari terima (senin–minggu), start_recive, end_recive, address_invoice, checklist persyaratan |

Navigasi: tombol **Sebelumnya / Berikutnya** + tombol **Simpan** hanya muncul di step terakhir. Progress indicator di atas.

### 3.3 Validasi Client-Side
- Validasi per step sebelum lanjut (field inti: name_customer, telephone, email, id_prov, id_kota — final disepakati saat implementasi).
- Feedback inline (highlight `has-error` + pesan), tidak lagi mengandalkan atribut `required` bawaan yang berlebihan.

### 3.4 Konsistensi Komponen
- Select2 untuk semua dropdown.
- iCheck untuk checkbox/radio.
- CSS scoped modul agar tidak bocor ke modul lain.

## 4. State Machine (Status)

```
Customer.activation:  aktif  <──> inaktif
Customer.deleted:     0 (aktif) ──> 1 (terhapus, soft delete)
Category.activation:  active <──> inactive (inactive = terhapus)
```

## 5. Daftar Bug / Utang Teknis (DITUNDA — tidak diperbaiki pada fase upgrade UI ini)

> Dicatat sesuai kesepakatan. Perlu penanganan di fase berikutnya.

| # | Lokasi | Deskripsi | Dampak | Rekomendasi |
|---|--------|-----------|--------|-------------|
| B1 | `index.php` (JS `view_local`, `view_category`, `edit_international`, dll) | Handler tombol pada tab customer memanggil controller `master_suplier/...` (mis. `view_local` → `master_suplier/viewLokal`) | View/detail customer kemungkinan tidak jalan / salah data | Arahkan ke method di `master_customers` |
| B2 | `index.php` | Dua tabel memakai `id="example1"` yang sama | DataTables hanya init satu; tab kedua tidak optimal | Pakai id unik per tabel (akan diperbaiki di Task 2 sebagai bagian UI) |
| B3 | Banyak view (`add/edit_customer`) | Field non-esensial diberi `required` (fax, longitude, latitude, swift_code, npwp, nominal_dp, dll) | Gagal simpan tanpa alasan jelas | Tinjau ulang field wajib; ganti dengan validasi terarah |
| B4 | `add_customer.php`, dll | `json_encode($results['supplier'])` padahal controller tidak mengirim key `supplier` | Notice/undefined index | Hapus / sesuaikan variabel |
| B5 | `index.php` (list customer) | Badge status membandingkan `activation == 'aktif'`, konsisten dengan enum, OK; namun list kategori pakai `active` — perlu dipastikan konsisten | Rendah | Verifikasi mapping status |
| B6 | `edit_customer.php` | Value email di pre-fill memakai `$cus->name_customer` (bukan `$cus->email`) | Email salah ter-load saat edit | Perbaiki binding value email |
| B7 | Controller `saveEditcustomer` | Update juga menimpa `created_on`/`created_by` saat edit | Data audit "created" hilang | Pisahkan audit created vs modified |
| B8 | `Customer_model` | `getCustomer()` tidak memfilter, namun `$table_name` model menunjuk `ms_inventory_category1` (copy-paste) | Membingungkan, berpotensi bug jika method BF_Model dipakai | Rapikan properti model |

## 6. Status Implementasi (Upgrade UI/UX — 2026-09-09)

| Area | Status | Catatan |
|------|--------|---------|
| `index.php` | ✅ Selesai | Box modern, badge status pill+ikon, aksi btn-group+tooltip, DataTables id unik `tbl_customer` & `tbl_category` (fix B2), bahasa Indonesia, `columns.adjust()` saat tab dibuka |
| `add_customer.php` | ✅ Selesai | Wizard 4-step + validasi client-side (`mc-required`, `mc-email`); `json_encode(supplier)` dihapus (B4); `required` berlebihan dihapus (B3) |
| `edit_customer.php` | ✅ Selesai | Wizard 4-step + pre-fill lengkap (value/checked/selected); endpoint `saveEditcustomer`; B4 dihapus; B6 dipertahankan (komentar) |
| `add_category.php` | ✅ Selesai | Panel modern + validasi ringan; typo `id_categorry_customer` dipertahankan (kontrak backend) |
| `edit_category.php` | ✅ Selesai | Panel modern + validasi ringan; permission `master_suplier.*` dipertahankan (bug ditunda, komentar) |

### Komponen
- **Select2**: dipakai konsisten pada seluruh dropdown form customer (class `.select`).
- **iCheck**: TIDAK dipaksakan pada checkbox/radio untuk menghindari regresi (bergantung load global); dipertimbangkan untuk fase berikut.
- **CSS**: scoped per-view (prefix `.mc-form` / `.mc-cat-form` / `.mc-wrap`) agar tidak bocor ke modul lain.

## 7. Catatan Verifikasi

- Tidak ada pipeline build/test otomatis (PHP + XAMPP). PHP CLI: 5.6.40.
- **Lint sintaks**: `php -l` pada 5 view (index, add/edit customer, add/edit category) → **No syntax errors**.
- **Kelengkapan payload**: verifikasi otomatis 48 field `name` inti pada `add_customer.php` & `edit_customer.php` → **ALL present**; `id_customer` (edit) + struktur `data1[]`/`data2[]` tetap. Payload POST tidak berubah → backend tetap kompatibel.
- **Smoke test manual (disarankan)**: buka list (kedua tab), tambah customer via wizard, edit customer (cek pre-fill per step), tambah/edit kategori, hapus. Bandingkan Network tab payload dengan ekspektasi.
