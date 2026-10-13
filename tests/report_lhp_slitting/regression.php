<?php
// PHP 5.6 CLI regression: fixtures shadow real tables for this connection only.
if (PHP_SAPI !== 'cli') exit('CLI only');
define('BASEPATH', __DIR__);
class BF_Model { public $db; public function __construct($db) { $this->db = $db; } }
class LhpResult {
    private $statement;
    public function __construct($statement) { $this->statement = $statement; }
    public function result_array() { return $this->statement->fetchAll(PDO::FETCH_ASSOC); }
    public function row_array() { return $this->statement->fetch(PDO::FETCH_ASSOC); }
}
class LhpDatabase {
    public $connection;
    public function __construct() {
        foreach (array('HOST','USER','PASSWORD','DATABASE') as $key) {
            if (getenv('REPORT_TEST_'.$key) === false) throw new RuntimeException('Missing REPORT_TEST_'.$key);
        }
        $this->connection = new PDO('mysql:host='.getenv('REPORT_TEST_HOST').';dbname='.getenv('REPORT_TEST_DATABASE').';charset=utf8',
            getenv('REPORT_TEST_USER'), getenv('REPORT_TEST_PASSWORD'), array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    }
    public function query($sql, $bindings = array()) {
        $statement = $this->connection->prepare($sql); $statement->execute($bindings); return new LhpResult($statement);
    }
}
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
require dirname(dirname(__DIR__)).'/application/modules/report_lhp_slitting/models/Report_lhp_slitting_model.php';
$db = new LhpDatabase();
foreach (array('tr_spk_aktual_fg','dt_spk_aktual_detail_fg') as $table) {
    $schema=$db->query('SHOW CREATE TABLE '.$table)->row_array();
    $db->query(preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $schema['Create Table']));
}
$db->query("INSERT INTO tr_spk_aktual_fg (id_spk_aktual,id_dt_spkaktualdetail,no_surat_produksi,date_production,tgl_produksi,nama_material,bfinishgood,lsisa_aktual,bsisa_aktual,bscrap_aktual,lebar_ng_in,berat_ng_in,lebar_ng_ek,berat_ng_ek,tweightslitting,ng_packinglist)
    VALUES ('L1',10,'PROD-A','2026-05-06','2026-06-01','Fallback product',21000,1.5,300,90,NULL,10,0,0,21400,-10),
           ('L2',20,'PROD-B',NULL,'2026-05-07','Copper',0,0,0,0,0,0,0,0,0,0),
           ('L3',30,'PROD-C','0000-00-00','2026-05-08','Copper',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL)");
$db->query("INSERT INTO dt_spk_aktual_detail_fg (id,id_spk_aktual,id_dt_spkaktualdetail,no_surat,namacustomer,nmmaterial,weight,width,lot_slitting,totalaktual)
    VALUES (1,'L1',10,'MKT-A','Customer A','Product A',12.4,15.2,'LOT-1',18600),
           (2,'L1',10,'NONSPK','Customer B','',12.4,15.2,'LOT-2',2400),
           (3,'L2',20,'NONSPK',NULL,'Copper',0,0,'LOT%_!',0),
           (4,'L3',30,'=SUM(1,2)','<script>','Copper',NULL,NULL,'LOT-4',NULL),
           (5,'L1',999,'MISMATCH','Hidden','Hidden',1,1,'INVALID',1)");
$model = new Report_lhp_slitting_model($db);
$all = $model->normalize_filters(array());
$rows = $model->get_rows($all,0,null);
check(count(Report_lhp_slitting_model::headers()) === 19, '19 headers');
check($model->count_rows($all) === 4 && count($rows) === 4, 'One row per matching detail');
check(array_column($rows,'detail_id') === array('4','3','1','2'), 'Production date ordering and stable tie break');
$first = $model->format_row($rows[2]);
check($first[3] === '2026-05-06' && $first[5] === 12.4 && $first[6] === 15.2 && $first[8] === 18600.0, 'Date and weight mapping');
check($first[9] === 21000.0 && $first[13] === null && $first[18] === -10.0, 'Summary null and negative values');
check($model->format_row($rows[3])[4] === 'Fallback product', 'Empty product fallback');
check($model->format_row($rows[3])[2] === 'Customer B', 'NONSPK retains customer');
check($model->format_row($rows[0])[3] === '2026-05-08', 'Zero date fallback');
check(count($model->get_rows($model->normalize_filters(array('start_date'=>'2026-05-06','end_date'=>'2026-05-06')),0,null)) === 2, 'Inclusive date boundaries');
foreach (array('PROD-A'=>2,'MKT-A'=>1,'Customer B'=>1,'Product A'=>1,'Fallback product'=>1,'LOT-1'=>1,'LOT%_!'=>1,"' OR 1=1 --"=>0) as $term=>$count) {
    check($model->count_rows($model->normalize_filters(array('search'=>$term))) === $count, 'Search: '.$term);
}
foreach (array(array('start_date'=>'2026-02-30'),array('start_date'=>'2026-06-01','end_date'=>'2026-05-01'),array('search'=>array('bad'))) as $input) {
    $failed=false; try { $model->normalize_filters($input); } catch (InvalidArgumentException $e) { $failed=true; }
    check($failed, 'Reject invalid filters');
}
for ($column=0;$column<19;$column++) foreach (array('asc','desc') as $direction) {
    $sorted=$model->get_rows($all,0,null,$column,$direction);
    $paged=array_merge($model->get_rows($all,0,2,$column,$direction),$model->get_rows($all,2,2,$column,$direction));
    check($paged === $sorted, 'Page/export equality column '.$column.' '.$direction);
}
check($model->get_rows($all,100,25) === array(), 'Out of range page');
echo "PASS model: mapping, joins, dates, search, NULL/zero, 38 sorting/page-export comparisons\n";
