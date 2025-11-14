<?php 
/**
*	App Name	: Admin Template Codeigniter 4
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2020-2023
*/

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\Auth;
use Config\App;
use App\Models\BaseModel;

class BaseController extends Controller
{
	protected $data;
	protected $config;
	protected $session;
	protected $router;
	protected $request;
	protected $isLoggedIn;
	protected $auth;
	protected $user;
	protected $model;
	protected $themeMode;
	
	public $currentModule;
	private $controllerName;
	private $methodName;
	// protected $actionUser;
	protected $moduleURL;
	// protected $moduleRole;
	protected $modulePermission;
	protected $userPermission;
	protected $cache;
	
	/*
	Alur:
	Sistem akan menegcek router.
	Default router adalah login (app\Config\Routes.php) $routes->get('/', 'Login::index');
	Sistem akan mengecek app\Filters\Bootstrap
	Selanjutnya kaan dieksekusi fungsi $this->loginRestricted();
	Kemudian sistem akan mengakses controller login
	Pada controller login dieksekusi fungsi $this->mustNotLoggedIn(); 
	-- Jika user telah login maka method $this->mustNotLoggedIn(); akan mengarahkan user ke halaman default yang di assign pada user tersebut
	
	// Menghindari looping redirect gunakan
	if ($this->isLoggedIn && $this->currentModule['nama_module'] != 'login') {
	
	}
	*/
	public function __construct() 
	{
		date_default_timezone_set('Asia/Jakarta');
		
		$this->config = new App;
		$this->auth = new Auth;
		$this->model = new BaseModel;
		
		// Initialize cache service
		try {
			$this->cache = \Config\Services::cache();
		} catch (\Exception $e) {
			// Fallback: cache will be null, all cache operations will use database
			$this->cache = null;
			log_message('error', 'Cache initialization failed: ' . $e->getMessage());
		}
		
		// Autoload util helpers (app\Helpers\Autoload.php)
		if ($this->config->csrf['enable']) {
			helper('csrf');
			csrf_settoken();
		}
		
		$this->session = \Config\Services::session();
		$this->request = \Config\Services::request();
		
		helper('util');
		$web = $this->session->get('web');

		$nama_module = $web['nama_module'];
		
		// Cache module data (1 hour TTL)
		$module = $this->getCachedData(
			'module_' . $nama_module,
			function() use ($nama_module) {
				return $this->model->getModule($nama_module);
			},
			3600 // 1 hour
		);
		
		if (!$module) {
			$this->data['content'] = 'Module ' . $nama_module . ' tidak ditemukan di database';
			$this->exitError($this->data);
		}
		
		$this->isLoggedIn = $this->session->get('logged_in');
		$this->currentModule = $module;
		$this->moduleURL = $web['module_url'];
		$this->user = $this->session->get('user');
		$this->model->checkRememberme();
		
		$this->data['current_module'] = $this->currentModule;
		$this->data['scripts'] = array($this->config->baseURL . '/public/assets/vendors/jquery/jquery.min.js'
										, $this->config->baseURL . '/public/assets/vendors/flatpickr/flatpickr.js'
										, $this->config->baseURL . '/public/themes/modern/assets/js/site.js?r='.time()
										, $this->config->baseURL . '/public/assets/vendors/bootstrap/js/bootstrap.js'
								);
		$this->data['styles'] = array(
									$this->config->baseURL . '/public/assets/vendors/bootstrap/css/bootstrap.css'
									, $this->config->baseURL . '/public/themes/modern/assets/css/site.css?r='.time()
								);
		$this->data['config'] = $this->config;
		$this->data['request'] = $this->request;
		$this->data['isloggedin'] = $this->isLoggedIn;
		$this->data['session'] = $this->session;
		$this->data['site_title'] = 'Admin Template Codeigniter 4';
		$this->data['site_desc'] = 'Admin Template Codeigniter 4 lengkap dengan berbagai fitur untuk memudahkan pengembangan aplikasi';
		
		// Cache app settings (24 hours TTL)
		$this->data['settingAplikasi'] = $this->getCachedData(
			'app_settings',
			function() {
				return $this->model->getSettingAplikasi();
			},
			86400 // 24 hours
		);
		$this->data['user'] = [];
		$this->data['auth'] = $this->auth;
		$this->data['scripts'] = [];
		$this->data['styles'] = [];
		$this->data['module_url'] = $this->moduleURL;
		
		if (!empty($_COOKIE['jwd_adm_theme'])) {
			$this->themeMode = $this->data['theme_mode'] = $_COOKIE['jwd_adm_theme'];
		} else {
			$this->themeMode = $this->data['theme_mode'] = 'light';
		}
				
		if ($this->isLoggedIn) {
			// Use session for user settings to avoid repeated queries
			if (isset($_SESSION['user']['user_settings']) && !empty($_SESSION['user']['user_settings'])) {
				$user_setting = $_SESSION['user']['user_settings'];
				if ($user_setting) {
					$this->data['app_layout'] = is_string($user_setting) ? json_decode($user_setting, true) : $user_setting;
				}
			} else {
				$user_setting = $this->model->getUserSetting();
				if ($user_setting) {
					$layout_data = json_decode($user_setting->param, true);
					$this->data['app_layout'] = $layout_data;
					// Store in session for next request
					if (!isset($_SESSION['user'])) {
						$_SESSION['user'] = [];
					}
					$_SESSION['user']['user_settings'] = $user_setting->param;
				}
			}
				
		} else {
			// Cache layout settings (24 hours TTL)
			$query = $this->getCachedData(
				'layout_settings',
				function() {
					return $this->model->getAppLayoutSetting();
				},
				86400 // 24 hours
			);
			
			$app_layout = [];
			foreach ($query as $val) {
				$app_layout[$val['param']] = $val['value'];
			}
			$this->data['app_layout'] = $app_layout;
		}
		
		// Login? Yes, No, Restrict
		if ($this->currentModule['login'] == 'Y' && $nama_module != 'login') {
			$this->loginRequired();
		} else if ($this->currentModule['login'] == 'R') {
			$this->loginRestricted();
		}
		
		if ($this->isLoggedIn) 
		{
			$this->data['user'] = $this->user;

			// List action assigned to role
			$this->data['action_user'] = $this->userPermission;
			
			// Cache menu data with role-based key (30 minutes TTL)
			$user_role = $this->user['role'] ?? [];
			$role_ids = !empty($user_role) ? array_keys($user_role) : [];
			$role_hash = !empty($role_ids) ? md5(implode(',', $role_ids)) : 'no_role';
			$menu_cache_key = 'menu_' . $role_hash . '_' . $this->currentModule['nama_module'];
			
			$this->data['menu'] = $this->getCachedData(
				$menu_cache_key,
				function() {
					return $this->model->getMenu($this->currentModule['nama_module']);
				},
				1800 // 30 minutes
			);
			
			$this->data['breadcrumb'] = ['Home' => $this->config->baseURL, $this->currentModule['judul_module'] => $this->moduleURL];
			$this->data['module_role'] = $this->model->getDefaultUserModule();
						
			$this->getModulePermission();
			$this->getListPermission();
			
			// Use session for user permissions to avoid repeated queries
			if (isset($_SESSION['user']['all_permission']) && !empty($_SESSION['user']['all_permission'])) {
				$all_module_permission = $_SESSION['user']['all_permission'];
			} else {
				// Cache all module permissions (1 hour TTL)
				$result = $this->getCachedData(
					'user_permissions_' . $_SESSION['user']['id_user'],
					function() {
						return $this->model->getAllModulePermission($_SESSION['user']['id_user']);
					},
					3600 // 1 hour
				);
				
				$all_module_permission = [];
				if ($result) {
					foreach ($result as $val) {
						$all_module_permission[$val['id_module']][$val['nama_permission']] = $val;
					}
				}
				$_SESSION['user']['all_permission'] = $all_module_permission;
			}
			
			// Check Global Role Action
			$this->checkRoleAction();
			if ($nama_module == 'login') {
				$this->redirectOnLoggedIn();
			}
		}
		
		if ($module['id_module_status'] != 1) {
			$this->printError('Module ' . $module['judul_module'] . ' sedang ' . strtolower($module['nama_status']));
			exit();
		}
	}
	
