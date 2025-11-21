<?php

/**
 * Agent Sales Controller (Cart & Checkout)
 * 
 * Handles cart and checkout functionality for agents
 * 
 * @package    App\Controllers\Agent
 * @author     Mikhael Felian Waskito <mikhaelfelian@gmail.com>
 * @copyright  2025
 * @license    MIT
 * @version    1.0.0
 * @since      2025-11-04
 */

namespace App\Controllers\Agent;

use App\Controllers\BaseController;
use App\Models\SalesModel;
use App\Models\SalesItemsModel;
use App\Models\SalesItemSnModel;
use App\Models\SalesPaymentsModel;
use App\Models\SalesDetailModel;
use App\Models\SalesFeeModel;
use App\Models\FeeTypeModel;
use App\Models\ItemModel;
use App\Models\ItemSnModel;
use App\Models\AgentModel;
use App\Models\CustomerModel;
use App\Models\UserRoleAgentModel;
use App\Models\PlatformModel;
use App\Models\SalesPaymentLogModel;
use App\Models\SalesGatewayLogModel;
use App\Models\AgentPaylaterModel;
use App\Models\WilayahPropinsiModel;
use App\Models\WilayahKabupatenModel;
use App\Models\WilayahKecamatanModel;
use App\Models\WilayahKelurahanModel;
use CodeIgniter\HTTP\ResponseInterface;

class Sales extends BaseController
{
    protected $model;
    protected $salesItemsModel;
    protected $salesItemSnModel;
    protected $salesPaymentsModel;
    protected $salesDetailModel;
    protected $salesFeeModel;
    protected $feeTypeModel;
    protected $itemModel;
    protected $itemSnModel;
    protected $agentModel;
    protected $customerModel;
    protected $userRoleAgentModel;
    protected $platformModel;
    protected $salesPaymentLogModel;
    protected $salesGatewayLogModel;
    protected $agentPaylaterModel;
    
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
     * Payment Gateway Configuration
     */
    protected const GATEWAY_API_KEY = 'P@ssw0rdMav123';
    protected const GATEWAY_PUBLIC_KEY = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAp4d7Fd9aXgP5zK0YbM3Z2skasRz9i41ZwPjH3KQZlfZ5LcvXbApX8yRHLJc/biMFcXosIuHf6Z0hG9vChgCyOf2V6tYb05Q+bmAlwJ1pDkg4zKHzXv/uhRRjZQxX2ld7rYgXW9BvvkKIRu5ATkKeYzX8o9u8ZBqYkDg0ACZ1X+YP0X1ZlNQy3YBqx4fXKPRdZ6gPSPzN4r4s7vYSmZ7fWQIDAQAB';
    
    /**
     * Initialize models
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new SalesModel();
        $this->salesItemsModel = new SalesItemsModel();
        $this->salesItemSnModel = new SalesItemSnModel();
        $this->salesPaymentsModel = new SalesPaymentsModel();
        $this->salesDetailModel = new SalesDetailModel();
        $this->salesFeeModel = new SalesFeeModel();
        $this->feeTypeModel = new FeeTypeModel();
        $this->itemModel = new ItemModel();
        $this->itemSnModel = new ItemSnModel();
        $this->agentModel = new AgentModel();
        $this->customerModel = new CustomerModel();
        $this->userRoleAgentModel = new UserRoleAgentModel();
        $this->platformModel = new PlatformModel();
        $this->salesPaymentLogModel = new SalesPaymentLogModel();
        $this->salesGatewayLogModel = new SalesGatewayLogModel();
        $this->agentPaylaterModel = new AgentPaylaterModel();
        $this->data['role'] = $this->hasRole();
        
        // Add Material Icons CSS for statistics cards
        $this->addStyle($this->config->baseURL . 'public/vendors/material-icons/css.css');
    }
    
    /**
     * Display cart and checkout page
     * 
     * @return void
     */
    public function cart(): void
    {
        // Get cart from session
        $cart = $this->session->get('agent_cart') ?? [];
        
        // Get agent ID from current user
        $userId = $this->user['id_user'] ?? null;
        $agentId = null;
        
        if ($userId) {
            // Find agent through user_role_agent relationship
            $userRoleAgent = $this->userRoleAgentModel->where('user_id', $userId)->first();
            if ($userRoleAgent) {
                $agentId = $userRoleAgent->agent_id;
            }
        }
        
        // Get agents linked to the logged-in user (via user_role_agent)
        $agents = [];
        if ($userId) {
            $agents = $this->agentModel
                ->select('agent.*')
                ->join('user_role_agent', 'user_role_agent.agent_id = agent.id', 'inner')
                ->where('user_role_agent.user_id', $userId)
                ->where('agent.is_active', '1')
                ->orderBy('agent.name', 'ASC')
                ->findAll();
        }

        // Fallback: if user has no agent mapping, still allow selecting active agents (optional)
        if (empty($agents)) {
            $agents = $this->agentModel
                ->where('is_active', '1')
                ->orderBy('name', 'ASC')
                ->findAll();
        }
        
        // Get agent's registered address if agentId exists
        $agentAddress = '';
        $agentCreditLimit = 0;
        $hasCreditLimit = false;
        
        if ($agentId) {
            $agent = $this->agentModel->find($agentId);
            if ($agent) {
                $agentCreditLimit = (float) ($agent->credit_limit ?? 0);
                $hasCreditLimit = $agentCreditLimit > 0;
                
                // Format agent address
                $addressParts = [];
                if (!empty($agent->address)) {
                    $addressParts[] = $agent->address;
                }
                
                // Get location names using Wilayah models
                $provinceName = '';
                $regencyName = '';
                $districtName = '';
                $villageName = '';
                
                if (!empty($agent->village_id)) {
                    $villageModel = new WilayahKelurahanModel();
                    $village = $villageModel->find($agent->village_id);
                    if ($village) {
                        $villageName = $village->nama_kelurahan ?? '';
                    }
                }
                
                if (!empty($agent->district_id)) {
                    $districtModel = new WilayahKecamatanModel();
                    $district = $districtModel->find($agent->district_id);
                    if ($district) {
                        $districtName = $district->nama_kecamatan ?? '';
                    }
                }
                
                if (!empty($agent->regency_id)) {
                    $regencyModel = new WilayahKabupatenModel();
                    $regency = $regencyModel->find($agent->regency_id);
                    if ($regency) {
                        $regencyName = $regency->nama_kabupaten ?? '';
                    }
                }
                
                if (!empty($agent->province_id)) {
                    $provinceModel = new WilayahPropinsiModel();
                    $province = $provinceModel->find($agent->province_id);
                    if ($province) {
                        $provinceName = $province->nama_propinsi ?? '';
                    }
                }
                
                // Build address lines
                if ($villageName || $districtName) {
                    $locationLine = trim($villageName . ($villageName && $districtName ? ', ' : '') . $districtName);
                    if ($locationLine) {
                        $addressParts[] = $locationLine;
                    }
                }
                
                if ($regencyName || $provinceName) {
                    $cityLine = trim($regencyName . ($regencyName && $provinceName ? ', ' : '') . $provinceName);
                    if ($cityLine) {
                        if (!empty($agent->postal_code)) {
                            $cityLine .= ' ' . $agent->postal_code;
                        }
                        $addressParts[] = $cityLine;
                    }
                }
                
                $country = $agent->country ?? 'Indonesia';
                $addressParts[] = $country;
                
                $agentAddress = implode("\n", array_filter($addressParts));
            }
        }
        
        // Get platforms with status_agent='1' (include status_pos, gw_status, gw_code for API check)
        $allPlatforms = $this->platformModel
            ->select('platform.*, platform.status_pos, platform.gw_status, platform.gw_code')
            ->where('status', '1')
            ->where('status_agent', '1')
            ->orderBy('platform', 'ASC')
            ->findAll();
        
        // Separate platforms by gw_status
        $platformsManualTransfer = []; // gw_status = 0
        $platformsPaymentGateway = []; // gw_status = 1
        
        foreach ($allPlatforms as $platform) {
            $gwStatus = (string) ($platform->gw_status ?? '0');
            if ($gwStatus === '0') {
                $platformsManualTransfer[] = $platform;
            } elseif ($gwStatus === '1') {
                $platformsPaymentGateway[] = $platform;
            }
        }
        
        // Get PPN setting from settings table
        $baseModel = new \App\Models\BaseModel();
        $settings = $baseModel->getSettingAplikasi();
        $ppnPercentage = (float) ($settings['ppn'] ?? 11); // Default 11%
        
        // Prepare view data
        $this->data['title'] = 'Keranjang & Checkout';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['cart'] = $cart;
        $this->data['agentId'] = $agentId;
        $this->data['agents'] = $agents;
        $this->data['platforms'] = $allPlatforms; // All platforms for backward compatibility
        $this->data['platformsManualTransfer'] = $platformsManualTransfer;
        $this->data['platformsPaymentGateway'] = $platformsPaymentGateway;
        $this->data['agentAddress'] = $agentAddress;
        $this->data['hasCreditLimit'] = $hasCreditLimit;
        $this->data['agentCreditLimit'] = $agentCreditLimit;
        $this->data['ppnPercentage'] = $ppnPercentage;
        // Invoice number will be generated automatically when saving
        $this->data['message'] = $this->session->getFlashdata('message');

        $this->data['breadcrumb'] = [
            'Home'               => $this->config->baseURL.'agent/dashboard',
            'Keranjang'          => '',
        ];
        
        // Load helper for currency formatting
        helper('angka');
        
        // Render view
        $this->view('sales/agent/sales-form', $this->data);
    }
    
