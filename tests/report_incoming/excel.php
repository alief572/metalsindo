<?php
ob_start(); require __DIR__.'/controller.php'; $priorOutput=ob_get_clean();
require APPPATH.'libraries/PHPExcel.php';
require APPPATH.'modules/report_incoming/libraries/Report_incoming_excel.php';
$exporter=new Report_incoming_excel();
$filters=$model->normalize_filters(array('search'=>'CPD0661B','start_date'=>'2026-09-23','end_date'=>'2026-09-23'));
$book=$exporter->build($model,$filters,8,'desc'); $sheet=$book->getActiveSheet();
incoming_check($sheet->getCell('A1')->getValue()==='REPORT INCOMING','Workbook title');
incoming_check(strpos($sheet->getCell('A2')->getValue(),'2026-09-23')!==false,'Applied period');
$path=tempnam(sys_get_temp_dir(),'incoming_xlsx_');
try {
    PHPExcel_IOFactory::createWriter($book,'Excel2007')->save($path); $book->disconnectWorksheets();
    $read=PHPExcel_IOFactory::load($path); $sheet=$read->getActiveSheet();
    incoming_check($sheet->getTitle()==='Report Incoming' && $sheet->getFreezePane()==='','Sheet and no frozen panes');
    incoming_check($sheet->getAutoFilter()->getRange()==='A3:S4','Filtered one-row export');
    incoming_check($sheet->getCell('B4')->getValue()==='P2600999' && $sheet->getCell('R4')->getValue()==='CPD0661B-230926-12380-1','Text IDs survive');
    foreach(array('G4'=>380,'H4'=>264,'J4'=>265,'K4'=>265,'L4'=>-1) as $cell=>$expected)
        incoming_check($sheet->getCell($cell)->getDataType()==='n' && $sheet->getCell($cell)->getValue()==$expected,'Typed number '.$cell);
    incoming_check($sheet->getCell('I4')->getDataType()==='n' && $sheet->getStyle('I4')->getNumberFormat()->getFormatCode()==='dd-mm-yyyy','Excel date');
    $read->disconnectWorksheets();
} finally { if(is_file($path)) unlink($path); }
$book=$exporter->build($model,$model->normalize_filters(array('search'=>'IC-CONFLICT')),8,'desc');
$sheet=$book->getActiveSheet();
incoming_check($sheet->getCell('R4')->getDataType()==='s' && $sheet->getCell('R4')->getValue()==='=1+1','Formula-like lot is literal text');
incoming_check($sheet->getCell('H4')->getValue()===null && $sheet->getCell('O4')->getValue()==='Belum terverifikasi','NULL and ambiguous headers');
$book->disconnectWorksheets();
$book=$exporter->build($model,$all,17,'asc'); $sheet=$book->getActiveSheet();
$expected=$model->get_rows($all,0,null,17,'asc');
incoming_check($sheet->getAutoFilter()->getRange()==='A3:S10','Export all matching rows, not a page');
foreach($expected as $i=>$row) {
    $values=$model->format_row($row,$i+1);
    foreach($values as $column=>$value) {
        $cell=$sheet->getCellByColumnAndRow($column,$i+4);
        if($value===null) incoming_check($cell->getValue()===null,'Export null '.$column);
        elseif(in_array($column,array(3,8),true))
            incoming_check($cell->getValue()==PHPExcel_Shared_Date::FormattedPHPToExcel((int)substr($value,0,4),(int)substr($value,5,2),(int)substr($value,8,2)),'Export date source');
        elseif(is_int($value) || is_float($value)) incoming_check($cell->getDataType()==='n' && $cell->getValue()==$value,'Export sorted numeric value '.$column);
        else incoming_check($cell->getValue()===$value,'Export sorted value '.$column);
    }
}
$book->disconnectWorksheets();
$book=$exporter->build($model,$model->normalize_filters(array('search'=>'NO RESULTS')),8,'desc');
incoming_check($book->getActiveSheet()->getAutoFilter()->getRange()==='A3:S3','Empty export retains headers');
$book->disconnectWorksheets();
class IncomingTestLoader { public function library($name) { throw new RuntimeException('Do not instantiate PHPExcel through CI before cache configuration'); } }
function show_error($message,$code) { throw new InvalidArgumentException($message,$code); }
$controller->auth->allowed=true; $controller->load=new IncomingTestLoader();
$controller->input->values=array('search'=>'CPD0661B','order'=>array(array('column'=>2,'dir'=>'asc')));
ob_start(); $controller->export_excel(); $binary=ob_get_clean();
incoming_check(substr($binary,0,2)==='PK','Controller writes XLSX');
$path=tempnam(sys_get_temp_dir(),'incoming_endpoint_');
try {
    file_put_contents($path,$binary); $book=PHPExcel_IOFactory::load($path);
    incoming_check($book->getActiveSheet()->getCell('L4')->getValue()==-1,'Endpoint export actual sample');
    $book->disconnectWorksheets();
} finally { unlink($path); }
$controller->input->values=array('end_date'=>'bad'); $rejected=false;
try { $controller->export_excel(); } catch(InvalidArgumentException $e) { $rejected=$e->getCode()===400; }
incoming_check($rejected,'Export validates filters');
echo $priorOutput;
echo "PASS XLSX: write/read, title/period, all 19 headers, typed dates/numbers/text, negative/NULL/empty, formula safety and actual export endpoint\n";
