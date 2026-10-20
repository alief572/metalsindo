-- MySQL 5.7 / MariaDB. Default: read-only preflight.
-- Select the database first. Review preflight output and existing indexes before apply.
-- Apply: SET @riap_mode = 'apply'; then run this file again.
-- Rollback: SET @riap_mode = 'rollback'; then run this file again.
-- Expected database defaults to metalsindo_dev. For an independently approved
-- deployment elsewhere, explicitly set @riap_expected_database to that schema.
-- ALTER TABLE is not transactional. Backup index definitions from preflight output.
-- Rollback removes only idx_riap_* names below, never differently named indexes.
SET @riap_mode = COALESCE(@riap_mode, 'preflight');
SET @riap_expected_database = COALESCE(@riap_expected_database, 'metalsindo_dev');
SELECT @@hostname AS server_host, DATABASE() AS selected_database,
       VERSION() AS server_version, @riap_mode AS mode,
       @riap_expected_database AS expected_database;
SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME, SUB_PART
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('tr_receive_invoice_ap_header','tr_receive_invoice_ap_detail',
    'tr_incoming','dt_incoming','dt_trans_po','tr_purchase_order')
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

-- tr_receive_invoice_ap_header: (created_date,id_rec_inv_ap)
SET @riap_equivalent = (
  SELECT COUNT(*) FROM (
    SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_used,
      SUM(SUB_PART IS NOT NULL) AS prefix_parts
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_header'
      AND INDEX_TYPE = 'BTREE'
    GROUP BY INDEX_NAME
  ) existing_indexes
  WHERE prefix_parts = 0 AND (columns_used = 'created_date,id_rec_inv_ap' OR LEFT(columns_used, 27) = 'created_date,id_rec_inv_ap,')
);
SET @riap_named = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_header' AND INDEX_NAME = 'idx_riap_header_created'
);
SET @riap_engine = (
  SELECT ENGINE FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_header'
);
SET @riap_sql = CASE
  WHEN DATABASE() <> @riap_expected_database OR DATABASE() IS NULL
    THEN 'SELECT ''SKIP: expected database does not match'' AS result'
  WHEN @riap_mode = 'rollback' AND @riap_named > 0
    THEN 'ALTER TABLE `tr_receive_invoice_ap_header` DROP INDEX `idx_riap_header_created`, ALGORITHM=INPLACE, LOCK=NONE'
  WHEN @riap_mode = 'apply' AND @riap_equivalent = 0 AND @riap_named = 0 AND @riap_engine = 'InnoDB'
    THEN 'ALTER TABLE `tr_receive_invoice_ap_header` ADD INDEX `idx_riap_header_created` (`created_date`, `id_rec_inv_ap`), ALGORITHM=INPLACE, LOCK=NONE'
  ELSE 'SELECT ''tr_receive_invoice_ap_header.idx_riap_header_created: preflight, already indexed, missing table, unsupported engine, or nothing to rollback'' AS result'
END;
SELECT 'tr_receive_invoice_ap_header' AS table_name, 'idx_riap_header_created' AS proposed_index,
  'created_date,id_rec_inv_ap' AS columns_needed, @riap_engine AS engine,
  @riap_equivalent AS equivalent_indexes, @riap_named AS named_index_rows;
PREPARE riap_statement FROM @riap_sql;
EXECUTE riap_statement;
DEALLOCATE PREPARE riap_statement;

