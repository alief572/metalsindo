<?php
if (PHP_SAPI !== 'cli') exit('CLI only');
define('BASEPATH', __DIR__);
class CI_Model { public $db; }
require dirname(dirname(__DIR__)).'/application/modules/adjustmentstock/models/Adjustment_balance_model.php';
function check_adjust($ok, $label) { if (!$ok) throw new RuntimeException($label); }
class AdjustmentTestResult
{
    private $statement;
    public function __construct($statement) { $this->statement=$statement; }
    public function result_array() { return $this->statement->fetchAll(PDO::FETCH_ASSOC); }
}
class AdjustmentTestDatabase
{
    public $database='metalsindo_dev';
    public $db_debug=true;
    public $connection;
    public $failHistory=false;
    public $locks=0;
    public $queries=0;
    public function __construct()
    {
        $host=getenv('BOOKING_DB_HOST');
        check_adjust(in_array($host,array('metalsindo_db','db_metalsindo','127.0.0.1','localhost'),true),'Local Docker host required');
        $this->connection=new PDO('mysql:host='.$host.';dbname=metalsindo_dev;charset=utf8',getenv('BOOKING_DB_USER'),getenv('BOOKING_DB_PASSWORD'),array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    }
    public function query($sql,$bindings=array())
    {
        $this->queries++;
        if (strpos($sql,'FOR UPDATE')!==false) $this->locks++;
        if ($this->failHistory && strpos($sql,'INSERT INTO adjustment_stock')===0) throw new RuntimeException('Injected history failure');
        $s=$this->connection->prepare($sql); $s->execute($bindings);
        return new AdjustmentTestResult($s);
    }
    public function trans_begin() { return $this->connection->beginTransaction(); }
    public function trans_commit() { return $this->connection->commit(); }
    public function trans_rollback() { return $this->connection->inTransaction() ? $this->connection->rollBack() : true; }
    public function trans_status() { return true; }
    public function insert_id() { return $this->connection->lastInsertId(); }
}
$db=new AdjustmentTestDatabase();
$model=new Adjustment_balance_model(); $model->db=$db;
if (isset($argv[1]) && $argv[1]==='--benchmark') {
    $candidate=$db->query("SELECT s.id_category3,COUNT(*) n FROM stock_material s WHERE s.aktif='Y' AND s.qty>0 AND EXISTS (SELECT 1 FROM ms_inventory_category3 m WHERE m.id_category3=s.id_category3 AND m.deleted=0) GROUP BY s.id_category3 ORDER BY n DESC LIMIT 1")->result_array();
    check_adjust(count($candidate)===1,'Benchmark requires existing active material');
    $started=microtime(true); $options=$model->options($candidate[0]['id_category3']); $elapsed=microtime(true)-$started;
    check_adjust($elapsed<3,'Options benchmark exceeds 3 seconds');
    echo 'PASS SELECT-only options benchmark: '.count($options['lots']).' lots in '.round($elapsed,3)."s\n";
    exit;
}
// Shadow every production table used by the model on this connection before any fixture write.
$tables=array('stock_material','adjustment_stock','warehouse_stock','warehouse_history','ms_inventory_category3','ms_bentuk','ms_gudang','ms_warehouse');
foreach ($tables as $table) {
    $definition=$db->query('SHOW CREATE TABLE metalsindo_dev.'.$table)->result_array();
    $db->query(preg_replace('/^CREATE TABLE /','CREATE TEMPORARY TABLE ',$definition[0]['Create Table']));
}
function fixture_insert($table,$data) {
    global $db;
    $db->query('INSERT INTO '.$table.' (`'.implode('`,`',array_keys($data)).'`) VALUES ('.implode(',',array_fill(0,count($data),'?')).')',array_values($data));
}
function reset_adjust()
{
    global $db,$tables;
    $db->failHistory=false;
    foreach ($tables as $table) $db->query('DELETE FROM '.$table);
    fixture_insert('ms_inventory_category3',array('id_category3'=>'MAT','nama'=>'Material server','id_bentuk'=>'ROLL','thickness'=>1,'deleted'=>0));
    fixture_insert('ms_bentuk',array('id_bentuk'=>'ROLL','nm_bentuk'=>'ROLL'));
    foreach (array(1,2) as $id) {
        fixture_insert('ms_gudang',array('id_gudang'=>$id,'nama_gudang'=>'Gudang '.$id));
        fixture_insert('ms_warehouse',array('id'=>$id,'wh_name'=>'WH'.$id));
    }
    fixture_insert('stock_material',array('id_stock'=>1,'id_category3'=>'MAT','nama_material'=>'Material server','lotno'=>'LOT-A',
        'qty'=>10,'weight'=>100,'totalweight'=>1000,'sisa_spk'=>90,'booking'=>0,'delivery_book'=>0,'costbook'=>0,
        'id_bentuk'=>'ROLL','width'=>100,'length'=>0,'thickness'=>1,'id_gudang'=>1,'aktif'=>'Y','customer'=>'C','created_by'=>'original'));
    fixture_insert('stock_material',array('id_stock'=>2,'id_category3'=>'MAT','nama_material'=>'Material server','lotno'=>'LOT-A',
        'qty'=>2,'weight'=>100,'totalweight'=>200,'sisa_spk'=>100,'booking'=>0,'delivery_book'=>0,'costbook'=>0,
        'id_bentuk'=>'ROLL','width'=>100,'length'=>0,'thickness'=>1,'id_gudang'=>2,'aktif'=>'Y','customer'=>'C'));
    foreach (array(1=>1000,2=>200) as $id=>$kg) fixture_insert('warehouse_stock',array('id_material'=>'MAT','id_gudang'=>$id,'qty_stock'=>$kg,'qty_booking'=>0));
}
function input_adjust($type,$qty,$kg,$extra=array()) {
    return array_merge(array('adjustment'=>$type,'id_material'=>'MAT','id_stock'=>1,'total_qty'=>(string)$qty,'total_jumlah_berat'=>(string)$kg,'note'=>'Timbang ulang','id_gudang_baru'=>2),$extra);
}
function row_adjust($table,$where='1=1') {
    global $db; $rows=$db->query('SELECT * FROM '.$table.' WHERE '.$where)->result_array(); return $rows ? $rows[0] : null;
}
function snapshot_adjust() {
    global $db; $result=array();
    foreach (array('stock_material','warehouse_stock','warehouse_history','adjustment_stock') as $t) $result[$t]=$db->query('SELECT * FROM '.$t)->result_array();
    return json_encode($result);
}
function rejected_adjust($input,$message)
{
    global $model;
    $before=snapshot_adjust();
    try { $model->save($input,'tester'); } catch (InvalidArgumentException $e) {
        check_adjust(strpos($e->getMessage(),$message)!==false,'Unexpected rejection: '.$e->getMessage());
        check_adjust($before===snapshot_adjust(),'Rejected save must roll back every table'); return;
    }
    throw new RuntimeException('Expected validation rejection: '.$message);
}
reset_adjust();
$start=microtime(true);
$code=$model->save(input_adjust('MINUS',0,25,array('mother_qty'=>999,'id_gudang'=>999,'nama_material'=>'Forged','lotno'=>'Forged')),'tester');
$lot=row_adjust('stock_material','id_stock=1'); $hist=row_adjust('adjustment_stock');
check_adjust($lot['qty']==10 && $lot['totalweight']==975 && $lot['sisa_spk']==87.5,'MINUS kg only');
check_adjust($hist['mother_qty']==10 && $hist['nama_material']==='Material server' && $hist['lotno']==='LOT-A' && $hist['total_berat']===null,'Server metadata and unit NULL');
check_adjust(row_adjust('warehouse_stock','id_gudang=1')['qty_stock']==975,'MINUS ledger');
check_adjust(row_adjust('warehouse_history')['no_ipp']===$code,'History transaction reference');
$model->save(input_adjust('PLUS',0,25),'tester');
check_adjust(row_adjust('stock_material','id_stock=1')['totalweight']==1000,'PLUS existing kg only');
check_adjust(count($db->query('SELECT * FROM stock_material')->result_array())===2,'PLUS existing does not insert duplicate lot');
check_adjust(count($db->query('SELECT DISTINCT id_transaksi FROM adjustment_stock')->result_array())===2,'Unique sequential codes');
reset_adjust(); $model->save(input_adjust('MINUS',5,0),'tester');
check_adjust(row_adjust('stock_material','id_stock=1')['sisa_spk']==180 && row_adjust('warehouse_stock','id_gudang=1')['qty_stock']==1000,'Qty only preserves kg');
reset_adjust(); $model->save(input_adjust('MUTASI',0,25,array('id_stock_tujuan'=>2)),'tester');
check_adjust(row_adjust('stock_material','id_stock=1')['totalweight']==975 && row_adjust('stock_material','id_stock=2')['totalweight']==225,'Weight-only transfer');
check_adjust(row_adjust('stock_material','id_stock=2')['qty']==2,'Weight-only target preserves qty');
check_adjust(row_adjust('warehouse_history',"id_gudang=2")['jenis_transaksi']==='Adjustment Mutasi (+)','Target ledger marked incoming');
check_adjust(row_adjust('warehouse_stock','id_gudang=1')['qty_stock']+row_adjust('warehouse_stock','id_gudang=2')['qty_stock']==1200,'Transfer conserves warehouse kg');
reset_adjust(); $model->save(input_adjust('MUTASI',2,100,array('id_stock_tujuan'=>2)),'tester');
check_adjust(row_adjust('stock_material','id_stock=1')['qty']==8 && row_adjust('stock_material','id_stock=2')['qty']==4,'Independent qty/weight transfer');
check_adjust(row_adjust('stock_material','id_stock=1')['totalweight']==900 && row_adjust('stock_material','id_stock=2')['totalweight']==300,'Transfer gross independent');
reset_adjust(); $db->query('DELETE FROM stock_material WHERE id_stock=2'); $db->query('DELETE FROM warehouse_stock WHERE id_gudang=2');
$model->save(input_adjust('MUTASI',2,100),'tester');
check_adjust(row_adjust('stock_material','id_gudang=2')['totalweight']==100 && row_adjust('warehouse_stock','id_gudang=2')['qty_stock']==100,'Transfer creates lot and ledger');
check_adjust(row_adjust('stock_material','id_gudang=2')['customer']==='C','New transfer lot preserves provenance');
reset_adjust(); $db->query('UPDATE stock_material SET sisa_spk=100 WHERE id_stock=1');
$model->save(input_adjust('MUTASI',10,1000,array('id_stock_tujuan'=>2)),'tester');
check_adjust(row_adjust('stock_material','id_stock=1')['aktif']==='N' && row_adjust('stock_material','id_stock=2')['qty']==12,'Full transfer preserves source references');
reset_adjust(); $db->query('UPDATE stock_material SET sisa_spk=100 WHERE id_stock=1');
$model->save(input_adjust('MINUS',10,1000),'tester');
check_adjust(row_adjust('stock_material','id_stock=1')['qty']==0 && row_adjust('stock_material','id_stock=1')['aktif']==='N','Full MINUS depletion');
reset_adjust(); $db->query('UPDATE stock_material SET booking=2,delivery_book=4 WHERE id_stock=1');
rejected_adjust(input_adjust('MINUS',0,25),'booking'); rejected_adjust(input_adjust('MUTASI',0,25,array('id_stock_tujuan'=>2)),'booking');
$model->save(input_adjust('PLUS',10,1000),'tester');
check_adjust(row_adjust('stock_material','id_stock=1')['booking']==1 && row_adjust('stock_material','id_stock=1')['delivery_book']==2,'PLUS preserves production/delivery booking totals');
check_adjust(row_adjust('stock_material','id_stock=1')['created_by']==='original','Existing provenance preserved');
reset_adjust();
$model->save(input_adjust('PLUS',2,100,array('id_stock'=>'','plus_lot'=>'new','lotno'=>'NEW','id_gudang'=>1,'width'=>'100','length'=>'0','thickness'=>999)),'tester');
check_adjust(row_adjust('stock_material',"lotno='NEW'")['sisa_spk']==50 && row_adjust('stock_material',"lotno='NEW'")['thickness']==1,'PLUS new initializes operational stock and server thickness');
rejected_adjust(input_adjust('PLUS',2,100,array('id_stock'=>'','lotno'=>'LOT-A','id_gudang'=>1,'width'=>100,'length'=>0)),'sudah ada');
rejected_adjust(input_adjust('PLUS',0,25,array('id_stock'=>'','lotno'=>'NEW2','id_gudang'=>1)),'Lot baru');
reset_adjust();
foreach (array(
    array(input_adjust('MINUS',0,901),'melebihi'),array(input_adjust('MINUS',10,25),'Saldo akhir'),
    array(input_adjust('MINUS',7,0),'presisi'),array(input_adjust('MUTASI',0,25),'wajib'),
    array(input_adjust('MUTASI',2,100,array('id_gudang_baru'=>1)),'berbeda'),
    array(input_adjust('MINUS',0,25,array('id_material'=>'OTHER')),'Material'),
    array(input_adjust('MINUS',0,25,array('id_stock'=>999)),'Lot stok'),
    array(input_adjust('MINUS',0,25,array('id_stock'=>'1abc')),'ID'),
    array(input_adjust('INVALID',0,25),'Jenis'),array(input_adjust('MINUS',0,0),'keduanya'),
    array(input_adjust('MINUS','1.5',25),'Qty'),array(input_adjust('MINUS',0,'1.001'),'Total berat'),
    array(input_adjust('MINUS',0,-25),'Total berat'),array(input_adjust('MINUS',0,25,array('note'=>'')),'note')
) as $case) rejected_adjust($case[0],$case[1]);
rejected_adjust(input_adjust('MUTASI',2,100),'sudah ada');
rejected_adjust(input_adjust('PLUS',2,100,array('plus_lot'=>'new')),'Pilihan lot');
$db->query('UPDATE stock_material SET booking=0,delivery_book=1 WHERE id_stock=1');
rejected_adjust(input_adjust('MINUS',0,25),'booking');
reset_adjust(); $db->query('DELETE FROM warehouse_stock WHERE id_gudang=1');
rejected_adjust(input_adjust('MINUS',5,0),'belum tersedia');
reset_adjust();
$db->query('UPDATE stock_material SET width=200 WHERE id_stock=2');
rejected_adjust(input_adjust('MUTASI',0,25,array('id_stock_tujuan'=>2)),'dimensi');
reset_adjust(); $db->query('UPDATE stock_material SET customer=? WHERE id_stock=2',array('OTHER'));
rejected_adjust(input_adjust('MUTASI',0,25,array('id_stock_tujuan'=>2)),'customer');
reset_adjust(); $db->query('UPDATE stock_material SET sisa_spk=NULL WHERE id_stock=1');
rejected_adjust(input_adjust('MINUS',0,25),'Berat operasional');
reset_adjust(); fixture_insert('warehouse_stock',array('id_material'=>'MAT','id_gudang'=>1,'qty_stock'=>100));
rejected_adjust(input_adjust('MINUS',0,25),'Saldo gudang ganda');
reset_adjust(); $db->query('UPDATE warehouse_stock SET qty_stock=10 WHERE id_gudang=1');
rejected_adjust(input_adjust('MINUS',0,25),'Saldo gudang tidak cukup');
reset_adjust(); $db->failHistory=true; $before=snapshot_adjust();
try { $model->save(input_adjust('MINUS',0,25),'tester'); throw new LogicException('Expected injected failure'); }
catch (RuntimeException $e) { check_adjust($e->getMessage()==='Injected history failure','Injected error surfaced'); }
check_adjust(snapshot_adjust()===$before && $db->db_debug===true,'History failure rolls back stock and ledgers and restores db_debug');
$db->failHistory=false;
$other=new AdjustmentTestDatabase();
$owner=$other->query('SELECT IS_FREE_LOCK(?) AS free',array('adjstock:metalsindo_dev'))->result_array();
check_adjust($owner[0]['free']==1,'Advisory lock released on failed save');
$db->query('SELECT GET_LOCK(?,0)',array('adjstock:metalsindo_dev'));
$busy=$other->query('SELECT GET_LOCK(?,0) AS acquired',array('adjstock:metalsindo_dev'))->result_array();
check_adjust($busy[0]['acquired']==0,'Concurrent connection cannot acquire adjustment lock');
$db->query('SELECT RELEASE_LOCK(?)',array('adjstock:metalsindo_dev'));
$free=$other->query('SELECT GET_LOCK(?,0) AS acquired',array('adjstock:metalsindo_dev'))->result_array();
check_adjust($free[0]['acquired']==1,'Next connection can acquire released lock');
$other->query('SELECT RELEASE_LOCK(?)',array('adjstock:metalsindo_dev'));
check_adjust($db->locks>0,'FOR UPDATE row locks used');
reset_adjust(); $options=$model->options('MAT');
check_adjust(count($options['lots'])===2 && count($options['warehouses'])===2 && !isset($options['lots'][0]['harga_deal']),'Options cardinality and whitelist');
foreach (array(array(10,0,'Y'),array(11,5,'N'),array(12,5,null)) as $fixture) {
    fixture_insert('stock_material',array('id_stock'=>$fixture[0],'id_category3'=>'MAT','lotno'=>'OLD-'.$fixture[0],'qty'=>$fixture[1],'aktif'=>$fixture[2],'id_gudang'=>1));
}
$options=$model->options('MAT');
check_adjust(count($options['lots'])===5,'Options must not silently hide zero-Qty, inactive or legacy lots');
foreach ($options['lots'] as $lot) check_adjust(array_key_exists('aktif',$lot),'Lot availability status must be explicit');
echo 'PASS adjustment transactions: temporary tables, PLUS/MINUS/MUTASI, balances, ledgers, metadata, booking, rollback, locks and options ('.round(microtime(true)-$start,3)."s)\n";
