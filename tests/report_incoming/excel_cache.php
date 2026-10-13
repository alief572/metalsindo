<?php
if (PHP_SAPI !== 'cli') exit('CLI only');
date_default_timezone_set('Asia/Jakarta');
define('BASEPATH', dirname(dirname(__DIR__)).'/system/');
define('APPPATH', dirname(dirname(__DIR__)).'/application/');
class BF_Model {}
require APPPATH.'modules/report_incoming/models/Report_incoming_model.php';
require APPPATH.'libraries/PHPExcel.php';
require APPPATH.'modules/report_incoming/libraries/Report_incoming_excel.php';
if (!class_exists('ZipArchive')) PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
function cache_check($condition,$message) {
    if (!$condition) throw new RuntimeException($message);
}
class IncomingCacheSource {
    public function export_rows($filters,$column,$direction) { return array(array()); }
    public function format_row($row,$number) {
        return array($number,'000123','PO-001','2026-09-11','Supplier','Material',380,264,
            '2026-09-23',265,265,-1,'Open','IC-001','PIC','SJ-001','Gudang','=1+1','Customer');
    }
}
$expected=PHPExcel_CachedObjectStorage_SQLite3::cacheMethodIsAvailable()
    ? PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3
    : PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
$exporter=new Report_incoming_excel();
$book=$exporter->build(new IncomingCacheSource(),array('start_date'=>'','end_date'=>'','search'=>''));
cache_check(PHPExcel_CachedObjectStorageFactory::getCacheStorageMethod()===$expected,'Available cache selected');
$path=tempnam(sys_get_temp_dir(),'incoming_cache_');
try {
    $writer=PHPExcel_IOFactory::createWriter($book,'Excel2007');
    $writer->setUseDiskCaching(true,sys_get_temp_dir());
    $writer->save($path);
    $book->disconnectWorksheets();
    $read=PHPExcel_IOFactory::load($path); $sheet=$read->getActiveSheet();
    cache_check($sheet->getCell('B4')->getValue()==='000123','Leading zeros preserved');
    cache_check($sheet->getCell('L4')->getValue()==-1,'Negative quantity preserved');
    cache_check($sheet->getCell('R4')->getDataType()==='s' && $sheet->getCell('R4')->getValue()==='=1+1','Formula-like text preserved');
    cache_check($sheet->getCell('I4')->getDataType()==='n' && $sheet->getStyle('I4')->getNumberFormat()->getFormatCode()==='dd-mm-yyyy','Date format preserved');
    cache_check($sheet->getAutoFilter()->getRange()==='A3:S4' && $sheet->getFreezePane()==='','Workbook layout preserved');
    $read->disconnectWorksheets();
    $book=$exporter->build(new IncomingCacheSource(),array('start_date'=>'','end_date'=>'','search'=>''));
    cache_check($book->getActiveSheet()->getCell('B4')->getValue()==='000123','Repeated export works');
    $book->disconnectWorksheets();
} finally { if(is_file($path)) unlink($path); }
PHPExcel_CachedObjectStorageFactory::finalize();
$existing=new PHPExcel(); $rejected=false;
try {
    $exporter->build(new IncomingCacheSource(),array('start_date'=>'','end_date'=>'','search'=>''));
} catch(RuntimeException $e) {
    $rejected=strpos($e->getMessage(),'sebelum workbook dibuat')!==false;
}
cache_check($rejected,'An incompatible existing workbook cache is rejected');
$existing->disconnectWorksheets();
echo 'PASS export cache '.$expected.': XLSX write/read, values, dates, layout, repeated export and cache ordering guard'.PHP_EOL;
