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
use App\Models\PlatformModel;
use App\Models\AgentCashbackRuleModel;

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
    protected $platformModel;
    protected $cashbackRuleModel;

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
        $this->platformModel = new PlatformModel();
        $this->cashbackRuleModel = new AgentCashbackRuleModel();
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

        // Default user account data
        $this->data['agentUser'] = null;
        $this->data['existingUserRole'] = '1';
        $this->data['productRule'] = $this->getDefaultProductRuleData();

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
        $this->data['productRule'] = $this->getAgentProductRuleData($id);

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

        // Prepare existing user account data
        $agentUser = null;
        $existingUserRole = '1';
        try {
            $userRole = $this->userRoleAgentModel->where('agent_id', $id)->first();
            if ($userRole) {
                $existingUserRole = $userRole->role;
                $agentUser = $this->userModel->find($userRole->user_id);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error loading user account for agent edit: ' . $e->getMessage());
        }
        $this->data['agentUser'] = $agentUser;
        $this->data['existingUserRole'] = $existingUserRole;

        return $this->view('agent-form', $this->data);
    }

    public function store()
    {
        helper('angka');
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
            [
                'name'     => 'required|max_length[255]',
                'email'    => 'permit_empty|valid_email|max_length[255]',
                'phone'    => 'permit_empty|max_length[20]',
                'country'  => 'required|max_length[100]',
            ]
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
        // Formatted agent data input variables
        $creditLimitEnabled  = $this->request->getPost('enable_credit_limit') ? true : false;
        $creditLimitValueRaw = $this->request->getPost('credit_limit_raw');
        if ($creditLimitValueRaw === null) {
            $creditLimitValueRaw = $this->request->getPost('credit_limit');
        }
        $creditLimitValue    = format_angka_db($creditLimitValueRaw);

        if (!$creditLimitEnabled) {
            $creditLimitValue = 0;
        }

        $productRuleInput       = $this->getProductRuleInputFromRequest();

        $name                   = $this->request->getPost('name');
        $email                  = $this->request->getPost('email');
        $phone                  = $this->request->getPost('phone');
        $address                = $this->request->getPost('address');
        $latitude               = $this->request->getPost('latitude');
        $longitude              = $this->request->getPost('longitude');
        $province_id            = $this->request->getPost('province_id');
        $regency_id             = $this->request->getPost('regency_id');
        $district_id            = $this->request->getPost('district_id');
        $village_id             = $this->request->getPost('village_id');
        $postal_code            = $this->request->getPost('postal_code');
        $country                = $this->request->getPost('country');
        $tax_number             = $this->request->getPost('tax_number');
        $credit_limit           = $creditLimitValue;
        $payment_terms          = $this->request->getPost('payment_terms');

        $data = [
            'name'              => $name,
            'email'             => $email,
            'phone'             => $phone,
            'address'           => $address,
            'latitude'          => $latitude,
            'longitude'         => $longitude,
            'province_id'       => $province_id,
            'regency_id'        => $regency_id,
            'district_id'       => $district_id,
            'village_id'        => $village_id,
            'postal_code'       => $postal_code,
            'country'           => $country,
            'tax_number'        => $tax_number,
            'credit_limit'      => $credit_limit,
            'payment_terms'     => $payment_terms,
        ];

        // Add code for edit mode (preserve existing code and is_active)
        if ($isEdit) {
            $data['code'] = $this->request->getPost('code');
            $existingAgent = $this->model->find($id);
            if ($existingAgent) {
                $data['is_active'] = $existingAgent->is_active ?? '1';

                if (!$creditLimitEnabled) {
                    $data['credit_limit'] = 0;
                } elseif ($credit_limit === 0 && isset($existingAgent->credit_limit)) {
                    $data['credit_limit'] = format_angka_db($existingAgent->credit_limit);
                }
            }
        } else {
            // Generate code for new records
            $data['code'] = $this->model->generateCode();
            
            // Default to active for new records
            $data['is_active'] = '1';
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Save agent data
            if ($isEdit) {
                if (!$this->model->update($id, $data)) {
                    throw new \RuntimeException('Gagal memperbarui data agen: ' . implode(', ', $this->model->errors()));
                }
                $agentId = $id;
            } else {
                if (!$this->model->insert($data)) {
                    throw new \RuntimeException('Gagal menyimpan data agen: ' . implode(', ', $this->model->errors()));
                }
                $agentId = $this->model->getInsertID();
            }

            // Handle user account (formatted)
            $existingUserId   = $this->request->getPost('existing_user_id');
            $existingUserId   = $existingUserId ? (int) $existingUserId : null;
            $usernameInput    = trim((string) ($this->request->getPost('account_username') ?? ''));
            $passwordInput    = (string) ($this->request->getPost('account_password') ?? '');
            $passwordConfirm  = (string) ($this->request->getPost('account_password_confirm') ?? '');
            $userRole         = $this->request->getPost('user_role') ?? '1';

            $existingUser = null;
            if ($existingUserId) {
                $existingUser = $this->userModel->find($existingUserId);
                if (!$existingUser) {
                    $existingUserId = null;
                }
            }

            $shouldHandleAccount = $existingUserId !== null || $usernameInput !== '' || $passwordInput !== '';

            if ($shouldHandleAccount) {
                if ($usernameInput === '' && !$existingUserId) {
                    throw new \RuntimeException('Username wajib diisi untuk akun agen.');
                }
                if (!$existingUserId && $passwordInput === '') {
                    throw new \RuntimeException('Password wajib diisi untuk akun agen.');
                }
                if ($passwordInput !== '' && $passwordInput !== $passwordConfirm) {
                    throw new \RuntimeException('Konfirmasi password tidak sesuai.');
                }

                if ($usernameInput !== '') {
                    $usernameQuery = $this->userModel->where('username', $usernameInput);
                    if ($existingUserId) {
                        $usernameQuery->where('id_user !=', $existingUserId);
                    }
                    if ($usernameQuery->first()) {
                        throw new \RuntimeException('Username sudah digunakan.');
                    }
                }

                $userData = [
                    'nama' => $data['name'],
                    'email' => $data['email']
                ];

                if ($usernameInput !== '') {
                    $userData['username'] = $usernameInput;
                }

                if ($passwordInput !== '') {
                    $userData['password'] = password_hash($passwordInput, PASSWORD_DEFAULT);
                }

                if (!$existingUserId) {
                    $userData['status'] = 'active';
                    $userData['verified'] = '1';
                    if (!$this->userModel->insert($userData)) {
                        throw new \RuntimeException('Gagal membuat akun user: ' . implode(', ', $this->userModel->errors()));
                    }
                    $userId = $this->userModel->getInsertID();
                } else {
                    $userId = $existingUserId;
                    if (!$this->userModel->update($userId, $userData)) {
                        throw new \RuntimeException('Gagal memperbarui akun user: ' . implode(', ', $this->userModel->errors()));
                    }
                }

                $this->userRoleAgentModel->assignUserToAgent($userId, $agentId, $userRole ?: '1');
            }

            $this->persistAgentProductRule($agentId, $productRuleInput);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            $db->transCommit();

            $message = $isEdit ? 'Data berhasil diupdate' : 'Data berhasil disimpan';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => $message
                ]);
            }
            return redirect()->to('agent')->with('message', $message);
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }

            log_message('error', 'Agent::store error: ' . $e->getMessage());
            $message = 'Gagal menyimpan data: ' . $e->getMessage();

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->back()->withInput()->with('message', $message);
        }
    }

    protected function getDefaultProductRuleData(): array
    {
        return [
            'window_days' => 0,
            'threshold_amount' => 0,
            'cashback_amount' => 0,
            'is_stackable' => 0,
        ];
    }

    protected function getAgentProductRuleData(?int $agentId): array
    {
        $defaults = $this->getDefaultProductRuleData();
        if (empty($agentId)) {
            return $defaults;
        }

        $rule = $this->cashbackRuleModel
            ->where('agent_id', $agentId)
            ->where('rule_type', 'cashback')
            ->first();

        if (!$rule) {
            return $defaults;
        }

        $defaults['threshold_amount'] = (float) ($rule->min_transaction ?? 0);
        $defaults['cashback_amount'] = (float) ($rule->cashback_amount ?? 0);

        // Prefer dedicated columns, fall back to legacy notes if needed
        $windowDays = $rule->window_days ?? null;
        $isStackable = $rule->is_stackable ?? null;

        if ($windowDays === null || $isStackable === null) {
            $notes = [];
            if (!empty($rule->notes)) {
                $decodedNotes = json_decode($rule->notes, true);
                if (is_array($decodedNotes)) {
                    $notes = $decodedNotes;
                }
            }
            if ($windowDays === null && isset($notes['window_days'])) {
                $windowDays = (int) $notes['window_days'];
            }
            if ($isStackable === null && array_key_exists('is_stackable', $notes)) {
                $isStackable = !empty($notes['is_stackable']) ? 1 : 0;
            }
        }

        $defaults['window_days'] = (int) ($windowDays ?? 0);
        $defaults['is_stackable'] = (int) ($isStackable ?? 0);

        return $defaults;
    }

    protected function getProductRuleInputFromRequest(): array
    {
        helper('angka');
        return [
            'window_days' => (int) ($this->request->getPost('cashback_window_days') ?? 0),
            'threshold_amount' => format_angka_db($this->request->getPost('cashback_threshold_amount')),
            'cashback_amount' => format_angka_db($this->request->getPost('cashback_amount')),
            'is_stackable' => $this->request->getPost('cashback_is_stackable') ? 1 : 0,
        ];
    }

    protected function persistAgentProductRule(int $agentId, array $ruleInput): void
    {
        $shouldSave = $ruleInput['window_days'] > 0
            && $ruleInput['threshold_amount'] > 0
            && $ruleInput['cashback_amount'] > 0;

        $existingRule = $this->cashbackRuleModel
            ->where('agent_id', $agentId)
            ->where('rule_type', 'cashback')
            ->first();

        if ($shouldSave) {
            $data = [
                'agent_id'        => $agentId,
                'rule_type'       => 'cashback',
                'window_days'     => $ruleInput['window_days'],
                'min_transaction' => $ruleInput['threshold_amount'],
                'cashback_amount' => $ruleInput['cashback_amount'],
                'is_stackable'    => $ruleInput['is_stackable'],
                'is_active'       => 1,
                'start_date'      => null,
                'end_date'        => null,
                'notes'           => json_encode([
                    'window_days'  => $ruleInput['window_days'],
                    'is_stackable' => $ruleInput['is_stackable'] ? true : false,
                ]),
            ];

            if ($existingRule) {
                $this->cashbackRuleModel->update($existingRule->id, $data);
            } else {
                $this->cashbackRuleModel->insert($data);
            }
        } else {
            if ($existingRule) {
                $this->cashbackRuleModel->delete($existingRule->id);
            }
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

            // Check if user needs permission filtering
            $needsPermissionFilter = false;
            $userId = null;
            if ($this->hasPermissionPrefix('read')) {
                if (in_array('read_own', $this->userPermission) && !in_array('read_all', $this->userPermission)) {
                    $needsPermissionFilter = true;
                    $userId = $this->user['id_user'];
                }
            }

            // Get total records (before search filter) - use fresh query builder
            $db = \Config\Database::connect();
            
            if ($needsPermissionFilter) {
                // Use subquery approach to avoid duplicate column issues
                $totalBuilder = $db->table('agent')
                                 ->select('agent.id')
                                 ->join('user_role_agent', 'user_role_agent.agent_id = agent.id', 'inner')
                                 ->where('user_role_agent.user_id', $userId)
                                 ->groupBy('agent.id');
            } else {
                $totalBuilder = $db->table('agent');
            }
            $recordsTotal = $totalBuilder->countAllResults(false);

            // Get filtered records count (with search filter) - use fresh query builder
            if ($needsPermissionFilter) {
                $filteredBuilder = $db->table('agent')
                                     ->select('agent.id')
                                     ->join('user_role_agent', 'user_role_agent.agent_id = agent.id', 'inner')
                                     ->where('user_role_agent.user_id', $userId)
                                     ->groupBy('agent.id');
            } else {
                $filteredBuilder = $db->table('agent');
            }
            if (!empty($search)) {
                $filteredBuilder->groupStart()
                               ->like('agent.name', $search)
                               ->orLike('agent.code', $search)
                               ->orLike('agent.address', $search)
                               ->groupEnd();
            }
            $recordsFiltered = $filteredBuilder->countAllResults(false);

            // Get data with pagination - use fresh query builder
            if ($needsPermissionFilter) {
                // Use inner join and groupBy to avoid duplicate issues
                $dataBuilder = $db->table('agent')
                                 ->select('agent.*')
                                 ->join('user_role_agent', 'user_role_agent.agent_id = agent.id', 'inner')
                                 ->where('user_role_agent.user_id', $userId)
                                 ->groupBy('agent.id');
            } else {
                $dataBuilder = $db->table('agent')->select('agent.*');
            }
            
            if (!empty($search)) {
                $dataBuilder->groupStart()
                           ->like('agent.name', $search)
                           ->orLike('agent.code', $search)
                           ->orLike('agent.address', $search)
                           ->groupEnd();
            }
            
            $agents = $dataBuilder->orderBy('agent.' . $column_order, $order_dir)
                                 ->limit($length, $start)
                                 ->get()
                                 ->getResult();

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
        $this->data['title']           = 'Import Data Agen';
        $this->data['current_module']  = $this->currentModule;
        $this->data['msg']             = $this->session->getFlashdata('message');

        if ($this->request->getMethod() === 'post') {
            $file = $this->request->getFile('file');

            if ($file && $file->isValid() && !$file->hasMoved()) {
                $extension = $file->getClientExtension();

                if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                    $this->data['msg'] = show_alert('File harus berupa Excel (.xlsx, .xls) atau CSV', 'error');
                } else {
                    try {
                        $reader      = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(ucfirst($extension));
                        $spreadsheet = $reader->load($file->getTempName());
                        $worksheet   = $spreadsheet->getActiveSheet();
                        $rows        = $worksheet->toArray();

                        $successCount = 0;
                        $errorCount   = 0;
                        $errors       = [];

                        // Skip header row
                        array_shift($rows);

                        foreach ($rows as $index => $row) {
                            if (empty($row[0])) {
                                continue; // Skip empty rows
                            }

                            try {
                                $data = [
                                    'code'          => $this->model->generateCode(),
                                    'name'          => $row[0] ?? '',
                                    'email'         => $row[1] ?? '',
                                    'phone'         => $row[2] ?? '',
                                    'address'       => $row[3] ?? '',
                                    'country'       => $row[4] ?? 'Indonesia',
                                    'tax_number'    => $row[5] ?? '',
                                    'credit_limit'  => $row[6] ?? 0,
                                    'payment_terms' => $row[7] ?? 0,
                                    'is_active'     => '1'
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

    public function updateUserPassword()
    {
        try {
            // Check update permissions
            if (!$this->hasPermissionPrefix('update')) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'You do not have permission to update user password.'
                    ]);
                }
                return redirect()->to('agent')->with('message', 'You do not have permission to update user password.');
            }

            $agentId = $this->request->getPost('agent_id');
            $userId = $this->request->getPost('user_id');
            $oldPassword = $this->request->getPost('old_password');
            $newPassword = $this->request->getPost('new_password');
            $repeatPassword = $this->request->getPost('repeat_password');

            // Validate required fields
            if (empty($userId)) {
                $message = 'User ID tidak ditemukan';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->with('message', $message);
            }

            if (empty($oldPassword) || empty($newPassword) || empty($repeatPassword)) {
                $message = 'Semua field password harus diisi';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->with('message', $message);
            }

            // Validate password match
            if ($newPassword !== $repeatPassword) {
                $message = 'Password baru dan ulangi password tidak cocok';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->with('message', $message);
            }

            // Get user data
            $user = $this->userModel->find($userId);
            if (!$user) {
                $message = 'User tidak ditemukan';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->with('message', $message);
            }

            // Verify old password
            if (!password_verify($oldPassword, $user->password)) {
                $message = 'Password lama tidak cocok';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->with('message', $message);
            }

            // Validate new password strength (minimum 3 characters as per existing validation)
            if (strlen($newPassword) < 3) {
                $message = 'Password baru minimal 3 karakter';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->with('message', $message);
            }

            // Update password
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $this->userModel->update($userId, ['password' => $passwordHash]);

            if ($update) {
                $message = 'Password berhasil diupdate';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->with('message', $message);
            } else {
                $message = 'Password gagal diupdate';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->with('message', $message);
            }

        } catch (\Exception $e) {
            log_message('error', 'Agent::updateUserPassword error: ' . $e->getMessage());
            $message = 'Terjadi kesalahan: ' . $e->getMessage();
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->back()->with('message', $message);
        }
    }

    /**
     * Check active gateway for agent/post integration
     * POST endpoint: agent/checkActiveGateway
     * Returns active gateway platforms that agents can use
     * 
     * @return ResponseInterface
     */
    public function checkActiveGateway()
    {
        try {
            // Get active gateways (status = 1 AND gw_status = 1)
            $gateways = $this->platformModel->getActiveGateways();
            
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'data' => $gateways,
                    'count' => count($gateways)
                ]);
            }
            
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $gateways,
                'count' => count($gateways)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Agent::checkActiveGateway error: ' . $e->getMessage());
            
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengambil data gateway aktif: ' . $e->getMessage()
                ]);
            }
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengambil data gateway aktif: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get gateway by code for agent/post integration
     * POST/GET endpoint: agent/getGatewayByCode
     * Validates if gateway exists and is active
     * 
     * @param string|null $gwCode Gateway code
     * @return ResponseInterface
     */
    public function getGatewayByCode($gwCode = null)
    {
        try {
            if (!$gwCode) {
                $gwCode = $this->request->getGet('gw_code') ?? $this->request->getPost('gw_code');
            }
            
            if (!$gwCode) {
                if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Gateway code tidak ditemukan'
                    ]);
                }
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gateway code tidak ditemukan'
                ]);
            }
            
            $gateway = $this->platformModel->getByGatewayCode($gwCode);
            
            if ($gateway) {
                // Check if gateway is active (status = 1 AND status_agent = 1 AND gw_status = 1)
                $isActive = ($gateway['status'] ?? '0') == '1' 
                         && ($gateway['status_agent'] ?? '0') == '1' 
                         && ($gateway['gw_status'] ?? '0') == '1';
                
                if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'data' => $gateway,
                        'is_active' => $isActive
                    ]);
                }
                
                return $this->response->setJSON([
                    'status' => 'success',
                    'data' => $gateway,
                    'is_active' => $isActive
                ]);
            } else {
                if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Gateway tidak ditemukan atau tidak aktif'
                    ]);
                }
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gateway tidak ditemukan atau tidak aktif'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Agent::getGatewayByCode error: ' . $e->getMessage());
            
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengambil data gateway: ' . $e->getMessage()
                ]);
            }
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengambil data gateway: ' . $e->getMessage()
            ]);
        }
    }
}
