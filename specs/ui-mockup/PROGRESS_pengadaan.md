# Mockup Pengadaan Non Material sampai Pembayaran (SELESAI)

## Ketentuan dari user
- Mockup cukup basic: HTML, CSS, JavaScript biasa. Tanpa framework, library baru, atau build tool.
- Semua file di `specs/ui-mockup/` (HTML di root, JS/CSS di `assets/`).
- Fungsi dan link antar modul dipertahankan (bar "Dari / Lanjut ke", badge hitungan, data lintas halaman lewat localStorage).

## Hasil
- 22 halaman + `flow_pengadaan.html` (peta alur). Revisi 2026-11-24: Receive Invoice disatukan (lihat bagian bawah); `receive_invoice_ap` dan `purchase_order_index_purchase` dihapus. Nama file = `<modul>_<halaman>.html`, mis. `non_rutin_approval_head.html`.
- Data: `assets/seed_proc.js` (`Mock.seedR`). Logika: `proc.js` (inti), `proc_pr.js`, `proc_po.js`, `proc_fin.js`.
- Edit file lama: `mock.js` (menu + persistSave seedR), `mock.css` (flowbar/steps/print), `guide.js` (fallback panduan), `index.html` (kartu).
- Menu "Reset data mockup" mengembalikan data awal.

## Pemetaan 23 modul permintaan
| # | Permintaan | File |
|---|---|---|
| 1 | metode_pembelian/pr | metode_pembelian_pr |
| 2 | non_rutin/index | non_rutin_index |
| 3 | purchase_order_non_material | purchase_order_non_material |
| 4 | purchase_order/index_purchase | (digabung) tipe DP di purchase_order_payment |
| 5 | purchase_order_payment | purchase_order_payment (modul Receive Invoice tunggal) |
| 6 | incoming_departemen | incoming_departemen |
| 7 | incoming_asset/asset | incoming_asset_asset |
| 8 | receive_invoice_ap | (digabung) tipe Incoming di purchase_order_payment |
| 9 | asset_planning/index_asset | asset_planning_index_asset |
| 10 | index_asset/approve | asset_planning_index_asset_approve (asumsi) |
| 11 | approval_po_non_material | approval_po_non_material |
| 12-13 | request_payment checker / management | request_payment_list_approve_checker / _management |
| 14-16 | non_rutin approval head / cost control / management | non_rutin_approval_* |
| 17-19 | pr_asset approval | pr_asset_approval_* |
| 20-22 | app_pr_stock approval | app_pr_stock_approval_* |
| 23 | pembayaran_material/payment_list | pembayaran_material_payment_list |
Tambahan di luar daftar: `pr_asset_pr` (buat PR dari planning) dan `flow_pengadaan`.

## Pengujian (Chrome headless, puppeteer-core di folder temp, bukan di proyek)
- Semua 25 halaman termuat tanpa error JS.
- Alur ujung ke ujung lulus: PR Departemen -> 3 approval -> Metode Pembelian -> PO -> Approval PO -> Incoming -> Receive Invoice AP -> Request Payment -> Checker -> Management -> Payment; plus reject PR Asset, planning -> PR Asset, invoice DP, Request UM PO Material, reload, print, panduan.

## Catatan / batasan
- "index_asset/approve" ditafsirkan sebagai `asset_planning/index_asset/approve`.
- Hanya supplier lokal (IDR) pada PO Non Material; NON-PO pada Incoming tidak dibuat.
- Jurnal akuntansi tidak dimockup. Potongan DP pada invoice incoming mengikuti field `value_dp` di kode asli.
- Aturan `module-documentation` (PRD/DESIGN/ERD di `/md/`) tidak dijalankan: tidak ada kode modul aplikasi yang diubah.

