<?php
// Standalone PHP 5.6/MySQL 5.7 regression runner; uses connection-local TEMPORARY tables only.
// Required environment: REPORT_TEST_HOST, REPORT_TEST_USER, REPORT_TEST_PASSWORD, REPORT_TEST_DATABASE.
if (PHP_SAPI !== 'cli') exit('CLI only');
define('BASEPATH', __DIR__);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class BF_Model
{
    public $db;
    public function __construct($db) { $this->db = $db; }
}
class Admin_Controller {}
class ReportTestInput
{
    private $input;
    public function __construct($input) { $this->input = $input; }
    public function post($key = null, $xss = false) { return $this->input; }
}
class ReportTestAuth
{
    public $permission;
    public function restrict($permission) { $this->permission = $permission; }
}
class ReportTestOutput
{
    public $json;
    public $type;
    public function set_content_type($type) { $this->type = $type; return $this; }
    public function set_output($json) { $this->json = $json; return $this; }
}
class ReportTestResult
{
    private $result;
    public function __construct($result) { $this->result = $result; }
    public function result_array()
    {
        $rows = array();
        while ($row = $this->result->fetch_assoc()) $rows[] = $row;
        return $rows;
    }
    public function row_array() { return $this->result->fetch_assoc(); }
}
class ReportTestDatabase
{
    public $connection;
    public $queries = array();
    public $temporaryFixture = true;
    public $transactionDepth = 0;
    public $failWhen = null;
    public function __construct()
    {
        foreach (array('HOST', 'USER', 'PASSWORD', 'DATABASE') as $key) {
            if (getenv('REPORT_TEST_'.$key) === false) throw new RuntimeException('Missing REPORT_TEST_'.$key);
        }
        $this->connection = new mysqli(getenv('REPORT_TEST_HOST'), getenv('REPORT_TEST_USER'),
            getenv('REPORT_TEST_PASSWORD'), getenv('REPORT_TEST_DATABASE'));
        $this->connection->set_charset('utf8');
    }
    public function trans_start() { $this->transactionDepth++; $this->connection->begin_transaction(); }
    public function trans_complete() { $this->connection->commit(); $this->transactionDepth--; }
    public function query($sql, $bindings = array())
    {
        if ($this->failWhen !== null && strpos($sql, $this->failWhen) !== false) throw new RuntimeException('Injected batch query failure');
        // MySQL 5.7 cannot reopen a TEMPORARY table in one statement. Identical
        // connection-local copies stand in for the extra aliases of the real detail table.
        if ($this->temporaryFixture) {
            $sql = str_replace('dt_spkmarketing seq', 'fixture_sequence seq', $sql);
            $sql = str_replace('FROM dt_spkmarketing WHERE', 'FROM fixture_totals WHERE', $sql);
        }
        // None of this module's SQL templates contains quoted question marks.
        // Split the template once so '?' inside bound values is never rebound.
        $parts = explode('?', $sql);
        if (count($parts) !== count($bindings) + 1) throw new RuntimeException('Binding count mismatch');
        $sql = $parts[0];
        foreach (array_values($bindings) as $i => $value) {
            $sql .= "'".$this->connection->real_escape_string((string) $value)."'".$parts[$i + 1];
        }
        $start = microtime(true);
        $result = $this->connection->query($sql);
        $this->queries[] = array('sql'=>$sql, 'ms'=>(microtime(true) - $start) * 1000);
        return new ReportTestResult($result);
    }
}
require __DIR__.'/fixtures/Report_spk_marketing_baseline.php';
require dirname(dirname(__DIR__)).'/application/modules/report_spk_marketing/models/Report_spk_marketing_model.php';
require dirname(dirname(__DIR__)).'/application/modules/report_spk_marketing/controllers/Report_spk_marketing.php';
class ReportTestCountingModel extends Report_spk_marketing_model
{
    public $countCalls = 0;
    public function count_rows($filters) { $this->countCalls++; return parent::count_rows($filters); }
}

