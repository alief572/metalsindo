# PRD — Modul Master Customers

> Product Requirements Document
> Modul: `application/modules/master_customers`
> Terakhir diperbarui: 2026-09-09

## 1. Ringkasan

Modul **Master Customers** digunakan untuk mengelola data master pelanggan (customer) lokal beserta data pendukungnya: kategori customer, PIC (Person In Charge), informasi pembayaran (bank & pajak), jadwal & alamat penerimaan invoice, serta persyaratan dokumen pembayaran.

Modul ini menjadi sumber data customer yang dipakai modul lain (mis. invoicing, penjualan).

## 2. Stakeholder

| Peran | Kepentingan |
|-------|-------------|
| Admin / Staff Marketing | Input & pemeliharaan data customer |
| Divisi Keuangan | Informasi bank, pajak, dan persyaratan pembayaran customer |
| Sales / Marketing | Relasi customer dengan karyawan (marketing) penanggung jawab |

## 3. Fitur Utama

### 3.1 Manajemen Customer (Local Customer)
- **List Customer** — menampilkan daftar customer aktif (belum dihapus / `deleted = 0`) dengan kolom: Nama Customer, Sales (nama karyawan), Status (Aktif/Non-Aktif).
- **Tambah Customer** — form lengkap identitas + kategori (multi) + PIC (multi) + info pembayaran + info invoice + persyaratan pembayaran.
- **Edit Customer** — mengubah data customer beserta relasinya.
- **Hapus Customer** — soft delete (`deleted = 1`).
- **View Customer** — melihat detail data.

### 3.2 Manajemen Kategori Customer (Master Customer Category)
- **List Kategori** — daftar kategori (`activation = active`).
- **Tambah / Edit Kategori** — nama kategori + kode customer.
- **Hapus Kategori** — soft delete via `activation = inactive`.

## 4. Alur Bisnis

### 4.1 Tambah Customer
1. User buka tab **Local Customer** → klik **Tambah Data**.
2. Form dimuat via AJAX ke modal.
3. User mengisi:
   - Identitas: nama, telepon, fax, email, tanggal mulai, marketing, provinsi/kota, alamat, kode pos, koordinat, status, facility.
   - Kategori Customer: satu atau lebih (baris dinamis).
   - PIC: satu atau lebih (nama, telepon, email, jabatan).
   - Informasi Pembayaran: data bank & data pajak (NPWP/PKP), term of payment.
   - Informasi Invoice: hari terima, jam terima, alamat invoice.
   - Persyaratan Pembayaran: checklist dokumen (Berita Acara, Faktur, TDP, dll).
4. Submit → `saveNewcustomer` → generate `id_customer` (format `MC` + YY + 5 digit) → insert `master_customers`, `child_customer_pic`, `child_category_customer` dalam satu transaksi.

### 4.2 Edit Customer
1. Klik edit → data di-load & pre-fill ke form.
2. Submit → `saveEditcustomer` → update header + hapus & re-insert child (PIC & kategori).

### 4.3 Kategori Customer
- Tambah → `saveNewCategory` (generate `id_category_customer` format `CC` + YY + 5 digit).
- Edit → `saveEditCategory`.
- Hapus → `deleteCategory` (set `activation = inactive`).

## 5. Aturan & Perhitungan

- **Generate ID Customer**: `MC` + 2 digit tahun + counter 5 digit (`MAX(id_customer)` + 1).
- **Generate ID Kategori**: `CC` + 2 digit tahun + counter 5 digit.
- **Soft delete**: customer & PIC/kategori memakai flag/relasi; kategori memakai `activation`.
- **Checkbox Y/N**: field hari (senin–minggu) dan persyaratan (berita_acara, faktur, dll) disimpan sebagai enum `Y`/`N`; jika checkbox tidak dicentang → `N`.

## 6. Hak Akses (Permission)

| Permission | Fungsi |
|------------|--------|
| `Master_customers.View` | Melihat list & detail |
| `Master_customers.Add` | Menambah data |
| `Master_customers.Manage` | Mengubah data |
| `Master_customers.Delete` | Menghapus data |

## 7. Di Luar Cakupan (Saat Ini)

- Customer international (di-handle modul `master_suplier`).
- Modul inventory (`view_inventory`) yang tercampur di controller ini bukan bagian inti customer.

## 8. Catatan Non-Fungsional (UI/UX)

- Basis tampilan: AdminLTE + Bootstrap 3.
- Komponen tersedia: DataTables, Select2, SweetAlert (v1 & v2), iCheck.
- Form add/edit ditata sebagai **wizard multi-step** untuk mengurangi beban scroll dan mempercepat input (lihat DESIGN.md).
