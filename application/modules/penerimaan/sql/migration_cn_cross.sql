-- Migration: Cross Credit Note Penerimaan
-- Requirements: 8.1, 8.2, 8.3, 8.4, 8.5
-- Description: Add no_cn column to tr_retur_penjualan and create tr_cn_cross table

-- Add no_cn column to tr_retur_penjualan
ALTER TABLE tr_retur_penjualan
ADD COLUMN no_cn VARCHAR(50) DEFAULT NULL AFTER no_retur;

-- Create tr_cn_cross table
CREATE TABLE tr_cn_cross (
    id_cn_cross INT AUTO_INCREMENT PRIMARY KEY,
    id_retur VARCHAR(20) NOT NULL,
    no_cn VARCHAR(50) NOT NULL,
    kd_pembayaran VARCHAR(50) NOT NULL,
    amount_crossed DECIMAL(15,2) NOT NULL,
    tgl_cross DATE NOT NULL,
    created_by INT NOT NULL,
    created_on DATETIME NOT NULL,
    INDEX idx_id_retur (id_retur),
    INDEX idx_kd_pembayaran (kd_pembayaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
