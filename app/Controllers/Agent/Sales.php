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
use App\Models\ItemModel;
use App\Models\ItemSnModel;
use App\Models\AgentModel;
use App\Models\CustomerModel;
use App\Models\UserRoleAgentModel;
use App\Models\PlatformModel;
use CodeIgniter\HTTP\ResponseInterface;

class Sales extends BaseController
{
    protected $model;
    protected $salesItemsModel;
    protected $salesItemSnModel;
    protected $salesPaymentsModel;
    protected $itemModel;
    protected $itemSnModel;
    protected $agentModel;
    protected $customerModel;
    protected $userRoleAgentModel;
    protected $platformModel;
    
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
        $this->itemModel = new ItemModel();
        $this->itemSnModel = new ItemSnModel();
        $this->agentModel = new AgentModel();
        $this->customerModel = new CustomerModel();
        $this->userRoleAgentModel = new UserRoleAgentModel();
        $this->platformModel = new PlatformModel();
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
        
        // Get agents list for dropdown (if needed)
        $agents = $this->agentModel->where('is_active', '1')->orderBy('name', 'ASC')->findAll();
        
        // Get platforms with status_agent='1' (include status_pos, gw_status, gw_code for API check)
        $platforms = $this->platformModel
            ->select('platform.*, platform.status_pos, platform.gw_status, platform.gw_code')
            ->where('status', '1')
            ->where('status_agent', '1')
            ->orderBy('platform', 'ASC')
            ->findAll();
        
        // Prepare view data
        $this->data['title'] = 'Keranjang & Checkout';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['cart'] = $cart;
        $this->data['agentId'] = $agentId;
        $this->data['agents'] = $agents;
        $this->data['platforms'] = $platforms;
        $this->data['invoice_no'] = $this->model->generateInvoiceNo();
        $this->data['message'] = $this->session->getFlashdata('message');
        
        // Load helper for currency formatting
        helper('angka');
        
        // Render view
        $this->view('sales/agent/sales-form', $this->data);
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
            
            // Handle customer creation/selection
            $customerId = null;
            if (!empty($postData['customer_name'])) {
                // Create or find customer
                $customerName = trim($postData['customer_name']);
                $plateCode = trim($postData['plate_code'] ?? '');
                $plateNumber = trim($postData['plate_number'] ?? '');
                $plateSuffix = trim($postData['plate_suffix'] ?? '');
                
                // Check if customer exists
                $customer = null;
                if (!empty($plateCode) && !empty($plateNumber)) {
                    $customer = $this->customerModel
                        ->where('plat_code', $plateCode)
                        ->where('plat_number', $plateNumber)
                        ->first();
                }
                
                if (!$customer && !empty($customerName)) {
                    // Create new customer
                    $customerData = [
                        'name' => $customerName,
                        'plat_code' => $plateCode,
                        'plat_number' => $plateNumber,
                        'plat_last' => $plateSuffix,
                        'phone' => $postData['customer_phone'] ?? '',
                        'email' => $postData['customer_email'] ?? ''
                    ];
                    
                    $this->customerModel->insert($customerData);
                    $customerId = $this->customerModel->getInsertID();
                } else {
                    $customerId = $customer ? $customer->id : null;
                }
            }
            
            // Calculate totals
            $totalQty = 0;
            $subtotal = 0;
            foreach ($cart as $item) {
                $totalQty += $item['qty'];
                $subtotal += $item['subtotal'];
            }
            
            $discount = (float)($postData['discount'] ?? 0);
            $tax = (float)($postData['tax'] ?? 0);
            $grandTotal = $subtotal - $discount + $tax;
            
            // Handle payment gateway API call if platform is selected
            $gatewayResponse = null;
            $platformId = !empty($postData['platform_id']) ? (int)$postData['platform_id'] : null;
            
