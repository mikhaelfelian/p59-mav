<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

/**
 * Role Permission Model
 * Handles role permission assignments
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @table      role_module_permission
 * @version    4.3.1
 *
 * Modified by Mikhael Felian Waskito
 * @link       https://github.com/mikhaelfelian/p59-mav
 * @notes      Refactored to CI4 Query Builder syntax; fixed alias, DataTables, and PHP8 compatibility.
 */
class RolePermissionModel extends \App\Models\BaseModel
{
        
    public function deletePermission($id_role, $id_permission) {
        $delete = $this->db->table('role_module_permission')->delete(['id_role' => $id_role, 'id_module_permission' => $id_permission]);
        return $delete;
    }
    
    public function deleteRolePermissionByModule($id_role, $id_module) {
        // Get permission IDs for the module first
        $permissionIds = $this->builder('module_permission')
                             ->select('id_module_permission')
                             ->where('id_module', $id_module)
                             ->get()
                             ->getResultArray();
        
        if (empty($permissionIds)) {
            return true;
        }
        
        $ids = array_column($permissionIds, 'id_module_permission');
        
        return $this->builder('role_module_permission')
                    ->where('id_role', $id_role)
                    ->whereIn('id_module_permission', $ids)
                    ->delete();
    }
    
    public function getRolePermissionByIdRole($id) 
    {
        $query = $this->builder('role_module_permission')
                      ->where('id_role', $id)
                      ->get()
                      ->getResultArray();
        
        $result = [];
        foreach ($query as $val) {
            $result[$val['id_module_permission']] = $val;
        }

        return $result;
    }
    
    public function getAllPermissionByModule() 
    {
        $module_permission = $this->builder('module_permission')
                                  ->select('module_permission.*, module.*')
                                  ->join('module', 'module.id_module = module_permission.id_module', 'left')
                                  ->orderBy('module.judul_module')
                                  ->get()
                                  ->getResultArray();
                
        foreach ($module_permission as $val) {
            $result[$val['id_module']][$val['id_module_permission']] = $val;
        }

        return $result;
    }
    
    public function getAllModules() {
        $query = $this->builder('module')
                      ->orderBy('judul_module')
                      ->get()
                      ->getResultArray();
        foreach ($query as $val) {
            $result[$val['id_module']] = $val;
        }
        return $result;
    }
    
    public function getAllModulesById($id_module = '') {
        $builder = $this->builder('module');
        
        if ($id_module) {
            $builder->where('id_module', $this->request->getGet('id_module'));
        }
        
        $query = $builder->orderBy('judul_module')->get()->getResultArray();
        foreach ($query as $val) {
            $result[$val['id_module']] = $val;
        }
        return $result;
    }
    
    public function getRoleById($id) {
        return $this->builder('role')
                    ->where('id_role', $id)
                    ->get()
                    ->getRowArray();
    }
    
    public function getAllRole() {
        return $this->builder('role')->get()->getResultArray();
    }
    
    public function getAllRolePermission() {
        return $this->builder('role_module_permission')
                    ->select('role_module_permission.*, module_permission.*, module.*')
                    ->join('module_permission', 'module_permission.id_module_permission = role_module_permission.id_module_permission', 'left')
                    ->join('module', 'module.id_module = module_permission.id_module', 'left')
                    ->get()
                    ->getResultArray();
    }
    
    public function saveData() 
    {
        $this->db->transStart();
        
        $table = $this->db->table('role_module_permission');
        $id_module_post = $this->request->getPost('id_module');
        $id_post = $this->request->getPost('id');
        
        if (!empty($id_module_post) && $id_module_post != 'semua_module') {
            // Get permission IDs for the specific module
            $permissionIds = $this->builder('module_permission')
                                 ->select('id_module_permission')
                                 ->where('id_module', $id_module_post)
                                 ->get()
                                 ->getResultArray();
            
            if (!empty($permissionIds)) {
                $ids = array_column($permissionIds, 'id_module_permission');
                $table->where('id_role', $id_post)
                      ->whereIn('id_module_permission', $ids)
                      ->delete();
            }
        } else {
            $table->delete(['id_role' => $id_post]);
        }
        
        $permissions = $this->request->getPost('permission');
        $data_db = [];
        if (!empty($permissions) && is_array($permissions)) {
            foreach ($permissions as $val) {
                $data_db[] = ['id_role' => $id_post, 'id_module_permission' => $val];
            }
        }
        
        if (!empty($data_db)) {
            $table->insertBatch($data_db);
        }
        
        $this->db->transComplete();
        if ($this->db->transStatus() == false) {
            return false;
        }
        
        return true;
    }
    
