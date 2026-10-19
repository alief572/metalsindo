<?php
if (PHP_SAPI !== 'cli') exit('CLI only');
date_default_timezone_set('Asia/Jakarta');
define('BASEPATH', dirname(dirname(__DIR__)).'/system/');
function booking_check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
class BF_Model {
    public $db;
    public function __construct($db) { $this->db = $db; }
}
class BookingResult {
    private $statement;
    public function __construct($statement) { $this->statement = $statement; }
    public function row_array() { return $this->statement->fetch(PDO::FETCH_ASSOC); }
    public function result_array() { return $this->statement->fetchAll(PDO::FETCH_ASSOC); }
    public function num_rows() { return $this->statement->rowCount(); }
}
class BookingDatabase {
    public $connection;
    public $statements = array();
    public function __construct($database = 'metalsindo_dev') {
        $host = getenv('BOOKING_DB_HOST');
        booking_check(in_array($host, array('db', 'metalsindo_db', 'db_metalsindo', '127.0.0.1', 'localhost'), true), 'Local host required');
        booking_check(in_array($database, array('metalsindo_dev', 'metalsindo_live'), true), 'Unexpected schema');
        $this->connection = new PDO('mysql:host='.$host.';dbname='.$database.';charset=utf8', getenv('BOOKING_DB_USER'), getenv('BOOKING_DB_PASSWORD'), array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $this->connection->exec('SET SESSION max_execution_time=10000');
    }
    public function query($sql, $bindings = array()) {
        $this->statements[] = array($sql, $bindings);
        $statement = $this->connection->prepare($sql);
        $statement->execute($bindings);
        return new BookingResult($statement);
    }
    public function escape_like_str($value) {
        return str_replace(array('!', '%', '_'), array('!!', '!%', '!_'), (string)$value);
    }
}
require dirname(dirname(__DIR__)).'/application/modules/stock_material/models/Serverside_model.php';
