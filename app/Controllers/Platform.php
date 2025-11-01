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
            'status' => 'in_list[0,1]',
            'status_sys' => 'permit_empty|in_list[0,1]'
        ];

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
        $status = $this->request->getPost('status') ?? '1';
        // Checkbox: if not set or empty, default to '0'
        $statusSys = $this->request->getPost('status_sys') ? '1' : '0';
        $id = $this->request->getPost('id');

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
            'status_sys' => $statusSys
        ];

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
            
            $statusSysBadge = $row['status_sys'] == '1' 
                ? '<span class="badge bg-info">Ya</span>' 
                : '<span class="badge bg-secondary">Tidak</span>';

            $actionButtons = '<div class="btn-group" role="group">';
            $actionButtons .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="' . $row['id'] . '" title="Edit"><i class="fas fa-edit"></i></button>';
            $actionButtons .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row['id'] . '" data-platform="' . esc($row['platform']) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
            $actionButtons .= '</div>';

            $result[] = [
                'ignore_search_urut' => $no,
                'code' => esc($row['code'] ?? '-'),
                'platform' => esc($row['platform'] ?? '-'),
                'description' => esc(substr($row['description'] ?? '', 0, 100)) . (strlen($row['description'] ?? '') > 100 ? '...' : ''),
                'status' => $statusBadge,
                'status_sys' => $statusSysBadge,
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
}

