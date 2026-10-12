-- Schema minimal untuk eksperimen Go (DB: material_planning_dev).
-- Di DB asli metalsindo_dev, view_material_planning adalah VIEW.
-- Di sini dibuat sebagai TABLE agar bisa jalan lokal tanpa dump penuh.
CREATE DATABASE IF NOT EXISTS material_planning_dev CHARACTER SET utf8;
USE material_planning_dev;

CREATE TABLE IF NOT EXISTS view_material_planning (
  id_dt_spkmarketing VARCHAR(50) PRIMARY KEY,
  no_spk VARCHAR(100),
  customer VARCHAR(150),
  id_material VARCHAR(100),
  no_alloy VARCHAR(100),
  thickness DOUBLE DEFAULT 0,
  width DOUBLE DEFAULT 0,
  length_val DOUBLE DEFAULT 0,
  delivery_date VARCHAR(20),
  total_weight DOUBLE DEFAULT 0,
  total_sheet DOUBLE DEFAULT 0,
  fg_booking DOUBLE DEFAULT 0,
  status_lanjutan VARCHAR(5) DEFAULT '1',
  KEY idx_spk (no_spk),
  KEY idx_cust (customer),
  KEY idx_mat (id_material)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS dt_spkmarketing (
  id_dt_spkmarketing VARCHAR(50) PRIMARY KEY,
  id_spkmarketing VARCHAR(50),
  id_material VARCHAR(100),
  width DOUBLE DEFAULT 0,
  total_weight DOUBLE DEFAULT 0,
  status_lanjutan VARCHAR(5) DEFAULT '1',
  status_close_planning VARCHAR(10) DEFAULT 'OPN',
  approved VARCHAR(5) DEFAULT '0',
  deal VARCHAR(5) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS tr_spk_marketing (
  id_spkmarketing VARCHAR(50) PRIMARY KEY,
  no_surat VARCHAR(100),
  id_customer VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS master_customers (
  id_customer VARCHAR(50) PRIMARY KEY,
  name_customer VARCHAR(150)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS stock_material (
  id_stock VARCHAR(50) PRIMARY KEY,
  id_category3 VARCHAR(100),
  lotno VARCHAR(100),
  width DOUBLE DEFAULT 0,
  qty DOUBLE DEFAULT 0,
  weight DOUBLE DEFAULT 0,
  totalweight DOUBLE DEFAULT 0,
  booking DOUBLE DEFAULT 0,
  delivery_book DOUBLE DEFAULT 0,
  sisa_spk DOUBLE DEFAULT 0,
  qty_sheet DOUBLE DEFAULT 0,
  customer VARCHAR(150),
  no_surat VARCHAR(100),
  id_gudang VARCHAR(10),
  id_dt_spkmarketing VARCHAR(50),
  KEY idx_cat (id_category3),
  KEY idx_gudang (id_gudang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS dt_trans_pr (
  id INT AUTO_INCREMENT PRIMARY KEY,
  idmaterial VARCHAR(100),
  totalweight DOUBLE DEFAULT 0,
  KEY idx_mat (idmaterial)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS dt_incoming (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_material VARCHAR(100),
  width_recive DOUBLE DEFAULT 0,
  KEY idx_mat (id_material)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS stock_material_customer (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_stock VARCHAR(50),
  id_customer VARCHAR(50),
  berat DOUBLE DEFAULT 0,
  width DOUBLE DEFAULT 0,
  qty DOUBLE DEFAULT 0,
  weight DOUBLE DEFAULT 0,
  id_dt_spkmarketing VARCHAR(50),
  keterangan VARCHAR(255),
  created_by VARCHAR(50),
  created_date DATETIME,
  sts_over VARCHAR(5) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