function check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}
function same_rows($expected, $actual, $message)
{
    // SELECT field order is immaterial; row order and values are not.
    foreach ($expected as &$row) ksort($row);
    unset($row);
    foreach ($actual as &$row) ksort($row);
    unset($row);
    check($expected === $actual, $message);
}
function create_fixture($db, $headers)
{
    $schemas = array(
        'tr_spk_marketing'=>"id_spkmarketing VARCHAR(24) PRIMARY KEY, no_surat VARCHAR(100), id_customer VARCHAR(24), nama_customer VARCHAR(100), tgl_spk_marketing DATE, tgl_po DATE, plan_cust DATE, no_po VARCHAR(100), deleted VARCHAR(1), status_approve VARCHAR(1)",
        'dt_spkmarketing'=>"id INT PRIMARY KEY, id_spkmarketing VARCHAR(24), id_child_penawaran VARCHAR(24), id_material VARCHAR(24), thickness DECIMAL(12,4), width DECIMAL(12,4), length DECIMAL(12,4), qty_produk DECIMAL(12,4), delivery DATE, deal VARCHAR(1), deleted VARCHAR(1), KEY fixture_spk (id_spkmarketing)",
        'master_customers'=>"id_customer VARCHAR(24) PRIMARY KEY, name_customer VARCHAR(100)",
        'child_penawaran'=>"id_child_penawaran VARCHAR(24) PRIMARY KEY, id_category3 VARCHAR(24)",
        'ms_inventory_category3'=>"id_category3 VARCHAR(24) PRIMARY KEY, id_category2 VARCHAR(24), id_surface VARCHAR(24), spek VARCHAR(24), hardness VARCHAR(24), id_bentuk VARCHAR(24)",
        'ms_inventory_category2'=>"id_category2 VARCHAR(24) PRIMARY KEY, nama VARCHAR(100)",
        'ms_surface'=>"id_surface VARCHAR(24) PRIMARY KEY, nm_surface VARCHAR(100)"
    );
    foreach ($schemas as $table => $schema) $db->connection->query('CREATE TEMPORARY TABLE '.$table.' ('.$schema.') ENGINE=InnoDB');
    $db->connection->query("INSERT INTO master_customers VALUES ('C1','Customer Alpha'),('C2',''),('C3','Customer Gamma')");
    $db->connection->query("INSERT INTO child_penawaran VALUES ('P1','M1'),('P2',''),('P3',NULL)");
    $db->connection->query("INSERT INTO ms_inventory_category3 VALUES ('M1','G1','S1','AL 1100','H14','B2000001'),('M2','G2','S2','AL 5052','H32','B2')");
    $db->connection->query("INSERT INTO ms_inventory_category2 VALUES ('G1','Coil'),('G2','Sheet')");
    $db->connection->query("INSERT INTO ms_surface VALUES ('S1','Mill finish'),('S2','Brushed')");
    $header = $db->connection->prepare('INSERT INTO tr_spk_marketing VALUES (?,?,?,?,?,?,?,?,?,?)');
    $detail = $db->connection->prepare('INSERT INTO dt_spkmarketing VALUES (?,?,?,?,?,?,?,?,?,?,?)');
    $id = 0;
    for ($n = 1; $n <= $headers; $n++) {
        $spk = sprintf('SPK%06d', $n); $number = 'MKT/SPK/26/'.$n;
        $customer = 'C'.($n % 4 + 1); $fallback = 'Historical '.$n;
        $date = '2026-10-'.sprintf('%02d', $n % 28 + 1); $poDate = '2026-09-01'; $plan = '2026-11-01';
        $po = $n === 1 ? "PO_%!'literal" : 'PO-'.$n;
        $deleted = $n % 9 === 0 ? '1' : ($n % 2 ? null : '0');
        $approval = $n % 3 === 0 ? null : ($n % 3 === 1 ? '1' : '0');
        $header->bind_param('ssssssssss', $spk, $number, $customer, $fallback, $date, $poDate, $plan, $po, $deleted, $approval);
        $header->execute();
        for ($j = 1; $j <= 12; $j++) {
            $id++; $child = $j % 4 === 0 ? 'missing' : 'P'.($j % 3 + 1);
            $material = $j % 5 === 0 ? 'missing' : 'M2';
            $thickness = ($j % 4 + 1) / 10; $width = 100 + $j; $length = 50 + $n % 10;
            $qty = $j % 5 === 0 ? null : ($n % 13 * 7.25 + $j / 10);
            $delivery = $j % 4 === 0 ? null : '2026-11-'.sprintf('%02d', $j);
            $deal = $j === 3 ? '0' : '1'; $detailDeleted = $j === 5 ? '1' : ($j % 2 ? null : '0');
            $detail->bind_param('isssdddssss', $id, $spk, $child, $material, $thickness, $width, $length, $qty, $delivery, $deal, $detailDeleted);
            $detail->execute();
        }
    }
    foreach (array('fixture_sequence', 'fixture_totals') as $copy) {
        $db->connection->query('CREATE TEMPORARY TABLE '.$copy.' LIKE dt_spkmarketing');
        $db->connection->query('INSERT INTO '.$copy.' SELECT * FROM dt_spkmarketing');
    }
}
function median($values) { sort($values); return $values[(int) floor(count($values) / 2)]; }
function benchmark($model, $db)
{
    $filters = $model->normalize_filters(array());
    $all = $model->normalize_filters(array('status'=>'all'));
    $db->queries = array();
    $start = microtime(true);
    $rows = $model->get_rows($filters, 0, 25, 2, 'desc');
    $dataMs = (microtime(true) - $start) * 1000;
    $start = microtime(true); $total = $model->count_rows($all); $totalMs = (microtime(true) - $start) * 1000;
    $start = microtime(true); $filtered = $model->count_rows($filters); $filteredMs = (microtime(true) - $start) * 1000;
    return array('data'=>$dataMs, 'total'=>$totalMs, 'filtered'=>$filteredMs,
        'combined'=>$dataMs + $totalMs + $filteredMs, 'queries'=>count($db->queries));
}

