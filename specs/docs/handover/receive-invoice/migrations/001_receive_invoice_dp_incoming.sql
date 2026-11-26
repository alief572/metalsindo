-- 001_receive_invoice_dp_incoming.sql
-- Modul   : purchase_order_payment (Receive Invoice)
-- Tujuan  : Receive Invoice hanya tipe DP dan Incoming, dengan DPP dan PPn (DPP = Nilai x 11/12, PPn = DPP x 12%).
-- Status  : DRAF. Jangan dijalankan tanpa review. Target: metalsindo_live (lokal). Tabel dasar (tr_invoice_po, tr_top_po, dll.) belum ada di sana dan dibuat programmer; skema acuan di sini dari hfg_dev (HFG), lihat README.
-- Catatan : DDL di MySQL commit otomatis (tidak bisa di-rollback); karena itu tidak ada START TRANSACTION. MySQL 8.0.30. Tidak idempotent (tidak ada ADD COLUMN IF NOT EXISTS); jalankan sekali.
--           Jalankan pemeriksaan pra-syarat di 03-data-dan-migrasi.md bagian 4 terlebih dahulu.

-- 1. Header invoice: kolom baru (semua NULL / default aman agar baris lama tetap valid)
ALTER TABLE `tr_invoice_po`
  ADD COLUMN `tipe_invoice`      VARCHAR(20)   NULL DEFAULT NULL COMMENT 'dp | incoming | legacy (Progress/Retensi/lama)' AFTER `id`,
  ADD COLUMN `nilai_dasar`       DECIMAL(20,2) NULL DEFAULT NULL COMMENT 'Nilai DP atau Incoming sebelum PPN' AFTER `total_pembelian`,
  ADD COLUMN `potongan_dp`       DECIMAL(20,2) NOT NULL DEFAULT 0.00 COMMENT 'Potongan DP proporsional (tipe incoming)' AFTER `nilai_dasar`,
  ADD COLUMN `nilai_dpp`         DECIMAL(20,2) NULL DEFAULT NULL COMMENT 'DPP = Nilai x 11/12' AFTER `potongan_dp`,
  ADD COLUMN `total_invoice_idr` DECIMAL(20,2) NULL DEFAULT NULL COMMENT 'Nilai Invoice x kurs' AFTER `total_invoice`,
  ADD COLUMN `modified_by`       VARCHAR(100)  NULL DEFAULT NULL AFTER `created_date`,
  ADD COLUMN `modified_date`     DATETIME      NULL DEFAULT NULL AFTER `modified_by`,
  ADD INDEX `idx_invoice_tipe` (`tipe_invoice`),
  ADD INDEX `idx_invoice_po` (`no_po`);

-- 2. Rincian invoice per PO / per dokumen incoming
CREATE TABLE `tr_invoice_po_detail` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_invoice`    VARCHAR(100) NOT NULL COMMENT 'tr_invoice_po.id',
  `no_po`         VARCHAR(100) NOT NULL COMMENT 'no_surat PO (sama dengan tr_invoice_po.no_po)',
  `id_top`        INT NULL DEFAULT NULL COMMENT 'tr_top_po.id, hanya tipe dp',
  `no_incoming`   VARCHAR(100) NULL DEFAULT NULL COMMENT 'kode_trans incoming, hanya tipe incoming',
  `nilai_dasar`   DECIMAL(20,2) NOT NULL DEFAULT 0.00 COMMENT 'sebelum PPN',
  `potongan_dp`   DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `nilai`         DECIMAL(20,2) NOT NULL DEFAULT 0.00 COMMENT 'nilai_dasar - potongan_dp; dipakai untuk batas total per PO',
  `nilai_dpp`     DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `nilai_ppn`     DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `nilai_invoice` DECIMAL(20,2) NOT NULL DEFAULT 0.00 COMMENT 'nilai_dpp + nilai_ppn',
  `created_by`    VARCHAR(100) NULL DEFAULT NULL,
  `created_date`  DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_detail_invoice` (`id_invoice`),
  KEY `idx_detail_po` (`no_po`),
  KEY `idx_detail_incoming` (`no_incoming`),
  KEY `idx_detail_top` (`id_top`)
) ENGINE=InnoDB -- charset: ikuti tr_invoice_po (samakan collation kolom varchar yang di-join)
  COMMENT='Rincian Receive Invoice (DP / Incoming) per PO atau incoming';

-- 3. Klasifikasi baris lama: DP bila id_top menunjuk TOP group 76 (uang muka); selainnya legacy.
--    Nilai dasar / DPP / total_invoice_idr baris lama sengaja dibiarkan NULL (tidak dihitung ulang dengan rumus baru).
UPDATE `tr_invoice_po` i
LEFT JOIN `tr_top_po` t ON t.`id` = i.`id_top`
SET i.`tipe_invoice` = CASE WHEN t.`group_top` = '76' THEN 'dp' ELSE 'legacy' END
WHERE i.`tipe_invoice` IS NULL;

