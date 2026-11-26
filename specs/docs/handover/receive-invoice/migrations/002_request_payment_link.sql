-- 002_request_payment_link.sql
-- Fase 2: Request Payment otomatis dari Receive Invoice (jalur PO) dengan Checker / Management.
-- Status  : DRAF TUNDA. Jangan dijalankan sebelum butir terbuka 5 di README dijawab
--           (tabel request_payment dipakai untuk jalur PO, atau tabel lain).
-- Target  : metalsindo_live (lokal); tabel dasar dibuat programmer. Tidak idempotent. Jalankan setelah 001.
-- request_payment.tipe adalah varchar(20): nilai baru 'invoice_po' tidak perlu perubahan skema.
-- PERLU DISESUAIKAN: draf ini mengikuti bentuk request_payment versi HFG. Di metalsindo_live tabel itu
-- status smallint (0/1/2), hanya app_checker*, TANPA app_management* / no_surat / id_supplier, charset latin1.
-- Fase 2 harus menambah kolom/status Management bila request_payment dipakai (lihat README butir terbuka 5).
-- Kolom varchar di bawah tidak menyebut charset agar mengikuti tabel tujuan.

ALTER TABLE `request_payment`
  ADD COLUMN `id_invoice_po`     VARCHAR(100) NULL DEFAULT NULL COMMENT 'tr_invoice_po.id (tipe invoice_po)',
  ADD COLUMN `tgl_rencana_bayar` DATE NULL DEFAULT NULL COMMENT 'Tanggal pembayaran yang diminta (wajib untuk invoice_po)',
  ADD COLUMN `reject_reason`     TEXT NULL,
  ADD COLUMN `reject_by`         VARCHAR(100) NULL DEFAULT NULL,
  ADD COLUMN `reject_on`         DATETIME NULL DEFAULT NULL,
  ADD COLUMN `superseded_by`     VARCHAR(50) NULL DEFAULT NULL COMMENT 'no_doc request pengganti setelah reject diperbaiki; baris ini tidak dihitung di dashboard',
  ADD INDEX `idx_reqpay_invoice` (`id_invoice_po`);

ALTER TABLE `tr_invoice_po`
  ADD COLUMN `no_request_payment` VARCHAR(50) NULL DEFAULT NULL COMMENT 'request_payment.no_doc yang aktif untuk invoice ini' AFTER `nm_acc_bank`;

