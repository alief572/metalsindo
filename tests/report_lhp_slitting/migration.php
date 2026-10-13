<?php
require __DIR__.'/regression.php';
require dirname(dirname(__DIR__)).'/application/modules/report_lhp_slitting/libraries/Report_lhp_slitting_registration.php';
foreach (array('menus','permissions','group_permissions','user_permissions') as $table) {
    $schema=$db->query('SHOW CREATE TABLE '.$table)->row_array();
    $db->query(preg_replace('/^CREATE TABLE/','CREATE TEMPORARY TABLE',$schema['Create Table']));
}
$db->query("INSERT INTO menus (id,title,link,parent_id,permission_id,`order`,group_menu,status) VALUES (1,'Production','#',0,1,7,1,1),(2,'Progress Order','progress_order',1,1,9,1,1),(3,'Following','following',1,1,10,1,1)");
$originalMenus=$db->query('SELECT * FROM menus ORDER BY id')->result_array();
$registration=new Report_lhp_slitting_registration($db->connection);
check($registration->apply()==='installed','Install');
$menu=$db->query("SELECT * FROM menus WHERE link='report_lhp_slitting'")->row_array();
check((int)$menu['parent_id']===1 && (int)$menu['order']===10,'Position under Progress Order');
check((int)$db->query("SELECT `order` FROM menus WHERE id=3")->row_array()['order']===11,'Shift following sibling');
check($db->query('SELECT * FROM group_permissions')->result_array()===array() && $db->query('SELECT * FROM user_permissions')->result_array()===array(),'No non-admin grants');
$installedMenus=$db->query('SELECT * FROM menus ORDER BY id')->result_array();
check($registration->apply()==='already installed' && $db->query('SELECT * FROM menus ORDER BY id')->result_array()===$installedMenus,'Idempotent install');
$permission=$menu['permission_id'];
$db->query('INSERT INTO group_permissions (id_group,id_permission) VALUES (2,?)',array($permission));
$db->query('INSERT INTO user_permissions (id_user,id_permission) VALUES (9,?)',array($permission));
check($menu['title']==='Report Weight Production','New installations use the new report name');
$db->query('UPDATE menus SET title=? WHERE id=?',array('Report LHP Slitting',$menu['id']));
$db->query('UPDATE permissions SET nm_menu=? WHERE id_permission=?',array('Report LHP Slitting',$permission));
check($registration->apply()==='renamed','Existing menu migrates to the new report name');
check($db->query('SELECT * FROM menus ORDER BY id')->result_array()===$installedMenus,'Rename preserves menu identity/order/link');
check($db->query('SELECT nm_menu FROM permissions WHERE id_permission=?',array($permission))->row_array()['nm_menu']==='Report Weight Production','Permission display name updated');
check(count($db->query('SELECT * FROM group_permissions')->result_array())===1 && count($db->query('SELECT * FROM user_permissions')->result_array())===1,'Existing permission grants preserved');
check($registration->apply()==='already installed','Rename is idempotent');
check($registration->rollback()==='removed','Rollback');
check($db->query('SELECT * FROM menus ORDER BY id')->result_array()===$originalMenus,'Rollback restores menu order');
check($db->query('SELECT * FROM permissions')->result_array()===array() && $db->query('SELECT * FROM group_permissions')->result_array()===array() && $db->query('SELECT * FROM user_permissions')->result_array()===array(),'Rollback removes owned permission and grants');
check($registration->rollback()==='already removed','Idempotent rollback');
$db->query("DELETE FROM menus WHERE link='progress_order'");
$failed=false; try { $registration->apply(); } catch (RuntimeException $e) { $failed=true; }
check($failed && $db->query('SELECT * FROM permissions')->result_array()===array(),'Missing anchor leaves no writes');
echo "PASS migration: install/reinstall, legacy rename, preserved menu/order/grants, rollback/repeat, missing anchor\n";
