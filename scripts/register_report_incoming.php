<?php
// Deliberately has no --app-config option: localhost currently uses a nonlocal active database.
if(PHP_SAPI!=='cli') exit('CLI only');
define('BASEPATH',dirname(__DIR__).'/system/');
require dirname(__DIR__).'/application/modules/report_incoming/libraries/Report_incoming_registration.php';
try {
    $action=isset($argv[1]) ? $argv[1] : '';
    if(count($argv)!==2 || !in_array($action,array('apply','rollback'),true))
        throw new RuntimeException('Usage: php scripts/register_report_incoming.php apply|rollback (local metalsindo_dev only)');
    foreach(array('HOST','USER','PASSWORD','DATABASE') as $key)
        if(getenv('REPORT_INSTALL_'.$key)===false) throw new RuntimeException('Missing REPORT_INSTALL_'.$key);
    $host=getenv('REPORT_INSTALL_HOST'); $database=getenv('REPORT_INSTALL_DATABASE');
    if($database!=='metalsindo_dev' || !in_array($host,array('localhost','127.0.0.1','metalsindo_db','db','db_metalsindo'),true))
        throw new RuntimeException('Installer refuses nonlocal hosts or databases other than metalsindo_dev.');
    $port=getenv('REPORT_INSTALL_PORT')!==false ? getenv('REPORT_INSTALL_PORT') : '3306';
    if(!ctype_digit((string)$port) || (int)$port<1 || (int)$port>65535) throw new RuntimeException('Invalid port');
    $db=new PDO('mysql:host='.$host.';port='.$port.';dbname=metalsindo_dev;charset=utf8',
        getenv('REPORT_INSTALL_USER'),getenv('REPORT_INSTALL_PASSWORD'),array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    foreach(array('menus','permissions','group_permissions','user_permissions') as $table) {
        $statement=$db->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        $statement->execute(array($table));
        if(strtoupper((string)$statement->fetchColumn())!=='INNODB') throw new RuntimeException('Requires transactional InnoDB table: '.$table);
    }
    $registration=new Report_incoming_registration($db);
    echo 'Local metalsindo_dev / Report Incoming: '.$registration->$action().PHP_EOL;
} catch(Exception $e) { fwrite(STDERR,$e->getMessage().PHP_EOL); exit(1); }
