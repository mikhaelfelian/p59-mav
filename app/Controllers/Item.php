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
    protected $itemAgentModel;
    protected $promoModel;
    protected $productRuleModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new \App\Models\ItemModel();
        $this->itemBrandModel = new \App\Models\ItemBrandModel();
        $this->itemCategoryModel = new \App\Models\ItemCategoryModel();
        $this->itemSpecModel = new \App\Models\ItemSpecModel();
        $this->itemSpecIdModel = new \App\Models\ItemSpecIdModel();
        $this->itemAgentModel = new \App\Models\ItemAgentModel();
        $this->promoModel = new \App\Models\ProductPromoRuleModel();
        $this->productRuleModel = new \App\Models\ProductRuleModel();
        helper('angka');
        
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
        $this->data['message']          = $this->session->getFlashdata('message') ?? '';

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
        // For promo tab dropdowns & agent price tab
        $this->data['items']            = $this->model->select('id, name')->where('status','1')->findAll();
        // Agents for agent price tab
        try {
            $agentModel = new \App\Models\AgentModel();
            $this->data['agents'] = $agentModel->where('is_active','1')->findAll();
        } catch (\Throwable $e) {
            $this->data['agents'] = [];
        }

        // Image data for filepicker
        $this->data['image']            = [];
        // Product rule data (1-to-1 relationship)
        $this->data['product_rule']     = null;

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
        $this->data['message']         = $this->session->getFlashdata('message') ?? '';

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
        // For promo tab dropdowns & agent price tab
        $this->data['items']           = $this->model->select('id, name')->where('status','1')->findAll();
        try {
            $agentModel = new \App\Models\AgentModel();
            $this->data['agents'] = $agentModel->where('is_active','1')->findAll();
        } catch (\Throwable $e) {
            $this->data['agents'] = [];
        }

        // Spesifikasi untuk item ini
        $this->data['existing_specifications'] = $this->itemSpecIdModel->getSpecsForItem($id);

        // Gambar untuk filepicker
        $this->data['image'] = !empty($item->image) ? [
            'id_file_picker' => $item->image,
            'nama_file'      => $item->image
        ] : [];

        // Product rule data (1-to-1 relationship)
        $this->data['product_rule'] = $this->productRuleModel->getByItemId($id);

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
        // Separate variables for POST inputs
        $sku              = $this->request->getPost('sku');
        $name             = $this->request->getPost('name');
        $slug             = $this->request->getPost('slug');
        $description      = $this->request->getPost('description');
        $shortDescription = $this->request->getPost('short_description');
        $price            = $this->request->getPost('price');
        $agentPrice       = $this->request->getPost('agent_price');
        $brandId          = $this->request->getPost('brand_id');
        $categoryId       = $this->request->getPost('category_id');
        $isStockable      = $this->request->getPost('is_stockable') ?? '1';
        $isCatalog        = $this->request->getPost('is_catalog') ?? '1';
        $isAgen           = $this->request->getPost('is_agen') ?? '0';
        $status           = $this->request->getPost('status') ?? '1';
        $warranty         = $this->request->getPost('warranty');
        $id               = $this->request->getPost('id');

        // Enforce status to only allow '1' or '0'
        if ($status === null || ($status !== '1' && $status !== '0' && $status !== 1 && $status !== 0)) {
            $status = '1';
        } else {
            $status = (string)($status == '1' ? '1' : '0');
        }

        // Ensure schema supports agent_price to avoid silent drops when migration not run
        $this->ensureAgentPriceColumn();
        $validation = \Config\Services::validation();

        $rules = [
            'sku'         => 'permit_empty|max_length[50]',
            'name'        => 'required|max_length[100]',
            'slug'        => 'permit_empty|max_length[100]',
            'brand_id'    => 'required|integer|is_natural_no_zero',
            'category_id' => 'required|integer|is_natural_no_zero',
            // 'price'       => 'required|decimal',
            // 'agent_price' => 'permit_empty|decimal', // still optional, but price is required
            // 'is_stockable'  => 'permit_empty|in_list[0,1]',
            // 'is_catalog'    => 'permit_empty|in_list[0,1]',
            // 'status'        => 'permit_empty|in_list[0,1]'
        ];

        // Normalize price and agentPrice using angka helper before validation
        $form                = $this->request->getPost();

        // Format price fields before validation
        $form['price']       = isset($form['price']) ? format_angka_db((string) $form['price']) : null;
        $form['agent_price'] = isset($form['agent_price']) ? format_angka_db((string) $form['agent_price']) : null;

        // Override POST for validation process
        $this->request->setGlobal('post', $form);

        // Run validation
        if (!$this->validate($rules)) {
            $errors = $validation->getErrors();
            
            // Add manual error for price, if not numeric or empty or zero
            $showPriceError = false;
            if (!isset($form['price']) || $form['price'] === '' || !is_numeric($form['price']) || $form['price'] <= 0) {
                $errors['price'] = 'Harga harus diisi dan lebih dari 0';
                $showPriceError = true;
            }

            // Format error messages with field names
            $errorMessages = [];
            foreach ($errors as $field => $error) {
                $fieldLabel = str_replace(['_', 'id'], [' ', 'ID'], $field);
                $errorMessages[] = ucfirst($fieldLabel) . ': ' . $error;
            }
            $message = !empty($errorMessages) ? implode('<br>', $errorMessages) : 'Validasi gagal. Silakan periksa kembali data yang diinput.';

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message,
                    'errors'  => $errors
                ]);
            }

            return redirect()->back()->withInput()->with('message', $message);
        }

        // Final check if price is still empty or zero after above conversion, as last failsafe
        $price = $form['price'];
        if ($price === null || $price === '' || !is_numeric($price) || floatval($price) <= 0) {
            $message = 'Harga item wajib diisi dan harus lebih dari 0';
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message,
                    'errors'  => ['price' => $message]
                ]);
            }
            return redirect()->back()->withInput()->with('message', $message);
        }
        $price = floatval($price);
        $agentPrice = $form['agent_price'] !== null ? floatval($form['agent_price']) : 0;

        // Get file reference (will be uploaded after database save)
        $file = $this->request->getFile('image');
        $hasNewFile = $file && $file->isValid() && !$file->hasMoved();

        // Correct session retrieval to array, not string.
        $userSession = session('user');
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

            // Keep existing image initially (will be updated if new file uploaded)
            $image = $existingRecord->image ?? '';
            
            $data = [
                'user_id'           => $userId,
                'sku'               => $existingRecord->sku,
                'name'              => $name,
                'slug'              => $slug,
                'description'       => $description,
                'short_description' => $shortDescription,
                'image'             => $image,
                'price'             => $price,
                'agent_price'       => $agentPrice,
                'brand_id'          => $brandId,
                'category_id'       => $categoryId,
                'is_stockable'      => $isStockable,
                'is_catalog'        => $isCatalog,
                'is_agen'           => $isAgen,
                'status'            => $status,
                'warranty'          => !empty($warranty) ? (int)$warranty : null
            ];

            $result = false;
            $message = '';
            
            try {
                $this->model->skipValidation(true);
                $result = $this->model->update($id, $data);
                
                if (!$result) {
                    $modelErrors = $this->model->errors();
                    if (!empty($modelErrors)) {
                        $message = 'Validasi gagal: ' . implode(', ', array_values($modelErrors));
                    } else {
                        $db = \Config\Database::connect();
                        $updated = $db->table('item')->where('id', $id)->countAllResults();
                        if ($updated == 0) {
                            $message = 'Data tidak ditemukan untuk diupdate.';
                        } else {
                            $message = 'Data gagal diupdate. Tidak ada perubahan atau terjadi kesalahan.';
                        }
                    }
                } else {
                    try {
                        $db = \Config\Database::connect();
                        $db->table('item')->where('id', $id)->update(['agent_price' => $agentPrice]);
                    } catch (\Throwable $e) {
                        // warning suppressed
                    }
                    
                    // Handle file upload after database update
                    if ($hasNewFile) {
                        // Delete existing image if it exists
                        if (!empty($existingRecord->image)) {
                            // Handle both old format (filename only) and new format (item_id/filename)
                            $oldImage = $existingRecord->image;
                            if (strpos($oldImage, '/') !== false) {
                                // New format: item_id/filename
                                $oldImagePath = ROOTPATH . 'public/images/produk/' . $oldImage;
                            } else {
                                // Old format: just filename (in root produk directory)
                                $oldImagePath = ROOTPATH . 'public/images/produk/' . $oldImage;
                            }
                            if (file_exists($oldImagePath)) {
                                @unlink($oldImagePath);
                            }
                        }
                        
                        // Upload new file to item_id subdirectory
                        $newName = $file->getRandomName();
                        $uploadPath = ROOTPATH . 'public/images/produk/' . $id . '/';
                        $file->move($uploadPath, $newName);
                        
                        // Update database with new image path (relative: item_id/filename)
                        $imagePath = $id . '/' . $newName;
                        $this->model->update($id, ['image' => $imagePath]);
                    }
                    
                    $message = 'Data berhasil diupdate';
                }
            } catch (\Throwable $e) {
                $result = false;
                $message = 'Error saat update: ' . $e->getMessage();
            }

            // Handle specifications for update
            if ($result) {
                $specNames = $this->request->getPost('spec_name') ?? [];
                $specValues = $this->request->getPost('spec_value') ?? [];
                $this->itemSpecIdModel->where('item_id', $id)->delete();
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
            // Insert new record - generate new SKU (without image first)
            $data = [
                'user_id'           => $userId,
                'sku'               => $this->model->generateSku(),
                'name'              => $name,
                'slug'              => $slug,
                'description'       => $description,
                'short_description' => $shortDescription,
                'image'             => '', // Will be set after upload
                'price'             => $price,
                'agent_price'       => $agentPrice,
                'brand_id'          => $brandId,
                'category_id'       => $categoryId,
                'is_stockable'      => $isStockable,
                'is_catalog'        => $isCatalog,
                'is_agen'           => $isAgen,
                'status'            => $status,
                'warranty'          => !empty($warranty) ? (int)$warranty : null
            ];

            $result = false;
            $message = '';
            
            try {
                $this->model->skipValidation(true);
                $result = $this->model->save($data);
                
                if (!$result) {
                    $modelErrors = $this->model->errors();
                    if (!empty($modelErrors)) {
                        $message = 'Validasi gagal: ' . implode(', ', array_values($modelErrors));
                    } else {
                        $message = 'Data gagal disimpan. Silakan coba lagi atau periksa koneksi database.';
                    }
                } else {
                    $message = 'Data berhasil disimpan';
                }
            } catch (\Throwable $e) {
                $result = false;
                $message = 'Error saat insert: ' . $e->getMessage();
            }
            
            if ($result) {
                $itemId = $this->model->getInsertID();
                try {
                    $db = \Config\Database::connect();
                    $db->table('item')->where('id', $itemId)->update(['agent_price' => $agentPrice]);
                } catch (\Throwable $e) {
                    // ignore
                }
                
                // Handle file upload after database insert
                if ($hasNewFile) {
                    // Upload file to item_id subdirectory
                    $newName = $file->getRandomName();
                    $uploadPath = ROOTPATH . 'public/images/produk/' . $itemId . '/';
                    $file->move($uploadPath, $newName);
                    
                    // Update database with image path (relative: item_id/filename)
                    $imagePath = $itemId . '/' . $newName;
                    $this->model->update($itemId, ['image' => $imagePath]);
                }
                
                $specNames = $this->request->getPost('spec_name') ?? [];
                $specValues = $this->request->getPost('spec_value') ?? [];
                foreach ($specNames as $key => $specId) {
                    if (!empty($specId) && !empty($specValues[$key])) {
                        $specData = [
                            'item_id'       => $itemId,
                            'item_spec_id'  => $specId,
                            'user_id'       => $userId,
                            'value'         => $specValues[$key],
                        ];
                        $this->itemSpecIdModel->save($specData);
                    }
                }
            }
        }

        // Proper response handling, no debug output
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

    // Legacy product rules method removed
    private function ensureAgentPriceColumn(): void
    {
        try {
            $db = \Config\Database::connect();
            $fields = $db->getFieldNames('item');
            if (!in_array('agent_price', $fields)) {
                $forge = \Config\Database::forge();
                $forge->addColumn('item', [
                    'agent_price' => [
                        'type'       => 'DECIMAL',
                        'constraint' => '10,2',
                        'null'       => false,
                        'default'    => 0.00,
                        'after'      => 'price',
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            // Do not block save; if schema change fails, the model will simply ignore the field
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
            $action = '<div class="btn-group" role="group">';
                        
            // Detail button - always show if user has read permissions
            if ($this->hasPermissionPrefix('read')) {
                $action .= '<button class="btn btn-sm btn-info btn-detail rounded-0" data-id="' . $row->id . '" title="Detail">
                    <i class="fa fa-eye"></i>
                </button>';
            }

            // Edit button - check update permission using existing system
            if ($this->hasPermissionPrefix('update')) {
                $action .= '<button class="btn btn-sm btn-primary btn-edit rounded-0" data-id="' . $row->id . '">
                    <i class="fa fa-edit"></i>
                </button>';
            }
            
            // Delete button - check delete permission using existing system
            if ($this->hasPermissionPrefix('delete')) {
                $action .= '<button class="btn btn-sm btn-danger btn-delete rounded-0" data-id="' . $row->id . '" data-name="' . $row->name . '">
                    <i class="fa fa-trash"></i>
                </button>';
            }
            
            $action .= '</div>';

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

    /**
     * List agent prices for a specific item
     * No permission check - if user can view item, they can see agent prices
     */
    public function listAgentPrices($itemId)
    {
        try {
            $rows = $this->itemAgentModel
                ->select('item_agent.*, agent.name as agent_name, agent.code as agent_code')
                ->join('agent', 'agent.id = item_agent.user_id', 'left')
                ->where('item_agent.item_id', $itemId)
                ->orderBy('item_agent.created_at', 'DESC')
                ->findAll();
            
            return $this->response->setJSON([
                'status' => 'success', 
                'data' => $rows
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store agent price for an item
     * No permission check - same as main store() method
     * If user can edit item, they can manage agent prices
     */
    public function storeAgentPrice()
    {
        $validation = \Config\Services::validation();
        $rules = [
            'item_id' => 'required|integer|is_natural_no_zero',
            'user_id' => 'required|integer|is_natural_no_zero',
            'price'   => 'required|decimal|greater_than_equal_to[0]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Validasi gagal',
                'errors'  => $validation->getErrors()
            ]);
        }

        try {
            $item_id   = $this->request->getPost('item_id');
            $user_id   = $this->request->getPost('user_id');
            $priceRaw  = $this->request->getPost('price');
            $price     = format_angka_db((string)$priceRaw);
            $is_active = $this->request->getPost('is_active') ? 1 : 0;

            // Check if combination already exists
            $existing = $this->itemAgentModel
                ->where('item_id', $item_id)
                ->where('user_id', $user_id)
                ->first();

            if ($existing) {
                // Update existing
                $data = [
                    'price'     => $price,
                    'is_active' => $is_active
                ];
                $result = $this->itemAgentModel->update($existing->id, $data);
                $message = $result ? 'Data berhasil diupdate' : 'Data gagal diupdate';
            } else {
                // Insert new
                $data = [
                    'item_id'   => $item_id,
                    'user_id'   => $user_id,
                    'price'     => $price,
                    'is_active' => $is_active ?? 1
                ];
                $result = $this->itemAgentModel->save($data);
                $message = $result ? 'Data berhasil disimpan' : 'Data gagal disimpan';
            }

            return $this->response->setJSON([
                'status'  => $result ? 'success' : 'error',
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete agent price
     * No permission check - same as main store() method
     * If user can edit item, they can manage agent prices
     */
    public function deleteAgentPrice()
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'ID tidak ditemukan'
            ]);
        }

        try {
            $itemAgent = $this->itemAgentModel->find($id);
            if (!$itemAgent) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $result = $this->itemAgentModel->delete($id);
            $message = $result ? 'Data berhasil dihapus' : 'Data gagal dihapus';

            return $this->response->setJSON([
                'status'  => $result ? 'success' : 'error',
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * List promo rules for a specific item
     * If user can view item, they can see promo rules
     */
    public function promoList($itemId)
    {
        $this->response->setContentType('application/json');
        
        try {
            $rows = $this->promoModel
                ->select('item_promo_rule.*, i1.name AS item_name, i2.name AS bonus_name')
                ->join('item i1', 'i1.id = item_promo_rule.item_id', 'left')
                ->join('item i2', 'i2.id = item_promo_rule.bonus_item_id', 'left')
                ->where('item_promo_rule.item_id', $itemId)
                ->orderBy('item_promo_rule.created_at', 'DESC')
                ->findAll();
            
            return $this->response->setJSON([
                'status' => 'success', 
                'data' => $rows
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store or update promo rule
     * If user can edit item, they can manage promo rules
     */
    public function promoStore()
    {
        $this->response->setContentType('application/json');
        
        $id = $this->request->getPost('id');
        $data = [
            'item_id'       => $this->request->getPost('item_id'),
            'bonus_item_id' => $this->request->getPost('bonus_item_id'),
            'min_qty'       => $this->request->getPost('min_qty') ?: 1,
            'bonus_qty'     => $this->request->getPost('bonus_qty') ?: 1,
            'is_multiple'   => $this->request->getPost('is_multiple') ? 1 : 0,
            'start_date'    => $this->request->getPost('start_date') ?: null,
            'end_date'      => $this->request->getPost('end_date') ?: null,
            'status'        => $this->request->getPost('status') ?: 'active',
            'notes'         => $this->request->getPost('notes'),
        ];
        
        // Validate required fields
        if (empty($data['item_id'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Item ID is required']);
        }
        if (empty($data['bonus_item_id'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Produk Bonus harus dipilih']);
        }
        if (empty($data['min_qty']) || $data['min_qty'] <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Jumlah Minimum harus lebih dari 0']);
        }
        if (empty($data['bonus_qty']) || $data['bonus_qty'] <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Jumlah Bonus harus lebih dari 0']);
        }
        
        try {
            if ($id) {
                $result = $this->promoModel->update($id, $data);
                $message = $result ? 'Aturan promo berhasil diperbarui' : 'Gagal memperbarui aturan promo';
            } else {
                $result = $this->promoModel->insert($data);
                $message = $result ? 'Aturan promo berhasil disimpan' : 'Gagal menyimpan aturan promo';
            }
            
            if (!$result) {
                $errors = $this->promoModel->errors();
                return $this->response->setJSON([
                    'status' => 'error', 
                    'message' => $message,
                    'errors' => $errors
                ]);
            }
            
            return $this->response->setJSON([
                'status' => 'success', 
                'message' => $message,
                'id' => $result
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete promo rule
     * If user can edit item, they can manage promo rules
     */
    public function promoDelete($id)
    {
        $this->response->setContentType('application/json');
        
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'ID tidak ditemukan'
            ]);
        }
        
        try {
            $result = $this->promoModel->delete($id);
            return $this->response->setJSON([
                'status' => $result ? 'success' : 'error',
                'message' => $result ? 'Data berhasil dihapus' : 'Gagal menghapus data'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save or update product rule
     * Handles both cashback and buy_get rule types
     * 
     * @param int $itemId Item ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function saveProductRule($itemId)
    {
        $this->response->setContentType('application/json');
        
        if (!$itemId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Item ID is required'
            ]);
        }

        // Verify item exists
        $item = $this->model->find($itemId);
        if (!$item) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Item tidak ditemukan'
            ]);
        }

        $postData = $this->request->getPost();
        $ruleType = $postData['rule_type'] ?? '';
        
        // Validate rule type
        if (!in_array($ruleType, ['cashback', 'buy_get'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Rule type harus cashback atau buy_get'
            ]);
        }

        // Prepare data based on rule type
        $data = [
            'item_id' => $itemId,
            'rule_type' => $ruleType,
            'is_active' => isset($postData['is_active']) ? (int)$postData['is_active'] : 1,
            'notes' => $postData['notes'] ?? null
        ];

        // Cashback rule specific fields
        if ($ruleType === 'cashback') {
            $data['threshold_amount'] = !empty($postData['threshold_amount']) ? (float)$postData['threshold_amount'] : null;
            $data['cashback_amount'] = !empty($postData['cashback_amount']) ? (float)$postData['cashback_amount'] : null;
            
            // Validate cashback fields
            if (empty($data['threshold_amount']) || $data['threshold_amount'] <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Threshold Amount harus diisi dan lebih dari 0 untuk cashback rule'
                ]);
            }
            if (empty($data['cashback_amount']) || $data['cashback_amount'] <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Cashback Amount harus diisi dan lebih dari 0 untuk cashback rule'
                ]);
            }
        }

        // Buy Get rule specific fields
        if ($ruleType === 'buy_get') {
            $data['min_qty'] = !empty($postData['min_qty']) ? (int)$postData['min_qty'] : null;
            $data['bonus_item_id'] = !empty($postData['bonus_item_id']) ? (int)$postData['bonus_item_id'] : null;
            $data['bonus_qty'] = !empty($postData['bonus_qty']) ? (int)$postData['bonus_qty'] : null;
            $data['is_multiple'] = isset($postData['is_multiple']) ? (int)$postData['is_multiple'] : 0;
            
            // Validate buy_get fields
            if (empty($data['min_qty']) || $data['min_qty'] <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Minimum Quantity harus diisi dan lebih dari 0 untuk buy_get rule'
                ]);
            }
            if (empty($data['bonus_item_id'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Bonus Item harus dipilih untuk buy_get rule'
                ]);
            }
            if (empty($data['bonus_qty']) || $data['bonus_qty'] <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Bonus Quantity harus diisi dan lebih dari 0 untuk buy_get rule'
                ]);
            }
        }

        try {
            // Check if rule already exists for this item (1-to-1 relationship)
            $existingRule = $this->productRuleModel->getByItemId($itemId);
            
            if ($existingRule) {
                // Update existing rule
                $result = $this->productRuleModel->update($existingRule->id, $data);
                $message = $result ? 'Product rule berhasil diperbarui' : 'Gagal memperbarui product rule';
                $ruleId = $existingRule->id;
            } else {
                // Insert new rule
                $result = $this->productRuleModel->insert($data);
                $message = $result ? 'Product rule berhasil disimpan' : 'Gagal menyimpan product rule';
                $ruleId = $result ? $this->productRuleModel->getInsertID() : null;
            }
            
            if (!$result) {
                $errors = $this->productRuleModel->errors();
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $message,
                    'errors' => $errors
                ]);
            }
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'id' => $ruleId,
                    'rule_type' => $ruleType
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ProductRule save error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