## Revisi 2026-11-24: Receive Invoice satu modul (`purchase_order_payment`)
Keputusan user: modul Receive Invoice hanya satu, menarik semua PO Approved (Non Material dan Material), tiga tipe.
- **DP**: persen DP di TOP x total PO. **Progress**: persen Progress di TOP x total PO (boleh beberapa baris Progress). **Incoming**: nilai aktual diterima (qty diterima x harga PO + PPN + biaya kirim proporsional).
- **Retensi dihapus dari TOP** (PO2611003 sekarang DP 30 / Progress 60 / Progress 10).
- **Potongan DP proporsional**: invoice Incoming dipotong nilai incoming x persen DP (dan x persen Progress), bukan dihabiskan dulu. Alasan user: jarak waktu incoming berikutnya bisa lama.
- Satu tabel data `R.invoice` (menggantikan `recInv` dan `invoicePO`), `lines[]` per PO: gross, pot_dp, pot_prog, net. Total invoice per PO tidak boleh melebihi total PO (`P.poLedger`).
- Lima tab: Ringkasan PO, Receive Invoice DP, Incoming, Progress, Daftar Invoice. Incoming boleh gabung beberapa incoming dari supplier dan mata uang yang sama (lintas PO).
- Request Payment tipe tunggal `po` (tipe `purchase_invoice` dan `uangmuka` dihapus). Request ditolak -> invoice berstatus Request Ditolak, dapat diedit dan Ajukan Ulang (request baru, yang lama jadi riwayat).
- PO Material: TOP diturunkan di kode (`MAT_TOP` di `proc_fin.js`: P2600997 DP 30, P2601004 DP 20 / Progress 30; lainnya Incoming 100). Incoming material = satu dokumen virtual `RCV-<no PO>` dari qty diterima (dibatasi qty PO). PO Material belum punya tabel TOP/incoming sendiri.
- Belum dikerjakan: edit nilai/rincian invoice (hanya header; ganti rincian = hapus lalu buat ulang), incoming material per surat jalan, status dibayar balik ke PO Non Material selain lewat status invoice.
- Diuji Chrome headless (2026-11-24): 23 halaman proc termuat tanpa error; alur ajukan ulang request ditolak, invoice Incoming proporsional (TRS2611001: 1,065,600 - 319,680 = 745,920), request payment terbentuk.

## Revisi 2026-11-24: Request Payment otomatis dan Dashboard Pembayaran
Keputusan user setelah membaca kode asli `application/modules/request_payment` (hanya dibaca, tidak diubah):
- Checker dan Management masih dipakai. Jalur invoice PO tetap: Receive Invoice -> Request Payment Checker -> Management -> Payment List.
- **Receive Invoice otomatis membuat Request Payment** saat disimpan. Tanggal Pembayaran wajib (default hari ini + TOP supplier). Invoice boleh diedit/dihapus selama Waiting Checker (request ikut diperbarui/ditarik); setelah Checker approve terkunci. Bila request ditolak, invoice diperbaiki dan menyimpan otomatis mengirim request baru (yang lama diberi `superseded`). Tombol Request manual dan status Belum diajukan dihapus dari alur.
- **Request Payment non-PO** (Kasbon, Expense, Transport, Periodik, PR Cash, Direct Payment) terbentuk otomatis saat dokumennya Approved di modul asal (modul asal di luar mockup, jadi datanya langsung berupa request di `R.reqPay`; tipe `cash` ditambahkan dan tab Cash di Checker/Management). Checker/Management sama dengan jalur PO (satu pasang halaman, tab per jalur). Request non-PO yang ditolak punya tombol Simulasi perbaikan (pembuat memperbaiki di modul asal, request baru terbentuk).
- **`request_payment/index` menjadi `Dashboard Pembayaran (Finance)`**, baca-saja (`request_payment_index.html`): kartu jumlah dan nominal per status (Waiting Checker, Waiting Management, Menunggu Pembayaran, Sudah Dibayar, Ditolak) plus Lewat Tempo dan Jatuh Tempo <= 7 hari, matriks kategori x status, daftar yang bisa disaring dengan klik kartu. Tidak ada Proses Pembayaran/Reject (di kode asli keduanya melompati Checker dan Management).
- Bug lama `mock.js` (teks "Showing x to y" menimpa badge berkelas `info`) diperbaiki.
- Diuji Chrome headless: 24 halaman proc termuat tanpa error; 24 skenario lulus (request otomatis, default tanggal, edit saat Waiting Checker, terkunci setelah Checker, hapus menarik request, perbaiki dan kirim ulang, simulasi perbaikan non-PO, kartu dan filter dashboard).
- Catatan untuk aplikasi nyata: dashboard harus membaca `request_payment` (non-PO) dan `purchase_order_request_payment` / `_nm` (PO) sekaligus; temuan bug kode asli (copy_to_payment tipe `transport` vs `transportasi`, Cash tanpa cabang, JSON dalam loop di reject_req_payment, tr_added_req_payment global) ada di percakapan 2026-11-24.

## Revisi 2026-11-24: Tampilan Receive Invoice ala aplikasi, TOP DP/Progress, nomor PR tetap

