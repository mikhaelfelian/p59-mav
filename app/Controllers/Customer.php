<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-04
 * Github: github.com/mikhaelfelian
 * Description: Controller for managing customers with CRUD operations
 */

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;

class Customer extends BaseController
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new CustomerModel();
    }

    public function index()
    {
        $this->data['title'] = 'Data Pelanggan';
        $this->data['current_module'] = $this->currentModule;
        $this->data['result'] = $this->model->findAll();
        $this->data['message'] = '';

        $this->view('customer-result.php', $this->data);
    }

    public function add()
    {
        $this->data['title'] = 'Form Pelanggan';
        $this->data['current_module'] = $this->currentModule;
        $this->data['customer'] = [];
        $this->data['id'] = '';
        $this->data['message'] = '';
        $this->data['isModal'] = $this->request->isAJAX();

        if ($this->request->isAJAX()) {
            return view('themes/modern/customer-form', $this->data);
        }

        return view('themes/modern/customer-form', $this->data);
    }

    public function edit()
    {
        $id = $this->request->getGet('id');
        if (!$id) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            return redirect()->to('customer')->with('message', 'ID tidak ditemukan');
        }

        $customer = $this->model->find($id);
        if (!$customer) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ]);
            }
            return redirect()->to('customer')->with('message', 'Data tidak ditemukan');
        }

        $this->data['title'] = 'Edit Pelanggan';
        $this->data['current_module'] = $this->currentModule;
        $this->data['customer'] = $customer;
        $this->data['id'] = $id;
        $this->data['message'] = '';
        $this->data['isModal'] = $this->request->isAJAX();

        if ($this->request->isAJAX()) {
            return view('themes/modern/customer-form', $this->data);
        }

        $this->view('customer-form.php', $this->data);
    }

    public function store()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'name' => 'required|max_length[255]',
            'email' => 'permit_empty|valid_email|max_length[255]',
            'phone' => 'permit_empty|max_length[20]',
            'plat_code' => 'permit_empty|max_length[10]',
            'plat_number' => 'permit_empty|max_length[10]',
            'plat_last' => 'permit_empty|max_length[10]',
            'status' => 'permit_empty|in_list[active,inactive]'
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

        // Get POST data
        $postData = $this->request->getPost();
        $id = $postData['id'] ?? null;

        // Validate user session
        $userSession = session('user');
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

        // Prepare data
        $data = [
            'name' => trim($postData['name'] ?? ''),
            'email' => !empty($postData['email']) ? trim($postData['email']) : null,
            'phone' => !empty($postData['phone']) ? trim($postData['phone']) : null,
            'address' => !empty($postData['address']) ? trim($postData['address']) : null,
            'province_id' => !empty($postData['province_id']) ? (int)$postData['province_id'] : null,
            'regency_id' => !empty($postData['regency_id']) ? (int)$postData['regency_id'] : null,
            'district_id' => !empty($postData['district_id']) ? (int)$postData['district_id'] : null,
            'village_id' => !empty($postData['village_id']) ? (int)$postData['village_id'] : null,
            'postal_code' => !empty($postData['postal_code']) ? trim($postData['postal_code']) : null,
            'country' => !empty($postData['country']) ? trim($postData['country']) : 'Indonesia',
            'tax_number' => !empty($postData['tax_number']) ? trim($postData['tax_number']) : null,
            'status' => !empty($postData['status']) ? $postData['status'] : 'active',
            'plat_code' => !empty($postData['plat_code']) ? trim($postData['plat_code']) : null,
            'plat_number' => !empty($postData['plat_number']) ? trim($postData['plat_number']) : null,
            'plat_last' => !empty($postData['plat_last']) ? trim($postData['plat_last']) : null
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
                return redirect()->back()->with('message', $message);
            }
            
            // Keep existing code if present
            if (!empty($existingRecord['code'])) {
                $data['code'] = $existingRecord['code'];
            }
            
            $this->model->skipValidation(true);
            $result = $this->model->update($id, $data);
            $this->model->skipValidation(false);
            $message = $result ? 'Data berhasil diupdate' : 'Data gagal diupdate';
        } else {
            // Insert new record
            // Auto-generate code if not provided
            if (empty($postData['code'])) {
                $data['code'] = $this->generateCustomerCode();
            } else {
                $data['code'] = trim($postData['code']);
            }
            
            $this->model->skipValidation(true);
            $result = $this->model->insert($data);
            $this->model->skipValidation(false);
            $message = $result ? 'Data berhasil disimpan' : 'Data gagal disimpan';
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $message
            ]);
        }

        if ($result) {
            return redirect()->to('customer')->with('message', $message);
        } else {
            return redirect()->back()->withInput()->with('message', $message);
        }
    }

    public function delete()
    {
        $id = $this->request->getPost('id') ?? $this->request->getGet('id');
        if (!$id) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            return redirect()->to('customer')->with('message', 'ID tidak ditemukan');
        }

        $result = $this->model->delete($id);
        $message = $result ? 'Data berhasil dihapus' : 'Data gagal dihapus';

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $message
            ]);
        }

        return redirect()->to('customer')->with('message', $message);
    }

    public function toggleStatus()
    {
        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');
        
        if (!$id || !in_array($status, ['active', 'inactive'])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'ID atau status tidak valid'
                ]);
            }
            return redirect()->to('customer')->with('message', 'ID atau status tidak valid');
        }

        $result = $this->model->update($id, ['status' => $status]);
        $message = $result ? 'Status berhasil diubah' : 'Status gagal diubah';

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $message
            ]);
        }

        return redirect()->to('customer')->with('message', $message);
    }

    public function getDataDT()
    {
        $draw = $this->request->getPost('draw') ?? $this->request->getGet('draw') ?? 0;
        $start = $this->request->getPost('start') ?? $this->request->getGet('start') ?? 0;
        $length = $this->request->getPost('length') ?? $this->request->getGet('length') ?? 10;
        $searchValue = $this->request->getPost('search')['value'] ?? $this->request->getGet('search')['value'] ?? '';

        $totalRecords = $this->model->countAll();
        $totalFiltered = $totalRecords;

        // Build query
        $query = $this->model->select('id, code, name, email, phone, plat_code, plat_number, plat_last, status');
        
        if (!empty($searchValue)) {
            $query->groupStart()
                  ->like('name', $searchValue)
                  ->orLike('code', $searchValue)
                  ->orLike('email', $searchValue)
                  ->orLike('phone', $searchValue)
                  ->orLike('plat_code', $searchValue)
                  ->orLike('plat_number', $searchValue)
                  ->groupEnd();
                  
            $totalFiltered = $this->model->groupStart()
                                       ->like('name', $searchValue)
                                       ->orLike('code', $searchValue)
                                       ->orLike('email', $searchValue)
                                       ->orLike('phone', $searchValue)
                                       ->orLike('plat_code', $searchValue)
                                       ->orLike('plat_number', $searchValue)
                                       ->groupEnd()
                                       ->countAllResults();
        }

        $data = $query->orderBy('name', 'ASC')
                     ->findAll($length, $start);

        $result = [];
        $no = $start + 1;

        foreach ($data as $row) {
            $checked = ($row['status'] ?? 'active') == 'active' ? 'checked=""' : '';
            $status = '<div class="form-switch">
                        <input name="aktif" type="checkbox" class="form-check-input switch" data-module-id="' . $row['id'] . '" ' . $checked . '>
                      </div>';
            
            // Format plate number
            $plateDisplay = '-';
            if (!empty($row['plat_code']) && !empty($row['plat_number'])) {
                $plateDisplay = $row['plat_code'] . '-' . $row['plat_number'];
                if (!empty($row['plat_last'])) {
                    $plateDisplay .= '-' . $row['plat_last'];
                }
            }
            
            $result[] = [
                'ignore_search_urut' => $no++,
                'code' => $row['code'] ?? '-',
                'name' => $row['name'] ?? '-',
                'email' => $row['email'] ?? '-',
                'phone' => $row['phone'] ?? '-',
                'plate' => $plateDisplay,
                'status' => $status,
                'ignore_search_action' => '
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-primary btn-edit rounded-0" data-id="' . $row['id'] . '">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete rounded-0" data-id="' . $row['id'] . '" data-name="' . ($row['name'] ?? '') . '">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
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

    /**
     * Generate unique customer code
     * 
     * @return string
     */
    private function generateCustomerCode()
    {
        $prefix = 'CUST';
        $date = date('Ymd');
        $lastCustomer = $this->model->select('code')
            ->like('code', $prefix . $date)
            ->orderBy('id', 'DESC')
            ->first();

        if ($lastCustomer && !empty($lastCustomer['code'])) {
            $lastNumber = (int) substr($lastCustomer['code'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}


