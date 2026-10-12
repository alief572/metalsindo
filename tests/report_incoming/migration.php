<?php
require __DIR__.'/fixtures.php';
require dirname(dirname(__DIR__)).'/application/modules/report_incoming/libraries/Report_incoming_registration.php';
foreach (array('menus','permissions','group_permissions','user_permissions') as $table) {
    $schema=$db->query('SHOW CREATE TABLE '.$table)->row_array();
    $db->query(preg_replace('/^CREATE TABLE/','CREATE TEMPORARY TABLE',$schema['Create Table']));
}
$db->query("INSERT INTO menus (id,title,link,parent_id,permission_id,`order`,group_menu,status) VALUES (1,'Warehouse Material','#',0,1,7,1,1),(2,'Kartu Stock','kartu_stock',1,1,9,1,1),(3,'Following','following',1,1,10,1,1)");
$originalMenus=$db->query('SELECT * FROM menus ORDER BY id')->result_array();
$registration=new Report_incoming_registration($db->connection);
incoming_check($registration->apply()==='installed','Install');
$menu=$db->query("SELECT * FROM menus WHERE link='report_incoming'")->row_array();
incoming_check((int)$menu['parent_id']===1 && (int)$menu['order']===10,'Position under Kartu Stock');
incoming_check((int)$db->query("SELECT `order` FROM menus WHERE id=3")->row_array()['order']===11,'Shift following sibling');
incoming_check($db->query('SELECT * FROM group_permissions')->result_array()===array() && $db->query('SELECT * FROM user_permissions')->result_array()===array(),'No non-admin grants');
$installedMenus=$db->query('SELECT * FROM menus ORDER BY id')->result_array();
incoming_check($registration->apply()==='already installed' && $db->query('SELECT * FROM menus ORDER BY id')->result_array()===$installedMenus,'Idempotent install');
$permission=$menu['permission_id'];
$db->query('INSERT INTO group_permissions (id_group,id_permission) VALUES (2,?)',array($permission));
$db->query('INSERT INTO user_permissions (id_user,id_permission) VALUES (9,?)',array($permission));
incoming_check($registration->rollback()==='removed','Rollback');
incoming_check($db->query('SELECT * FROM menus ORDER BY id')->result_array()===$originalMenus,'Rollback restores menu order');
incoming_check($db->query('SELECT * FROM permissions')->result_array()===array() && $db->query('SELECT * FROM group_permissions')->result_array()===array() && $db->query('SELECT * FROM user_permissions')->result_array()===array(),'Rollback removes owned permission and grants');
incoming_check($registration->rollback()==='already removed','Idempotent rollback');
$db->query("DELETE FROM menus WHERE link='kartu_stock'");
$failed=false; try { $registration->apply(); } catch (RuntimeException $e) { $failed=true; }
incoming_check($failed && $db->query('SELECT * FROM permissions')->result_array()===array(),'Missing anchor leaves no writes');
incoming_insert('menus',$originalMenus[1]);
incoming_insert('menus',array('id'=>9,'title'=>'Duplicate','link'=>'kartu_stock','parent_id'=>1,'permission_id'=>1,'order'=>9,'group_menu'=>1,'status'=>1));
$failed=false; try { $registration->apply(); } catch(RuntimeException $e) { $failed=true; }
incoming_check($failed && $db->query('SELECT * FROM permissions')->result_array()===array(),'Duplicate anchor leaves no writes');
$db->query('DELETE FROM menus WHERE id=9');
$db->query("UPDATE menus SET title='Other' WHERE id=1");
$failed=false; try { $registration->apply(); } catch(RuntimeException $e) { $failed=true; }
incoming_check($failed,'Wrong parent rejected');
$db->query("UPDATE menus SET title='Warehouse Material' WHERE id=1");
incoming_insert('permissions',array('nm_permission'=>'Report_Incoming.View','ket'=>'Existing foreign permission','nm_menu'=>'Other'));
$failed=false; try { $registration->apply(); } catch(RuntimeException $e) { $failed=true; }
incoming_check($failed && count($db->query('SELECT * FROM menus')->result_array())===3,'Orphan permission not taken over');
$db->query('DELETE FROM permissions');
$registration->apply();
$menu=$db->query("SELECT * FROM menus WHERE link='report_incoming'")->row_array();
$db->query("UPDATE permissions SET ket='Foreign owner' WHERE id_permission=?",array($menu['permission_id']));
foreach(array('apply','rollback') as $action) {
    $failed=false; try { $registration->$action(); } catch(RuntimeException $e) { $failed=true; }
    incoming_check($failed,'Ownership conflict rejected: '.$action);
}
$db->query("UPDATE permissions SET ket='Installed by report_incoming registration' WHERE id_permission=?",array($menu['permission_id']));
incoming_insert('menus',array('id'=>99,'title'=>'Foreign reference','link'=>'foreign','parent_id'=>1,'permission_id'=>$menu['permission_id'],'order'=>20,'group_menu'=>1,'status'=>1));
$failed=false; try { $registration->rollback(); } catch(RuntimeException $e) { $failed=true; }
incoming_check($failed && $db->query("SELECT * FROM menus WHERE link='report_incoming'")->row_array()!==false,'Shared permission prevents rollback');
$db->query('DELETE FROM menus WHERE id=99'); $registration->rollback();
foreach(array(array('203.0.113.1','metalsindo_dev'),array(getenv('REPORT_TEST_HOST'),'metalsindo_live')) as $target) {
    putenv('REPORT_INSTALL_HOST='.$target[0]); putenv('REPORT_INSTALL_DATABASE='.$target[1]);
    putenv('REPORT_INSTALL_USER='.getenv('REPORT_TEST_USER')); putenv('REPORT_INSTALL_PASSWORD='.getenv('REPORT_TEST_PASSWORD'));
    $output=array(); $code=0;
    exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg(dirname(dirname(__DIR__)).'/scripts/register_report_incoming.php').' apply 2>&1',$output,$code);
    incoming_check($code===1 && strpos(implode('\n',$output),'refuses nonlocal')!==false,'Installer refuses unauthorized target before connecting');
}
echo "PASS migration: apply/reapply/rollback, admin defaults, missing/duplicate/wrong anchor, orphan permission, ownership/shared-reference conflicts, nonlocal/live target refusal\n";

