<?php
// CLI fixtures must never use the application's nonlocal development configuration.
if (PHP_SAPI !== 'cli') exit('CLI only');
date_default_timezone_set('Asia/Jakarta');
define('BASEPATH', dirname(dirname(__DIR__)).'/system/');
define('APPPATH', dirname(dirname(__DIR__)).'/application/');
function incoming_check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
class BF_Model {
    public $db;
    public function __construct($db) { $this->db=$db; }
}
class IncomingTestResult {
    private $statement;
    public function __construct($statement) { $this->statement=$statement; }
    public function row_array() { return $this->statement->fetch(PDO::FETCH_ASSOC); }
    public function result_array() { return $this->statement->fetchAll(PDO::FETCH_ASSOC); }
    public function unbuffered_row($type='array') { return $this->statement->fetch(PDO::FETCH_ASSOC); }
    public function free_result() { $this->statement->closeCursor(); }
}
class IncomingTestDatabase {
    public $connection;
    public $statements=array();
    public function __construct() {
        foreach (array('HOST','USER','PASSWORD','DATABASE') as $key)
            incoming_check(getenv('REPORT_TEST_'.$key)!==false,'Missing REPORT_TEST_'.$key);
        $host=getenv('REPORT_TEST_HOST');
        incoming_check(in_array($host,array('localhost','127.0.0.1','metalsindo_db','db','db_metalsindo'),true), 'Tests require a local Docker/loopback host');
        incoming_check(getenv('REPORT_TEST_DATABASE')==='metalsindo_dev','Tests require metalsindo_dev');
        $this->connection=new PDO('mysql:host='.$host.';dbname=metalsindo_dev;charset=utf8',
            getenv('REPORT_TEST_USER'),getenv('REPORT_TEST_PASSWORD'),array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
        incoming_check($this->query('SELECT DATABASE() name')->row_array()['name']==='metalsindo_dev','Local target confirmed');
    }
    public function query($sql,$bindings=array()) {
        $this->statements[]=$sql;
        $statement=$this->connection->prepare($sql); $statement->execute($bindings);
        return new IncomingTestResult($statement);
    }
    public function temporary_tables($tables) {
        foreach ($tables as $table) {
            $schema=$this->query('SHOW CREATE TABLE '.$table)->row_array();
            $this->query(preg_replace('/^CREATE TABLE/','CREATE TEMPORARY TABLE',$schema['Create Table']));
        }
    }
}
$db=new IncomingTestDatabase();
if (realpath($_SERVER['SCRIPT_FILENAME'])===__FILE__) {
    foreach (array('dt_incoming','tr_incoming','dt_trans_po','tr_purchase_order','master_supplier','ms_gudang','stock_material','menus','permissions','group_permissions','user_permissions') as $table)
        $db->query('SHOW COLUMNS FROM '.$table);
    echo "PASS baseline: PHP ".PHP_VERSION."; local metalsindo_dev schema available; no application config loaded\n";
}