try {
    $db = new ReportTestDatabase();
    $old = new Report_spk_marketing_baseline($db);
    $new = new Report_spk_marketing_model($db);
    $isBenchmark = in_array('--benchmark', $argv, true);
    $existingData = in_array('--existing-data', $argv, true);
    check(!$existingData || $isBenchmark, '--existing-data is allowed only with --benchmark (read-only)');
    $db->temporaryFixture = !$existingData;
    if (!$existingData) create_fixture($db, $isBenchmark ? 2000 : 15);
    if (!$isBenchmark) {
        foreach (array('dt_spkmarketing','fixture_totals','fixture_sequence') as $table) {
            $db->connection->query("UPDATE ".$table." SET id_spkmarketing = LOWER(id_spkmarketing) WHERE id_spkmarketing = 'SPK000001'");
            $db->connection->query("UPDATE ".$table." SET qty_produk = NULL WHERE id_spkmarketing = 'SPK000002'");
        }
    }
    $filters = $new->normalize_filters(array());
    $db->queries = array(); $new->count_rows($filters);
    check(stripos($db->queries[0]['sql'], 'SUM(') === false, 'Count query still aggregates quantity across all SPKs');
    $db->queries = array(); $new->get_rows($filters, 0, 25, 2, 'desc');
    check(stripos($db->queries[0]['sql'], 'SUM(') === false && stripos($db->queries[0]['sql'], 'SELECT COUNT(*)') === false,
        'Ordinary page query still calculates aggregates before LIMIT');
    check(count($db->queries) <= 3, 'Page enrichment must use bounded batch queries, not N+1');
    echo "PASS: count query and paginated data avoid unnecessary global aggregates\n";

    if (!$isBenchmark) {
        $cases = array(array(), array('status'=>'all'), array('status'=>'unapproved'),
            array('start_date'=>'2026-10-04','end_date'=>'2026-10-09'), array('customer_id'=>'C2'),
            array('customer_id'=>'C4'), array('search'=>"_%!'"), array('search'=>'SPK/26/1'), array('search'=>'not found'));
        $comparisons = 0;
        foreach ($cases as $input) {
            $filters = $new->normalize_filters($input);
            check($old->count_rows($filters) === $new->count_rows($filters), 'Count mismatch: '.json_encode($input));
            for ($column = 0; $column < 17; $column++) foreach (array('asc','desc') as $direction) {
                foreach (array(0, 7) as $offset) {
                    $expected = $old->get_rows($filters, $offset, 7, $column, $direction);
                    $actual = $new->get_rows($filters, $offset, 7, $column, $direction);
                    same_rows($expected, $actual, 'Rows mismatch: '.json_encode(array($input,$column,$direction,$offset)));
                    check(array_map(array($old,'format_row'), $expected) === array_map(array($new,'format_row'), $actual), 'Formatted rows mismatch');
                    $comparisons++;
                }
                same_rows($old->get_rows($filters, 0, null, $column, $direction), $new->get_rows($filters, 0, null, $column, $direction), 'Export query mismatch');
            }
        }
        echo 'PASS: '.$comparisons." paginated comparisons, all 17 sort columns/both directions, and 306 export comparisons\n";
        $filters = $new->normalize_filters(array('status'=>'all'));
        foreach (array(-1, 0, 101) as $length) same_rows($old->get_rows($filters, -1, $length), $new->get_rows($filters, -1, $length), 'Pagination bounds mismatch');
        $db->queries = array();
        check($new->get_rows($filters, 100000, 25) === array(), 'Out-of-range page must be empty');
        check(count($db->queries) === 1, 'Empty page must not issue enrichment queries');
        check($db->transactionDepth === 0, 'Read transaction left open after an empty page');
        $db->failWhen = 'SELECT page.id';
        try {
            $new->get_rows($filters, 0, 25);
            throw new RuntimeException('Batch failure was not exercised');
        } catch (RuntimeException $e) {
            check($e->getMessage() === 'Injected batch query failure', 'Unexpected batch failure');
            check($db->transactionDepth === 0, 'Read transaction left open after a batch exception');
        }
        $db->failWhen = null;
        foreach (array(array('status'=>'all'), array('status'=>'approved'), array('status'=>'all','search'=>'SPK/26/1')) as $input) {
            $model = new ReportTestCountingModel($db);
            $reflection = new ReflectionClass('Report_spk_marketing');
            $controller = $reflection->newInstanceWithoutConstructor();
            $controller->report_model = $model;
            $controller->input = new ReportTestInput(array_merge($input, array('draw'=>9,'start'=>0,'length'=>7)));
            $controller->auth = new ReportTestAuth();
            $controller->output = new ReportTestOutput();
            $controller->get_data();
            $json = json_decode($controller->output->json, true);
            $applied = $new->normalize_filters($input);
            $all = $new->normalize_filters(array('status'=>'all'));
            check($json['draw'] === 9 && $controller->output->type === 'application/json', 'DataTables response contract changed');
            check($json['recordsTotal'] === $old->count_rows($all) && $json['recordsFiltered'] === $old->count_rows($applied), 'Controller count mismatch');
            check($model->countCalls === ($applied === $all ? 1 : 2), 'Controller repeated an identical count');
            check($controller->auth->permission === 'Report_SPK_Marketing.View', 'Permission contract changed');
            $expected = array();
            foreach ($old->get_rows($applied, 0, 7) as $row) {
                $formatted = $old->format_row($row);
                foreach ($formatted as &$value) $value = is_float($value) ? number_format($value, 2, ',', '.') : htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
                unset($value);
                $expected[] = $formatted;
            }
            check($json['data'] === $expected, 'Controller escaped/formatted data changed');
        }
        $controller->input = new ReportTestInput(array('start_date'=>'2026-10-09','end_date'=>'2026-10-01'));
        $controller->get_data(); $json = json_decode($controller->output->json, true);
        check(isset($json['error']) && $json['data'] === array(), 'Invalid filter response changed');
        echo "PASS: controller count reuse, permissions, DataTables JSON, formatting and validation\n";
        require dirname(dirname(__DIR__)).'/application/libraries/PHPExcel.php';
        require dirname(dirname(__DIR__)).'/application/modules/report_spk_marketing/libraries/Report_spk_marketing_excel.php';
        $exporter = new Report_spk_marketing_excel();
        $oldBook = $exporter->build($old->headers(), array_map(array($old,'format_row'), $old->get_rows($filters)));
        $newBook = $exporter->build($new->headers(), array_map(array($new,'format_row'), $new->get_rows($filters)));
        check($oldBook->getActiveSheet()->toArray() === $newBook->getActiveSheet()->toArray(), 'Excel cell values changed');
        check($oldBook->getActiveSheet()->getMergeCells() === $newBook->getActiveSheet()->getMergeCells(), 'Excel header merges changed');
        check($newBook->getActiveSheet()->getFreezePane() === 'C3', 'Excel frozen pane changed');
        foreach ($oldBook->getActiveSheet()->getCellCollection() as $coordinate) {
            check($oldBook->getActiveSheet()->getCell($coordinate)->getDataType() === $newBook->getActiveSheet()->getCell($coordinate)->getDataType(), 'Excel cell type changed: '.$coordinate);
        }
        $xlsx = tempnam(sys_get_temp_dir(), 'report-spk-test-');
        try {
            PHPExcel_IOFactory::createWriter($newBook, 'Excel2007')->save($xlsx);
            $loaded = PHPExcel_IOFactory::load($xlsx);
            $expectedSheet = $newBook->getActiveSheet(); $actualSheet = $loaded->getActiveSheet();
            check((int) $actualSheet->getHighestRow() === (int) $expectedSheet->getHighestRow() && $actualSheet->getHighestColumn() === 'Q',
                'XLSX dimensions changed: '.json_encode(array($expectedSheet->getHighestRow(),$actualSheet->getHighestRow(),$actualSheet->getHighestColumn())));
            foreach ($expectedSheet->getCellCollection() as $coordinate) {
                $expectedCell = $expectedSheet->getCell($coordinate); $actualCell = $actualSheet->getCell($coordinate);
                check($expectedCell->getValue() == $actualCell->getValue(), 'XLSX cell value mismatch: '.$coordinate);
                // PHPExcel reads explicitly empty strings back as null cells.
                if ($expectedCell->getValue() !== '' && $expectedCell->getValue() !== null) {
                    check($expectedCell->getDataType() === $actualCell->getDataType(), 'XLSX cell type mismatch: '.$coordinate);
                }
                check($expectedCell->getStyle()->getNumberFormat()->getFormatCode() === $actualCell->getStyle()->getNumberFormat()->getFormatCode(), 'XLSX number/date format mismatch: '.$coordinate);
            }
            $loaded->disconnectWorksheets();
        } finally { unlink($xlsx); }
        $oldBook->disconnectWorksheets(); $newBook->disconnectWorksheets();
        echo "PASS: Excel workbook comparison and XLSX write/read round trip (values, types, date/number formats)\n";
        // Unknown lookup cardinality must not cause count_rows to silently drop duplicate report rows.
        $db->connection->query('ALTER TABLE master_customers DROP PRIMARY KEY');
        $db->connection->query("INSERT INTO master_customers VALUES ('C1','Customer duplicate')");
        check($old->count_rows($filters) === $new->count_rows($filters), 'Duplicate lookup count mismatch');
        // A duplicated lookup can tie on all existing ORDER BY keys. Compare a
        // customer-ordered page so these intentionally distinct rows have an explicit order.
        same_rows($old->get_rows($filters, 0, 100, 1, 'asc'), $new->get_rows($filters, 0, 100, 1, 'asc'), 'Duplicate lookup row mismatch');
        echo "PASS: pagination bounds and duplicate lookup cardinality preserved\n";
    } else {
        foreach (array(array(), array('status'=>'all')) as $input) {
            $filters = $new->normalize_filters($input);
            same_rows($old->get_rows($filters, 0, 25), $new->get_rows($filters, 0, 25), 'Benchmark default-page correctness mismatch');
            check($old->count_rows($filters) === $new->count_rows($filters), 'Benchmark count correctness mismatch');
        }
        // Warm both paths, then alternate measurement order to reduce cache/order bias.
        benchmark($old, $db); benchmark($new, $db);
        $samples = array('old'=>array(), 'new'=>array());
        for ($i = 0; $i < 5; $i++) foreach ($i % 2 ? array('new','old') : array('old','new') as $name) {
            $samples[$name][] = benchmark($name === 'old' ? $old : $new, $db);
        }
        echo ($existingData ? 'Existing database (read-only)' : 'Synthetic fixture: 2,000 SPKs / 24,000 details')."; warm-cache, alternating order, five samples per path\n";
        foreach (array('data','total','filtered','combined') as $metric) {
            $oldValues = array_column($samples['old'], $metric); $newValues = array_column($samples['new'], $metric);
            printf("%s: old %.3f ms [%.3f..%.3f], new %.3f ms [%.3f..%.3f]\n", $metric,
                median($oldValues), min($oldValues), max($oldValues), median($newValues), min($newValues), max($newValues));
        }
        printf("SQL queries per default load: old %d, new %d\n", $samples['old'][0]['queries'], $samples['new'][0]['queries']);
        $oldCombined = array_column($samples['old'], 'combined'); $newCombined = array_column($samples['new'], 'combined');
        check(max($newCombined) < min($oldCombined), 'Performance improvement not separated from sample variation; investigate before accepting');
        echo "PASS: all new combined samples faster than all baseline samples\n";
        echo ($existingData ? 'Index metadata (existing database)' : 'Index metadata (fixture only; NOT production)').":\n";
        foreach (array('tr_spk_marketing','dt_spkmarketing') as $table) {
            echo $table.': '.json_encode($db->query('SHOW INDEX FROM '.$table)->result_array())."\n";
        }
        foreach (array('old','new') as $name) {
            benchmark($name === 'old' ? $old : $new, $db); $queries = $db->queries;
            foreach ($queries as $i => $query) echo 'EXPLAIN '.$name.' #'.($i + 1).': '.json_encode($db->query('EXPLAIN '.$query['sql'])->result_array())."\n";
        }
    }
} catch (Exception $e) {
    fwrite(STDERR, 'FAIL: '.$e->getMessage()."\n");
    exit(1);
}