    /**
     * Search product by SKU/barcode (AJAX endpoint)
     * 
     * @return ResponseInterface
     */
    public function searchProductBySku(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Request tidak valid.'
            ]);
        }
        
        try {
            $sku = trim($this->request->getPost('sku') ?? $this->request->getGet('sku') ?? '');
            
            if (empty($sku)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'SKU tidak boleh kosong.'
                ]);
            }
            
            // Search product by SKU
            $item = $this->itemModel->where('status', '1')->where('sku', $sku)->first();
            
            if (!$item) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Produk dengan SKU "' . esc($sku) . '" tidak ditemukan.'
                ]);
            }
            
            // Convert to array if object
            $itemData = is_object($item) ? (array)$item : $item;
            
            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'id' => $itemData['id'],
                    'name' => $itemData['name'],
                    'sku' => $itemData['sku'] ?? '',
                    'price' => (float)($itemData['price'] ?? 0),
                    'agent_price' => (float)($itemData['agent_price'] ?? 0),
                    'image' => $itemData['image'] ?? ''
                ],
                'csrf_hash' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Agent\Sales::searchProductBySku error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mencari produk: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }
    
    /**
     * Add item to cart (AJAX endpoint)
     * 
     * @return ResponseInterface
     */
    public function addToCart(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Request tidak valid.'
            ]);
        }
        
        try {
            $itemId = (int)($this->request->getPost('item_id') ?? 0);
            $itemName = trim($this->request->getPost('item_name') ?? '');
            $itemPrice = (float)($this->request->getPost('item_price') ?? 0);
            $qty = (int)($this->request->getPost('qty') ?? 1);
            
            if ($itemId <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Item ID tidak valid.'
                ]);
            }
            
            // Get item details from database
            $item = $this->itemModel->find($itemId);
            if (!$item) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Item tidak ditemukan.'
                ]);
            }
            
            // Get current cart
            $cart = $this->session->get('agent_cart') ?? [];
            
            // Check if item already exists in cart
            $itemIndex = -1;
            foreach ($cart as $index => $cartItem) {
                if ($cartItem['item_id'] == $itemId) {
                    $itemIndex = $index;
                    break;
                }
            }
            
            if ($itemIndex >= 0) {
                // Update quantity
                $cart[$itemIndex]['qty'] += $qty;
                $cart[$itemIndex]['subtotal'] = $cart[$itemIndex]['qty'] * $cart[$itemIndex]['price'];
            } else {
                // Add new item
                $cart[] = [
                    'item_id' => $itemId,
                    'item_name' => !empty($itemName) ? $itemName : $item->name,
                    'price' => $itemPrice > 0 ? $itemPrice : (float)$item->price,
                    'qty' => $qty,
                    'subtotal' => ($itemPrice > 0 ? $itemPrice : (float)$item->price) * $qty,
                    'image' => $item->image ?? ''
                ];
            }
            
            // Save cart to session
            $this->session->set('agent_cart', $cart);
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Item berhasil ditambahkan ke keranjang.',
                'cart_count' => count($cart),
                'cart_total' => array_sum(array_column($cart, 'subtotal')),
                'csrf_hash' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Agent\Sales::addToCart error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menambahkan item: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }
    
    /**
     * Update cart item quantity
     * 
     * @return ResponseInterface
     */
    public function updateCart(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Request tidak valid.'
            ]);
        }
        
        try {
            $itemId = (int)($this->request->getPost('item_id') ?? 0);
            $qty = (int)($this->request->getPost('qty') ?? 1);
            
            if ($itemId <= 0 || $qty < 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data tidak valid.'
                ]);
            }
            
            $cart = $this->session->get('agent_cart') ?? [];
            
            if ($qty == 0) {
                // Remove item
                $cart = array_filter($cart, function($item) use ($itemId) {
                    return $item['item_id'] != $itemId;
                });
                $cart = array_values($cart); // Re-index array
            } else {
                // Update quantity
                foreach ($cart as &$cartItem) {
                    if ($cartItem['item_id'] == $itemId) {
                        $cartItem['qty'] = $qty;
                        $cartItem['subtotal'] = $cartItem['price'] * $qty;
                        break;
                    }
                }
            }
            
            $this->session->set('agent_cart', $cart);
            
            $total = array_sum(array_column($cart, 'subtotal'));
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Keranjang berhasil diperbarui.',
                'cart_count' => count($cart),
                'cart_total' => $total,
                'csrf_hash' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Agent\Sales::updateCart error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal memperbarui keranjang: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }
    
    /**
     * Remove item from cart
     * 
     * @return ResponseInterface
     */
    public function removeFromCart(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Request tidak valid.'
            ]);
        }
        
        try {
            $itemId = (int)($this->request->getPost('item_id') ?? 0);
            
            if ($itemId <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Item ID tidak valid.'
                ]);
            }
            
            $cart = $this->session->get('agent_cart') ?? [];
            $cart = array_filter($cart, function($item) use ($itemId) {
                return $item['item_id'] != $itemId;
            });
            $cart = array_values($cart); // Re-index array
            
            $this->session->set('agent_cart', $cart);
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Item berhasil dihapus dari keranjang.',
                'cart_count' => count($cart),
                'cart_total' => array_sum(array_column($cart, 'subtotal')),
                'csrf_hash' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Agent\Sales::removeFromCart error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus item: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }
    
    /**
     * Clear cart
     * 
     * @return ResponseInterface
     */
    public function clearCart(): ResponseInterface
    {
        $this->session->remove('agent_cart');
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Keranjang berhasil dikosongkan.'
            ]);
        }
        
        return redirect()->to('agent/sales/cart');
    }
    
    /**
     * Handle checkout submission
     * 
     * @return ResponseInterface
     */
    public function store()
    {
        $isAjax = $this->request->isAJAX();
        $gatewayLogBuffer = [];
        
        try {
            // Get cart from session
            $cart = $this->session->get('agent_cart') ?? [];
            
            if (empty($cart)) {
                $message = 'Keranjang kosong. Silakan tambahkan item terlebih dahulu.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->to('agent/sales/cart')->with('message', [
                    'status' => 'error',
                    'message' => $message
                ]);
            }
            
            // Get POST data
            $postData = $this->request->getPost();
            
            // Generate invoice number automatically (for online sales, channel = '2')
            $invoiceNo = $this->model->generateInvoiceNo(self::CHANNEL_ONLINE);
            log_message('info', 'Agent\Sales::store - Generated invoice number: ' . $invoiceNo);
            
            // Validate required fields
            if (empty($postData['agent_id'])) {
                $message = 'Agen harus dipilih.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->back()->withInput()->with('message', [
                    'status' => 'error',
                    'message' => $message
                ]);
            }
            
            $selectedAgentId = (int)$postData['agent_id'];
            
            // Get current user ID
            $userId = $this->user['id_user'] ?? null;
            if (!$userId) {
                $message = 'User tidak ditemukan. Silakan login ulang.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->back()->withInput()->with('message', [
                    'status' => 'error',
                    'message' => $message
                ]);
            }
            
            // Get logged-in agent ID from user_role_agent relationship
            // This will be used for sales.customer_id instead of actual customer ID
            $loggedInAgentId = null;
            if ($userId) {
                // Find agent through user_role_agent relationship
                $userRoleAgent = $this->userRoleAgentModel->where('user_id', $userId)->first();
                if ($userRoleAgent) {
                    // If user has multiple agents, prefer the one matching selectedAgentId if available
                    if ($selectedAgentId) {
                        $matchingAgent = $this->userRoleAgentModel
                            ->where('user_id', $userId)
                            ->where('agent_id', $selectedAgentId)
                            ->first();
                        if ($matchingAgent) {
                            $loggedInAgentId = $matchingAgent->agent_id;
                        } else {
                            // Use first agent if selected one doesn't match
                            $loggedInAgentId = $userRoleAgent->agent_id;
                        }
                    } else {
                        // Use first agent found
                        $loggedInAgentId = $userRoleAgent->agent_id;
                    }
                }
            }
            
            // Validate that logged-in agent ID exists
            if (!$loggedInAgentId) {
                $message = 'Agen tidak ditemukan untuk user ini. Silakan hubungi administrator.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->back()->withInput()->with('message', [
                    'status' => 'error',
                    'message' => $message
                ]);
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
            
            // Calculate totals
            $totalQty = 0;
            $subtotal = 0;
            foreach ($cart as $item) {
                $totalQty += $item['qty'];
                $subtotal += $item['subtotal'];
            }
            
            $discount = (float)($postData['discount'] ?? 0);
            $baseAmount = $subtotal - $discount;
            
            // Calculate tax based on tax_type
            $taxType = $postData['tax_type'] ?? '0';
            
            // Get PPN percentage from settings
            $baseModel = new \App\Models\BaseModel();
            $settings = $baseModel->getSettingAplikasi();
            $ppnPercentage = (float) ($settings['ppn'] ?? 11); // Default 11%
            
            $taxAmount = 0;
            $grandTotal = $baseAmount;
            
            if ($taxType === '1') {
                // Include tax (PPN termasuk): tax is included in baseAmount
                // Tax = baseAmount - (baseAmount / (1 + ppn/100))
                $taxAmount = $baseAmount - ($baseAmount / (1 + ($ppnPercentage / 100)));
                $grandTotal = $baseAmount; // Grand total is baseAmount (tax already included)
            } elseif ($taxType === '2') {
                // Added tax (PPN ditambahkan): tax is added on top
                $taxAmount = $baseAmount * ($ppnPercentage / 100);
                $grandTotal = $baseAmount + $taxAmount;
            } else {
                // No tax (tax_type = '0')
                $taxAmount = 0;
                $grandTotal = $baseAmount;
            }
            
            // Handle payment type
            $paymentType = $postData['payment_type'] ?? 'paynow';
            
            // Paylater credit limit handling setup
            $shouldDeductCredit = false;
            $agentCreditAfter = null;
            
            if ($paymentType === 'paylater') {
                $agentRecord = $this->agentModel->find($selectedAgentId);
                if (!$agentRecord) {
                    $message = 'Data agen tidak ditemukan.';
                    if ($isAjax) {
                        return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                    }
                    return redirect()->back()->withInput()->with('message', [
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                
                // Get max credit limit from agent (this is the maximum credit allowed)
                $maxCreditLimit = (float) ($agentRecord->credit_limit ?? 0);
                
                if ($maxCreditLimit <= 0) {
                    $message = 'Agen tidak memiliki limit kredit.';
                    if ($isAjax) {
                        return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                    }
                    return redirect()->back()->withInput()->with('message', [
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                
                // Calculate current debt balance from agent_paylater transactions
                // Sum all amounts: positive for purchases (type 1), negative for repayments (type 2)
                $db = \Config\Database::connect();
                $debtBalance = $db->table('agent_paylater')
                    ->selectSum('amount')
                    ->where('agent_id', $selectedAgentId)
                    ->get()
                    ->getRow();
                
                $currentDebt = (float) ($debtBalance->amount ?? 0);
                
                // Available credit = max limit - current debt
                $availableCredit = $maxCreditLimit - $currentDebt;
                
                // Check if available credit is sufficient
                if ($availableCredit <= 0 || $availableCredit < $grandTotal) {
                    $message = 'Limit kredit agen tidak mencukupi. Sisa limit: Rp ' . number_format(max($availableCredit, 0), 0, ',', '.');
                    if ($isAjax) {
                        return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                    }
                    return redirect()->back()->withInput()->with('message', [
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                
                // Calculate remaining available credit after this transaction
                // This will be stored in agent.credit_limit to track remaining credit
                $agentCreditAfter = $availableCredit - $grandTotal;
                $shouldDeductCredit = true;
            }
            
            // Handle payment gateway API call if platform is selected and payment type is paynow
            $gatewayResponse = null;
            $isOfflinePlatform = false;
            $settlementTime = null;
            $platformId = null;
            $platform = null;
            
            // Only process platform if payment type is paynow
            if ($paymentType === 'paynow') {
                $platformId = !empty($postData['platform_id']) ? (int)$postData['platform_id'] : null;
            }
            
            if ($platformId) {
                // Get platform details
                $platform = $this->platformModel->find($platformId);
                
                if (!$platform) {
                    $message = 'Platform pembayaran tidak ditemukan.';
                    if ($isAjax) {
                        return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                    }
                    return redirect()->back()->withInput()->with('message', [
                        'status' => 'error',
                        'message' => $message
                    ]);
                }
                
                // Only send to gateway if platform.gw_status = '1'
                // Platforms like "Tunai" (Cash) with gw_status = '0' should NOT go through gateway
                $gwStatus = $platform['gw_status'] ?? '0';
                // Handle both string and integer values
                $gwStatus = (string)$gwStatus;
                if ($gwStatus === '1') {
                    
                    // Prepare API request data
                    // Use generated invoiceNo (already generated above)
                    // Don't overwrite calculated grandTotal - use it directly
                    
                    // Get customer email and phone from form data
                    $customerEmail = !empty($postData['customer_email']) ? trim($postData['customer_email']) : '';
                    $customerPhone = !empty($postData['customer_phone']) ? trim($postData['customer_phone']) : '';
                    
                    // If customer exists, try to get email/phone from customer record
                    if ($customerId) {
                        $customerRecord = $this->customerModel->find($customerId);
                        if ($customerRecord) {
                            if (empty($customerEmail) && !empty($customerRecord['email'])) {
                                $customerEmail = $customerRecord['email'];
                            }
                            if (empty($customerPhone) && !empty($customerRecord['phone'])) {
                                $customerPhone = $customerRecord['phone'];
                            }
                        }
                    }
                    
                    // Use defaults if still empty
                    if (empty($customerEmail)) {
                        $customerEmail = 'customer@example.com';
                    }
                    if (empty($customerPhone)) {
                        $customerPhone = '';
                    }
                    
                    // Split customer name into firstName and lastName
                    $customerName = !empty($postData['customer_name']) ? trim($postData['customer_name']) : '';
                    $nameParts = !empty($customerName) ? explode(' ', $customerName, 2) : ['Customer', 'Customer'];
                    $firstName = $nameParts[0] ?? 'Customer';
                    $lastName = $nameParts[1] ?? $firstName;
                    
                    // PAYMENT GATEWAY RULE: Check sales_gateway_logs first
                    // Once an invoice number is sent to the gateway, it cannot be reused
                    // Step 1: Check if invoice number has been sent to gateway before
                    if ($this->salesGatewayLogModel->invoiceExists($invoiceNo)) {
                        log_message('warning', 'Agent\Sales::store - Invoice number already sent to gateway: ' . $invoiceNo . '. Generating new invoice number (gateway rule: cannot reuse).');
                        $invoiceNo = $this->model->generateInvoiceNo(self::CHANNEL_ONLINE);
                        log_message('info', 'Agent\Sales::store - New invoice number generated: ' . $invoiceNo);
                    }
                    
                    // Step 2: Check if invoice number already exists in database
                    $existingSale = $this->model->where('invoice_no', $invoiceNo)->first();
                    if ($existingSale) {
                        log_message('warning', 'Agent\Sales::store - Invoice number already exists in database: ' . $invoiceNo . '. Generating new invoice number.');
                        $invoiceNo = $this->model->generateInvoiceNo(self::CHANNEL_ONLINE);
                        log_message('info', 'Agent\Sales::store - New invoice number generated: ' . $invoiceNo);
                    }
                    
                    // Step 3: Check gateway for existing payment (optional - for reusing existing payments)
                    log_message('info', 'Agent\Sales::store - Checking gateway for existing payment with orderId: ' . $invoiceNo);
                    $gatewayStatusResponse = $this->getPaymentStatusFromGateway($invoiceNo);
                    
                    if ($gatewayStatusResponse !== null) {
                        // Payment exists in gateway, reuse it
                        log_message('info', 'Agent\Sales::store - Payment exists in gateway for invoice: ' . $invoiceNo . ', reusing existing payment');
                        $gatewayResponse = $gatewayStatusResponse;
                    } else {
                        // Prepare API payload with current invoice number
                        // Ensure amount is integer (not float) as per API spec
                        $apiData = [
                            'code'     => $platform['gw_code'] ?? 'QRIS',
                            'orderId'  => $invoiceNo,
                            'amount'   => (int) round($grandTotal),
                            'customer' => [
                                'firstName' => $firstName,
                                'lastName'  => $lastName,
                                'email'     => $customerEmail,
                                'phone'     => $customerPhone,
                            ],
                        ];
                    }
                    
                    // Only call gateway API if we don't have a response yet
                    if ($gatewayResponse === null) {
                        // Auto-retry logic for "different request payload" errors
                        $maxRetries = 3;
                        $retryCount = 0;
                        $lastError = null;
                        
                        while ($retryCount < $maxRetries && $gatewayResponse === null) {
                            // Log the payload being sent (for debugging)
                            log_message('info', 'Agent\Sales::store - Gateway API Payload (attempt ' . ($retryCount + 1) . '): ' . json_encode($apiData, JSON_PRETTY_PRINT));
                            
                            // Call payment gateway API
                            $gatewayResponse = $this->callPaymentGateway($apiData);
                            
                            // Check if API call failed
                            if ($gatewayResponse === null) {
                                // Get last error from log to check for "different request payload"
                                $logFile = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.log';
                                $lastError = null;
                                if (file_exists($logFile)) {
                                    $logContent = file_get_contents($logFile);
                                    $lines = explode("\n", $logContent);
                                    // Search backwards for the last callPaymentGateway error
                                    for ($i = count($lines) - 1; $i >= 0; $i--) {
                                        if (stripos($lines[$i], 'callPaymentGateway') !== false && 
                                            stripos($lines[$i], 'different request payload') !== false) {
                                            $lastError = trim($lines[$i]);
                                            break;
                                        }
                                    }
                                }
                                
                                // If "different request payload" error detected, retry with new invoice number
                                if ($lastError && stripos($lastError, 'different request payload') !== false) {
                                    $retryCount++;
                                    log_message('warning', 'Agent\Sales::store - Detected "different request payload" error. Retry attempt ' . $retryCount . ' of ' . $maxRetries);
                                    
                                    // Log failed attempt to sales_gateway_logs
                                    $gatewayLogBuffer[] = [
                                        'invoice_no' => $invoiceNo,
                                        'platform_id' => $platformId,
                                        'amount' => $grandTotal,
                                        'payload' => $apiData,
                                        'response' => null,
                                        'status' => 'FAILED',
                                    ];
                                    
                                    // Generate new invoice number
                                    $invoiceNo = $this->model->generateInvoiceNo(self::CHANNEL_ONLINE);
                                    $apiData['orderId'] = $invoiceNo;
                                    
                                    log_message('info', 'Agent\Sales::store - Generated new invoice number for retry: ' . $invoiceNo);
                                    
                                    // Reset gatewayResponse to null to retry
                                    $gatewayResponse = null;
                                } else {
                                    // Different error, don't retry
                                    break;
                                }
                            } else {
                                // Success, log to sales_gateway_logs
                                $gatewayStatus = null;
                                if ($gatewayResponse && isset($gatewayResponse['status'])) {
                                    $gatewayStatus = $gatewayResponse['status'];
                                }
                                
                                $gatewayLogBuffer[] = [
                                    'invoice_no' => $invoiceNo,
                                    'platform_id' => $platformId,
                                    'amount' => $grandTotal,
                                    'payload' => $apiData,
                                    'response' => $gatewayResponse,
                                    'status' => $gatewayStatus,
                                ];
                                
                                log_message('info', 'Agent\Sales::store - Logged invoice number to sales_gateway_logs: ' . $invoiceNo);
                            }
                        }
                        
                        // If still failed after retries, log the final attempt
                        if ($gatewayResponse === null && $retryCount > 0) {
                            $gatewayLogBuffer[] = [
                                'invoice_no' => $invoiceNo,
                                'platform_id' => $platformId,
                                'amount' => $grandTotal,
                                'payload' => $apiData,
                                'response' => null,
                                'status' => 'FAILED',
                            ];
                        }
                    }
                    
                    if ($gatewayResponse === null) {
                        $logFile = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.log';
                        $message = 'Gagal mengirim ke payment gateway. Silakan cek log di: ' . $logFile;
                        if ($isAjax) {
                            return $this->response->setJSON([
                                'status' => 'error', 
                                'message' => $message
                            ]);
                        }
                        return redirect()->back()->withInput()->with('message', ['status' => 'error', 'message' => $message]);
                    }
                } else {
                    // Offline/cash platform, bypass gateway
                    $isOfflinePlatform = true;
                    $settlementTime = date('Y-m-d H:i:s');
                }
            }
            
            // Start database transaction
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
            // Determine payment status based on payment type and gateway response
            $paymentStatus = '0'; // Default: unpaid
            if ($paymentType === 'paylater') {
                // Pay Later: set as paylater status
                $paymentStatus = '3'; // paylater
            } elseif ($isOfflinePlatform) {
                $paymentStatus = '2'; // paid (for offline platforms like cash)
            }
            if ($gatewayResponse && isset($gatewayResponse['status'])) {
                $gatewayStatus = strtoupper($gatewayResponse['status']);
                if ($gatewayStatus === 'PAID') {
                    $paymentStatus = '2'; // paid
                } elseif (in_array($gatewayStatus, ['PENDING', 'FAILED', 'CANCELED', 'EXPIRED'])) {
                    $paymentStatus = '0'; // unpaid
                }
            }
            
            // Get delivery address and note
            $deliveryAddress = !empty($postData['delivery_address']) ? trim($postData['delivery_address']) : '';
            $note = !empty($postData['note']) ? trim($postData['note']) : '';
            
            // Save to sales table
            // Use $invoiceNo variable which may have been updated if duplicate orderId was detected
            $finalInvoiceNo = $invoiceNo;
            $saleData = [
                'invoice_no' 		=> $finalInvoiceNo,
                'user_id' 			=> $userId,
                'customer_id' 		=> $loggedInAgentId, // Use logged-in agent ID instead of actual customer ID
                'warehouse_id' 		=> !empty($postData['agent_id']) ? (int)$postData['agent_id'] : null,
                'sale_channel' 		=> self::CHANNEL_ONLINE,
                'total_amount' 		=> (float)($postData['subtotal'] ?? $subtotal),
                'discount_amount' 	=> (float)($postData['discount'] ?? $discount),
                'tax_amount' 		=> $taxAmount,
                'tax_type' 			=> $taxType,
                'grand_total' 		=> (float)($postData['grand_total'] ?? $grandTotal),
                'payment_status' 	=> $paymentStatus,
                'delivery_address' 	=> $deliveryAddress,
                'note' 				=> $note
            ];
            
            // Add settlement_time if gateway response has it and status is PAID
            if ($isOfflinePlatform && !$settlementTime) {
                $settlementTime = date('Y-m-d H:i:s');
            }
            
            if ($gatewayResponse && isset($gatewayResponse['settlementTime']) && 
                isset($gatewayResponse['status']) && strtoupper($gatewayResponse['status']) === 'PAID') {
                try {
                    $settlementDateTime = new \DateTime($gatewayResponse['settlementTime']);
                    $saleData['settlement_time'] = $settlementDateTime->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    log_message('error', 'Agent\Sales::store - Invalid settlementTime format: ' . ($gatewayResponse['settlementTime'] ?? ''));
                }
            } elseif ($settlementTime) {
                $saleData['settlement_time'] = $settlementTime;
            }
                
            $this->model->skipValidation(true);
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
                log_message('error', 'Agent Sales insert failed: ' . $errorMsg);
                log_message('error', 'Agent Sales insert data: ' . json_encode($saleData));
                throw new \Exception($errorMsg);
            }
            $saleId = $this->model->getInsertID();
            if (!$saleId || $saleId == 0) {
                throw new \Exception('Gagal mendapatkan ID penjualan setelah insert.');
            }
            $this->model->skipValidation(false);

            if (!empty($gatewayLogBuffer) && !empty($saleId)) {
                foreach ($gatewayLogBuffer as $logEntry) {
                    $this->salesGatewayLogModel->logGatewayRequest(
                        $logEntry['invoice_no'],
                        $logEntry['platform_id'],
                        $logEntry['amount'],
                        $logEntry['payload'],
                        $logEntry['response'],
                        $logEntry['status'] ?? null,
                        $saleId
                    );
                }
                $gatewayLogBuffer = [];
            }

            // Create paylater transaction record if payment type is paylater
            if ($paymentType === 'paylater' && $selectedAgentId) {
                $this->agentPaylaterModel->skipValidation(true);
                $paylaterData = [
                    'agent_id' => $selectedAgentId,
                    'sale_id' => $saleId,
                    'mutation_type' => '1', // purchase
                    'amount' => $grandTotal, // positive value for purchase
                    'description' => 'Pembelian paylater - Invoice: ' . $finalInvoiceNo,
                    'reference_code' => $finalInvoiceNo
                ];
                $paylaterInsertResult = $this->agentPaylaterModel->insert($paylaterData);
                $this->agentPaylaterModel->skipValidation(false);
                
                if (!$paylaterInsertResult) {
                    $errors = $this->agentPaylaterModel->errors();
                    $errorMsg = 'Gagal membuat record paylater: ';
                    if ($errors && is_array($errors)) {
                        $errorMsg .= implode(', ', array_map(function($e) {
                            return is_array($e) ? json_encode($e) : $e;
                        }, $errors));
                    }
                    throw new \Exception($errorMsg);
                }
            }

            // Save to sales_detail table only
            $itemModel = new \App\Models\ItemModel();
            foreach ($cart as $item) {
                // Get item name
                $itemRecord = $itemModel->find((int)$item['item_id']);
                $itemName = 'Unknown';
                $isStockable = '0';
                if ($itemRecord) {
                    if (is_array($itemRecord)) {
                        $itemName = isset($itemRecord['name']) ? (string)$itemRecord['name'] : 'Unknown';
                        $isStockable = isset($itemRecord['is_stockable']) ? (string)$itemRecord['is_stockable'] : '0';
                    } else {
                        $itemName = isset($itemRecord->name) ? (string)$itemRecord->name : 'Unknown';
                        $isStockable = isset($itemRecord->is_stockable) ? (string)$itemRecord->is_stockable : '0';
                    }
                }

                // Note: SN validation removed for agent transactions
                // Agents can complete transactions without SN - SN will be assigned later by admin in SalesConfirm
                // The validation in SalesConfirm::assignSN() still enforces 1 SN = 1 Qty when admin assigns SNs

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

                // Save serial numbers to SalesItemSnModel (for agent orders, do NOT update item_sn.is_sell)
                // Serial numbers need manual admin confirmation before activation
                if (!empty($item['sns'])) {
                    // Check if sns is already an array or needs to be decoded
                    if (is_array($item['sns'])) {
                        $sns = $item['sns'];
                    } else {
                        $sns = json_decode($item['sns'], true);
                    }
                    
                    if (is_array($sns) && $salesDetailId) {
                        // Only save to SalesItemSnModel for tracking
                        // Do NOT update item_sn.is_sell or item_sn.is_activated
                        // Admin must manually confirm and assign serial numbers
                        foreach ($sns as $sn) {
                            if (!empty($sn['item_sn_id']) && !empty($sn['sn'])) {
                                $itemSnId = (int)$sn['item_sn_id'];
                                $snValue = (string)$sn['sn'];
                                
                                // Save to SalesItemSnModel only (for tracking)
                                $this->salesItemSnModel->skipValidation(true);
                                $salesItemSnData = [
                                    'sales_item_id' => $salesDetailId,
                                    'item_sn_id' => $itemSnId,
                                    'sn' => $snValue
                                ];
                                $this->salesItemSnModel->insert($salesItemSnData);
                                $this->salesItemSnModel->skipValidation(false);
                                
                                // NOTE: Do NOT update item_sn.is_sell or item_sn.is_activated here
                                // Admin must manually confirm payment and assign serial numbers
                            }
                        }
                    }
                }
            }
                
            // Save payment information if platform is selected
            // Only save if sale was created successfully
            if ($saleId && $platformId && $gatewayResponse) {
                // Determine payment method based on gateway code
                $paymentMethod = 'qris'; // default
                if (!empty($gatewayResponse['code'])) {
                    $gwCode = strtoupper($gatewayResponse['code']);
                    if (in_array($gwCode, ['QRIS', 'QR'])) {
                        $paymentMethod = 'qris';
                    } elseif (in_array($gwCode, ['BCA', 'MANDIRI', 'BNI', 'BRI'])) {
                        $paymentMethod = 'transfer';
                    } else {
                        $paymentMethod = 'other';
                    }
                }
                
                // Store full gateway response in response field (TEXT), note for manual notes
                $gatewayResponseJson = json_encode($gatewayResponse);
                
                $paymentData = [
                    'sale_id' => $saleId,
                    'platform_id' => $platformId,
                    'method' => $paymentMethod,
                    'amount' => (float)($postData['grand_total'] ?? $grandTotal),
                    'note' => '', // Manual notes if needed
                    'response' => $gatewayResponseJson // Full gateway response JSON
                ];
                
                $this->salesPaymentsModel->skipValidation(true);
                $paymentInsertResult = $this->salesPaymentsModel->insert($paymentData);
                $this->salesPaymentsModel->skipValidation(false);
                
                if (!$paymentInsertResult) {
                    $errors = $this->salesPaymentsModel->errors();
                    $errorMsg = 'Gagal menyimpan data pembayaran: ';
                    if ($errors && is_array($errors)) {
                        $errorMsg .= implode(', ', $errors);
                    }
                    throw new \Exception($errorMsg);
                }
                
                // Log payment request/response
                $this->salesPaymentLogModel->skipValidation(true);
                $this->salesPaymentLogModel->insert([
                    'sale_id' => $saleId,
                    'platform_id' => $platformId,
                    'response' => $gatewayResponseJson
                ]);
                $this->salesPaymentLogModel->skipValidation(false);
            } elseif ($saleId && $platformId) {
                // Platform selected but no gateway response (cash payment via platform like "Tunai")
                $paymentData = [
                    'sale_id' => $saleId,
                    'platform_id' => $platformId,
                    'method' => 'cash',
                    'amount' => (float)($postData['grand_total'] ?? $grandTotal),
                    'note' => ''
                ];
                
                $this->salesPaymentsModel->skipValidation(true);
                $paymentInsertResult = $this->salesPaymentsModel->insert($paymentData);
                $this->salesPaymentsModel->skipValidation(false);
                
                if (!$paymentInsertResult) {
                    $errors = $this->salesPaymentsModel->errors();
                    $errorMsg = 'Gagal menyimpan data pembayaran: ';
                    if ($errors && is_array($errors)) {
                        $errorMsg .= implode(', ', $errors);
                    } else {
                        $dbError = $db->error();
                        if ($dbError) {
                            $errorMsg .= 'Database error: ' . (is_array($dbError) ? json_encode($dbError) : $dbError);
                        }
                    }
                    throw new \Exception($errorMsg);
                }
                
                // Log offline payment confirmation
                $offlineLogPayload = [
                    'status' => 'PAID',
                    'channel' => 'offline',
                    'message' => 'Pembayaran ditandai lunas tanpa gateway',
                    'recorded_at' => date('c'),
                    'platform' => [
                        'id' => $platformId,
                        'name' => $platform['platform'] ?? ''
                    ],
                    'amount' => (float)($postData['grand_total'] ?? $grandTotal)
                ];
                
                $this->salesPaymentLogModel->skipValidation(true);
                $this->salesPaymentLogModel->insert([
                    'sale_id' => $saleId,
                    'platform_id' => $platformId,
                    'response' => json_encode($offlineLogPayload)
                ]);
                $this->salesPaymentLogModel->skipValidation(false);
            }

            if ($shouldDeductCredit && $selectedAgentId) {
                $this->agentModel->skipValidation(true);
                $updateResult = $this->agentModel->update($selectedAgentId, [
                    'credit_limit' => $agentCreditAfter
                ]);
                $this->agentModel->skipValidation(false);
                if (!$updateResult) {
                    throw new \Exception('Gagal memperbarui limit kredit agen.');
                }
            }

            // Complete transaction
            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi gagal.');
            }
                
                // Auto-fetch latest payment status from gateway if payment was created with gateway
                if ($saleId && $platformId && $gatewayResponse) {
                    try {
                        // Use final invoice number (may have been regenerated)
                        $checkInvoiceNo = $finalInvoiceNo;
                        $latestGatewayResponse = $this->getPaymentStatusFromGateway($checkInvoiceNo);
                        
                        if ($latestGatewayResponse !== null) {
                            // Update payment record with latest gateway response
                            $paymentRecord = $this->salesPaymentsModel
                                ->where('sale_id', $saleId)
                                ->where('platform_id', $platformId)
                                ->first();
                            
                            if ($paymentRecord) {
                                $latestResponseJson = json_encode($latestGatewayResponse);
                                $this->salesPaymentsModel->skipValidation(true);
                                $this->salesPaymentsModel->update($paymentRecord['id'], [
                                    'response' => $latestResponseJson
                                ]);
                                $this->salesPaymentsModel->skipValidation(false);
                                
                                // Update gateway response variable for response data
                                $gatewayResponse = $latestGatewayResponse;
                                
                                log_message('info', 'Agent\Sales::store - Updated payment response with latest gateway data for invoice: ' . $invoiceNo);
                            }
                        }
                    } catch (\Exception $e) {
                        // Don't fail the transaction if status fetch fails, just log it
                        log_message('error', 'Agent\Sales::store - Failed to fetch latest payment status: ' . $e->getMessage());
                    }
                }
                
                // Clear cart
                $this->session->remove('agent_cart');
                
                // Use final invoice number (may have been regenerated)
                $finalInvoiceNoForMessage = $finalInvoiceNo;
                log_message('info', "Agent Sales transaction created: ID {$saleId}, Invoice: {$finalInvoiceNoForMessage}");
                
                $message = 'Penjualan berhasil disimpan. Invoice: ' . $finalInvoiceNoForMessage;
                $responseData = ['id' => $saleId];
                
                // Include gateway response data (QR code URL) if available
                // Only calculate totalReceive if platform.gw_status = 1 (gateway is active)
                if ($gatewayResponse && !empty($gatewayResponse['url'])) {
                    $totalReceive = 0;
                    
                    // Calculate totalReceive based on chargeCustomerForPaymentGatewayFee
                    if (isset($gatewayResponse['chargeCustomerForPaymentGatewayFee']) && 
                        isset($gatewayResponse['originalAmount'])) {
                        
                        $chargeCustomer = $gatewayResponse['chargeCustomerForPaymentGatewayFee'];
                        $originalAmount = (float) ($gatewayResponse['originalAmount'] ?? $grandTotal);
                        
                        if ($chargeCustomer === true || $chargeCustomer === 'true' || $chargeCustomer === 1 || $chargeCustomer === '1') {
                            // Customer is charged the fee, so totalReceive = originalAmount
                            $totalReceive = $originalAmount;
                        } else {
                            // Customer is NOT charged the fee, so totalReceive = originalAmount - paymentGatewayAdminFee
                            $adminFee = (float) ($gatewayResponse['paymentGatewayAdminFee'] ?? 0);
                            $totalReceive = $originalAmount - $adminFee;
                        }
                    } else {
                        // Fallback: if fields not present, use grand_total
                        $totalReceive = $grandTotal;
                    }
                    
                    $responseData['gateway'] = [
                        'url' => $gatewayResponse['url'],
                        'status' => $gatewayResponse['status'] ?? 'PENDING',
                        'paymentGatewayAdminFee' => $gatewayResponse['paymentGatewayAdminFee'] ?? 0,
                        'originalAmount' => $gatewayResponse['originalAmount'] ?? $grandTotal,
                        'chargeCustomerForPaymentGatewayFee' => $gatewayResponse['chargeCustomerForPaymentGatewayFee'] ?? false,
                        'totalReceive' => $totalReceive
                    ];
                }
                
                if ($isAjax) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => $message,
                        'data' => $responseData
                    ]);
                }
                
                return redirect()->to("agent/sales/{$saleId}")->with('message', [
                    'status' => 'success',
                    'message' => $message
                ]);
            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Agent\Sales::store error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            
            $message = 'Gagal menyimpan penjualan: ' . $e->getMessage();
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'error', 'message' => $message]);
            }
            
            return redirect()->back()->withInput()->with('message', [
                'status' => 'error',
                'message' => $message
            ]);
        }
    }
    
    /**
     * Encrypt API key with RSA public key
     * 
     * Uses RSA encryption with PKCS1 padding (equivalent to RSA/ECB/PKCS1Padding in Java/Kotlin)
     * 
     * @param string $data Data to encrypt (API key)
     * @param string $base64PublicKey Base64 encoded public key (DER format)
     * @return string Base64 encoded encrypted data
     * @throws \Exception If encryption fails
     */
    protected function encryptApiKey(string $data, string $base64PublicKey): string
    {
        // The base64PublicKey is in base64 DER format (SubjectPublicKeyInfo)
        // We need to convert it to PEM format for OpenSSL
        
        // Remove any whitespace from the key
        $base64PublicKey = trim(preg_replace('/\s+/', '', $base64PublicKey));
        
        // Verify it's valid base64
        if (!preg_match('/^[A-Za-z0-9+\/]+=*$/', $base64PublicKey)) {
            throw new \Exception('Invalid base64 public key format');
        }
        
        // Decode base64 to get DER binary
        $derKey = base64_decode($base64PublicKey, true);
        if ($derKey === false) {
            throw new \Exception('Failed to decode base64 public key');
        }
        
        // Re-encode DER to base64 for PEM format (PEM body is base64-encoded DER)
        $pemBody = base64_encode($derKey);
        
        // Build PEM format with proper line breaks (64 chars per line)
        $pemKey = "-----BEGIN PUBLIC KEY-----" . PHP_EOL;
        $pemKey .= chunk_split($pemBody, 64, PHP_EOL);
        $pemKey = rtrim($pemKey, PHP_EOL) . PHP_EOL; // Remove trailing newline
        $pemKey .= "-----END PUBLIC KEY-----" . PHP_EOL;
        
        // Clear any previous OpenSSL errors
        while (openssl_error_string() !== false) {
            // Clear errors
        }
        
        // Try to load the public key
        $publicKeyResource = openssl_pkey_get_public($pemKey);
        
        if ($publicKeyResource === false) {
            // If that fails, try using the original base64 string directly
            // (in case it's already in the correct format)
            $pemKeyAlt = "-----BEGIN PUBLIC KEY-----" . PHP_EOL;
            $pemKeyAlt .= chunk_split($base64PublicKey, 64, PHP_EOL);
            $pemKeyAlt = rtrim($pemKeyAlt, PHP_EOL) . PHP_EOL;
            $pemKeyAlt .= "-----END PUBLIC KEY-----" . PHP_EOL;
            
            // Clear errors
            while (openssl_error_string() !== false) {
                // Clear errors
            }
            
            $publicKeyResource = openssl_pkey_get_public($pemKeyAlt);
            
            if ($publicKeyResource === false) {
                // Collect all errors
                $errors = [];
                while (($error = openssl_error_string()) !== false) {
                    $errors[] = $error;
                }
                $errorMsg = implode('; ', $errors);
                
                // Write debug info to file
                $debugFile = WRITEPATH . 'logs/key-debug-' . date('Y-m-d') . '.txt';
                $debugInfo = "Key Debug Info:\n";
                $debugInfo .= "Base64 Key Length: " . strlen($base64PublicKey) . "\n";
                $debugInfo .= "DER Key Length: " . strlen($derKey) . " bytes\n";
                $debugInfo .= "PEM Key:\n" . $pemKey . "\n";
                $debugInfo .= "Errors: " . $errorMsg . "\n";
                @file_put_contents($debugFile, $debugInfo, FILE_APPEND);
                
                throw new \Exception('Failed to load public key: ' . ($errorMsg ?: 'Unknown error') . '. Debug info saved to: ' . $debugFile);
            }
        }
        
        // Encrypt data with RSA and PKCS1 padding (RSA/ECB/PKCS1Padding equivalent)
        $encrypted = '';
        $success = openssl_public_encrypt($data, $encrypted, $publicKeyResource, OPENSSL_PKCS1_PADDING);
        
        // Free the key resource
        openssl_free_key($publicKeyResource);
        
        if (!$success || empty($encrypted)) {
            $errors = [];
            while (($error = openssl_error_string()) !== false) {
                $errors[] = $error;
            }
            $errorMsg = implode('; ', $errors);
            throw new \Exception('RSA encryption failed: ' . ($errorMsg ?: 'Unknown encryption error'));
        }
        
        // Return base64-encoded ciphertext
        return base64_encode($encrypted);
    }
    
    /**
     * Call payment gateway API
     * 
     * @param array $apiData API request data
     * @return array|null Gateway response or null on failure
     */
    protected function callPaymentGateway(array $apiData): ?array
    {
        $errorDetails = [];
        
        try {
            // Encrypt API key for x-api-key header
            // $encryptedApiKey = null;
            // try {
            //     $encryptedApiKey = $this->encryptApiKey(self::GATEWAY_API_KEY, self::GATEWAY_PUBLIC_KEY);
            //     $errorDetails['encryption'] = 'success';
            //     log_message('error', 'Sales::callPaymentGateway - API key encrypted successfully');
            // } catch (\Exception $e) {
            //     $errorDetails['encryption'] = 'failed: ' . $e->getMessage();
            //     log_message('error', 'Sales::callPaymentGateway - API key encryption failed: ' . $e->getMessage());
                    
            //     // Write to debug file immediately
            //     $debugFile = WRITEPATH . 'logs/gateway-debug-' . date('Y-m-d') . '.txt';
            //     @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Encryption failed: " . $e->getMessage() . "\n", FILE_APPEND);
                    
            //     return null;
            // }
            
            $client = \Config\Services::curlrequest();
            $apiUrl = 'https://dev.osu.biz.id/mig/esb/v1/api/payments';
            
            // // Write debug info immediately
            // $debugFile = WRITEPATH . 'logs/gateway-debug-' . date('Y-m-d') . '.txt';
            // $debugContent = date('Y-m-d H:i:s') . " - Starting gateway call\n";
            // $debugContent .= "URL: " . $apiUrl . "\n";
            // $debugContent .= "Payload: " . json_encode($apiData, JSON_PRETTY_PRINT) . "\n";
            // @file_put_contents($debugFile, $debugContent, FILE_APPEND);
            
            // // Log the request details (without sensitive data)
            // log_message('error', 'Sales::callPaymentGateway - Request URL: ' . $apiUrl);
            // log_message('error', 'Sales::callPaymentGateway - Request Payload: ' . json_encode($apiData, JSON_PRETTY_PRINT));
            // log_message('error', 'Sales::callPaymentGateway - x-api-key header: ' . substr($encryptedApiKey, 0, 20) . '...');
            
            try {
                $response = $client->request(
                    'POST',
                    $apiUrl,
                    [
                        'headers' => [
                            'Content-Type'  => 'application/json',
                            'Accept'        => 'application/json',
                            'x-api-key'     => 'Lmp1xKoggDE4FH2SKk/d/hqRiF+uxyAZOtO/piLOdox1F0OPr/RyLbhH0JyzNJY2zTI9uEEG4P2Hgeh/i8fiD7ZjsMTEWJXgx8Zgdp74nAOLtel/zi9Z611c+GG4Ra0nMx5K2UjOeZvWFyfXDOuILmu4zYL+MyyW8uSGYO8ug9a17HS6tlmzg7PkdEEb2XzNQ84ahKTRxFTTrxJiFGa34FO0rzLjeNGTV5KihVwUkZjL67DrfiSZweUsKX8NNHgxHy242KPcRWcJ5/sLH/Klus9LRfx9pC3F4gzNr3k1VvoAP5Kv9DTP6IGOZshgDu8WnUAcsvDJG4wtpkZgvYBoUg=='
                        ],
                        'json'        => $apiData,
                        'timeout'     => 30,
                        'http_errors' => false,
                    ]
                );
                $errorDetails['request'] = 'sent';
            } catch (\Exception $e) {
                $errorDetails['request'] = 'failed: ' . $e->getMessage();
                // @file_put_contents(
                //     $debugFile,
                //     date('Y-m-d H:i:s') . " - Request exception: " . $e->getMessage() . "\n",
                //     FILE_APPEND
                // );
                throw $e;
            }
            
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $responseData = json_decode($body, true);
            
            // Log response details
            log_message('error', 'Sales::callPaymentGateway - Response Status: ' . $statusCode);
            log_message('error', 'Sales::callPaymentGateway - Response Body: ' . $body);
            
            if ($statusCode >= 200 && $statusCode < 300 && $responseData) {
                // Return full response data (includes paymentCode, expiredAt, etc.)
                // Don't format it - keep all fields from gateway
                log_message('error', 'Sales::callPaymentGateway - Gateway response received successfully');
                return $responseData;
            } else {
                $errorMsg = 'Payment gateway returned error';
                if (isset($responseData['message'])) {
                    $errorMsg = $responseData['message'];
                } elseif (!empty($body)) {
                    $errorMsg = 'Response: ' . $body;
                }
                
                // Log detailed error information
                $logMsg = 'Sales::callPaymentGateway - API Error: ' . $errorMsg . ' | Status: ' . $statusCode . ' | Body: ' . $body;
                log_message('error', $logMsg);
                
                // Also write to a separate debug file to ensure we capture it
                $debugFile = WRITEPATH . 'logs/gateway-debug-' . date('Y-m-d') . '.txt';
                file_put_contents($debugFile, date('Y-m-d H:i:s') . " - " . $logMsg . "\n", FILE_APPEND);
                
                return null;
            }
            
        } catch (\Exception $e) {
            $errorMsg = 'Sales::callPaymentGateway error: ' . $e->getMessage();
            $traceMsg = 'Sales::callPaymentGateway trace: ' . $e->getTraceAsString();
            
            log_message('error', $errorMsg);
            log_message('error', $traceMsg);
            
            // Also write to debug file
            $debugFile = WRITEPATH . 'logs/gateway-debug-' . date('Y-m-d') . '.txt';
            file_put_contents($debugFile, date('Y-m-d H:i:s') . " - " . $errorMsg . "\n" . $traceMsg . "\n", FILE_APPEND);
            
            return null;
        }
    }
    
    /**
     * Get payment status from gateway API (GET request)
     * 
     * @param string $invoiceNo Invoice number (orderId)
     * @return array|null Gateway response or null on failure
     */
    protected function getPaymentStatusFromGateway(string $invoiceNo): ?array
    {
        try {
            // Encrypt API key for x-api-key header
            // $encryptedApiKey = '';
            // try {
            //     $encryptedApiKey = $this->encryptApiKey(self::GATEWAY_API_KEY, self::GATEWAY_PUBLIC_KEY);
            // } catch (\Exception $e) {
            //     log_message('error', 'Agent\Sales::getPaymentStatusFromGateway - API key encryption failed: ' . $e->getMessage());
            //     return null;
            // }

            $encryptedApiKey = 'Lmp1xKoggDE4FH2SKk/d/hqRiF+uxyAZOtO/piLOdox1F0OPr/RyLbhH0JyzNJY2zTI9uEEG4P2Hgeh/i8fiD7ZjsMTEWJXgx8Zgdp74nAOLtel/zi9Z611c+GG4Ra0nMx5K2UjOeZvWFyfXDOuILmu4zYL+MyyW8uSGYO8ug9a17HS6tlmzg7PkdEEb2XzNQ84ahKTRxFTTrxJiFGa34FO0rzLjeNGTV5KihVwUkZjL67DrfiSZweUsKX8NNHgxHy242KPcRWcJ5/sLH/Klus9LRfx9pC3F4gzNr3k1VvoAP5Kv9DTP6IGOZshgDu8WnUAcsvDJG4wtpkZgvYBoUg==';
            
            $client = \Config\Services::curlrequest();
            $apiUrl = 'https://dev.osu.biz.id/mig/esb/v1/api/payments/' . urlencode($invoiceNo);
            
            log_message('info', 'Agent\Sales::getPaymentStatusFromGateway - Fetching payment status for: ' . $invoiceNo);
            
            $response = $client->request('GET', $apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'x-api-key' => $encryptedApiKey
                ],
                'timeout' => 30,
                'http_errors' => false
            ]);
            
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $responseData = json_decode($body, true);
            
            log_message('info', 'Agent\Sales::getPaymentStatusFromGateway - Response Status: ' . $statusCode);
            log_message('info', 'Agent\Sales::getPaymentStatusFromGateway - Response Body: ' . $body);
            
            if ($statusCode >= 200 && $statusCode < 300 && $responseData) {
                // Return full gateway response (includes status, paymentCode, expiredAt, orderId, etc.)
                // Response format: { "status": "PAID", "paymentCode": "...", "expiredAt": "...", ... }
                return $responseData;
            } else {
                $errorMsg = 'Payment gateway returned error';
                if (isset($responseData['message'])) {
                    $errorMsg = $responseData['message'];
                } elseif (!empty($body)) {
                    $errorMsg = 'Response: ' . $body;
                }
                log_message('error', 'Agent\Sales::getPaymentStatusFromGateway - API Error: ' . $errorMsg . ' | Status: ' . $statusCode);
                return null;
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Agent\Sales::getPaymentStatusFromGateway error: ' . $e->getMessage());
            log_message('error', 'Agent\Sales::getPaymentStatusFromGateway trace: ' . $e->getTraceAsString());
            return null;
        }
    }
    
    /**
     * Display agent sales list page
     * 
     * @return void
     */
    public function index(): void
    {        
        // Pass permission data to view
        $isAdmin = $this->hasPermission('read_all');
        $this->data['read_all']   = $isAdmin;

        if ($isAdmin) {
            $title = 'Penjualan (Online)';
        } else {
            $title = 'Pembelian';
        }

        // Get all active agents for filter dropdown (admin only)
        $agents = [];
        if ($isAdmin) {
            $agents = $this->agentModel
                ->where('is_active', '1')
                ->orderBy('name', 'ASC')
                ->findAll();
        }

        // Get logged-in agent ID for statistics calculation (agent users only)
        $agentId = null;
        $statistics = [
            'total_loan' => 0,
            'total_paid' => 0,
            'total_amount' => 0
        ];

        if (!$isAdmin) {
            $userId = $this->user['id_user'] ?? null;
            if ($userId) {
                $userRoleAgent = $this->userRoleAgentModel->where('user_id', $userId)->first();
                if ($userRoleAgent) {
                    $agentId = $userRoleAgent->agent_id;
                }
            }

            // Calculate statistics for agent users
            if ($agentId) {
                $db = \Config\Database::connect();
                
                // Total Loan: sum of grand_total where payment_status = 3
                $totalLoan = $db->table('sales')
                    ->selectSum('grand_total')
                    ->where('sale_channel', self::CHANNEL_ONLINE)
                    ->where('warehouse_id', $agentId)
                    ->where('payment_status', '3')
                    ->get()
                    ->getRow();
                $statistics['total_loan'] = (float)($totalLoan->grand_total ?? 0);
                
                // Total Paid: sum of total_payment where payment_status = 2
                $totalPaid = $db->table('sales')
                    ->selectSum('total_payment')
                    ->where('sale_channel', self::CHANNEL_ONLINE)
                    ->where('warehouse_id', $agentId)
                    ->where('payment_status', '2')
                    ->get()
                    ->getRow();
                $statistics['total_paid'] = (float)($totalPaid->total_payment ?? 0);
                
                // Total Amount: sum of grand_total for all sales
                $totalAmount = $db->table('sales')
                    ->selectSum('grand_total')
                    ->where('sale_channel', self::CHANNEL_ONLINE)
                    ->where('warehouse_id', $agentId)
                    ->get()
                    ->getRow();
                $statistics['total_amount'] = (float)($totalAmount->grand_total ?? 0);
            }
        }

        $this->data = array_merge($this->data, [
            'title'         => 'Data ' . $title,
            'currentModule' => $this->currentModule,
            'config'        => $this->config,
            'msg'           => $this->session->getFlashdata('message'),
            'agents'        => $agents,
            'statistics'    => $statistics,
        ]);

        $this->data['breadcrumb'] = [
            'Home'                 => $this->config->baseURL,
            'Data ' . $title       => '', // Current page, no link
        ];

        $this->view('sales/agent/sales-result', $this->data);
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

            // Get fees for this sale
            $fees = $this->salesFeeModel->getFeesBySale($id);

            // Get fee types for dropdown
            $feeTypes = $this->feeTypeModel->getActiveFeeTypes();

            // Get payment information
            $payments = $this->salesPaymentsModel->getPaymentsBySale($id);
            $paymentInfo = null;
            $gatewayResponse = null;
            
            if (!empty($payments)) {
                $payment = $payments[0]; // Get first payment (usually only one)
                $paymentInfo = $payment;
                
                // Decode gateway response if exists
                if (!empty($payment['response'])) {
                    $gatewayResponse = json_decode($payment['response'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $gatewayResponse = null;
                    }
                }
            }

            // Check if user is admin (has read_all or update_all permission)
            $userPermission = is_array($this->userPermission) ? $this->userPermission : [];
            $isAdmin = key_exists('read_all', $userPermission) || key_exists('update_all', $userPermission);

            $this->data['title'] = ($isAdmin ? 'Penjualan' : 'Pembelian');
            $this->data['currentModule'] = 'Agen &laquo; ' . $sale['agent_name'];
            $this->data['config'] = $this->config;
            $this->data['sale'] = $sale;
            $this->data['items'] = $items;
            $this->data['fees'] = $fees;
            $this->data['feeTypes'] = $feeTypes;
            $this->data['isAgent'] = true; // Always true in agent context
            $this->data['isAdmin'] = $isAdmin;
            $this->data['payment'] = $paymentInfo;
            $this->data['gatewayResponse'] = $gatewayResponse;

            $this->data['breadcrumb'] = [
                'Home'               => $this->config->baseURL.'agent/dashboard',
                ($isAdmin ? 'Penjualan' : 'Pembelian') => $this->config->baseURL.'agent/sales',
                'Detail'             => '', // Current page, no link
            ];

            $this->view('sales/agent/sales-detail', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Sales::detail error: ' . $e->getMessage());
            return redirect()->to('sales')->with('message', [
                'status' => 'error',
                'message' => 'Gagal memuat detail penjualan.'
            ]);
        }
    }

    /**
     * Refresh payment status from gateway (AJAX endpoint)
     * 
     * @param int $id Sale ID
     * @return ResponseInterface
     */
    public function refreshPaymentStatus(int $id): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method'
            ])->setStatusCode(400);
        }

        try {
            // Get sale by ID
            $sale = $this->model->find($id);
            
            if (!$sale) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data penjualan tidak ditemukan.'
                ])->setStatusCode(404);
            }

            // Get invoice number
            $invoiceNo = $sale['invoice_no'] ?? null;
            
            if (empty($invoiceNo)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Nomor invoice tidak ditemukan.'
                ])->setStatusCode(400);
            }

            // Fetch latest payment status from gateway (GET request only)
            $gatewayResponse = $this->getPaymentStatusFromGateway($invoiceNo);
            
            if ($gatewayResponse === null) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal mengambil status pembayaran dari gateway. Silakan coba lagi.'
                ])->setStatusCode(500);
            }

            // Auto-update sales_payments.response with latest gateway data
            try {
                $payments = $this->salesPaymentsModel->getPaymentsBySale($id);
                
                if (!empty($payments)) {
                    $payment = $payments[0]; // Get first payment
                    $gatewayResponseJson = json_encode($gatewayResponse);
                    
                    $this->salesPaymentsModel->skipValidation(true);
                    $this->salesPaymentsModel->update($payment['id'], [
                        'response' => $gatewayResponseJson
                    ]);
                    $this->salesPaymentsModel->skipValidation(false);
                    
                    log_message('info', 'Agent\Sales::refreshPaymentStatus - Updated payment response for sale ID: ' . $id);
                }
            } catch (\Exception $e) {
                // Don't fail the request if database update fails, just log it
                log_message('error', 'Agent\Sales::refreshPaymentStatus - Failed to update payment response: ' . $e->getMessage());
            }

            // Auto-update sales table payment_status based on gateway status
            try {
                $gatewayStatus = strtoupper($gatewayResponse['status'] ?? '');
                
                // Map API status to payment_status
                // payment_status: 0=unpaid, 1=partial, 2=paid
                $paymentStatusMap = [
                    'PAID' => '2',      // paid
                    'PENDING' => '0',   // unpaid
                    'FAILED' => '0',    // unpaid
                    'CANCELED' => '0',  // unpaid
                    'EXPIRED' => '0'    // unpaid
                ];
                
                $paymentStatus = $paymentStatusMap[$gatewayStatus] ?? '0';
                
                // Prepare update data
                $updateData = [
                    'payment_status' => $paymentStatus
                ];
                
                // Add settlement_time if provided and status is PAID
                if ($gatewayStatus === 'PAID' && !empty($gatewayResponse['settlementTime'])) {
                    // Parse settlementTime (ISO 8601 format: 2025-11-17T10:43:05)
                    try {
                        $settlementDateTime = new \DateTime($gatewayResponse['settlementTime']);
                        $updateData['settlement_time'] = $settlementDateTime->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        // Continue without settlement_time if parsing fails
                        log_message('warning', 'Agent\Sales::refreshPaymentStatus - Failed to parse settlementTime: ' . $e->getMessage());
                    }
                }
                
                // Update sale record
                $this->model->skipValidation(true);
                $updateResult = $this->model->update($id, $updateData);
                $this->model->skipValidation(false);
                
                if ($updateResult) {
                    log_message('info', 'Agent\Sales::refreshPaymentStatus - Updated sales payment_status to ' . $paymentStatus . ' for sale ID: ' . $id);
                } else {
                    $errors = $this->model->errors();
                    $errorMsg = 'Failed to update sale payment_status';
                    if ($errors && is_array($errors)) {
                        $errorMsg .= ': ' . implode(', ', $errors);
                    }
                    log_message('error', 'Agent\Sales::refreshPaymentStatus - ' . $errorMsg);
                }
            } catch (\Exception $e) {
                // Don't fail the request if database update fails, just log it
                log_message('error', 'Agent\Sales::refreshPaymentStatus - Failed to update sales payment_status: ' . $e->getMessage());
            }

            // Return gateway response (includes status, paymentCode, expiredAt, orderId, etc.)
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Status pembayaran berhasil diperiksa dan diperbarui.',
                'gatewayResponse' => $gatewayResponse // Includes 'status' field: 'PAID', 'PENDING', etc.
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Agent\Sales::refreshPaymentStatus error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui status pembayaran: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Add fee to sale (AJAX endpoint - Agent only)
     * 
     * @param int $saleId Sale ID
     * @return ResponseInterface
     */
    public function addFee(int $saleId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ])->setStatusCode(400);
        }

        // Verify sale exists
        $sale = $this->model->find($saleId);
        if (!$sale) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data penjualan tidak ditemukan.'
            ])->setStatusCode(404);
        }

        $postData = $this->request->getPost();
        $feeTypeId = (int)($postData['fee_type_id'] ?? 0);
        $feeName = trim($postData['fee_name'] ?? '');
        $amount = (float)($postData['amount'] ?? 0);

        // Validate
        if ($feeTypeId <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jenis biaya harus dipilih.'
            ]);
        }

        if ($amount <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jumlah biaya harus lebih dari 0.'
            ]);
        }

        // Verify fee type exists
        $feeType = $this->feeTypeModel->find($feeTypeId);
        if (!$feeType) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jenis biaya tidak ditemukan.'
            ]);
        }

        // Insert fee
        $feeData = [
            'sale_id' => $saleId,
            'fee_type_id' => $feeTypeId,
            'fee_name' => !empty($feeName) ? $feeName : null,
            'amount' => $amount,
        ];

        if ($this->salesFeeModel->insert($feeData)) {
            // Get updated fee with type info
            $feeId = $this->salesFeeModel->getInsertID();
            $fee = $this->salesFeeModel->getFeesBySale($saleId);
            $newFee = null;
            foreach ($fee as $f) {
                if ($f['id'] == $feeId) {
                    $newFee = $f;
                    break;
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Biaya tambahan berhasil ditambahkan.',
                'fee' => $newFee
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menambahkan biaya tambahan: ' . implode(', ', $this->salesFeeModel->errors())
            ]);
        }
    }

    /**
     * Update fee (AJAX endpoint - Agent only)
     * 
     * @param int $saleId Sale ID
     * @param int $feeId Fee ID
     * @return ResponseInterface
     */
    public function updateFee(int $saleId, int $feeId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ])->setStatusCode(400);
        }

        // Verify sale exists
        $sale = $this->model->find($saleId);
        if (!$sale) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data penjualan tidak ditemukan.'
            ])->setStatusCode(404);
        }

        // Verify fee exists and belongs to this sale
        $fee = $this->salesFeeModel->find($feeId);
        if (!$fee || (int)$fee['sale_id'] !== $saleId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Biaya tambahan tidak ditemukan.'
            ])->setStatusCode(404);
        }

        $postData = $this->request->getPost();
        $feeTypeId = (int)($postData['fee_type_id'] ?? 0);
        $feeName = trim($postData['fee_name'] ?? '');
        $amount = (float)($postData['amount'] ?? 0);

        // Validate
        if ($feeTypeId <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jenis biaya harus dipilih.'
            ]);
        }

        if ($amount <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jumlah biaya harus lebih dari 0.'
            ]);
        }

        // Verify fee type exists
        $feeType = $this->feeTypeModel->find($feeTypeId);
        if (!$feeType) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Jenis biaya tidak ditemukan.'
            ]);
        }

        // Update fee
        $feeData = [
            'fee_type_id' => $feeTypeId,
            'fee_name' => !empty($feeName) ? $feeName : null,
            'amount' => $amount,
        ];

        if ($this->salesFeeModel->update($feeId, $feeData)) {
            // Get updated fee with type info
            $updatedFee = $this->salesFeeModel->getFeesBySale($saleId);
            $feeResult = null;
            foreach ($updatedFee as $f) {
                if ($f['id'] == $feeId) {
                    $feeResult = $f;
                    break;
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Biaya tambahan berhasil diubah.',
                'fee' => $feeResult
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengubah biaya tambahan: ' . implode(', ', $this->salesFeeModel->errors())
            ]);
        }
    }

    /**
     * Delete fee (AJAX endpoint - Agent only)
     * 
     * @param int $saleId Sale ID
     * @param int $feeId Fee ID
     * @return ResponseInterface
     */
    public function deleteFee(int $saleId, int $feeId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ])->setStatusCode(400);
        }

        // Verify sale exists
        $sale = $this->model->find($saleId);
        if (!$sale) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data penjualan tidak ditemukan.'
            ])->setStatusCode(404);
        }

        // Verify fee exists and belongs to this sale
        $fee = $this->salesFeeModel->find($feeId);
        if (!$fee || (int)$fee['sale_id'] !== $saleId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Biaya tambahan tidak ditemukan.'
            ])->setStatusCode(404);
        }

        // Delete fee
        if ($this->salesFeeModel->delete($feeId)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Biaya tambahan berhasil dihapus.'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus biaya tambahan.'
            ]);
        }
    }

    /**
     * Update note (courier/air waybill) (AJAX endpoint - Agent only)
     * 
     * @param int $saleId Sale ID
     * @return ResponseInterface
     */
    public function updateNote(int $saleId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ])->setStatusCode(400);
        }

        // Verify sale exists
        $sale = $this->model->find($saleId);
        if (!$sale) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data penjualan tidak ditemukan.'
            ])->setStatusCode(404);
        }

        $postData = $this->request->getPost();
        $note = trim($postData['note'] ?? '');

        // Update note
        $updateData = [
            'note' => !empty($note) ? $note : null,
        ];

        if ($this->model->update($saleId, $updateData)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Catatan berhasil disimpan.',
                'note' => $note
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan catatan: ' . implode(', ', $this->model->errors())
            ]);
        }
    }

    /**
     * Update admin note (courier/AWB) for a sale
     * Only accessible by admins
     * 
     * @param int $saleId Sale ID
     * @return ResponseInterface
     */
    public function updateAdminNote(int $saleId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ])->setStatusCode(400);
        }

        // Check if user is admin (has read_all or update_all permission)
        $userPermission = is_array($this->userPermission) ? $this->userPermission : [];
        $isAdmin = key_exists('read_all', $userPermission) || key_exists('update_all', $userPermission);
        
        // Only admins can update admin note
        if (!$isAdmin) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Hanya admin yang dapat mengupdate catatan admin.'
            ])->setStatusCode(403);
        }

        // Verify sale exists
        $sale = $this->model->find($saleId);
        if (!$sale) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data penjualan tidak ditemukan.'
            ])->setStatusCode(404);
        }

        $postData = $this->request->getPost();
        $adminNote = trim($postData['admin_note'] ?? '');

        // Update admin note
        $updateData = [
            'admin_note' => !empty($adminNote) ? $adminNote : null,
        ];

        if ($this->model->update($saleId, $updateData)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Catatan admin berhasil disimpan.',
                'admin_note' => $adminNote
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan catatan admin: ' . implode(', ', $this->model->errors())
            ]);
        }
    }
    
    /**
     * Display activation form page
     * 
     * @param int $id Sales item SN ID
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function activateForm(int $id)
    {
        if ($id <= 0) {
            return redirect()->to($this->config->baseURL . 'agent/sales/sn')->with('message', [
                'status' => 'error',
                'message' => 'ID serial number tidak valid.'
            ]);
        }

        try {
            // Get SN record with item warranty
            $db = \Config\Database::connect();
            $snRecord = $db->table('sales_item_sn')
                ->select('sales_item_sn.*, item.warranty as item_warranty')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'left')
                ->join('item', 'item.id = sales_detail.item_id', 'left')
                ->where('sales_item_sn.id', $id)
                ->get()
                ->getRowArray();
            
            if (!$snRecord) {
                return redirect()->to($this->config->baseURL . 'agent/sales/sn')->with('message', [
                    'status' => 'error',
                    'message' => 'Serial number tidak ditemukan.'
                ]);
            }

            // Check if already activated
            if (!empty($snRecord['activated_at'])) {
                return redirect()->to($this->config->baseURL . 'agent/sales/sn')->with('message', [
                    'status' => 'warning',
                    'message' => 'Serial number ini sudah diaktifasi.'
                ]);
            }

            // Get warranty in months (convert to days for JavaScript)
            $itemWarranty = isset($snRecord['item_warranty']) ? (int)$snRecord['item_warranty'] : 0;
            $itemWarrantyDays = $itemWarranty * 30; // Convert months to days (1 month = 30 days)

            $this->data['title'] = 'Form Aktivasi SN';
            $this->data['currentModule'] = $this->currentModule;
            $this->data['config'] = $this->config;
            $this->data['sn'] = $snRecord;
            $this->data['itemWarrantyDays'] = $itemWarrantyDays;
            $this->data['msg'] = $this->session->getFlashdata('message');

            $this->view('sales/agent/sales-sn-activate', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Agent\Sales::activateForm error: ' . $e->getMessage());
            return redirect()->to($this->config->baseURL . 'agent/sales/sn')->with('message', [
                'status' => 'error',
                'message' => 'Gagal memuat form aktivasi.'
            ]);
        }
    }
    
    /**
     * Get serial number data for activation form
     * 
     * @param int $id Sales item SN ID
     * @return ResponseInterface
     */
    public function getSnData(int $id): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Request tidak valid.',
            ]);
        }

        try {
            $snRecord = $this->salesItemSnModel->find($id);
            if (!$snRecord) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Serial number tidak ditemukan.',
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'id'           => $snRecord['id'],
                    'sn'           => $snRecord['sn'],
                    'no_hp'        => $snRecord['no_hp'] ?? '',
                    'plat_code'    => $snRecord['plat_code'] ?? '',
                    'plat_number'  => $snRecord['plat_number'] ?? '',
                    'plat_last'    => $snRecord['plat_last'] ?? '',
                    'file'         => $snRecord['file'] ?? '',
                    'activated_at' => $snRecord['activated_at'] ?? '',
                    'expired_at'   => $snRecord['expired_at'] ?? '',
                ],
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Agent\Sales::getSnData error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get DataTables data for serial numbers
     * Filters by unused (activated_at IS NULL) or used (activated_at IS NOT NULL)
     * 
     * @return ResponseInterface
     */
    public function getSnDataDT(): ResponseInterface
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
            $filter = $this->request->getPost('filter') ?? 'unused'; // 'unused' or 'used'

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

            // Check if user is admin (has read_all or update_all permission)
            // Defensive check: ensure userPermission is an array
            $userPermission = is_array($this->userPermission) ? $this->userPermission : [];
            $isAdmin = key_exists('read_all', $userPermission) || key_exists('update_all', $userPermission);
            
            // Get agent IDs for current user (only for non-admin users)
            $agentIds = [];
            $userId = !empty($this->user) && is_array($this->user) ? ($this->user['id_user'] ?? null) : null;
            if (!$isAdmin && !empty($userId)) {
                $agentRows = $this->userRoleAgentModel
                    ->select('agent_id')
                    ->where('user_id', $userId)
                    ->findAll();

                $agentIds = array_values(array_unique(array_map(
                    static function ($row) {
                        if (is_object($row)) {
                            return (int)($row->agent_id ?? 0);
                        }
                        if (is_array($row)) {
                            return (int)($row['agent_id'] ?? 0);
                        }
                        return 0;
                    },
                    $agentRows
                )));
            }

            // For non-admin users without agent mapping, return empty data
            if (!$isAdmin && empty($agentIds)) {
                return $this->response->setJSON([
                    'draw'            => $draw,
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => [],
                ]);
            }

            $db = \Config\Database::connect();
            
            // Build base query
            $builder = $db->table('sales_item_sn')
                ->select('sales_item_sn.*,
                    sales_detail.item_id,
                    sales_detail.item as item_name,
                    sales_detail.qty,
                    sales_detail.sale_id,
                    item.name as item_name_fallback,
                    item.sku as item_sku,
                    item_sn.barcode,
                    sales.invoice_no,
                    sales.sale_channel,
                    sales.warehouse_id')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
                ->join('sales', 'sales.id = sales_detail.sale_id', 'inner')
                ->join('item', 'item.id = sales_detail.item_id', 'left')
                ->join('item_sn', 'item_sn.id = sales_item_sn.item_sn_id', 'left')
                ->where('sales.sale_channel', self::CHANNEL_ONLINE);
            
            // Apply agent filter only for non-admin users
            if (!$isAdmin && !empty($agentIds)) {
                $builder->whereIn('sales.warehouse_id', $agentIds);
            }

            // Apply filter for unreceived/unused/used
            if ($filter === 'unreceived') {
                $builder->where('sales_item_sn.is_receive', '0');
            } elseif ($filter === 'unused') {
                $builder->where('sales_item_sn.is_receive', '1')
                        ->where('sales_item_sn.activated_at IS NULL');
            } else {
                $builder->where('sales_item_sn.activated_at IS NOT NULL');
            }

            // Count total records
            $totalRecords = $db->table('sales_item_sn')
                ->select('sales_item_sn.id')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
                ->join('sales', 'sales.id = sales_detail.sale_id', 'inner')
                ->where('sales.sale_channel', self::CHANNEL_ONLINE);
            
            // Apply agent filter only for non-admin users
            if (!$isAdmin && !empty($agentIds)) {
                $totalRecords->whereIn('sales.warehouse_id', $agentIds);
            }
            
            if ($filter === 'unreceived') {
                $totalRecords->where('sales_item_sn.is_receive', '0');
            } elseif ($filter === 'unused') {
                $totalRecords->where('sales_item_sn.activated_at IS NULL');
            } else {
                $totalRecords->where('sales_item_sn.activated_at IS NOT NULL');
            }
            $totalRecords = $totalRecords->countAllResults();

            // Apply search filter
            $totalFiltered = $totalRecords;
            if (!empty($searchValue)) {
                $builder->groupStart()
                      ->like('sales_item_sn.sn', $searchValue)
                      ->orLike('sales_detail.item', $searchValue)
                      ->orLike('item.name', $searchValue)
                      ->orLike('item.sku', $searchValue)
                      ->orLike('item_sn.barcode', $searchValue)
                      ->groupEnd();

                // Clone query for count
                $countBuilder = clone $builder;
                $totalFiltered = $countBuilder->countAllResults();
            }

            // Get data
            $data = $builder->orderBy('sales_item_sn.created_at', 'DESC')
                          ->limit($length, $start)
                          ->get()
                          ->getResultArray();

            // Format for DataTables
            $result = [];
            $no = $start + 1;

            foreach ($data as $row) {
                // Use item name from sales_detail, fallback to item.name
                $itemName = !empty($row['item_name']) ? $row['item_name'] : ($row['item_name_fallback'] ?? '-');
                
                // Get item SKU
                $itemSku = !empty($row['item_sku']) ? $row['item_sku'] : '-';
                
                // Get barcode (from item_sn.barcode only, show '-' if empty - do NOT use serial number as fallback)
                $barcode = !empty($row['barcode']) ? $row['barcode'] : '-';
                
                // Action button
                $actionButton = '';
                if ($filter === 'unreceived') {
                    // Show receive button for unreceived SNs (agent only)
                    $saleId = !empty($row['sale_id']) ? $row['sale_id'] : 0;
                    $actionButton = '<button type="button" class="btn btn-sm btn-success receive-sn-btn" '
                        . 'data-sales-item-sn-id="' . $row['id'] . '" '
                        . 'data-sale-id="' . $saleId . '" '
                        . 'data-sn="' . esc($row['sn']) . '" '
                        . 'title="Terima Serial Number">'
                        . '<i class="fas fa-check me-1"></i>Terima</button>';
                } elseif ($filter === 'unused') {
                    $actionButton = '<a href="' . $this->config->baseURL . 'agent/sales/sn/activate/' . $row['id'] . '" class="btn btn-sm btn-primary" title="Aktifasi Serial Number"><i class="fas fa-check-circle"></i> Aktifasi</a>';
                } else {
                    $actionButton = '<span class="badge bg-success">Aktif</span>';
                }

                $result[] = [
                    'ignore_search_urut' => $no,
                    'invoice_no'        => esc($row['invoice_no']),
                    'sn'                => esc($row['sn']),
                    'item_name'         => esc($itemName),
                    'item_sku'          => esc($itemSku),
                    'ignore_search_action' => $actionButton,
                ];

                $no++;
            }

            return $this->response->setJSON([
                'draw'            => $draw,
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data'            => $result,
            ]);
        } catch (\Throwable $e) {
            // Enhanced error logging
            $errorDetails = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => !empty($this->user) && is_array($this->user) ? ($this->user['id_user'] ?? 'N/A') : 'N/A',
                'userPermission_type' => gettype($this->userPermission ?? null),
                'isLoggedIn' => $this->isLoggedIn ?? false,
            ];

            return $this->response->setJSON([
                'draw'            => $draw ?? 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get DataTables data for serial numbers for a specific sale
     * 
     * @param int $saleId Sale ID
     * @return ResponseInterface
     */
    public function getSnDataDTForSale(int $saleId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Request tidak valid.',
            ]);
        }

        if ($saleId <= 0) {
            return $this->response->setJSON([
                'draw'            => 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'ID penjualan tidak valid.',
            ]);
        }

        try {
            $draw   = (int) ($this->request->getPost('draw') ?? $this->request->getGet('draw') ?? 0);
            $start  = (int) ($this->request->getPost('start') ?? $this->request->getGet('start') ?? 0);
            $length = (int) ($this->request->getPost('length') ?? $this->request->getGet('length') ?? 10);
            $filter = $this->request->getPost('filter') ?? 'unused'; // 'unused' or 'used'

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

            $db = \Config\Database::connect();
            
            // Build base query - filter by sale_id
            $builder = $db->table('sales_item_sn')
                ->select('sales_item_sn.*,
                    sales_detail.item_id,
                    sales_detail.item as item_name,
                    sales_detail.qty,
                    item.name as item_name_fallback,
                    item.sku as item_sku,
                    item_sn.barcode,
                    sales.invoice_no,
                    sales.sale_channel,
                    sales.warehouse_id')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
                ->join('sales', 'sales.id = sales_detail.sale_id', 'inner')
                ->join('item', 'item.id = sales_detail.item_id', 'left')
                ->join('item_sn', 'item_sn.id = sales_item_sn.item_sn_id', 'left')
                ->where('sales.id', $saleId)
                ->where('sales.sale_channel', self::CHANNEL_ONLINE)
                ->where('sales_item_sn.is_receive', '1');

            // Apply filter for unused/used
            if ($filter === 'unused') {
                $builder->where('sales_item_sn.activated_at IS NULL');
            } else {
                $builder->where('sales_item_sn.activated_at IS NOT NULL');
            }

            // Count total records
            $totalRecords = $db->table('sales_item_sn')
                ->select('sales_item_sn.id')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
                ->join('sales', 'sales.id = sales_detail.sale_id', 'inner')
                ->where('sales.id', $saleId)
                ->where('sales.sale_channel', self::CHANNEL_ONLINE)
                ->where('sales_item_sn.is_receive', '1');
            
            if ($filter === 'unused') {
                $totalRecords->where('sales_item_sn.activated_at IS NULL');
            } else {
                $totalRecords->where('sales_item_sn.activated_at IS NOT NULL');
            }
            $totalRecords = $totalRecords->countAllResults();

            // Apply search filter
            $totalFiltered = $totalRecords;
            if (!empty($searchValue)) {
                $builder->groupStart()
                      ->like('sales_item_sn.sn', $searchValue)
                      ->orLike('sales_detail.item', $searchValue)
                      ->orLike('item.name', $searchValue)
                      ->orLike('item.sku', $searchValue)
                      ->orLike('item_sn.barcode', $searchValue)
                      ->groupEnd();

                // Clone query for count
                $countBuilder = clone $builder;
                $totalFiltered = $countBuilder->countAllResults();
            }

            // Get data
            $data = $builder->orderBy('sales_item_sn.created_at', 'DESC')
                          ->limit($length, $start)
                          ->get()
                          ->getResultArray();

            // Format for DataTables
            $result = [];
            $no = $start + 1;

            foreach ($data as $row) {
                // Use item name from sales_detail, fallback to item.name
                $itemName = !empty($row['item_name']) ? $row['item_name'] : ($row['item_name_fallback'] ?? '-');
                
                // Get item SKU
                $itemSku = !empty($row['item_sku']) ? $row['item_sku'] : '-';
                
                // Get barcode (from item_sn.barcode only, show '-' if empty)
                $barcode = !empty($row['barcode']) ? $row['barcode'] : '-';
                
                // Get SN from sales_item_sn.sn (included in sales_item_sn.*)
                $sn = !empty($row['sn']) ? $row['sn'] : '-';
                
                // Action button (only for unused)
                $actionButton = '';
                if ($filter === 'unused') {
                    $actionButton = '<a href="' . $this->config->baseURL . 'agent/sales/sn/activate/' . $row['id'] . '" class="btn btn-sm btn-primary" title="Aktifasi Serial Number"><i class="fas fa-check-circle"></i> Aktifasi</a>';
                } else {
                    $actionButton = '<span class="badge bg-success">Aktif</span>';
                }

                $result[] = [
                    'ignore_search_urut' => $no,
                    'sn'                => esc($sn),
                    'item_name'         => esc($itemName),
                    'item_sku'          => esc($itemSku),
                    'barcode'           => esc($barcode),
                    'ignore_search_action' => $actionButton,
                ];

                $no++;
            }

            return $this->response->setJSON([
                'draw'            => $draw,
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data'            => $result,
            ]);
        } catch (\Throwable $e) {
            // Enhanced error logging
            $errorDetails = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => !empty($this->user) && is_array($this->user) ? ($this->user['id_user'] ?? 'N/A') : 'N/A',
                'sale_id' => $saleId,
            ];
            
            log_message(
                'error',
                'Agent\Sales::getSnDataDTForSale error: ' . json_encode($errorDetails, JSON_PRETTY_PRINT)
            );
            
            return $this->response->setJSON([
                'draw'            => $draw ?? 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Get DataTables data for agent sales list (online sales only)
     * Filters by sale_channel = '2' (CHANNEL_ONLINE)
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

            // Get filter parameters
            $filterAgentId = $this->request->getPost('filter_agent_id') ?? $this->request->getGet('filter_agent_id') ?? '';
            $filterPlatform = $this->request->getPost('filter_platform') ?? $this->request->getGet('filter_platform') ?? '';

            $db = \Config\Database::connect();
            
            // Count total records with channel filter
            $totalRecordsQuery = $db->table('sales')
                ->where('sale_channel', self::CHANNEL_ONLINE);
            
            // Apply filters to count query
            if (!empty($filterAgentId) && $filterAgentId > 0) {
                $totalRecordsQuery->where('sales.warehouse_id', (int)$filterAgentId);
            }
            if (!empty($filterPlatform)) {
                $totalRecordsQuery->where('sales.payment_status', $filterPlatform);
            }
            
            $totalRecords = $totalRecordsQuery->countAllResults();

            // Main query builder with joins and channel filter
            $query = $this->buildSalesQuery();

            // Apply filters
            if (!empty($filterAgentId) && $filterAgentId > 0) {
                $query->where('sales.warehouse_id', (int)$filterAgentId);
            }
            if (!empty($filterPlatform)) {
                $query->where('sales.payment_status', $filterPlatform);
            }

            // Apply filtering if search term present
            $totalFiltered = $totalRecords;

            if (!empty($searchValue)) {
                $query->groupStart()
                      ->like('sales.invoice_no', $searchValue)
                      ->orLike('customer.name', $searchValue)
                      ->orLike('user.nama', $searchValue)
                      ->orLike('agent.name', $searchValue)
                      ->groupEnd();

                // Clone query for the count (mimics actual filter)
                $countQuery = $this->buildSalesQuery();
                
                // Apply filters to count query
                if (!empty($filterAgentId) && $filterAgentId > 0) {
                    $countQuery->where('sales.warehouse_id', (int)$filterAgentId);
                }
                if (!empty($filterPlatform)) {
                    $countQuery->where('sales.payment_status', $filterPlatform);
                }
                
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

            // Format for DataTables
            $result = $this->formatDataTablesData($data, $start);

            return $this->response->setJSON([
                'draw'            => $draw,
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data'            => $result,
            ]);
        } catch (\Throwable $e) {
            log_message(
                'error',
                'Agent\Sales::getDataDT error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString()
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
     * Build base sales query with joins and channel filter
     * Filters by sale_channel = '2' (CHANNEL_ONLINE)
     * 
     * @return \CodeIgniter\Database\BaseBuilder
     */
    protected function buildSalesQuery()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('sales');
        
        // customer_name now refers to agent.name only, customer table not needed
        return $builder->select('sales.*, 
            agent.name as customer_name,
            user.nama as user_name,
            agent.name as agent_name,
            COALESCE(sales.balance_due, sales.grand_total - COALESCE(sales.total_payment, 0)) as balance_due')
            ->join('user', 'user.id_user = sales.user_id', 'left')
            ->join('agent', 'agent.id = sales.warehouse_id', 'left')
            ->where('sales.sale_channel', self::CHANNEL_ONLINE);
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

        foreach ($data as $row) {
            $paymentStatus = $row['payment_status'] ?? '0';
            $saleId = $row['id'] ?? null;
            $statusDisplay = get_payment_status_badge($paymentStatus, $saleId);

            $actionButtons = '<div class="btn-group" role="group">';
            $actionButtons .= '<a href="' . $this->config->baseURL . 'agent/sales/' . $row['id'] . '" ';
            $actionButtons .= 'class="btn btn-sm btn-info" title="Detail">';
            $actionButtons .= '<i class="fas fa-eye"></i></a>';
            
            // Show confirm button only if:
            // 1. Payment status is '2' (Paid)
            // 2. Sales status is NOT '1' (not completed)
            // 3. User has 'update_all' permission
            $salesStatus = $row['status'] ?? '0';
            $hasUpdateAllPermission = $this->hasPermission('update_all');
            
            // Only allow if payment status is 2 or 3, not completed, is_receive is '0', and has update permission
            if (
                (in_array($paymentStatus, ['2', '3']))
                && $salesStatus !== '1'
                && (isset($row['is_receive']) && $row['is_receive'] === '0')
                && $hasUpdateAllPermission
            ) {
                $actionButtons .= '<a href="' . $this->config->baseURL . 'agent/sales/confirm/' . $row['id'] . '" ';
                $actionButtons .= 'class="btn btn-sm btn-success" title="Confirm">';
                $actionButtons .= '<i class="fas fa-check"></i></a>';
            }

            // Add "Bayar" button only for role id 4
            if (isset($this->data['role']['id_role']) && $this->data['role']['id_role'] == 4) {
                $actionButtons .= '<a href="' . $this->config->baseURL . 'agent/paylater/pay/' . $row['id'] . '" ';
                $actionButtons .= 'class="btn btn-sm btn-success btn-bayar" title="Bayar">';
                $actionButtons .= 'Bayar</a>';
            }

            $actionButtons .= '</div>';

            // Calculate balance_due
            // $balanceDue = 0;
            // if (isset($row['total_payment'])) {
            //     $balanceDue = (float)$row['total_payment'];
            // } else {
                $grandTotal = (float)($row['grand_total'] ?? 0);
                $totalPayment = (float)($row['total_payment'] ?? 0);
                $balanceDue = $grandTotal - $totalPayment;
            // }

            // Ensure customer_name is correctly retrieved (not agent.name)
            // Explicitly check for customer_name field and fallback to '-' if null/empty
            $customerName = '';
            if (isset($row['customer_name']) && !empty($row['customer_name'])) {
                $customerName = $row['customer_name'];
            } else {
                $customerName = '-';
            }
            
            // Ensure agent_name is correctly retrieved
            $agentName = isset($row['agent_name']) && !empty($row['agent_name']) ? $row['agent_name'] : '-';

            $result[] = [
                'ignore_search_urut'    => $no,
                'id'                    => $row['id'] ?? 0,
                'invoice_no'            => esc($row['invoice_no'] ?? ''),
                'customer_name'         => esc($customerName),
                'agent_name'            => esc($agentName),
                'grand_total'           => format_angka((float) ($row['grand_total'] ?? 0), 0),
                'balance_due'           => format_angka($balanceDue, 0),
                'payment_status'        => $statusDisplay,
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
     * Activate serial number
     * Handles form submission with file upload and updates sales_item_sn
     * 
     * @param int $id Sales item SN ID
     * @return ResponseInterface
     */
    public function activateSN(int $id)
    {
        try {
            // Validate ID
            if ($id <= 0) {
                return redirect()->to($this->config->baseURL . 'agent/sales/sn')->with('message', [
                    'status'  => 'error',
                    'message' => 'ID tidak valid.',
                ]);
            }

            // Get existing record
            $snRecord = $this->salesItemSnModel->find($id);
            if (!$snRecord) {
                return redirect()->to($this->config->baseURL . 'agent/sales/sn')->with('message', [
                    'status'  => 'error',
                    'message' => 'Serial number tidak ditemukan.',
                ]);
            }

            // Get form data
            $noHp = trim($this->request->getPost('no_hp') ?? '');
            $platCode = $this->request->getPost('plat_code') ?? '';
            $platNumber = $this->request->getPost('plat_number') ?? '';
            $platLast = $this->request->getPost('plat_last') ?? '';
            $activatedAt = $this->request->getPost('activated_at') ?? '';
            $expiredAt = $this->request->getPost('expired_at') ?? '';

            // Validate required fields
            if (empty($activatedAt)) {
                return redirect()->back()->withInput()->with('message', [
                    'status'  => 'error',
                    'message' => 'Tanggal Aktif harus diisi.',
                ]);
            }

            // Handle file upload
            $filePath = '';
            $file = $this->request->getFile('file');
            
            if ($file && $file->isValid() && !$file->hasMoved()) {
                // Validate file size (max 5MB)
                if ($file->getSize() > 5242880) { // 5MB
                    return redirect()->back()->withInput()->with('message', [
                        'status'  => 'error',
                        'message' => 'Ukuran file maksimal 5MB.',
                    ]);
                }
                
                // Validate file type (images only)
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!in_array($file->getMimeType(), $allowedTypes)) {
                    return redirect()->back()->withInput()->with('message', [
                        'status'  => 'error',
                        'message' => 'Format file harus JPG, PNG, atau GIF.',
                    ]);
                }
                
                // Create upload directory if it doesn't exist
                $uploadPath = ROOTPATH . 'public/uploads/sn/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Generate unique filename
                $newName = $file->getRandomName();
                if ($file->move($uploadPath, $newName)) {
                    $filePath = 'sn/' . $newName;
                    
                    // Delete old file if exists
                    if (!empty($snRecord['file']) && file_exists(ROOTPATH . 'public/uploads/' . $snRecord['file'])) {
                        @unlink(ROOTPATH . 'public/uploads/' . $snRecord['file']);
                    }
                } else {
                    return redirect()->back()->withInput()->with('message', [
                        'status'  => 'error',
                        'message' => 'Gagal mengupload file.',
                    ]);
                }
            } elseif (!empty($snRecord['file'])) {
                // Keep existing file if no new file uploaded
                $filePath = $snRecord['file'];
            }

            // Get item warranty if expired_at is empty
            $itemWarranty = 0;
            if (empty($expiredAt)) {
                $db = \Config\Database::connect();
                $itemData = $db->table('sales_item_sn')
                    ->select('item.warranty')
                    ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'left')
                    ->join('item', 'item.id = sales_detail.item_id', 'left')
                    ->where('sales_item_sn.id', $id)
                    ->get()
                    ->getRowArray();
                
                if ($itemData && isset($itemData['warranty'])) {
                    $itemWarranty = (int)$itemData['warranty'];
                }
            }

            // Prepare update data
            $updateData = [
                'no_hp' => $noHp,
                'plat_code' => $platCode,
                'plat_number' => $platNumber,
                'plat_last' => $platLast,
                'activated_at' => date('Y-m-d H:i:s', strtotime($activatedAt)),
            ];

            // Set expired_at: use provided value, or calculate from warranty if empty
            if (!empty($expiredAt)) {
                $updateData['expired_at'] = date('Y-m-d H:i:s', strtotime($expiredAt));
            } elseif ($itemWarranty > 0) {
                // Calculate expired_at: activated_at + (warranty months * 30 days)
                $activatedDateTime = new \DateTime($activatedAt);
                $warrantyDays = $itemWarranty * 30; // Convert months to days
                $activatedDateTime->modify('+' . $warrantyDays . ' days');
                $updateData['expired_at'] = $activatedDateTime->format('Y-m-d H:i:s');
            }

            if (!empty($filePath)) {
                $updateData['file'] = $filePath;
            }

            // Update record
            $this->salesItemSnModel->skipValidation(true);
            $result = $this->salesItemSnModel->update($id, $updateData);
            $this->salesItemSnModel->skipValidation(false);

            if (!$result) {
                $errors = $this->salesItemSnModel->errors();
                $errorMsg = 'Gagal mengupdate serial number: ';
                if ($errors && is_array($errors)) {
                    $errorMsg .= implode(', ', $errors);
                }
                return redirect()->back()->withInput()->with('message', [
                    'status'  => 'error',
                    'message' => $errorMsg,
                ]);
            }

            return redirect()->to($this->config->baseURL . 'agent/sales/sn')->with('message', [
                'status'  => 'success',
                'message' => 'Serial number berhasil diaktifasi.',
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Agent\Sales::activateSN error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('message', [
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Receive all unreceived serial numbers for current agent
     * This method receives all SNs where is_receive='0' for the current agent/user
     * 
     * @return ResponseInterface
     */
    public function receiveAllUnreceivedSN(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ])->setStatusCode(400);
        }

        try {
            // Check if user is admin (has read_all or update_all permission)
            $userPermission = is_array($this->userPermission) ? $this->userPermission : [];
            $isAdmin = key_exists('read_all', $userPermission) || key_exists('update_all', $userPermission);
            
            // Only agents can receive SNs, not admins
            if ($isAdmin) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Hanya agen yang dapat menerima serial number.'
                ])->setStatusCode(403);
            }

            // Get agent IDs for current user
            $agentIds = [];
            $userId = !empty($this->user) && is_array($this->user) ? ($this->user['id_user'] ?? null) : null;
            if (empty($userId)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User tidak valid.'
                ])->setStatusCode(403);
            }

            $agentRows = $this->userRoleAgentModel
                ->select('agent_id')
                ->where('user_id', $userId)
                ->findAll();

            $agentIds = array_values(array_unique(array_map(
                static function ($row) {
                    if (is_object($row)) {
                        return (int)($row->agent_id ?? 0);
                    }
                    if (is_array($row)) {
                        return (int)($row['agent_id'] ?? 0);
                    }
                    return 0;
                },
                $agentRows
            )));

            if (empty($agentIds)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses sebagai agen.'
                ])->setStatusCode(403);
            }

            // Get all unreceived SNs for this agent across all sales
            $unreceivedSns = $this->salesItemSnModel
                ->select('sales_item_sn.id, sales_item_sn.item_sn_id, sales_detail.sale_id, sales.warehouse_id as agent_id')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
                ->join('sales', 'sales.id = sales_detail.sale_id', 'inner')
                ->where('sales.sale_channel', self::CHANNEL_ONLINE)
                ->where('sales_item_sn.is_receive', '0')
                ->whereIn('sales.warehouse_id', $agentIds)
                ->findAll();

            if (empty($unreceivedSns)) {
                return $this->response->setJSON([
                    'status' => 'info',
                    'message' => 'Tidak ada serial number yang perlu diterima.',
                    'count' => 0
                ]);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            try {
                $updatedCount = 0;
                $itemSnUpdates = []; // Group by agent_id to update item_sn efficiently

                foreach ($unreceivedSns as $salesItemSn) {
                    $salesItemSnId = (int)($salesItemSn['id'] ?? 0);
                    $itemSnId = (int)($salesItemSn['item_sn_id'] ?? 0);
                    $agentId = (int)($salesItemSn['agent_id'] ?? 0);

                    if ($salesItemSnId <= 0 || $agentId <= 0) {
                        continue;
                    }

                    // Update sales_item_sn.is_receive = '1' and set receive_at timestamp
                    $this->salesItemSnModel->update($salesItemSnId, [
                        'is_receive' => '1',
                        'receive_at' => date('Y-m-d H:i:s')
                    ]);

                    // Collect item_sn_ids grouped by agent_id
                    if ($itemSnId > 0) {
                        if (!isset($itemSnUpdates[$agentId])) {
                            $itemSnUpdates[$agentId] = [];
                        }
                        if (!in_array($itemSnId, $itemSnUpdates[$agentId])) {
                            $itemSnUpdates[$agentId][] = $itemSnId;
                        }
                    }

                    $updatedCount++;
                }

                // Update all item_sn.agent_id in batches per agent
                foreach ($itemSnUpdates as $agentId => $itemSnIds) {
                    if (!empty($itemSnIds)) {
                        $this->itemSnModel->whereIn('id', $itemSnIds)
                            ->set('agent_id', $agentId)
                            ->update();
                    }
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Transaksi gagal.');
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => "Semua serial number berhasil diterima. Total: {$updatedCount} SN",
                    'count' => $updatedCount
                ]);

            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }

        } catch (\Exception $e) {
            log_message('error', 'Agent\Sales::receiveAllUnreceivedSN error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menerima serial number: ' . $e->getMessage()
            ]);
        }
    }
}

