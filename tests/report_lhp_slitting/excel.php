<?php
ob_start(); require __DIR__.'/controller.php'; $priorOutput=ob_get_clean();
require dirname(dirname(__DIR__)).'/application/libraries/PHPExcel.php';
require dirname(dirname(__DIR__)).'/application/modules/report_lhp_slitting/libraries/Report_lhp_slitting_excel.php';
$exporter=new Report_lhp_slitting_excel();
$values=array(); foreach ($model->get_rows($all,0,null) as $row) $values[]=$model->format_row($row);
$book=$exporter->build(Report_lhp_slitting_model::headers(),$values);
$sheet=$book->getActiveSheet();
check($sheet->getHighestColumn()==='S' && $sheet->getAutoFilter()->getRange()==='A2:S6','19 columns, 4 results');
check($sheet->getTitle()==='Report Weight Production','Worksheet name');
check($sheet->getCell('B3')->getValue()==='=SUM(1,2)' && $sheet->getCell('B3')->getDataType()===PHPExcel_Cell_DataType::TYPE_STRING,'Formula-looking text remains text');
check($sheet->getCell('I3')->getValue()===null && $sheet->getCell('I4')->getValue()==0 && $sheet->getCell('I4')->getDataType()===PHPExcel_Cell_DataType::TYPE_NUMERIC,'NULL versus zero');
check($sheet->getCell('S5')->getValue()==-10 && $sheet->getCell('D5')->getDataType()===PHPExcel_Cell_DataType::TYPE_NUMERIC,'Negative values and numeric dates');
check($sheet->getFreezePane()==='' && $sheet->getAutoFilter()->getRange()==='A2:S6','No frozen panes and preserved autofilter');
check(isset($sheet->getMergeCells()['A1:E1']) && isset($sheet->getMergeCells()['F1:I1']) && isset($sheet->getMergeCells()['J1:R1']),'Three merged group headers');
check(strpos($sheet->getCell('A8')->getValue(),'Catatan:')===0,'Visible footer note outside data table');
$temporary=tempnam(sys_get_temp_dir(),'lhp-test-');
try {
    PHPExcel_IOFactory::createWriter($book,'Excel2007')->save($temporary);
    $roundtrip=PHPExcel_IOFactory::load($temporary)->getActiveSheet();
    check($roundtrip->getCell('B3')->getDataType()===PHPExcel_Cell_DataType::TYPE_STRING && $roundtrip->getCell('S5')->getValue()==-10,'XLSX roundtrip types/values');
} finally { unlink($temporary); }
class LhpLoader { public function library($name) {} }
$controller->load=new LhpLoader();
$denied=false;
try { $controller->export_excel(); } catch (RuntimeException $e) { $denied=$e->getMessage()==='Access denied'; }
check($denied && $controller->auth->permission==='Report_LHP_Slitting.View','Export permission guard');
$controller->auth->allowed=true;
$controller->input->values=array('start_date'=>'2026-05-06','end_date'=>'2026-05-06','order'=>array(array('column'=>7,'dir'=>'desc')));
ob_start(); $controller->export_excel(); $binary=ob_get_clean();
$temporary=tempnam(sys_get_temp_dir(),'lhp-http-');
try {
    file_put_contents($temporary,$binary);
    $actual=PHPExcel_IOFactory::load($temporary)->getActiveSheet();
    check($actual->getAutoFilter()->getRange()==='A2:S4' && $actual->getCell('H3')->getValue()==='LOT-2' && $actual->getCell('H4')->getValue()==='LOT-1','Endpoint exports all filtered rows with active sorting');
} finally { unlink($temporary); }
function show_error($message,$code) { throw new InvalidArgumentException($message); }
$controller->input->values=array('start_date'=>'2026-02-30');
$invalid=false; try { $controller->export_excel(); } catch (InvalidArgumentException $e) { $invalid=true; }
check($invalid,'Invalid export filters rejected');
$empty=$exporter->build(Report_lhp_slitting_model::headers(),array())->getActiveSheet();
check($empty->getCell('A2')->getValue()==='No SPK Produksi' && $empty->getAutoFilter()->getRange()==='A2:S2','Empty export retains headers');
echo $priorOutput;
echo "PASS Excel: 19 columns, groups, dates/numbers/NULL, text protection, XLSX roundtrip, endpoint filters/sorting/access\n";
