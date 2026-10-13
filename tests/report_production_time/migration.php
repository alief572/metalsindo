<?php
require __DIR__.'/bootstrap.php';
$db=new TimeDatabase();
require dirname(dirname(__DIR__)).'/application/modules/report_production_time/libraries/Report_production_time_registration.php';
foreach (array('menus','permissions','group_permissions','user_permissions') as $table) {
    $schema=$db->query('SHOW CREATE TABLE '.$table)->row_array();
    $db->query(preg_replace('/^CREATE TABLE/','CREATE TEMPORARY TABLE',$schema['Create Table']));
}
$db->query("INSERT INTO menus (id,title,link,parent_id,permission_id,`order`,group_menu,status) VALUES (1,'Production','#',0,1,7,1,1),(2,'Report LHP Slitting','report_lhp_slitting',1,1,9,1,1),(3,'Following','following',1,1,10,1,1)");
$originalMenus=$db->query('SELECT * FROM menus ORDER BY id')->result_array();
$registration=new Report_production_time_registration($db->connection);
check($registration->apply()==='installed','Install');
$menu=$db->query("SELECT * FROM menus WHERE link='report_production_time'")->row_array();
check((int)$menu['parent_id']===1 && (int)$menu['order']===10,'Position under Report LHP Slitting');
check((int)$db->query("SELECT `order` FROM menus WHERE id=3")->row_array()['order']===11,'Shift following sibling');
check($db->query('SELECT * FROM group_permissions')->result_array()===array() && $db->query('SELECT * FROM user_permissions')->result_array()===array(),'No non-admin grants');
$installedMenus=$db->query('SELECT * FROM menus ORDER BY id')->result_array();
check($registration->apply()==='already installed' && $db->query('SELECT * FROM menus ORDER BY id')->result_array()===$installedMenus,'Idempotent install');
$permission=$menu['permission_id'];
$db->query('INSERT INTO group_permissions (id_group,id_permission) VALUES (2,?)',array($permission));
$db->query('INSERT INTO user_permissions (id_user,id_permission) VALUES (9,?)',array($permission));
check($registration->rollback()==='removed','Rollback');
check($db->query('SELECT * FROM menus ORDER BY id')->result_array()===$originalMenus,'Rollback restores menu order');
check($db->query('SELECT * FROM permissions')->result_array()===array() && $db->query('SELECT * FROM group_permissions')->result_array()===array() && $db->query('SELECT * FROM user_permissions')->result_array()===array(),'Rollback removes owned permission and grants');
check($registration->rollback()==='already removed','Idempotent rollback');
$db->query("INSERT INTO permissions (nm_permission,ket,nm_menu) VALUES ('Report_Production_Time.View','Existing','Report Production Time')");
$failed=false; try { $registration->apply(); } catch (RuntimeException $e) { $failed=true; }
check($failed && $db->query('SELECT * FROM menus ORDER BY id')->result_array()===$originalMenus,'Existing orphan permission leaves menu order unchanged');
$db->query("DELETE FROM permissions WHERE nm_permission='Report_Production_Time.View'");
$db->query("DELETE FROM menus WHERE link='report_lhp_slitting'");
$failed=false; try { $registration->apply(); } catch (RuntimeException $e) { $failed=true; }
check($failed && $db->query('SELECT * FROM permissions')->result_array()===array(),'Missing anchor leaves no writes');
echo "PASS migration: install/reinstall, dynamic position, admin-only defaults, rollback/repeat, missing anchor\n";
