<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

/**
 * Module Role Model
 * Handles module-role relationships
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @table      module_role
 * @version    4.3.1
 */
class ModuleRoleModel extends \App\Models\BaseModel
{
    public function getAllModule() {
        $sql = 'SELECT * FROM module';
        $result = $this->db->query($sql)->getResultArray();
        return $result;
    }
    
    /**
     * Get module by ID
     * 
     * @param int|string $id Module ID
     * @return array|null
     */
    public function getModuleById($id): ?array
    {
        return $this->builder('module')
                   ->where('id_module', $id)
                   ->get()
                   ->getRowArray();
    }
    
    public function getAllRole() {
        $sql = 'SELECT * FROM role';
        $result = $this->db->query($sql)->getResultArray();
        return $result;
    }
    
    public function getRoleDetail() {
        $sql = 'SELECT * FROM role_detail';
        $result = $this->db->query($sql)->getResultArray();
        return $result;
    }
    
    public function getAllModuleRole() {
        $sql = 'SELECT module_role.*, nama_role, judul_role FROM module_role LEFT JOIN role USING(id_role)';
        $result = $this->db->query($sql)->getResultArray();
        return $result;
    }
    
    public function getModuleRoleById($id) {
        $sql = 'SELECT * FROM module_role WHERE id_module = ?';
        $result = $this->db->query($sql, [$id])->getResultArray();
        // echo '<pre>'; print_r($result); die;
        return $result;
    }
    
    public function getModuleStatus() {
        $sql = 'SELECT * FROM module m
                LEFT JOIN module_status ms ON ms.id_module_status = m.id_module_status';
                
        $result = $this->db->query($sql)->getResultArray();
        return $result;
    }
    
    public function deleteData() {
        $this->db->table('module_role')->delete(['id_module' => $_POST['id_module'], 'id_role' => $_POST['id_role']]);
        return $this->db->affectedRows();
    }
    
    public function saveData() 
    {
        foreach ($_POST as $key => $val) {
            $exp = explode('_', $key);
            if ($exp[0] == 'role') {
                $id_role = $exp[1];
                $data_db[] = ['id_module' => $_POST['id']
                                , 'id_role' => $id_role
                                , 'read_data' => $_POST['akses_read_data_' . $id_role]
                                , 'create_data' => $_POST['akses_create_data_' . $id_role]
                                , 'update_data' => $_POST['akses_update_data_' . $id_role]
                                , 'delete_data' => $_POST['akses_delete_data_' . $id_role]
                            ];
            }
        }
        
        // INSERT - UPDATE
        $this->db->transStart();
        $this->db->table('module_role')->delete(['id_module' => $_POST['id']]);
        $this->db->table('module_role')->insertBatch($data_db);
        $this->db->transComplete();
        $result = $this->db->transStatus();
                                
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
        $search_all = @$this->request->getPost('search')['value'];
        
        // Build base query - MUST initialize from table first
        $builder = $this->builder('module');
        
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
?>