<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

use App\Models\Builtin\RoleModel;
use CodeIgniter\Model;

/**
 * Menu Role Model
 * Handles relationships between menus and roles
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @table      menu_role
 * @version    4.3.1
 */
class MenuRoleModel extends Model
{
    protected $table = 'menu_role';
    protected $primaryKey = 'id_menu_role';
    protected $allowedFields = ['id_menu', 'id_role'];
    protected $useTimestamps = false;
    
    /**
     * @var RoleModel
     */
    protected $roleModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->roleModel = new RoleModel();
    }
    
    /**
     * Get all roles - delegate to RoleModel
     * 
     * @return array
     */
    public function getAllRole()
    {
        return $this->roleModel->getAllRole();
    }
    
    /**
     * Get all menu roles with role details
     * 
     * @return array
     */
    public function getAllMenuRole()
    {
        return $this->select('menu_role.*, role.*')
                    ->join('role', 'role.id_role = menu_role.id_role', 'left')
                    ->findAll();
    }
    
    /**
     * Get menu roles by menu ID
     * 
     * @param int $id Menu ID
     * @return array
     */
    public function getMenuRoleById($id)
    {
        return $this->where('id_menu', $id)->findAll();
    }
    
    /**
     * Create menu roles
     * 
     * @param int $id_menu Menu ID
     * @param array $id_roles Array of role IDs
     * @return bool
     */
    public function createMenuRoles($id_menu, array $id_roles)
    {
        if (empty($id_roles)) {
            return false;
        }
        
        $data = [];
        foreach ($id_roles as $id_role) {
            $data[] = ['id_menu' => $id_menu, 'id_role' => $id_role];
        }
        
        return $this->insertBatch($data);
    }
    
    /**
     * Update menu roles (delete old, insert new)
     * 
     * @param int $id_menu Menu ID
     * @param array $id_roles Array of role IDs
     * @return bool
     */
    public function updateMenuRoles($id_menu, array $id_roles)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        // Delete existing roles
        $this->deleteByMenuId($id_menu);
        
        // Insert new roles
        if (!empty($id_roles)) {
            $this->createMenuRoles($id_menu, $id_roles);
        }
        
        $db->transComplete();
        return $db->transStatus();
    }
    
    /**
     * Delete menu roles by menu ID
     * 
     * @param int $id_menu Menu ID
     * @return bool
     */
    public function deleteByMenuId($id_menu)
    {
        return $this->where('id_menu', $id_menu)->delete();
    }
    
    /**
     * Delete specific menu role
     * 
     * @return bool
     */
    public function deleteData()
    {
        return $this->where('id_menu', $this->request->getPost('id_menu'))
                    ->where('id_role', $_POST['id_role'])
                    ->delete();
    }
    
    public function saveData() 
    {
        // Find all parent
        $menu_parent = $this->allParents($_POST['id_menu']);
        
        $insert_parent = [];
        if ($menu_parent && !empty($_POST['id_role'])) 
        {
            // Cek apakah parent telah diassign di role yang tercentang, jika belum buat insert nya
            foreach($menu_parent as $id_menu_parent) {
                foreach ($_POST['id_role'] as $id_role) {
                    $sql = 'SELECT * FROM menu_role WHERE id_menu = ? AND id_role = ?';
                    $data = [$id_menu_parent, $id_role];
                    $query = $this->db->query($sql, $data)->getResultArray();
                    if (!$query) {
                        $insert_parent[] = ['id_menu' => $id_menu_parent, 'id_role' => $id_role];
                    }
                }
            }
        }

        // INSERT - DELETE
        $this->db->transStart();
        
        // Insert Parent
        if ($insert_parent) {
            $this->db->table('menu_role')->insertBatch($insert_parent);
        }
        
        // Hapus role pada menu
        $this->db->table('menu_role')->delete(['id_menu' => $_POST['id_menu']]);
        
        // Insert role yang tercentang
        if (!empty($_POST['id_role'])) {
            $data_db = [];
            foreach ($_POST['id_role'] as $id_role) {
                $data_db[] = ['id_menu' => $_POST['id_menu'], 'id_role' => $id_role];
            }
            $this->db->table('menu_role')->insertBatch($data_db);
        }

        $this->db->transComplete();
        $trans = $this->db->transStatus();
        
        if ($trans) {
            $result['status'] = 'ok';
            $result['insert_parent'] = $insert_parent;
        } else {
            $result['status'] = 'error';
        }
        return $result;
    }
    
    private function allParents($id_menu, &$list_parent = []) {
        
        $query = $this->db->query('SELECT * FROM menu')->getResultArray();
        foreach($query as $val) {
            $menu[$val['id_menu']] = $val;
        }
        
        if (key_exists($id_menu, $menu)) {
            $parent = $menu[$id_menu]['id_parent'];
            if ($parent) {
                $list_parent[$parent] = &$parent;
                $this->allParents($parent, $list_parent);
            }
        }
        
        return $list_parent;
    }
    
    public function countAllData() {
        $sql = 'SELECT COUNT(*) AS jml FROM menu';
        $result = $this->db->query($sql)->getRow();
        return $result->jml;
    }
    
    public function getListData($where) {

        $columns = $this->request->getPost('columns');

        // Search
        $search_all = @$this->request->getPost('search')['value'];
        if ($search_all) {
            foreach ($columns as $val) 
            {
                if (strpos($val['data'], 'ignore') !== false)
                    continue;
                
                $where_col[] = $val['data'] . ' LIKE "%' . $search_all . '%"';
            }
             $where .= ' AND (' . join(' OR ', $where_col) . ') ';
        }
        
        // Order        
        $order_data = $this->request->getPost('order');
        $order = '';
        if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore') === false) {
            $order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
            $order = ' ORDER BY ' . $order_by;
        }

        // Query Total Filtered
        $sql = 'SELECT COUNT(*) AS jml_data FROM menu ' . $where;
        $total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
        
        // Query Data
        $start = $this->request->getPost('start') ?: 0;
        $length = $this->request->getPost('length') ?: 10;
        $sql = 'SELECT * FROM menu 
                ' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
        $data = $this->db->query($sql)->getResultArray();

        return ['data' => $data, 'total_filtered' => $total_filtered];
    }
}
