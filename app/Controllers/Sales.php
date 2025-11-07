<?php

/**
 * Sales Controller
 * 
 * Handles sales transaction management including:
 * - Creating new sales transactions
 * - Viewing sales details
 * - Listing sales with DataTables
 * - Managing serial numbers for items
 * 
 * @package    App\Controllers
 * @author     Mikhael Felian Waskito <mikhaelfelian@gmail.com>
 * @copyright  2025
 * @license    MIT
 * @version    1.0.0
 * @since      2025-11-01
 */

namespace App\Controllers;

use App\Models\SalesModel;
use App\Models\SalesItemsModel;
use App\Models\SalesItemSnModel;
use App\Models\SalesPaymentsModel;
use App\Models\SalesDetailModel;
use App\Models\ItemModel;
use App\Models\ItemSnModel;
use App\Models\AgentModel;
use App\Models\PlatformModel;
use App\Models\CustomerModel;
use CodeIgniter\HTTP\ResponseInterface;

class Sales extends BaseController
{
    /**
     * Model instances
     */
    protected $model;
    protected $salesItemsModel;
    protected $salesItemSnModel;
    protected $salesPaymentsModel;
    protected $salesDetailModel;
    protected $itemModel;
    protected $itemSnModel;
    protected $agentModel;
    protected $platformModel;
    protected $customerModel;

    /**
     * Sales status constants
     */
    protected const STATUS_PENDING = 'pending';
    protected const STATUS_COMPLETED = 'completed';
    protected const STATUS_CANCELLED = 'cancelled';

    /**
     * Sales channel constants
     */
    protected const CHANNEL_OFFLINE = '1';
    protected const CHANNEL_ONLINE = '2';

    /**
     * Initialize models and dependencies
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new SalesModel();
        $this->salesItemsModel = new SalesItemsModel();
        $this->salesItemSnModel = new SalesItemSnModel();
        $this->salesPaymentsModel = new SalesPaymentsModel();
        $this->salesDetailModel = new SalesDetailModel();
        $this->itemModel = new ItemModel();
        $this->itemSnModel = new ItemSnModel();
        $this->agentModel = new AgentModel();
        $this->platformModel = new PlatformModel();
        $this->customerModel = new CustomerModel();
    }

    /**
     * Display sales list page
     * 
     * @return void
     */
    public function index(): void
    {
        $this->data['title'] = 'Data Penjualan';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        $this->view('sales/sales-result', $this->data);
    }

    /**
     * Search customers by name (for autocomplete)
     * 
     * @return ResponseInterface
     */
    public function searchCustomers(): ResponseInterface
    {
        $searchTerm = $this->request->getGet('term') ?? '';
        
        if (empty($searchTerm) || strlen($searchTerm) < 2) {
            return $this->response->setJSON(['status' => 'success', 'data' => []]);
        }
        
        $customers = $this->customerModel
            ->groupStart()
                ->like('name', $searchTerm)
                ->orLike('phone', $searchTerm)
                ->orLike('plate_code', $searchTerm)
                ->orLike('plate_number', $searchTerm)
                ->orLike('plat_code', $searchTerm)
                ->orLike('plat_number', $searchTerm)
            ->groupEnd()
            ->orderBy('name', 'ASC')
            ->limit(20)
            ->findAll();

        $results = [];
        foreach ($customers as $customer) {
            // Use plat_* fields if available, otherwise fallback to plate_* fields
            $plateCode = !empty($customer['plat_code']) ? $customer['plat_code'] : ($customer['plate_code'] ?? '');
            $plateNumber = !empty($customer['plat_number']) ? $customer['plat_number'] : ($customer['plate_number'] ?? '');
            $plateSuffix = !empty($customer['plat_last']) ? $customer['plat_last'] : ($customer['plate_suffix'] ?? '');
            
            $results[] = [
                'id' => $customer['id'],
                'label' => $customer['name'],
                'value' => $customer['name'],
                'phone' => $customer['phone'] ?? '',
                'plate_code' => $plateCode,
                'plate_number' => $plateNumber,
                'plate_suffix' => $plateSuffix
            ];
        }
        
        return $this->response->setJSON(['status' => 'success', 'data' => $results]);
    }

