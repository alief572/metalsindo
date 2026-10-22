<?php
// Run: php tests/regression/retur_pmb_cn_sj.php [--pdf-dir=/temporary/directory]
if (PHP_SAPI !== 'cli') exit;
define('BASEPATH', __DIR__);
define('FCPATH', dirname(dirname(__DIR__)) . '/');
date_default_timezone_set('Asia/Jakarta');
class BF_Model { public $db; public function __construct() {} }
class Admin_Controller {}
function base_url($path = '') { return 'http://localhost:8080/' . $path; }
function has_permission($permission) { return $GLOBALS['sj_permission']; }
function show_error($message, $status = 500) { throw new RuntimeException($message, $status); }
require FCPATH . 'application/modules/retur_pmb_cn/models/Retur_pmb_cn_model.php';
require FCPATH . 'application/modules/retur_pmb_cn/controllers/Retur_pmb_cn.php';

function sj_assert($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
class SjDatabase {
    public $tables;
    public $queries = array();
    private $table;
    private $filters = array();
    public function __construct($tables) { $this->tables = $tables; }
    public function select($columns) { return $this; }
    public function from($table) { $this->table = explode(' ', $table)[0]; return $this; }
    public function where($field, $value) { $this->filters[$field] = array($value); return $this; }
    public function where_in($field, $values) { $this->filters[$field] = $values; return $this; }
    public function order_by($field, $direction) { return $this; }
    public function get() {
        $rows = isset($this->tables[$this->table]) ? $this->tables[$this->table] : array();
        foreach ($this->filters as $field => $values) {
            $parts = explode('.', $field);
            $field = end($parts);
            $rows = array_values(array_filter($rows, function ($row) use ($field, $values) {
                return isset($row->$field) && in_array($row->$field, $values);
            }));
        }
        $this->queries[] = array($this->table, $this->filters);
        $this->filters = array();
        return new SjResult($rows);
    }
}
class SjResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function result() { return $this->rows; }
    public function row() { return isset($this->rows[0]) ? $this->rows[0] : null; }
}
class SjAuth {
    public function restrict($permission) {
        sj_assert($permission === 'Retur_Credit_Note.View', 'Endpoint must use View permission');
        if (!$GLOBALS['sj_permission']) throw new RuntimeException('Denied', 403);
    }
}
class SjViewCapture {
    public $data;
    public function view($name, $data, $return = false) {
        sj_assert($name === 'print_sj' && $return, 'Dedicated SJ view rendered as a string');
        $this->data = $data;
        throw new RuntimeException('SJ render reached', 200);
    }
}
function sj_html($data) {
    extract($data);
    ob_start();
    include FCPATH . 'application/modules/retur_pmb_cn/views/print_sj.php';
    return ob_get_clean();
}
function sj_fixture() {
    $dn = (object) array('id' => '7', 'no_surat' => 'DN-PMB-202610-0007', 'id_retur' => '9', 'deleted_by' => null);
    $retur = (object) array('id' => '9', 'no_surat' => 'RTR-001', 'id_supplier' => 'SUP1',
        'nm_supplier' => 'Supplier & <Steel>', 'no_po' => 'PO1, PO2', 'id_rec_inv_ap' => 'INV1',
        'tgl_retur' => '2026-09-22', 'subtotal' => 11187500, 'ppn_persen' => 11,
        'nilai_ppn' => 1230625, 'grand_total' => 12418125, 'matauang' => 'IDR', 'deleted_by' => null);
    $rows = array();
    foreach (array(437, 458) as $i => $qty) {
        $rows[] = (object) array('id' => $i + 1, 'id_header' => 'RTR-001', 'id_material' => 'MAT1',
            'no_po' => 'PO1', 'lotno' => 'LOT-' . ($i + 1), 'nama_material' => 'HOT ROLLED & <STEEL>',
            'width' => 186, 'qty_order' => 12000, 'qty_receive' => 592, 'jumlah_retur' => $qty,
            'qty_sheet' => 0, 'qty_sheet_retur' => 0, 'harga_satuan' => 12500,
            'grand_total' => $qty * 12500, 'matauang' => 'IDR');
    }
    return array('tr_dn_retur_pmb' => array($dn), 'tr_retur_pembelian' => array($retur),
        'dt_retur_pembelian' => $rows,
        'master_supplier' => array((object) array('id_suplier' => 'SUP1', 'address_office' => 'Jl. Supplier & <Office>')),
        'tr_purchase_order' => array(
            (object) array('no_po' => 'PO1', 'no_surat' => 'PO-MP/26/01', 'tanggal' => '2026-04-01', 'matauang' => 'IDR'),
            (object) array('no_po' => 'PO2', 'no_surat' => 'PO-MP/26/02', 'tanggal' => '2026-04-02', 'matauang' => 'IDR')),
        'ms_inventory_category3' => array((object) array('id_category3' => 'MAT1', 'id_bentuk' => 'B2000001')));
}
$tables = sj_fixture();
$model = new Retur_pmb_cn_model();
$model->db = new SjDatabase($tables);
sj_assert(method_exists($model, 'get_sj_print_data'), 'SJ print data loader must exist');
$data = $model->get_sj_print_data($tables['tr_dn_retur_pmb'][0], $tables['tr_retur_pembelian'][0]);
sj_assert(count($data['detail']) === 2, 'Two lots of the same material must remain separate');
sj_assert($data['total_receive'] === 1184.0 && $data['total_retur'] === 895.0, 'Quantity totals match reference');
sj_assert(count($model->db->queries) === 4, 'Detail, supplier, PO and material fetched once each');
$html = sj_html($data);
foreach (array('SURAT JALAN', 'DN-PMB-202610-0007', 'Supplier &amp; &lt;Steel&gt;', 'LOT-1', 'LOT-2',
    'PO-MP/26/01', 'PO-MP/26/02', '01-Apr-2026', '02-Apr-2026', '437.00 KGS', '458.00 KGS',
    'Total Qty', '1,184.00', '895.00', 'Cikarang,22-09-2026',
    'STEMPEL/NAMA JELAS', 'POS JAGA', 'GUDANG JADI') as $expected) {
    sj_assert(strpos($html, $expected) !== false, 'Missing print content: ' . $expected);
}
sj_assert(strpos($html, '<STEEL>') === false && strpos($html, '<Office>') === false, 'Escape stored text');
$last = 0;
foreach (array('Tgl Incoming', 'Lot Number', 'Nama Material', 'Width', 'Qty Order', 'Qty Rec', 'Qty Retur') as $column) {
    $position = strpos($html, '>' . $column . '<');
    sj_assert($position !== false && $position > $last, 'Column order: ' . $column);
    $last = $position;
}
sj_assert(strpos($html, '<thead>') !== false && strpos($html, '<nobreak>') !== false, 'Repeated table heading and intact signature block');
sj_assert(strpos($html, 'Supir') === false && strpos($html, 'No Kendaraan') === false, 'No driver fields');
foreach (array('Rincian Material yang Diretur', 'Harga Satuan', 'Total Harga', 'Subtotal', 'PPN', 'Grand Total', 'Rp ', '/Kg', '/Sheet') as $removed) {
    sj_assert(strpos($html, $removed) === false, 'Excluded from SJ: ' . $removed);
}
sj_assert(substr_count($html, '<th style=') === 7, 'SJ contains seven quantity-only columns');

