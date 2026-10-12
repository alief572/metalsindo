<?php
require __DIR__.'/fixtures.php';
require APPPATH.'modules/report_incoming/models/Report_incoming_model.php';
// Scale only temporary fixtures, leaving the real local and nonlocal business tables untouched.
function incoming_bulk($table,$columns,$rows) {
    global $db;
    $bindings=array(); foreach($rows as $row) $bindings=array_merge($bindings,$row);
    $tuple='('.implode(',',array_fill(0,count($columns),'?')).')';
    $db->query('INSERT INTO '.$table.' ('.implode(',',$columns).') VALUES '.implode(',',array_fill(0,count($rows),$tuple)),$bindings);
}
for($batch=0;$batch<72;$batch++) {
    $details=array(); $stocks=array(); $pos=array(); $heads=array();
    for($i=$batch*500;$i<($batch+1)*500;$i++) {
        $key='BENCH-'.(int)floor($i/10); $lot='BENCH-LOT-'.$i;
        $details[]=array($key,$key,$key,'M1','Benchmark receipt',380,10,'2026-09-23',$lot,3);
        $stocks[]=array($key,$key,'M1',$lot,'Customer A',1,'N');
        if($i%10===0) {
            $pos[]=array($key,'P1',100,100,'N');
            $heads[]=array($key,$key,'2026-09-23','S1','ASEP','BENCH-SJ');
        }
    }
    incoming_bulk('dt_incoming',array('id_data','id_incoming','id_dt_po','id_material','nama_material','width','width_recive','tgl_datang','lotno','id_gudang'),$details);
    incoming_bulk('stock_material',array('id_incoming','no_po','id_category3','lotno','customer','deleted','aktif'),$stocks);
    incoming_bulk('dt_trans_po',array('id_dt_po','no_po','totalwidth','berat_terima','close_po'),$pos);
    incoming_bulk('tr_incoming',array('id_data','id_incoming','tanggal','id_suplier','pic','sj_supplier'),$heads);
}
$model=new Report_incoming_model($db); $all=$model->normalize_filters(array());
$volume=(int)$db->query('SELECT COUNT(*) n FROM dt_incoming')->row_array()['n'];
incoming_check($volume>=36000,'Representative detail volume');
foreach(array('count','page','search_count','search_page') as $operation) {
    $filters=strpos($operation,'search')!==false ? $model->normalize_filters(array('search'=>'Customer A')) : $all;
    $start=microtime(true);
    if(strpos($operation,'count')!==false) $model->count_rows($filters); else $model->get_rows($filters,0,25);
    $elapsed=microtime(true)-$start;
    incoming_check($elapsed<=3,$operation.' exceeds 3 seconds: '.$elapsed);
    echo 'PASS performance '.$operation.': '.round($elapsed*1000).' ms on '.$volume.' temporary details'.PHP_EOL;
}
