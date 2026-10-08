<?php
// CLI only. Default is a read-only preflight; pass --apply to correct exactly two invoices.
// php scripts/correct_bonded_invoices_0848_0849.php [--apply --backup-dir=PATH]
// Choose an audit directory outside the web document root.
if (PHP_SAPI !== 'cli') exit;
date_default_timezone_set('Asia/Jakarta');
$root = dirname(__DIR__);
define('BASEPATH', $root . '/system/');
define('ENVIRONMENT', 'development');
require $root . '/application/config/development/database.php';
$apply = in_array('--apply', $argv, true);
$backupDir = null;
foreach ($argv as $argument) {
    if (strpos($argument, '--backup-dir=') === 0) $backupDir = substr($argument, 13);
}
if ($apply && empty($backupDir)) {
    fwrite(STDERR, 'Apply requires --backup-dir=PATH outside the web document root.' . PHP_EOL);
    exit(1);
}
$expected = array(
    'I2603222' => array('number' => 'INV-MP/26/VIII/0848', 'subtotal' => 224217000.00, 'old_total' => 248880870.00, 'old_tax' => 24663870.00),
    'I2603223' => array('number' => 'INV-MP/26/VIII/0849', 'subtotal' => 12668250.00, 'old_total' => 14061757.50, 'old_tax' => 1393507.50)
);
function require_condition($condition, $message) {
    if (!$condition) throw new Exception($message);
}
function fetch_rows($connection, $sql) {
    $result = $connection->query($sql);
    if (!$result) throw new Exception($connection->error);
    $rows = array();
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    $result->free();
    return $rows;
}
function same_amount($a, $b) { return abs((float) $a - (float) $b) < 0.005; }
function save_snapshot($path, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    require_condition($json !== false && file_put_contents($path, $json . PHP_EOL, LOCK_EX) !== false, 'Cannot save audit snapshot.');
}
mysqli_report(MYSQLI_REPORT_OFF);
$settings = $db['default'];
require_condition($settings['database'] === 'metalsindo_live', 'Unexpected database; abort.');
$connection = mysqli_init();
$connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
require_condition(@$connection->real_connect($settings['hostname'], $settings['username'], $settings['password'], $settings['database'], isset($settings['port']) ? $settings['port'] : 3306), 'Database connection failed.');
$connection->set_charset('utf8');
$backupPath = null;
$inTransaction = false;
try {
    $engines = fetch_rows($connection, "SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'metalsindo_live' AND TABLE_NAME IN ('tr_invoice','tr_invoice_detail','tr_invoice_payment_detail')");
    require_condition(count($engines) === 3, 'Missing transaction tables.');
    foreach ($engines as $engine) require_condition($engine['ENGINE'] === 'InnoDB', 'Transactional tables required.');
    require_condition($connection->begin_transaction(), 'Cannot start transaction.');
    $inTransaction = true;
    $ids = "('I2603222','I2603223')";
    $before = fetch_rows($connection, 'SELECT * FROM tr_invoice WHERE no_invoice IN ' . $ids . ' ORDER BY no_invoice FOR UPDATE');
    require_condition(count($before) === 2, 'Expected exactly two invoice headers.');
    $customer = fetch_rows($connection, "SELECT id_customer, name_customer, facility FROM master_customers WHERE id_customer = 'MC2000013' FOR UPDATE");
    require_condition(count($customer) === 1 && stripos($customer[0]['facility'], 'Kawasan Berikat') !== false
        && $customer[0]['name_customer'] === 'PT. TAKITA MANUFACTURING INDONESIA', 'Customer identity/facility changed.');
    $details = fetch_rows($connection, 'SELECT * FROM tr_invoice_detail WHERE no_invoice IN ' . $ids . ' ORDER BY no_invoice FOR UPDATE');
    $payments = fetch_rows($connection, 'SELECT * FROM tr_invoice_payment_detail WHERE no_invoice IN ' . $ids . ' FOR UPDATE');
    require_condition(count($payments) === 0, 'Payment records found; abort.');
    $subtotals = array('I2603222' => 0, 'I2603223' => 0);
    foreach ($details as $detail) $subtotals[$detail['no_invoice']] += $detail['qty_invoice'] * $detail['harga_satuan'];
    foreach ($before as $invoice) {
        $target = $expected[$invoice['no_invoice']];
        require_condition($invoice['no_surat'] === $target['number'] && $invoice['id_customer'] === 'MC2000013', 'Invoice identity changed.');
        require_condition($invoice['status_jurnal'] === 'OPN' && $invoice['status_close'] === '0' && !empty($invoice['printed_on']), 'Invoice status changed.');
        require_condition(same_amount($invoice['total_bayar_idr'], 0) && same_amount($invoice['total_bayar'], 0), 'Invoice already paid.');
        require_condition(same_amount($subtotals[$invoice['no_invoice']], $target['subtotal']) && same_amount($invoice['nilai_produk'], $target['subtotal']), 'Invoice subtotal changed.');
        require_condition(same_amount($invoice['ppn'], 11) && same_amount($invoice['nilai_ppn'], $target['old_tax'])
            && same_amount($invoice['nilai_invoice'], $target['old_total']) && same_amount($invoice['sisa_invoice_idr'], $target['old_total']), 'Before values changed or correction already applied.');
    }
    $refs = "('I2603222','I2603223','INV-MP/26/VIII/0848','INV-MP/26/VIII/0849')";
    $journal = fetch_rows($connection, "SELECT nomor FROM gl_metalsindo_live.jurnal WHERE no_reff IN $refs OR keterangan LIKE '%INV-MP/26/VIII/0848%' OR keterangan LIKE '%INV-MP/26/VIII/0849%' OR keterangan LIKE '%I2603222%' OR keterangan LIKE '%I2603223%'");
    $jarh = fetch_rows($connection, "SELECT nomor FROM gl_metalsindo_live.jarh WHERE no_reff IN $refs OR note LIKE '%INV-MP/26/VIII/0848%' OR note LIKE '%INV-MP/26/VIII/0849%' OR note LIKE '%I2603222%' OR note LIKE '%I2603223%'");
    require_condition(count($journal) === 0 && count($jarh) === 0, 'Accounting journal references found; abort.');
    if (!$apply) {
        $connection->rollback();
        $inTransaction = false;
        echo "PREFLIGHT PASS: two unpaid, open, bonded invoices; expected subtotals and no journal references. No data changed." . PHP_EOL;
        exit(0);
    }
    require_condition(is_dir($backupDir) || mkdir($backupDir, 0777, true), 'Cannot create audit directory.');
    $backupPath = $backupDir . '/before_' . date('Ymd_His') . '_' . getmypid() . '.json';
    save_snapshot($backupPath, array('captured_at' => date('c'), 'database' => 'metalsindo_live', 'invoices' => $before, 'customer' => $customer, 'details' => $details, 'payments' => $payments, 'journal' => $journal, 'jarh' => $jarh));
    $affected = 0;
    foreach ($expected as $id => $target) {
        $statement = $connection->prepare('UPDATE tr_invoice SET ppn = 0, nilai_ppn = 0, nilai_invoice = ?, sisa_invoice_idr = ? WHERE no_invoice = ?');
        require_condition($statement !== false, 'Cannot prepare correction.');
        $total = $target['subtotal'];
        $statement->bind_param('dds', $total, $total, $id);
        require_condition($statement->execute(), 'Correction failed.');
        $affected += $statement->affected_rows;
        $statement->close();
    }
    require_condition($affected === 2, 'Expected exactly two changed rows.');
    $after = fetch_rows($connection, 'SELECT * FROM tr_invoice WHERE no_invoice IN ' . $ids . ' ORDER BY no_invoice');
    require_condition(count($after) === 2, 'Missing invoice after correction.');
    foreach ($after as $index => $invoice) {
        $target = $expected[$invoice['no_invoice']];
        require_condition(same_amount($invoice['ppn'], 0) && same_amount($invoice['nilai_ppn'], 0)
            && same_amount($invoice['nilai_invoice'], $target['subtotal']) && same_amount($invoice['sisa_invoice_idr'], $target['subtotal']), 'Post-correction totals do not match.');
        foreach ($invoice as $key => $value) {
            if (!in_array($key, array('ppn','nilai_ppn','nilai_invoice','sisa_invoice_idr'), true)) {
                require_condition($value === $before[$index][$key], 'Unexpected change to invoice field: ' . $key);
            }
        }
    }
    require_condition($details === fetch_rows($connection, 'SELECT * FROM tr_invoice_detail WHERE no_invoice IN ' . $ids . ' ORDER BY no_invoice'), 'Details changed.');
    require_condition($payments === fetch_rows($connection, 'SELECT * FROM tr_invoice_payment_detail WHERE no_invoice IN ' . $ids), 'Payments changed.');
    // Persist verified after-values before commit; the separate marker confirms commit succeeded.
    save_snapshot($backupPath . '.verified-after.json', array('verified_at' => date('c'), 'affected_rows' => $affected, 'invoices' => $after));
    require_condition($connection->commit(), 'Commit failed.');
    $inTransaction = false;
    echo 'COMMITTED: exactly two invoices corrected. Backup: ' . $backupPath . PHP_EOL;
    if (file_put_contents($backupPath . '.committed', date('c') . PHP_EOL) === false) {
        echo 'WARNING: commit succeeded, but audit commit marker could not be written.' . PHP_EOL;
    }
} catch (Exception $error) {
    if ($inTransaction) $connection->rollback();
    fwrite(STDERR, 'ABORTED: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
$connection->close();
