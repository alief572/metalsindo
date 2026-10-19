<?php
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__).'/stock_booking/bootstrap.php';
class CI_Model { public $db; }
require dirname(dirname(__DIR__)).'/application/modules/stock_material/models/Stock_raw_material_model.php';
function raw_assert($ok, $label) { if (!$ok) throw new RuntimeException($label); }
$model = new Stock_raw_material_model();
raw_assert($model->filters(array())['stock_date'] === $model->today(), 'Default today');
foreach (array('2026-02-30','0000-00-00','2026-1-01','bad','2999-01-01',array('2020-01-01')) as $date) {
    try { $model->filters(array('stock_date'=>$date)); throw new RuntimeException('Accepted invalid date'); }
    catch (InvalidArgumentException $e) {}
}
echo "PASS date validation\n";
$model->db = new BookingDatabase();
$db = $model->db;
foreach (array(
    'stock_material'=>'id_stock INT PRIMARY KEY,id_category3 VARCHAR(40),lotno VARCHAR(50),nama_material VARCHAR(50),width DOUBLE,qty DOUBLE,sisa_spk DOUBLE,no_surat VARCHAR(50),customer VARCHAR(50),lot_slitting VARCHAR(50),id_gudang VARCHAR(10),aktif CHAR(1),delivery_book DOUBLE',
    'stock_material_backup'=>'id_stock INT PRIMARY KEY,id_category3 VARCHAR(40),lotno VARCHAR(50),nama_material VARCHAR(50),width DOUBLE,qty DOUBLE,sisa_spk DOUBLE,no_surat VARCHAR(50),customer VARCHAR(50),lot_slitting VARCHAR(50),id_gudang VARCHAR(10),aktif CHAR(1),delivery_book DOUBLE,tgl DATETIME',
    'ms_inventory_category3'=>'id_category3 VARCHAR(40),maker VARCHAR(50),id_bentuk VARCHAR(20)',
    'ms_bentuk'=>'id_bentuk VARCHAR(20),nm_bentuk VARCHAR(20)',
    'master_customers'=>'id_customer VARCHAR(40),name_customer VARCHAR(50)'
) as $table=>$columns) $db->query('CREATE TEMPORARY TABLE '.$table.' ('.$columns.') ENGINE=InnoDB');
$db->query("INSERT INTO ms_inventory_category3 VALUES ('MAT','Maker','ROLL'),('MAT','Maker','ROLL')");
$db->query("INSERT INTO ms_bentuk VALUES ('ROLL','ROLL'),('ROLL','ROLL')");
$db->query("INSERT INTO master_customers VALUES ('C','Customer'),('C','Customer')");
$insert = "INSERT INTO stock_material VALUES (?, 'MAT', ?, 'Material', ?, 2, ?, 'UPLOAD', 'C', '', '1', 'Y', 0)";
for ($i=1;$i<=12;$i++) $db->query($insert,array($i,'LOT'.$i,400,$i));
$db->query("INSERT INTO stock_material_backup SELECT *, '2026-10-01 09:00:00' FROM stock_material WHERE id_stock=1");
$db->query("INSERT INTO stock_material_backup SELECT id_stock+100,id_category3,lotno,nama_material,width,qty,sisa_spk,no_surat,customer,lot_slitting,id_gudang,aktif,delivery_book,'2026-10-01 13:33:05' FROM stock_material WHERE id_stock<=2");
$db->query("INSERT INTO stock_material_backup SELECT id_stock+200,id_category3,lotno,nama_material,width,qty,0,no_surat,customer,lot_slitting,id_gudang,aktif,delivery_book,'2026-10-02 13:33:05' FROM stock_material WHERE id_stock=1");
$db->query("INSERT INTO stock_material_backup SELECT 999,id_category3,'INACTIVE',nama_material,width,qty,99999,no_surat,customer,lot_slitting,id_gudang,'N',delivery_book,'2026-10-01 13:33:05' FROM stock_material WHERE id_stock=1");
$current=$model->table(array('length'=>5));
raw_assert($current['recordsTotal']===12 && count($current['data'])===5 && $current['recordsAset']===156.0,'Current totals and master cardinality');
$db->statements=array();
$model->table(array('series'=>400,'search'=>array('value'=>'Customer'),'length'=>5));
raw_assert(count($db->statements)===2,'Filtered current table uses one aggregate and one page query');
raw_assert(strpos($db->statements[0][0],'ms_inventory_category3')===false && strpos($db->statements[0][0],'ms_bentuk')===false,'Summary does not join material/shape masters');
$past=$model->table(array('stock_date'=>'2026-10-01'));
raw_assert($past['recordsTotal']===2 && $past['recordsAset']===6.0 && $past['snapshot_at']==='2026-10-01 13:33:05','Last batch only');
raw_assert($model->table(array('stock_date'=>'2026-10-01','search'=>array('value'=>'INACTIVE')))['recordsFiltered']===0,'Inactive backup excluded from table, count and weight');
$empty=$model->table(array('stock_date'=>'2026-10-02'));
raw_assert($empty['available'] && $empty['recordsTotal']===0,'Existing empty snapshot');
$missing=$model->table(array('stock_date'=>'2026-10-03'));
raw_assert(!$missing['available'] && $missing['recordsTotal']===0,'Missing snapshot no fallback');
$filtered=$model->table(array('series'=>400,'search'=>array('value'=>'LOT1'),'length'=>1,'start'=>1));
raw_assert($filtered['recordsTotal']===12 && $filtered['recordsFiltered']===4 && count($filtered['data'])===1,'Search, width, paging');
raw_assert($model->table(array('series'=>500))['recordsFiltered']===0,'Width excludes rows');
raw_assert($model->table(array('order'=>array(array('column'=>999,'dir'=>'desc; DROP TABLE stock_material'))))['recordsTotal']===12,'Ordering whitelist');
raw_assert($model->table(array('order'=>array(array('dir'=>'desc')),'length'=>1))['data'][0][0]==='12','Descending numbering');
raw_assert($model->table(array('search'=>array('value'=>"%' OR 1=1 --")))['recordsFiltered']===0,'Bound search and literal wildcards');
echo "PASS current/history, latest batch, missing/empty snapshot, totals, filters, pagination, duplicate masters\n";

