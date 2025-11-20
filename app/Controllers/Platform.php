<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Controller for managing platforms with CRUD operations
 * This file represents the Controller for Platform.
 */

namespace App\Controllers;

use App\Models\PlatformModel;

class Platform extends BaseController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PlatformModel();
    }

    /**
     * List all platforms
     */
    public function index()
    {
        $this->data['title'] = 'Data Platform';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        return $this->view('master/platform/platform-result', $this->data);
    }

    /**
     * Display platform creation form
     */
    public function add()
    {
        $this->data['title'] = 'Form Platform';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['platform'] = [];
        $this->data['id'] = '';
        $this->data['message'] = '';
        
        // Check if it's an AJAX request for modal
        $isAjax = $this->request->isAJAX() || $this->request->getHeader('X-Requested-With') !== null;
        $this->data['isModal'] = $isAjax;
        
        if ($isAjax) {
            return view('themes/modern/master/platform/platform-form', $this->data);
        }
        
        return view('themes/modern/master/platform/platform-form', $this->data);
    }

    /**
     * Load data for editing
     */
    public function edit($id)
    {
        if (!$id) {
            $message = ['status' => 'error', 'message' => 'ID tidak ditemukan'];
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            return redirect()->to('platform')->with('message', $message);
        }

        $platform = $this->model->find($id);
        
        if (!$platform) {
            $message = ['status' => 'error', 'message' => 'Data tidak ditemukan'];
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ]);
            }
            return redirect()->to('platform')->with('message', $message);
        }

        $this->data['title'] = 'Form Platform';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['platform'] = $platform;
        $this->data['id'] = $id;
        $this->data['message'] = '';
        
        // Check if it's an AJAX request for modal
        $isAjax = $this->request->isAJAX() || $this->request->getHeader('X-Requested-With') !== null;
        $this->data['isModal'] = $isAjax;
        
        if ($isAjax) {
            return view('themes/modern/master/platform/platform-form', $this->data);
        }
        
        return view('themes/modern/master/platform/platform-form', $this->data);
    }

    /**
     * Handle form submission for add or edit
     */
    public function store()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'code' => 'permit_empty|max_length[160]',
            'platform' => 'permit_empty|max_length[160]',
            'description' => 'permit_empty',
            'gw_code' => 'permit_empty|max_length[50]',
            'gw_status' => 'permit_empty|in_list[0,1]'
        ];
        
        // Only validate logo if file is uploaded
        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $rules['logo'] = 'uploaded[logo]|max_size[logo,2048]|ext_in[logo,jpg,jpeg,png,gif]|is_image[logo]';
        }

        if (!$this->validate($rules)) {
            $errors = $validation->getErrors();
            $message = implode('<br>', $errors);
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $message
                ]);
            }
            
            return redirect()->back()->withInput()->with('message', ['status' => 'error', 'message' => $message]);
        }

        // Get POST data
        $code = $this->request->getPost('code');
        $platform = $this->request->getPost('platform');
        $description = $this->request->getPost('description');
        // Checkbox: if not set or empty, default to '0'
        $status = $this->request->getPost('status') ? '1' : '0';
        $statusAgent = $this->request->getPost('status_agent') ? '1' : '0';
        $statusPos = $this->request->getPost('status_pos') ? '1' : '0';
        $gwCode = $this->request->getPost('gw_code');
        $gwStatus = $this->request->getPost('gw_status') ? '1' : '0';
        $statusSysInput = $this->request->getPost('status_sys', FILTER_DEFAULT);
        $statusSys = ($statusSysInput === '1') ? '1' : (($statusSysInput === '0') ? '0' : null);
        $id = $this->request->getPost('id');
        
        // Handle logo upload
        $logo = '';
        $file = $this->request->getFile('logo');
        $logoOld = $this->request->getPost('logo_old');
        
        // Create upload directory if it doesn't exist
        $uploadPath = ROOTPATH . 'public/uploads/platform/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validate file
            if ($file->getSize() > 2097152) { // 2MB
                $message = 'Ukuran file logo maksimal 2MB.';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->withInput()->with('message', ['status' => 'error', 'message' => $message]);
            }
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                $message = 'Format file harus JPG, PNG, atau GIF.';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->withInput()->with('message', ['status' => 'error', 'message' => $message]);
            }
            
            // Generate unique filename
            $newName = $file->getRandomName();
            if ($file->move($uploadPath, $newName)) {
                $logo = $newName;
                
                // Delete old logo if exists
                if ($logoOld && file_exists($uploadPath . $logoOld)) {
                    @unlink($uploadPath . $logoOld);
                }
            } else {
                $message = 'Gagal mengupload logo.';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->withInput()->with('message', ['status' => 'error', 'message' => $message]);
            }
        } elseif ($id && $logoOld) {
            // Keep existing logo if no new file uploaded
            $logo = $logoOld;
        }

        // Get user session
        $userSession = session('user');
        if (!is_array($userSession) || !isset($userSession['id_user'])) {
            $message = 'User session tidak ditemukan, silakan login ulang.';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('login')->with('message', ['status' => 'error', 'message' => $message]);
        }

        $userId = $userSession['id_user'];

        $data = [
            'user_id' => $userId,
            'code' => $code,
            'platform' => $platform,
            'description' => $description,
            'status' => $status,
            'status_agent' => $statusAgent,
            'status_pos' => $statusPos,
            'gw_code' => $gwCode,
            'gw_status' => $gwStatus
        ];

        if ($statusSys !== null) {
            $data['status_sys'] = $statusSys;
        }
        
        // Only add logo if it's set (new upload or existing)
        if (!empty($logo)) {
            $data['logo'] = $logo;
        }

        if ($id) {
            // Update existing record
            $existingRecord = $this->model->find($id);
            if (!$existingRecord) {
                $message = 'Data tidak ditemukan untuk update.';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->to('platform')->with('message', ['status' => 'error', 'message' => $message]);
            }

            if ($this->model->update($id, $data)) {
                $message = 'Platform berhasil diupdate.';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => $message
                    ]);
                }
                return redirect()->to('platform')->with('message', ['status' => 'success', 'message' => $message]);
            } else {
                $errors = $this->model->errors();
                $message = 'Gagal mengupdate platform: ' . implode(', ', $errors);
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->withInput()->with('message', ['status' => 'error', 'message' => $message]);
            }
        } else {
            // Insert new record
            if ($this->model->insert($data)) {
                $message = 'Platform berhasil ditambahkan.';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => $message
                    ]);
                }
                return redirect()->to('platform')->with('message', ['status' => 'success', 'message' => $message]);
            } else {
                $errors = $this->model->errors();
                $message = 'Gagal menambahkan platform: ' . implode(', ', $errors);
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->withInput()->with('message', ['status' => 'error', 'message' => $message]);
            }
        }
    }

    /**
     * Update record (alias for store with ID)
     */
    public function update($id)
    {
        // Add ID to POST data and call store
        $_POST['id'] = $id;
        return $this->store();
    }

    /**
     * Delete record
     */
    public function delete($id)
    {
        if (!$id) {
            $message = ['status' => 'error', 'message' => 'ID tidak ditemukan'];
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            return redirect()->to('platform')->with('message', $message);
        }

        $platform = $this->model->find($id);
        
        if (!$platform) {
            $message = ['status' => 'error', 'message' => 'Data tidak ditemukan'];
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ]);
            }
            return redirect()->to('platform')->with('message', $message);
        }

        // Hard delete
        if ($this->model->delete($id)) {
            $message = ['status' => 'success', 'message' => 'Platform berhasil dihapus.'];
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Platform berhasil dihapus.'
                ]);
            }
            return redirect()->to('platform')->with('message', $message);
        } else {
            $message = ['status' => 'error', 'message' => 'Gagal menghapus platform.'];
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal menghapus platform.'
                ]);
            }
            return redirect()->to('platform')->with('message', $message);
        }
    }

    /**
     * Get DataTables data
     */
    public function getDataDT()
    {
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start') ?? 0;
        $length = $this->request->getPost('length') ?? 10;
        $searchValue = $this->request->getPost('search')['value'] ?? '';

        $totalRecords = $this->model->countAllResults(false);
        $totalFiltered = $totalRecords;

        // Build query
        $query = $this->model->select('platform.*, user.nama as user_name')
            ->join('user', 'user.id_user = platform.user_id', 'left');

        // Apply search
        if (!empty($searchValue)) {
            $query->groupStart()
                  ->like('platform.code', $searchValue)
                  ->orLike('platform.platform', $searchValue)
                  ->orLike('platform.description', $searchValue)
                  ->groupEnd();

            $totalFiltered = $this->model->select('platform.*')
                ->join('user', 'user.id_user = platform.user_id', 'left')
                ->groupStart()
                ->like('platform.code', $searchValue)
                ->orLike('platform.platform', $searchValue)
                ->orLike('platform.description', $searchValue)
                ->groupEnd()
                ->countAllResults(false);
        }

        // Get data
        $data = $query->orderBy('platform.created_at', 'DESC')
                     ->findAll($length, $start);

        // Format data for DataTables
        $result = [];
        $no = $start + 1;

        foreach ($data as $row) {
            $statusBadge = $row['status'] == '1' 
                ? '<span class="badge bg-success">Aktif</span>' 
                : '<span class="badge bg-danger">Tidak Aktif</span>';
            
            $gwStatusBadge = ($row['gw_status'] ?? '0') == '1' 
                ? '<span class="badge bg-success">Aktif</span>' 
                : '<span class="badge bg-secondary">Tidak Aktif</span>';
            
            $logoDisplay = '';
            if (!empty($row['logo']) && file_exists(ROOTPATH . 'public/uploads/platform/' . $row['logo'])) {
                $logoDisplay = '<img src="' . base_url('public/uploads/platform/' . $row['logo']) . '" alt="Logo" style="max-width: 50px; max-height: 50px;" class="img-thumbnail">';
            } else {
                $logoDisplay = '<span class="text-muted">-</span>';
            }

            $actionButtons = '<div class="btn-group" role="group">';
            if (($row['status_sys'] ?? '0') !== '1') {
                $actionButtons .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="' . $row['id'] . '" title="Edit"><i class="fas fa-edit"></i></button>';
                $actionButtons .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row['id'] . '" data-platform="' . esc($row['platform']) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
            } else {
                $actionButtons .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="' . $row['id'] . '" title="Edit"><i class="fas fa-edit"></i></button>';
            }
            $actionButtons .= '</div>';

            $result[] = [
                'ignore_search_urut' => $no,
                'code' => esc($row['code'] ?? '-'),
                'platform' => esc($row['platform'] ?? '-'),
                'description' => esc(substr($row['description'] ?? '', 0, 100)) . (strlen($row['description'] ?? '') > 100 ? '...' : ''),
                'gw_code' => esc($row['gw_code'] ?? '-'),
                'gw_status' => $gwStatusBadge,
                'logo' => $logoDisplay,
                'status' => $statusBadge,
                'ignore_search_action' => $actionButtons
            ];

            $no++;
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $result
        ]);
    }

    /**
     * Check active gateway for agent/post integration
     * Returns active gateway platforms
     */
    public function checkActiveGateway()
    {
        $gateways = $this->model->getActiveGateways();
        
        if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $gateways
            ]);
        }
        
        return $gateways;
    }

    /**
     * Get gateway by code (for agent/post integration)
     */
    public function getGatewayByCode($gwCode = null)
    {
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
            return null;
        }
        
        $gateway = $this->model->getByGatewayCode($gwCode);
        
        if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
            if ($gateway) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'data' => $gateway
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gateway tidak ditemukan atau tidak aktif'
                ]);
            }
        }
        
        return $gateway;
    }
}

