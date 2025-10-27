<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

/**
 * Module Model
 * Handles module management operations
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @table      module
 * @version    4.3.1
 */
class ModuleModel extends \App\Models\BaseModel
{
    public function getAllModules() {
        return $this->builder('module')
                   ->orderBy('judul_module')
                   ->get()
                   ->getResultArray();
    }
    
    public function getAllModuleStatus() {
        return $this->builder('module_status')
                   ->get()
                   ->getResultArray();
    }
    
    /**
     * Get module by ID
     * 
     * @param int $id_module Module ID
     * @return array|null
     */
    public function getModuleById(int $id_module): ?array
    {
        return $this->builder('module')
                   ->where('id_module', $id_module)
                   ->get()
                   ->getRowArray();
    }
    
    public function getAllModuleRole() {
        return $this->builder('module_role')
                   ->select('module_role.*, module.*')
                   ->join('module', 'module.id_module = module_role.id_module', 'left')
                   ->get()
                   ->getResultArray();
    }
    
    public function getAllRoles() {
        return $this->builder('role')
                   ->get()
                   ->getResultArray();
    }
    
    private function getPermissionByIdModule($id) {
        return $this->builder('module_permission')
                   ->where('id_module', (int) $id)
                   ->get()
                   ->getResultArray();
    }
    
    public function getRoleByIdModule($id) {
        return $this->builder('role')
                   ->where('id_module', (int) $id)
                   ->get()
                   ->getResultArray();
    }
    
    public function checkModuleUsedDefaultPage() {
        return $this->builder('user')
                   ->where('default_page_type', 'id_module')
                   ->where('default_page_id_module', $this->request->getPost('id'))
                   ->get()
                   ->getResultArray();
    }
    
    public function deleteData() {
        
        $this->db->transStart();
        
        $id = $this->request->getPost('id');
        $this->db->table('module')->delete(['id_module' => $id]);
        $module_permission = $this->getPermissionByIdModule( $id );
        $this->db->table('module_permission')->delete(['id_module' => $id]);
        if ($module_permission) {
            foreach ($module_permission as $val) {
                $this->db->table('role_module_permission')->delete(['id_module_permission' => $val['id_module_permission']]);
            }
        }
        
        $role = $this->getRoleByIdModule($id);
        if ($role) {
            foreach ($role as $val) {
                $this->db->table('role')->update(['id_module' => null], ['id_role' => $val['id_role']]);
            }
        }
        
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            return false;
        } 
        
        return true;

    }
    
    public function updateStatus() {
        
        $field = $_POST['switch_type'] == 'aktif' ? 'id_module_status' : 'login';
        $this->db->table('module')
                    ->update( 
                        [$field => $_POST['id_result']], 
                        ['id_module' => $_POST['id_module']]
                    );
    }
    
    public function getModules() {
        return $this->builder('module')
                   ->select('module.*, module_status.*')
                   ->join('module_status', 'module_status.id_module_status = module.id_module_status', 'left')
                   ->orderBy('module.judul_module')
                   ->get()
                   ->getResultArray();
    }
    
    /**
     * Get permissions for a module
     * Note: BaseModel::getModulePermission() already provides this functionality
     * This method kept for backward compatibility
     * 
     * @param int $id_module Module ID
     * @return array
     */
    public function getModulePermissions(int $id_module): array
    {
        return $this->builder('module_permission')
                   ->where('id_module', $id_module)
                   ->get()
                   ->getResultArray();
    }
    
    public function getRolePermissionByModule($id_module) {
        $query = $this->builder('role_module_permission')
                     ->select('role_module_permission.*, module_permission.*')
                     ->join('module_permission', 'module_permission.id_module_permission = role_module_permission.id_module_permission', 'left')
                     ->where('module_permission.id_module', $id_module)
                     ->get()
                     ->getResultArray();
        $result = [];
        foreach ($query as $val) {
            $result[$val['id_role']][$val['id_module_permission']] = $val;
        }
        return $result;
    }
    
    public function saveData() 
    {
        $fields = ['nama_module', 'judul_module', 'deskripsi', 'id_module_status', 'login'];

        foreach ($fields as $field) {
            $data_db[$field] = $this->request->getPost($field);
        }
        
        // Save database
        $this->db->transStart();
        
        if ($this->request->getPost('id')) {
            $id_module = $this->request->getPost('id');
            $save = $this->db->table('module')->update($data_db, ['id_module' => $_POST['id']]);
        } else {
            $save = $this->db->table('module')->insert($data_db);
            $id_module = $this->db->insertID();
        }
        
        // Permission
        if (!empty($_POST['generate_permission'])) {
            $_POST['id_module'] = $id_module;
            $model = new \App\Models\Builtin\PermissionModel;
            $model->saveData();
        }
                
        $this->db->transComplete();
        
        if ($this->db->transStatus() === false) {
            $result['status'] = 'error';
            $result['message'] = 'Data gagal disimpan';
        } else {
            $result['status'] = 'ok';
            $result['message'] = 'Data berhasil disimpan';
            $result['id_module'] = $id_module;
        }
                                
        return $result;
    }
    
    // EDIT
    public function getRole() {
        $id_role = $this->request->getGet('id');
        $result = $this->builder('role')
                      ->where('id_role', $id_role)
                      ->get()
                      ->getRowArray();
        if (!$result)
            $result = [];
        return $result;
    }
    
    public function countAllData() {
        return $this->builder('module')->countAllResults();
    }
    
    /**
     * Get list data for DataTables with optional WHERE filter
     * 
     * @param array|null $where Optional WHERE conditions (array format for Query Builder)
     * @return array Data and total filtered count
     */
    public function getListData($where = null) {
        $columns = $this->request->getPost('columns');

        // Build base query - MUST initialize from table first
        $builder = $this->builder('module');
        
        // Apply where clause from parameter (array format)
        if (!empty($where) && is_array($where)) {
            $builder->where($where);
        }

        // Search
        $search_all = @$this->request->getPost('search')['value'];
        if ($search_all) {
            $builder->groupStart();
            foreach ($columns as $val) 
            {
                if (strpos($val['data'], 'ignore') !== false)
                    continue;
                
                $builder->orLike($val['data'], $search_all);
            }
            $builder->groupEnd();
        }
        
        // Order        
        $order_data = $this->request->getPost('order');
        if ($order_data && isset($order_data[0]) && isset($columns[$order_data[0]['column']])) {
            $order_column = $columns[$order_data[0]['column']]['data'] ?? '';
            if (strpos($order_column, 'ignore_search') === false) {
                $order_dir = strtoupper($order_data[0]['dir'] ?? 'ASC');
                $builder->orderBy($order_column, $order_dir);
            }
        }

        // Query Total Filtered - countAllResults(false) preserves query
        $total_filtered = $builder->countAllResults(false);
        
        // Query Data
        $start = $this->request->getPost('start') ?: 0;
        $length = $this->request->getPost('length') ?: 10;
        $data = $builder->limit($length, $start)->get()->getResultArray();

        return ['data' => $data, 'total_filtered' => $total_filtered];
    }
    
}
