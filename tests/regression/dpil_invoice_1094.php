<?php
// Integration test: local Docker MySQL only. Never loads application DB credentials.
// php tests/regression/dpil_invoice_1094.php
if (PHP_SAPI !== 'cli') exit;
require dirname(dirname(__DIR__)) . '/scripts/correct_dpil_invoice_1094.php';
mysqli_report(MYSQLI_REPORT_OFF);
$db = new mysqli('metalsindo_db', 'root', 'root', 'metalsindo_dev');
if ($db->connect_errno) throw new Exception('Local test database unavailable.');
$schema = 'metalsindo_invoice1094_test_' . bin2hex(openssl_random_pseudo_bytes(6));
if (!$db->query("CREATE DATABASE $schema")) throw new Exception($db->error);
$db->select_db($schema);
$tables = array('tr_invoice', 'master_customers', 'tr_invoice_detail', 'tr_invoice_payment_detail', 'dt_spkmarketing', 'faktur_e_logs', 'jurnal', 'jarh');
foreach ($tables as $table) $db->query("DROP TABLE IF EXISTS $table");
$ddl = array(
    'tr_invoice' => 'id INT PRIMARY KEY, no_invoice VARCHAR(30) UNIQUE, no_surat VARCHAR(60), id_customer VARCHAR(30), no_so VARCHAR(30), no_do VARCHAR(60), id_do VARCHAR(30), no_po VARCHAR(60), tgl_invoice DATE, status_jurnal VARCHAR(10), status_close INT, total_bayar DECIMAL(15,2), total_bayar_idr DECIMAL(15,2), nilai_produk DECIMAL(15,2), total DECIMAL(15,2), diskon DECIMAL(15,2), dpp DECIMAL(15,2), ppn INT, nilai_ppn DECIMAL(15,2), nilai_invoice DECIMAL(15,2), grand_total DECIMAL(15,2), sisa_invoice_idr DECIMAL(15,2), stat_efaktur INT, printed_on DATETIME',
    'master_customers' => 'id_customer VARCHAR(30) PRIMARY KEY, name_customer VARCHAR(100), facility VARCHAR(50)',
    'tr_invoice_detail' => 'id_invoice_detail INT PRIMARY KEY, no_invoice VARCHAR(30), no_so VARCHAR(30), id_category3 VARCHAR(30), qty_invoice DECIMAL(15,2), harga_satuan DECIMAL(15,2), total_harga DECIMAL(15,2), nilai_diskon DECIMAL(15,2), freight_cost DECIMAL(15,2)',
    'tr_invoice_payment_detail' => 'id INT PRIMARY KEY, no_invoice VARCHAR(30)',
    'dt_spkmarketing' => 'id INT PRIMARY KEY, id_spkmarketing VARCHAR(30), deal INT, nominal_discount DECIMAL(15,2)',
    'faktur_e_logs' => 'id INT PRIMARY KEY, invoice_no VARCHAR(60), id_export VARCHAR(30)',
    'jurnal' => 'nomor VARCHAR(30) PRIMARY KEY, no_reff VARCHAR(60), keterangan TEXT',
    'jarh' => 'nomor VARCHAR(30) PRIMARY KEY, no_reff VARCHAR(60), note TEXT'
);
foreach ($ddl as $table => $columns) {
    $engine = in_array($table, array('jurnal', 'jarh'), true) ? 'MyISAM' : 'InnoDB';
    if (!$db->query("CREATE TABLE $table ($columns) ENGINE=$engine")) throw new Exception($db->error);
}
function test_sql($sql) { global $db; if (!$db->query($sql)) throw new Exception($db->error); }
function test_assert($value, $message) { if (!$value) throw new Exception($message); }
function fixture1094() {
    global $db, $tables;
    foreach ($tables as $table) test_sql("DELETE FROM $table");
    test_sql("INSERT INTO tr_invoice VALUES (3482,'I2603468','INV-MP/26/IX/1094','MC2400160','P2602716','DO-MP/26/IX/1095','DO2603556','POP260001541','2026-09-18','OPN',0,0,0,600000,600000,0,600000,0,0,600000,600000,600000,1,'2026-10-12 09:35:42')");
    test_sql("INSERT INTO tr_invoice SELECT 9999,'OTHER','OTHER',id_customer,no_so,no_do,id_do,no_po,tgl_invoice,status_jurnal,status_close,total_bayar,total_bayar_idr,nilai_produk,total,diskon,dpp,ppn,nilai_ppn,nilai_invoice,grand_total,sisa_invoice_idr,stat_efaktur,printed_on FROM tr_invoice WHERE id=3482");
    test_sql("INSERT INTO master_customers VALUES ('MC2400160','PT.LIMA TEKNO INDONESIA ','DPIL')");
    test_sql("INSERT INTO tr_invoice_detail VALUES (10342,'I2603468','P2602716','I2000010',2.40,250000,600000,0,0)");
    test_sql("INSERT INTO dt_spkmarketing VALUES (1,'P2602716',1,0)");
    test_sql("INSERT INTO faktur_e_logs VALUES (701,'INV-MP/26/IX/1094','2609251429')");
}
function rejected1094($operation, $message) {
    global $db;
    $before = $db->query('SELECT * FROM tr_invoice ORDER BY id')->fetch_all(MYSQLI_ASSOC);
    $failed = false;
    try { $operation(); } catch (Exception $e) { $failed = true; }
    test_assert($failed, $message . ' must be rejected');
    test_assert($before === $db->query('SELECT * FROM tr_invoice ORDER BY id')->fetch_all(MYSQLI_ASSOC), $message . ' must not change invoices');
}
$audit = sys_get_temp_dir() . '/dpil1094-tests-' . uniqid();
mkdir($audit, 0700);
$fix = new DpilInvoice1094Correction($db, $schema, $schema);