$zero = sj_fixture();
$zero['tr_retur_pembelian'][0]->subtotal = 0;
$zero['tr_retur_pembelian'][0]->nilai_ppn = 0;
$zero['tr_retur_pembelian'][0]->ppn_persen = 0;
$zero['tr_retur_pembelian'][0]->grand_total = 0;
$zero['tr_retur_pembelian'][0]->tgl_retur = '0000-00-00';
$zero['ms_inventory_category3'][0]->id_bentuk = 'B2000002';
$zero['dt_retur_pembelian'][0]->qty_sheet = 10;
$zero['dt_retur_pembelian'][0]->qty_sheet_retur = 3;
$zero['dt_retur_pembelian'][0]->grand_total = 0;
$zero['dt_retur_pembelian'][0]->width = null;
$model->db = new SjDatabase($zero);
$edge = $model->get_sj_print_data($zero['tr_dn_retur_pmb'][0], $zero['tr_retur_pembelian'][0]);
sj_assert($edge['subtotal'] == 0 && $edge['nilai_ppn'] == 0 && $edge['grand_total'] == 0, 'Stored zero totals must not trigger recalculation');
$edgeHtml = sj_html($edge);
sj_assert(strpos($edgeHtml, '3 Sheet') !== false && strpos($edgeHtml, '/Sheet') === false, 'Sheet quantity without price units');
sj_assert(strpos($edgeHtml, 'PPN') === false && strpos($edgeHtml, '1970') === false && strpos($edgeHtml, '-0001') === false, 'No tax and no invalid date');
sj_assert($edge['detail'][0]->width === null, 'Missing numeric value stays null');
$legacy = sj_fixture();
$legacy['tr_retur_pembelian'][0]->id_rec_inv_ap = null;
$legacy['tr_retur_pembelian'][0]->matauang = 'USD';
$legacy['tr_retur_pembelian'][0]->subtotal = null;
$legacy['tr_retur_pembelian'][0]->nilai_ppn = null;
$legacy['tr_retur_pembelian'][0]->grand_total = null;
$legacy['master_supplier'] = array();
$legacy['ms_inventory_category3'] = array();
foreach ($legacy['dt_retur_pembelian'] as $row) $row->matauang = 'USD';
$model->db = new SjDatabase($legacy);
$legacyData = $model->get_sj_print_data($legacy['tr_dn_retur_pmb'][0], $legacy['tr_retur_pembelian'][0]);
sj_assert($legacyData['detail'][0]->print_date === '2026-04-01', 'Legacy date follows corresponding PO');
sj_assert($legacyData['grand_total'] === 12418125.0, 'Only missing totals are calculated from rows and tax');
$legacyHtml = sj_html($legacyData);
sj_assert(strpos($legacyHtml, 'USD 12,418,125.00') === false && strpos($legacyHtml, '01/04/2026') !== false, 'Legacy dates rendered without financial values or supplier/material master data');
$invalidDates = array('2026-02-30', 'not-a-date', null);
foreach ($invalidDates as $invalidDate) {
    $legacyData['retur_header'] = clone $legacyData['retur_header'];
    $legacyData['retur_header']->tgl_retur = $invalidDate;
    $invalidHtml = sj_html($legacyData);
    sj_assert(strpos($invalidHtml, 'Cikarang,</b>') !== false, 'Invalid signature date must be blank');
}
$bulk = sj_fixture();
$bulk['dt_retur_pembelian'] = array();
for ($i = 0; $i < 1000; $i++) {
    $row = clone $tables['dt_retur_pembelian'][$i % 2];
    $row->lotno = 'BULK-' . $i;
    $bulk['dt_retur_pembelian'][] = $row;
}
$model->db = new SjDatabase($bulk);
$bulkData = $model->get_sj_print_data($bulk['tr_dn_retur_pmb'][0], $bulk['tr_retur_pembelian'][0]);
sj_assert(count($bulkData['detail']) === 1000 && count($model->db->queries) === 4, 'Query count stays constant for 1000 separate lots');
$zero['dt_retur_pembelian'] = array();
$zero['tr_retur_pembelian'][0]->id_supplier = '';
$zero['tr_retur_pembelian'][0]->no_po = '';
$model->db = new SjDatabase($zero);
$empty = $model->get_sj_print_data($zero['tr_dn_retur_pmb'][0], $zero['tr_retur_pembelian'][0]);
sj_assert(strpos(sj_html($empty), 'Belum ada rincian material') !== false, 'Explain empty details');
sj_assert(count($model->db->queries) === 1, 'Missing IDs must not fetch entire master tables');

