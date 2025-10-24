<?php

namespace App\Controllers;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-23
 * Github: github.com/mikhaelfelian
 * Description: Controller for managing item agent prices with bulk editing and Excel import functionality.
 */
class Item_Agent extends BaseController
{
    protected $itemAgentModel;
    protected $itemModel;
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->itemAgentModel = new \App\Models\ItemAgentModel();
        $this->itemModel = new \App\Models\ItemModel();
        $this->userModel = new \App\Models\Builtin\UserModel();
    }

    public function index()
    {
        $this->data['title']           = 'Manajemen Harga Agen';
        $this->data['current_module']  = $this->currentModule;
        $this->data['msg']             = $this->session->getFlashdata('message');

        // Get all users (agents) for dropdown
        $this->data['agents'] = $this->userModel
            ->select('id_user as id, nama')
            ->findAll();

        return $this->view('item-agent-result', $this->data);
    }

    public function add()
    {
        $this->data['title'] = 'Form Item Agent';
        $this->data['current_module'] = $this->currentModule;
        $this->data['item_agent'] = [];
        $this->data['id'] = '';
        $this->data['message'] = '';

        // Get all items for dropdown
        $this->data['items'] = $this->itemModel
            ->select('item.*, item_brand.name as brand_name, item_category.category as category_name')
            ->join('item_brand', 'item_brand.id = item.brand_id', 'left')
            ->join('item_category', 'item_category.id = item.category_id', 'left')
            ->where('item.status', '1')
            ->findAll();

        // Get all users (agents) for dropdown
        $this->data['agents'] = $this->userModel
            ->select('id_user as id, nama as name')
            ->findAll();

        // AJAX/modal check
        $isAjax = $this->request->isAJAX() 
                  || $this->request->getHeader('X-Requested-With') !== null;
        $this->data['isModal'] = $isAjax;

        // if ($isAjax) {
        //     return view('themes/modern/item-agent-form', $this->data);
        // }

        return $this->view('item-agent-form', $this->data);
    }

    public function edit()
    {
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
            return redirect()->to('item-agent')->with('message', $message);
        }

        // Ambil data item agent
        $item_agent = $this->itemAgentModel->find($id);
        if (!$item_agent) {
            $message = 'Data tidak ditemukan';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('item-agent')->with('message', $message);
        }

        $this->data['title'] = 'Form Item Agent';
        $this->data['current_module'] = $this->currentModule;
        $this->data['item_agent'] = $item_agent;
        $this->data['id'] = $id;

        // Get all items for dropdown
        $this->data['items'] = $this->itemModel
            ->select('item.*, item_brand.name as brand_name, item_category.category as category_name')
            ->join('item_brand', 'item_brand.id = item.brand_id', 'left')
            ->join('item_category', 'item_category.id = item.category_id', 'left')
            ->where('item.status', '1')
            ->findAll();

        // Get all users (agents) for dropdown
        $this->data['agents'] = $this->userModel
            ->select('id_user as id, nama')
            ->findAll();

        // Cek AJAX/modal
        $isAjax = $this->request->isAJAX() 
                  || $this->request->getHeader('X-Requested-With') !== null;
        $this->data['isModal'] = $isAjax;

        if ($isAjax) {
            return $this->view('themes/modern/item-agent-form', $this->data);
        }
        return $this->view('item-agent-form', $this->data);
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak ditemukan']);
        }

        $result = $this->itemAgentModel->delete($id);
        $message = $result ? 'Data berhasil dihapus' : 'Data gagal dihapus';

        return $this->response->setJSON([
            'status' => $result ? 'success' : 'error',
            'message' => $message
        ]);
    }

    public function toggleStatus()
    {
        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');
        
        if (!$id || !in_array($status, ['0', '1'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID atau status tidak valid'
            ]);
        }

        $result = $this->itemAgentModel->update($id, ['is_active' => $status]);
        $message = $result ? 'Status berhasil diubah' : 'Status gagal diubah';

        return $this->response->setJSON([
            'status' => $result ? 'success' : 'error',
            'message' => $message
        ]);
    }

    public function getItemAgentDT()
    {
        $search     = $this->request->getPost('search')['value'] ?? '';
        $start      = $this->request->getPost('start') ?? 0;
        $length     = $this->request->getPost('length') ?? 10;
        $order      = $this->request->getPost('order')[0]['column'] ?? 0;
        $order_dir  = $this->request->getPost('order')[0]['dir'] ?? 'asc';
        $columns    = $this->request->getPost('columns');
        $column_order = $columns[$order]['data'] ?? 'name';
        $agent_id   = $this->request->getPost('agent_id');

        // Get total records without joins
        $recordsTotal = $this->itemModel
            ->where('status', '1')
            ->countAllResults();

        // Build query for filtered count
        $query = $this->itemModel
            ->select('item.*, item_brand.name as brand_name, item_category.category as category_name')
            ->join('item_brand', 'item_brand.id = item.brand_id', 'left')
            ->join('item_category', 'item_category.id = item.category_id', 'left')
            ->where('item.status', '1');

        if (!empty($search)) {
            $query->groupStart()
                ->like('item.name', $search)
                ->orLike('item_brand.name', $search)
                ->orLike('item_category.category', $search)
            ->groupEnd();
        }

        $recordsFiltered = $query->countAllResults();

        // Build query for data
        $dataQuery = $this->itemModel
            ->select('item.*, item_brand.name as brand_name, item_category.category as category_name')
            ->join('item_brand', 'item_brand.id = item.brand_id', 'left')
            ->join('item_category', 'item_category.id = item.category_id', 'left')
            ->where('item.status', '1');

        if (!empty($search)) {
            $dataQuery->groupStart()
                ->like('item.name', $search)
                ->orLike('item_brand.name', $search)
                ->orLike('item_category.category', $search)
            ->groupEnd();
        }

        $dataQuery->orderBy($column_order, $order_dir);
        $dataQuery->limit($length, $start);
        $items = $dataQuery->findAll();

        $data = [];
        $no = ($start ?? 0) + 1;
        
        foreach ($items as $item) {
            // Get item agent data if agent_id is provided
            $itemAgent = null;
            if ($agent_id) {
                $itemAgent = $this->itemAgentModel
                    ->where(['item_id' => $item->id, 'user_id' => $agent_id])
                    ->first();
            }
            
            $checked = $itemAgent && $itemAgent->is_active == '1' ? 'checked=""' : '';
            $status = '<div class="form-switch">
                        <input name="aktif" type="checkbox" class="form-check-input switch" data-module-id="' . ($itemAgent ? $itemAgent->id : $item->id) . '" ' . $checked . '>
                      </div>';
            
            $agentPrice = $itemAgent ? 'Rp ' . number_format($itemAgent->price, 0, ',', '.') : 'Rp 0';
            
            $data[] = [
                'ignore_search_urut' => $no++,
                'name' => $item->name,
                'price' => 'Rp ' . number_format($item->price, 0, ',', '.'),
                'agent_price' => $agentPrice,
                'status' => $status,
                'ignore_search_action' => '
                    <button class="btn btn-sm btn-primary btn-edit rounded-0" data-id="' . ($itemAgent ? $itemAgent->id : $item->id) . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger btn-delete rounded-0" data-id="' . ($itemAgent ? $itemAgent->id : $item->id) . '" data-name="' . $item->name . '">
                        <i class="fa fa-trash"></i>
                    </button>
                '
            ];
        }

        $output = [
            "draw"            => $this->request->getPost('draw'),
            "recordsTotal"    => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data"            => $data,
        ];

        return $this->response->setJSON($output);
    }

    public function store()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'item_id' => 'required|integer|is_natural_no_zero',
            'user_id' => 'required|integer|is_natural_no_zero',
            'price' => 'required|decimal|greater_than_equal_to[0]'
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validation->getErrors()
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $id = $this->request->getPost('id');
        $item_id = $this->request->getPost('item_id');
        $user_id = $this->request->getPost('user_id');
        $price = str_replace('.', '', $this->request->getPost('price'));
        $price = str_replace(',', '.', $price);
        $price = (float) $price;
        $is_active = $this->request->getPost('is_active') ? 1 : 0;
        $notes = $this->request->getPost('notes');

        $data = [
            'item_id' => $item_id,
            'user_id' => $user_id,
            'price' => $price,
            'is_active' => $is_active
        ];

        // Check if combination already exists (for new records)
        if (!$id) {
            $existing = $this->itemAgentModel
                ->where(['item_id' => $item_id, 'user_id' => $user_id])
                ->first();
            
            if ($existing) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Kombinasi produk dan agen sudah ada'
                    ]);
                }
                return redirect()->back()->withInput()->with('message', 'Kombinasi produk dan agen sudah ada');
            }
        }

        $result = false;
        if ($id) {
            // Update existing record
            $result = $this->itemAgentModel->update($id, $data);
            $message = $result ? 'Data berhasil diupdate' : 'Data gagal diupdate';
        } else {
            // Insert new record
            $result = $this->itemAgentModel->insert($data);
            $message = $result ? 'Data berhasil disimpan' : 'Data gagal disimpan';
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $message
            ]);
        }

        return redirect()->to('item-agent')->with('message', $message);
    }

    public function upload()
    {
        $this->data['title']          = 'Upload Harga Agen';
        $this->data['current_module'] = $this->currentModule;
        $this->data['msg']            = $this->session->getFlashdata('message');

        if ($this->request->getMethod() === 'post') {
            $validation = \Config\Services::validation();

            $rules = [
                'excel_file' => [
                    'uploaded[excel_file]',
                    'mime_in[excel_file,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv]',
                    'max_size[excel_file,2048]',
                ],
                'agent_id'   => 'required',
            ];

            if (!$this->validate($rules)) {
                $this->data['errors'] = $validation->getErrors();
            } else {
                $file     = $this->request->getFile('excel_file');
                $agent_id = $this->request->getPost('agent_id');

                if ($file->isValid() && !$file->hasMoved()) {
                    $db = \Config\Database::connect();
                    $db->transStart();

                    try {
                        $filePath      = $file->getTempName();
                        $fileExtension = $file->getClientExtension();

                        if (in_array($fileExtension, ['xls', 'xlsx'])) {
                            // Handle Excel files
                            $reader      = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
                            $spreadsheet = $reader->load($filePath);
                            $sheet       = $spreadsheet->getActiveSheet();
                            $highestRow  = $sheet->getHighestRow();

                            for ($row = 2; $row <= $highestRow; $row++) {
                                $product_name = $sheet->getCell('A' . $row)->getValue();
                                $agent_price  = $sheet->getCell('B' . $row)->getValue();
                                $status_text  = $sheet->getCell('C' . $row)->getValue();

                                $item = $this->itemModel->where('name', $product_name)->first();

                                if ($item) {
                                    $is_active = (strtolower($status_text) == 'aktif' || $status_text == '1') ? 1 : 0;

                                    $existing = $this->itemAgentModel
                                        ->where(['item_id' => $item->id, 'user_id' => $agent_id])
                                        ->first();

                                    $data = [
                                        'item_id'   => $item->id,
                                        'user_id'   => $agent_id,
                                        'price'     => (float) $agent_price,
                                        'is_active' => $is_active,
                                    ];

                                    if ($existing) {
                                        $this->itemAgentModel->update($existing->id, $data);
                                    } else {
                                        $this->itemAgentModel->insert($data);
                                    }
                                }
                            }
                        } elseif ($fileExtension == 'csv') {
                            // Handle CSV files
                            $handle = fopen($filePath, 'r');
                            $row    = 0;

                            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                                if ($row == 0) {
                                    $row++;
                                    continue; // Skip header
                                }

                                $product_name = $data[0];
                                $agent_price  = $data[1];
                                $status_text  = $data[2] ?? '';

                                $item = $this->itemModel->where('name', $product_name)->first();

                                if ($item) {
                                    $is_active = (strtolower($status_text) == 'aktif' || $status_text == '1') ? 1 : 0;

                                    $existing = $this->itemAgentModel
                                        ->where(['item_id' => $item->id, 'user_id' => $agent_id])
                                        ->first();

                                    $insertData = [
                                        'item_id'   => $item->id,
                                        'user_id'   => $agent_id,
                                        'price'     => (float) $agent_price,
                                        'is_active' => $is_active,
                                    ];

                                    if ($existing) {
                                        $this->itemAgentModel->update($existing->id, $insertData);
                                    } else {
                                        $this->itemAgentModel->insert($insertData);
                                    }
                                }
                                $row++;
                            }
                            fclose($handle);
                        }

                        $db->transComplete();

                        if ($db->transStatus()) {
                            $this->session->setFlashdata('success', 'Data harga agen berhasil diupload.');
                            return redirect()->to('item-agent');
                        } else {
                            $this->session->setFlashdata('error', 'Gagal mengupload data.');
                        }
                    } catch (\Exception $e) {
                        $db->transRollback();
                        $this->session->setFlashdata('error', 'Gagal mengupload data: ' . $e->getMessage());
                    }
                } else {
                    $this->session->setFlashdata('error', 'File tidak valid atau gagal diupload.');
                }
            }
        }

        // Get agents for dropdown
        $this->data['agents'] = $this->userModel
            ->select('id_user as id, nama')
            ->findAll();

        return $this->view('item-agent-upload-form', $this->data);
    }
}
