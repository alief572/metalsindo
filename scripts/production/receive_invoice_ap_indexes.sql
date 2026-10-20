-- Receive Invoice AP - production index migration (MySQL 5.7).
-- Run the WHOLE file in a single connection, not only the current statement.
-- First run: leave mode = 'preflight' and save all result sets as the audit snapshot.
-- To apply: change ONLY the next SET value to 'apply', then run the whole file again.
-- To rollback: change it to 'rollback'. Only remove migration indexes absent in your snapshot.
-- No transaction rows or table collations are changed. DDL commits implicitly.
-- Online DDL still needs metadata locks. On timeout, stop and inspect active queries.
-- Do not automatically kill sessions on production.
SET @riap_prod_mode = 'preflight';

USE `metalsindo_live`;
SET @riap_prod_saved_lock_timeout = @@SESSION.lock_wait_timeout;
SET SESSION lock_wait_timeout = 10;

SELECT @@hostname AS server_host, @@port AS server_port, VERSION() AS server_version,
       DATABASE() AS target_database, @riap_prod_mode AS migration_mode;

-- Save these definitions/results before apply. They are the rollback reference.
SHOW CREATE TABLE `tr_receive_invoice_ap_header`;
SHOW CREATE TABLE `tr_receive_invoice_ap_detail`;
SHOW CREATE TABLE `tr_incoming`;
SHOW CREATE TABLE `dt_incoming`;
SHOW CREATE TABLE `dt_trans_po`;
SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME, SUB_PART
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (
  'tr_receive_invoice_ap_header', 'tr_receive_invoice_ap_detail',
  'tr_incoming', 'dt_incoming', 'dt_trans_po')
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

SET @riap_prod_ready = DATABASE() = 'metalsindo_live'
  AND @riap_prod_mode IN ('preflight', 'apply', 'rollback')
  AND (SELECT COUNT(*) FROM information_schema.TABLES
       WHERE TABLE_SCHEMA = DATABASE() AND ENGINE = 'InnoDB' AND TABLE_NAME IN (
         'tr_receive_invoice_ap_header', 'tr_receive_invoice_ap_detail',
         'tr_incoming', 'dt_incoming', 'dt_trans_po')) = 5;

-- tr_receive_invoice_ap_header: (created_date,id_rec_inv_ap)
SET @riap_prod_equivalent = (
  SELECT COUNT(*) FROM (
    SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_used,
      SUM(SUB_PART IS NOT NULL) AS prefix_parts
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_header' AND INDEX_TYPE = 'BTREE'
    GROUP BY INDEX_NAME
  ) existing_indexes
  WHERE prefix_parts = 0 AND (columns_used = 'created_date,id_rec_inv_ap'
    OR LEFT(columns_used, 27) = 'created_date,id_rec_inv_ap,')
);
SET @riap_prod_named_columns = (
  SELECT GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_header' AND INDEX_NAME = 'idx_riap_header_created'
);
SET @riap_prod_named_exact = (
  SELECT COUNT(*) = 2 AND COALESCE(SUM(SUB_PART IS NOT NULL OR INDEX_TYPE <> 'BTREE'), 0) = 0
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_header' AND INDEX_NAME = 'idx_riap_header_created'
) AND @riap_prod_named_columns = 'created_date,id_rec_inv_ap';
SELECT 'tr_receive_invoice_ap_header' AS table_name, 'idx_riap_header_created' AS migration_index,
  'created_date,id_rec_inv_ap' AS required_columns, @riap_prod_equivalent AS equivalent_indexes,
  CASE
    WHEN NOT @riap_prod_ready THEN 'BLOCKED: check database, mode and all five InnoDB tables'
    WHEN @riap_prod_named_columns IS NOT NULL AND NOT @riap_prod_named_exact THEN 'BLOCKED: index name already has a different definition'
    WHEN @riap_prod_mode = 'rollback' AND @riap_prod_named_exact THEN 'DROP migration index; confirm it was absent before deployment'
    WHEN @riap_prod_mode = 'rollback' THEN 'SKIP: migration index absent'
    WHEN @riap_prod_equivalent > 0 THEN 'SKIP: equivalent index already exists'
    ELSE 'ADD index required'
  END AS action;
