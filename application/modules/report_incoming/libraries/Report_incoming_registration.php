<?php defined('BASEPATH') OR exit('No direct script access allowed');

// CLI registration only; production report endpoints never instantiate this class.
class Report_incoming_registration
{
    private $db;
    private $permission = 'Report_Incoming.View';
    private $marker = 'Installed by report_incoming registration';

    public function __construct(PDO $db) {
        $this->db=$db;
        if($this->query('SELECT DATABASE()')->fetchColumn()!=='metalsindo_dev')
            throw new RuntimeException('Registration is limited to local metalsindo_dev.');
    }

    private function query($sql, $bindings = array())
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($bindings);
        return $statement;
    }

    private function anchor()
    {
        $rows = $this->query("SELECT * FROM menus WHERE TRIM(BOTH '/' FROM link) = 'kartu_stock' FOR UPDATE")->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) !== 1 || !(int)$rows[0]['parent_id'] || $rows[0]['order'] === null || (int)$rows[0]['status']!==1)
            throw new RuntimeException('Expected one Kartu Stock menu with parent and order. No changes applied.');
        $parent=$this->query('SELECT title,parent_id FROM menus WHERE id=? FOR UPDATE',array($rows[0]['parent_id']))->fetch(PDO::FETCH_ASSOC);
        if(!$parent || $parent['title']!=='Warehouse Material' || (int)$parent['parent_id']!==0)
            throw new RuntimeException('Kartu Stock must be inside Warehouse Material. No changes applied.');
        return $rows[0];
    }

    private function menu()
    {
        $rows = $this->query("SELECT * FROM menus WHERE TRIM(BOTH '/' FROM link) = 'report_incoming' FOR UPDATE")->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) > 1) throw new RuntimeException('Duplicate Report Incoming menus; resolve before installation.');
        return $rows ? $rows[0] : null;
    }

    public function apply()
    {
        $this->db->beginTransaction();
        try {
            $anchor = $this->anchor();
            $menu = $this->menu();
            if ($menu) {
                $permission = $this->query('SELECT nm_permission,ket FROM permissions WHERE id_permission=?',array($menu['permission_id']))->fetch(PDO::FETCH_ASSOC);
                if (!$permission || $permission['nm_permission'] !== $this->permission || $permission['ket']!==$this->marker || $menu['title'] !== 'Report Incoming'
                    || $menu['parent_id'] !== $anchor['parent_id'] || (int)$menu['order'] !== (int)$anchor['order']+1)
                    throw new RuntimeException('Existing report menu differs from expected registration. No changes applied.');
                $this->db->commit();
                return 'already installed';
            }
            if ($this->query('SELECT id_permission FROM permissions WHERE nm_permission=? FOR UPDATE',array($this->permission))->fetchColumn() !== false)
                throw new RuntimeException('Report permission already exists without its menu. Resolve before installation.');
            $this->query('INSERT INTO permissions (nm_permission,ket,nm_menu,created_on) VALUES (?,?,?,NOW())',
                array($this->permission,$this->marker,'Report Incoming'));
            $permissionId = $this->db->lastInsertId();
            $position = (int)$anchor['order']+1;
            $this->query('UPDATE menus SET `order`=`order`+1 WHERE parent_id=? AND `order`>=?',array($anchor['parent_id'],$position));
            $this->query('INSERT INTO menus (title,link,icon,target,group_menu,parent_id,permission_id,status,`order`,created_on,pusat)
                VALUES (?,?,?,?,?,?,?,?,?,NOW(),?)', array('Report Incoming','report_incoming','fa fa-angle-right',
                $anchor['target'],$anchor['group_menu'],$anchor['parent_id'],$permissionId,1,$position,$anchor['pusat']));
            // Auth::is_admin() already grants administrators access. Do not grant other users/groups.
            $this->db->commit();
            return 'installed';
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function rollback()
    {
        $this->db->beginTransaction();
        try {
            $menu = $this->menu();
            if (!$menu) { $this->db->commit(); return 'already removed'; }
            $permission = $this->query('SELECT * FROM permissions WHERE id_permission=? FOR UPDATE',array($menu['permission_id']))->fetch(PDO::FETCH_ASSOC);
            if (!$permission || $permission['nm_permission'] !== $this->permission || $permission['ket'] !== $this->marker
                || $menu['title'] !== 'Report Incoming') throw new RuntimeException('Registration ownership differs; rollback refused.');
            $references = $this->query('SELECT COUNT(*) FROM menus WHERE permission_id=? AND id<>?',array($menu['permission_id'],$menu['id']))->fetchColumn();
            if ($references) throw new RuntimeException('Report permission is referenced by another menu; rollback refused.');
            $this->query('DELETE FROM menus WHERE id=?',array($menu['id']));
            $this->query('UPDATE menus SET `order`=`order`-1 WHERE parent_id=? AND `order`>?',array($menu['parent_id'],$menu['order']));
            foreach (array('group_permissions','user_permissions') as $table)
                $this->query('DELETE FROM '.$table.' WHERE id_permission=?',array($menu['permission_id']));
            $this->query('DELETE FROM permissions WHERE id_permission=?',array($menu['permission_id']));
            $this->db->commit();
            return 'removed';
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}

