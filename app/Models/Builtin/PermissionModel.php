<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

/**
 * Permission Model
 * Handles permission management operations
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @table      module_permission
 * @version    4.3.1
 */
class PermissionModel extends \App\Models\BaseModel
{
    public function getAllModules() {
        
        $sql = 'SELECT * FROM module ORDER BY judul_module';
        $modules =  $this->db->query($sql)->getResultArray();
        foreach ($modules as $val) {
            $result[$val['id_module']] = $val['judul_module'];
        }
        
        return $result;
    }
    
    public function getModuleById($id_module) {
        
        $sql = 'SELECT * FROM module WHERE id_module = ?';
        $result =  $this->db->query($sql, $id_module)->getRowArray();
        return $result;
    }

    public function getPermissionById(int $id = null) 
    {
        $sql = 'SELECT * FROM module_permission mp LEFT JOIN module m ON m.id_module = mp.id_module WHERE mp.id_module_permission = ?';
        $module_permission = $this->db->query($sql, $id )->getRowArray();
        
        return $module_permission;
    }
    
    // For controller "module"
    public function getRolePermission($id_role) {
        
        $sql = 'SELECT id_module_permission FROM role_module_permission WHERE id_role = ?';
        $result =  $this->db->query($sql, $id_role)->getResultArray();
        return $result;
    }
    // --
    
    public function getPermission(int $id = null) 
    {
        $result = [];
        if ($id) {
            $sql = 'SELECT * FROM module_permission mp LEFT JOIN module m ON m.id_module = mp.id_module WHERE mp.id_module = ?';
            $module_permission = $this->db->query($sql, $id )->getResultArray();
        }
        
        else {
            $sql = 'SELECT * FROM module m LEFT JOIN module_permission mp ON mp.id_module = m.id_module ORDER BY mp.nama_permission, m.judul_module';
            $module_permission = $this->db->query($sql)->getResultArray();
        }
        
        foreach ($module_permission as $val) {
            $result[$val['id_module']][$val['id_module_permission']] = $val;
        }

        return $result;
    }
    
    public function checkDuplicate() {
        $result = false;
        if (!empty($_POST['nama_permission_old'])) {
            if ($_POST['nama_permission'] != $_POST['nama_permission_old']) {
                $sql = 'SELECT * FROM module_permission WHERE nama_permission = ? AND id_module = ?';
                $result  = $this->db->query($sql, [$_POST['nama_permission'], $_POST['id_module']] )->getRowArray();
            }
        }
        return $result;
    }
    
    /*
        Method for save data
    */
    private function checkPermissionExists($permission) 
    {
        $sql = 'SELECT * FROM module_permission 
                    WHERE id_module = ? 
                    AND nama_permission IN ("' . join('","', $permission) . '")';
                    
        // echo $sql; die;
                    
        $query = $this->db->query($sql, (int) $_POST['id_module'])->getResultArray();
        $permission_exists = [];
        foreach ($query as $val) {
            $permission_exists[$val['nama_permission']] = $val['nama_permission'];
        }
        return $permission_exists;
    }
    
    private function saveCrud() 
    {
        $keterangan = ['membuat', 'membaca', 'mengupdate', 'menghapus'];
        
        // Cek exists
        $list_permission = ["create", "read_all", "update_all", "delete_all"];
        $permission_exists = $this->checkPermissionExists($list_permission);
        
        foreach ($list_permission as $key => $nama_permission) 
        {
            if (in_array($nama_permission, $permission_exists))
                continue;
            
            $data_db = [];
            $data_db['id_module'] = (int) $_POST['id_module'];
            $data_db['nama_permission'] = $nama_permission;
            $data_db['judul_permission'] = ucwords( str_replace('_', ' ', $nama_permission) ) . ' Data';
            $ket_data = $nama_permission == 'create' ? ' data' : ' semua data';
            $data_db['keterangan'] = 'Hak akses untuk ' . $keterangan[$key] . $ket_data;
            $query = $this->db->table('module_permission')->insert($data_db);
        }
    }
    
