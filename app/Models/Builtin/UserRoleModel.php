<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

/**
 * User Role Model
 * Handles user-role relationships
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @table      user_role
 * @version    4.3.1
 */
class UserRoleModel extends \App\Models\BaseModel
{
    public function getAllRole() {
        $sql = 'SELECT * FROM role';
        $result = $this->db->query($sql)->getResultArray();
        return $result;
    }
    
    public function getUserRole() {
        $sql = 'SELECT * FROM user_role LEFT JOIN role USING(id_role)';
        $result = $this->db->query($sql)->getResultArray();
        return $result;
    }
    
    public function getUserRoleByID($id) {
        $sql = 'SELECT * FROM user_role WHERE id_user = ?';
        $result = $this->db->query($sql, $id)->getResultArray();
        return $result;
    }
    
    public function getAllUser() {
        $sql = 'SELECT * FROM user';
        $result = $this->db->query($sql)->getResultArray();
        return $result;
    }
    
    public function deleteData() {
        $this->db->table('user_role')->delete(['id_user' => $_POST['id_user'], 'id_role' => $_POST['id_role']]);
        return $this->db->affectedRows();
    }
    
    public function saveData() 
    {
        $this->db->transStart();
        $this->db->table('user_role')->delete(['id_user' => $_POST['id_user']]);
        
        if (!empty($_POST['id_role'])) {
            foreach ($_POST['id_role'] as $key => $id_role) {
                $insert[] = ['id_user' => $_POST['id_user'], 'id_role' => $id_role];
            }
            $this->db->table('user_role')->insertBatch($insert);
        }
        $this->db->transComplete();
        $result = $this->db->transStatus();
        
        return $result;
    }
    
    public function countAllData() {
        return $this->builder('user')->countAllResults();
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
        $builder = $this->builder('user');
        
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