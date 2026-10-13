<?php defined('BASEPATH') OR exit('No direct script access allowed');

// CLI registration only; production report endpoints never instantiate this class.
class Report_lhp_slitting_registration
{
    private $db;
    private $permission = 'Report_LHP_Slitting.View';
    private $marker = 'Installed by report_lhp_slitting registration';
    private $title = 'Report Weight Production';

    public function __construct(PDO $db) { $this->db = $db; }

    private function query($sql, $bindings = array())
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($bindings);
        return $statement;
    }

    private function anchor()
    {
        $rows = $this->query("SELECT * FROM menus WHERE TRIM(BOTH '/' FROM link) = 'progress_order' FOR UPDATE")->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) !== 1 || !(int)$rows[0]['parent_id'] || $rows[0]['order'] === null)
            throw new RuntimeException('Expected one Progress Order menu with parent and order. No changes applied.');
        return $rows[0];
    }

    private function menu()
    {
        $rows = $this->query("SELECT * FROM menus WHERE TRIM(BOTH '/' FROM link) = 'report_lhp_slitting' FOR UPDATE")->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) > 1) throw new RuntimeException('Duplicate Report Weight Production menus; resolve before installation.');
        return $rows ? $rows[0] : null;
    }

    public function apply()
    {
        $this->db->beginTransaction();
        try {
            $anchor = $this->anchor();
            $menu = $this->menu();
            if ($menu) {
                $permission = $this->query('SELECT * FROM permissions WHERE id_permission=?',array($menu['permission_id']))->fetch(PDO::FETCH_ASSOC);
                if (!$permission || $permission['nm_permission'] !== $this->permission || $permission['ket'] !== $this->marker
                    || !in_array($menu['title'],array($this->title,'Report LHP Slitting'),true)
                    || $menu['parent_id'] !== $anchor['parent_id'] || (int)$menu['order'] !== (int)$anchor['order']+1)
                    throw new RuntimeException('Existing report menu differs from expected registration. No changes applied.');
                $renamed = $menu['title'] !== $this->title || $permission['nm_menu'] !== $this->title;
                if ($renamed) {
                    $this->query('UPDATE menus SET title=? WHERE id=?',array($this->title,$menu['id']));
                    $this->query('UPDATE permissions SET nm_menu=? WHERE id_permission=?',array($this->title,$menu['permission_id']));
                }
                $this->db->commit();
                return $renamed ? 'renamed' : 'already installed';
            }
            if ($this->query('SELECT id_permission FROM permissions WHERE nm_permission=? FOR UPDATE',array($this->permission))->fetchColumn() !== false)
                throw new RuntimeException('Report permission already exists without its menu. Resolve before installation.');
            $this->query('INSERT INTO permissions (nm_permission,ket,nm_menu,created_on) VALUES (?,?,?,NOW())',
                array($this->permission,$this->marker,$this->title));
            $permissionId = $this->db->lastInsertId();
            $position = (int)$anchor['order']+1;
            $this->query('UPDATE menus SET `order`=`order`+1 WHERE parent_id=? AND `order`>=?',array($anchor['parent_id'],$position));
            $this->query('INSERT INTO menus (title,link,icon,target,group_menu,parent_id,permission_id,status,`order`,created_on,pusat)
                VALUES (?,?,?,?,?,?,?,?,?,NOW(),?)', array($this->title,'report_lhp_slitting','fa fa-angle-right',
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
                || !in_array($menu['title'],array($this->title,'Report LHP Slitting'),true)) throw new RuntimeException('Registration ownership differs; rollback refused.');
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
