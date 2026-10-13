<?php
ob_start(); require __DIR__.'/regression.php'; $prior=ob_get_clean();
class Admin_Controller {}
class TimeInput {
    public $values=array();
    public function post($key=null,$xss=false) { return $this->values; }
    public function get($key=null,$xss=false) { return $this->values; }
}
class TimeAuth {
    public $allowed=true; public $permission;
    public function restrict($permission) { $this->permission=$permission; if (!$this->allowed) throw new RuntimeException('Access denied'); }
}
class TimeOutput {
    public $json;
    public function set_content_type($type) { return $this; }
    public function set_output($json) { $this->json=$json; return $this; }
}
class TimeTemplate {
    public $data; public $view;
    public function set($data) { $this->data=$data; }
    public function render($view) { $this->view=$view; }
}
class TimeLoader { public function library($name) {} }
function show_error($message,$code=500) { throw new InvalidArgumentException($message); }
require dirname(dirname(__DIR__)).'/application/modules/report_production_time/controllers/Report_production_time.php';
$reflection=new ReflectionClass('Report_production_time'); $controller=$reflection->newInstanceWithoutConstructor();
$controller->input=new TimeInput(); $controller->auth=new TimeAuth(); $controller->output=new TimeOutput();
$controller->template=new TimeTemplate(); $controller->report_model=$model; $controller->load=new TimeLoader();
$controller->index();
check(count($controller->template->data['headers'])===18 && $controller->template->view==='index','Page headers');
$controller->input->values=array('length'=>1); $controller->get_data(); $json=json_decode($controller->output->json,true);
check($json['recordsFiltered']===3 && count($json['data'])===1 && count($json['data'][0]['progress'])===12,'JSON group pagination');
$controller->input->values=array('start_date'=>'bad'); $controller->get_data();
check(isset(json_decode($controller->output->json,true)['error']),'Invalid filter response');
$controller->input->values=array('start'=>array('bad'),'length'=>array('bad')); $controller->get_data();
check(count(json_decode($controller->output->json,true)['data'])===3,'Malformed page controls cannot change SQL');
$controller->auth->allowed=false;
foreach (array('index','get_data','export_excel') as $method) {
    $denied=false; try { $controller->$method(); } catch (RuntimeException $e) { $denied=$e->getMessage()==='Access denied'; }
    check($denied && $controller->auth->permission==='Report_Production_Time.View','Permission '.$method);
}
require dirname(dirname(__DIR__)).'/application/libraries/PHPExcel.php';
require dirname(dirname(__DIR__)).'/application/modules/report_production_time/libraries/Report_production_time_excel.php';
$exporter=new Report_production_time_excel(); $book=$exporter->build($model->get_groups($all)); $sheet=$book->getActiveSheet();
check($sheet->getTitle()==='Report Waktu Pekerjaan (Final)' && $sheet->getHighestColumn()==='R','Final sheet and 18 columns');
foreach (array('A1:J1','K1:P1','Q1:R1','A3:A14','Q3:Q14','R3:R14','A15:A25') as $merge)
    check(isset($sheet->getMergeCells()[$merge]),'Merge '.$merge);
check($sheet->getCell('R3')->getValue()===120.5 && $sheet->getCell('Q3')->getValue()===120.5/60,'Excel and model totals agree');
check($sheet->getCell('O3')->getValue()===0.0 && $sheet->getCell('O4')->getValue()===null,'Blank and numeric zero');
check($sheet->getCell('C3')->getDataType()===PHPExcel_Cell_DataType::TYPE_NUMERIC && $sheet->getCell('M9')->getDataType()===PHPExcel_Cell_DataType::TYPE_NUMERIC,'Typed dates and times');
check($sheet->getCell('R15')->getValue()==='Tidak tersedia' && strpos($sheet->getCell('O21')->getValue(),'Tidak valid:')===0,'Invalid totals visible in Excel');
check($sheet->getCell('B26')->getValue()==='=SUM(1,2)' && $sheet->getCell('B26')->getDataType()===PHPExcel_Cell_DataType::TYPE_STRING,'Formula text remains string');
check($sheet->getFreezePane()==='','No frozen panes');
function saved_cells($path) {
    $zipClass=PHPExcel_Settings::getZipClass(); $zip=new $zipClass();
    check($zip->open($path)===true,'Saved file opens as ZIP');
    $strings=simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
    $shared=array(); foreach ($strings->si as $item) $shared[]=(string)$item->t;
    $xml=simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
    $cells=array();
    foreach ($xml->sheetData->row as $row) foreach ($row->c as $cell) {
        $type=(string)$cell['t']; $value=(string)$cell->v;
        $cells[(string)$cell['r']]=array('type'=>$type,'value'=>$type==='s'?$shared[(int)$value]:$value);
        check(!isset($cell->f),'No unexpected formula in saved file');
    }
    $zip->close(); return $cells;
}
$temporary=tempnam(sys_get_temp_dir(),'pt-xlsx-');
try {
    $exporter->save($book,$temporary);
    $roundtrip=saved_cells($temporary);
    check($roundtrip['B26']['type']==='s' && $roundtrip['B26']['value']==='=SUM(1,2)' && (float)$roundtrip['R3']['value']===120.5,'Saved XLSX values/types');
} finally { unlink($temporary); }
$controller->auth->allowed=true; $controller->input->values=array('start_date'=>'2026-09-26','end_date'=>'2026-09-26');
ob_start(); $controller->export_excel(); $binary=ob_get_clean();
$temporary=tempnam(sys_get_temp_dir(),'pt-endpoint-');
try {
    file_put_contents($temporary,$binary); $saved=saved_cells($temporary);
    check($saved['A3']['value']==='PROD-A' && !isset($saved['A15']) && (float)$saved['R3']['value']===120.5,'Endpoint exports all filtered groups');
} finally { unlink($temporary); }
$controller->input->values=array('end_date'=>'bad'); $failed=false;
try { $controller->export_excel(); } catch (InvalidArgumentException $e) { $failed=true; }
check($failed,'Invalid export date rejected');
$empty=$exporter->build(array())->getActiveSheet();
check($empty->getCell('A2')->getValue()==='No SPK Produksi' && $empty->getCell('R2')->getValue()==='Total Waktu Keseluruhan (Menit)','Empty export headers');
function base_url($path) { return '/'.$path; }
$headers=Report_production_time_model::headers(); ob_start();
require dirname(dirname(__DIR__)).'/application/modules/report_production_time/views/index.php'; $html=ob_get_clean();
check(substr_count($html,'scope="col"')===18 && strpos($html,'colspan="10"')!==false && strpos($html,'colspan="6"')!==false,'PHP view structure');
echo $prior;
echo "PASS controller/Excel: pagination, permissions, malformed filters, 18 columns/merges, typed values, formula protection, roundtrip, filtered endpoint\n";