    /**
     * Display sales creation form
     * 
     * @return void
     */
    public function create(): void
    {
        try {
            // Get active items for dropdown
        $items = $this->itemModel
            ->select('item.id, item.name, item.sku, item.price')
            ->where('item.status', '1')
            ->orderBy('item.name', 'ASC')
            ->findAll();

            // Get active agents for dropdown
            $agents = $this->agentModel
                ->where('is_active', '1')
                ->orderBy('name', 'ASC')
                ->findAll();

            // Get active platforms for dropdown
        $platforms = $this->platformModel
            ->where('status', '1')
            ->orderBy('platform', 'ASC')
            ->findAll();

            // Prepare view data
            $this->data['title'] = 'Tambah Penjualan';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['items'] = $items;
            $this->data['agents'] = $agents;
        $this->data['platforms'] = $platforms;
            $this->data['invoice_no'] = $this->model->generateInvoiceNo();
            $this->data['sale'] = [];
        $this->data['message'] = '';

            $this->view('sales/sales-form', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Sales::create error: ' . $e->getMessage());
            $this->handleError('Gagal memuat form penjualan: ' . $e->getMessage());
        }
    }

    /**
     * Handle form submission for saving sales
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
     */

    public function store()
    {
        $isAjax = $this->request->isAJAX();
        
        // Validate user session
        $userSession = session('user');
        if (!is_array($userSession) || !isset($userSession['id_user'])) {
            $message = 'User session tidak ditemukan, silakan login ulang.';
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'error', 'message' => $message]);
            }
            return redirect()->to('login')->with('message', ['status' => 'error', 'message' => $message]);
        }
        $userId = $userSession['id_user'];

        $postData = $this->request->getPost();
        $id = $postData['id'] ?? null;
        
        // Parse items from POST
        $items = $this->parseSaleItems($postData);
        if (empty($items)) {
            $message = 'Minimal harus ada satu item dalam transaksi.';
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'error', 'message' => $message]);
            }
            return redirect()->back()->withInput()->with('message', ['status' => 'error', 'message' => $message]);
        }

        // Validate invoice number
        if (empty($postData['invoice_no'])) {
            $message = 'Nomor invoice wajib diisi.';
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'error', 'message' => $message]);
            }
            return redirect()->back()->withInput()->with('message', ['status' => 'error', 'message' => $message]);
        }

        // Check invoice uniqueness (only for new records)
        if (empty($id)) {
            $existingSale = $this->model->where('invoice_no', $postData['invoice_no'])->first();
            if ($existingSale) {
                $message = 'Nomor invoice sudah digunakan.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->back()->withInput()->with('message', ['status' => 'error', 'message' => $message]);
            }
        }

        // Prepare plate data for customer lookup
        $platCode = !empty($postData['plate_code']) ? trim($postData['plate_code']) : null;
        $platNumber = !empty($postData['plate_number']) ? trim($postData['plate_number']) : null;
        $platLast = !empty($postData['plate_suffix']) ? trim($postData['plate_suffix']) : null;
        $customerName = !empty($postData['customer_name']) ? trim($postData['customer_name']) : null;

        // Check if customer exists (read-only, can be outside transaction)
        $customerId = null;
        if ($platCode && $platNumber) {
            $existingCustomer = $this->customerModel->findByPlate($platCode, $platNumber, $platLast);
            if ($existingCustomer) {
                $customerId = $existingCustomer['id'];
            }
        } elseif (!empty($postData['customer_id'])) {
            $customerId = (int)$postData['customer_id'];
        }

        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Create customer if not found but plate data provided
            if ($customerId === null && $platCode && $platNumber && $customerName) {
                $customerData = [
                    'name' => $customerName,
                    'plat_code' => $platCode,
                    'plat_number' => $platNumber,
                    'plat_last' => $platLast ?: null,
                    'status' => 'active'
                ];
                
                $this->customerModel->skipValidation(true);
                $insertResult = $this->customerModel->insert($customerData);
                if (!$insertResult) {
                    $errors = $this->customerModel->errors();
                    $errorMsg = 'Gagal membuat customer: ';
                    if ($errors && is_array($errors)) {
                        $errorMsg .= implode(', ', array_map(function($e) {
                            return is_array($e) ? json_encode($e) : $e;
                        }, $errors));
                    }
                    throw new \Exception($errorMsg);
                }
                $customerId = $this->customerModel->getInsertID();
                $this->customerModel->skipValidation(false);
            }
            // Save to sales table
            $saleData = [
                'invoice_no' => trim($postData['invoice_no']),
                'user_id' => $userId,
                'customer_id' => $customerId,
                'warehouse_id' => !empty($postData['agent_id']) ? (int)$postData['agent_id'] : null,
                'sale_channel' => $postData['sales_channel'] ?? self::CHANNEL_OFFLINE,
                'total_amount' => (float)($postData['subtotal'] ?? 0),
                'discount_amount' => (float)($postData['discount'] ?? 0),
                'tax_amount' => (float)($postData['tax'] ?? 0),
                'grand_total' => (float)($postData['grand_total'] ?? 0),
                'payment_status' => $postData['payment_status'] ?? '0'
            ];

            $this->model->skipValidation(true);
            if ($id) {
                $updateResult = $this->model->update($id, $saleData);
                if (!$updateResult) {
                    $errors = $this->model->errors();
                    $errorMsg = 'Gagal update data penjualan: ';
                    if ($errors && is_array($errors)) {
                        $errorMsg .= implode(', ', array_map(function($e) {
                            return is_array($e) ? json_encode($e) : $e;
                        }, $errors));
                    } else {
                        $errorMsg .= 'Unknown error';
                    }
                    throw new \Exception($errorMsg);
                }
                $saleId = $id;
                // Delete existing details
                $this->salesDetailModel->where('sale_id', $saleId)->delete();
            } else {
                $insertResult = $this->model->insert($saleData);
                if (!$insertResult) {
                    $errors = $this->model->errors();
                    $dbError = $db->error();
                    $errorMsg = 'Gagal insert data penjualan. ';
                    if ($errors && is_array($errors)) {
                        $errorMsg .= 'Validation: ' . implode(', ', array_map(function($e) {
                            return is_array($e) ? json_encode($e) : $e;
                        }, $errors));
                    }
                    if ($dbError) {
                        if (is_array($dbError)) {
                            $errorMsg .= ' Database: ' . (isset($dbError['message']) ? $dbError['message'] : json_encode($dbError));
                        } else {
                            $errorMsg .= ' Database: ' . $dbError;
                        }
                    }
                    log_message('error', 'Sales insert failed: ' . $errorMsg);
                    log_message('error', 'Sales insert data: ' . json_encode($saleData));
                    throw new \Exception($errorMsg);
                }
                $saleId = $this->model->getInsertID();
                if (!$saleId || $saleId == 0) {
                    throw new \Exception('Gagal mendapatkan ID penjualan setelah insert.');
                }
            }
            $this->model->skipValidation(false);

            // Save to sales_detail table only
            $itemModel = new \App\Models\ItemModel();
            foreach ($items as $item) {
                // Get item name
                $itemRecord = $itemModel->find((int)$item['item_id']);
                $itemName = 'Unknown';
                if ($itemRecord) {
                    if (is_array($itemRecord)) {
                        $itemName = isset($itemRecord['name']) ? (string)$itemRecord['name'] : 'Unknown';
                    } else {
                        $itemName = isset($itemRecord->name) ? (string)$itemRecord->name : 'Unknown';
                    }
                }

                // Insert to sales_detail - ensure sn is a string
                $snValue = null;
                if (!empty($item['sns'])) {
                    $snValue = is_array($item['sns']) ? json_encode($item['sns']) : (string)$item['sns'];
                }
                
                $this->salesDetailModel->skipValidation(true);
                $salesDetailData = [
                        'sale_id' => $saleId,
                        'item_id' => (int)$item['item_id'],
                        'variant_id' => !empty($item['variant_id']) ? (int)$item['variant_id'] : null,
                    'sn' => $snValue,
                    'item' => $itemName,
                        'price' => (float)($item['price'] ?? 0),
                    'qty' => (int)($item['qty'] ?? 1),
                    'disc' => (float)($item['discount'] ?? 0),
                    'amount' => (float)($item['subtotal'] ?? 0)
                ];
                $detailInsertResult = $this->salesDetailModel->insert($salesDetailData);
                if (!$detailInsertResult) {
                    $errors = $this->salesDetailModel->errors();
                    $errorMsg = 'Gagal insert sales_detail: ';
                    if ($errors && is_array($errors)) {
                        $errorMsg .= implode(', ', array_map(function($e) {
                            return is_array($e) ? json_encode($e) : (string)$e;
                        }, $errors));
                    }
                    throw new \Exception($errorMsg);
                }
                $salesDetailId = $this->salesDetailModel->getInsertID();
                $this->salesDetailModel->skipValidation(false);

                // Save serial numbers to SalesItemSnModel and update ItemSnModel
                if (!empty($item['sns'])) {
                    // Check if sns is already an array or needs to be decoded
                    if (is_array($item['sns'])) {
                        $sns = $item['sns'];
                    } else {
                        $sns = json_decode($item['sns'], true);
                    }
                    
                    if (is_array($sns) && $salesDetailId) {
                        // Get item warranty from item record
                        $itemWarranty = 0;
                        if ($itemRecord) {
                            if (is_array($itemRecord)) {
                                $itemWarranty = isset($itemRecord['warranty']) ? (int)$itemRecord['warranty'] : 0;
                            } else {
                                $itemWarranty = isset($itemRecord->warranty) ? (int)$itemRecord->warranty : 0;
                            }
                        }
                        
                        $itemSnModel = new \App\Models\ItemSnModel();
                        $activatedAt = date('Y-m-d H:i:s');
                        
                        // Calculate expired_at if warranty exists
                        $expiredAt = null;
                        if ($itemWarranty > 0) {
                            $expiredAt = (new \DateTime($activatedAt))
                                ->modify('+' . $itemWarranty . ' months')
                                ->format('Y-m-d H:i:s');
                        }
                        
                        foreach ($sns as $sn) {
                                if (!empty($sn['item_sn_id']) && !empty($sn['sn'])) {
                                $itemSnId = (int)$sn['item_sn_id'];
                                $snValue = (string)$sn['sn'];
                                
                                // Save to SalesItemSnModel
                                $this->salesItemSnModel->skipValidation(true);
                                $salesItemSnData = [
                                    'sales_item_id' => $salesDetailId,
                                    'item_sn_id' => $itemSnId,
                                    'sn' => $snValue
                                ];
                                $this->salesItemSnModel->insert($salesItemSnData);
                                $this->salesItemSnModel->skipValidation(false);
                                
                                // Update ItemSnModel with warranty expiration
                                $updateData = [
                                    'is_sell' => 1,
                                    'is_activated' => 1,
                                    'activated_at' => $activatedAt
                                ];
                                
                                // Add expired_at if warranty is set
                                if ($expiredAt) {
                                    $updateData['expired_at'] = $expiredAt;
                                }
                                
                                $itemSnModel->skipValidation(true);
                                $itemSnModel->update($itemSnId, $updateData);
                                $itemSnModel->skipValidation(false);
                            }
                        }
                    }
                }
            }

            // Complete transaction
            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi gagal.');
            }

            $message = $id ? 'Penjualan berhasil diupdate.' : 'Penjualan berhasil disimpan.';
            if ($isAjax) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => $message,
                    'data' => ['id' => $saleId]
                ]);
            }

            return redirect()->to('sales')->with('message', ['status' => 'success', 'message' => $message]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Sales::store error: ' . $e->getMessage());
            
            $message = 'Gagal menyimpan penjualan: ' . $e->getMessage();
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'error', 'message' => $message]);
            }
            
            return redirect()->back()->withInput()->with('message', ['status' => 'error', 'message' => $message]);
        }
    }
    // DEBUG: pre message store is called

    /**
     * Parse sale items from POST data
     * 
     * @param array $postData
     * @return array
     */
    protected function parseSaleItems(array $postData): array
    {
        $items = [];
        
        // Check if items are sent as JSON
        if (!empty($postData['items_json'])) {
            $items = json_decode($postData['items_json'], true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'JSON decode error: ' . json_last_error_msg());
                $items = [];
            }
            
            return $items ?? [];
        }
        
        // Check if items are sent as form array (items[0][item_id], items[1][item_id], etc.)
        if (!empty($postData['items']) && is_array($postData['items'])) {
            foreach ($postData['items'] as $item) {
                if (empty($item['item_id'])) {
                    continue; // Skip invalid items
                }
                
                // Parse SNs if they're JSON string
                $sns = [];
                if (!empty($item['sns'])) {
                    if (is_string($item['sns'])) {
                        $sns = json_decode($item['sns'], true) ?? [];
                    } elseif (is_array($item['sns'])) {
                        $sns = $item['sns'];
                    }
                }
                
                $items[] = [
                    'item_id' => $item['item_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'qty' => $item['qty'] ?? $item['quantity'] ?? 1,
                    'quantity' => $item['qty'] ?? $item['quantity'] ?? 1,
                    'price' => $item['price'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $item['subtotal'] ?? 0,
                    'note' => $item['note'] ?? '',
                    'sns' => $sns
                ];
            }
            
            log_message('debug', 'Sales::parseSaleItems - Parsed ' . count($items) . ' items from form array');
            return $items;
        }
        
        log_message('debug', 'Sales::parseSaleItems - No items found in POST data');
        return [];
    }

    /**
     * Show sale detail
     * 
     * @param int $id Sale ID
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function detail(int $id)
    {
        if ($id <= 0) {
            return redirect()->to('sales')->with('message', [
                'status' => 'error',
                'message' => 'ID penjualan tidak valid.'
            ]);
        }

        try {
            $sale = $this->model->getSalesWithRelations($id);
            
            if (!$sale) {
                return redirect()->to('sales')->with('message', [
                    'status' => 'error',
                    'message' => 'Data penjualan tidak ditemukan.'
                ]);
            }

            // Get items from sales_detail table
            $items = $this->salesDetailModel->getDetailsBySale($id);

            // Parse serial numbers from sn field (stored as JSON string)
        foreach ($items as &$item) {
                if (!empty($item['sn'])) {
                    $sns = json_decode($item['sn'], true);
                    $item['sns'] = is_array($sns) ? $sns : [];
                } else {
                    $item['sns'] = [];
                }
        }

        $this->data['title'] = 'Detail Penjualan';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['sale'] = $sale;
        $this->data['items'] = $items;

            $this->view('sales/sales-detail', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Sales::detail error: ' . $e->getMessage());
            return redirect()->to('sales')->with('message', [
                'status' => 'error',
                'message' => 'Gagal memuat detail penjualan.'
            ]);
        }
    }

    /**
     * Get DataTables data for sales list
     * 
     * @return ResponseInterface
     */
    public function getDataDT(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Request tidak valid.',
            ]);
        }

        try {
            $draw   = (int) ($this->request->getPost('draw') ?? $this->request->getGet('draw') ?? 0);
            $start  = (int) ($this->request->getPost('start') ?? $this->request->getGet('start') ?? 0);
            $length = (int) ($this->request->getPost('length') ?? $this->request->getGet('length') ?? 10);

            // Defensive bounds for pagination
            if ($start < 0) {
                $start = 0;
            }
            if ($length < 1 || $length > 100) {
                $length = 10;
            }

            // Retrieve search value from DataTables POST/GET
            $search = $this->request->getPost('search');
            if ($search === null) {
                $search = $this->request->getGet('search');
            }

            $searchValue = '';
            if (is_array($search) && isset($search['value'])) {
                $searchValue = trim($search['value']);
            } elseif (is_string($search)) {
                $searchValue = trim($search);
            }

            $db           = \Config\Database::connect();
            $totalRecords = $db->table('sales')->countAllResults();

            // Main query builder with joins
            $query = $this->buildSalesQuery();

            // Apply filtering if search term present
            $totalFiltered = $totalRecords;

            if (!empty($searchValue)) {
                $query->groupStart()
                      ->like('sales.invoice_no', $searchValue)
                      ->orLike('customer.name', $searchValue)
                      ->orLike('user.nama', $searchValue)
                      ->orLike('agent.name', $searchValue)
                      ->groupEnd();

                // Clone join for the count (mimics actual filter)
                $countQuery = $this->buildSalesQuery();
                $countQuery->groupStart()
                           ->like('sales.invoice_no', $searchValue)
                           ->orLike('customer.name', $searchValue)
                           ->orLike('user.nama', $searchValue)
                           ->orLike('agent.name', $searchValue)
                           ->groupEnd();

                $totalFiltered = $countQuery->countAllResults();
            }

            $data = $query->orderBy('sales.created_at', 'DESC')
                          ->limit($length, $start)
                          ->get()
                          ->getResultArray();

            // Format for DataTables (if applicable)
            if (method_exists($this, 'formatDataTablesData')) {
                $result = $this->formatDataTablesData($data, $start);
            } else {
                $result = $data;
            }

            return $this->response->setJSON([
                'draw'            => $draw,
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data'            => $result,
            ]);
        } catch (\Throwable $e) {
            log_message(
                'error',
                'Sales::getDataDT error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString()
            );

            return $this->response->setJSON([
                'draw'            => (int) ($this->request->getPost('draw') ?? $this->request->getGet('draw') ?? 0),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'Gagal memuat data: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Build base sales query with joins
     * 
     * @return \CodeIgniter\Database\BaseBuilder
     */
    protected function buildSalesQuery()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('sales');
        
        return $builder->select('sales.*, 
            customer.name as customer_name,
            user.nama as user_name')
            ->join('customer', 'customer.id = sales.customer_id', 'left')
            ->join('user', 'user.id_user = sales.user_id', 'left');
    }

    /**
     * Build count query for filtered results
     * 
     * @param string $searchValue
     * @return \CodeIgniter\Database\BaseBuilder
     */
    protected function buildCountQuery(string $searchValue): \CodeIgniter\Database\BaseBuilder
    {
        $db = \Config\Database::connect();
        $builder = $db->table('sales');
        
        // Join tables for search
        $builder->join('customer', 'customer.id = sales.customer_id', 'left')
                ->join('user', 'user.id_user = sales.user_id', 'left')
                ->join('agent', 'agent.id = sales.agent_id', 'left');
        
        // Apply search filter
        if (!empty($searchValue)) {
            $builder->groupStart()
                    ->like('sales.invoice_no', $searchValue)
                    ->orLike('customer.name', $searchValue)
                    ->orLike('user.nama', $searchValue)
                    ->orLike('agent.name', $searchValue)
                    ->groupEnd();
        }
        
        return $builder;
    }

    /**
     * Apply search filter to query
     * 
     * @param \CodeIgniter\Database\BaseBuilder $query
     * @param string $searchValue
     * @return void
     */
    protected function applySearchFilter($query, string $searchValue): void
    {
        if (empty($searchValue)) {
            return;
        }
        
        $query->groupStart()
              ->like('sales.invoice_no', $searchValue)
              ->orLike('customer.name', $searchValue)
              ->orLike('user.nama', $searchValue)
              ->orLike('agent.name', $searchValue)
              ->groupEnd();
    }

    /**
     * Get filtered count
     * 
     * @param string $searchValue
     * @return int
     */
    protected function getFilteredCount(string $searchValue): int
    {
        $query = $this->buildSalesQuery();
        $this->applySearchFilter($query, $searchValue);
        return $query->countAllResults();
    }

    /**
     * Format data for DataTables
     * 
     * @param array $data
     * @param int $start
     * @return array
     */
    protected function formatDataTablesData(array $data, int $start): array
    {
        $result = [];
        $no = $start + 1;

        $statusBadge = [
            self::STATUS_PENDING => '<span class="badge bg-warning">Pending</span>',
            self::STATUS_COMPLETED => '<span class="badge bg-success">Completed</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-danger">Cancelled</span>'
        ];

        foreach ($data as $row) {
            $status = $row['status'] ?? '';
            $statusDisplay = $statusBadge[$status] ?? '<span class="badge bg-secondary">' . esc($status) . '</span>';

            $actionButtons = '<div class="btn-group" role="group">';
            $actionButtons .= '<a href="' . $this->config->baseURL . 'sales/' . $row['id'] . '" ';
            $actionButtons .= 'class="btn btn-sm btn-info" title="Detail">';
            $actionButtons .= '<i class="fas fa-eye"></i></a>';
            $actionButtons .= '</div>';

            $result[] = [
                'ignore_search_urut'    => $no,
                'invoice_no'            => esc($row['invoice_no'] ?? ''),
                'customer_name'         => esc($row['customer_name'] ?? '-'),
                'agent_name'            => esc($row['agent_name'] ?? '-'),
                'grand_total'           => format_angka((float) ($row['grand_total'] ?? 0), 2),
                'status'                => $statusDisplay,
                'created_at'            => !empty($row['created_at'])
                                            ? date('d/m/Y H:i', strtotime($row['created_at']))
                                            : '-',
                'ignore_search_action'  => $actionButtons,
            ];

            $no++;
        }

        return $result;
    }

    /**
     * Get unsold serial numbers for AJAX request
     * 
     * @return ResponseInterface
     */
    public function getUnusedSNs(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Request tidak valid.'
            ]);
        }

        try {
            $itemId = (int)($this->request->getGet('item_id') ?? 0);

            if ($itemId <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Item ID harus diisi dan berupa angka positif.'
                ]);
            }

            // Verify item exists
            $item = $this->itemModel->find($itemId);
            if (!$item) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Item tidak ditemukan.'
                ]);
            }

            // Get available serial numbers
            $sns = $this->itemSnModel
            ->select('item_sn.*, item.name as item_name')
            ->join('item', 'item.id = item_sn.item_id', 'left')
            ->where('item_sn.item_id', $itemId)
                ->where('item_sn.is_sell', '0')
                ->orderBy('item_sn.created_at', 'ASC')
                ->findAll();

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $sns,
                'count' => count($sns)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Sales::getUnusedSNs error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal memuat data serial number.'
            ]);
        }
    }

    /**
     * Handle error and return appropriate response
     * 
     * @param string $message
     * @return void
     */
    protected function handleError(string $message): void
    {
        $this->data['title'] = 'Error';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['msg'] = [
            'status' => 'error',
            'message' => $message
        ];
        
        $this->view('error', $this->data);
    }
}