            if ($platformId) {
                // Get platform details
                $platform = $this->platformModel->find($platformId);
                
                // Only send to gateway if platform.gw_status = '1'
                // Platforms like "Tunai" (Cash) with gw_status = '0' should NOT go through gateway
                $gwStatus = $platform['gw_status'] ?? '0';
                // Handle both string and integer values
                $gwStatus = (string)$gwStatus;
                if ($platform && $gwStatus === '1') {
                    
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
                }
            }
            
            // Determine payment status based on gateway response
            $paymentStatus = '2'; // Default: paid
            if ($gatewayResponse && isset($gatewayResponse['status'])) {
                $gatewayStatus = strtoupper($gatewayResponse['status']);
                if ($gatewayStatus === 'PAID') {
                    $paymentStatus = '2'; // paid
                } elseif (in_array($gatewayStatus, ['PENDING', 'FAILED', 'CANCELED', 'EXPIRED'])) {
                    $paymentStatus = '0'; // unpaid
                }
            }
            
            // Start database transaction
            $db = \Config\Database::connect();
            $db->transStart();
            
            try {
            // Create sale record
            $saleData = [
                'invoice_no' => $postData['invoice_no'],
                'customer_id' => $customerId,
                'warehouse_id' => (int)$postData['agent_id'], // warehouse_id stores agent_id
                'total_amount' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'grand_total' => $grandTotal,
                'sale_channel' => self::CHANNEL_OFFLINE,
                'payment_status' => $paymentStatus,
                'user_id' => $userId
            ];
            
            // Add settlement_time if gateway response has it and status is PAID
            if ($gatewayResponse && isset($gatewayResponse['settlementTime']) && 
                isset($gatewayResponse['status']) && strtoupper($gatewayResponse['status']) === 'PAID') {
                try {
                    $settlementDateTime = new \DateTime($gatewayResponse['settlementTime']);
                    $saleData['settlement_time'] = $settlementDateTime->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    log_message('error', 'Agent\Sales::store - Invalid settlementTime format: ' . ($gatewayResponse['settlementTime'] ?? ''));
                }
            }
                
                $this->model->skipValidation(true);
                $saleId = $this->model->insert($saleData);
                $this->model->skipValidation(false);
                
                if (!$saleId) {
                    throw new \Exception('Gagal menyimpan data penjualan.');
                }
                
                // Create sale items
                foreach ($cart as $item) {
                    $itemData = [
                        'sale_id' => $saleId,
                        'item_id' => $item['item_id'],
                        'variant_id' => null, // No variant selection for agent sales
                        'quantity' => $item['qty'],
                        'price' => $item['price'],
                        'discount' => 0,
                        'subtotal' => $item['subtotal'],
                        'note' => ''
                    ];
                    
                    $this->salesItemsModel->skipValidation(true);
                    $salesItemId = $this->salesItemsModel->insert($itemData);
                    $this->salesItemsModel->skipValidation(false);
                    
                    // Handle serial numbers if needed
                    // (You can add SN handling here if required)
                }
                
                // Save payment information if platform is selected (within transaction)
                if ($platformId && $gatewayResponse) {
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
                        'amount' => $grandTotal,
                        'note' => '', // Manual notes if needed
                        'response' => $gatewayResponseJson // Full gateway response JSON
                    ];
                    
                    $this->salesPaymentsModel->skipValidation(true);
                    $this->salesPaymentsModel->insert($paymentData);
                    $this->salesPaymentsModel->skipValidation(false);
                } elseif ($platformId) {
                    // Platform selected but no gateway response (cash payment via platform)
                    $paymentData = [
                        'sale_id' => $saleId,
                        'platform_id' => $platformId,
                        'method' => 'cash',
                        'amount' => $grandTotal,
                        'note' => ''
                    ];
                    
                    $this->salesPaymentsModel->skipValidation(true);
                    $this->salesPaymentsModel->insert($paymentData);
                    $this->salesPaymentsModel->skipValidation(false);
                }
                
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
                if ($isAjax) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => $message,
                        'data' => ['id' => $saleId]
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
}

