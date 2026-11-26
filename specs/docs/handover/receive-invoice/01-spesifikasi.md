# 01 — Spesifikasi Receive Invoice (DP / Incoming)

Perilaku di bawah ini sudah berjalan di mockup (`specs/ui-mockup/assets/proc_fin.js`, bagian "RECEIVE INVOICE"). Bila dokumen dan mockup berbeda, mockup yang dijadikan acuan lalu perbaiki dokumen ini.

## 1. Tipe invoice

| Tipe | Dasar tagihan | Sumber baris |
|---|---|---|
| **DP** | Persen DP pada TOP PO × nilai PO (sebelum PPN) | Satu baris TOP DP per PO |
| **Incoming** | Nilai aktual barang yang sudah diterima (qty diterima × harga PO setelah diskon, ditambah biaya kirim proporsional) | Satu atau lebih dokumen incoming; boleh digabung dalam satu invoice bila supplier, mata uang, dan kurs sama |

Tidak ada tipe Progress dan Retensi.

## 2. TOP pada PO

- Form PO hanya punya isian **DP (%)** dan **Keterangan DP**. Boleh kosong (tanpa DP). Nilai 0 sampai 100.
- Sisa persen (100 − DP) **tidak disimpan sebagai baris**. Ia otomatis dianggap tagihan Incoming.
- Tampilan detail PO: baris DP (bila ada) dan baris Incoming otomatis dengan sisa persen.
- Validasi: DP > 100 ditolak; persen harus > 0 bila diisi.
- Nilai yang ditampilkan per baris TOP (form, detail, approval, print) adalah nilai **sebelum PPN** = round(persen × Nilai PO sebelum PPN / 100), sama dengan Nilai DP di Receive Invoice. Total PO termasuk PPN tetap ditampilkan sebagai Total Order.

## 3. Rumus perhitungan

Semua pembulatan ke Rupiah (mata uang asing: 2 desimal) di **setiap langkah**, pembulatan biasa (setengah naik).

```
Nilai Dasar      = nilai DP atau nilai Incoming, SEBELUM PPN
Potongan DP      = Nilai Dasar Incoming × persen DP / 100        (hanya tipe Incoming; DP = 0)
Nilai            = Nilai Dasar − Potongan DP
DPP              = round(Nilai × 11 / 12)
PPn              = round(DPP × 12 / 100)
Nilai Invoice    = DPP + PPn
```

- Nilai DP = round(persen DP × Nilai PO sebelum PPN / 100).
- Nilai PO sebelum PPN = Σ (qty × harga × (1 − disc)) + biaya kirim. PPN dari item PO tidak ikut.
- Incoming Non Material: biaya kirim dibagi proporsional terhadap subtotal bersih.
- Satu invoice Incoming yang memuat beberapa dokumen: setiap baris dihitung sendiri-sendiri, total invoice = jumlah Nilai Invoice per baris (bukan dihitung ulang dari total).
- Mata uang asing: nilai dalam mata uang PO; **nilai Rupiah = Nilai Invoice × kurs**.

> Seluruh rumus pajak harus berada di **satu fungsi** (mis. di `Pr_model` atau library baru) supaya dapat diganti bila bagian pajak mengubah rumus. Hitung ulang di server; jangan percaya angka yang dikirim form.

## 4. Batas total per PO

`Σ Nilai (DP + semua Incoming) per PO ≤ Nilai PO sebelum PPN` (toleransi Rp 1, atau 0,01 untuk mata uang asing). Bila terlampaui, simpan ditolak dengan pesan "Total invoice PO ini akan melebihi nilai PO". Perbandingan memakai kolom **Nilai** (bukan Nilai Invoice).

## 5. Aturan per tipe

**DP**
- Hanya muncul untuk PO yang TOP-nya punya DP. Satu PO hanya boleh satu invoice DP.
- Tombol Receive Invoice hilang setelah invoice DP dibuat.

