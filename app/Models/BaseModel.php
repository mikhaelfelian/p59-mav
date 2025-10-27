<?php
/**
 * Admin Template Codeigniter 4 	
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models;

use App\Libraries\Auth;
use CodeIgniter\Model;

/**
 * Base Model
 * Extended model class with automatic primary key detection
 * Provides common functionality for all models
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @version    4.3.1
 *
 * Modified by Mikhael Felian Waskito
 * @link       https://github.com/mikhaelfelian/p59-mav
 * @notes      Added automatic primary key detection and CI 4.3.1 compliance refactor
 */
class BaseModel extends Model
{
    // Modern CI4 Model Structure
    protected $table;
    protected $primaryKey = 'id';
    protected $allowedFields = [];
    protected $useTimestamps = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $deletedField = 'deleted_at';

    // Custom Properties
    protected $request;
    protected $session;
    protected $auth;
    protected $user;

    /**
     * Initialize the model
     */
    public function __construct()
    {
        parent::__construct();

        // Load services
        $this->request = \Config\Services::request();
        $this->session = \Config\Services::session();
        $this->auth = new Auth();

        // Load user from session
        $user = $this->session->get('user');
        if ($user) {
            $this->user = $this->getUserById($user['id_user']);
        }

        // Auto-detect primary key if not set
        $this->autoDetectPrimaryKey();
    }

    /**
     * Automatically detect primary key from database schema
     * Compatible with CI 4.3.x and forward-compatible with CI 4.6.x
     * 
     * This method runs during constructor and detects the primary key field
     * by inspecting the table schema when $primaryKey is not explicitly set.
     */
    protected function autoDetectPrimaryKey(): void
    {
        // Skip if table is not defined
        if (empty($this->table)) {
            return;
        }

        // Skip if primary key is already explicitly defined by child class
        if (!empty($this->primaryKey) && $this->primaryKey !== 'id') {
            return;
        }

        try {
            // Access database through parent model's connection
            if (!$this->db) {
                return;
            }

            $fields = $this->db->getFieldData($this->table);

            // Find the primary key from field data
            foreach ($fields as $field) {
                if (isset($field->primary_key) && $field->primary_key == 1) {
                    $this->primaryKey = $field->name;
                    log_message('debug', 'Auto-detected primary key for table "' . $this->table . '": ' . $this->primaryKey);
                    break;
                }
            }
        } catch (\Exception $e) {
            // Log error but don't break the application
            log_message('debug', 'Auto-detect primary key failed for table "' . $this->table . '": ' . $e->getMessage());
        }
    }