SET @riap_prod_sql = CASE
  WHEN NOT @riap_prod_ready
    THEN 'SELECT ''BLOCKED: invalid target/mode or missing InnoDB tables'' AS result'
  WHEN @riap_prod_named_columns IS NOT NULL AND NOT @riap_prod_named_exact
    THEN 'SELECT ''BLOCKED: conflicting index definition: idx_riap_header_created'' AS result'
  WHEN @riap_prod_mode = 'apply' AND @riap_prod_equivalent = 0
    THEN 'ALTER TABLE `tr_receive_invoice_ap_header` ADD INDEX `idx_riap_header_created` (`created_date`, `id_rec_inv_ap`), ALGORITHM=INPLACE, LOCK=NONE'
  WHEN @riap_prod_mode = 'rollback' AND @riap_prod_named_exact
    THEN 'ALTER TABLE `tr_receive_invoice_ap_header` DROP INDEX `idx_riap_header_created`, ALGORITHM=INPLACE, LOCK=NONE'
  ELSE 'SELECT ''idx_riap_header_created: no ALTER executed (preflight or already satisfied)'' AS result'
END;
PREPARE riap_prod_statement FROM @riap_prod_sql;
EXECUTE riap_prod_statement;
DEALLOCATE PREPARE riap_prod_statement;

-- tr_receive_invoice_ap_detail: (id_rec_inv_ap)
SET @riap_prod_equivalent = (
  SELECT COUNT(*) FROM (
    SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_used,
      SUM(SUB_PART IS NOT NULL) AS prefix_parts
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_detail' AND INDEX_TYPE = 'BTREE'
    GROUP BY INDEX_NAME
  ) existing_indexes
  WHERE prefix_parts = 0 AND (columns_used = 'id_rec_inv_ap'
    OR LEFT(columns_used, 14) = 'id_rec_inv_ap,')
);
SET @riap_prod_named_columns = (
  SELECT GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_detail' AND INDEX_NAME = 'idx_riap_detail_invoice'
);
SET @riap_prod_named_exact = (
  SELECT COUNT(*) = 1 AND COALESCE(SUM(SUB_PART IS NOT NULL OR INDEX_TYPE <> 'BTREE'), 0) = 0
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_receive_invoice_ap_detail' AND INDEX_NAME = 'idx_riap_detail_invoice'
) AND @riap_prod_named_columns = 'id_rec_inv_ap';
SELECT 'tr_receive_invoice_ap_detail' AS table_name, 'idx_riap_detail_invoice' AS migration_index,
  'id_rec_inv_ap' AS required_columns, @riap_prod_equivalent AS equivalent_indexes,
  CASE
    WHEN NOT @riap_prod_ready THEN 'BLOCKED: check database, mode and all five InnoDB tables'
    WHEN @riap_prod_named_columns IS NOT NULL AND NOT @riap_prod_named_exact THEN 'BLOCKED: index name already has a different definition'
    WHEN @riap_prod_mode = 'rollback' AND @riap_prod_named_exact THEN 'DROP migration index; confirm it was absent before deployment'
    WHEN @riap_prod_mode = 'rollback' THEN 'SKIP: migration index absent'
    WHEN @riap_prod_equivalent > 0 THEN 'SKIP: equivalent index already exists'
    ELSE 'ADD index required'
  END AS action;
SET @riap_prod_sql = CASE
  WHEN NOT @riap_prod_ready
    THEN 'SELECT ''BLOCKED: invalid target/mode or missing InnoDB tables'' AS result'
  WHEN @riap_prod_named_columns IS NOT NULL AND NOT @riap_prod_named_exact
    THEN 'SELECT ''BLOCKED: conflicting index definition: idx_riap_detail_invoice'' AS result'
  WHEN @riap_prod_mode = 'apply' AND @riap_prod_equivalent = 0
    THEN 'ALTER TABLE `tr_receive_invoice_ap_detail` ADD INDEX `idx_riap_detail_invoice` (`id_rec_inv_ap`), ALGORITHM=INPLACE, LOCK=NONE'
  WHEN @riap_prod_mode = 'rollback' AND @riap_prod_named_exact
    THEN 'ALTER TABLE `tr_receive_invoice_ap_detail` DROP INDEX `idx_riap_detail_invoice`, ALGORITHM=INPLACE, LOCK=NONE'
  ELSE 'SELECT ''idx_riap_detail_invoice: no ALTER executed (preflight or already satisfied)'' AS result'
END;
PREPARE riap_prod_statement FROM @riap_prod_sql;
EXECUTE riap_prod_statement;
DEALLOCATE PREPARE riap_prod_statement;

