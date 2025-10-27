<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

/**
 * Role Model
 * Handles role management operations
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @table      role
 * @version    4.3.1
 */
class RoleModel extends \App\Models\BaseModel
{
    public function getAllModules() {
        
        $sql = 'SELECT * FROM module';
        return $this->db->query($sql)->getResultArray();
    }
    
    public function getModuleStatus() {
        $sql = 'SELECT * FROM module_status';
        $result = $this->db->query($sql)->getResultArray();
        return $result;
    }
    
    public function listModuleRole() {
        $sql = 'SELECT * FROM role r LEFT JOIN module m ON m.id_module = r.id_module';
        $result = $this->db->query($sql)->getResultArray();
        return $result;
    }
    
    public function getAllRole() {
        $sql = 'SELECT * FROM role';
        $result = $this->db->query($sql)->getResultArray();
        return $result;
    }
    
    public function getListModules() {
        
        $sql = 'SELECT * FROM role_module_permission rmp
                    LEFT JOIN module_permission mp ON mp.id_module_permission = rmp.id_module_permission
                    LEFT JOIN module m ON m.id_module = mp.id_module
                    LEFT JOIN module_status ms ON ms.id_module_status = m.id_module_status
                ORDER BY m.nama_module';
        return $this->db->query($sql)->getResultArray();
    }
    
    public function checkRoleUsedDefaultPage() {
        $sql = 'SELECT * FROM user WHERE default_page_type = "id_role" AND default_page_id_role = ?';
        return $this->db->query($sql, $this->request->getPost('id'))->getResultArray();
    }
    
    // EDIT
    public function getRole() {
        $id_role = $this->request->getGet('id');
        $sql = 'SELECT * FROM role WHERE id_role = ?';
        $result = $this->db->query($sql, [$id_role])->getRowArray();
        if (!$result)
            $result = [];
        return $result;
    }
    
    public function saveData() 
    {
        $fields = ['nama_role', 'judul_role', 'keterangan', 'id_module'];

        foreach ($fields as $field) {
            $data_db[$field] = $this->request->getPost($field);
        }
        $fields['id_module'] = $this->request->getPost('id_module') ?: 0;
        
        // Save database
        if ($this->request->getPost('id')) {
            $id_role = $this->request->getPost('id');
            $save = $this->db->table('role')->update($data_db, ['id_role' => $id_role]);
        } else {
            $save = $this->db->table('role')->insert($data_db);
            $id_role = $this->db->insertID();
        }
        
        if ($save) {
            $result['status'] = 'ok';
            $result['message'] = 'Data berhasil disimpan';
            $result['id_role'] = $id_role;
        } else {
            $result['status'] = 'error';
            $result['message'] = 'Data gagal disimpan';
        }
                                
        return $result;
    }
    
    public function deleteData() {
        $this->db->table('role')->delete(['id_role' => $this->request->getPost('id')]);
        return $this->db->affectedRows();
    }
    
    public function countAllData() {
        return $this->builder('role')->countAllResults();
    }
    
    public function getListData() {
        $columns = $this->request->getPost('columns');
        $search_all = @$this->request->getPost('search')['value'];
        
        // Build base query - MUST initialize from table first
        $builder = $this->builder('role');
        
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
        
        // Get total filtered - MUST be called on initialized builder
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