-- tr_receive_invoice_ap_header: (id_rec_inv_ap)
SET @riap_equivalent = (
  SELECT COUNT(*) FROM (
    SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_used,
      SUM(SUB_PART IS NOT NULL) AS prefix_parts
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_header'
      AND INDEX_TYPE = 'BTREE'
    GROUP BY INDEX_NAME
  ) existing_indexes
  WHERE prefix_parts = 0 AND (columns_used = 'id_rec_inv_ap' OR LEFT(columns_used, 14) = 'id_rec_inv_ap,')
);
SET @riap_named = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_header' AND INDEX_NAME = 'idx_riap_header_id'
);
SET @riap_engine = (
  SELECT ENGINE FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_header'
);
SET @riap_sql = CASE
  WHEN DATABASE() <> @riap_expected_database OR DATABASE() IS NULL
    THEN 'SELECT ''SKIP: expected database does not match'' AS result'
  WHEN @riap_mode = 'rollback' AND @riap_named > 0
    THEN 'ALTER TABLE `tr_receive_invoice_ap_header` DROP INDEX `idx_riap_header_id`, ALGORITHM=INPLACE, LOCK=NONE'
  WHEN @riap_mode = 'apply' AND @riap_equivalent = 0 AND @riap_named = 0 AND @riap_engine = 'InnoDB'
    THEN 'ALTER TABLE `tr_receive_invoice_ap_header` ADD INDEX `idx_riap_header_id` (`id_rec_inv_ap`), ALGORITHM=INPLACE, LOCK=NONE'
  ELSE 'SELECT ''tr_receive_invoice_ap_header.idx_riap_header_id: preflight, already indexed, missing table, unsupported engine, or nothing to rollback'' AS result'
END;
SELECT 'tr_receive_invoice_ap_header' AS table_name, 'idx_riap_header_id' AS proposed_index,
  'id_rec_inv_ap' AS columns_needed, @riap_engine AS engine,
  @riap_equivalent AS equivalent_indexes, @riap_named AS named_index_rows;
PREPARE riap_statement FROM @riap_sql;
EXECUTE riap_statement;
DEALLOCATE PREPARE riap_statement;

-- tr_receive_invoice_ap_detail: (id_rec_inv_ap)
SET @riap_equivalent = (
  SELECT COUNT(*) FROM (
    SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_used,
      SUM(SUB_PART IS NOT NULL) AS prefix_parts
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_detail'
      AND INDEX_TYPE = 'BTREE'
    GROUP BY INDEX_NAME
  ) existing_indexes
  WHERE prefix_parts = 0 AND (columns_used = 'id_rec_inv_ap' OR LEFT(columns_used, 14) = 'id_rec_inv_ap,')
);
SET @riap_named = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_detail' AND INDEX_NAME = 'idx_riap_detail_invoice'
);
SET @riap_engine = (
  SELECT ENGINE FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_detail'
);
SET @riap_sql = CASE
  WHEN DATABASE() <> @riap_expected_database OR DATABASE() IS NULL
    THEN 'SELECT ''SKIP: expected database does not match'' AS result'
  WHEN @riap_mode = 'rollback' AND @riap_named > 0
    THEN 'ALTER TABLE `tr_receive_invoice_ap_detail` DROP INDEX `idx_riap_detail_invoice`, ALGORITHM=INPLACE, LOCK=NONE'
  WHEN @riap_mode = 'apply' AND @riap_equivalent = 0 AND @riap_named = 0 AND @riap_engine = 'InnoDB'
    THEN 'ALTER TABLE `tr_receive_invoice_ap_detail` ADD INDEX `idx_riap_detail_invoice` (`id_rec_inv_ap`), ALGORITHM=INPLACE, LOCK=NONE'
  ELSE 'SELECT ''tr_receive_invoice_ap_detail.idx_riap_detail_invoice: preflight, already indexed, missing table, unsupported engine, or nothing to rollback'' AS result'
END;
SELECT 'tr_receive_invoice_ap_detail' AS table_name, 'idx_riap_detail_invoice' AS proposed_index,
  'id_rec_inv_ap' AS columns_needed, @riap_engine AS engine,
  @riap_equivalent AS equivalent_indexes, @riap_named AS named_index_rows;
PREPARE riap_statement FROM @riap_sql;
EXECUTE riap_statement;
DEALLOCATE PREPARE riap_statement;