require dirname(dirname(__DIR__)).'/scripts/optimize_stock_raw_date.php';
$installer=new StockRawDateIndex($db->connection,getenv('BOOKING_DB_HOST'));
$manifest=sys_get_temp_dir().'/raw-date-test-'.uniqid().'.json';
try {
    raw_assert(!$installer->preflight()['covered'],'Index preflight');
    raw_assert($installer->apply($manifest)['created']===1,'Index apply');
    raw_assert($installer->apply($manifest)['created']===0,'Index apply idempotent');
    raw_assert($installer->rollback($manifest)['dropped']===1,'Index rollback');
    raw_assert($installer->rollback($manifest)['dropped']===0,'Index rollback idempotent');
    $bad=new StockRawDateIndex($db->connection,'remote.invalid');
    try { $bad->preflight(); throw new LogicException('Remote accepted'); } catch (RuntimeException $e) {}
} finally { if (file_exists($manifest)) unlink($manifest); }
echo "PASS index preflight/apply/rollback on temporary table and host guard\n";

require dirname(dirname(__DIR__)).'/application/libraries/PHPExcel.php';
require dirname(dirname(__DIR__)).'/application/modules/stock_material/libraries/Stock_raw_material_excel.php';
foreach (array('=2+2','+formula','-formula','@formula') as $i=>$value) $db->query('UPDATE stock_material SET lotno=? WHERE id_stock=?',array($value,$i+1));
$exporter=new Stock_raw_material_excel(); $filters=$model->filters(array()); $meta=$model->resolve($filters);
$book=$exporter->build($model,$filters,$meta); $sheet=$book->getActiveSheet();
raw_assert(PHPExcel_CachedObjectStorageFactory::getCacheStorageMethod()===PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp,'Export does not require SQLite3');
raw_assert($sheet->getCell('I17')->getValue()===156.0,'Export total matches table across all pages');
raw_assert($sheet->getCell('M16')->getValue()===$model->today(),'All 12 rows exported with date');
for ($row=5;$row<=8;$row++) raw_assert($sheet->getCell('C'.$row)->getDataType()===PHPExcel_Cell_DataType::TYPE_STRING,'Formula-like code is string');
$file=sys_get_temp_dir().'/raw-stock-'.uniqid().'.xlsx';
try {
    PHPExcel_IOFactory::createWriter($book,'Excel2007')->save($file);
    $book->disconnectWorksheets();
    $zip=new PclZip($file);
    $entries=$zip->extract(PCLZIP_OPT_EXTRACT_AS_STRING);
    $xml=array(); foreach ($entries as $entry) $xml[$entry['filename']]=$entry['content'];
    $strings=simplexml_load_string($xml['xl/sharedStrings.xml']);
    $worksheet=simplexml_load_string($xml['xl/worksheets/sheet1.xml']);
    $literal=null; $total=null;
    foreach ($worksheet->sheetData->row as $row) foreach ($row->c as $cell) {
        if ((string)$cell['r']==='C5') {
            raw_assert((string)$cell['t']==='s' && !isset($cell->f),'Serialized formula-like code is shared string');
            $literal=(string)$strings->si[(int)$cell->v]->t;
        }
        if ((string)$cell['r']==='I17') $total=(float)$cell->v;
    }
    raw_assert($literal==='=2+2' && $total===156.0,'Serialized XLSX literal and total');
} finally { if (file_exists($file)) unlink($file); }
echo "PASS complete XLSX, date, totals, PHPTemp cache and formula-safe round trip\n";