	private function getModulePermission()
	{
		// Cache module permissions (1 hour TTL)
		$query = $this->getCachedData(
			'module_permission_' . $this->currentModule['id_module'],
			function() {
				return $this->model->getModulePermission($this->currentModule['id_module']);
			},
			3600 // 1 hour
		);
		
		$this->modulePermission = [];
		foreach ($query as $val) {
			$nama_permission = $val['nama_permission'] ?: 'null';
			$this->modulePermission[$val['id_role']][$nama_permission] = $nama_permission;
		}
	}

	private function getListPermission() 
	{ 
		$user_role = $this->session->get('user')['role'];
				
		if ($this->isLoggedIn && $this->currentModule['nama_module'] != 'login') 
		{
			// Use user data from session if available, only query if status check needed
			$current_user = null;
			if (isset($this->user['status'])) {
				// User data already in session, use it
				$current_user = $this->user;
			} else {
				// Fallback: query database if status not in session
				$current_user = $this->model->getUserById($this->user['id_user']);
			}
			
			if ($current_user && isset($current_user['status']) && $current_user['status'] != 'active') {
				$this->data['content'] = 'Status akun Anda ' . ucfirst($current_user['status']);
				$this->exitError($this->data);
			}
			if (!$user_role) {
				$this->printError('User belum memiliki role');
				exit;
			}
			
			if ($this->modulePermission) 
			{
				$error = false;
				if ($this->currentModule['nama_module'] != 'login' ) {
					
					$role_exists = false;
					foreach ($user_role as $id_role => $val) {
						if (key_exists($id_role, $this->modulePermission)) {
							$this->userPermission = $this->modulePermission[$id_role];
							unset($this->userPermission['null']);
							$role_exists = true;
							break;
						}
					}
					
					if ($this->userPermission) {
						$session_user = $this->session->get('user');
						$session_user['permission'] = $this->userPermission;
						$this->session->set('user', $session_user);
					}

					if ($role_exists) 
					{
						if (!$this->userPermission) {
							$error = 'Role Anda tidak memiliki permission pada module ' . $this->currentModule['judul_module'];
						}
						
					} else {
						$error = 'Anda tidak berhak mengakses halaman ini';
					}
					
					if ($error) {
						$this->printError($error);
						exit();
					}
				}
			} else {
				$this->printError('Role untuk module ini belum diatur');
				exit();
			}
		}
	}
	