    private function saveCrudOwn() 
    {
        $keterangan = ['membuat', 'membaca', 'mengupdate', 'menghapus'];
        
        // Cek exists
        $list_permission = ["create", "read_own", "update_own", "delete_own"];
        $permission_exists = $this->checkPermissionExists($list_permission);
        
        // print_r($permission_exists); die;
        foreach ($list_permission as $key => $nama_permission) 
        {
            if (in_array($nama_permission, $permission_exists))
                continue;
            
            $data_db = [];
            $data_db['id_module'] = (int) $_POST['id_module'];
            $data_db['nama_permission'] = $nama_permission;
            $data_db['judul_permission'] = ucwords( str_replace('_', ' ', $nama_permission) ) . ' Data';
            $ket_data = $nama_permission == 'create' ? ' data' : ' data miliknya sendiri';
            $data_db['keterangan'] = 'Hak akses untuk ' . $keterangan[$key] . $ket_data;
            $query = $this->db->table('module_permission')->insert($data_db);
        }
    }
    
    public function saveData() 
    {
        $this->db->transStart();
        
        $id_new = '';
        if ($_POST['generate_permission']) {
        
            if ($_POST['generate_permission'] == 'crud_all') {
                $this->saveCrud();
            } else if (  $_POST['generate_permission'] == 'crud_own' ) {
                $this->saveCrudOwn();
            } else if (  $_POST['generate_permission'] == 'crud_all_crud_own' ) {
                $this->saveCrud();
                $this->saveCrudOwn();
            } else {
                
                $data_db['id_module'] = (int) $_POST['id_module'];
                $data_db['nama_permission'] = $_POST['nama_permission'];
                $data_db['judul_permission'] = $_POST['judul_permission'];
                $data_db['keterangan'] =  $_POST['keterangan'];
                if (empty($_POST['id'])) {
                    $query = $this->db->table('module_permission')->insert($data_db);
                    $id_new = $this->db->insertID();
                } else {
                    $query = $this->db->table('module_permission')->update($data_db, ['id_module_permission' => (int) $_POST['id']] );
                }
            }
            
            if (!empty($_POST['id_role']))
            {
                $id_module = (int) $_POST['id_module'];
                $sql = 'SELECT * FROM module_permission WHERE id_module = ?';
                $module_permission = $this->db->query($sql, $id_module)->getResultArray();
                $values = [];
                foreach ($module_permission as $val) {
                    $values[] = ['id_role' => $_POST['id_role'],  'id_module_permission' => $val['id_module_permission']];
                }
                
                if ($values){
                    $this->db->table('role_module_permission')->insertBatch($values);
                }
            }
        }
                
        $this->db->transComplete();
        if ($this->db->transStatus() == false) {
            $result['status'] = 'error';
            $result['message'] = 'Data gagal disimpan';
        } else {
            $result['status'] = 'ok';
            $result['message'] = 'Data berhasil disimpan';
        }
        $result['id'] = $id_new;
        return $result;
    }
    
    /*
        -- Method for save data
    */
    
    public function deletePermissionByModule($id) 
    {
        $this->db->transStart();
        $sql = 'DELETE FROM role_module_permission 
                    WHERE id_module_permission 
                    IN (SELECT id_module_permission FROM module_permission WHERE id_module = ?)';
        $this->db->query($sql, (int) trim($id));
        $this->db->table('module_permission')->delete(['id_module' => (int) trim($id) ]);
        $this->db->transComplete();
        return $this->db->transStatus();
    }
    
    public function deleteData($id) {
        $this->db->transStart();
        $delete = $this->db->table('role_module_permission')->delete(['id_module_permission' => (int) trim($id) ]);
        $delete = $this->db->table('module_permission')->delete(['id_module_permission' => (int) trim($id) ]);
        $this->db->transComplete();
        return $this->db->transStatus();
    }
    
    /**
     * Count all data with optional WHERE filter
     * 
     * @param array|null $where Optional WHERE conditions (array format for Query Builder)
     * @return int Total count
     */
    public function countAllData($where = null) {
        $builder = $this->builder('module_permission');
        
        if (!empty($where) && is_array($where)) {
            $builder->where($where);
        }
        
        return $builder->countAllResults();
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
        
        // Build base query - MUST initialize from table first with JOIN
        $builder = $this->builder('module_permission mp')
                        ->join('module m', 'm.id_module = mp.id_module', 'left');
        
        // Apply where clause from parameter (array format)
        if (!empty($where) && is_array($where)) {
            $builder->where($where);
        }
        
        // Apply search
        if ($search_all && !empty($columns)) {
            $builder->groupStart();
            $first = true;
            foreach ($columns as $val) {
                if (strpos($val['data'] ?? '', 'ignore_search') !== false) continue;
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