$controller = (new ReflectionClass('Retur_pmb_cn'))->newInstanceWithoutConstructor();
$controller->auth = new SjAuth();
$controller->Retur_pmb_cn_model = $model;
$controller->load = new SjViewCapture();
$GLOBALS['sj_permission'] = false;
sj_assert($controller->_render_action_retur_dn($tables['tr_dn_retur_pmb'][0]) === ' ', 'No buttons without View permission');
try { $controller->print_sj('7'); throw new Exception('Permission was not enforced'); }
catch (RuntimeException $e) { sj_assert($e->getCode() === 403, 'Direct endpoint access denied'); }
$GLOBALS['sj_permission'] = true;
$buttons = $controller->_render_action_retur_dn($tables['tr_dn_retur_pmb'][0]);
sj_assert(strpos($buttons, 'retur_pmb_cn/print_sj/7') !== false && strpos($buttons, 'Print SJ') !== false, 'SJ link beside existing print action');
sj_assert(strpos($buttons, 'print_dn/7') !== false, 'DN print preserved');
foreach (array('', '0', 'abc', '7 OR 1=1', '999') as $id) {
    $model->db = new SjDatabase($tables);
    try { $controller->print_sj($id); throw new Exception('Invalid ID accepted'); }
    catch (RuntimeException $e) { sj_assert($e->getCode() === 404, 'Reject invalid/missing DN'); }
}
foreach (array('deleted_dn', 'missing_retur', 'deleted_retur', 'valid') as $scenario) {
    $case = sj_fixture();
    if ($scenario === 'deleted_dn') $case['tr_dn_retur_pmb'][0]->deleted_by = 'admin';
    if ($scenario === 'missing_retur') $case['tr_retur_pembelian'] = array();
    if ($scenario === 'deleted_retur') $case['tr_retur_pembelian'][0]->deleted_by = 'admin';
    $model->db = new SjDatabase($case);
    try { $controller->print_sj('7'); throw new Exception('Expected captured render or error'); }
    catch (RuntimeException $e) { sj_assert($e->getCode() === ($scenario === 'valid' ? 200 : 404), 'Endpoint scenario: ' . $scenario); }
}
echo "PASS SJ data, per-lot cardinality, units, zero totals/tax, escaping, dates, missing data, permissions and endpoint guards\n";

