<?php
require __DIR__.'/bootstrap.php';
$db=new TimeDatabase(); $model=new Report_production_time_model($db);
$all=$model->normalize_filters(array()); $started=microtime(true);
$expected=(int)$db->query('SELECT COUNT(*) AS n FROM tr_spk_produksi h WHERE EXISTS (SELECT 1 FROM dt_spk_produksi_lph p WHERE p.id_spk_aktual=h.id_spkproduksi)')->row_array()['n'];
check($model->count_groups($all)===$expected,'Real count per SPK');
$page=$model->get_groups($all,0,25); $groups=$model->get_groups($all);
check(count($groups)===$expected && $page===array_slice($groups,0,25),'Real page/export agreement');
check(count(array_unique(array_column($groups,'id')))===count($groups),'No duplicated SPK');
$invalid=0; $undated=0;
foreach ($groups as $group) {
    if ($group['invalid_total']) $invalid++;
    if ($group['info'][2]===null) $undated++;
}
if ($page) {
    $group=$page[0];
    $rows=$db->query('SELECT total FROM dt_spk_produksi_lph WHERE id_spk_aktual=?',array($group['id']))->result_array();
    $sum=0.0; $hasTotal=false; $bad=false;
    foreach ($rows as $row) {
        $raw=trim((string)$row['total']);
        if ($raw==='') continue;
        if (!preg_match('/^[0-9]+(?:\.[0-9]+)?$/D',$raw)) { $bad=true; continue; }
        $sum+=(float)$raw; $hasTotal=true;
    }
    check($group['summary'][1]===($bad || !$hasTotal?null:$sum),'Independent actual total');
}
echo 'PASS existing data: '.$expected.' SPK, '.$invalid.' invalid-duration groups, '.$undated.' missing actual dates; page/export agreement; '.round((microtime(true)-$started)*1000).' ms'.PHP_EOL;
if (in_array('--export',$argv,true)) {
    require dirname(dirname(__DIR__)).'/application/libraries/PHPExcel.php';
    require dirname(dirname(__DIR__)).'/application/modules/report_production_time/libraries/Report_production_time_excel.php';
    $exporter=new Report_production_time_excel(); $book=$exporter->build($groups);
    $file=tempnam(sys_get_temp_dir(),'pt-full-');
    try {
        $exporter->save($book,$file);
        check(filesize($file)>0,'Full export saved');
        echo 'PASS full export: '.filesize($file).' bytes; peak '.round(memory_get_peak_usage(true)/1048576).' MiB; '.round(microtime(true)-$started,1).' seconds'.PHP_EOL;
    } finally { unlink($file); $book->disconnectWorksheets(); }
}
