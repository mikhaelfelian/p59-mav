<?php

namespace App\Controllers;

use App\Models\UserRoleAgentModel;

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
    protected $agentModel;
    protected $userRoleAgentModel;

    public function __construct()
    {
        parent::__construct();
        $this->itemAgentModel = new \App\Models\ItemAgentModel();
        $this->itemModel = new \App\Models\ItemModel();
        $this->agentModel = new \App\Models\AgentModel();
        $this->userRoleAgentModel = new UserRoleAgentModel();
    }

    public function index()
    {
        // Check read permissions
        if (!$this->hasPermissionPrefix('read')) {
            return $this->view('errors/403', [
                'title'   => 'Access Denied',
                'message' => 'You do not have permission to access this module.'
            ]);
        }

        $this->data['title']          = 'Manajemen Harga Agen';
        $this->data['current_module'] = $this->currentModule;
        $this->data['msg']            = $this->session->getFlashdata('message');

        // Get agents based on user permissions using existing RBAC system
        if (in_array('read_all', $this->userPermission) || !in_array('read_own', $this->userPermission)) {
            // User has read_all or no specific read_own restriction
            $this->data['agents'] = $this->agentModel
                ->select('agent.id, agent.code, agent.name as agent, agent.is_active')
                ->join('user_role_agent', 'user_role_agent.agent_id = agent.id')
                ->join('user', 'user.id_user = user_role_agent.user_id')
                ->where('agent.is_active', '1')
                ->groupBy('agent.id')
                ->findAll();
        } else {
            // User has read_own but not read_all - only show agents they are assigned to
            $userId = $this->user['id_user'];
            $this->data['agents'] = $this->agentModel
                ->select('agent.id, agent.code, agent.name as agent, agent.is_active')
                ->join('user_role_agent', 'user_role_agent.agent_id = agent.id')
                ->join('user', 'user.id_user = user_role_agent.user_id')
                ->where('user_role_agent.user_id', $userId)
                ->where('agent.is_active', '1')
                ->groupBy('agent.id')
                ->findAll();
        }

        return $this->view('item-agent-result', $this->data);
    }

    public function add()
    {
        $this->data['title']          = 'Form Item Agent';
        $this->data['current_module'] = $this->currentModule;
        $this->data['item_agent']     = [];
        $this->data['id']             = '';
        $this->data['message']        = '';

        // Get all items for dropdown
        $this->data['items'] = $this->itemModel
            ->select('item.*, item_brand.name as brand_name, item_category.category as category_name')
            ->join('item_brand', 'item_brand.id = item.brand_id', 'left')
            ->join('item_category', 'item_category.id = item.category_id', 'left')
            ->where('item.status', '1')
            ->findAll();

        // Get agents for dropdown
        $this->data['agents'] = $this->agentModel
            ->select('agent.id, agent.code, agent.name as agent, agent.is_active')
            ->join('user_role_agent', 'user_role_agent.agent_id = agent.id')
            ->join('user', 'user.id_user = user_role_agent.user_id')
            ->where('agent.is_active', '1')
            ->groupBy('agent.id')
            ->findAll();

        // AJAX/modal check
        $isAjax                  = $this->request->isAJAX() || $this->request->getHeader('X-Requested-With') !== null;
        $this->data['isModal']   = $isAjax;

        return $this->view('item-agent-form', $this->data);
    }

    public function edit()
    {
        $id = $this->request->getGet('id');

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

        $this->data['title']          = 'Form Item Agent';
        $this->data['current_module'] = $this->currentModule;
        $this->data['item_agent']     = $item_agent;
        $this->data['id']             = $id;

        $this->data['items'] = $this->itemModel
            ->select('item.*, item_brand.name as brand_name, item_category.category as category_name')
            ->join('item_brand', 'item_brand.id = item.brand_id', 'left')
            ->join('item_category', 'item_category.id = item.category_id', 'left')
            ->where('item.status', '1')
            ->findAll();

        $this->data['agents'] = $this->agentModel
            ->select('agent.id, agent.code, agent.name as agent, agent.is_active')
            ->join('user_role_agent', 'user_role_agent.agent_id = agent.id')
            ->join('user', 'user.id_user = user_role_agent.user_id')
            ->where('agent.is_active', '1')
            ->groupBy('agent.id')
            ->findAll();

        $isAjax                = $this->request->isAJAX() || $this->request->getHeader('X-Requested-With') !== null;
        $this->data['isModal'] = $isAjax;

        if ($isAjax) {
            return $this->view('themes/modern/item-agent-form', $this->data);
        }
        return $this->view('item-agent-form', $this->data);
    }

    /**
     * Store item agent data for add, edit, and bulk/batch overwrite.
     */
    public function store()
    {
        // Permission check
        if (!$this->hasPermissionPrefix('write')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'You do not have permission to save item-agent data.'
                ]);
            }
            return redirect()->to('item-agent')->with('message', 'You do not have permission to save item-agent data.');
        }

        // If batch store (bulk insert for agent's item list)
        $agentId     = $this->request->getPost('agent_id');
        $itemIds     = $this->request->getPost('item_id');
        $agentPrices = $this->request->getPost('agent_price');
        $isActive    = $this->request->getPost('is_active');
        $notes       = $this->request->getPost('notes');

        $isBulkForm  = is_array($itemIds) && is_array($agentPrices);

        if ($isBulkForm) {
            // --- BULK STORE (overwrite agent's items) ---
            // Write_own permission check
            if (!empty($agentId) && in_array('write_own', $this->userPermission) && !in_array('write_all', $this->userPermission)) {
                $userId         = $this->user['id_user'];
                $userRoleAgent  = $this->userRoleAgentModel->where('user_id', $userId)
                                                           ->where('agent_id', $agentId)
                                                           ->first();
                if (!$userRoleAgent) {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON([
                            'status'  => 'error',
                            'message' => 'You can only modify data for agents you are assigned to.'
                        ]);
                    }
                    return redirect()->to('item-agent')->with('message', 'You can only modify data for agents you are assigned to.');
                }
            }

            if (empty($itemIds) || empty($agentId)) {
                $message = 'Data tidak lengkap';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->to('item-agent/add')->with('message', $message);
            }

            $db = \Config\Database::connect();
            $db->transStart();
            try {
                // Overwrite (delete old, insert all new)
                $this->itemAgentModel->where('agent_id', $agentId)->delete();

                foreach ($itemIds as $index => $itemId) {
                    if (!empty($itemId) && isset($agentPrices[$index]) && $agentPrices[$index] !== '') {
                        $data = [
                            'agent_id'   => $agentId,
                            'item_id'    => $itemId,
                            'price'      => (float) str_replace(['.', ','], ['', '.'], $agentPrices[$index]),
                            'is_active'  => isset($isActive[$index]) ? '1' : '0',
                            'notes'      => $notes[$index] ?? ''
                        ];
                        $this->itemAgentModel->insert($data);
                    }
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Database transaction failed');
                }

                $message = 'Data berhasil disimpan';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status'  => 'success',
                        'message' => $message
                    ]);
                }
                return redirect()->to('item-agent')->with('message', $message);

            } catch (\Exception $e) {
                $db->transRollback();
                $message = 'Gagal menyimpan data: ' . $e->getMessage();
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->to('item-agent/add')->with('message', $message);
            }

        } else {
            // --- SINGLE ROW STORE (edit or add) ---
            $validation = \Config\Services::validation();
            $rules = [
                'item_id' => 'required|integer|is_natural_no_zero',
                'user_id' => 'required|integer|is_natural_no_zero',
                'price'   => 'required|decimal|greater_than_equal_to[0]'
            ];

            if (!$this->validate($rules)) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => 'Validasi gagal',
                        'errors'  => $validation->getErrors()
                    ]);
                }
                return redirect()->back()->withInput()->with('errors', $validation->getErrors());
            }

            $id        = $this->request->getPost('id');
            $item_id   = $this->request->getPost('item_id');
            $user_id   = $this->request->getPost('user_id');
            $priceRaw  = $this->request->getPost('price');
            $price     = (float) str_replace(['.', ','], ['', '.'], $priceRaw);
            $is_active = $this->request->getPost('is_active') ? 1 : 0;
            $notes     = $this->request->getPost('notes');

            $data = [
                'item_id'   => $item_id,
                'user_id'   => $user_id,
                'price'     => $price,
                'is_active' => $is_active,
                'notes'     => $notes,
            ];

            // Check combination uniqueness (if inserting)
            if (!$id) {
                $existing = $this->itemAgentModel
                    ->where(['item_id' => $item_id, 'user_id' => $user_id])
                    ->first();

                if ($existing) {
                    $msg = 'Kombinasi produk dan agen sudah ada';
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON([
                            'status'  => 'error',
                            'message' => $msg
                        ]);
                    }
                    return redirect()->back()->withInput()->with('message', $msg);
                }
            }

            if ($id) {
                // Update
                $result  = $this->itemAgentModel->update($id, $data);
                $message = $result ? 'Data berhasil diupdate' : 'Data gagal diupdate';
            } else {
                // Insert
                $result  = $this->itemAgentModel->insert($data);
                $message = $result ? 'Data berhasil disimpan' : 'Data gagal disimpan';
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => $result ? 'success' : 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('item-agent')->with('message', $message);
        }
    }

    public function delete()
    {
        if (!$this->hasPermissionPrefix('delete')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'You do not have permission to delete item-agent data.'
            ]);
        }

        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'ID tidak ditemukan'
            ]);
        }

        $itemAgent = $this->itemAgentModel->find($id);
        if (!$itemAgent) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Data tidak ditemukan'
            ]);
        }

        // Check permissions for delete_own vs delete_all
        if (in_array('delete_own', $this->userPermission) && !in_array('delete_all', $this->userPermission)) {
            $userId        = $this->user['id_user'];
            $userRoleAgent = $this->userRoleAgentModel->where('user_id', $userId)
                                                      ->where('agent_id', $itemAgent->agent_id)
                                                      ->first();
            if (!$userRoleAgent) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'You can only delete data for agents you are assigned to.'
                ]);
            }
        }

        $result  = $this->itemAgentModel->delete($id);
        $message = $result ? 'Data berhasil dihapus' : 'Data gagal dihapus';

        return $this->response->setJSON([
            'status'  => $result ? 'success' : 'error',
            'message' => $message
        ]);
    }

    public function toggleStatus()
    {
        $id     = $this->request->getPost('id');
        $status = $this->request->getPost('status');

        if (!$id || !in_array($status, ['0', '1'])) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'ID atau status tidak valid'
            ]);
        }

        $result  = $this->itemAgentModel->update($id, ['is_active' => $status]);
        $message = $result ? 'Status berhasil diubah' : 'Status gagal diubah';

        return $this->response->setJSON([
            'status'  => $result ? 'success' : 'error',
            'message' => $message
        ]);
    }

    public function getItemAgentDT()
    {
        // Check read permissions using existing RBAC system
        if (!$this->hasPermissionPrefix('read')) {
            return $this->response->setJSON([
                'draw'            => intval($this->request->getPost('draw')),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'You do not have permission to view item-agent data.'
            ]);
        }

        $search       = $this->request->getPost('search')['value'] ?? '';
        $start        = $this->request->getPost('start') ?? 0;
        $length       = $this->request->getPost('length') ?? 10;
        $order        = $this->request->getPost('order')[0]['column'] ?? 0;
        $order_dir    = $this->request->getPost('order')[0]['dir'] ?? 'asc';
        $columns      = $this->request->getPost('columns');
        $column_order = $columns[$order]['data'] ?? 'name';
        $agent_id     = $this->request->getPost('agent_id');

        // Check agent access permissions
        if (!empty($agent_id)) {
            // If user has read_own but not read_all, check if they can access this specific agent
            if (in_array('read_own', $this->userPermission) && !in_array('read_all', $this->userPermission)) {
                $userId = $this->user['id_user'];
                $userRoleAgent = $this->userRoleAgentModel->where('user_id', $userId)
                                                          ->where('agent_id', $agent_id)
                                                          ->first();
                if (!$userRoleAgent) {
                    return $this->response->setJSON([
                        'draw'            => intval($this->request->getPost('draw')),
                        'recordsTotal'    => 0,
                        'recordsFiltered' => 0,
                        'data'            => [],
                        'error'           => 'You do not have permission to access this agent.'
                    ]);
                }
            }
        }

        $recordsTotal = $this->itemModel
            ->where('status', '1')
            ->countAllResults();

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
        $no   = ($start ?? 0) + 1;

        foreach ($items as $item) {
            $itemAgent = null;
            if ($agent_id) {
                $itemAgent = $this->itemAgentModel
                    ->where(['item_id' => $item->id, 'user_id' => $agent_id])
                    ->first();
            }

            $checked    = $itemAgent && $itemAgent->is_active == '1' ? 'checked=""' : '';
            $status     = '<div class="form-switch">
                               <input name="aktif" type="checkbox" class="form-check-input switch" data-module-id="' . ($itemAgent ? $itemAgent->id : $item->id) . '" ' . $checked . '>
                           </div>';
            $agentPrice = $itemAgent ? 'Rp ' . number_format($itemAgent->price, 0, ',', '.') : 'Rp 0';

            $action = '';
            if ($this->hasPermissionPrefix('write')) {
                $action .= '<button class="btn btn-sm btn-primary btn-edit rounded-0" data-id="' . ($itemAgent ? $itemAgent->id : $item->id) . '">
                    <i class="fa fa-edit"></i></button>';
            }
            if ($this->hasPermissionPrefix('delete') && $itemAgent) {
                $action .= '<button class="btn btn-sm btn-danger btn-delete rounded-0" data-id="' . $itemAgent->id . '" data-name="' . $item->name . '">
                    <i class="fa fa-trash"></i></button>';
            }

            $data[] = [
                'ignore_search_urut'   => $no++,
                'name'                 => $item->name,
                'price'                => 'Rp ' . number_format($item->price, 0, ',', '.'),
                'agent_price'          => $agentPrice,
                'status'               => $status,
                'ignore_search_action' => $action
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
        $this->data['agents'] = $this->agentModel
            ->select('agent.id, agent.code, agent.name as agent, agent.is_active')
            ->join('user_role_agent', 'user_role_agent.agent_id = agent.id')
            ->join('user', 'user.id_user = user_role_agent.user_id')
            ->where('agent.is_active', '1')
            ->groupBy('agent.id')
            ->findAll();

        return $this->view('item-agent-upload-form', $this->data);
    }

    public function listByItem($itemId)
    {
        if (!$this->hasPermissionPrefix('read')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak memiliki izin.']);
        }
        $rows = $this->itemAgentModel
            ->select('item_agent.*, agent.name as agent_name, agent.code as agent_code')
            ->join('agent', 'agent.id = item_agent.user_id', 'left')
            ->where('item_agent.item_id', $itemId)
            ->orderBy('item_agent.created_at','DESC')
            ->findAll();
        return $this->response->setJSON(['status' => 'success', 'data' => $rows]);
    }
}