// Execute the actual list model and print template with controlled fixture reads.
define('BASEPATH', dirname(dirname(__DIR__)) . '/system/');
class BF_Model { public $db; public $input; }
function has_permission($permission) { return false; }
require dirname(dirname(__DIR__)) . '/application/modules/wt_invoicing/models/Wt_invoicing_model.php';
class Invoice1094Result {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function row() { return empty($this->rows) ? null : (object) $this->rows[0]; }
    public function row_array() { return empty($this->rows) ? array() : $this->rows[0]; }
    public function result() { return array_map(function ($row) { return (object) $row; }, $this->rows); }
    public function result_array() { return $this->rows; }
}
class Invoice1094ViewDatabase {
    public $header;
    private $table;
    private $selection;
    public function __construct($header) {
        $this->header = array_merge(array('type' => 'reguler', 'top' => '0', 'tgl_do' => '2026-09-18', 'tgl_po' => '2026-09-16', 'note' => 'Cash on Delivery', 'name_customer' => 'PT.LIMA TEKNO INDONESIA', 'facility' => 'DPIL', 'no_proforma_invoice' => '', 'no_inquiry' => ''), $header);
    }
    public function select($selection) { $this->selection = $selection; return $this; }
    public function from($table) { $this->table = $table; return $this; }
    public function __call($name, $args) { return $this; }
    public function count_all_results() { return 1; }
    public function get() {
        if ($this->table === 'tr_invoice a') return new Invoice1094Result(array($this->header));
        if ($this->table === 'tr_invoice_detail a') return new Invoice1094Result(strpos($this->selection, 'SUM(') === 0 ? array(array('ttl_harga' => 600000)) : array());
        if ($this->table === 'dt_spkmarketing') return new Invoice1094Result(array(array('total_disc' => 0)));
        return new Invoice1094Result(array(array('harga_deal' => 250000)));
    }
    public function get_where($table, $where) { return new Invoice1094Result(array(array('id_bentuk' => 'B2000001'))); }
    public function query($sql) {
        if (strpos($sql, 'FROM master_customers') !== false) return new Invoice1094Result(array(array('name_customer' => 'PT.LIMA TEKNO INDONESIA', 'facility' => 'DPIL', 'address_office' => '', 'telephone' => '')));
        return new Invoice1094Result(array());
    }
}
class Invoice1094Input {
    public function post($key) { $values = array('draw' => 1, 'start' => 0, 'length' => 10, 'search' => array()); return $values[$key]; }
}
class Invoice1094PrintRenderer {
    public $db;
    public function render($invoice, $line) {
        $this->db = new Invoice1094ViewDatabase($invoice);
        $header = array((object) $this->db->header);
        $detail = array((object) array_merge(array('nama_produk' => 'BSR-C2680-1/2H-0.50', 'tobe_size' => '0.50 X 93.00 X C', 'part_number' => null), $line));
        $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(__DIR__));
        ob_start();
        include dirname(dirname(__DIR__)) . '/application/modules/wt_invoicing/views/PrintInvoice.php';
        return ob_get_clean();
    }
}
function verify1094Views($invoice, $line, $corrected) {
    $model = (new ReflectionClass('Wt_invoicing_model'))->newInstanceWithoutConstructor();
    $model->db = new Invoice1094ViewDatabase($invoice);
    $model->input = new Invoice1094Input();
    ob_start(); $model->get_invoicing(); $json = json_decode(ob_get_clean(), true);
    test_assert($json['data'][0]['nilai_ppn'] === ($corrected ? '66,000' : '0') && $json['data'][0]['nilai_invoice'] === ($corrected ? '666,000' : '600,000'), 'Actual invoice list tax/total');
    $html = (new Invoice1094PrintRenderer())->render($invoice, $line);
    test_assert(preg_match('/>PPN<\/th>\s*<th[^>]*>66,000<\/th>/', $html) === ($corrected ? 1 : 0), 'Actual print template tax visibility');
    test_assert(strpos($html, $corrected ? '>666,000</th>' : '>600,000</th>') !== false, 'Actual print template grand total');
}
try {
    fixture1094();
    $original = $db->query('SELECT * FROM tr_invoice ORDER BY id')->fetch_all(MYSQLI_ASSOC);
    $line = $db->query('SELECT * FROM tr_invoice_detail')->fetch_assoc();
    verify1094Views($original[0], $line, false);
    $preflight = $fix->run(false, null);
    test_assert($preflight['status'] === 'preflight', 'Default is preflight');
    test_assert($original === $db->query('SELECT * FROM tr_invoice ORDER BY id')->fetch_all(MYSQLI_ASSOC), 'Preflight is read-only');
    $applied = $fix->run(true, $audit);
    test_assert($applied['status'] === 'committed', 'Correction committed');
    $after = $db->query('SELECT * FROM tr_invoice ORDER BY id')->fetch_all(MYSQLI_ASSOC);
    verify1094Views($after[0], $line, true);
    test_assert($after[0]['nilai_ppn'] === '66000.00' && $after[0]['nilai_invoice'] === '666000.00' && $after[0]['sisa_invoice_idr'] === '666000.00', 'Tax, invoice and receivable corrected');
    test_assert($after[1] === $original[1], 'Other invoice unchanged');
    test_assert($fix->run(true, $audit)['status'] === 'already-corrected', 'Rerun is a no-op');
    test_assert($after === $db->query('SELECT * FROM tr_invoice ORDER BY id')->fetch_all(MYSQLI_ASSOC), 'Rerun does not update');
    $fix->run(true, $audit, $applied['backup']);
    test_assert($original === $db->query('SELECT * FROM tr_invoice ORDER BY id')->fetch_all(MYSQLI_ASSOC), 'Rollback restores original header exactly');
    foreach (array(
        "UPDATE tr_invoice SET no_surat='WRONG' WHERE id=3482",
        "UPDATE master_customers SET facility='Kawasan Berikat'",
        "UPDATE tr_invoice SET nilai_invoice=600001 WHERE id=3482",
        "UPDATE tr_invoice SET status_close=1 WHERE id=3482",
        "UPDATE tr_invoice SET total_bayar_idr=1 WHERE id=3482",
        "UPDATE tr_invoice_detail SET qty_invoice=3 WHERE id_invoice_detail=10342",
        "INSERT INTO tr_invoice_payment_detail VALUES (1,'I2603468')",
        "INSERT INTO jurnal VALUES ('J1','I2603468','invoice')",
        "INSERT INTO jarh VALUES ('J1','OTHER','INV-MP/26/IX/1094')"
    ) as $sql) {
        fixture1094(); test_sql($sql);
        rejected1094(function () use ($fix, $audit) { $fix->run(true, $audit); }, $sql);
    }
    foreach (array(
        "INSERT INTO tr_invoice_payment_detail VALUES (1,'I2603468')",
        "INSERT INTO jurnal VALUES ('J1','I2603468','invoice')",
        "UPDATE tr_invoice SET printed_on=NOW() WHERE id=3482"
    ) as $sql) {
        fixture1094(); $applied = $fix->run(true, $audit); test_sql($sql);
        rejected1094(function () use ($fix, $audit, $applied) { $fix->run(true, $audit, $applied['backup']); }, 'Unsafe rollback');
    }
    fixture1094();
    test_sql('CREATE TRIGGER corrupt1094 BEFORE UPDATE ON tr_invoice FOR EACH ROW SET NEW.dpp=1');
    rejected1094(function () use ($fix, $audit) { $fix->run(true, $audit); }, 'Post-update verification rolls back unexpected trigger change');
    test_sql('DROP TRIGGER corrupt1094');
    fixture1094();
    $applied = $fix->run(true, $audit);
    $tampered = json_decode(file_get_contents($applied['backup']), true);
    $tampered['before']['header'][0]['no_surat'] = 'WRONG';
    file_put_contents($applied['backup'], json_encode($tampered));
    rejected1094(function () use ($fix, $audit, $applied) { $fix->run(true, $audit, $applied['backup']); }, 'Tampered rollback snapshot');
    fixture1094();
    rejected1094(function () use ($fix) { $fix->run(true, dirname(dirname(__DIR__))); }, 'Audit inside web root');
    rejected1094(function () use ($fix) { $fix->run(true, null); }, 'Missing audit directory');
    echo "PASS: correction, preflight, idempotency, rollback, unchanged invoice, rejection scenarios, verification failure rollback, list JSON and print rendering.\n";
} finally {
    // Only the fixed local test schema created above is removed.
    $db->query("DROP DATABASE $schema");
    foreach (glob($audit . '/*') as $file) unlink($file);
    rmdir($audit);
    $db->close();
}
