-- 002_request_payment_link.down.sql
-- Membatalkan 002. Hanya sebelum ada data baru yang memakai kolom ini. Cadangkan request_payment dan tr_invoice_po terlebih dahulu.

ALTER TABLE `tr_invoice_po`
  DROP COLUMN `no_request_payment`;

ALTER TABLE `request_payment`
  DROP INDEX `idx_reqpay_invoice`,
  DROP COLUMN `superseded_by`,
  DROP COLUMN `reject_on`,
  DROP COLUMN `reject_by`,
  DROP COLUMN `reject_reason`,
  DROP COLUMN `tgl_rencana_bayar`,
  DROP COLUMN `id_invoice_po`;
