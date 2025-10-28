<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

/**
 * Setting Registrasi Model
 * Handles registration settings
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @table      setting
 * @version    4.3.1
 *
 * Modified by Mikhael Felian Waskito
 * @link       https://github.com/mikhaelfelian/p59-mav
 * @notes      Refactored to CI4 Query Builder syntax, removed native SQL and improved PHP8.2 compatibility.
 */
class SettingRegistrasiModel extends \App\Models\BaseModel
{
    /**
     * Get all roles
     * 
     * @return array
     */
    public function getRole(): array 
    {
        try {
            return $this->builder('role')
                        ->get()
                        ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to get roles in ' . __CLASS__ . ': ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get registration settings as associative array (param => value)
     * 
     * @return array Associative array with param as key and value as value
     */
    public function getSettingRegistrasi(): array 
    {
        try {
            $query = $this->builder('setting')
                        ->where('type', 'register')
                        ->get()
                        ->getResultArray();
            
            // Convert to associative array (param => value) for easier access
            $setting_register = [];
            foreach ($query as $val) {
                $setting_register[$val['param']] = $val['value'];
            }
            
            return $setting_register;
        } catch (\Throwable $e) {
            log_message('error', 'Failed to get registration settings in ' . __CLASS__ . ': ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get list of modules with their status
     * 
     * @return array
     */
    public function getListModules(): array 
    {
        try {
            return $this->builder('module m')
                        ->select('m.*, ms.*')
                        ->join('module_status ms', 'ms.id_module_status = m.id_module_status', 'left')
                        ->orderBy('m.nama_module', 'ASC')
                        ->get()
                        ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to get modules list in ' . __CLASS__ . ': ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save registration settings
     * 
     * @return array Status result
     */
    public function saveData(): array 
    {
        try {
            // Prepare data from request
            $data_db = [
                ['type' => 'register', 'param' => 'enable', 'value' => $this->request->getPost('enable')],
                ['type' => 'register', 'param' => 'metode_aktivasi', 'value' => $this->request->getPost('metode_aktivasi')],
                ['type' => 'register', 'param' => 'id_role', 'value' => $this->request->getPost('id_role')],
                ['type' => 'register', 'param' => 'default_page_type', 'value' => $this->request->getPost('option_default_page')],
                ['type' => 'register', 'param' => 'default_page_id_role', 'value' => $this->request->getPost('id_role')],
                ['type' => 'register', 'param' => 'default_page_id_module', 'value' => $this->request->getPost('default_page_id_module')],
                ['type' => 'register', 'param' => 'default_page_url', 'value' => $this->request->getPost('default_page_url')]
            ];
            
            // Transaction for delete + insert
            $this->db->transStart();
            
            $this->builder('setting')->delete(['type' => 'register']);
            $this->builder('setting')->insertBatch($data_db);
            
            $this->db->transComplete();
            
            if ($this->db->transStatus()) {
                return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
            } else {
                return ['status' => 'error', 'message' => 'Data gagal disimpan'];
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to save registration settings in ' . __CLASS__ . ': ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Terjadi kesalahan saat menyimpan data'];
        }
    }
}
?>