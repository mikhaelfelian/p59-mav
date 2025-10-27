<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models\Builtin;

use CodeIgniter\Model;

/**
 * Menu Model
 * Handles menu management operations
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @table      menu
 * @version    4.3.1
 */
class MenuModel extends Model
{
    protected $table = 'menu';
    protected $primaryKey = 'id_menu';
    protected $allowedFields = ['nama_menu', 'id_module', 'url', 'id_menu_kategori', 'aktif', 'class', 'id_parent', 'urut'];
    protected $useTimestamps = false;
    
    /**
     * @var MenuKategoriModel
     */
    protected $menuKategoriModel;
    
    /**
     * @var MenuRoleModel
     */
    protected $menuRoleModel;
    
    /**
     * @var ModuleModel
     */
    protected $moduleModel;
    
    /**
     * @var RoleModel
     */
    protected $roleModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->menuKategoriModel = new MenuKategoriModel();
        $this->menuRoleModel = new MenuRoleModel();
        $this->moduleModel = new ModuleModel();
        $this->roleModel = new RoleModel();
    }
    
    /**
     * Get menu by category
     * 
     * @param int|null $id_menu_kategori Category ID
     * @return array
     */
    public function getMenuByKategori($id_menu_kategori = null)
    {
        $query = $this->select('menu.*, module.id_module AS module_id_module, module.nama_module AS module_nama_module, module.judul_module AS module_judul_module')
                      ->join('menu_role', 'menu_role.id_menu = menu.id_menu', 'left')
                      ->join('module', 'module.id_module = menu.id_module', 'left');
        
        if ($id_menu_kategori) {
            $query->where('menu.id_menu_kategori', $id_menu_kategori);
        } else {
            $query->where('(menu.id_menu_kategori = 0 OR menu.id_menu_kategori = "" OR menu.id_menu_kategori IS NULL)', null, false);
        }
        
        $query->orderBy('menu.urut', 'ASC');
        $result = $query->findAll();
        
        // Format result - map module columns to expected names
        $formatted = [];
        foreach ($result as $row) {
            // Map module columns
            if (isset($row['module_id_module'])) {
                $row['id_module'] = $row['module_id_module'];
                $row['nama_module'] = $row['module_nama_module'] ?? null;
                $row['judul_module'] = $row['module_judul_module'] ?? null;
                unset($row['module_id_module'], $row['module_nama_module'], $row['module_judul_module']);
            }
            
            $formatted[$row['id_menu']] = $row;
            $formatted[$row['id_menu']]['highlight'] = 0;
            $formatted[$row['id_menu']]['depth'] = 0;
        }
        
        return $formatted;
    }
    
    /**
     * Get all menus
     * 
     * @return array
     */
    public function getAllMenu()
    {
        return $this->findAll();
    }
    
    /**
     * Get menu by ID with roles
     * 
     * @param int $id Menu ID
     * @return array|null
     */
    public function getMenuById($id)
    {
        $menu_obj = $this->find($id);
        
        if (!$menu_obj) {
            return null;
        }
        
        $menu = is_array($menu_obj) ? $menu_obj : (array)$menu_obj;
        
        // Get roles for this menu
        $roles = $this->menuRoleModel->getMenuRoleById($id);
        $menu['id_role'] = implode(',', array_column($roles, 'id_role'));
        
        return $menu;
    }
    
    /**
     * Get categories - delegate to MenuKategoriModel
     * 
     * @return array
     */
    public function getKategori()
    {
        return $this->menuKategoriModel->getAll();
    }
    
    /**
     * Get category by ID - delegate to MenuKategoriModel
     * 
     * @param int $id Category ID
     * @return array|null
     */
    public function getKategoriById($id)
    {
        return $this->menuKategoriModel->getById($id);
    }
    
    /**
     * Get all roles - delegate to RoleModel
     * 
     * @return array
     */
    public function getAllRole()
    {
        return $this->roleModel->getAllRole();
    }
    
    /**
     * Get list of modules - delegate to ModuleModel
     * 
     * @return array
     */
    public function getListModules()
    {
        return $this->moduleModel->getModules();
    }
    
    /**
     * Save menu
     * 
     * @param int|null $id Menu ID for update
     * @return bool|int Menu ID on success, false on failure
     */
    public function saveMenu($id = null)
    {
        $data = [
            'nama_menu' => $_POST['nama_menu'],
            'id_module' => $_POST['id_module'] ?: null,
            'url' => $_POST['url'],
            'id_menu_kategori' => trim($_POST['id_menu_kategori']) ?: null,
            'aktif' => !empty($_POST['aktif']) ? 1 : 0,
            'class' => $_POST['use_icon'] ? $_POST['icon_class'] : null,
        ];
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        if ($id) {
            // Update existing menu
            $old_menu_obj = $this->find($id);
            $old_menu = is_array($old_menu_obj) ? $old_menu_obj : (array)$old_menu_obj;
            
            // Check if category changed
            if ($old_menu['id_menu_kategori'] != $data['id_menu_kategori']) {
                $data['id_parent'] = null;
            }
            
            $this->update($id, $data);
            
            // Update category for all children
            if (isset($_POST['menu_tree'])) {
                $json = json_decode(trim($_POST['menu_tree']), true);
                $array = $this->buildChild($json);
                $all_child = $this->allChild($id, $array);
                
                foreach ($all_child as $child_id) {
                    $this->update($child_id, ['id_menu_kategori' => $data['id_menu_kategori']]);
                }
            }
            
            // Update roles
            if (!empty($_POST['id_role'])) {
                $this->menuRoleModel->updateMenuRoles($id, $_POST['id_role']);
            } else {
                $this->menuRoleModel->deleteByMenuId($id);
            }
            
            $result = $id;
        } else {
            // Insert new menu
            $this->insert($data);
            $insert_id = $this->insertID();
            
            // Save roles
            if (!empty($_POST['id_role'])) {
                $this->menuRoleModel->createMenuRoles($insert_id, $_POST['id_role']);
            }
            
            $result = $insert_id;
        }
        
        $db->transComplete();
        
        if (!$db->transStatus()) {
            return false;
        }
                
        return $result;
    }
    
    /**
     * Delete menu and all children
     * 
     * @return bool
     */
    public function deleteMenu()
    {
        $id = $_POST['id'];
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        // Get all children
        if (isset($_POST['menu_tree'])) {
            $json = json_decode(trim($_POST['menu_tree']), true);
            $array = $this->buildChild($json);
            $all_child = $this->allChild($id, $array);
        } else {
            $all_child = [];
        }
        
        // Delete all children
        if ($all_child) {
            foreach ($all_child as $child_id) {
                $this->delete($child_id);
            }
        }
        
        // Delete the menu itself
        $this->delete($id);
        
        $db->transComplete();
        return $db->transStatus();
    }
    
    /**
     * Update menu sort order
     * 
     * @return bool
     */
    public function updateMenuUrut()
    {
        $json = json_decode(trim($_POST['data']), true);
        $array = $this->buildChild($json);
        
        $list_menu = [];
        foreach ($array as $id_parent => $arr) {
            foreach ($arr as $key => $id_menu) {
                $list_menu[$id_menu] = ['id_parent' => $id_parent, 'urut' => ($key + 1)];
            }
        }
    
        $id_menu_kategori = trim($_POST['id_menu_kategori']);
        
        // Get all menus in this category
        if (empty($id_menu_kategori)) {
            $result = $this->where('(id_menu_kategori = "" OR id_menu_kategori IS NULL)', null, false)->findAll();
        } else {
            $result = $this->where('id_menu_kategori', $id_menu_kategori)->findAll();
        }
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        foreach ($result as $row) {
            $row_array = is_array($row) ? $row : (array)$row;
            $data_db = [];
            
            if ($list_menu[$row_array['id_menu']]['id_parent'] != $row_array['id_parent']) {
                $id_parent = $list_menu[$row_array['id_menu']]['id_parent'] == 0 ? null : $list_menu[$row_array['id_menu']]['id_parent'];
                $data_db['id_parent'] = $id_parent;
            }
            
            if ($list_menu[$row_array['id_menu']]['urut'] != $row_array['urut']) {
                $data_db['urut'] = $list_menu[$row_array['id_menu']]['urut'];
            }
            
            if ($data_db) {
                $this->update($row_array['id_menu'], $data_db);
            }
        }
        
        $db->transComplete();
        return $db->transStatus();
    }
    
    /**
     * Save category - delegate to MenuKategoriModel
     * 
     * @param array $data Category data
     * @return array Result with status and message
     */
    public function saveKategori($data)
    {
        if (isset($data['id'])) {
            $this->menuKategoriModel->update($data['id'], [
                'nama_kategori' => $data['nama_kategori'],
                'deskripsi' => $data['deskripsi'],
                'aktif' => $data['aktif'],
                'show_title' => $data['show_title'],
            ]);
            
            $result = ['status' => 'ok', 'message' => 'Menu berhasil diupdate'];
        } else {
            $next_urut = $this->menuKategoriModel->getNextUrut();
            $id_kategori = $this->menuKategoriModel->insert([
                'nama_kategori' => $data['nama_kategori'],
                'deskripsi' => $data['deskripsi'],
                'aktif' => $data['aktif'],
                'show_title' => $data['show_title'],
                'urut' => $next_urut,
            ]);
            
            $result = ['status' => 'ok', 'message' => 'Menu berhasil ditambah', 'id_kategori' => $id_kategori];
        }
        
        return $result;
    }
    
    /**
     * Update category sort order - delegate to MenuKategoriModel
     * 
     * @param array $list_kategori Array of category IDs
     * @return bool
     */
    public function updateKategoriUrut($list_kategori)
    {
        return $this->menuKategoriModel->updateUrut($list_kategori);
    }
    
    /**
     * Delete category by ID - delegate to MenuKategoriModel
     * 
     * @param int $id Category ID
     * @return bool
     */
    public function deleteKategoriById($id) 
    {
        return $this->menuKategoriModel->deleteCategory($id);
    }
    
    /**
     * Build child hierarchy from tree structure
     * 
     * @param array $arr Tree structure
     * @param int $parent Parent ID
     * @param array $list Output list
     * @return array
     */
    private function buildChild($arr, $parent = 0, &$list = [])
    {
        foreach ($arr as $key => $val) {
            $list[$parent][] = $val['id'];

            if (key_exists('children', $val)) {
                $this->buildChild($val['children'], $val['id'], $list);
            }
        }
        
        return $list;
    }
    
    /**
     * Get all child IDs recursively
     * 
     * @param int $id Menu ID
     * @param array $list Hierarchy list
     * @param array $result Output result
     * @return array
     */
    private function allChild($id, $list, &$result = []) 
    {
        if (!key_exists($id, $list)) {
            return $result;
        }
        
        $result[$id] = $id;
        foreach ($list[$id] as $val) {
            $result[$val] = $val;
            if (key_exists($val, $list)) {
                $this->allChild($val, $list, $result);
            }
        }
        
        return $result;
    }
}
