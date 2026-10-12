<?php
require __DIR__.'/regression.php';
$source=file_get_contents(APPPATH.'modules/report_incoming/models/Report_incoming_model.php');
$mutations=array(
    'supplier'=>array('AND h.id_suplier=po.id_suplier',''),
    'date'=>array('AND h.tanggal=d.tgl_datang',''),
    'balance'=>array('p.totalwidth - p.berat_terima','p.berat_terima - p.totalwidth'),
    'weight'=>array("'d.width_recive'","'d.actual_berat'"),
    'customer'=>array('GROUP_CONCAT(DISTINCT NULLIF','GROUP_CONCAT(NULLIF')
);
foreach($mutations as $name=>$change) {
    incoming_check(strpos($source,$change[0])!==false,'Mutation target exists '.$name);
    $class='IncomingMutation_'.$name;
    $mutant=str_replace('Report_incoming_model',$class,str_replace($change[0],$change[1],$source));
    eval(substr($mutant,5)); $candidate=new $class($db); $caught=false;
    try {
        $rows=$candidate->get_rows($all,0,null);
        incoming_check(count($rows)===7,'No duplicated details');
        $sample=$candidate->get_rows($candidate->normalize_filters(array('search'=>'CPD0661B')),0,null);
        incoming_check(count($sample)===1,'Sample one row');
        $v=$candidate->format_row($sample[0]);
        incoming_check($v[14]==='ASEP' && $v[9]===265.0 && $v[11]===-1.0,'Source/header/balance invariant');
        $multi=$candidate->get_rows($candidate->normalize_filters(array('search'=>'MULTI%_!')),0,null);
        incoming_check($candidate->format_row($multi[0])[18]==='Customer A; Customer B','Customer dedup invariant');
    } catch(RuntimeException $e) { $caught=true; }
    incoming_check($caught,'Regression must reject mutation '.$name);
}
echo "PASS mutation: five intentional supplier/date/balance/weight/customer regressions rejected on in-memory class copies\n";
