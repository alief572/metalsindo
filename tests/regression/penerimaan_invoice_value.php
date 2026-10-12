<?php
// Run: php tests/regression/penerimaan_invoice_value.php [project-root]
if (PHP_SAPI !== 'cli') exit;
define('BASEPATH', __DIR__);
class Admin_Controller { public $db; public $input; }
$root = isset($argv[1]) ? $argv[1] : dirname(dirname(__DIR__));
require $root . '/application/modules/penerimaan/controllers/Penerimaan.php';

class InvoiceValueResult
{
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function row() { return isset($this->rows[0]) ? $this->rows[0] : null; }
    public function result() { return $this->rows; }
}

class InvoiceValueDatabase
{
    private $table;
    private $header;
    private $sheet;
    private $subtotal;
    private $selection;
    private $paid;
    public function __construct($header, $sheet, $subtotal, $paid = 0)
    {
        $this->header = (object) $header;
        $this->sheet = $sheet;
        $this->subtotal = $subtotal;
        $this->paid = $paid;
    }
    public function select($selection) { $this->selection = $selection; return $this; }
    public function from($table) { $this->table = $table; return $this; }
    public function __call($name, $args) { return $this; }
    public function count_all_results() { return 1; }
    public function escape($value) { return "'" . $value . "'"; }
    private function invoiceRow()
    {
        return (object) array('type' => 'invoice', 'code' => 'TEST', 'no_invoice' => 'TEST',
            'no_surat' => 'INV-TEST', 'nm_customer' => 'Test customer', 'no_do' => 'DO-TEST',
            'id_do' => 'DO-ID', 'printed_on' => '2026-08-04', 'total_invoice_idr' => 0, 'sisa' => 0);
    }
    public function query($sql, $bindings = array())
    {
        if (strpos($sql, 'SUM(total_bayar_idr)') !== false) {
            return new InvoiceValueResult(array((object) array('total_paid' => $this->paid)));
        }
        if (strpos($sql, 'COUNT(*)') !== false) {
            return new InvoiceValueResult(array((object) array('total' => 1)));
        }
        return new InvoiceValueResult(array($this->invoiceRow()));
    }
    public function get()
    {
        if ($this->table === 'tr_invoice a') {
            if ($this->selection === 'a.*, b.name_customer as nm_customer') {
                return new InvoiceValueResult(array($this->invoiceRow()));
            }
            return new InvoiceValueResult(array($this->header));
        }
        if ($this->table === 'stock_material a') {
            return new InvoiceValueResult(array((object) array('qty_sheet' => 2)));
        }
        // The first detail query joins inventory; the second selects the sum.
        $rows = $this->detailQuery++ === 0
            ? ($this->sheet ? array((object) array('id_category3' => 'TEST', 'harga_satuan' => $this->subtotal / 2)) : array())
            : array((object) array('ttl_harga' => $this->subtotal));
        return new InvoiceValueResult($rows);
    }
    private $detailQuery = 0;
}

$taxed = array('facility' => '', 'ppn' => 11, 'nilai_ppn' => 11);
$bonded = array('facility' => 'Kawasan Berikat', 'ppn' => 11, 'nilai_ppn' => 11);
$cases = array(
    array('0849 bonded invoice', $bonded, false, 12668250, 12668250),
    array('0848 bonded invoice', $bonded, false, 224217000, 224217000),
    array('bonded sheet', $bonded, true, 1000, 1000),
    array('facility case insensitive', array('facility' => 'Fasilitas KAWASAN BERIKAT', 'ppn' => 11, 'nilai_ppn' => 11), false, 1000, 1000),
    array('explicit zero tax non-sheet', array('facility' => '', 'ppn' => '0', 'nilai_ppn' => '0.00'), false, 1000, 1000),
    array('explicit zero tax sheet', array('facility' => '', 'ppn' => 0, 'nilai_ppn' => 0), true, 1000, 1000),
    array('taxable non-sheet rounding', $taxed, false, 1000, 1110.04),
    array('taxable sheet rounding', $taxed, true, 1000, 1110.04),
    array('null tax is not explicit zero', array('facility' => '', 'ppn' => null, 'nilai_ppn' => null), false, 1000, 1110.04),
    array('only one zero tax field', array('facility' => '', 'ppn' => 11, 'nilai_ppn' => 0), false, 1000, 1110.04)
);
$reflection = new ReflectionClass('Penerimaan');
$method = $reflection->getMethod('_calculate_real_invoice_value');
$method->setAccessible(true);
$failures = 0;
foreach ($cases as $case) {
    $controller = $reflection->newInstanceWithoutConstructor();
    $controller->db = new InvoiceValueDatabase($case[1], $case[2], $case[3]);
    $actual = $method->invoke($controller, 'TEST', 'DO-TEST', 'DO-ID');
    $ok = abs($actual - $case[4]) < 0.00001;
    echo ($ok ? 'PASS ' : 'FAIL ') . $case[0] . ': ' . $actual . PHP_EOL;
    if (!$ok) $failures++;
}

class InvoicePickerInput
{
    public function post($key)
    {
        $values = array('draw' => 1, 'start' => 0, 'length' => 10, 'search' => array(),
            'id_customer' => 'TEST', 'filter_type' => 'invoice');
        return isset($values[$key]) ? $values[$key] : null;
    }
}
$balances = array(
    array(12668250, 0, 12668250), array(12668250, 10000000, 2668250), array(12668250, 12668250, 0),
    array(224217000, 0, 224217000), array(224217000, 200000000, 24217000), array(224217000, 224217000, 0)
);
foreach (array('get_invoice_serverside', 'get_invoice_cn_serverside') as $endpoint) {
    foreach ($balances as $balance) {
        $controller = $reflection->newInstanceWithoutConstructor();
        $controller->db = new InvoiceValueDatabase($bonded, false, $balance[0], $balance[1]);
        $controller->input = new InvoicePickerInput();
        ob_start();
        $controller->$endpoint();
        $response = json_decode(ob_get_clean(), true);
        $ok = is_array($response) && isset($response['data']);
        if ($ok && $balance[2] === 0) {
            $ok = count($response['data']) === 0;
        } elseif ($ok) {
            $row = isset($response['data'][0]) ? $response['data'][0] : array();
            $sisaKey = $endpoint === 'get_invoice_serverside' ? 'sisa_invoice' : 'sisa';
            $ok = isset($row['total_invoice'], $row[$sisaKey], $row['action'])
                && $row['total_invoice'] === number_format($balance[0])
                && $row[$sisaKey] === number_format($balance[2])
                && strpos(str_replace(', ', ',', $row['action']), "'" . $balance[0] . "','" . $balance[2] . "'") !== false;
        }
        echo ($ok ? 'PASS ' : 'FAIL ') . $endpoint . ' paid=' . $balance[1] . ' expected balance=' . $balance[2] . PHP_EOL;
        if (!$ok) $failures++;
    }
}
echo count($cases) . ' value cases and 12 picker/balance cases; failures: ' . $failures . PHP_EOL;
exit($failures ? 1 : 0);
