<?php
require dirname(__DIR__).'/stock_booking/bootstrap.php';
class CI_Model { public $db; }
require dirname(dirname(__DIR__)).'/application/modules/stock_material/models/Warehouse_stock_date_model.php';
$model=new Warehouse_stock_date_model(); $model->db=new BookingDatabase();
$latest=$model->db->query('SELECT tgl FROM stock_material_backup ORDER BY tgl DESC LIMIT 1')->row_array();
foreach ($model->warehouses() as $warehouse=>$name) {
    foreach (array($model->today(),substr($latest['tgl'],0,10)) as $date) {
        $model->db->statements=array(); $start=microtime(true);
        $result=$model->table(array('gudang'=>(string)$warehouse,'stock_date'=>$date,'length'=>10));
        $elapsed=microtime(true)-$start;
        echo json_encode(array('warehouse'=>$warehouse,'date'=>$date,'seconds'=>round($elapsed,4),'rows'=>$result['recordsFiltered'],'weight'=>$result['recordsAset'])),"\n";
        $statements=$model->db->statements;
        if ($date!==$model->today()) {
            $plan=$model->db->query('EXPLAIN '.$statements[1][0],$statements[1][1])->result_array();
            foreach ($plan as $row) if ($row['table']==='a') {
                echo json_encode(array('warehouse'=>$warehouse,'key'=>$row['key'],'estimated_rows'=>$row['rows'])),"\n";
                booking_check($row['key']==='idx_raw_snapshot_date_warehouse','Historical summary uses snapshot index');
            }
        }
        booking_check($elapsed<3,'Warehouse table must load below 3 seconds locally');
    }
}
echo "PASS five warehouse local latency and historical index\n";