	private function setCurrentModule($module) {
		$this->currentModule['nama_module'] = $module;
	}
	
	protected function getControllerName() {
		return $this->controllerName;
	}
	
	protected function getMethodName() {
		return $this->methodName;
	}
	
	protected function addStyle($file) {
		$this->data['styles'][] = $file;
	}
	
	protected function addJs($file, $print = false) {
		if ($print) {
			$this->data['scripts'][] = ['print' => true, 'script' => $file];
		} else {
			$this->data['scripts'][] = $file;
		}
	}
	
	protected function exitError($data) {
		echo view('app_error.php', $data);
		exit;
	}
	
	protected function view($file, $data = false, $file_only = false) 
	{
		// Merge with $this->data to ensure all BaseController variables are available
		if ($data === false) {
			$data = $this->data;
		} else {
			// Merge passed data with $this->data (passed data takes precedence)
			$data = array_merge($this->data, $data);
		}
		
		if (is_array($file)) {
			foreach ($file as $file_item) {
				echo view($file_item, $data);
			}
		} else {
			echo view('themes/modern/header.php', $data);
			echo view('themes/modern/' . $file, $data);
			echo view('themes/modern/footer.php');
		}
	}
	
	protected function loginRequired() 
	{
		if (!$this->isLoggedIn) {
			header('Location: ' . $this->config->baseURL . 'login');
			// redirect()->to($this->config->baseURL . 'login');
			exit();
		}
	}
	
	protected function loginRestricted() {
		if ($this->isLoggedIn) {
			if ($this->methodName !== 'logout') {
				header('Location: ' . $this->config->baseURL);
			}
		}
	}
	
	protected function redirectOnLoggedIn() {
		if ($this->isLoggedIn) {
			
			header('Location: ' . $this->config->baseURL . $this->user['default_module']['nama_module']);
			// header('Location: ' . $this->config->baseURL . $this->user['default_module']['nama_module']);
			// redirect($this->router->default_controller);
		}
	}
	
	/* Redirect User setelah login */
	protected function mustNotLoggedIn() {
		if ($this->isLoggedIn) {	
			if ($this->currentModule['nama_module'] == 'login') {
				
				$redirect_url = '';
				if ($this->user['default_page_type'] == 'url') {
					$redirect_url = str_replace('{{BASE_URL}}', $this->config->baseURL, $this->user['default_page_url']);
				} else if ($this->user['default_page_type'] == 'id_module') {
					$redirect_url = $this->config->baseURL . $this->user['default_module']['nama_module'];
				} else {
					$redirect_url = $this->config->baseURL . $this->user['role'][$this->user['default_page_id_role']]['nama_module'];
				}
				// header('Location: ' . $this->config->baseURL . $this->data['module_role']->nama_module);
				header('Location: ' . $redirect_url);
				exit();
			}
		}
	}
	
