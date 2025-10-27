<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

/**
 * Setting Layout Model
 * Handles layout settings
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
class SettingLayoutModel extends \App\Models\BaseModel
{
    /**
     * Get default layout settings
     * 
     * @return array
     */
    public function getDefaultSetting(): array 
    {
        try {
            return $this->builder('setting')
                        ->where('type', 'layout')
                        ->get()
                        ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to get default layout settings in ' . __CLASS__ . ': ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user-specific layout settings (as single row array)
     * 
     * @return array|null
     */
    public function getUserLayoutSetting(): ?array 
    {
        try {
            $userId = $this->session->get('user')['id_user'] ?? null;
            if (!$userId) {
                return null;
            }
            
            return $this->builder('setting_user')
                        ->where('id_user', $userId)
                        ->where('type', 'layout')
                        ->get()
                        ->getRowArray();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to get user layout settings in ' . __CLASS__ . ': ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Save layout settings (global or user-specific based on permissions)
     * 
     * @return bool Success status
     */
    public function saveData(): bool 
    {
        try {
            $params = [
                'color_scheme' => 'Color Scheme',
                'bootswatch_theme' => 'Theme',
                'sidebar_color' => 'Sidebar Color',
                'logo_background_color' => 'Background Logo',
                'font_family' => 'Font Family',
                'font_size' => 'Font Size'
            ];
            
            $data_db = [];
            $arr = [];
            
            foreach ($params as $param => $title) {
                $value = $this->request->getPost($param);
                $data_db[] = ['type' => 'layout', 'param' => $param, 'value' => $value];
                $arr[$param] = $value;
            }
            
            $userPermissions = $this->session->get('user')['permission'] ?? [];
            $userId = $this->session->get('user')['id_user'] ?? null;
            
            // Update global settings if user has update_all permission
            if (key_exists('update_all', $userPermissions)) {
                $this->db->transStart();
                
                $this->builder('setting')->delete(['type' => 'layout']);
                $this->builder('setting')->insertBatch($data_db);
                
                $this->db->transComplete();
                
                $result = $this->db->transStatus();
                
                // Create font-size CSS file if transaction successful
                if ($result) {
                    $fontSize = $this->request->getPost('font_size');
                    $file_name = ROOTPATH . 'public/themes/modern/builtin/css/fonts/font-size-' . $fontSize . '.css';
                    if (!file_exists($file_name)) {
                        file_put_contents($file_name, 'html, body { font-size: ' . $fontSize . 'px }');
                    }
                }
                
                return $result;
            } 
            // Update user-specific settings if user has update_own permission
            else if (key_exists('update_own', $userPermissions) && $userId) {
                $this->db->transStart();
                
                $this->builder('setting_user')->delete(['id_user' => $userId, 'type' => 'layout']);
                $this->builder('setting_user')->insert([
                    'id_user' => $userId,
                    'param' => json_encode($arr),
                    'type' => 'layout'
                ]);
                
                $this->db->transComplete();
                
                return $this->db->transStatus();
            }
            
            return false;
        } catch (\Throwable $e) {
            log_message('error', 'Failed to save layout settings in ' . __CLASS__ . ': ' . $e->getMessage());
            return false;
        }
    }
}
?>