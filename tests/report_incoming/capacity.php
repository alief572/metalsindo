<?php
require __DIR__.'/bootstrap.php';
require APPPATH.'modules/report_incoming/models/Report_incoming_model.php';
require APPPATH.'libraries/PHPExcel.php';
require APPPATH.'modules/report_incoming/libraries/Report_incoming_excel.php';
ini_set('memory_limit','512M');
class IncomingCapacitySource {
    private $formatter;
    public function __construct($db) { $this->formatter=new Report_incoming_model($db); }
    public function export_rows($filters,$column,$direction) {
        for($i=1;$i<=36000;$i++) {
            $values=array($i,'P2600999','PO-MP/26/IX/0263','2026-09-11','PT.MICS STEEL INDONESIA',
                'SHEET COLD ROLLED SCGA270C-0-None-1.20-380-370-COLD ROLLED 0',380,264,'2026-09-23',265,265,-1,
                'Open','IC-120/MP-09/2026','ASEP','001088/MICS/09/26','Gudang Finish Good','LOT-'.$i,'Customer A; Customer B');
            $row=array('detail_id'=>$i); foreach($values as $c=>$value) $row['c'.$c]=$value;
            yield $row;
        }
    }
    public function format_row($row,$number) { return $this->formatter->format_row($row,$number); }
}
$source=new IncomingCapacitySource($db); $exporter=new Report_incoming_excel(); $start=microtime(true);
$path=tempnam(sys_get_temp_dir(),'incoming_capacity_');
try {
    $book=$exporter->build($source,array('start_date'=>'','end_date'=>'','search'=>''));
    incoming_check($book->getActiveSheet()->getAutoFilter()->getRange()==='A3:S36003','36000 complete export rows');
    $writer=PHPExcel_IOFactory::createWriter($book,'Excel2007'); $writer->setUseDiskCaching(true,sys_get_temp_dir()); $writer->save($path);
    $book->disconnectWorksheets(); unset($writer,$book); gc_collect_cycles();
    $zip=new ZipArchive(); incoming_check($zip->open($path)===true,'Capacity XLSX readable');
    $xml=$zip->getFromName('xl/worksheets/sheet1.xml');
    incoming_check(strpos($xml,'ref="A3:S36003"')!==false && preg_match('/<c r="A36003"[^>]*>.*?<v>36000<\/v>/s',$xml),'Last row and complete filter survive XLSX');
    $zip->close();
    echo 'PASS capacity: 36000 rows / 684000 cells, '.round(microtime(true)-$start,2).' s, peak '.round(memory_get_peak_usage(true)/1048576,1).' MiB under 512M, XLSX '.round(filesize($path)/1048576,2).' MiB'.PHP_EOL;
} finally { if(is_file($path)) unlink($path); }
