<?php
if (PHP_SAPI !== 'cli') exit;
define('BASEPATH', __DIR__);
class BF_Model { public function __construct() {} }
class Admin_Controller {}
function base_url($path) { return '/' . $path; }
function has_permission($permission) { return true; }
require dirname(dirname(__DIR__)) . '/application/modules/receive_invoice_ap/models/Receive_invoice_ap_model.php';
require dirname(dirname(__DIR__)) . '/application/modules/receive_invoice_ap/controllers/Receive_invoice_ap.php';

class InvoiceIndexResult
{
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function row() { return reset($this->rows); }
    public function result() { return $this->rows; }
}
class InvoiceIndexDb
{
    public $pdo;
    public function __construct()
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->sqliteCreateFunction('CONCAT', function () { return implode('', func_get_args()); });
        $this->pdo->sqliteCreateFunction('FORMAT', function ($amount, $decimals) { return number_format($amount, $decimals); });
        $this->pdo->sqliteCreateCollation('utf8_general_ci', 'strcasecmp');
    }
    public function escape_like_str($value) { return str_replace(array('!', '%', '_'), array('!!', '!%', '!_'), $value); }
    public function query($sql, $params = array())
    {
        // SQLite has no charset conversion; the MariaDB mode tests these expressions intact.
        $sql = str_replace(array('CONVERT(po.matauang USING utf8)', 'CONVERT(FORMAT(invoice_amount, 2) USING utf8)'),
            array('po.matauang', 'FORMAT(invoice_amount, 2)'), $sql);
        $stmt = $this->pdo->prepare($sql);
        foreach (array_values($params) as $i => $value) {
            $stmt->bindValue($i + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return new InvoiceIndexResult($stmt->fetchAll(PDO::FETCH_OBJ));
    }
}
class InvoiceCollationConnection
{
    public $db;
    public function __construct()
    {
        // Dedicated disposable test server; never read application DB credentials.
        $this->db = new mysqli('127.0.0.1', 'root', '', 'mysql', 18762);
        $this->db->set_charset('utf8');
    }
    public function exec($sql)
    {
        if (strpos($sql, 'CREATE TABLE ') === 0) {
            $sql = str_replace('CREATE TABLE ', 'CREATE TEMPORARY TABLE ', $sql)
                . ' DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci';
        }
        $this->db->query($sql);
    }
}
class InvoiceCollationDb extends InvoiceIndexDb
{
    public $queries = array();
    public function __construct() { $this->pdo = new InvoiceCollationConnection(); }
    public function query($sql, $params = array())
    {
        $db = $this->pdo->db;
        $index = 0;
        $sql = preg_replace_callback('/\?/', function () use (&$index, $params, $db) {
            $value = $params[$index++];
            return is_int($value) ? (string) $value : "'" . $db->real_escape_string($value) . "'";
        }, $sql);
        $result = $db->query($sql);
        $this->queries[] = $sql;
        $rows = array();
        while ($row = $result->fetch_object()) $rows[] = $row;
        return new InvoiceIndexResult($rows);
    }
}
function check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqlCollation = in_array('--mysql-collation', $argv, true);
$db = $mysqlCollation ? new InvoiceCollationDb() : new InvoiceIndexDb();
foreach (array(
    'CREATE TABLE tr_receive_invoice_ap_header (id_rec_inv_ap TEXT, no_invoice TEXT, nm_suplier TEXT, id_suplier TEXT, ppn NUMERIC, created_date TEXT)',
    'CREATE TABLE tr_receive_invoice_ap_detail (id_rec_inv_ap TEXT, id_incoming TEXT, total_nilai NUMERIC)',
    'CREATE TABLE tr_incoming (id_incoming TEXT, id_suplier TEXT)',
    'CREATE TABLE dt_incoming (id_incoming TEXT, id_dt_po TEXT, id_material TEXT)',
    'CREATE TABLE dt_trans_po (id_dt_po TEXT, idmaterial TEXT, no_po TEXT)',
    'CREATE TABLE tr_purchase_order (no_po TEXT, id_suplier TEXT, matauang TEXT)',
    "INSERT INTO tr_receive_invoice_ap_header VALUES ('INV-AP-20241200004','JIC24833','JEIL INDUSTRIAL CO.LTD','S1',11,'2024-12-01'), ('IDR','LOCAL/001','LOCAL SUPPLIER','S2',0,'2024-12-02'), ('MISSING','EMPTY','NO PO','S3',0,'2024-12-03'), ('MIXED','MIX','MULTI','S1',0,'2024-12-04'), ('ESCAPED','LITERAL_100%','HTML','S4',0,'2024-12-05')",
    "INSERT INTO tr_receive_invoice_ap_detail VALUES ('INV-AP-20241200004','INC1',1000), ('IDR','INC2',2000), ('MISSING','INC3',0), ('MIXED','INC1',100), ('MIXED','INC4',200), ('ESCAPED','INC5',100)",
    "INSERT INTO tr_incoming VALUES ('INC1','S1'),('INC2','S2'),('INC3','S3'),('INC4','S1'),('INC5','S4')",
    "INSERT INTO dt_incoming VALUES ('INC1','REUSED','M1'),('INC1','REUSED','M1'),('INC2','LOCAL','M2'),('INC4','VALID_IDR','M4'),('INC5','HTML','M5')",
    "INSERT INTO dt_trans_po VALUES ('REUSED','M1','USD_PO'),('REUSED','WRONG_MATERIAL','WRONG_MATERIAL_PO'),('REUSED','M1','OTHER_SUPPLIER'),('LOCAL','M2','IDR_PO'),('VALID_IDR','M4','IDR_S1'),('HTML','M5','HTML_PO')",
    "INSERT INTO tr_purchase_order VALUES ('USD_PO','S1',' usd '),('IDR_PO','S2','IDR'),('WRONG_MATERIAL_PO','S1','IDR'),('OTHER_SUPPLIER','S2','IDR'),('IDR_S1','S1','IDR'),('HTML_PO','S4','<x>')"
) as $sql) $db->pdo->exec($sql);
if ($mysqlCollation && in_array('--benchmark', $argv, true)) {
    // Temporary fixture indexes mirror the optional production index script.
    foreach (array(
        'CREATE INDEX idx_riap_header_created ON tr_receive_invoice_ap_header (created_date(40), id_rec_inv_ap(80))',
        'CREATE INDEX idx_riap_header_id ON tr_receive_invoice_ap_header (id_rec_inv_ap(80))',
        'CREATE INDEX idx_riap_detail_invoice ON tr_receive_invoice_ap_detail (id_rec_inv_ap(80))',
        'CREATE INDEX idx_riap_incoming_header ON tr_incoming (id_incoming(80))',
        'CREATE INDEX idx_riap_incoming_detail ON dt_incoming (id_incoming(80))',
        'CREATE INDEX idx_riap_po_detail ON dt_trans_po (id_dt_po(80), idmaterial(80))',
        'CREATE INDEX idx_riap_po_header ON tr_purchase_order (no_po(80))'
    ) as $sql) $db->pdo->exec($sql);
}
$old = $db->query("SELECT GROUP_CONCAT(DISTINCT UPPER(TRIM(po.matauang))) AS currencies
    FROM tr_receive_invoice_ap_detail d JOIN dt_incoming inc ON inc.id_incoming = d.id_incoming
    JOIN dt_trans_po detail_po ON detail_po.id_dt_po = inc.id_dt_po
    JOIN tr_purchase_order po ON po.no_po = detail_po.no_po
    WHERE d.id_rec_inv_ap = 'INV-AP-20241200004'")->row();
check(strpos($old->currencies, 'IDR') !== false && strpos($old->currencies, 'USD') !== false, 'Fixture must reproduce the old false mixed-currency lookup');
$model = new Receive_invoice_ap_model();
$model->db = $db;
$all = $model->get_index_data('', 0, 10);
check($all['total'] === 5 && $all['filtered'] === 5 && count($all['rows']) === 5, 'Invoice cardinality changed');
foreach (array(
    'INV-AP-20241200004' => 'US$ 1,011.00',
    'JIC24833' => 'US$ 1,011.00',
    'JEIL' => 'US$ 1,011.00',
    'US$ 1,011.00' => 'US$ 1,011.00',
    '1011' => 'US$ 1,011.00',
    'Rp 2,000.00' => 'Rp 2,000.00',
    'LITERAL_100%' => '<X> 100.00',
    'MISSING' => '0.00 (mata uang belum tersedia)'
) as $term => $expected) {
    $result = $model->get_index_data($term, 0, 10);
    check($result['total'] === 5 && $result['filtered'] === 1, 'Search/count failed: ' . $term);
    check(count($result['rows']) === 1 && $result['rows'][0]->nominal_display === $expected, 'Wrong currency/amount: ' . $term);
}
$none = $model->get_index_data("' OR 1=1 --", 0, 10);
check($none['filtered'] === 0 && !$none['rows'], 'Search injection or no-match failure');
foreach (array('%', '_') as $literal) {
    $literalResult = $model->get_index_data($literal, 0, 10);
    check($literalResult['filtered'] === 1 && $literalResult['rows'][0]->id_rec_inv_ap === 'ESCAPED', 'LIKE wildcard must be searched literally');
}
$page = $model->get_index_data('', 1, 1, array('column' => 4, 'dir' => 'desc'));
check(count($page['rows']) === 1 && $page['rows'][0]->id_rec_inv_ap === 'INV-AP-20241200004', 'Numeric sorting/pagination failed');
$pageHeaders = $model->get_index_data('', 1, 1, array('column' => 1, 'dir' => 'asc'));
check(count($pageHeaders['rows']) === 1 && $pageHeaders['rows'][0]->id_rec_inv_ap === 'IDR'
    && $pageHeaders['filtered'] === 5, 'Header-first pagination must preserve offset and counts');
check(count($model->get_index_data('', 0, -1)['rows']) === 5, 'All entries failed');
$model->get_index_data('', 0, 10, array('column' => '99; DROP TABLE x', 'dir' => 'injected'));
$mixed = $model->get_index_data('MIXED', 0, 10);
check(strpos($mixed['rows'][0]->nominal_display, 'Rp') !== false && strpos($mixed['rows'][0]->nominal_display, 'US$') !== false, 'Genuine mixed currencies must not be hidden');

class InvoiceIndexInput
{
    public function post($key)
    {
        $input = array('draw' => 3, 'start' => 0, 'length' => 10, 'search' => array('value' => 'ESCAPED'), 'order' => array());
        return $input[$key];
    }
}
class InvoiceIndexAuth { public function restrict($permission) {} }
$controller = (new ReflectionClass('Receive_invoice_ap'))->newInstanceWithoutConstructor();
$controller->auth = new InvoiceIndexAuth();
$controller->input = new InvoiceIndexInput();
$controller->Receive_invoice_ap_model = $model;
ob_start(); $controller->get_data_incoming(); $json = json_decode(ob_get_clean(), true);
check($json['draw'] === 3 && $json['recordsTotal'] === 5 && $json['recordsFiltered'] === 1, 'DataTables contract failed');
check($json['data'][0]['nominal_invoice'] === '&lt;X&gt; 100.00', 'HTML currency escaping failed');
foreach (array('EUR' => '€', 'JPY' => '¥', 'GBP' => '£', 'CNY' => 'CN¥') as $code => $symbol) {
    $db->pdo->exec("UPDATE tr_purchase_order SET matauang = '" . $code . "' WHERE no_po = 'HTML_PO'");
    $unicode = $model->get_index_data($symbol . ' 100.00', 0, 10);
    check($unicode['filtered'] === 1 && $unicode['rows'][0]->nominal_display === $symbol . ' 100.00',
        'Unicode symbol/search failed for ' . $code);
}
echo 'PASS: ' . ($mysqlCollation ? 'MariaDB latin1 tables / UTF-8 connection' : 'SQLite fixtures')
    . "; ID/invoice/supplier/nominal search, counts, paging, sorting, duplicate PO IDs, material/supplier guards, PPN, missing/mixed currencies, escaping and injection checks\n";

if ($mysqlCollation && in_array('--benchmark', $argv, true)) {
    foreach ($argv as $arg) {
        if (strpos($arg, '--baseline-file=') === 0) {
            $baseline = file_get_contents(substr($arg, strlen('--baseline-file=')));
            eval('?>' . str_replace('class Receive_invoice_ap_model ', 'class Receive_invoice_ap_baseline ', $baseline));
        }
    }
    for ($batch = 0; $batch < 20; $batch++) {
        $headers = $details = $incoming = $incomingDetails = $poDetails = $poHeaders = array();
        for ($n = 0; $n < 250; $n++) {
            $id = 'BENCH-' . sprintf('%06d', $batch * 250 + $n);
            $headers[] = "('$id','INVOICE-$id','BENCH SUPPLIER','S1',11,'2020-01-01')";
            $details[] = "('$id','$id',1000)";
            $incoming[] = "('$id','S1')";
            $incomingDetails[] = "('$id','$id','M1')";
            $poDetails[] = "('$id','M1','$id')";
            $poHeaders[] = "('$id','S1','USD')";
        }
        foreach (array('tr_receive_invoice_ap_header' => $headers, 'tr_receive_invoice_ap_detail' => $details,
            'tr_incoming' => $incoming, 'dt_incoming' => $incomingDetails,
            'dt_trans_po' => $poDetails, 'tr_purchase_order' => $poHeaders) as $table => $rows) {
            $db->pdo->exec('INSERT INTO ' . $table . ' VALUES ' . implode(',', $rows));
        }
    }
    $models = array('optimized' => $model);
    if (class_exists('Receive_invoice_ap_baseline')) {
        $models = array('baseline' => new Receive_invoice_ap_baseline()) + $models;
        $models['baseline']->db = $db;
    }
    foreach (array('index_100' => array('', 0, 100), 'search_id' => array('INV-AP-20241200004', 0, 100),
        'search_currency' => array('US$', 0, 100), 'sort_amount' => array('', 0, 100, array('column' => 4, 'dir' => 'desc'))) as $scenario => $args) {
        if (in_array('--benchmark-index-only', $argv, true) && $scenario !== 'index_100') continue;
        $reference = null;
        foreach ($models as $label => $candidate) {
            if ($label === 'baseline' && !in_array($scenario, array('index_100', 'search_id'), true)) continue;
            call_user_func_array(array($candidate, 'get_index_data'), $args);
            $times = array();
            for ($run = 0; $run < 3; $run++) {
                $begin = microtime(true);
                $result = call_user_func_array(array($candidate, 'get_index_data'), $args);
                $times[] = round((microtime(true) - $begin) * 1000, 2);
            }
            $signature = array($result['total'], $result['filtered']);
            foreach ($result['rows'] as $row) $signature[] = array($row->id_rec_inv_ap, $row->nominal_display);
            if ($reference === null) $reference = $signature;
            else check($signature === $reference, 'Benchmark result differs from baseline: ' . $scenario);
            sort($times);
            echo $scenario . ' ' . $label . ' median_ms=' . $times[1] . ' samples=' . implode(',', $times) . PHP_EOL;
            if ($label === 'optimized') check($times[1] < 3000, 'Fixture performance exceeds 3 seconds');
        }
    }
    $db->queries = array();
    $model->get_index_data('', 0, 10);
    check(count($db->queries) === 2, 'Ordinary page must use count + one page query');
    check(strpos($db->queries[1], 'LIMIT 10 OFFSET 0) h') !== false, 'Header pagination must precede detail lookups');
    foreach ($db->query('EXPLAIN ' . $db->queries[1])->result() as $plan) {
        if (in_array($plan->table, array('amount_detail', 'd', 'incoming', 'inc', 'detail_po', 'po'), true)) {
            check($plan->type !== 'ALL', 'Unindexed detail scan: ' . $plan->table);
            echo 'PLAN ' . $plan->table . ' type=' . $plan->type . ' key=' . $plan->key . PHP_EOL;
        }
    }
}