**Incoming**
- Hanya incoming yang belum di-invoice-kan (`inc_inv_create` kosong). Incoming milik PO tanpa sisa persen Incoming tidak muncul.
- Beberapa incoming boleh digabung hanya bila supplier, mata uang, dan kurs sama; bila tidak, tolak dengan pesan "Incoming yang dipilih harus dari supplier yang sama" (atau mata uang / kurs).
- Potongan DP tetap dihitung proporsional walaupun invoice DP belum dibuat; tampilkan peringatan bila DP PO itu belum di-invoice.

## 6. Field form (urutan mengikuti aplikasi asli)

Nama Supplier, Currency (readonly), Kurs (readonly), Receive Invoice Date *, Nomor Invoice *, Invoice Date *, Nomor Faktur Pajak, Tanggal Faktur Pajak, Upload Invoice, Tanggal Pembayaran * (Fase 2; default = hari ini + TOP supplier dalam hari), Notes, Informasi Bank (Bank, No. Bank, Nama Acc Bank). Tampilan hitungan (readonly): Total PO / Total Incoming sebelum PPN, Potongan DP, Nilai, DPP, PPn, Nilai Invoice (+ Nilai Rupiah bila mata uang asing).

Tampilan daftar: radio **Receive Invoice DP / Receive Invoice Incoming / Daftar Invoice**. Panel DP dan Daftar punya filter Supplier + tombol Cari dan kolom: No, No. PO, No. Purchase Invoice, No. Invoice, No. Payment, Nama Supplier, Tanggal PO, Keterangan, Status, Action. Panel Incoming: checkbox, tombol Clear Checked Invoice dan Receive Invoice, kolom Nominal Incoming, Potongan DP, Nilai Invoice.

## 7. Status invoice (diturunkan dari Request Payment, Fase 2)

Invoice tidak menyimpan status sendiri.

| Status | Kondisi Request Payment |
|---|---|
| Waiting Checker | belum di-approve Checker |
| Waiting Management | Checker approve, Management belum |
| Menunggu Pembayaran | Management approve, belum ada Payment |
| Sudah Dibayar | sudah punya Payment |
| Request Ditolak | ditolak Checker atau Management |

- **Edit / hapus invoice**: boleh selama Waiting Checker atau setelah ditolak. Setelah Checker approve, terkunci.
- Edit saat Waiting Checker memperbarui Request Payment yang sama (nomor invoice, tanggal pembayaran). Hapus invoice menarik (menghapus) Request Payment-nya dan membebaskan incoming / DP agar dapat di-invoice lagi.
- Invoice ditolak → "Perbaiki & Kirim Ulang": simpan perbaikan membuat Request Payment **baru**; request lama ditandai pengganti (`superseded_by`) dan tidak dihitung pada dashboard.

## 8. Nomor PR (Fase 1b)

- Nomor PR Departemen (`PRN` + yymm + 4 digit) dibuat **saat PR dibuat**, bukan saat approval Management. Approval tiga level tidak mengubah nomor. PR yang ditolak lalu diedit tetap memakai nomor yang sama.
- Metode Pembelian, PO, dan daftar lain memakai nomor yang sama.

## 9. Layar mockup ↔ modul aplikasi

| Layar mockup | File mockup | Modul / fungsi aplikasi asli |
|---|---|---|
| Receive Invoice | `purchase_order_payment.html` | `purchase_order_payment` — `index`, `checkbx`, `search_dp`, `search_inc`/`check_list_inc`, `req_app`, `req_inc_app`, `save_invoice`, `view`, `view_inc` |
| PO Non Material (form TOP) | `purchase_order_non_material.html` | `purchase_order_non_material` — form tambah/edit PO, simpan `tr_top_po` |
| PR Departemen | `non_rutin_index.html` | `non_rutin` — `add` (simpan dan approve) |
| Request Payment Checker/Management | `request_payment_list_approve_*.html` | `request_payment` (Fase 2) |
| Dashboard Pembayaran | `request_payment_index.html` | `request_payment/index` (Fase 2) |
| Payment List | `pembayaran_material_payment_list.html` | `request_payment` payment list / `pembayaran_material` |