$parts=array(); $bindings=array();
for ($i=1000;$i<2001;$i++) {
    $parts[]="(?,'MAT',?,'Batch',800,1,1,'UPLOAD','C','','1','Y',0)";
    $bindings[]=$i; $bindings[]='BATCH'.$i;
}
$db->query('INSERT INTO stock_material VALUES '.implode(',',$parts),$bindings);
$filters=$model->filters(array('series'=>'800')); $meta=$model->resolve($filters);
$book=$exporter->build($model,$filters,$meta);
raw_assert($book->getActiveSheet()->getCell('M1005')->getValue()===$model->today(),'Export crosses 1000-row query batch');
raw_assert($book->getActiveSheet()->getCell('I1006')->getValue()===1001.0,'Complete filtered batch total');
$book->disconnectWorksheets();
echo "PASS XLSX across multiple query batches\n";
$filters=$model->filters(array('stock_date'=>'2026-10-01')); $meta=$model->resolve($filters);
$book=$exporter->build($model,$filters,$meta,'desc'); $sheet=$book->getActiveSheet();
raw_assert($sheet->getCell('A5')->getValue()==2 && $sheet->getCell('I7')->getValue()==6,'Historical descending export and total');
raw_assert($sheet->getCell('H7')->getValue()==='TOTAL BERAT','Inactive backup excluded from exported rows');
raw_assert($sheet->getCell('A2')->getValue()==='Tanggal stok: 2026-10-01','Historical export date without snapshot details');
$book->disconnectWorksheets();
echo "PASS historical XLSX date-only header and descending order\n";

class Admin_Controller {}
class RawTestInput {
    public $data;
    public function post($key=null,$clean=false) { return $key===null ? $this->data : (isset($this->data[$key]) ? $this->data[$key] : null); }
}
class RawTestAuth {
    public $allowed=true; public $permission;
    public function restrict($permission) { $this->permission=$permission; if (!$this->allowed) throw new RuntimeException('Denied'); }
}
class RawTestLoader { public function model($path,$alias) {} }
class RawTestOutput {
    public $status=200; public $json;
    public function set_status_header($status) { $this->status=$status; return $this; }
    public function set_content_type($type) { return $this; }
    public function set_output($json) { $this->json=$json; return $this; }
}
class RawTestLegacy {
    public $calls=0;
    public function getDataJSON_GRW() { $this->calls++; }
}
require dirname(dirname(__DIR__)).'/application/modules/stock_material/controllers/Stock_material.php';
$reflection=new ReflectionClass('Stock_material'); $controller=$reflection->newInstanceWithoutConstructor();
$controller->input=new RawTestInput(); $controller->auth=new RawTestAuth(); $controller->load=new RawTestLoader();
$controller->output=new RawTestOutput(); $controller->raw_stock=$model; $controller->Serverside_model=new RawTestLegacy();
$controller->input->data=array('gudang'=>'2'); $controller->data_side_material_grw();
raw_assert($controller->Serverside_model->calls===1,'Other warehouses retain legacy serializer');
$controller->input->data=array('gudang'=>'1','stock_date'=>'2026-10-01'); $controller->data_side_material_grw();
$response=json_decode($controller->output->json,true);
raw_assert(count($response['data'][0])===13 && $response['snapshot_at']==='2026-10-01 13:33:05','Raw date metadata and appended column');
$controller->input->data=array('gudang'=>'1','stock_date'=>'bad'); $controller->data_side_material_grw();
raw_assert($controller->output->status===400,'Invalid date HTTP status');
$controller->auth->allowed=false;
foreach (array('data_side_material_grw','export_raw_material') as $method) {
    try { $controller->$method(); throw new LogicException('Permission guard missing'); } catch (RuntimeException $e) {}
    raw_assert($controller->auth->permission==='Warehouse_material.View','Required view permission');
}
echo "PASS controller permissions, validation HTTP status and legacy warehouse dispatch\n";
