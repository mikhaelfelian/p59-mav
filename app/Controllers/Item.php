<?php

namespace App\Controllers;


/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Controller for managing items with CRUD operations and dynamic specifications
 * This file represents the Controller.
 */
class Item extends BaseController
{
    protected $model;
    protected $itemBrandModel;
    protected $itemCategoryModel;
    protected $itemSpecModel;
    protected $itemSpecIdModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new \App\Models\ItemModel();
        $this->itemBrandModel = new \App\Models\ItemBrandModel();
        $this->itemCategoryModel = new \App\Models\ItemCategoryModel();
        $this->itemSpecModel = new \App\Models\ItemSpecModel();
        $this->itemSpecIdModel = new \App\Models\ItemSpecIdModel();
        
        // Add TinyMCE, DataTables and form scripts
        $this->addJs($this->config->baseURL . 'public/vendors/tinymce/tinymce.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js');
        $this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css');
        $this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/dist/js/jquery.dataTables.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/dist/js/dataTables.bootstrap5.min.js');
        $this->addStyle($this->config->baseURL . 'public/vendors/datatables/dist/css/dataTables.bootstrap5.min.css');
        $this->addJs($this->config->baseURL . 'public/themes/modern/js/item-form.js');
    }


    public function index()
    {
        $this->data['title'] = 'Item Management';
        $this->data['current_module'] = $this->currentModule;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        return $this->view('item-result', $this->data);
    }

    public function add()
    {
        $this->data['title']            = 'Form Item';
        $this->data['current_module']   = $this->currentModule;
        $this->data['item']             = [];
        $this->data['id']               = '';
        $this->data['message']          = '';

        // Reference data for dropdowns
        $this->data['brands']           = $this->itemBrandModel
                                               ->where('status', '1')
                                               ->findAll();
        $this->data['categories']       = $this->itemCategoryModel
                                               ->where('status', '1')
                                               ->findAll();
        $this->data['specifications']   = $this->itemSpecModel
                                               ->where('status', '1')
                                               ->findAll();

        // Image data for filepicker
        $this->data['image']            = [];

        // AJAX/modal check
        $isAjax                         = $this->request->isAJAX() 
                                         || $this->request->getHeader('X-Requested-With') !== null;
        $this->data['isModal']          = $isAjax;

        if ($isAjax) {
            return view('themes/modern/item-form', $this->data);
        }

        return $this->view('item-form', $this->data);
    }

    public function detail()
    {
        // Check read permissions
        if (!$this->hasPermissionPrefix('read')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'You do not have permission to view item details.'
                ]);
            }
            return $this->view('errors/403', [
                'title' => 'Access Denied',
                'message' => 'You do not have permission to view item details.'
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
            return redirect()->to('item')->with('message', $message);
        }

        // Ambil data item dengan join untuk brand dan category
        $item = $this->model->select('item.*, item_brand.name as brand_name, item_category.category as category_name')
                            ->join('item_brand', 'item_brand.id = item.brand_id', 'left')
                            ->join('item_category', 'item_category.id = item.category_id', 'left')
                            ->where('item.id', $id)
                            ->first();
        
        if (!$item) {
            $message = 'Data tidak ditemukan';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('item')->with('message', $message);
        }

        $this->data['title'] = 'Detail Item';
        $this->data['current_module'] = $this->currentModule;
        $this->data['item'] = $item;
        $this->data['id'] = $id;

        // Load specifications for this item
        $this->data['specifications'] = $this->itemSpecIdModel->getSpecsForItem($id);

        // Cek AJAX/modal
        $isAjax = $this->request->isAJAX() 
                  || $this->request->getHeader('X-Requested-With') !== null;
        
        if ($isAjax) {
            return view('themes/modern/item-detail', $this->data);
        } else {
            return $this->view('item-detail', $this->data);
        }
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
            return redirect()->to('item')->with('message', $message);
        }

        // Ambil data item
        $item = $this->model->find($id);
        if (!$item) {
            $message = 'Data tidak ditemukan';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('item')->with('message', $message);
        }

        $this->data['title']           = 'Form Item';
        $this->data['current_module']  = $this->currentModule;
        $this->data['item']            = $item;
        $this->data['id']              = $id;

        // Referensi dropdowns
        $this->data['brands']          = $this->itemBrandModel
                                            ->where('status', '1')
                                            ->findAll();
        $this->data['categories']      = $this->itemCategoryModel
                                            ->where('status', '1')
                                            ->findAll();
        $this->data['specifications']  = $this->itemSpecModel
                                            ->where('status', '1')
                                            ->findAll();

        // Spesifikasi untuk item ini
        $this->data['existing_specifications'] = $this->itemSpecIdModel->getSpecsForItem($id);

        // Gambar untuk filepicker
        $this->data['image'] = !empty($item->image) ? [
            'id_file_picker' => $item->image,
            'nama_file'      => $item->image
        ] : [];

        // Cek AJAX/modal
        $isAjax = $this->request->isAJAX() 
                  || $this->request->getHeader('X-Requested-With') !== null;
        $this->data['isModal'] = $isAjax;

        if ($isAjax) {
            return view('themes/modern/item-form', $this->data);
        }
        return $this->view('item-form', $this->data);
    }

    public function store()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'sku'           => 'permit_empty|max_length[50]',
            'name'          => 'required|max_length[100]',
            'slug'          => 'permit_empty|max_length[100]',
            'brand_id'      => 'required|integer|is_natural_no_zero',
            'category_id'   => 'required|integer|is_natural_no_zero',
            'price'         => 'permit_empty|decimal',
            'is_stockable'  => 'in_list[0,1]',
            'status'        => 'in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            $errors  = $validation->getErrors();
            $message = implode('<br>', $errors);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message
                ]);
            }

            return redirect()->back()->withInput()->with('message', $message);
        }

        // Separate variables for POST inputs
        $sku             = $this->request->getPost('sku');
        $name            = $this->request->getPost('name');
        $slug            = $this->request->getPost('slug');
        $description     = $this->request->getPost('description');
        $shortDescription= $this->request->getPost('short_description');
        $price           = $this->request->getPost('price');
        $brandId         = $this->request->getPost('brand_id');
        $categoryId      = $this->request->getPost('category_id');
        $isStockable     = $this->request->getPost('is_stockable') ?? '1';
        $status          = $this->request->getPost('status') ?? '1';
        $id              = $this->request->getPost('id');

        // Handle file upload
        $image = '';
        $file  = $this->request->getFile('image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/', $newName);
            $image = $newName;
        } elseif ($id) {
            // Keep existing image if no new file uploaded
            $existingRecord = $this->model->find($id);
            $image = $existingRecord ? $existingRecord->image : '';
        }

        // Correct session retrieval to array, not string.
        $userSession = session('user'); // Expecting this as array with 'id_user'
        if (!is_array($userSession) || !isset($userSession['id_user'])) {
            $message = 'User session tidak ditemukan, silakan login ulang.';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message
                ]);
            }
            return redirect()->to('login')->with('message', $message);
        }
        $userId = $userSession['id_user'];

        // Clean price value (remove formatting)
        $price = str_replace(['.', ','], '', $price);
        $price = $price ? (float)$price : 0;

        if ($id) {
            // Update existing record - keep existing SKU
            $existingRecord = $this->model->find($id);

            if (!$existingRecord) {
                $message = 'Data tidak ditemukan untuk update.';
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => $message
                    ]);
                }
                return redirect()->back()->with('message', $message);
            }

            $data = [
                'user_id'           => $userId,
                'sku'               => $existingRecord->sku, // Keep existing SKU
                'name'              => $name,
                'slug'              => $slug,
                'description'       => $description,
                'short_description' => $shortDescription,
                'image'             => $image,
                'price'             => $price,
                'brand_id'          => $brandId,
                'category_id'       => $categoryId,
                'is_stockable'      => $isStockable,
                'status'            => $status
            ];

            $result  = $this->model->update($id, $data);
            $message = $result ? 'Data berhasil diupdate' : 'Data gagal diupdate';
            
            // Handle specifications for update - save directly to ItemSpecIdModel
            if ($result) {
                // Get specification data from POST
                $specNames = $this->request->getPost('spec_name') ?? [];
                $specValues = $this->request->getPost('spec_value') ?? [];
                
                // Remove existing specifications for this item
                $this->itemSpecIdModel->where('item_id', $id)->delete();
                
                // Save new specifications directly to ItemSpecIdModel
                foreach ($specNames as $key => $specId) {
                    if (!empty($specId) && !empty($specValues[$key])) {
                        $specData = [
                            'item_id' => $id,
                            'item_spec_id' => $specId,
                            'user_id' => $userId,
                            'value' => $specValues[$key]
                        ];
                        $this->itemSpecIdModel->save($specData);
                    }
                }
            }

        } else {
            // Insert new record - generate new SKU
            $data = [
                'user_id'           => $userId,
                'sku'               => $this->model->generateSku(), // Auto-generate SKU
                'name'              => $name,
                'slug'              => $slug,
                'description'       => $description,
                'short_description' => $shortDescription,
                'image'             => $image,
                'price'             => $price,
                'brand_id'          => $brandId,
                'category_id'       => $categoryId,
                'is_stockable'      => $isStockable,
                'status'            => $status
            ];

            $result  = $this->model->save($data);
            $message = $result ? 'Data berhasil disimpan' : 'Data gagal disimpan';
            
            // Handle specifications for new item - save directly to ItemSpecIdModel
            if ($result) {
                $itemId = $this->model->getInsertID();
                
                // Get specification data from POST
                $specNames = $this->request->getPost('spec_name') ?? [];
                $specValues = $this->request->getPost('spec_value') ?? [];
                
                // Save specifications directly to ItemSpecIdModel
                foreach ($specNames as $key => $specId) {
                    if (!empty($specId) && !empty($specValues[$key])) {
                        $specData = [
                            'item_id' => $itemId,
                            'item_spec_id' => $specId,
                            'user_id' => $userId,
                            'value' => $specValues[$key]
                        ];
                        $this->itemSpecIdModel->save($specData);
                    }
                }
            }
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => $result ? 'success' : 'error',
                'message' => $message
            ]);
        }

        if ($result) {
            return redirect()->to('item')->with('message', $message);
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
            return redirect()->to('item')->with('message', $message);
        }

        $result = $this->model->delete($id);
        $message = $result ? 'Data berhasil dihapus' : 'Data gagal dihapus';

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $message
            ]);
        }

        return redirect()->to('item')->with('message', $message);
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
            return redirect()->to('item')->with('message', 'ID atau status tidak valid');
        }

        $result = $this->model->update($id, ['status' => $status]);
        $message = $result ? 'Status berhasil diubah' : 'Status gagal diubah';

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $message
            ]);
        }

        return redirect()->to('item')->with('message', $message);
    }

    public function getItemDT()
    {
        $request      = \Config\Services::request();
        $draw         = $request->getPost('draw');
        $start        = $request->getPost('start') ?? 0;
        $length       = $request->getPost('length') ?? 10;
        $searchValue  = $request->getPost('search')['value'] ?? '';
        $orderColumn  = $request->getPost('order')[0]['column'] ?? 0;
        $orderDir     = $request->getPost('order')[0]['dir'] ?? 'asc';

        // Build base query with joins
        $query = $this->model->select('item.*, item_brand.name AS brand_name, item_category.category AS category_name')
            ->join('item_brand', 'item_brand.id = item.brand_id', 'left')
            ->join('item_category', 'item_category.id = item.category_id', 'left');

        // Debug information
        $debugInfo = [];
        $debugInfo['userPermissions'] = $this->userPermission ?? [];
        $debugInfo['sessionUserId'] = $this->user['id_user'] ?? null;
        $debugInfo['hasReadPrefix'] = $this->hasPermissionPrefix('read');
        
        // Apply permission-based filtering using existing RBAC system
        if ($this->hasPermissionPrefix('read')) {
            // Check if user has read_own but not read_all (same logic as checkRoleAction)
            if (in_array('read_own', $this->userPermission) && !in_array('read_all', $this->userPermission)) {
                // User can only see items they created
                $userId = $this->user['id_user'];
                $debugInfo['applyingFilter'] = 'read_own filter for user_id: ' . $userId;
                $query->where('item.user_id', $userId);
            } else {
                $debugInfo['applyingFilter'] = 'No read_own filter - user has read_all or no read permissions';
            }
            // If user has read_all, no filtering is applied (shows all items)
        } else {
            $debugInfo['applyingFilter'] = 'User has no read permissions';
        }

        // Get total records (before search filter)
        $totalRecords = $query->countAllResults(false);

        // Apply search filter
        if (!empty($searchValue)) {
            $query->groupStart()
                ->like('item.name', $searchValue)
                ->orLike('item.sku', $searchValue)
                ->orLike('item_brand.name', $searchValue)
                ->orLike('item_category.category', $searchValue)
                ->groupEnd();
        }

        $filteredRecords = $query->countAllResults(false);

        // Get data with pagination
        $data = $query->orderBy('item.name', $orderDir)
            ->findAll($length, $start);
            
        // Debug: Query results
        $debugInfo['queryResults'] = count($data) . ' items returned';
        if (!empty($data)) {
            $debugInfo['firstItemUserId'] = $data[0]->user_id;
        }

        $result = [];
        $no     = $start + 1;

        foreach ($data as $row) {
            $checked = $row->status == '1' ? 'checked=""' : '';
            $status = '
                <div class="form-switch">
                    <input name="aktif" type="checkbox" class="form-check-input switch" data-module-id="' . $row->id . '" ' . $checked . '>
                </div>
            ';

            $stockable = $row->is_stockable == '1'
                ? '<span class="badge bg-success">Stockable</span>'
                : '<span class="badge bg-secondary">Non-Stockable</span>';

            // Build action buttons based on existing RBAC system
            $action = '';
            
            // Detail button - always show if user has read permissions
            if ($this->hasPermissionPrefix('read')) {
                $action .= '<button class="btn btn-sm btn-info btn-detail rounded-0" data-id="' . $row->id . '" title="Detail">
                    <i class="fa fa-eye"></i>
                </button>';
            }
            
            // Edit button - check write permission using existing system
            if ($this->hasPermissionPrefix('write')) {
                $action .= '<button type="button" class="btn btn-sm btn-warning btn-edit rounded-0" data-id="' . $row->id . '">
                    <i class="fa fa-edit"></i>
                </button>';
            }
            
            // Delete button - check delete permission using existing system
            if ($this->hasPermissionPrefix('delete')) {
                $action .= '<button type="button" class="btn btn-sm btn-danger btn-delete rounded-0" data-id="' . $row->id . '" data-name="' . $row->name . '">
                    <i class="fa fa-trash"></i>
                </button>';
            }

            $result[] = [
                'ignore_search_urut'    => $no++,
                'sku'                   => $row->sku,
                'name'                  => $row->name,
                'brand_name'            => $row->brand_name ?? '-',
                'category_name'         => $row->category_name ?? '-',
                'price'                 => format_angka($row->price, 0),
                'is_stockable'          => $stockable,
                'status'                => $status,
                'ignore_search_action'  => $action
            ];
        }

        return $this->response->setJSON([
            'draw'            => intval($draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $result,
            'debug'           => $debugInfo
        ]);
    }
}
