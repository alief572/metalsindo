<?php
require dirname(__DIR__).'/stock_booking/bootstrap.php';
class CI_Model { public $db; }
require dirname(dirname(__DIR__)).'/application/modules/stock_material/models/Stock_raw_material_model.php';
class RawTimedDatabase extends BookingDatabase {
    public $times=array();
    public function query($sql,$bindings=array()) {
        $start=microtime(true); $result=parent::query($sql,$bindings);
        $this->times[]=round(microtime(true)-$start,5); return $result;
    }
}
$model=new Stock_raw_material_model(); $model->db=new RawTimedDatabase();
$latest=$model->db->query('SELECT tgl FROM stock_material_backup ORDER BY tgl DESC LIMIT 1')->row_array();
foreach (array(
    array('stock_date'=>$model->today()),
    array('stock_date'=>substr($latest['tgl'],0,10)),
    array('stock_date'=>'2026-09-30'),
    array('stock_date'=>substr($latest['tgl'],0,10),'series'=>'400'),
    array('stock_date'=>substr($latest['tgl'],0,10),'search'=>array('value'=>'SUS'))
) as $input) {
    $samples=array();
    for ($i=0;$i<3;$i++) {
        $model->db->statements=array(); $model->db->times=array(); $start=microtime(true);
        $result=$model->table($input+array('length'=>10));
        $elapsed=microtime(true)-$start; $samples[]=$elapsed;
        booking_check($elapsed<3,'Table latency must be below 3 seconds');
    }
    sort($samples);
    echo json_encode(array('input'=>$input,'median_seconds'=>round($samples[1],4),'query_count'=>count($model->db->statements),
        'query_seconds'=>$model->db->times,'rows'=>$result['recordsFiltered'],'weight'=>$result['recordsAset'],'snapshot'=>$result['snapshot_at'])),"\n";
    foreach ($model->db->statements as $query) {
        $explain=$model->db->query('EXPLAIN '.$query[0],$query[1])->result_array();
        foreach ($explain as $row) if ($row['table']==='a' || $row['table']==='stock_material_backup')
            echo json_encode(array('table'=>$row['table'],'type'=>$row['type'],'key'=>$row['key'],'estimated_rows'=>$row['rows'])),"\n";
    }
}
echo "PASS local current and historical latency\n";
