<?php
if (PHP_SAPI !== 'cli') exit('CLI only');
define('BASEPATH', __DIR__);
require dirname(__DIR__).'/application/modules/report_lhp_slitting/libraries/Report_lhp_slitting_registration.php';
try {
    $action = isset($argv[1]) ? $argv[1] : '';
    if (!in_array($action,array('apply','rollback'),true)) throw new RuntimeException('Usage: php scripts/register_report_lhp_slitting.php apply|rollback [--app-config=development]');
    if (isset($argv[2])) {
        if (!preg_match('/^--app-config=(development|testing|production)$/D',$argv[2],$matches) || count($argv)>3)
            throw new RuntimeException('Expected --app-config=development|testing|production');
        define('ENVIRONMENT',$matches[1]);
        require dirname(__DIR__).'/application/config/database.php';
        $override=dirname(__DIR__).'/application/config/'.ENVIRONMENT.'/database.php';
        if (is_file($override)) require $override;
        $settings=$db[$active_group];
        if (!empty($settings['dbprefix'])) throw new RuntimeException('Registration expects unprefixed application tables.');
        $host=$settings['hostname']; $database=$settings['database'];
        $user=$settings['username']; $password=$settings['password'];
        $port=!empty($settings['port']) ? $settings['port'] : 3306;
        echo 'Using application '.ENVIRONMENT.' database: '.$host.':'.$port.'/'.$database.PHP_EOL;
    } else {
        foreach (array('HOST','USER','PASSWORD','DATABASE') as $key) {
            if (getenv('REPORT_INSTALL_'.$key) === false) throw new RuntimeException('Missing REPORT_INSTALL_'.$key);
        }
        $host=getenv('REPORT_INSTALL_HOST'); $database=getenv('REPORT_INSTALL_DATABASE');
        $user=getenv('REPORT_INSTALL_USER'); $password=getenv('REPORT_INSTALL_PASSWORD');
        $port=getenv('REPORT_INSTALL_PORT') !== false ? getenv('REPORT_INSTALL_PORT') : 3306;
    }
    if (!ctype_digit((string)$port) || (int)$port<1 || (int)$port>65535) throw new RuntimeException('Invalid database port.');
    $db = new PDO('mysql:host='.$host.';port='.$port.';dbname='.$database.';charset=utf8',
        $user,$password,array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
    foreach (array('menus','permissions','group_permissions','user_permissions') as $table) {
        $statement=$db->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
        $statement->execute(array($table));
        if (strtoupper((string)$statement->fetchColumn()) !== 'INNODB') throw new RuntimeException('Registration requires transactional InnoDB table: '.$table);
    }
    $registration = new Report_lhp_slitting_registration($db);
    echo 'Report Weight Production: '.$registration->$action().PHP_EOL;
} catch (Exception $e) {
    fwrite(STDERR,$e->getMessage().PHP_EOL);
    exit(1);
}