    public function hasAllPermission($id_role) {
        // Get all permissions
        $allPermissionsCount = $this->builder('module_permission')->countAllResults();
        
        // Get role permissions count
        $rolePermissionsCount = $this->builder('role_module_permission')
                                    ->where('id_role', $id_role)
                                    ->countAllResults();
        
        // If role has all permissions, return true
        return $rolePermissionsCount >= $allPermissionsCount;
    }
    
    public function deleteAllPermission() {
        return $this->db->table('role_module_permission')->delete(['id_role' => $this->request->getPost('id_role')]);
    }
    
    public function assignPermission() 
    {
        $assign = $this->request->getPost('assign');
        $id_role = $this->request->getPost('id_role');
        $id_permission = $this->request->getPost('id_module_permission');
        
        if ($assign == 'Y') {
            return $this->db->table('role_module_permission')->insert(['id_role' => $id_role, 'id_module_permission' => $id_permission]);
        }
        
        return $this->db->table('role_module_permission')->delete(['id_role' => $id_role, 'id_module_permission' => $id_permission]);
    }
    
    public function assignAllPermission() 
    {
        $assign_all = $this->request->getPost('assign_all');
        $id_role = $this->request->getPost('id_role');
        
        if ($assign_all == 'Y') {
            $data = $this->builder('module_permission')->get()->getResultArray();
            foreach ($data as $val) {
                $data_db[] = ['id_role' => $id_role, 'id_module_permission' => $val['id_module_permission']];
            }
            $this->db->transStart();
            $this->db->table('role_module_permission')->delete(['id_role' => $id_role]);
            $this->db->table('role_module_permission')->insertBatch($data_db);
            $this->db->transComplete();
            return $this->db->transStatus();
        }
        
        return $this->db->table('role_module_permission')->delete(['id_role' => $id_role]);
    }
    
    
    public function countAllDataPermission() {
        return $this->builder('module_permission')->countAllResults();
    }
    
    public function getListDataPermission() {
        $columns = $this->request->getPost('columns');
        $search_all = @$this->request->getPost('search')['value'];
        $id_role = $this->request->getGet('id');
        
        // Get role permissions for JOIN
        $rolePermissions = $this->builder('role_module_permission')
                               ->select('id_module_permission, id_role')
                               ->where('id_role', $id_role)
                               ->get()
                               ->getResultArray();
        
        $rolePermMap = [];
        foreach ($rolePermissions as $rp) {
            $rolePermMap[$rp['id_module_permission']] = $rp['id_role'];
        }
        
        // Build base query
        $builder = $this->builder('module_permission');
        $builder->select('module_permission.*, module.nama_module, module.judul_module')
                ->join('module', 'module.id_module = module_permission.id_module', 'left');
        
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
        
        // Get total filtered
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
        
        // Add id_role to results
        foreach ($data as &$row) {
            $row['id_role'] = $rolePermMap[$row['id_module_permission']] ?? null;
        }
        
        return ['data' => $data, 'total_filtered' => $total_filtered];
    }
    
    public function countAllData() {
        return $this->builder('role')->countAllResults();
    }
    
    public function getListData() {
        $columns = $this->request->getPost('columns');
        $search_all = @$this->request->getPost('search')['value'];
        
        // Build base query for counting
        $builderCount = $this->builder('role');
        
        // Apply search to count query
        if ($search_all && !empty($columns)) {
            $builderCount->groupStart();
            $first = true;
            foreach ($columns as $val) {
                if (strpos($val['data'] ?? '', 'ignore') !== false) continue;
                
                $column = $val['data'] ?? '';
                if ($first) {
                    $builderCount->like($column, $search_all);
                    $first = false;
                } else {
                    $builderCount->orLike($column, $search_all);
                }
            }
            $builderCount->groupEnd();
        }
        
        $total_filtered = $builderCount->countAllResults(false);
        
        // Build main data query with aggregations
        $builder = $this->builder('role');
        $builder->select('role.*, 
                          COUNT(DISTINCT module_permission.id_module) as jml_module,
                          COUNT(module_permission.id_module_permission) as jml_permission')
                ->join('role_module_permission', 'role_module_permission.id_role = role.id_role', 'left')
                ->join('module_permission', 'module_permission.id_module_permission = role_module_permission.id_module_permission', 'left')
                ->groupBy('role.id_role');
        
        // Apply search to main query
        if ($search_all && !empty($columns)) {
            $builder->groupStart();
            $first = true;
            foreach ($columns as $val) {
                if (strpos($val['data'] ?? '', 'ignore') !== false) continue;
                
                $column = $val['data'] ?? '';
                // Only search on role table columns to avoid group by issues
                if (strpos($column, 'role.') === 0 || strpos($column, '.') === false) {
                    if ($first) {
                        $builder->like($column, $search_all);
                        $first = false;
                    } else {
                        $builder->orLike($column, $search_all);
                    }
                }
            }
            $builder->groupEnd();
        }
        
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
?>