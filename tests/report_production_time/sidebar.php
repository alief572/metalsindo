<?php
// Read-only CLI rendering of the actual sidebar generator using the application's development database.
if (PHP_SAPI !== 'cli') exit('CLI only');
$root=dirname(dirname(__DIR__));
define('BASEPATH',$root.'/system/');
define('APPPATH',$root.'/application/');
define('ENVIRONMENT','development');
function log_message($level,$message) {}
function is_php($version) { return version_compare(PHP_VERSION,$version,'>='); }
function show_error($message) { throw new RuntimeException($message); }
function check_class($link,$exact=false) { return ''; }
function site_url($link='') { return '/metalsindo_2/'.$link; }
require APPPATH.'config/database.php';
require APPPATH.'config/development/database.php';
$settings=$db[$active_group]; $settings['db_debug']=false;
require BASEPATH.'database/DB.php';
$connection=DB($settings);
require APPPATH.'libraries/Menu_generator.php';
class TimeSidebarProbe extends Menu_generator {
    public function __construct($database) {
        $this->ci=(object)array('db'=>$database); $this->x=$database->dbprefix;
        $this->uri='/spk_aktual/'; $this->is_admin=true; $this->user_id=0;
    }
    protected function get_auth_permission($user_id=0) { return array(); }
}
$generator=new TimeSidebarProbe($connection);
$html=$generator->build_menus(1);
$progress=strpos($html,"href='/metalsindo_2/report_lhp_slitting'");
$report=strpos($html,"href='/metalsindo_2/report_production_time'");
if ($progress===false || $report===false || $report<=$progress || substr_count($html,"href='/metalsindo_2/report_production_time'")!==1)
    throw new RuntimeException('Report link must render once after Report Weight Production on the active application database.');
if (strpos(substr($html,$progress+strlen("href='/metalsindo_2/report_lhp_slitting'"),$report-$progress-strlen("href='/metalsindo_2/report_lhp_slitting'")),'href=')!==false)
    throw new RuntimeException('Another menu appears between the report links.');
echo "PASS actual Menu_generator: admin sidebar renders Report Production Time once immediately after Report Weight Production\n";
