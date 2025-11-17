<?php

/**
 * Utility Controller for System Setup
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-26
 * Github: github.com/mikhaelfelian
 * Description: Utility controller for injecting modules, roles, and permissions into database
 * WITHOUT requiring authentication. Use this for initial setup or system maintenance.
 */

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\Response;

class Util extends Controller
{
    protected $db;
    protected $request;
    protected $moduleModel;
    protected $roleModel;
    protected $permissionModel;
    protected $rolePermissionModel;
    protected $moduleRoleModel;
    protected $moduleStatusModel;
    protected $menuModel;
    protected $userModel;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->request = \Config\Services::request();
        
        // Initialize all models
        $this->moduleModel = new \App\Models\Builtin\ModuleModel();
        $this->roleModel = new \App\Models\Builtin\RoleModel();
        $this->permissionModel = new \App\Models\Builtin\PermissionModel();
        $this->rolePermissionModel = new \App\Models\Builtin\RolePermissionModel();
        $this->moduleRoleModel = new \App\Models\Builtin\ModuleRoleModel();
        $this->moduleStatusModel = new \App\Models\Builtin\ModuleStatusModel();
        $this->menuModel = new \App\Models\Builtin\MenuModel();
        $this->userModel = new \App\Models\Builtin\UserModel();
    }
    
    /**
     * Main index method for utility operations
     * No view rendering - outputs JSON or plain text
     */
    public function index()
    {
        // Get action parameter
        $action = $this->request->getGet('action') ?? 'help';
        
        $response = [
            'status' => 'success',
            'action' => $action,
            'data' => []
        ];
        
        try {
            switch ($action) {
                case 'inject_modules':
                    $response['data'] = $this->injectModules();
                    break;
                    
                case 'inject_roles':
                    $response['data'] = $this->injectRoles();
                    break;
                    
                case 'inject_permissions':
                    $response['data'] = $this->injectPermissions();
                    break;
                    
                case 'inject_role_permissions':
                    $response['data'] = $this->injectRolePermissions();
                    break;
                    
                case 'setup_all':
                    $response['data'] = $this->setupAll();
                    break;
                    
                case 'clear_all':
                    $response['data'] = $this->clearAll();
                    break;
                    
                case 'list_modules':
                    $response['data'] = $this->listModules();
                    break;
                    
                case 'list_roles':
                    $response['data'] = $this->listRoles();
                    break;
                    
                case 'test_product_rules':
                    $response['data'] = $this->testProductRules();
                    break;
                    
                default:
                    $response['status'] = 'info';
                    $response['message'] = 'Available actions: inject_modules, inject_roles, inject_permissions, inject_role_permissions, setup_all, clear_all, list_modules, list_roles, test_product_rules';
                    break;
            }
        } catch (\Exception $e) {
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
            $response['trace'] = $e->getTraceAsString();
        }
        
        // Output JSON response
        return $this->response->setJSON($response);
    }
    
    /**
     * Inject default modules into database
     */
    protected function injectModules()
    {
        $modules = [
            [
                'nama_module'      => 'agent/gudang/stok-masuk',
                'judul_module'     => 'Agent Gudang - Stok Masuk',
                'deskripsi'        => 'Module khusus untuk proses penerimaan stok (SN) gudang agent.',
                'id_module_status' => 1,
                'login'            => 'Y'
            ]
        ];
        
        $results = [];
        foreach ($modules as $module) {
            // Check if module exists
            $exists = $this->moduleModel->builder('module')
                ->where('nama_module', $module['nama_module'])
                ->countAllResults();
            
            if ($exists > 0) {
                $results[] = [
                    'status' => 'exists',
                    'module' => $module['nama_module'],
                    'message' => 'Module already exists'
                ];
            } else {
                // Insert module
                $this->db->table('module')->insert($module);
                $results[] = [
                    'status' => 'created',
                    'module' => $module['nama_module'],
                    'message' => 'Module created successfully',
                    'id' => $this->db->insertID()
                ];
            }
        }
        
        return ['message' => 'Module injection completed', 'results' => $results];
    }
    
    /**
     * Inject default roles into database
     */
    protected function injectRoles()
    {
        $roles = [
            [
                'nama_role' => 'superadmin',
                'judul_role' => 'Super Administrator',
                'keterangan' => 'Full system access',
                'id_module' => 1
            ],
            [
                'nama_role' => 'admin',
                'judul_role' => 'Administrator',
                'keterangan' => 'System administrator',
                'id_module' => 1
            ],
            [
                'nama_role' => 'manager',
                'judul_role' => 'Manager',
                'keterangan' => 'Department manager',
                'id_module' => 1
            ],
            [
                'nama_role' => 'staff',
                'judul_role' => 'Staff',
                'keterangan' => 'Staff member',
                'id_module' => 1
            ]
        ];
        
        $results = [];
        foreach ($roles as $role) {
            // Check if role exists
            $exists = $this->roleModel->builder('role')
                ->where('nama_role', $role['nama_role'])
                ->countAllResults();
            
            if ($exists > 0) {
                $results[] = [
                    'status' => 'exists',
                    'role' => $role['nama_role'],
                    'message' => 'Role already exists'
                ];
            } else {
                // Insert role
                $this->db->table('role')->insert($role);
                $results[] = [
                    'status' => 'created',
                    'role' => $role['nama_role'],
                    'message' => 'Role created successfully',
                    'id' => $this->db->insertID()
                ];
            }
        }
        
        return ['message' => 'Role injection completed', 'results' => $results];
    }
    
    /**
     * Inject default permissions for modules
     */
    protected function injectPermissions()
    {
        // Get all modules
        $modules = $this->moduleModel->builder('module')->get()->getResultArray();
        
        // Define standard permissions
        $permissionTypes = [
            'read',
            'read_all',
            'read_own',
            'create',
            'create_all',
            'create_own',
            'update',
            'update_all',
            'update_own',
            'delete',
            'delete_all',
            'delete_own',
            'export',
            'import'
        ];
        
        $results = [];
        
        foreach ($modules as $module) {
            $moduleId = $module['id_module'];
            
            foreach ($permissionTypes as $permissionType) {
                // Check if permission exists
                $exists = $this->permissionModel->builder('module_permission')
                    ->where('id_module', $moduleId)
                    ->where('nama_permission', $permissionType)
                    ->countAllResults();
                
                if ($exists == 0) {
                    $permissionData = [
                        'id_module' => $moduleId,
                        'nama_permission' => $permissionType,
                        'judul_permission' => ucwords(str_replace('_', ' ', $permissionType)) . ' Data',
                        'keterangan' => 'Hak akses untuk ' . str_replace('_', ' ', $permissionType)
                    ];
                    
                    $this->db->table('module_permission')->insert($permissionData);
                    $results[] = [
                        'status' => 'created',
                        'module' => $module['nama_module'],
                        'permission' => $permissionType,
                        'message' => 'Permission created'
                    ];
                }
            }
        }
        
        return ['message' => 'Permission injection completed', 'results' => $results];
    }
    
    /**
     * Assign permissions to roles
     */
    protected function injectRolePermissions()
    {
        // Get superadmin role
        $superadminRole = $this->roleModel->builder('role')
            ->where('nama_role', 'superadmin')
            ->get()
            ->getRowArray();
        
        if (!$superadminRole) {
            return ['message' => 'Superadmin role not found'];
        }
        
        // Get all module permissions
        $permissions = $this->db->table('module_permission')->get()->getResultArray();
        
        $results = [];
        foreach ($permissions as $permission) {
            // Check if permission is already assigned to superadmin
            $exists = $this->db->table('role_module_permission')
                ->where('id_role', $superadminRole['id_role'])
                ->where('id_module_permission', $permission['id_module_permission'])
                ->countAllResults();
            
            if ($exists == 0) {
                $this->db->table('role_module_permission')->insert([
                    'id_role' => $superadminRole['id_role'],
                    'id_module_permission' => $permission['id_module_permission']
                ]);
                
                $results[] = [
                    'status' => 'created',
                    'permission' => $permission['nama_permission'],
                    'message' => 'Permission assigned to superadmin'
                ];
            }
        }
        
        return ['message' => 'Role permission injection completed', 'results' => $results];
    }
    
    /**
     * Setup all modules, roles, and permissions in one go
     */
    protected function setupAll()
    {
        $results = [];
        
        $results['modules'] = $this->injectModules();
        $results['roles'] = $this->injectRoles();
        $results['permissions'] = $this->injectPermissions();
        $results['role_permissions'] = $this->injectRolePermissions();
        
        return $results;
    }
    
    /**
     * Clear all data (use with caution!)
     */
    protected function clearAll()
    {
        // This is a dangerous operation - use with care
        $tables = [
            'role_module_permission',
            'module_permission',
            'role',
            'module'
        ];
        
        $results = [];
        foreach ($tables as $table) {
            try {
                $count = $this->db->table($table)->countAll();
                $this->db->table($table)->truncate();
                $results[$table] = [
                    'status' => 'cleared',
                    'records_deleted' => $count
                ];
            } catch (\Exception $e) {
                $results[$table] = [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * List all modules
     */
    protected function listModules()
    {
        return $this->moduleModel->builder('module')
            ->select('module.*, module_status.nama_status')
            ->join('module_status', 'module_status.id_module_status = module.id_module_status', 'left')
            ->orderBy('module.judul_module')
            ->get()
            ->getResultArray();
    }
    
    /**
     * List all roles
     */
    protected function listRoles()
    {
        return $this->roleModel->builder('role')
            ->select('role.*, module.judul_module')
            ->join('module', 'module.id_module = role.id_module', 'left')
            ->orderBy('role.nama_role')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Test Product Rules feature - verify column exists and can store/retrieve data
     */
    protected function testProductRules()
    {
        try {
            $results = [];
            
            // Check if product_rules column exists
            $db = \Config\Database::connect();
            $fields = $db->getFieldNames('item');
            $hasColumn = in_array('product_rules', $fields);
            
            $results['column_exists'] = $hasColumn;
            
            if ($hasColumn) {
                // Get a test item
                $testItem = $db->table('item')->limit(1)->get()->getRowArray();
                
                if ($testItem) {
                    $results['test_item_id'] = $testItem['id'];
                    $results['test_item_name'] = $testItem['name'];
                    
                    // Sample product rules data
                    $sampleRules = [
                        'min_order' => '10',
                        'max_order' => '100',
                        'unit' => 'pcs',
                        'backorder' => '1',
                        'notes' => 'Test product rules via Utility Controller'
                    ];
                    
                    // Try to update the test item with sample rules
                    $updateResult = $db->table('item')
                        ->where('id', $testItem['id'])
                        ->update(['product_rules' => json_encode($sampleRules)]);
                    
                    $results['update_result'] = $updateResult ? 'success' : 'failed';
                    
                    // Verify the update worked
                    $updatedItem = $db->table('item')
                        ->where('id', $testItem['id'])
                        ->get()
                        ->getRowArray();
                    
                    $results['retrieved_rules'] = $updatedItem['product_rules'] ?? null;
                    
                    if ($results['retrieved_rules']) {
                        $decodedRules = json_decode($results['retrieved_rules'], true);
                        $results['decoded_rules'] = $decodedRules;
                        $results['validation'] = (
                            isset($decodedRules['min_order']) &&
                            isset($decodedRules['max_order']) &&
                            isset($decodedRules['unit']) &&
                            isset($decodedRules['backorder']) &&
                            isset($decodedRules['notes'])
                        ) ? 'valid' : 'invalid';
                    }
                    
                    $results['message'] = 'Product rules column exists and is working correctly';
                } else {
                    $results['message'] = 'No items found in database to test with';
                }
            } else {
                $results['message'] = 'Product rules column does not exist in item table';
                $results['suggestion'] = 'Run migration: php spark migrate';
            }
            
            return [
                'status' => 'success',
                'message' => 'Product Rules feature test completed',
                'results' => $results
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
}

