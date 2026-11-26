-- 001_receive_invoice_dp_incoming.down.sql
-- Membatalkan 001. HANYA jalankan sebelum ada data baru yang memakai kolom / tabel ini
-- (DROP COLUMN dan DROP TABLE membuang data yang ditulis oleh kode baru).
-- Cadangkan tr_invoice_po dan tr_invoice_po_detail terlebih dahulu.

DROP TABLE IF EXISTS `tr_invoice_po_detail`;

ALTER TABLE `tr_invoice_po`
  DROP INDEX `idx_invoice_tipe`,
  DROP INDEX `idx_invoice_po`,
  DROP COLUMN `modified_date`,
  DROP COLUMN `modified_by`,
  DROP COLUMN `total_invoice_idr`,
  DROP COLUMN `nilai_dpp`,
  DROP COLUMN `potongan_dp`,
  DROP COLUMN `nilai_dasar`,
  DROP COLUMN `tipe_invoice`;
