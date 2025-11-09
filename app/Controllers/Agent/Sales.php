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
            
            // Handle gateway response if provided
            $gatewayResponse = null;
            $platformId = !empty($postData['platform_id']) ? (int)$postData['platform_id'] : null;
            
            if (!empty($postData['gateway_response'])) {
                $gatewayResponseJson = urldecode($postData['gateway_response']);
                $gatewayResponse = json_decode($gatewayResponseJson, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    log_message('error', 'Agent\Sales::store - Invalid gateway_response JSON: ' . json_last_error_msg());
                    $gatewayResponse = null;
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
                    
                    $paymentData = [
                        'sale_id' => $saleId,
                        'platform_id' => $platformId,
                        'method' => $paymentMethod,
                        'amount' => $grandTotal,
                        'note' => json_encode($gatewayResponse)
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
}