	protected function mustLoggedIn() {
		if (!$this->isLoggedIn) {
			header('Location: ' . $this->config->baseURL . 'login');
			exit();
		}
	}

	private function checkRoleAction() 
	{

		if ($this->config->checkRoleAction['enable_global']) 
		{
			$method = $this->session->get('web')['method_name'];
			$list_action = ['add' => 'create', 'edit' => 'update'];
			$list_error = ['add' => 'menambah', 'edit' => 'mengubah'];
			
			$error = false;
			if ($method == 'add' || $method=='edit') 
			{
				if (key_exists($method, $list_action)) {
					
					foreach ($this->userPermission as $val) 
					{
						$exp = explode('_', $val);
						$exists = false;
						
						if ($list_action[$method] == trim($exp[0])) {;
							$exists = true;
							break;
						}
						
					}
					if (!$exists) {
						$error = 'Role Anda tidak memiliki permission untuk ' . $list_error[$method] . ' data module ' . $this->currentModule['judul_module'];
					}
				}
			} else if (!empty($_POST['delete'])) {
				foreach ($this->userPermission as $val) {
					$exp = explode('_', $val);
					$exists = false;
					if (trim($exp[0]) == 'delete') {
						$exists = true;
						break;
					}
				}
				
				if (!$exists) {
					$error = 'Role Anda tidak diperkenankan untuk menghapus data';
				}
			}
			
			if ($error) {
				$this->data['msg'] = ['status' => 'error', 'message' => $error];
				$this->view('error.php', $this->data);
				exit;
			}
		}
		
	}
	
	protected function userCan($action) {
		if (!$this->userPermission) {
			return '';
		}
				
		foreach ($this->userPermission as $val) {
			
			$exp = explode('_', $val);
			if (count($exp) == 1) {
				if (trim($exp[0]) == trim($action)) {
					return true;
				}
			} else {
						
				if ($exp[0] == $action) {
					if ($exp[1] == 'all') {
						return 'all';
					} else if ($exp[1] == 'own') {
						return 'own';
					}
				}
			}
		}
		return '';
	}
	
	protected function mustHavePermission($permission) {
		
		if (!in_array($permission, $this->userPermission)) {
			$response = service('response');
			$response->setStatusCode(Response::HTTP_UNAUTHORIZED);
			$response->setJSON(['status' => 'error', 'message' => 'Akses ditolak: Anda tidak memiliki permission ' . $permission]);
			$response->setHeader('Content-type', 'application/json');
			$response->noCache();
			$response->send();
			exit;
		}
	}
	
	protected function hasPermission($action, $exit = false) 
	{
		if (!in_array($action, $this->userPermission)) {
			if ($exit) {
				$this->data['msg'] = ['status' => 'error', 'message' => 'Anda tidak memiliki permission ' . $action];
				$this->view('error.php', $this->data);
				exit;
			}
		}
		return in_array($action, $this->userPermission);
	}
	
	protected function hasPermissionPrefix($action, $return = false) {
		
		$has_permission = false;

		foreach ($this->userPermission as $val) 
		{
			$exp = explode('_', $val);
			$user_action = trim($exp[0]);
			if ($user_action == $action || $user_action == $action . '_all') {
				$has_permission = true;
				break;
			}
		}
		
		if (!$has_permission && $return = false) {
			
			$action_title = ['read' => 'melihat data', 'create' => 'menambah data', 'update' => 'mengubah data', 'delete' => 'menghapus data'];
			$this->currentModule['nama_module'] = 'error';
			$this->data['msg'] = ['status' => 'error', 'message' => 'Role Anda tidak diperkenankan untuk pada ' . $action_title[$action]];
			$this->view('error.php', $this->data);
			exit;
		}
		
		return $has_permission;	
	}
	
	/**
	 * Returns WHERE condition for ownership-based data access
	 * Now returns array format for Query Builder compatibility
	 * 
	 * @param string|null $column Column name to filter by
	 * @return array|null Array with column => value for Query Builder, or null for no restriction
	 */
	public function whereOwn($column = null) 
	{	
		if (!$column)
			$column = $this->config->checkRoleAction['field'];
			
		// If user has read_own permission but not read_all, restrict to their own data
		if (key_exists('read_own', $this->userPermission) && !key_exists('read_all', $this->userPermission)) {
			return [$column => $_SESSION['user']['id_user']];
		}
		
		// No restriction - return null (Query Builder will handle this)
		return null;
	}
	
	protected function printError($message) {
		$this->data['title'] = 'Error...';
		if (is_string($message)) {
			$message = ['status' => 'error', 'message' => $message];
		}
		$this->data['msg'] = $message;
		$this->view('error.php', $this->data);
	}
	
