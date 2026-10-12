<?php
require __DIR__.'/bootstrap.php';
function log_message($level,$message) {}
function is_php($version) { return version_compare(PHP_VERSION,$version,'>='); }
function show_error($message) { throw new RuntimeException($message); }
function check_class($link,$exact=false) { return ''; }
function site_url($link='') { return '/metalsindo_2/'.$link; }
require BASEPATH.'database/DB.php';
$connection=DB(array('hostname'=>getenv('REPORT_TEST_HOST'),'username'=>getenv('REPORT_TEST_USER'),
    'password'=>getenv('REPORT_TEST_PASSWORD'),'database'=>'metalsindo_dev','dbdriver'=>'mysqli','dbprefix'=>'',
    'pconnect'=>false,'db_debug'=>false,'cache_on'=>false,'cachedir'=>'','char_set'=>'utf8','dbcollat'=>'utf8_general_ci','save_queries'=>true));
require APPPATH.'libraries/Menu_generator.php';
class IncomingSidebarProbe extends Menu_generator {
    private $permissions;
    public function __construct($database,$admin,$permissions=array()) {
        $this->ci=(object)array('db'=>$database); $this->x=''; $this->uri='/report_incoming/';
        $this->is_admin=$admin; $this->user_id=0; $this->permissions=$permissions;
    }
    protected function get_auth_permission($user_id=0) { return $this->permissions; }
}
$html=(new IncomingSidebarProbe($connection,true))->build_menus(1);
$anchor=strpos($html,"href='/metalsindo_2/kartu_stock'"); $report=strpos($html,"href='/metalsindo_2/report_incoming'");
incoming_check($anchor!==false && $report>$anchor && substr_count($html,"href='/metalsindo_2/report_incoming'")===1,'Actual local sidebar order');
$menu=$db->query("SELECT m.*,p.nm_permission FROM menus m JOIN permissions p ON p.id_permission=m.permission_id WHERE m.link='report_incoming'")->row_array();
$anchorRow=$db->query("SELECT * FROM menus WHERE link='kartu_stock'")->row_array();
incoming_check($menu['parent_id']===$anchorRow['parent_id'] && (int)$menu['order']===(int)$anchorRow['order']+1 && $menu['nm_permission']==='Report_Incoming.View','Immediate sibling placement');
foreach(array('group_permissions','user_permissions') as $table)
    incoming_check((int)$db->query('SELECT COUNT(*) n FROM '.$table.' WHERE id_permission=?',array($menu['permission_id']))->row_array()['n']===0,'No initial non-admin grants');
$parent=$db->query('SELECT permission_id FROM menus WHERE id=?',array($anchorRow['parent_id']))->row_array();
$denied=(new IncomingSidebarProbe($connection,false,array($parent['permission_id'])))->build_menus(1);
incoming_check(strpos($denied,"href='/metalsindo_2/report_incoming'")===false,'Unauthorized user does not see report');
$allowed=(new IncomingSidebarProbe($connection,false,array($parent['permission_id'],$menu['permission_id'])))->build_menus(1);
incoming_check(substr_count($allowed,"href='/metalsindo_2/report_incoming'")===1,'Explicit permission shows report');
echo "PASS actual local Menu_generator: report once immediately after Kartu Stock, Administrator default, non-admin permission visibility\n";