-- tr_incoming: (id_incoming)
SET @riap_prod_equivalent = (
  SELECT COUNT(*) FROM (
    SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_used,
      SUM(SUB_PART IS NOT NULL) AS prefix_parts
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_incoming' AND INDEX_TYPE = 'BTREE'
    GROUP BY INDEX_NAME
  ) existing_indexes
  WHERE prefix_parts = 0 AND (columns_used = 'id_incoming'
    OR LEFT(columns_used, 12) = 'id_incoming,')
);
SET @riap_prod_named_columns = (
  SELECT GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_incoming' AND INDEX_NAME = 'idx_riap_incoming_header'
);
SET @riap_prod_named_exact = (
  SELECT COUNT(*) = 1 AND COALESCE(SUM(SUB_PART IS NOT NULL OR INDEX_TYPE <> 'BTREE'), 0) = 0
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_incoming' AND INDEX_NAME = 'idx_riap_incoming_header'
) AND @riap_prod_named_columns = 'id_incoming';
SELECT 'tr_incoming' AS table_name, 'idx_riap_incoming_header' AS migration_index,
  'id_incoming' AS required_columns, @riap_prod_equivalent AS equivalent_indexes,
  CASE
    WHEN NOT @riap_prod_ready THEN 'BLOCKED: check database, mode and all five InnoDB tables'
    WHEN @riap_prod_named_columns IS NOT NULL AND NOT @riap_prod_named_exact THEN 'BLOCKED: index name already has a different definition'
    WHEN @riap_prod_mode = 'rollback' AND @riap_prod_named_exact THEN 'DROP migration index; confirm it was absent before deployment'
    WHEN @riap_prod_mode = 'rollback' THEN 'SKIP: migration index absent'
    WHEN @riap_prod_equivalent > 0 THEN 'SKIP: equivalent index already exists'
    ELSE 'ADD index required'
  END AS action;
SET @riap_prod_sql = CASE
  WHEN NOT @riap_prod_ready
    THEN 'SELECT ''BLOCKED: invalid target/mode or missing InnoDB tables'' AS result'
  WHEN @riap_prod_named_columns IS NOT NULL AND NOT @riap_prod_named_exact
    THEN 'SELECT ''BLOCKED: conflicting index definition: idx_riap_incoming_header'' AS result'
  WHEN @riap_prod_mode = 'apply' AND @riap_prod_equivalent = 0
    THEN 'ALTER TABLE `tr_incoming` ADD INDEX `idx_riap_incoming_header` (`id_incoming`), ALGORITHM=INPLACE, LOCK=NONE'
  WHEN @riap_prod_mode = 'rollback' AND @riap_prod_named_exact
    THEN 'ALTER TABLE `tr_incoming` DROP INDEX `idx_riap_incoming_header`, ALGORITHM=INPLACE, LOCK=NONE'
  ELSE 'SELECT ''idx_riap_incoming_header: no ALTER executed (preflight or already satisfied)'' AS result'
END;
PREPARE riap_prod_statement FROM @riap_prod_sql;
EXECUTE riap_prod_statement;
DEALLOCATE PREPARE riap_prod_statement;

-- dt_incoming: (id_incoming)
SET @riap_prod_equivalent = (
  SELECT COUNT(*) FROM (
    SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_used,
      SUM(SUB_PART IS NOT NULL) AS prefix_parts
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dt_incoming' AND INDEX_TYPE = 'BTREE'
    GROUP BY INDEX_NAME
  ) existing_indexes
  WHERE prefix_parts = 0 AND (columns_used = 'id_incoming'
    OR LEFT(columns_used, 12) = 'id_incoming,')
);
SET @riap_prod_named_columns = (
  SELECT GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dt_incoming' AND INDEX_NAME = 'idx_riap_incoming_detail'
);
SET @riap_prod_named_exact = (
  SELECT COUNT(*) = 1 AND COALESCE(SUM(SUB_PART IS NOT NULL OR INDEX_TYPE <> 'BTREE'), 0) = 0
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dt_incoming' AND INDEX_NAME = 'idx_riap_incoming_detail'
) AND @riap_prod_named_columns = 'id_incoming';
SELECT 'dt_incoming' AS table_name, 'idx_riap_incoming_detail' AS migration_index,
  'id_incoming' AS required_columns, @riap_prod_equivalent AS equivalent_indexes,
  CASE
    WHEN NOT @riap_prod_ready THEN 'BLOCKED: check database, mode and all five InnoDB tables'
    WHEN @riap_prod_named_columns IS NOT NULL AND NOT @riap_prod_named_exact THEN 'BLOCKED: index name already has a different definition'
    WHEN @riap_prod_mode = 'rollback' AND @riap_prod_named_exact THEN 'DROP migration index; confirm it was absent before deployment'
    WHEN @riap_prod_mode = 'rollback' THEN 'SKIP: migration index absent'
    WHEN @riap_prod_equivalent > 0 THEN 'SKIP: equivalent index already exists'
    ELSE 'ADD index required'
  END AS action;
