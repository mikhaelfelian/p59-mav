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
use App\Models\SalesFeeModel;
use App\Models\FeeTypeModel;
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
    protected $salesFeeModel;
    protected $feeTypeModel;
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
     * Payment Gateway Configuration
     */
    protected const GATEWAY_API_KEY = 'P@ssw0rdMav123';
    protected const GATEWAY_PUBLIC_KEY = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAp4d7Fd9aXgP5zK0YbM3Z2skasRz9i41ZwPjH3KQZlfZ5LcvXbApX8yRHLJc/biMFcXosIuHf6Z0hG9vChgCyOf2V6tYb05Q+bmAlwJ1pDkg4zKHzXv/uhRRjZQxX2ld7rYgXW9BvvkKIRu5ATkKeYzX8o9u8ZBqYkDg0ACZ1X+YP0X1ZlNQy3YBqx4fXKPRdZ6gPSPzN4r4s7vYSmZ7fWQIDAQAB';

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
        $this->salesFeeModel = new SalesFeeModel();
        $this->feeTypeModel = new FeeTypeModel();
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
        
        // Pass permission data to view
        $this->data['canCreate'] = $this->hasPermissionPrefix('create');
        
        $this->view('sales/sales-result', $this->data);
    }

    /**
     * Search customers by name (for autocomplete)
     * 
     * @return ResponseInterface
     */
    public function searchCustomers(): ResponseInterface
    {
        try {
            $searchTerm = $this->request->getGet('term') ?? '';
            
            if (empty($searchTerm) || strlen($searchTerm) < 2) {
                return $this->response->setJSON(['status' => 'success', 'data' => []]);
            }
            
            // Escape search term to prevent SQL injection
            $searchTerm = trim($searchTerm);
            
            // Explicitly select fields to avoid issues
            $customers = $this->customerModel
                ->select('id, name, phone, email, plat_code, plat_number, plat_last, plate_code, plate_number, plate_suffix')
                ->groupStart()
                    ->like('name', $searchTerm)
                    ->orLike('phone', $searchTerm)
                    ->orLike('plat_code', $searchTerm)
                    ->orLike('plat_number', $searchTerm)
                    ->orLike('plat_last', $searchTerm)
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
                    'id' => $customer['id'] ?? 0,
                    'label' => $customer['name'] ?? '',
                    'value' => $customer['name'] ?? '',
                    'phone' => $customer['phone'] ?? '',
                    'email' => $customer['email'] ?? '',
                    'plate_code' => $plateCode,
                    'plate_number' => $plateNumber,
                    'plate_suffix' => $plateSuffix
                ];
            }
            
            return $this->response->setJSON(['status' => 'success', 'data' => $results]);
            
        } catch (\Exception $e) {
            log_message('error', 'Sales::searchCustomers error: ' . $e->getMessage());
            log_message('error', 'Sales::searchCustomers trace: ' . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mencari customer: ' . $e->getMessage(),
                'data' => []
            ])->setStatusCode(500);
        }
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

            // Get active platforms for dropdown (include status_pos and gw_status for API check)
        $platforms = $this->platformModel
            ->select('platform.*, platform.status_pos, platform.gw_status, platform.gw_code')
            ->where('status', '1')
            ->orderBy('platform', 'ASC')
            ->findAll();

            // Get active fee types for fee dropdown
        $feeTypes = $this->feeTypeModel->getActiveFeeTypes();

            // Get PPN setting from settings table
        $baseModel = new \App\Models\BaseModel();
        $settings = $baseModel->getSettingAplikasi();
        $ppnPercentage = (float) ($settings['ppn'] ?? 11); // Default 11%

            // Prepare view data
            $this->data['title'] = 'Tambah Penjualan';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['items'] = $items;
            $this->data['agents'] = $agents;
        $this->data['platforms'] = $platforms;
        $this->data['feeTypes'] = $feeTypes;
        $this->data['ppnPercentage'] = $ppnPercentage;
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
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message,
                ]);
            }
            return redirect()->to('login')
                ->with('message', [
                    'status'  => 'error',
                    'message' => $message,
                ]);
        }
        $userId = $userSession['id_user'];

        $postData = $this->request->getPost();
        $saleAgentId = !empty($postData['agent_id']) ? (int) $postData['agent_id'] : 0;
        $id = $postData['id'] ?? null;

        // Parse items from POST
        $items = $this->parseSaleItems($postData);
        if (empty($items)) {
            $message = 'Minimal harus ada satu item dalam transaksi.';
            if ($isAjax) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message,
                ]);
            }
            return redirect()->back()->withInput()->with('message', [
                'status'  => 'error',
                'message' => $message,
            ]);
        }

        // Validate invoice number
        if (empty($postData['invoice_no'])) {
            $message = 'Nomor invoice wajib diisi.';
            if ($isAjax) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message,
                ]);
            }
            return redirect()->back()->withInput()->with('message', [
                'status'  => 'error',
                'message' => $message,
            ]);
        }

        // Check invoice uniqueness (only for new records)
        if (empty($id)) {
            $existingSale = $this->model->where('invoice_no', $postData['invoice_no'])->first();
            if ($existingSale) {
                $message = 'Nomor invoice sudah digunakan.';
                if ($isAjax) {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => $message,
                    ]);
                }
                return redirect()->back()->withInput()->with('message', [
                    'status'  => 'error',
                    'message' => $message,
                ]);
            }
        }

        // Prepare plate data for customer lookup
        $platCode     = !empty($postData['plate_code'])   ? trim($postData['plate_code'])   : null;
        $platNumber   = !empty($postData['plate_number']) ? trim($postData['plate_number']) : null;
        $platLast     = !empty($postData['plate_suffix']) ? trim($postData['plate_suffix']) : null;
        $customerName = !empty($postData['customer_name']) ? trim($postData['customer_name']) : null;

        // Check if customer exists (read-only, can be outside transaction)
        $customerId = null;
        if ($platCode && $platNumber) {
            $existingCustomer = $this->customerModel->findByPlate($platCode, $platNumber, $platLast);
            if ($existingCustomer) {
                $customerId = $existingCustomer['id'];
            }
        } elseif (!empty($postData['customer_id'])) {
            $customerId = (int) $postData['customer_id'];
        }

        // Calculate grandTotal before payment gateway call (needed for API payload)
        $taxType = $postData['tax_type'] ?? '0';
        $subtotal = (float)($postData['subtotal'] ?? 0);
        $discountAmount = (float)($postData['discount'] ?? 0);
        $baseAmount = $subtotal - $discountAmount;
        
        // Get PPN percentage from settings
        $baseModel = new \App\Models\BaseModel();
        $settings = $baseModel->getSettingAplikasi();
        $ppnPercentage = (float) ($settings['ppn'] ?? 11); // Default 11%
        
        $taxAmount = 0;
        $grandTotal = $baseAmount;
        
        if ($taxType === '1') {
            // Include tax (PPN termasuk): tax is included in grand_total
            // If user enters grand_total, calculate tax from it
            $grandTotalInput = (float)($postData['grand_total'] ?? $baseAmount);
            // Tax = grand_total - (grand_total / (1 + ppn/100))
            $taxAmount = $grandTotalInput - ($grandTotalInput / (1 + ($ppnPercentage / 100)));
            $grandTotal = $grandTotalInput;
        } elseif ($taxType === '2') {
            // Added tax (PPN ditambahkan): tax is added on top
            $taxAmount = $baseAmount * ($ppnPercentage / 100);
            $grandTotal = $baseAmount + $taxAmount;
        } else {
            // No tax (tax_type = '0')
            $taxAmount = 0;
            $grandTotal = $baseAmount;
        }
        
        // Add total fees to grand total
        $totalFees = 0;
        if (!empty($postData['fees']) && is_array($postData['fees'])) {
            foreach ($postData['fees'] as $fee) {
                if (!empty($fee['amount'])) {
                    $totalFees += (float)$fee['amount'];
                }
            }
        }
        $grandTotal += $totalFees;

        // Handle payment gateway API call if platform is selected
        $gatewayResponse = null;
        $platformId = !empty($postData['platform_id']) ? (int) $postData['platform_id'] : null;

        if ($platformId) {
            // Get platform details
            $platform = $this->platformModel->find($platformId);

            // Only send to gateway if platform.gw_status = '1'
            // Platforms like "Tunai" (Cash) with gw_status = '0' should NOT go through gateway
            $gwStatus = $platform['gw_status'] ?? '0';
            $gwStatus = (string) $gwStatus;
            if ($platform && $gwStatus === '1') {
                // Prepare API request data
                // Use calculated grandTotal instead of POST data to ensure consistency
                $invoiceNo  = trim($postData['invoice_no']);
                // Use calculated grandTotal (already calculated above)

                // Get customer email and phone from existing customer or form data
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
                $nameParts = !empty($customerName) ? explode(' ', $customerName, 2) : ['Customer', 'Customer'];
                $firstName = $nameParts[0] ?? 'Customer';
                $lastName  = $nameParts[1] ?? $firstName;

                // Prepare API payload matching API specification
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

                // Log the payload being sent (for debugging)
                log_message('error', 'Sales::store - Gateway API Payload: ' . json_encode($apiData, JSON_PRETTY_PRINT));

                // Call payment gateway API
                $gatewayResponse = $this->callPaymentGateway($apiData);

                if ($gatewayResponse === null) {
                    // Get debug file content if it exists
                    $debugFile    = WRITEPATH . 'logs/gateway-debug-' . date('Y-m-d') . '.txt';
                    $debugContent = '';
                    if (file_exists($debugFile)) {
                        $debugContent = file_get_contents($debugFile);
                    }

                    $logFile      = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.log';
                    $errorDetails = 'Gagal mengirim ke payment gateway.';

                    // Try to get more details from the last log entry
                    if (file_exists($logFile)) {
                        $logContent = file_get_contents($logFile);
                        $lines = explode("\n", $logContent);
                        $lastError = '';
                        for ($i = count($lines) - 1; $i >= 0; $i--) {
                            if (stripos($lines[$i], 'callPaymentGateway') !== false) {
                                $lastError = trim($lines[$i]);
                                break;
                            }
                        }
                        if ($lastError) {
                            $errorDetails .= ' Detail: ' . substr($lastError, 0, 200);
                        }
                    }

                    $message = $errorDetails . ' Silakan cek log di: ' . $logFile;
                    if ($isAjax) {
                        return $this->response->setJSON([
                            'status'    => 'error',
                            'message'   => $message,
                            'debug'     => [
                                'log_file'      => $logFile,
                                'log_exists'    => file_exists($logFile),
                                'debug_file'    => $debugFile,
                                'debug_exists'  => file_exists($debugFile),
                                'debug_content' => substr($debugContent, -500), // Last 500 chars
                                'api_data'      => $apiData,
                                'platform_id'   => $platformId,
                                'gw_status'     => $platform['gw_status'] ?? 'N/A',
                            ],
                        ]);
                    }
                    return redirect()->back()->withInput()->with('message', [
                        'status'  => 'error',
                        'message' => $message,
                    ]);
                }
            }
        }

        // Start transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Create customer if not found but plate data provided
            if ($customerId === null && $platCode && $platNumber && $customerName) {
                $customerData = [
                    'name'       => $customerName,
                    'plat_code'  => $platCode,
                    'plat_number' => $platNumber,
                    'plat_last'  => $platLast ?: null,
                    'status'     => 'active',
                ];

                $this->customerModel->skipValidation(true);
                $insertResult = $this->customerModel->insert($customerData);
                if (!$insertResult) {
                    $errors = $this->customerModel->errors();
                    $errorMsg = 'Gagal membuat customer: ';
                    if ($errors && is_array($errors)) {
                        $errorMsg .= implode(', ', array_map(function ($e) {
                            return is_array($e) ? json_encode($e) : $e;
                        }, $errors));
                    }
                    throw new \Exception($errorMsg);
                }
                $customerId = $this->customerModel->getInsertID();
                $this->customerModel->skipValidation(false);
            }

            // Determine payment status based on gateway response
            $paymentStatus = $postData['payment_status'] ?? '0';
            if ($gatewayResponse && isset($gatewayResponse['status'])) {
                $gatewayStatus = strtoupper($gatewayResponse['status']);
                if ($gatewayStatus === 'PAID') {
                    $paymentStatus = '2'; // paid
                } elseif (in_array($gatewayStatus, ['PENDING', 'FAILED', 'CANCELED', 'EXPIRED'])) {
                    $paymentStatus = '0'; // unpaid
                }
            }

            // Use already calculated grandTotal (calculated before payment gateway call)
            // Tax calculation and grandTotal are already done above

            // Save to sales table
            $saleData = [
                'invoice_no'     => trim($postData['invoice_no']),
                'user_id'        => $userId,
                'customer_id'    => $customerId,
                'warehouse_id'   => $saleAgentId > 0 ? $saleAgentId : null,
                'sale_channel'   => $postData['sales_channel'] ?? self::CHANNEL_OFFLINE,
                'total_amount'   => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'     => $taxAmount,
                'tax_type'       => $taxType,
                'grand_total'    => $grandTotal,
                'payment_status' => $paymentStatus,
            ];

            // Add settlement_time if gateway response has it and status is PAID
            if (
                $gatewayResponse &&
                isset($gatewayResponse['settlementTime']) &&
                isset($gatewayResponse['status']) &&
                strtoupper($gatewayResponse['status']) === 'PAID'
            ) {
                try {
                    $settlementDateTime = new \DateTime($gatewayResponse['settlementTime']);
                    $saleData['settlement_time'] = $settlementDateTime->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    log_message('error', 'Sales::store - Invalid settlementTime format: ' . ($gatewayResponse['settlementTime'] ?? ''));
                }
            }

            $this->model->skipValidation(true);
            if ($id) {
                $updateResult = $this->model->update($id, $saleData);
                if (!$updateResult) {
                    $errors = $this->model->errors();
                    $errorMsg = 'Gagal update data penjualan: ';
                    if ($errors && is_array($errors)) {
                        $errorMsg .= implode(', ', array_map(function ($e) {
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
                    $errors  = $this->model->errors();
                    $dbError = $db->error();
                    $errorMsg = 'Gagal insert data penjualan. ';
                    if ($errors && is_array($errors)) {
                        $errorMsg .= 'Validation: ' . implode(', ', array_map(function ($e) {
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
                $itemRecord = $itemModel->find((int) $item['item_id']);
                $itemName = 'Unknown';
                if ($itemRecord) {
                    if (is_array($itemRecord)) {
                        $itemName = isset($itemRecord['name']) ? (string) $itemRecord['name'] : 'Unknown';
                    } else {
                        $itemName = isset($itemRecord->name) ? (string) $itemRecord->name : 'Unknown';
                    }
                }

                // Insert to sales_detail - ensure sn is a string
                $snValue = null;
                if (!empty($item['sns'])) {
                    $snValue = is_array($item['sns']) ? json_encode($item['sns']) : (string) $item['sns'];
                }

                $this->salesDetailModel->skipValidation(true);
                $salesDetailData = [
                    'sale_id'    => $saleId,
                    'item_id'    => (int) $item['item_id'],
                    'variant_id' => !empty($item['variant_id']) ? (int) $item['variant_id'] : null,
                    'sn'         => $snValue,
                    'item'       => $itemName,
                    'price'      => (float) ($item['price'] ?? 0),
                    'qty'        => (int) ($item['qty'] ?? 1),
                    'disc'       => (float) ($item['discount'] ?? 0),
                    'amount'     => (float) ($item['subtotal'] ?? 0),
                ];
                $detailInsertResult = $this->salesDetailModel->insert($salesDetailData);
                if (!$detailInsertResult) {
                    $errors = $this->salesDetailModel->errors();
                    $errorMsg = 'Gagal insert sales_detail: ';
                    if ($errors && is_array($errors)) {
                        $errorMsg .= implode(', ', array_map(function ($e) {
                            return is_array($e) ? json_encode($e) : (string) $e;
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
                        // Only activate serial numbers if platform.gw_status = '0' (cash/offline payment)
                        $shouldActivateSN = false;
                        if ($platformId && isset($platform)) {
                            $gwStatus = $platform['gw_status'] ?? '0';
                            $gwStatus = (string) $gwStatus;
                            if ($gwStatus === '0') {
                                $shouldActivateSN = true;
                            }
                        } elseif (!$platformId) {
                            // No platform selected, treat as cash payment
                            $shouldActivateSN = true;
                        }

                        if ($shouldActivateSN) {
                            // Get item warranty from item record
                            $itemWarranty = 0;
                            if ($itemRecord) {
                                if (is_array($itemRecord)) {
                                    $itemWarranty = isset($itemRecord['warranty']) ? (int) $itemRecord['warranty'] : 0;
                                } else {
                                    $itemWarranty = isset($itemRecord->warranty) ? (int) $itemRecord->warranty : 0;
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
                                    $itemSnId = (int) $sn['item_sn_id'];
                                    $snValue  = (string) $sn['sn'];

                                    // Save to SalesItemSnModel
                                    $this->salesItemSnModel->skipValidation(true);
                                    $salesItemSnData = [
                                        'sales_item_id' => $salesDetailId,
                                        'item_sn_id'    => $itemSnId,
                                        'sn'            => $snValue,
                                    ];
                                    $this->salesItemSnModel->insert($salesItemSnData);
                                    $this->salesItemSnModel->skipValidation(false);

                                    // Update ItemSnModel with warranty expiration
                                    $updateData = [
                                        'is_sell'      => '1',
                                        'is_activated' => '1',
                                        'activated_at' => $activatedAt,
                                    ];

                                    $updateData['agent_id'] = $saleAgentId > 0 ? $saleAgentId : 0;

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
            }

            // Save fees if provided
            if ($saleId && !empty($postData['fees']) && is_array($postData['fees'])) {
                // Delete existing fees for this sale (in case of update)
                $this->salesFeeModel->deleteBySale($saleId);
                
                // Insert new fees
                $feesToInsert = [];
                foreach ($postData['fees'] as $fee) {
                    if (!empty($fee['fee_type_id']) && !empty($fee['amount']) && (float)$fee['amount'] > 0) {
                        $feesToInsert[] = [
                            'sale_id' => $saleId,
                            'fee_type_id' => (int)$fee['fee_type_id'],
                            'fee_name' => !empty($fee['fee_name']) ? trim($fee['fee_name']) : null,
                            'amount' => (float)$fee['amount'],
                        ];
                    }
                }
                
                if (!empty($feesToInsert)) {
                    $this->salesFeeModel->skipValidation(true);
                    $this->salesFeeModel->insertBatch($feesToInsert);
                    $this->salesFeeModel->skipValidation(false);
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
                    'sale_id'    => $saleId,
                    'platform_id' => $platformId,
                    'method'     => $paymentMethod,
                    'amount'     => (float) ($postData['grand_total'] ?? 0),
                    'note'       => '', // Manual notes if needed
                    'response'   => $gatewayResponseJson, // Full gateway response JSON
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
            } elseif ($saleId && $platformId) {
                // Platform selected but no gateway response (cash payment via platform like "Tunai")
                $paymentData = [
                    'sale_id'    => $saleId,
                    'platform_id' => $platformId,
                    'method'     => 'cash',
                    'amount'     => (float) ($postData['grand_total'] ?? 0),
                    'note'       => '',
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
                                'response' => $latestResponseJson,
                            ]);
                            $this->salesPaymentsModel->skipValidation(false);

                            // Update gateway response variable for response data
                            $gatewayResponse = $latestGatewayResponse;

                            log_message('info', 'Sales::store - Updated payment response with latest gateway data for invoice: ' . $invoiceNo);
                        }
                    }
                } catch (\Exception $e) {
                    // Don't fail the transaction if status fetch fails, just log it
                    log_message('error', 'Sales::store - Failed to fetch latest payment status: ' . $e->getMessage());
                }
            }

            $message      = $id ? 'Penjualan berhasil diupdate.' : 'Penjualan berhasil disimpan.';
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
                    'url'                    => $gatewayResponse['url'],
                    'status'                 => $gatewayResponse['status'] ?? 'PENDING',
                    'paymentGatewayAdminFee' => $gatewayResponse['paymentGatewayAdminFee'] ?? 0,
                    'originalAmount'         => $gatewayResponse['originalAmount'] ?? $grandTotal,
                    'chargeCustomerForPaymentGatewayFee' => $gatewayResponse['chargeCustomerForPaymentGatewayFee'] ?? false,
                    'totalReceive'           => $totalReceive,
                ];
            }

            if ($isAjax) {
                return $this->response->setJSON([
                    'status'   => 'success',
                    'message'  => $message,
                    'data'     => $responseData,
                    'redirect' => $this->config->baseURL . 'sales/' . $saleId,
                ]);
            }

            return redirect()->to('sales/' . $saleId)
                ->with('message', [
                    'status'  => 'success',
                    'message' => $message,
                ]);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Sales::store error: ' . $e->getMessage());

            $message = 'Gagal menyimpan penjualan: ' . $e->getMessage();
            if ($isAjax) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $message,
                ]);
            }

            return redirect()->back()->withInput()->with('message', [
                'status'  => 'error',
                'message' => $message,
            ]);
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

            // Get fees for this sale
            $fees = $this->salesFeeModel->getFeesBySale($id);

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
            $this->data['fees'] = $fees;
            $this->data['payment'] = $paymentInfo;
            $this->data['gatewayResponse'] = $gatewayResponse;

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
     * Print sales detail in dot matrix format
     * 
     * @param int $id Sale ID
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function print_dm(int $id)
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

            $this->data['title'] = 'Cetak Nota';
            $this->data['currentModule'] = $this->currentModule;
            $this->data['config'] = $this->config;
            $this->data['sale'] = $sale;
            $this->data['items'] = $items;
            $this->data['payment'] = $paymentInfo;
            $this->data['gatewayResponse'] = $gatewayResponse;

            return view('themes/modern/sales/sales-print', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Sales::print_dm error: ' . $e->getMessage());
            return redirect()->to('sales')->with('message', [
                'status' => 'error',
                'message' => 'Gagal memuat data untuk cetak.'
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
            // Filter total records by offline channel
            $totalRecords = $db->table('sales')
                               ->where('sale_channel', self::CHANNEL_OFFLINE)
                               ->countAllResults();

            // Main query builder with joins
            $query = $this->buildSalesQuery();
            // Filter by offline channel
            $query->where('sales.sale_channel', self::CHANNEL_OFFLINE);

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
                // Filter by offline channel
                $countQuery->where('sales.sale_channel', self::CHANNEL_OFFLINE);
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
            user.nama as user_name,
            COALESCE(sales.balance_due, sales.grand_total - COALESCE(sales.total_payment, 0)) as balance_due')
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

            // Calculate balance_due
            $balanceDue = 0;
            if (isset($row['balance_due'])) {
                $balanceDue = (float)$row['balance_due'];
            } else {
                $grandTotal = (float)($row['grand_total'] ?? 0);
                $totalPayment = (float)($row['total_payment'] ?? 0);
                $balanceDue = $grandTotal - $totalPayment;
            }

            $result[] = [
                'ignore_search_urut'    => $no,
                'invoice_no'            => esc($row['invoice_no'] ?? ''),
                'customer_name'         => esc($row['customer_name'] ?? '-'),
                'agent_name'            => esc($row['agent_name'] ?? '-'),
                'grand_total'           => format_angka((float) ($row['grand_total'] ?? 0), 2),
                'balance_due'           => format_angka($balanceDue, 2),
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
                log_message('error', 'Sales::getPaymentStatusFromGateway - API key encryption failed: ' . $e->getMessage());
                return null;
            }
            
            $client = \Config\Services::curlrequest();
            $apiUrl = 'https://dev.osu.biz.id/mig/esb/v1/api/payments/' . urlencode($invoiceNo);
            
            log_message('info', 'Sales::getPaymentStatusFromGateway - Fetching payment status for: ' . $invoiceNo);
            
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
            
            log_message('info', 'Sales::getPaymentStatusFromGateway - Response Status: ' . $statusCode);
            log_message('info', 'Sales::getPaymentStatusFromGateway - Response Body: ' . $body);
            
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
                log_message('error', 'Sales::getPaymentStatusFromGateway - API Error: ' . $errorMsg . ' | Status: ' . $statusCode);
                return null;
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Sales::getPaymentStatusFromGateway error: ' . $e->getMessage());
            log_message('error', 'Sales::getPaymentStatusFromGateway trace: ' . $e->getTraceAsString());
            return null;
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