-- tr_incoming: (id_incoming)
SET @riap_equivalent = (
  SELECT COUNT(*) FROM (
    SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_used,
      SUM(SUB_PART IS NOT NULL) AS prefix_parts
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_incoming'
      AND INDEX_TYPE = 'BTREE'
    GROUP BY INDEX_NAME
  ) existing_indexes
  WHERE prefix_parts = 0 AND (columns_used = 'id_incoming' OR LEFT(columns_used, 12) = 'id_incoming,')
);
SET @riap_named = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_incoming' AND INDEX_NAME = 'idx_riap_incoming_header'
);
SET @riap_engine = (
  SELECT ENGINE FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_incoming'
);
SET @riap_sql = CASE
  WHEN DATABASE() <> @riap_expected_database OR DATABASE() IS NULL
    THEN 'SELECT ''SKIP: expected database does not match'' AS result'
  WHEN @riap_mode = 'rollback' AND @riap_named > 0
    THEN 'ALTER TABLE `tr_incoming` DROP INDEX `idx_riap_incoming_header`, ALGORITHM=INPLACE, LOCK=NONE'
  WHEN @riap_mode = 'apply' AND @riap_equivalent = 0 AND @riap_named = 0 AND @riap_engine = 'InnoDB'
    THEN 'ALTER TABLE `tr_incoming` ADD INDEX `idx_riap_incoming_header` (`id_incoming`), ALGORITHM=INPLACE, LOCK=NONE'
  ELSE 'SELECT ''tr_incoming.idx_riap_incoming_header: preflight, already indexed, missing table, unsupported engine, or nothing to rollback'' AS result'
END;
SELECT 'tr_incoming' AS table_name, 'idx_riap_incoming_header' AS proposed_index,
  'id_incoming' AS columns_needed, @riap_engine AS engine,
  @riap_equivalent AS equivalent_indexes, @riap_named AS named_index_rows;
PREPARE riap_statement FROM @riap_sql;
EXECUTE riap_statement;
DEALLOCATE PREPARE riap_statement;

-- dt_incoming: (id_incoming)
SET @riap_equivalent = (
  SELECT COUNT(*) FROM (
    SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_used,
      SUM(SUB_PART IS NOT NULL) AS prefix_parts
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dt_incoming'
      AND INDEX_TYPE = 'BTREE'
    GROUP BY INDEX_NAME
  ) existing_indexes
  WHERE prefix_parts = 0 AND (columns_used = 'id_incoming' OR LEFT(columns_used, 12) = 'id_incoming,')
);
SET @riap_named = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dt_incoming' AND INDEX_NAME = 'idx_riap_incoming_detail'
);
SET @riap_engine = (
  SELECT ENGINE FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dt_incoming'
);
SET @riap_sql = CASE
  WHEN DATABASE() <> @riap_expected_database OR DATABASE() IS NULL
    THEN 'SELECT ''SKIP: expected database does not match'' AS result'
  WHEN @riap_mode = 'rollback' AND @riap_named > 0
    THEN 'ALTER TABLE `dt_incoming` DROP INDEX `idx_riap_incoming_detail`, ALGORITHM=INPLACE, LOCK=NONE'
  WHEN @riap_mode = 'apply' AND @riap_equivalent = 0 AND @riap_named = 0 AND @riap_engine = 'InnoDB'
    THEN 'ALTER TABLE `dt_incoming` ADD INDEX `idx_riap_incoming_detail` (`id_incoming`), ALGORITHM=INPLACE, LOCK=NONE'
  ELSE 'SELECT ''dt_incoming.idx_riap_incoming_detail: preflight, already indexed, missing table, unsupported engine, or nothing to rollback'' AS result'
END;
SELECT 'dt_incoming' AS table_name, 'idx_riap_incoming_detail' AS proposed_index,
  'id_incoming' AS columns_needed, @riap_engine AS engine,
  @riap_equivalent AS equivalent_indexes, @riap_named AS named_index_rows;
PREPARE riap_statement FROM @riap_sql;
EXECUTE riap_statement;
DEALLOCATE PREPARE riap_statement;