	/* Used for modules when edited data not found */
	protected function errorDataNotFound($addData = null) {
		$data = $this->data;
		$data['title'] = 'Error';
		$data['msg']['status'] = 'error';
		$data['msg']['content'] = 'Data tidak ditemukan';
		
		if ($addData) {
			$data = array_merge($data, $addData);
		}
		$this->view('error-data-notfound.php', $data);
	}
	
	/**
	 * Get cached data with automatic fallback to database
	 * 
	 * @param string $key Cache key
	 * @param callable $callback Function to get data from database if cache miss
	 * @param int $ttl Time to live in seconds (default: 1 hour)
	 * @return mixed Cached data or result from callback
	 */
	protected function getCachedData($key, $callback, $ttl = 3600)
	{
		// If cache is not available, directly call callback
		if ($this->cache === null) {
			return $callback();
		}
		
		try {
			$data = $this->cache->get($key);
			
			if ($data === null) {
				// Cache miss - get from database
				$data = $callback();
				if ($data !== null && $data !== false) {
					$this->cache->save($key, $data, $ttl);
				}
			}
			return $data;
		} catch (\Exception $e) {
			// Fallback to database on cache error
			log_message('error', 'Cache get error for key "' . $key . '": ' . $e->getMessage());
			return $callback();
		}
	}
	
	/**
	 * Clear cache by key
	 * 
	 * @param string $key Cache key to clear
	 * @return bool True if successful, false otherwise
	 */
	protected function clearCacheByKey($key)
	{
		if ($this->cache === null) {
			return false;
		}
		
		try {
			return $this->cache->delete($key);
		} catch (\Exception $e) {
			log_message('error', 'Cache clear error for key "' . $key . '": ' . $e->getMessage());
			return false;
		}
	}
	
	/**
	 * Clear module cache
	 * 
	 * @param string|null $nama_module Module name, if null clears all module caches
	 * @return void
	 */
	protected function clearModuleCache($nama_module = null)
	{
		if ($nama_module !== null) {
			$this->clearCacheByKey('module_' . $nama_module);
		} else {
			// Clear all module caches (pattern matching)
			// Note: CodeIgniter cache doesn't support pattern deletion natively
			// This would require iterating through all possible keys or using cache tags
			// For now, we'll clear specific keys when known
		}
	}
	
	/**
	 * Clear menu cache for all role combinations
	 * 
	 * @return void
	 */
	protected function clearMenuCache()
	{
		// Clear all menu caches
		// Since menu cache keys include role hash, we need to clear all
		// For simplicity, we'll use a cache prefix approach
		// Note: This is a simplified version - in production you might want cache tags
		try {
			if ($this->cache !== null) {
				// Clear common menu cache patterns
				// In a real implementation, you might want to store cache keys in a registry
				// For now, we'll rely on TTL expiration or manual clearing
			}
		} catch (\Exception $e) {
			log_message('error', 'Menu cache clear error: ' . $e->getMessage());
		}
	}
	
	/**
	 * Clear permission cache
	 * 
	 * @param int|null $id_module Module ID, if null clears all permission caches
	 * @return void
	 */
	protected function clearPermissionCache($id_module = null)
	{
		if ($id_module !== null) {
			$this->clearCacheByKey('module_permission_' . $id_module);
		}
	}
	
	/**
	 * Clear settings cache
	 * 
	 * @return void
	 */
	protected function clearSettingsCache()
	{
		$this->clearCacheByKey('app_settings');
		$this->clearCacheByKey('layout_settings');
	}
	
	/**
	 * Clear all user permission caches
	 * Used when permissions are updated globally
	 * 
	 * @return void
	 */
	protected function clearAllUserPermissionCache()
	{
		// Clear session-based permission cache
		if (isset($_SESSION['user']['all_permission'])) {
			unset($_SESSION['user']['all_permission']);
		}
		
		// Note: We can't easily clear all user_permissions_* cache keys without pattern matching
		// The cache will expire naturally, or we rely on session clearing
		// For immediate effect, we clear session which is the primary cache for permissions
	}
	
	/**
	 * Clear all application caches
	 * Use with caution - only for maintenance or emergency
	 * 
	 * @return void
	 */
	protected function clearAllCache()
	{
		if ($this->cache === null) {
			return;
		}
		
		try {
			$this->cache->clean();
		} catch (\Exception $e) {
			log_message('error', 'Clear all cache error: ' . $e->getMessage());
		}
	}
}
