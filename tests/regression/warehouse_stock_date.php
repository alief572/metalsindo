<?php
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__).'/stock_booking/bootstrap.php';
class CI_Model { public $db; }
require dirname(dirname(__DIR__)).'/application/modules/stock_material/models/Warehouse_stock_date_model.php';
$model=new Warehouse_stock_date_model();
booking_check($model->filters(array('gudang'=>'2'))['stock_date']===$model->today(),'Default today');
foreach (array('1','0','7',array('2')) as $warehouse) {
    try { $model->filters(array('gudang'=>$warehouse)); throw new LogicException('Invalid warehouse accepted'); }
    catch (InvalidArgumentException $e) {}
}
echo "PASS warehouse/date validation\n";
$model->db=new BookingDatabase(); $db=$model->db;
$stockColumns='id_stock INT PRIMARY KEY,id_category3 VARCHAR(40),lotno VARCHAR(50),nama_material VARCHAR(60),width DOUBLE,qty DOUBLE,sisa_spk DOUBLE,totalweight DOUBLE,no_surat VARCHAR(50),customer VARCHAR(50),lot_slitting VARCHAR(50),id_gudang VARCHAR(10),aktif CHAR(1),delivery_book DOUBLE';
foreach (array('stock_material'=>$stockColumns,'stock_material_backup'=>$stockColumns.',tgl DATETIME',
    'ms_inventory_category3'=>'id_category3 VARCHAR(40),maker VARCHAR(40),id_bentuk VARCHAR(20),total_weight DOUBLE',
    'ms_bentuk'=>'id_bentuk VARCHAR(20),nm_bentuk VARCHAR(20)',
    'master_customers'=>'id_customer VARCHAR(40),name_customer VARCHAR(50)') as $table=>$columns)
    $db->query('CREATE TEMPORARY TABLE '.$table.' ('.$columns.') ENGINE=InnoDB');
