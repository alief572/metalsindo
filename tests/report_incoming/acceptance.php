<?php
require __DIR__.'/excel.php';
// Additional pathological lookup cases remain temporary and local.
incoming_insert('ms_gudang',array('id_gudang'=>3,'nama_gudang'=>'Gudang Finish Good'));
incoming_insert('master_supplier',array('id_suplier'=>'S1','name_suplier'=>'PT.MICS STEEL INDONESIA'));
incoming_check(count($model->get_rows($all,0,null))===7,'Duplicate master does not multiply details');
incoming_insert('dt_trans_po',array('id_dt_po'=>'P1-1','no_po'=>'P2','totalwidth'=>999,'berat_terima'=>888,'close_po'=>'Y'));
$sample=$model->format_row($model->get_rows($model->normalize_filters(array('search'=>'CPD0661B')),0,null)[0]);
incoming_check($sample[7]===null && $sample[10]===null && $sample[11]===null && $sample[14]==='Belum terverifikasi','Ambiguous PO is not arbitrarily selected');
// Use the real CI mysqli result API to check the generator on actual local snapshot tables.
function log_message($level,$message) {}
function is_php($version) { return version_compare(PHP_VERSION,$version,'>='); }
require BASEPATH.'database/DB.php';
$ci=DB(array('hostname'=>getenv('REPORT_TEST_HOST'),'username'=>getenv('REPORT_TEST_USER'),
    'password'=>getenv('REPORT_TEST_PASSWORD'),'database'=>'metalsindo_dev','dbdriver'=>'mysqli','dbprefix'=>'',
    'pconnect'=>false,'db_debug'=>false,'cache_on'=>false,'cachedir'=>'','char_set'=>'utf8','dbcollat'=>'utf8_general_ci','save_queries'=>true));
$real=new Report_incoming_model($ci); $oracle=new IncomingTestDatabase();
$expected=(int)$oracle->query('SELECT COUNT(*) n FROM dt_incoming')->row_array()['n'];
incoming_check($real->count_rows($all)===$expected,'Actual local source count');
$page=$real->get_rows($all,0,25); $first=array(); $ids=array();
foreach($real->export_rows($all) as $row) { $ids[]=$row['detail_id']; if(count($first)<25) $first[]=$row; }
incoming_check(count($ids)===$expected && count(array_unique($ids))===$expected && $page===$first,'Actual CI export generator/count/page/cardinality agreement');
if($page) {
    $detail=$oracle->query('SELECT * FROM dt_incoming WHERE id_dt_incoming=?',array($page[0]['detail_id']))->row_array();
    $v=$real->format_row($page[0]);
    incoming_check($v[17]===$detail['lotno'] && $v[9]==($detail['width_recive']===null ? null : (float)$detail['width_recive']),'Actual local sample source');
}
echo 'PASS acceptance: pathological lookups, actual CI mysqli generator, '.$expected.' unique local snapshot details, source/page/export reconciliation; no nonlocal config'.PHP_EOL;
