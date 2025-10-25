<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AgentModel;
use App\Models\Builtin\UserModel;
use App\Models\UserRoleAgentModel;
use App\Models\WilayahPropinsiModel;
use App\Models\WilayahKabupatenModel;
use App\Models\WilayahKecamatanModel;
use App\Models\WilayahKelurahanModel;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-24
 * Github: github.com/mikhaelfelian
 * Description: Controller for managing agent master data with CRUD operations and Excel/CSV import
 * This file represents the Controller for Agent.
 */
class Agent extends BaseController
{
    protected $model;
    protected $userModel;
    protected $userRoleAgentModel;
    protected $wilayahPropinsiModel;
    protected $wilayahKabupatenModel;
    protected $wilayahKecamatanModel;
    protected $wilayahKelurahanModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new AgentModel();
        $this->userModel = new UserModel();
        $this->userRoleAgentModel = new UserRoleAgentModel();
        $this->wilayahPropinsiModel = new WilayahPropinsiModel();
        $this->wilayahKabupatenModel = new WilayahKabupatenModel();
        $this->wilayahKecamatanModel = new WilayahKecamatanModel();
        $this->wilayahKelurahanModel = new WilayahKelurahanModel();
    }

    public function index()
    {
        // Check read permissions
        if (!$this->hasPermissionPrefix('read')) {
            return $this->view('errors/403', [
                'title' => 'Access Denied',
                'message' => 'You do not have permission to access this module.'
            ]);
        }

        $this->data['title'] = 'Data Agen';
        $this->data['current_module'] = $this->currentModule;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        // Pass permission data to view
        $this->data['canCreate'] = $this->hasPermissionPrefix('create');
        $this->data['canUpdate'] = $this->hasPermissionPrefix('update');
        $this->data['canDelete'] = $this->hasPermissionPrefix('delete');

        // Cek AJAX/modal
        $isAjax = $this->request->isAJAX() 
                  || $this->request->getHeader('X-Requested-With') !== null;
        
        if ($isAjax) {
            return $this->view('agent-result', $this->data);
        } else {
            return $this->view('agent-result', $this->data);
        }
    }

    public function add()
    {
        // Check create permissions
        if (!$this->hasPermissionPrefix('create')) {
            return $this->view('errors/403', [
                'title' => 'Access Denied',
                'message' => 'You do not have permission to add agent data.'
            ]);
        }

        $this->data['title'] = 'Tambah Agen';
        $this->data['current_module'] = $this->currentModule;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        // Pass permission data to view
        $this->data['canCreate'] = $this->hasPermissionPrefix('create');
        $this->data['canUpdate'] = $this->hasPermissionPrefix('update');
        $this->data['canDelete'] = $this->hasPermissionPrefix('delete');

        // Load province options
        $provinces = $this->wilayahPropinsiModel->findAll();
        $provinceOptions = ['' => 'Pilih Provinsi'];
        foreach ($provinces as $province) {
            $provinceOptions[$province->id_wilayah_propinsi] = $province->nama_propinsi;
        }
        $this->data['provinceOptions'] = $provinceOptions;

        // Load user options
        $users = $this->userModel->findAll();
        $userOptions = ['' => 'Select User'];
        foreach ($users as $user) {
            $userOptions[$user->id_user] = $user->nama . ' (' . $user->email . ')';
        }
        $this->data['userOptions'] = $userOptions;

        // Cek AJAX/modal
        $isAjax = $this->request->isAJAX() 
                  || $this->request->getHeader('X-Requested-With') !== null;
        
        if ($isAjax) {
            return view('themes/modern/agent-form', $this->data);
        } else {
            return $this->view('agent-form', $this->data);
        }
    }

    public function detail()
    {
        // Check read permissions
        if (!$this->hasPermissionPrefix('read')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'You do not have permission to view agent details.'
                ]);
            }
            return $this->view('errors/403', [
                'title' => 'Access Denied',
                'message' => 'You do not have permission to view agent details.'
            ]);
        }

        $id = $this->request->getGet('id');

        // Cek ID
        if (!$id) {
            $message = 'ID tidak ditemukan';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('agent')->with('message', $message);
        }

        // Ambil data agent
        $agent = $this->model->find($id);
        if (!$agent) {
            $message = 'Data tidak ditemukan';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('agent')->with('message', $message);
        }

        $this->data['title'] = 'Detail Agen';
        $this->data['current_module'] = $this->currentModule;
        $this->data['agent'] = $agent;
        $this->data['id'] = $id;

        // Load location names with error handling
        $this->data['provinceName'] = '-';
        $this->data['regencyName'] = '-';
        $this->data['districtName'] = '-';
        $this->data['villageName'] = '-';

        try {
            if (!empty($agent->province_id)) {
                $province = $this->wilayahPropinsiModel->find($agent->province_id);
                $this->data['provinceName'] = $province ? $province->nama_propinsi : '-';
            }

            if (!empty($agent->regency_id)) {
                $regency = $this->wilayahKabupatenModel->find($agent->regency_id);
                $this->data['regencyName'] = $regency ? $regency->nama_kabupaten : '-';
            }

            if (!empty($agent->district_id)) {
                $district = $this->wilayahKecamatanModel->find($agent->district_id);
                $this->data['districtName'] = $district ? $district->nama_kecamatan : '-';
            }

            if (!empty($agent->village_id)) {
                $village = $this->wilayahKelurahanModel->find($agent->village_id);
                $this->data['villageName'] = $village ? $village->nama_kelurahan : '-';
            }
        } catch (\Exception $e) {
            // If location models fail, just use default values
            log_message('error', 'Location model error in agent detail: ' . $e->getMessage());
        }

        // Cek AJAX/modal
        $isAjax = $this->request->isAJAX() 
                  || $this->request->getHeader('X-Requested-With') !== null;
        
        if ($isAjax) {
            return view('themes/modern/agent-detail', $this->data);
        } else {
            return $this->view('agent-detail', $this->data);
        }
    }

    public function edit()
    {
        // Check update permissions
        if (!$this->hasPermissionPrefix('update')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'You do not have permission to edit agent data.'
                ]);
            }
            return $this->view('errors/403', [
                'title' => 'Access Denied',
                'message' => 'You do not have permission to edit agent data.'
            ]);
        }

        $id = $this->request->getGet('id');

        // Cek ID
        if (!$id) {
            $message = 'ID tidak ditemukan';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('agent')->with('message', $message);
        }

        // Ambil data agent
        $agent = $this->model->find($id);
        if (!$agent) {
            $message = 'Data tidak ditemukan';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('agent')->with('message', $message);
        }

        $this->data['title'] = 'Edit Agen';
        $this->data['current_module'] = $this->currentModule;
        $this->data['agent'] = $agent;
        $this->data['id'] = $id;
        
        // Pass permission data to view
        $this->data['canCreate'] = $this->hasPermissionPrefix('create');
        $this->data['canUpdate'] = $this->hasPermissionPrefix('update');
        $this->data['canDelete'] = $this->hasPermissionPrefix('delete');

        // Load province options with error handling
        try {
            $provinces = $this->wilayahPropinsiModel->findAll();
            $provinceOptions = ['' => 'Pilih Provinsi'];
            foreach ($provinces as $province) {
                $provinceOptions[$province->id_wilayah_propinsi] = $province->nama_propinsi;
            }
            $this->data['provinceOptions'] = $provinceOptions;
        } catch (\Exception $e) {
            log_message('error', 'Error loading provinces in agent edit: ' . $e->getMessage());
            $this->data['provinceOptions'] = ['' => 'Pilih Provinsi'];
        }

        // Load regency options if province is selected
        if (!empty($agent->province_id)) {
            try {
                $regencies = $this->wilayahKabupatenModel->where('id_wilayah_propinsi', $agent->province_id)->findAll();
                $regencyOptions = ['' => 'Pilih Kota/Kabupaten'];
                foreach ($regencies as $regency) {
                    $regencyOptions[$regency->id_wilayah_kabupaten] = $regency->nama_kabupaten;
                }
                $this->data['regencyOptions'] = $regencyOptions;
            } catch (\Exception $e) {
                log_message('error', 'Error loading regencies in agent edit: ' . $e->getMessage());
                $this->data['regencyOptions'] = ['' => 'Pilih Kota/Kabupaten'];
            }
        }

        // Load district options if regency is selected
        if (!empty($agent->regency_id)) {
            try {
                $districts = $this->wilayahKecamatanModel->where('id_wilayah_kabupaten', $agent->regency_id)->findAll();
                $districtOptions = ['' => 'Pilih Kecamatan'];
                foreach ($districts as $district) {
                    $districtOptions[$district->id_wilayah_kecamatan] = $district->nama_kecamatan;
                }
                $this->data['districtOptions'] = $districtOptions;
            } catch (\Exception $e) {
                log_message('error', 'Error loading districts in agent edit: ' . $e->getMessage());
                $this->data['districtOptions'] = ['' => 'Pilih Kecamatan'];
            }
        }

        // Load village options if district is selected
        if (!empty($agent->district_id)) {
            try {
                $villages = $this->wilayahKelurahanModel->where('id_wilayah_kecamatan', $agent->district_id)->findAll();
                $villageOptions = ['' => 'Pilih Kelurahan'];
                foreach ($villages as $village) {
                    $villageOptions[$village->id_wilayah_kelurahan] = $village->nama_kelurahan;
                }
                $this->data['villageOptions'] = $villageOptions;
            } catch (\Exception $e) {
                log_message('error', 'Error loading villages in agent edit: ' . $e->getMessage());
                $this->data['villageOptions'] = ['' => 'Pilih Kelurahan'];
            }
        }

        // Load user options with error handling
        try {
            $users = $this->userModel->findAll();
            $userOptions = ['' => 'Select User'];
            foreach ($users as $user) {
                $userOptions[$user->id_user] = $user->nama . ' (' . $user->email . ')';
            }
            $this->data['userOptions'] = $userOptions;
        } catch (\Exception $e) {
            log_message('error', 'Error loading users in agent edit: ' . $e->getMessage());
            $this->data['userOptions'] = ['' => 'Select User'];
        }

        // Load existing user-role data for this agent with error handling
        try {
            $userRole = $this->userRoleAgentModel->where('agent_id', $id)->first();
            if ($userRole) {
                $this->data['agent']->user_id = $userRole->user_id;
                $this->data['agent']->user_role = $userRole->role;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error loading user role in agent edit: ' . $e->getMessage());
        }

        // Cek AJAX/modal
        $isAjax = $this->request->isAJAX() 
                  || $this->request->getHeader('X-Requested-With') !== null;
        
        if ($isAjax) {
            return view('themes/modern/agent-form', $this->data);
        } else {
            return $this->view('agent-form', $this->data);
        }
    }

    public function store()
    {
        // Check create/update permissions
        $id = $this->request->getPost('id');
        $isEdit = !empty($id);
        
        if ($isEdit) {
            // Check update permissions for edit
            if (!$this->hasPermissionPrefix('update')) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'You do not have permission to update agent data.'
                    ]);
                }
                return redirect()->to('agent')->with('message', 'You do not have permission to update agent data.');
            }
        } else {
            // Check create permissions for new record
            if (!$this->hasPermissionPrefix('create')) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'You do not have permission to create agent data.'
                    ]);
                }
                return redirect()->to('agent')->with('message', 'You do not have permission to create agent data.');
            }
        }

        // Validasi
        if (!$this->validate([
            'name' => 'required|max_length[255]',
            'email' => 'permit_empty|valid_email|max_length[255]',
            'phone' => 'permit_empty|max_length[20]',
            'country' => 'required|max_length[100]'
        ])) {
            $message = 'Data tidak valid: ' . implode(', ', $this->validator->getErrors());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->back()->withInput()->with('message', $message);
        }

        // Custom validation for code uniqueness
        $code = $this->request->getPost('code');
        if (!empty($code)) {
            $existingAgent = $this->model->where('code', $code);
            if ($isEdit) {
                $existingAgent->where('id !=', $id);
            }
            $existingAgent = $existingAgent->first();
            
            if ($existingAgent) {
                $message = 'Kode agen sudah digunakan';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->withInput()->with('message', $message);
            }
        }

        // Prepare data
        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
            'province_id' => $this->request->getPost('province_id'),
            'regency_id' => $this->request->getPost('regency_id'),
            'district_id' => $this->request->getPost('district_id'),
            'village_id' => $this->request->getPost('village_id'),
            'postal_code' => $this->request->getPost('postal_code'),
            'country' => $this->request->getPost('country'),
            'tax_number' => $this->request->getPost('tax_number'),
            'credit_limit' => $this->request->getPost('credit_limit'),
            'payment_terms' => $this->request->getPost('payment_terms'),
            'is_active' => $this->request->getPost('is_active') ? '1' : '0'
        ];

        // Add code for edit mode (preserve existing code)
        if ($isEdit) {
            $data['code'] = $this->request->getPost('code');
        } else {
            // Generate code for new records
            $data['code'] = $this->model->generateCode();
        }

        // Save data
        if ($isEdit) {
            $result = $this->model->update($id, $data);
        } else {
            $result = $this->model->insert($data);
        }

        if ($result) {
            // Get the agent ID (for new records, get the inserted ID)
            $agentId = $isEdit ? $id : $this->model->getInsertID();
            
            // Handle user-role assignment
            $userId = $this->request->getPost('user_id');
            $userRole = $this->request->getPost('user_role');
            
            if (!empty($userId) && !empty($userRole)) {
                // Remove existing user-role relationships for this agent
                $this->userRoleAgentModel->where('agent_id', $agentId)->delete();
                
                // Create new user-role relationship
                $this->userRoleAgentModel->insert([
                    'user_id' => $userId,
                    'agent_id' => $agentId,
                    'role' => $userRole
                ]);
            }
            
            $message = $isEdit ? 'Data berhasil diupdate' : 'Data berhasil disimpan';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => $message
                ]);
            }
            return redirect()->to('agent')->with('message', $message);
        } else {
            $message = 'Gagal menyimpan data: ' . implode(', ', $this->model->errors());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->back()->withInput()->with('message', $message);
        }
    }

    public function delete()
    {
        // Check delete permissions
        if (!$this->hasPermissionPrefix('delete')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You do not have permission to delete agent data.'
            ]);
        }

        $id = $this->request->getPost('id');

        if (!$id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'ID tidak ditemukan'
            ]);
        }

        $result = $this->model->delete($id);

        if ($result) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Data berhasil dihapus'
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menghapus data'
            ]);
        }
    }

    public function toggleStatus()
    {
        $id = $this->request->getPost('id');

        if (!$id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'ID tidak ditemukan'
            ]);
        }

        $agent = $this->model->find($id);
        if (!$agent) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Data tidak ditemukan'
            ]);
        }

        $newStatus = $agent->is_active == '1' ? '0' : '1';
        $result = $this->model->update($id, ['is_active' => $newStatus]);

        if ($result) {
            $statusText = $newStatus == '1' ? 'aktif' : 'tidak aktif';
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Status berhasil diubah menjadi ' . $statusText
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal mengubah status'
            ]);
        }
    }

    public function getAgentDT()
    {
        log_message('info', 'getAgentDT method called');
        try {
            $search = $this->request->getPost('search')['value'] ?? '';
            $start = $this->request->getPost('start') ?? 0;
            $length = $this->request->getPost('length') ?? 10;
            $order = $this->request->getPost('order')[0] ?? [];
            $columns = $this->request->getPost('columns') ?? [];

            // Safe column ordering with proper error handling
            $column_order = 'name'; // Default column
            $order_dir = 'ASC'; // Default direction
            
            if (!empty($order) && isset($order['column']) && isset($columns[$order['column']])) {
                $column_order = $columns[$order['column']]['data'] ?? 'name';
                $order_dir = $order['dir'] ?? 'ASC';
            }

            // Debug information
            $debugInfo = [];

            // Build base query
            $query = $this->model->select('agent.*');
            
            // Apply permission-based filtering using existing RBAC system
            if ($this->hasPermissionPrefix('read')) {
                // Check if user has read_own but not read_all (same logic as checkRoleAction)
                if (in_array('read_own', $this->userPermission) && !in_array('read_all', $this->userPermission)) {
                    // User can only see agents they are assigned to via user_role_agent table
                    $userId = $this->user['id_user'];
                    $debugInfo['applyingFilter'] = 'read_own filter for user_id: ' . $userId;
                    
                    // Join with user_role_agent to filter by user's assigned agents
                    $query->join('user_role_agent', 'user_role_agent.agent_id = agent.id')
                          ->where('user_role_agent.user_id', $userId);
                } else {
                    $debugInfo['applyingFilter'] = 'No read_own filter - user has read_all or no read permissions';
                }
                // If user has read_all, no filtering is applied (shows all agents)
            } else {
                $debugInfo['applyingFilter'] = 'User has no read permissions';
            }

            // Get total records (before search filter) - clone the query
            $totalQuery = clone $query;
            $recordsTotal = $totalQuery->countAllResults(false);

            // Apply search filter
            if (!empty($search)) {
                $query->groupStart()
                      ->like('name', $search)
                      ->orLike('code', $search)
                      ->orLike('address', $search)
                      ->groupEnd();
            }
            
            // Get filtered records count - clone the query again
            $filteredQuery = clone $query;
            $recordsFiltered = $filteredQuery->countAllResults(false);

            // Get data with pagination - create a fresh query for data retrieval
            $dataQuery = $this->model->select('agent.*');
            
            // Apply the same permission filtering as the main query
            if ($this->hasPermissionPrefix('read')) {
                if (in_array('read_own', $this->userPermission) && !in_array('read_all', $this->userPermission)) {
                    $userId = $this->user['id_user'];
                    $dataQuery->join('user_role_agent', 'user_role_agent.agent_id = agent.id')
                              ->where('user_role_agent.user_id', $userId);
                }
            }
            
            // Apply search filter to data query
            if (!empty($search)) {
                $dataQuery->groupStart()
                          ->like('name', $search)
                          ->orLike('code', $search)
                          ->orLike('address', $search)
                          ->groupEnd();
            }
            
            // Apply ordering and pagination
            $agents = $dataQuery->orderBy($column_order, $order_dir)
                                ->findAll($length, $start);
            
            // Debug: Query results
            $debugInfo['queryResults'] = count($agents) . ' agents returned';

            $data = [];
            $no = ($start ?? 0) + 1;
            
            foreach ($agents as $agent) {
                // Build action buttons based on existing RBAC system
                $action = '';

                // Detail button - always show if user has read permissions
                if ($this->hasPermissionPrefix('read')) {
                    $action .= '<button class="btn btn-sm btn-info btn-detail rounded-0" data-id="' . $agent->id . '" title="Detail">
                        <i class="fa fa-eye"></i>
                    </button>';
                }

                // Edit button - check write permission using existing system
                if ($this->hasPermissionPrefix('update')) {
                    $action .= '<button class="btn btn-sm btn-primary btn-edit rounded-0" data-id="' . $agent->id . '" title="Edit">
                        <i class="fa fa-edit"></i>
                    </button>';
                }

                // Delete button - check delete permission using existing system
                if ($this->hasPermissionPrefix('delete')) {
                    $action .= '<button class="btn btn-sm btn-danger btn-delete rounded-0" data-id="' . $agent->id . '" data-name="' . $agent->name . '" title="Hapus">
                        <i class="fa fa-trash"></i>
                    </button>';
                }

                $data[] = [
                    'ignore_search_urut' => $no++,
                    'code' => $agent->code,
                    'name' => $agent->name,
                    'address' => $agent->address ?? '-',
                    'ignore_search_action' => $action
                ];
            }

            $output = [
                'draw' => intval($this->request->getPost('draw')),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ];

            return $this->response->setJSON($output);
        } catch (\Exception $e) {
            log_message('error', 'DataTables Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => intval($this->request->getPost('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function upload()
    {
        $this->data['title'] = 'Import Data Agen';
        $this->data['current_module'] = $this->currentModule;
        $this->data['msg'] = $this->session->getFlashdata('message');

        if ($this->request->getMethod() === 'post') {
            $file = $this->request->getFile('file');
            
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $extension = $file->getClientExtension();
                
                if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                    $this->data['msg'] = show_alert('File harus berupa Excel (.xlsx, .xls) atau CSV', 'error');
                } else {
                    try {
                        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(ucfirst($extension));
                        $spreadsheet = $reader->load($file->getTempName());
                        $worksheet = $spreadsheet->getActiveSheet();
                        $rows = $worksheet->toArray();
                        
                        $successCount = 0;
                        $errorCount = 0;
                        $errors = [];
                        
                        // Skip header row
                        array_shift($rows);
                        
                        foreach ($rows as $index => $row) {
                            if (empty($row[0])) continue; // Skip empty rows
                            
                            try {
                                $data = [
                                    'code' => $this->model->generateCode(),
                                    'name' => $row[0] ?? '',
                                    'email' => $row[1] ?? '',
                                    'phone' => $row[2] ?? '',
                                    'address' => $row[3] ?? '',
                                    'country' => $row[4] ?? 'Indonesia',
                                    'tax_number' => $row[5] ?? '',
                                    'credit_limit' => $row[6] ?? 0,
                                    'payment_terms' => $row[7] ?? 0,
                                    'is_active' => '1'
                                ];
                                
                                if ($this->model->save($data)) {
                                    $successCount++;
                                } else {
                                    $errorCount++;
                                    $errors[] = 'Baris ' . ($index + 2) . ': ' . implode(', ', $this->model->errors());
                                }
                            } catch (\Exception $e) {
                                $errorCount++;
                                $errors[] = 'Baris ' . ($index + 2) . ': ' . $e->getMessage();
                            }
                        }
                        
                        $message = "Import selesai. Berhasil: {$successCount}, Gagal: {$errorCount}";
                        if (!empty($errors)) {
                            $message .= '<br>Error: ' . implode('<br>', array_slice($errors, 0, 10));
                        }
                        
                        $this->data['msg'] = show_alert($message, $errorCount > 0 ? 'warning' : 'success');
                        
                    } catch (\Exception $e) {
                        $this->data['msg'] = show_alert('Error membaca file: ' . $e->getMessage(), 'error');
                    }
                }
            } else {
                $this->data['msg'] = show_alert('File tidak valid', 'error');
            }
        }

        return $this->view('agent-upload-form', $this->data);
    }

    /**
     * Get regencies by province ID (AJAX)
     */
    public function getRegencies()
    {
        $provinceId = $this->request->getPost('province_id');
        
        if (!$provinceId) {
            return $this->response->setJSON([]);
        }

        $regencies = $this->wilayahKabupatenModel->where('id_wilayah_propinsi', $provinceId)->findAll();
        $options = [];
        
        foreach ($regencies as $regency) {
            $options[$regency->id_wilayah_kabupaten] = $regency->nama_kabupaten;
        }

        return $this->response->setJSON($options);
    }

    /**
     * Get districts by regency ID (AJAX)
     */
    public function getDistricts()
    {
        $regencyId = $this->request->getPost('regency_id');
        
        if (!$regencyId) {
            return $this->response->setJSON([]);
        }

        $districts = $this->wilayahKecamatanModel->where('id_wilayah_kabupaten', $regencyId)->findAll();
        $options = [];
        
        foreach ($districts as $district) {
            $options[$district->id_wilayah_kecamatan] = $district->nama_kecamatan;
        }

        return $this->response->setJSON($options);
    }

    /**
     * Get villages by district ID (AJAX)
     */
    public function getVillages()
    {
        $districtId = $this->request->getPost('district_id');
        
        if (!$districtId) {
            return $this->response->setJSON([]);
        }

        $villages = $this->wilayahKelurahanModel->where('id_wilayah_kecamatan', $districtId)->findAll();
        $options = [];
        
        foreach ($villages as $village) {
            $options[$village->id_wilayah_kelurahan] = $village->nama_kelurahan;
        }

        return $this->response->setJSON($options);
    }
}