$db->query("INSERT INTO ms_inventory_category3 VALUES ('ROLL','Maker','ROLL',1),('ROLL','Maker','ROLL',1),('SHEET','Maker','B2000002',2)");
$db->query("INSERT INTO ms_bentuk VALUES ('ROLL','ROLL'),('ROLL','ROLL'),('B2000002','SHEET')");
$db->query("INSERT INTO master_customers VALUES ('C','Customer'),('C','Customer')");
foreach ($model->warehouses() as $warehouse=>$name) {
    foreach (array(1,2,3) as $n) {
        $id=$warehouse*100+$n;
        $db->query("INSERT INTO stock_material VALUES (?,?,?,'Material',?,2,?,20,'UPLOAD','C','',?,'Y',0)",
            array($id,$warehouse===3 ? 'SHEET' : 'ROLL','LOT'.$n,$n===3 ? 500 : 400,$n,$warehouse));
    }
    $db->query("INSERT INTO stock_material_backup SELECT *, '2026-10-01 09:00:00' FROM stock_material WHERE id_stock=?",array($warehouse*100+1));
    $db->query("INSERT INTO stock_material_backup SELECT id_stock+10000,id_category3,lotno,nama_material,width,qty,sisa_spk,totalweight,no_surat,customer,lot_slitting,id_gudang,aktif,delivery_book,'2026-10-01 13:33:05' FROM stock_material WHERE id_gudang=?",array($warehouse));
    $db->query("INSERT INTO stock_material_backup SELECT id_stock+20000,id_category3,'INACTIVE',nama_material,width,qty,99999,totalweight,no_surat,customer,lot_slitting,id_gudang,'N',delivery_book,'2026-10-01 13:33:05' FROM stock_material WHERE id_stock=?",array($warehouse*100+1));
}
$db->query("INSERT INTO stock_material_backup SELECT 30000,id_category3,lotno,nama_material,width,qty,0,totalweight,no_surat,customer,lot_slitting,id_gudang,aktif,delivery_book,'2026-10-03 13:33:05' FROM stock_material WHERE id_stock=201");
foreach ($model->warehouses() as $warehouse=>$name) {
    $input=array('gudang'=>(string)$warehouse,'stock_date'=>'2026-10-01');
    $result=$model->table($input+array('length'=>1));
    booking_check($result['recordsTotal']===3 && $result['recordsAset']===12.0 && count($result['data'])===1,'Warehouse isolation, counts, weight, paging');
    booking_check($result['snapshot_at']==='2026-10-01 13:33:05','Latest daily snapshot only');
    $last=count($result['data'][0])-1;
    booking_check($result['data'][0][$last]==='2026-10-01' && $result['data'][0][$last-2]===$name,'Date and correct warehouse name');
    booking_check($last===($warehouse===3 ? 13 : 12),'FG retains extra sheet column');
    if ($warehouse===3) booking_check($result['data'][0][9]==='10','FG sheet conversion');
    $db->statements=array();
    $filtered=$model->table($input+array('series'=>'400','search'=>array('value'=>'Customer')));
    booking_check($filtered['recordsTotal']===3 && $filtered['recordsFiltered']===2 && $filtered['recordsAset']===6.0,'Combined width/customer search');
    booking_check(count($db->statements)===3,'One resolver/aggregate/page query');
    booking_check($model->table($input+array('search'=>array('value'=>'INACTIVE')))['recordsFiltered']===0,'Inactive backup excluded');
    booking_check($model->table($input+array('search'=>array('value'=>$name)))['recordsFiltered']===3,'Warehouse name search');
    booking_check($model->table($input+array('search'=>array('value'=>"%' OR 1=1 --")))['recordsFiltered']===0,'Bound literal search');
    $current=$model->table(array('gudang'=>(string)$warehouse,'length'=>1,'order'=>array(array('column'=>999,'dir'=>'desc'))));
    booking_check($current['source']==='stock_material' && $current['data'][0][0]==='3','Current source and descending numbering');
    $missing=$model->table(array('gudang'=>(string)$warehouse,'stock_date'=>'2026-10-02'));
    booking_check(!$missing['available'] && $missing['recordsFiltered']===0,'Missing snapshot has no fallback');
    $empty=$model->table(array('gudang'=>(string)$warehouse,'stock_date'=>'2026-10-03'));
    booking_check($empty['available'] && $empty['recordsFiltered']===0,'Existing snapshot without eligible warehouse stock');
}
echo "PASS five warehouse sources, snapshot, Y-only, totals, filters, sorting, paging, cardinality, FG sheet\n";

require dirname(dirname(__DIR__)).'/application/libraries/PHPExcel.php';
require dirname(dirname(__DIR__)).'/application/modules/stock_material/libraries/Warehouse_stock_date_excel.php';
$exporter=new Warehouse_stock_date_excel();
foreach ($model->warehouses() as $warehouse=>$name) {
    $db->query('UPDATE stock_material_backup SET lotno=? WHERE id_stock=?',array('=FORMULA',$warehouse*100+10001));
    $filters=$model->filters(array('gudang'=>(string)$warehouse,'stock_date'=>'2026-10-01'));
    $meta=$model->resolve($filters); $book=$exporter->build($model,$filters,$meta);
    $sheet=$book->getActiveSheet(); $last=$warehouse===3 ? 'N' : 'M';
    booking_check($sheet->getCell('A2')->getValue()==='Tanggal stok: 2026-10-01','Excel header contains stock date without snapshot details');
    booking_check($sheet->getCell($last.'7')->getValue()==='2026-10-01','XLSX all filtered rows across pagination');
    booking_check($sheet->getCell('I8')->getValue()==12,'XLSX total excludes N');
    booking_check($sheet->getCell('C5')->getDataType()===PHPExcel_Cell_DataType::TYPE_STRING,'Formula safety');
    if ($warehouse===3) booking_check($sheet->getCell('J5')->getValue()==10,'FG sheet exported');
    $file=sys_get_temp_dir().'/warehouse-date-'.uniqid().'.xlsx';
    try {
        PHPExcel_IOFactory::createWriter($book,'Excel2007')->save($file);
        $zip=new PclZip($file); $entries=$zip->extract(PCLZIP_OPT_EXTRACT_AS_STRING); $xml=array();
        foreach ($entries as $entry) $xml[$entry['filename']]=$entry['content'];
        booking_check(strpos($xml['xl/sharedStrings.xml'],'=FORMULA')!==false && strpos($xml['xl/worksheets/sheet1.xml'],'<f>')===false,'Serialized safe XLSX');
    } finally { if (file_exists($file)) unlink($file); $book->disconnectWorksheets(); }
}
echo "PASS five XLSX exports, all pages, snapshot/date, totals and formula-safe serialization\n";

