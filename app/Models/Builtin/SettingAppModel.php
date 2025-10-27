<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

/**
 * Setting App Model
 * Handles application settings
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
class SettingAppModel extends \App\Models\BaseModel
{
    /**
     * Get application settings
     * 
     * @return array
     */
    public function getSettingAplikasi(): array 
    {
        try {
            return $this->builder('setting')
                        ->where('type', 'app')
                        ->get()
                        ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to get app settings in ' . __CLASS__ . ': ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user-specific layout settings (as array)
     * 
     * @return array
     */
    public function getUserLayoutSettings(): array 
    {
        try {
            $userId = $this->session->get('user')['id_user'] ?? null;
            if (!$userId) {
                return [];
            }
            
            return $this->builder('setting_user')
                        ->where('id_user', $userId)
                        ->where('type', 'layout')
                        ->get()
                        ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to get user layout settings in ' . __CLASS__ . ': ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save application settings with image uploads
     * 
     * @return array Status result
     */
    public function saveData(): array 
    {
        helper(['util', 'upload_file']);
        
        // Get current settings using Query Builder
        try {
            $query = $this->builder('setting')
                          ->where('type', 'app')
                          ->get()
                          ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to fetch current settings in ' . __CLASS__ . ': ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Gagal mengambil data saat ini'];
        }
        
        $curr_db = [];
        foreach($query as $val) {
            $curr_db[$val['param']] = $val['value'];
        }
        
        // Logo Login
        $logo_login = $curr_db['logo_login'];
        $path = ROOTPATH . 'public/images/';
        if ($_FILES['logo_login']['name']) 
        {
            //old file
            if ($curr_db['logo_login']) {
                if (file_exists($path . $curr_db['logo_login'])) {
                    $unlink = delete_file($path . $curr_db['logo_login']);
                    if (!$unlink) {
                        $data['msg']['status'] = 'error';
                        $data['msg']['content'] = 'Gagal menghapus gambar lama';
                    }
                }
            }
            
            $logo_login = \upload_file($path, $_FILES['logo_login']);
        }
        
        // Logo App
        $logo_app = $curr_db['logo_app'];
        if ($_FILES['logo_app']['name']) 
        {
            //old file
            if ($curr_db['logo_app']) {
                if (file_exists($path . $curr_db['logo_app'])) {
                    $unlink = delete_file($path . $curr_db['logo_app']);
                    if (!$unlink) {
                        $data['msg']['status'] = 'error';
                        $data['msg']['content'] = 'Gagal menghapus gambar lama';
                    }
                }
            }
            
            $logo_app = \upload_file($path, $_FILES['logo_app']);
        }
        
        // Favicon
        $favicon = $curr_db['favicon'];
        if ($_FILES['favicon']['name']) 
        {
            //old file
            if ($curr_db['favicon']) {
                if (file_exists($path . $curr_db['favicon'])) {
                    $unlink = delete_file($path . $curr_db['favicon']);
                    if (!$unlink) {
                        $data['msg']['status'] = 'error';
                        $data['msg']['content'] = 'Gagal menghapus gambar lama';
                    }
                }
            }
            
            $favicon = \upload_file($path, $_FILES['favicon']);
        }
        
        // Logo Register
        $logo_register = $curr_db['logo_register'];
        if ($_FILES['logo_register']['name']) 
        {
            //old file
            if ($curr_db['logo_register']) {
                if (file_exists($path . $curr_db['logo_register'])) {
                    $unlink = delete_file($path . $curr_db['logo_register']);
                    if (!$unlink) {
                        $data['msg']['status'] = 'error';
                        $data['msg']['content'] = 'Gagal menghapus gambar lama';
                    }
                }
            }
            
            $logo_register = \upload_file($path, $_FILES['logo_register']);
        }
        
        $data_db =[];
        if ($logo_login && $logo_app && $favicon && $logo_register) 
        {
            $data_db[] = ['type' => 'app', 'param' => 'logo_login', 'value' => $logo_login];
            $data_db[] = ['type' => 'app', 'param' => 'logo_app', 'value' => $logo_app];
            $data_db[] = ['type' => 'app', 'param' => 'footer_login', 'value' => htmlentities($_POST['footer_login'])];
            $data_db[] = ['type' => 'app', 'param' => 'btn_login', 'value' => $_POST['btn_login']];
            $data_db[] = ['type' => 'app', 'param' => 'footer_app', 'value' => htmlentities($_POST['footer_app'])];
            $data_db[] = ['type' => 'app', 'param' => 'background_logo', 'value' => $_POST['background_logo']];
            $data_db[] = ['type' => 'app', 'param' => 'judul_web', 'value' => $_POST['judul_web']];
            $data_db[] = ['type' => 'app', 'param' => 'deskripsi_web', 'value' => $_POST['deskripsi_web']];
            $data_db[] = ['type' => 'app', 'param' => 'favicon', 'value' => $favicon];
            $data_db[] = ['type' => 'app', 'param' => 'logo_register', 'value' => $logo_register];
            
            $this->db->transStart();
            $this->db->table('setting')->delete(['type' => 'app']);
            $this->db->table('setting')->insertBatch($data_db);
            $query = $this->db->transComplete();
            $query_result = $this->db->transStatus();
            
            if ($query_result) {
                $file_name = ROOTPATH . 'public/themes/modern/builtin/css/login-header.css';
                $css = '.login-header {background-color: '.$_POST['background_logo'].';}.edit-logo-login-container {background: '.$_POST['background_logo'].';}';
                
                if (file_exists($file_name)) {
                    file_put_contents($file_name, $css);
                }
                
                $result['status'] = 'ok';
                $result['message'] = 'Data berhasil disimpan';
            } else {
                $result['status'] = 'error';
                $result['message'] = 'Data gagal disimpan';
            }
            
        } else {
            $result['status'] = 'error';
            $result['content'] = 'Error saat memperoses gambar';
        }

        return $result;
    }
}
?>