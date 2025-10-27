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
 */
class SettingLayoutModel extends \App\Models\BaseModel
{
    public function getDefaultSetting() {
        $sql = 'SELECT * FROM setting WHERE type="layout"';
        $data = $this->db->query($sql)->getResultArray();
        return $data;
    }
    
    public function getUserSetting() {
        $sql = 'SELECT * FROM setting_user WHERE id_user = ? AND type="layout"';
        $data = $this->db->query($sql, $_SESSION['user']['id_user'])
                    ->getRowArray();
        return $data;
    }
    
    public function saveData() 
    {
        $query = false;
        
        $params = ['color_scheme' => 'Color Scheme'
            , 'bootswatch_theme' => 'Theme'
            , 'sidebar_color' => 'Sidebar Color'
            , 'logo_background_color' => 'Background Logo'
            , 'font_family' => 'Font Family'
            , 'font_size' => 'Font Size'
        ];
        
        foreach ($params as $param => $title) {
            $data_db[] = ['type' => 'layout', 'param' => $param, 'value' => $_POST[$param]];
            $arr[$param] = $_POST[$param];
        }
        
        if (key_exists('update_all', $_SESSION['user']['permission']))
        {
            $this->db->transStart();
            $this->db->table('setting')->delete(['type' => 'layout']);
            $this->db->table('setting')->insertBatch($data_db);
            $this->db->transComplete();
            $result = $this->db->transStatus();
            
            if ($this->db->transStatus()) {
                $file_name = ROOTPATH . 'public/themes/modern/builtin/css/fonts/font-size-' . $_POST['font_size'] . '.css';
                if (!file_exists($file_name)) {
                    file_put_contents($file_name, 'html, body { font-size: ' . $_POST['font_size'] . 'px }');
                }                        
            }
            
        } else if (key_exists('update_own', $_SESSION['user']['permission'])) 
        {
            $this->db->transStart();
            $this->db->table('setting_user')->delete(['id_user' => $_SESSION['user']['id_user'], 'type' => 'layout' ]);
            $result = $this->db->table('setting_user')->insert([
                                                            'id_user' => $_SESSION['user']['id_user']
                                                            , 'param' => json_encode($arr)
                                                            , 'type' => 'layout'
                                                        ]
                            );
            $this->db->transComplete();
            $result = $this->db->transStatus();
        }
        
        return $result;
    }
}
?>