    /**
     * Check remember-me cookie and authenticate user
     * 
     * @return bool
     */
    public function checkRememberme(): bool
    {
        if ($this->session->get('logged_in')) {
            return true;
        }

        helper(['cookie']);
        $cookie_login = get_cookie('remember');

        if ($cookie_login) {
            [$selector, $cookie_token] = explode(':', $cookie_login);

            $data = $this->builder('user_token')
                        ->where('selector', $selector)
                        ->get()
                        ->getRowArray();

            if ($data && $this->auth->validateToken($cookie_token, $data['token'])) {
                if ($data['expires'] > date('Y-m-d H:i:s')) {
                    $user_detail = $this->getUserById($data['id_user']);
                    $this->session->set('user', $user_detail);
                    $this->session->set('logged_in', true);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get user by ID with roles and default module
     * 
     * @param int|null $id_user User ID
     * @param bool $array Return as array
     * @return array|false
     */
    public function getUserById(?int $id_user = null, bool $array = false): array|false
    {
        if (!$id_user) {
            if (!$this->user) {
                return false;
            }
            $id_user = $this->user['id_user'];
        }

        $user = $this->builder('user')
                    ->where('id_user', $id_user)
                    ->get()
                    ->getRowArray();

        if (!$user) {
            return false;
        }

        // Get user roles
        $user['role'] = [];
        $result = $this->builder('user_role')
                       ->select('user_role.*, role.*, module.*')
                       ->join('role', 'role.id_role = user_role.id_role', 'left')
                       ->join('module', 'module.id_module = role.id_module', 'left')
                       ->where('user_role.id_user', $id_user)
                       ->orderBy('role.nama_role', 'ASC')
                       ->get()
                       ->getResultArray();

        if ($result) {
            foreach ($result as $val) {
                $user['role'][$val['id_role']] = $val;
            }
        }

        // Get default module
        $user['default_module'] = $this->builder('module')
                                       ->where('id_module', $user['default_page_id_module'])
                                       ->get()
                                       ->getRowArray();

        return $user;
    }

    /**
     * Get user layout settings
     * 
     * @return object
     */
    public function getUserSetting(): object
    {
        $result = $this->builder('setting_user')
                       ->where('id_user', $this->session->get('user')['id_user'])
                       ->where('type', 'layout')
                       ->get()
                       ->getRow();

        if (!$result) {
            $query = $this->builder('setting')
                          ->where('type', 'layout')
                          ->get()
                          ->getResultArray();

            $data = [];
            foreach ($query as $val) {
                $data[$val['param']] = $val['value'];
            }

            $result = new \stdClass();
            $result->param = json_encode($data);
        }

        return $result;
    }

    /**
     * Get application layout settings
     * 
     * @return array
     */
    public function getAppLayoutSetting(): array
    {
        return $this->builder('setting')
                    ->where('type', 'layout')
                    ->get()
                    ->getResultArray();
    }

    /**
     * Get default user module
     * 
     * @return object|null
     */
    public function getDefaultUserModule(): ?object
    {
        $user = $this->session->get('user');
        $where_role = !empty($user['role']) ? join(',', array_keys($user['role'])) : 'null';

        return $this->builder('role')
                    ->select('role.*, module.*')
                    ->join('module', 'module.id_module = role.id_module', 'left')
                    ->where('role.id_role IN (' . $where_role . ')', null, false)
                    ->get()
                    ->getRow();
    }

    /**
     * Get module by name
     * 
     * @param string $nama_module Module name
     * @return array|null
     */
    public function getModule(string $nama_module): ?array
    {
        return $this->builder('module')
                    ->select('module.*, module_status.*')
                    ->join('module_status', 'module_status.id_module_status = module.id_module_status', 'left')
                    ->where('module.nama_module', $nama_module)
                    ->get()
                    ->getRowArray();
    }

    /**
     * Get menu structure for user
     * 
     * @param string $current_module Current module name
     * @return array
     */
    public function getMenu(string $current_module = ''): array
    {
        $user = $this->session->get('user');
        $where_role = !empty($user['role']) ? join(',', array_keys($user['role'])) : 'null';

        // Get menu items
        $query_result = $this->builder('menu')
                             ->select('menu.*, menu_role.*, module.*, menu_kategori.*')
                             ->join('menu_role', 'menu_role.id_menu = menu.id_menu', 'left')
                             ->join('module', 'module.id_module = menu.id_module', 'left')
                             ->join('menu_kategori', 'menu_kategori.id_menu_kategori = menu.id_menu_kategori', 'left')
                             ->where('menu_kategori.aktif', 'Y')
                             ->where('menu_role.id_role IN (' . $where_role . ')', null, false)
                             ->orderBy('menu_kategori.urut', 'ASC')
                             ->orderBy('menu.urut', 'ASC')
                             ->get()
                             ->getResultArray();

        $current_id = '';
        $menu = [];
        foreach ($query_result as $val) {
            $menu[$val['id_menu']] = $val;
            $menu[$val['id_menu']]['highlight'] = 0;
            $menu[$val['id_menu']]['depth'] = 0;

            if ($current_module == $val['nama_module']) {
                $current_id = $val['id_menu'];
                $menu[$val['id_menu']]['highlight'] = 1;
            }
        }

        if ($current_id) {
            $this->menuCurrent($menu, $current_id);
        }

        $menu_kategori = [];
        foreach ($menu as $id_menu => $val) {
            if (!$id_menu) {
                continue;
            }
            $menu_kategori[$val['id_menu_kategori']][$val['id_menu']] = $val;
        }

        // Get categories
        $query_result = $this->builder('menu_kategori')
                             ->where('aktif', 'Y')
                             ->orderBy('urut', 'ASC')
                             ->get()
                             ->getResultArray();

        $result = [];
        foreach ($query_result as $val) {
            if (key_exists($val['id_menu_kategori'], $menu_kategori)) {
                $result[$val['id_menu_kategori']] = [
                    'kategori' => $val,
                    'menu' => $menu_kategori[$val['id_menu_kategori']]
                ];
            }
        }

        return $result;
    }

    /**
     * Highlight current menu and parent menus
     * 
     * @param array $result Menu array
     * @param int $current_id Current menu ID
     */
    private function menuCurrent(array &$result, int $current_id): void
    {
        $parent = $result[$current_id]['id_parent'];

        $result[$parent]['highlight'] = 1;

        if (isset($result[$parent]['id_parent']) && $result[$parent]['id_parent']) {
            $this->menuCurrent($result, $parent);
        }
    }

    /**
     * Get module permissions
     * 
     * @param int $id_module Module ID
     * @return array
     */
    public function getModulePermission(int $id_module): array
    {
        return $this->builder('module_permission')
                    ->select('module_permission.*, role_module_permission.*')
                    ->join('role_module_permission', 'role_module_permission.id_module_permission = module_permission.id_module_permission', 'left')
                    ->where('module_permission.id_module', $id_module)
                    ->get()
                    ->getResultArray();
    }

    /**
     * Get all module permissions for user
     * 
     * @param int $id_user User ID
     * @return array
     */
    public function getAllModulePermission(int $id_user): array
    {
        return $this->builder('role_module_permission')
                    ->select('role_module_permission.*, module_permission.*, module.*')
                    ->join('module_permission', 'module_permission.id_module_permission = role_module_permission.id_module_permission', 'left')
                    ->join('module', 'module.id_module = module_permission.id_module', 'left')
                    ->join('user_role', 'user_role.id_role = role_module_permission.id_role', 'left')
                    ->where('user_role.id_user', $id_user)
                    ->get()
                    ->getResultArray();
    }

    /**
     * Validate CSRF form token
     * 
     * @param string|null $session_name Session name
     * @param string $post_name Post field name
     * @return bool
     */
    public function validateFormToken(?string $session_name = null, string $post_name = 'form_token'): bool
    {
        $form_token = explode(':', $this->request->getPost($post_name));

        if (count($form_token) < 2) {
            return false;
        }

        $form_selector = $form_token[0];
        $sess_token = $this->session->get('token');

        if ($session_name) {
            $sess_token = $sess_token[$session_name] ?? null;
        }

        if (!$sess_token || !is_array($sess_token) || !key_exists($form_selector, $sess_token)) {
            return false;
        }

        try {
            return $this->auth->validateToken($sess_token[$form_selector], $form_token[1]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get data by ID from any table
     * 
     * @param string $table Table name
     * @param string $column Column name
     * @param mixed $id ID value
     * @return array
     */
    public function getDataById(string $table, string $column, mixed $id): array
    {
        return $this->builder($table)
                    ->where($column, $id)
                    ->get()
                    ->getResultArray();
    }

    /**
     * Check user by username
     * 
     * @param string $username Username
     * @return array|false
     */
    public function checkUser(string $username): array|false
    {
        $user = $this->builder('user')
                     ->where('username', $username)
                     ->get()
                     ->getRowArray();

        if (!$user) {
            return false;
        }

        return $this->getUserById($user['id_user']);
    }

    /**
     * Get application settings
     * 
     * @return array
     */
    public function getSettingAplikasi(): array
    {
        $query = $this->builder('setting')
                      ->where('type', 'app')
                      ->get()
                      ->getResultArray();

        $settingAplikasi = [];
        foreach ($query as $val) {
            $settingAplikasi[$val['param']] = $val['value'];
        }

        return $settingAplikasi;
    }

    /**
     * Get registration settings
     * 
     * @return array
     */
    public function getSettingRegistrasi(): array
    {
        $query = $this->builder('setting')
                      ->where('type', 'register')
                      ->get()
                      ->getResultArray();

        $setting_register = [];
        foreach ($query as $val) {
            $setting_register[$val['param']] = $val['value'];
        }

        return $setting_register;
    }
}
