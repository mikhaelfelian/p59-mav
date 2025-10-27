<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

use App\Models\Builtin\RoleModel;

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
 *
 * Modified by Mikhael Felian Waskito
 * @link       https://github.com/mikhaelfelian/p59-mav
 * @notes      Refactored to extend BaseModel for request service and PHP8 compatibility
 */
class MenuRoleModel extends \App\Models\BaseModel
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
                    ->where('id_role', $this->request->getPost('id_role'))
                    ->delete();
    }
    
    public function saveData() 
    {
        $id_menu = $this->request->getPost('id_menu');
        $id_roles = $this->request->getPost('id_role');
        
        // Find all parent
        $menu_parent = $this->allParents($id_menu);
        
        $insert_parent = [];
        if ($menu_parent && !empty($id_roles)) 
        {
            // Cek apakah parent telah diassign di role yang tercentang, jika belum buat insert nya
            foreach($menu_parent as $id_menu_parent) {
                foreach ($id_roles as $id_role) {
                    $exists = $this->where('id_menu', $id_menu_parent)
                                  ->where('id_role', $id_role)
                                  ->countAllResults() > 0;
                    
                    if (!$exists) {
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
        $this->db->table('menu_role')->delete(['id_menu' => $id_menu]);
        
        // Insert role yang tercentang
        if (!empty($id_roles)) {
            $data_db = [];
            foreach ($id_roles as $id_role) {
                $data_db[] = ['id_menu' => $id_menu, 'id_role' => $id_role];
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
        $query = $this->builder('menu')->get()->getResultArray();
        $menu = [];
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
        return $this->builder('menu')->countAllResults();
    }
    
    /**
     * Get list data for DataTables with optional WHERE filter
     * 
     * @param array|null $where Optional WHERE conditions (array format for Query Builder)
     * @return array Data and total filtered count
     */
    public function getListData($where = null) {
        $columns = $this->request->getPost('columns');
        $search_all = @$this->request->getPost('search')['value'];
        
        // Build base query - MUST initialize from table first
        $builder = $this->builder('menu');
        
        // Apply where clause from parameter (array format)
        if (!empty($where) && is_array($where)) {
            $builder->where($where);
        }
        
        // Apply search
        if ($search_all && !empty($columns)) {
            $builder->groupStart();
            $first = true;
            foreach ($columns as $val) {
                if (strpos($val['data'] ?? '', 'ignore') !== false) continue;
                
                $column = $val['data'] ?? '';
                if ($first) {
                    $builder->like($column, $search_all);
                    $first = false;
                } else {
                    $builder->orLike($column, $search_all);
                }
            }
            $builder->groupEnd();
        }
        
        // Get total filtered - countAllResults(false) preserves query
        $total_filtered = $builder->countAllResults(false);
        
        // Apply ordering
        $order_data = $this->request->getPost('order');
        if ($order_data && isset($order_data[0]) && isset($columns[$order_data[0]['column']])) {
            $order_column = $columns[$order_data[0]['column']]['data'] ?? '';
            if (strpos($order_column, 'ignore') === false) {
                $order_dir = strtoupper($order_data[0]['dir'] ?? 'ASC');
                $builder->orderBy($order_column, $order_dir);
            }
        }
        
        // Apply pagination
        $start = $this->request->getPost('start') ?: 0;
        $length = $this->request->getPost('length') ?: 10;
        $data = $builder->limit($length, $start)->get()->getResultArray();

        return ['data' => $data, 'total_filtered' => $total_filtered];
    }
}
