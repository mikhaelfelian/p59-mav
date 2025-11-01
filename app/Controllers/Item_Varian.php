<?php

namespace App\Controllers;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Controller for managing item variants with CRUD operations
 * This file represents the Controller.
 */
class Item_Varian extends BaseController
{
    protected $model;
    protected $itemModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new \App\Models\ItemVarianModel();
        $this->itemModel = new \App\Models\ItemModel();
    }

    /**
     * Show form to add a new variant
     * 
     * @param int $item_id Item ID
     * @return string View content
     */
    public function add($item_id)
    {
        // Check permission
        if (!$this->hasPermissionPrefix('create', true)) {
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Tidak memiliki izin untuk menambahkan varian.</div>';
            }
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki izin untuk menambahkan data.'
            ]);
        }

        $data = [
            'item_id' => $item_id,
            'config' => $this->config
        ];

        try {
            $html = view('themes/modern/item/input_varian', $data);
            
            // For AJAX requests, return the HTML string directly
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return $html;
            }
            
            return $html;
        } catch (\Exception $e) {
            log_message('error', 'Item_Varian::add error: ' . $e->getMessage());
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Error loading variant form: ' . esc($e->getMessage()) . '</div>';
            }
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error loading variant form: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display all variants associated with the specified item
     * 
     * @param int $item_id Item ID
     * @return string View content
     */
    public function index($item_id)
    {
        // Check if user has permission (use return=true to avoid exit)
        if (!$this->hasPermissionPrefix('read', true)) {
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Tidak memiliki izin untuk melihat data varian.</div>';
            }
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki izin untuk melihat data.'
            ]);
        }

        // Ensure userPermission is set
        if (empty($this->userPermission)) {
            $this->userPermission = [];
        }

        $data = [
            'item_id' => $item_id,
            'config' => $this->config
        ];

        try {
            $html = view('themes/modern/item/input_varian', $data);
            
            // For AJAX requests, return the HTML string directly
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return $html;
            }
            
            return $html;
        } catch (\Exception $e) {
            log_message('error', 'Item_Varian::index error: ' . $e->getMessage());
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Error loading variant form: ' . esc($e->getMessage()) . '</div>';
            }
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error loading variant form: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Add or update a variant
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function store()
    {
        // Check permission
        if (!$this->hasPermissionPrefix('create', true) && !$this->hasPermissionPrefix('update', true)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki izin untuk menyimpan data.'
            ]);
        }

        $id = $this->request->getPost('id');
        $itemId = $this->request->getPost('item_id');
        $variantName = $this->request->getPost('variant_name');
        $skuVariant = $this->request->getPost('sku_variant');
        $stock = $this->request->getPost('stock') ?? 0;
        $price = $this->request->getPost('price') ?? 0.00;

        // Validate required fields
        if (empty($itemId) || empty($variantName) || empty($skuVariant)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Item ID, Nama Varian, dan SKU Varian harus diisi.'
            ]);
        }

        // Check if SKU variant already exists (for new records or if SKU changed)
        if (empty($id)) {
            // New record
            if ($this->model->skuVariantExists($skuVariant)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'SKU Varian sudah terdaftar.'
                ]);
            }
        } else {
            // Update - check if SKU changed and if new SKU exists
            $existing = $this->model->find($id);
            if ($existing && $existing['sku_variant'] != $skuVariant && $this->model->skuVariantExists($skuVariant)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'SKU Varian sudah terdaftar.'
                ]);
            }
        }

        $data = [
            'item_id' => $itemId,
            'variant_name' => $variantName,
            'sku_variant' => $skuVariant,
            'stock' => (int)$stock,
            'price' => (float)$price
        ];

        if (empty($id)) {
            // Insert new variant
            if ($this->model->insert($data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Varian berhasil ditambahkan.',
                    'data' => $this->model->find($this->model->getInsertID())
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal menambahkan varian: ' . implode(', ', $this->model->errors())
                ]);
            }
        } else {
            // Update existing variant
            if ($this->model->update($id, $data)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Varian berhasil diupdate.',
                    'data' => $this->model->find($id)
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengupdate varian: ' . implode(', ', $this->model->errors())
                ]);
            }
        }
    }

    /**
     * Delete a specific variant
     * 
     * @param int $id Variant ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete($id)
    {
        // Check permission
        if (!$this->hasPermissionPrefix('delete', true)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki izin untuk menghapus data.'
            ]);
        }

        // Check if variant exists
        $variant = $this->model->find($id);
        if (!$variant) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data varian tidak ditemukan.'
            ]);
        }

        // Check if variant has associated SNs
        $snModel = new \App\Models\ItemSnModel();
        $snCount = $snModel->where('variant_id', $id)->countAllResults();
        if ($snCount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Varian tidak dapat dihapus karena memiliki ' . $snCount . ' Serial Number yang terkait. Hapus SN terlebih dahulu.'
            ]);
        }

        if ($this->model->delete($id)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Varian berhasil dihapus.'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus varian: ' . implode(', ', $this->model->errors())
            ]);
        }
    }

    /**
     * Return JSON data for AJAX table refresh
     * 
     * @param int $item_id Item ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getByItem($item_id)
    {
        // Check permission
        if (!$this->hasPermissionPrefix('read', true)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki izin untuk melihat data.',
                'data' => []
            ]);
        }

        if (empty($item_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Item ID tidak valid.',
                'data' => []
            ]);
        }

        $variants = $this->model->getByItem($item_id);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => '',
            'data' => $variants
        ]);
    }
}

