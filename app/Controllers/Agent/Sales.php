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
use App\Models\ItemModel;
use App\Models\ItemSnModel;
use App\Models\AgentModel;
use App\Models\CustomerModel;
use App\Models\UserRoleAgentModel;
use App\Models\PlatformModel;
use App\Models\SalesPaymentLogModel;
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
    protected $itemModel;
    protected $itemSnModel;
    protected $agentModel;
    protected $customerModel;
    protected $userRoleAgentModel;
    protected $platformModel;
    protected $salesPaymentLogModel;
    
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
        $this->itemModel = new ItemModel();
        $this->itemSnModel = new ItemSnModel();
        $this->agentModel = new AgentModel();
        $this->customerModel = new CustomerModel();
        $this->userRoleAgentModel = new UserRoleAgentModel();
        $this->platformModel = new PlatformModel();
        $this->salesPaymentLogModel = new SalesPaymentLogModel();
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
        $this->data['invoice_no'] = $this->model->generateInvoiceNo();
        $this->data['message'] = $this->session->getFlashdata('message');
        
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
            
            // Validate required fields
            if (empty($postData['invoice_no'])) {
                $message = 'Nomor invoice harus diisi.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->back()->withInput()->with('message', [
                    'status' => 'error',
                    'message' => $message
                ]);
            }
            
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
                    $invoiceNo = trim($postData['invoice_no']);
                    $grandTotal = (float)($postData['grand_total'] ?? 0);
                    
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
                    
                    // Prepare API payload matching Midtrans custom middleware format
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
                    
                    // Log the payload being sent (for debugging)
                    log_message('error', 'Agent\Sales::store - Gateway API Payload: ' . json_encode($apiData, JSON_PRETTY_PRINT));
                    
                    // Call payment gateway API
                    $gatewayResponse = $this->callPaymentGateway($apiData);
                    
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
                // Pay Later: set as unpaid (will be paid later)
                $paymentStatus = '0'; // unpaid
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
            $saleData = [
                'invoice_no' 		=> trim($postData['invoice_no']),
                'user_id' 			=> $userId,
                'customer_id' 		=> $customerId,
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

            // Save to sales_detail table only
            $itemModel = new \App\Models\ItemModel();
            foreach ($cart as $item) {
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

            // Complete transaction
            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi gagal.');
            }
                
                // Auto-fetch latest payment status from gateway if payment was created with gateway
                if ($saleId && $platformId && $gatewayResponse) {
                    try {
                        $invoiceNo = trim($postData['invoice_no']);
                        $latestGatewayResponse = $this->getPaymentStatusFromGateway($invoiceNo);
                        
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
                
                log_message('info', "Agent Sales transaction created: ID {$saleId}, Invoice: {$postData['invoice_no']}");
                
                $message = 'Penjualan berhasil disimpan. Invoice: ' . $postData['invoice_no'];
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
                
                return redirect()->to('agent/sales/cart')->with('message', [
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
            $encryptedApiKey = null;
            try {
                $encryptedApiKey = $this->encryptApiKey(self::GATEWAY_API_KEY, self::GATEWAY_PUBLIC_KEY);
            } catch (\Exception $e) {
                log_message('error', 'Agent\Sales::getPaymentStatusFromGateway - API key encryption failed: ' . $e->getMessage());
                return null;
            }
            
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
                // Return full gateway response (includes paymentCode, expiredAt, etc.)
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
        $this->data['title'] = 'Data Penjualan Agent (Online)';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        // Pass permission data to view
        $this->data['canCreate'] = $this->hasPermissionPrefix('create');
        
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

            $this->data['title'] = 'Detail Penjualan';
            $this->data['currentModule'] = $this->currentModule;
            $this->data['config'] = $this->config;
            $this->data['sale'] = $sale;
            $this->data['items'] = $items;
            $this->data['payment'] = $paymentInfo;
            $this->data['gatewayResponse'] = $gatewayResponse;

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
     * Display serial numbers for agent sales
     * Shows unused and used serial numbers in tabbed interface with DataTables
     * 
     * @return void
     */
    public function sn(): void
    {
        // Get agent IDs for current user
        // Check if user is admin (has read_all or update_all permission)
        $isAdmin = key_exists('read_all', $this->userPermission) || key_exists('update_all', $this->userPermission);
        
        $agentIds = [];
        if (!$isAdmin && !empty($this->user['id_user'])) {
            $agentRows = $this->userRoleAgentModel
                ->select('agent_id')
                ->where('user_id', $this->user['id_user'])
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

        $this->data['title'] = 'Data Serial Number';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['isAdmin'] = $isAdmin;
        $this->data['agentIds'] = $agentIds;

        $this->view('sales/agent/sales-sn', $this->data);
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
            // Get SN record
            $snRecord = $this->salesItemSnModel->find($id);
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

            $this->data['title'] = 'Form Aktivasi SN';
            $this->data['currentModule'] = $this->currentModule;
            $this->data['config'] = $this->config;
            $this->data['sn'] = $snRecord;
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
                'data'   => [
                    'id' => $snRecord['id'],
                    'sn' => $snRecord['sn'],
                    'no_hp' => $snRecord['no_hp'] ?? '',
                    'plat_code' => $snRecord['plat_code'] ?? '',
                    'plat_number' => $snRecord['plat_number'] ?? '',
                    'plat_last' => $snRecord['plat_last'] ?? '',
                    'file' => $snRecord['file'] ?? '',
                    'activated_at' => $snRecord['activated_at'] ?? '',
                    'expired_at' => $snRecord['expired_at'] ?? '',
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
            $isAdmin = key_exists('read_all', $this->userPermission) || key_exists('update_all', $this->userPermission);
            
            // Get agent IDs for current user (only for non-admin users)
            $agentIds = [];
            if (!$isAdmin && !empty($this->user['id_user'])) {
                $agentRows = $this->userRoleAgentModel
                    ->select('agent_id')
                    ->where('user_id', $this->user['id_user'])
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
                    item.name as item_name_fallback,
                    item.sku as item_sku,
                    sales.sale_channel,
                    sales.warehouse_id')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
                ->join('sales', 'sales.id = sales_detail.sale_id', 'inner')
                ->join('item', 'item.id = sales_detail.item_id', 'left')
                ->where('sales.sale_channel', self::CHANNEL_ONLINE);
            
            // Apply agent filter only for non-admin users
            if (!$isAdmin && !empty($agentIds)) {
                $builder->whereIn('sales.warehouse_id', $agentIds);
            }

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
                ->where('sales.sale_channel', self::CHANNEL_ONLINE);
            
            // Apply agent filter only for non-admin users
            if (!$isAdmin && !empty($agentIds)) {
                $totalRecords->whereIn('sales.warehouse_id', $agentIds);
            }
            
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
                
                // Action button (only for unused)
                $actionButton = '';
                if ($filter === 'unused') {
                    $actionButton = '<a href="' . $this->config->baseURL . 'agent/sales/sn/activate/' . $row['id'] . '" class="btn btn-sm btn-primary" title="Aktifasi Serial Number"><i class="fas fa-check-circle"></i> Aktifasi</a>';
                } else {
                    $actionButton = '<span class="badge bg-success">Aktif</span>';
                }

                $result[] = [
                    'ignore_search_urut' => $no,
                    'sn'                => esc($row['sn']),
                    'item_name'         => esc($itemName),
                    'qty'               => esc($row['qty'] ?? 1),
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
            log_message(
                'error',
                'Agent\Sales::getSnDataDT error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString()
            );

            return $this->response->setJSON([
                'draw'            => 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'Terjadi kesalahan saat memuat data.',
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

            $db = \Config\Database::connect();
            
            // Count total records with channel filter
            $totalRecords = $db->table('sales')
                ->where('sale_channel', self::CHANNEL_ONLINE)
                ->countAllResults();

            // Main query builder with joins and channel filter
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

                // Clone query for the count (mimics actual filter)
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
        
        return $builder->select('sales.*, 
            customer.name as customer_name,
            user.nama as user_name,
            agent.name as agent_name')
            ->join('customer', 'customer.id = sales.customer_id', 'left')
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

        // Payment status badges
        $paymentStatusBadge = [
            '0' => '<span class="badge bg-warning">Unpaid</span>',
            '1' => '<span class="badge bg-info">Partial</span>',
            '2' => '<span class="badge bg-success">Paid</span>'
        ];

        foreach ($data as $row) {
            $paymentStatus = $row['payment_status'] ?? '0';
            $statusDisplay = $paymentStatusBadge[$paymentStatus] ?? '<span class="badge bg-secondary">Unknown</span>';

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
            
            if ($paymentStatus === '2' && $salesStatus !== '1' && $hasUpdateAllPermission) {
                $actionButtons .= '<a href="' . $this->config->baseURL . 'agent/sales/confirm/' . $row['id'] . '" ';
                $actionButtons .= 'class="btn btn-sm btn-success" title="Confirm">';
                $actionButtons .= '<i class="fas fa-check"></i></a>';
            }

            $actionButtons .= '</div>';

            $result[] = [
                'ignore_search_urut'    => $no,
                'id'                    => $row['id'] ?? 0,
                'invoice_no'            => esc($row['invoice_no'] ?? ''),
                'customer_name'         => esc($row['customer_name'] ?? '-'),
                'agent_name'            => esc($row['agent_name'] ?? '-'),
                'grand_total'           => format_angka((float) ($row['grand_total'] ?? 0), 2),
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

            // Prepare update data
            $updateData = [
                'no_hp' => $noHp,
                'plat_code' => $platCode,
                'plat_number' => $platNumber,
                'plat_last' => $platLast,
                'activated_at' => date('Y-m-d H:i:s', strtotime($activatedAt)),
            ];

            if (!empty($expiredAt)) {
                $updateData['expired_at'] = date('Y-m-d H:i:s', strtotime($expiredAt));
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
}