class Admin_Controller {}
class WarehouseDateInput {
    public $data;
    public function post($key=null,$clean=false) { return $this->data; }
    public function get($key=null,$clean=false) { return $key===null ? $this->data : (isset($this->data[$key]) ? $this->data[$key] : null); }
}
class WarehouseDateAuth {
    public $allowed=true; public $permission;
    public function restrict($permission) { $this->permission=$permission; if (!$this->allowed) throw new RuntimeException('Denied'); }
}
class WarehouseDateLoader { public function model($path,$alias) {} }
class WarehouseDateOutput {
    public $status=200; public $json;
    public function set_status_header($status) { $this->status=$status; return $this; }
    public function set_content_type($type) { return $this; }
    public function set_output($json) { $this->json=$json; return $this; }
}
require dirname(dirname(__DIR__)).'/application/modules/stock_material/controllers/Stock_material.php';
$reflection=new ReflectionClass('Stock_material'); $controller=$reflection->newInstanceWithoutConstructor();
$controller->input=new WarehouseDateInput(); $controller->auth=new WarehouseDateAuth();
$controller->load=new WarehouseDateLoader(); $controller->output=new WarehouseDateOutput(); $controller->dated_warehouse=$model;
$controller->input->data=array('gudang'=>'2','stock_date'=>'2026-10-01'); $controller->data_warehouse_date();
booking_check(json_decode($controller->output->json,true)['recordsTotal']===3,'Controller table contract');
$controller->input->data=array('gudang'=>'7'); $controller->data_warehouse_date();
booking_check($controller->output->status===400,'Controller invalid warehouse HTTP 400');
$controller->auth->allowed=false;
foreach (array('data_warehouse_date','export_warehouse_date') as $method) {
    try { $controller->$method(); throw new LogicException('Permission not guarded'); } catch (RuntimeException $e) {}
    booking_check($controller->auth->permission==='Warehouse_material.View','View permission on both endpoints');
}
echo "PASS controller permission and validation contracts\n";
$parts=array(); $bindings=array();
for ($id=40000;$id<41001;$id++) {
    $parts[]="(?,'ROLL',?,'Batch',800,1,1,1,'UPLOAD','C','','2','Y',0)";
    $bindings[]=$id; $bindings[]='BATCH'.$id;
}
$db->query('INSERT INTO stock_material VALUES '.implode(',',$parts),$bindings);
$filters=$model->filters(array('gudang'=>'2','series'=>'800')); $meta=$model->resolve($filters);
$book=$exporter->build($model,$filters,$meta,'desc'); $sheet=$book->getActiveSheet();
booking_check($sheet->getCell('A5')->getValue()==1001 && $sheet->getCell('A1005')->getValue()==1,'XLSX all batches and descending numbering');
booking_check($sheet->getCell('M1005')->getValue()===$model->today() && $sheet->getCell('I1006')->getValue()==1001,'XLSX batch boundary/date/total');
$book->disconnectWorksheets();
echo "PASS warehouse XLSX across 1000-row batch boundary\n";