-- dt_trans_po: (id_dt_po,idmaterial)
SET @riap_equivalent = (
  SELECT COUNT(*) FROM (
    SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_used,
      SUM(SUB_PART IS NOT NULL) AS prefix_parts
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dt_trans_po'
      AND INDEX_TYPE = 'BTREE'
    GROUP BY INDEX_NAME
  ) existing_indexes
  WHERE prefix_parts = 0 AND (columns_used = 'id_dt_po,idmaterial' OR LEFT(columns_used, 20) = 'id_dt_po,idmaterial,')
);
SET @riap_named = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dt_trans_po' AND INDEX_NAME = 'idx_riap_po_detail'
);
SET @riap_engine = (
  SELECT ENGINE FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dt_trans_po'
);
SET @riap_sql = CASE
  WHEN DATABASE() <> @riap_expected_database OR DATABASE() IS NULL
    THEN 'SELECT ''SKIP: expected database does not match'' AS result'
  WHEN @riap_mode = 'rollback' AND @riap_named > 0
    THEN 'ALTER TABLE `dt_trans_po` DROP INDEX `idx_riap_po_detail`, ALGORITHM=INPLACE, LOCK=NONE'
  WHEN @riap_mode = 'apply' AND @riap_equivalent = 0 AND @riap_named = 0 AND @riap_engine = 'InnoDB'
    THEN 'ALTER TABLE `dt_trans_po` ADD INDEX `idx_riap_po_detail` (`id_dt_po`, `idmaterial`), ALGORITHM=INPLACE, LOCK=NONE'
  ELSE 'SELECT ''dt_trans_po.idx_riap_po_detail: preflight, already indexed, missing table, unsupported engine, or nothing to rollback'' AS result'
END;
SELECT 'dt_trans_po' AS table_name, 'idx_riap_po_detail' AS proposed_index,
  'id_dt_po,idmaterial' AS columns_needed, @riap_engine AS engine,
  @riap_equivalent AS equivalent_indexes, @riap_named AS named_index_rows;
PREPARE riap_statement FROM @riap_sql;
EXECUTE riap_statement;
DEALLOCATE PREPARE riap_statement;

-- tr_purchase_order: (no_po)
SET @riap_equivalent = (
  SELECT COUNT(*) FROM (
    SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_used,
      SUM(SUB_PART IS NOT NULL) AS prefix_parts
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_purchase_order'
      AND INDEX_TYPE = 'BTREE'
    GROUP BY INDEX_NAME
  ) existing_indexes
  WHERE prefix_parts = 0 AND (columns_used = 'no_po' OR LEFT(columns_used, 6) = 'no_po,')
);
SET @riap_named = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_purchase_order' AND INDEX_NAME = 'idx_riap_po_header'
);
SET @riap_engine = (
  SELECT ENGINE FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_purchase_order'
);
SET @riap_sql = CASE
  WHEN DATABASE() <> @riap_expected_database OR DATABASE() IS NULL
    THEN 'SELECT ''SKIP: expected database does not match'' AS result'
  WHEN @riap_mode = 'rollback' AND @riap_named > 0
    THEN 'ALTER TABLE `tr_purchase_order` DROP INDEX `idx_riap_po_header`, ALGORITHM=INPLACE, LOCK=NONE'
  WHEN @riap_mode = 'apply' AND @riap_equivalent = 0 AND @riap_named = 0 AND @riap_engine = 'InnoDB'
    THEN 'ALTER TABLE `tr_purchase_order` ADD INDEX `idx_riap_po_header` (`no_po`), ALGORITHM=INPLACE, LOCK=NONE'
  ELSE 'SELECT ''tr_purchase_order.idx_riap_po_header: preflight, already indexed, missing table, unsupported engine, or nothing to rollback'' AS result'
END;
SELECT 'tr_purchase_order' AS table_name, 'idx_riap_po_header' AS proposed_index,
  'no_po' AS columns_needed, @riap_engine AS engine,
  @riap_equivalent AS equivalent_indexes, @riap_named AS named_index_rows;
PREPARE riap_statement FROM @riap_sql;
EXECUTE riap_statement;
DEALLOCATE PREPARE riap_statement;