SET @riap_prod_sql = CASE
  WHEN NOT @riap_prod_ready
    THEN 'SELECT ''BLOCKED: invalid target/mode or missing InnoDB tables'' AS result'
  WHEN @riap_prod_named_columns IS NOT NULL AND NOT @riap_prod_named_exact
    THEN 'SELECT ''BLOCKED: conflicting index definition: idx_riap_incoming_detail'' AS result'
  WHEN @riap_prod_mode = 'apply' AND @riap_prod_equivalent = 0
    THEN 'ALTER TABLE `dt_incoming` ADD INDEX `idx_riap_incoming_detail` (`id_incoming`), ALGORITHM=INPLACE, LOCK=NONE'
  WHEN @riap_prod_mode = 'rollback' AND @riap_prod_named_exact
    THEN 'ALTER TABLE `dt_incoming` DROP INDEX `idx_riap_incoming_detail`, ALGORITHM=INPLACE, LOCK=NONE'
  ELSE 'SELECT ''idx_riap_incoming_detail: no ALTER executed (preflight or already satisfied)'' AS result'
END;
PREPARE riap_prod_statement FROM @riap_prod_sql;
EXECUTE riap_prod_statement;
DEALLOCATE PREPARE riap_prod_statement;

-- dt_trans_po: (id_dt_po,idmaterial)
SET @riap_prod_equivalent = (
  SELECT COUNT(*) FROM (
    SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_used,
      SUM(SUB_PART IS NOT NULL) AS prefix_parts
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dt_trans_po' AND INDEX_TYPE = 'BTREE'
    GROUP BY INDEX_NAME
  ) existing_indexes
  WHERE prefix_parts = 0 AND (columns_used = 'id_dt_po,idmaterial'
    OR LEFT(columns_used, 20) = 'id_dt_po,idmaterial,')
);
SET @riap_prod_named_columns = (
  SELECT GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dt_trans_po' AND INDEX_NAME = 'idx_riap_po_detail'
);
SET @riap_prod_named_exact = (
  SELECT COUNT(*) = 2 AND COALESCE(SUM(SUB_PART IS NOT NULL OR INDEX_TYPE <> 'BTREE'), 0) = 0
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dt_trans_po' AND INDEX_NAME = 'idx_riap_po_detail'
) AND @riap_prod_named_columns = 'id_dt_po,idmaterial';
SELECT 'dt_trans_po' AS table_name, 'idx_riap_po_detail' AS migration_index,
  'id_dt_po,idmaterial' AS required_columns, @riap_prod_equivalent AS equivalent_indexes,
  CASE
    WHEN NOT @riap_prod_ready THEN 'BLOCKED: check database, mode and all five InnoDB tables'
    WHEN @riap_prod_named_columns IS NOT NULL AND NOT @riap_prod_named_exact THEN 'BLOCKED: index name already has a different definition'
    WHEN @riap_prod_mode = 'rollback' AND @riap_prod_named_exact THEN 'DROP migration index; confirm it was absent before deployment'
    WHEN @riap_prod_mode = 'rollback' THEN 'SKIP: migration index absent'
    WHEN @riap_prod_equivalent > 0 THEN 'SKIP: equivalent index already exists'
    ELSE 'ADD index required'
  END AS action;
SET @riap_prod_sql = CASE
  WHEN NOT @riap_prod_ready
    THEN 'SELECT ''BLOCKED: invalid target/mode or missing InnoDB tables'' AS result'
  WHEN @riap_prod_named_columns IS NOT NULL AND NOT @riap_prod_named_exact
    THEN 'SELECT ''BLOCKED: conflicting index definition: idx_riap_po_detail'' AS result'
  WHEN @riap_prod_mode = 'apply' AND @riap_prod_equivalent = 0
    THEN 'ALTER TABLE `dt_trans_po` ADD INDEX `idx_riap_po_detail` (`id_dt_po`, `idmaterial`), ALGORITHM=INPLACE, LOCK=NONE'
  WHEN @riap_prod_mode = 'rollback' AND @riap_prod_named_exact
    THEN 'ALTER TABLE `dt_trans_po` DROP INDEX `idx_riap_po_detail`, ALGORITHM=INPLACE, LOCK=NONE'
  ELSE 'SELECT ''idx_riap_po_detail: no ALTER executed (preflight or already satisfied)'' AS result'
END;
PREPARE riap_prod_statement FROM @riap_prod_sql;
EXECUTE riap_prod_statement;
DEALLOCATE PREPARE riap_prod_statement;

-- Postflight: each lookup should have at least one suitable index after apply.
SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME, SUB_PART
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (
  'tr_receive_invoice_ap_header', 'tr_receive_invoice_ap_detail',
  'tr_incoming', 'dt_incoming', 'dt_trans_po')
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
SET SESSION lock_wait_timeout = @riap_prod_saved_lock_timeout;