- **Receive Invoice (`purchase_order_payment`)**: tampilan mengikuti `index_incoming.php` asli. Judul "Receive Invoice", teks "Select Request Payment", radio DP / Incoming / Progress (ditambah "Daftar Invoice" untuk edit, hapus, dan perbaiki request yang ditolak). Panel DP dan Progress: filter Supplier + tombol Cari, kolom No, No. PO, No. Purchase Invoice, No. Invoice, No. Payment, Nama Supplier, Tanggal PO, Keterangan, Status, Action. Panel Incoming: checkbox, tombol Clear Checked Invoice dan Receive Invoice. Form modal memakai urutan field aplikasi: Nama Supplier, Currency, Kurs, Receive Invoice Date, Nomor Invoice, Invoice Date, Faktur Pajak, Upload, Notes, Informasi Bank (Bank, No. Bank, Nama Acc Bank), plus Total Incoming / Potongan / Total Invoice. Tab "Ringkasan PO" dihapus; detail PO tersedia lewat tombol 📄.
- **TOP PO**: hanya pilihan DP atau Progress. Total boleh di bawah 100% (bahkan kosong); sisanya otomatis menjadi tagihan Incoming (`P.normTop` menambah baris Incoming otomatis). Total DP + Progress tidak boleh lebih dari 100%. Receive Invoice membaca TOP lewat `P.normTop`.
- **Nomor PR tetap**: PR Departemen mendapat nomor PRN saat dibuat (No Pengajuan PLN dihapus; `no_pengajuan` = `no_pr`), PR Stock sudah bernomor PRS sejak awal. Approval Management tidak lagi mengubah nomor, sehingga Metode Pembelian mudah dicari. PR Asset sudah memakai satu nomor.
- Uji headless: t4.js (Receive Invoice) 12/12, t5.js (nomor PR + TOP) 10/10, 26 halaman proc tanpa error. t3.js lama memakai indeks tab dan sudah usang.

## Revisi 2026-11-24: DPP dan PPn pada Receive Invoice

- Aturan (keputusan user): **DPP = Nilai x 11/12; PPn = DPP x 12%; Nilai Invoice = DPP + PPn**. Nilai = nilai DP / Incoming / Progress **sebelum PPN** (Incoming sudah dikurangi potongan DP/Progress proporsional). Diterapkan persis seperti ditulis, sehingga Nilai Invoice = Nilai x 1,0267 (bukan Nilai + PPn 11%); user sudah memilih opsi ini.
- Seluruh perhitungan DP/Progress/potongan/batas total per PO sekarang memakai nilai sebelum PPN (total PO sebelum PPN = subtotal setelah diskon + biaya kirim). Total PO termasuk PPN 11% tetap ditampilkan di detail PO.
- Form dan tabel menampilkan Nilai, DPP, PPn, Nilai Invoice. Request Payment memakai Nilai Invoice. Data contoh lama dihitung ulang otomatis (`P.retax`).
- Uji: t6.js 9/9; 26 halaman proc tanpa error.

## Revisi 2026-11-26: Receive Invoice hanya DP dan Incoming (Progress dihapus)

- Keputusan user: Receive Invoice hanya **berdasarkan DP** atau **berdasarkan Incoming**. Tipe Progress dihapus total.
- TOP di PO: hanya satu isian **DP (%)** (opsional, 0 sampai 100) plus keterangan; sisa persen otomatis ditagih lewat Incoming. Group "Progress" tidak ada lagi di form PO.
- Receive Invoice: radio DP / Incoming / Daftar Invoice. Potongan Progress dan kolom Progress dihapus dari rincian, posisi tagihan, dan filter. Data contoh PO2611003 dan PO Material P2601004 disesuaikan (tanpa Progress).
- Uji: t7.js 10/10, t6.js (DPP/PPn) 9/9, 26 halaman proc tanpa error.

## Revisi 2026-11-26: Nilai TOP di layar PO sebelum PPN

- Keputusan user: samakan layar PO dengan Receive Invoice. Nilai per baris TOP (form PO, detail PO, approval) sekarang **sebelum PPN**: round(persen x (Sub Total - Discount + Biaya Kirim)). Contoh PO 6,500,000 + PPN 715,000, DP 30% = 1,950,000 (sebelumnya 2,164,500). Kolom berlabel "Value (sebelum PPN)".
- Total Order tetap termasuk PPN. Case 1 pada FAT: semua 18 test case Passed.
