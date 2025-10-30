<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-21
 * Github: github.com/mikhaelfelian
 * Description: Controller for managing item brands with CRUD operations and DataTables
 * This file represents the Controller for Item_Brand.
 */

namespace App\Controllers;

use App\Models\ItemBrandModel;

class Item_Brand extends BaseController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ItemBrandModel();
    }

    public function index()
    {
        $this->data['title'] = 'Data Brand';
        $this->data['current_module'] = $this->currentModule;
        $this->data['result'] = $this->model->findAll();
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        return $this->view('item-brand-result', $this->data);
    }

    public function add()
    {
        $this->data['title'] = 'Form Brand';
        $this->data['current_module'] = $this->currentModule;
        $this->data['item_brand'] = [];
        $this->data['id'] = '';
        $this->data['message'] = '';
        
        // Check if it's an AJAX request for modal
        $isAjax = $this->request->isAJAX() || $this->request->getHeader('X-Requested-With') !== null;
        $this->data['isModal'] = $isAjax;
        
        if ($isAjax) {
            return view('themes/modern/item-brand-form', $this->data);
        }
        
        return view('themes/modern/item-brand-form', $this->data);
    }

    public function edit()
    {
        $id = $this->request->getGet('id');
        
        if (!$id) {
            $message = 'ID tidak ditemukan';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('item-brand')->with('message', $message);
        }

        $item_brand = $this->model->find($id);
        
        if (!$item_brand) {
            $message = 'Data tidak ditemukan';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('item-brand')->with('message', $message);
        }

        $this->data['title'] = 'Form Brand';
        $this->data['current_module'] = $this->currentModule;
        $this->data['item_brand'] = $item_brand;
        $this->data['id'] = $id;
        
        // Check if it's an AJAX request for modal
        $isAjax = $this->request->isAJAX() || $this->request->getHeader('X-Requested-With') !== null;
        $this->data['isModal'] = $isAjax;
        
        if ($isAjax) {
            return $this->view('item-brand-form', $this->data);
        }
        
        return $this->view('item-brand-form', $this->data);
    }

    public function store()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'name' => 'required|max_length[100]',
            'code' => 'permit_empty|max_length[50]',
            'slug' => 'permit_empty|max_length[100]',
            'status' => 'in_list[0,1]'
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
            
            return redirect()->back()->withInput()->with('message', $message);
        }

        // Separate variables for POST inputs
        $name        = $this->request->getPost('name');
        $slug        = $this->request->getPost('slug');
        $description = $this->request->getPost('description');
        $status      = $this->request->getPost('status') ?? '1';
        $id          = $this->request->getPost('id');

        // Correct session retrieval to array, not string. Prevents "Cannot access offset of type string on string"
        $userSession = session('user'); // Expecting this as array with 'id_user'
        if (!is_array($userSession) || !isset($userSession['id_user'])) {
            $message = 'User session tidak ditemukan, silakan login ulang.';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('login')->with('message', $message);
        }

        $userId = $userSession['id_user'];

        if ($id) {
            // Update existing record - keep existing code
            $existingRecord = $this->model->find($id);
            if (!$existingRecord) {
                $message = 'Data tidak ditemukan untuk update.';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->with('message', $message);
            }
            $data = [
                'user_id'     => $userId,
                'code'        => $existingRecord->code, // Keep existing code
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
                'status'      => $status
            ];
            $result = $this->model->update($id, $data);
            $message = $result ? 'Data berhasil diupdate' : 'Data gagal diupdate';
        } else {
            // Insert new record - generate new code
            $data = [
                'user_id'     => $userId,
                'code'        => $this->model->generateCode($name), // Auto-generate code
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
                'status'      => $status
            ];
            $result = $this->model->save($data);
            $message = $result ? 'Data berhasil disimpan' : 'Data gagal disimpan';
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $message
            ]);
        }

        if ($result) {
            return redirect()->to('item-brand')->with('message', $message);
        } else {
            return redirect()->back()->withInput()->with('message', $message);
        }
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        
        if (!$id) {
            $message = 'ID tidak ditemukan';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('item-brand')->with('message', $message);
        }

        $result = $this->model->delete($id);
        $message = $result ? 'Data berhasil dihapus' : 'Data gagal dihapus';

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $message
            ]);
        }

        return redirect()->to('item-brand')->with('message', $message);
    }

    public function toggleStatus()
    {
        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');
        
        if (!$id || !in_array($status, ['0', '1'])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'ID atau status tidak valid'
                ]);
            }
            return redirect()->to('item-brand')->with('message', 'ID atau status tidak valid');
        }

        $result = $this->model->update($id, ['status' => $status]);
        $message = $result ? 'Status berhasil diubah' : 'Status gagal diubah';

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $message
            ]);
        }

        return redirect()->to('item-brand')->with('message', $message);
    }

    public function getDataDT()
    {
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        $searchValue = $this->request->getPost('search')['value'] ?? '';

        $totalRecords = $this->model->countAll();
        $totalFiltered = $totalRecords;

        // Build query
        $query = $this->model->select('id, code, name, description, status');
        
        if (!empty($searchValue)) {
            $query->groupStart()
                  ->like('name', $searchValue)
                  ->orLike('code', $searchValue)
                  ->orLike('description', $searchValue)
                  ->groupEnd();
                  
            $totalFiltered = $this->model->groupStart()
                                       ->like('name', $searchValue)
                                       ->orLike('code', $searchValue)
                                       ->orLike('description', $searchValue)
                                       ->groupEnd()
                                       ->countAllResults();
        }

        $data = $query->orderBy('name', 'ASC')
                     ->findAll($length ?? 10, $start ?? 0);

        $result = [];
        $no = ($start ?? 0) + 1;

        foreach ($data as $row) {
            $checked = $row->status == '1' ? 'checked=""' : '';
            $status = '<div class="form-switch">
                        <input name="aktif" type="checkbox" class="form-check-input switch" data-module-id="' . $row->id . '" ' . $checked . '>
                      </div>';
            
            $result[] = [
                'ignore_search_urut' => $no++,
                'code' => $row->code ?? '-',
                'name' => $row->name,
                'description' => $row->description ?? '-',
                'status' => $status,
                'ignore_search_action' => '
                    <button class="btn btn-sm btn-primary btn-edit rounded-0" data-id="' . $row->id . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger btn-delete rounded-0" data-id="' . $row->id . '" data-name="' . $row->name . '">
                        <i class="fa fa-trash"></i>
                    </button>
                '
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $result
        ]);
    }
}