foreach ($argv as $argument) {
    if (strpos($argument, '--pdf-dir=') !== 0) continue;
    $directory = substr($argument, strlen('--pdf-dir='));
    if (!is_dir($directory)) throw new RuntimeException('PDF output directory must exist');
    require_once FCPATH . 'assets/html2pdf/html2pdf/html2pdf.class.php';
    foreach (array('short' => 2, 'long' => 70) as $name => $count) {
        $fixture = $data;
        $fixture['detail'] = array();
        for ($i = 0; $i < $count; $i++) {
            $row = clone $data['detail'][$i % 2];
            $row->lotno = sprintf('V519892-290426-38186-%03d', $i + 1);
            $row->nama_material = 'ROLL HOT ROLLED HOT ROLLED-JSH270C-P-0-3.80-None 0 3.80';
            $fixture['detail'][] = $row;
        }
        $fixture['total_receive'] = 592.0 * $count;
        $fixture['total_retur'] = 895.0 * $count / 2;
        $fixture['subtotal'] = 11187500.0 * $count / 2;
        $fixture['nilai_ppn'] = $fixture['subtotal'] * $fixture['ppn_persen'] / 100;
        $fixture['grand_total'] = $fixture['subtotal'] + $fixture['nilai_ppn'];
        $pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 8, 10, 8));
        $pdf->WriteHTML(sj_html($fixture));
        $page_count = $pdf->pdf->getNumPages();
        $pdf->Output($directory . '/sj-' . $name . '.pdf', 'F');
        echo 'PDF ' . $name . ': ' . $page_count . " page(s)\n";
    }
}
