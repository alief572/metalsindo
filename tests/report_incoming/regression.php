<?php
require __DIR__.'/fixtures.php';
require APPPATH.'modules/report_incoming/models/Report_incoming_model.php';
$model=new Report_incoming_model($db);
$db->statements=array();
$all=$model->normalize_filters(array());
incoming_check(count(Report_incoming_model::headers())===19,'19 concept columns');
incoming_check($model->count_rows($all)===7,'One row per detail including missing lookup');
$rows=$model->get_rows($all,0,null);
incoming_check(count(array_unique(array_column($rows,'detail_id')))===7,'No duplicate detail IDs');
$sample=$model->get_rows($model->normalize_filters(array('search'=>'CPD0661B')),0,null);
$v=$model->format_row($sample[0],1);
incoming_check($v[1]==='P2600999' && $v[2]==='PO-MP/26/IX/0263' && $v[3]==='2026-09-11','PO/PR mapping');
incoming_check($v[6]===380.0 && $v[7]===264.0 && $v[9]===265.0 && $v[10]===265.0 && $v[11]===-1.0 && $v[12]==='Open','Excel sample numerical contract');
incoming_check($v[14]==='ASEP' && $v[15]==='001088/MICS/09/26' && $v[16]==='Gudang Finish Good','Header supplier/date match');
incoming_check($v[18]==='PT. TRI CENTRUM FORTUNA Plant II ( TCF )','Historical deleted stock customer');
$multi=$model->get_rows($model->normalize_filters(array('search'=>'MULTI%_!')),0,null);
incoming_check(count($multi)===1 && $model->format_row($multi[0])[18]==='Customer A; Customer B','Literal wildcard search and unique customers');
$range=$model->normalize_filters(array('start_date'=>'2026-09-23','end_date'=>'2026-09-23'));
incoming_check($model->count_rows($range)===2 && $model->format_row($model->get_rows($range,0,1)[0])[10]===265.0,'Inclusive date and unchanged current PO total');
$conflict=$model->format_row($model->get_rows($model->normalize_filters(array('search'=>'IC-CONFLICT')),0,null)[0]);
incoming_check($conflict[14]==='Belum terverifikasi' && $conflict[15]==='Belum terverifikasi' && $conflict[7]===null && $conflict[11]===null,'Conflicting header and missing values');
$zero=$model->format_row($model->get_rows($model->normalize_filters(array('search'=>'ZERO-LOT')),0,null)[0]);
incoming_check($zero[3]===null && $zero[8]===null && $zero[9]===0.0 && $zero[10]===0.0 && $zero[12]==='Closed' && $zero[14]===null,'Invalid dates, zero, manual Closed, NULL PIC');
incoming_check($model->get_rows($model->normalize_filters(array('search'=>'IMPOSSIBLE')),0,null)[0]['c8']===null,'Impossible date normalized');
foreach(array('ASEP','001088','Customer A','Gudang Finish Good','PT.MICS','P2600999','PO-MP','COLD ROLLED') as $search)
    incoming_check($model->count_rows($model->normalize_filters(array('search'=>$search)))>0,'Search field '.$search);
foreach(array(array('start_date'=>'bad'),array('start_date'=>'2026-02-31'),array('start_date'=>'2026-09-24','end_date'=>'2026-09-23'),array('search'=>array('bad'))) as $bad) {
    $rejected=false; try { $model->normalize_filters($bad); } catch(InvalidArgumentException $e) { $rejected=true; }
    incoming_check($rejected,'Malformed filters rejected');
}
for($column=0;$column<19;$column++) foreach(array('asc','desc') as $direction) {
    $full=$model->get_rows($all,0,null,$column,$direction);
    incoming_check($model->get_rows($all,1,2,$column,$direction)===array_slice($full,1,2),'Sorting/page/export agreement '.$column.$direction);
}
incoming_check(count($model->get_rows($all,0,-1))===1,'Bounded pagination');
foreach($db->statements as $sql)
    incoming_check(preg_match('/^SELECT /i',$sql) || $sql==='SET SESSION group_concat_max_len = 16777216','Model never writes business data');
echo "PASS regression: mapping, one-row cardinality, historical lookup, literal search, current totals, dates/NULL/zero/negative, 38 sorting/page comparisons\n";
