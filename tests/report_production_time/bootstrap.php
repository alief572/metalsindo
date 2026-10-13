<?php
if (PHP_SAPI !== 'cli') exit('CLI only');
define('BASEPATH',__DIR__);
class BF_Model { public $db; public function __construct($db) { $this->db=$db; } }
class TimeResult {
    private $statement;
    public function __construct($statement) { $this->statement=$statement; }
    public function result_array() { return $this->statement->fetchAll(PDO::FETCH_ASSOC); }
    public function row_array() { return $this->statement->fetch(PDO::FETCH_ASSOC); }
}
class TimeDatabase {
    public $connection;
    public function __construct() {
        define('ENVIRONMENT','development');
        require dirname(dirname(__DIR__)).'/application/config/database.php';
        require dirname(dirname(__DIR__)).'/application/config/development/database.php';
        $settings=$db[$active_group];
        $this->connection=new PDO('mysql:host='.$settings['hostname'].';dbname='.$settings['database'].';charset=utf8',
            $settings['username'],$settings['password'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    }
    public function query($sql,$bindings=array()) {
        $statement=$this->connection->prepare($sql); $statement->execute($bindings); return new TimeResult($statement);
    }
    public function shadow($tables) {
        foreach ($tables as $table) {
            $schema=$this->query('SHOW CREATE TABLE '.$table)->row_array();
            $this->query(preg_replace('/^CREATE TABLE/','CREATE TEMPORARY TABLE',$schema['Create Table']));
        }
    }
}
function check($condition,$message) { if (!$condition) throw new RuntimeException($message); }
require dirname(dirname(__DIR__)).'/application/modules/report_production_time/models/Report_production_time_model.php';
