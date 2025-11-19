<?php

/**
 * Agent Paylater Controller
 * 
 * Handles agent paylater transaction management
 * 
 * @package    App\Controllers\Agent
 * @author     Mikhael Felian Waskito <mikhaelfelian@gmail.com>
 * @copyright  2025
 * @license    MIT
 * @version    1.0.0
 * @since      2025-11-19
 */

namespace App\Controllers\Agent;

use App\Controllers\BaseController;
use App\Models\AgentPaylaterModel;
use App\Models\AgentModel;
use App\Models\SalesModel;
use App\Models\UserRoleAgentModel;
use App\Models\PlatformModel;
use App\Models\SalesGatewayLogModel;
use CodeIgniter\HTTP\ResponseInterface;

class Paylater extends BaseController
{
    protected $model;
    protected $agentModel;
    protected $salesModel;
    protected $userRoleAgentModel;
    protected $platformModel;
    protected $salesGatewayLogModel;
    
    /**
     * Initialize models
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new AgentPaylaterModel();
        $this->agentModel = new AgentModel();
        $this->salesModel = new SalesModel();
        $this->userRoleAgentModel = new UserRoleAgentModel();
        $this->platformModel = new PlatformModel();
        $this->salesGatewayLogModel = new SalesGatewayLogModel();
    }
    
    /**
     * Display paylater transactions list page
     * 
     * @return void
     */
    public function index(): void
    {
        // Pass permission data to view
        $isAdmin = $this->hasPermission('read_all');
        $this->data['read_all'] = $isAdmin;

        $this->data = array_merge($this->data, [
            'title'         => 'Data Paylater',
            'currentModule' => $this->currentModule,
            'config'        => $this->config,
            'msg'           => $this->session->getFlashdata('message'),
        ]);

        $this->data['breadcrumb'] = [
            'Home'         => $this->config->baseURL,
            'Data Paylater' => '', // Current page, no link
        ];

        $this->view('sales/agent/paylater-result', $this->data);
    }

    /**
     * Show paylater transaction detail
     * 
     * @param int $id Paylater transaction ID
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function detail(int $id)
    {
        if ($id <= 0) {
            return redirect()->to('paylater')->with('message', [
                'status' => 'error',
                'message' => 'ID transaksi tidak valid.'
            ]);
        }

        try {
            // Get paylater transaction with relations
            $transaction = $this->model->find($id);
            
            if (!$transaction) {
                return redirect()->to('paylater')->with('message', [
                    'status' => 'error',
                    'message' => 'Data transaksi tidak ditemukan.'
                ]);
            }

            // Check permission - non-admin users can only see their agent's records
            $userPermission = is_array($this->userPermission) ? $this->userPermission : [];
            $isAdmin = key_exists('read_all', $userPermission) || key_exists('update_all', $userPermission);
            
            if (!$isAdmin) {
                $userId = !empty($this->user) && is_array($this->user) ? ($this->user['id_user'] ?? null) : null;
                if ($userId) {
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

                    if (!in_array((int)$transaction->agent_id, $agentIds)) {
                        return redirect()->to('paylater')->with('message', [
                            'status' => 'error',
                            'message' => 'Anda tidak memiliki akses ke data ini.'
                        ]);
                    }
                }
            }

            // Get agent info
            $agent = $this->agentModel->find($transaction->agent_id);
            
            // Get sales info if sale_id exists
            $sale = null;
            if (!empty($transaction->sale_id)) {
                $sale = $this->salesModel->find($transaction->sale_id);
            }

            // Format mutation type
            $mutationTypeLabels = [
                '1' => 'Pembelian',
                '2' => 'Pembayaran',
                '3' => 'Penyesuaian'
            ];
            $mutationTypeLabel = $mutationTypeLabels[$transaction->mutation_type] ?? 'Unknown';

            $this->data['title'] = 'Detail Transaksi Paylater';
            $this->data['currentModule'] = 'Agen &laquo; ' . ($agent->name ?? 'Unknown');
            $this->data['config'] = $this->config;
            $this->data['transaction'] = $transaction;
            $this->data['agent'] = $agent;
            $this->data['sale'] = $sale;
            $this->data['mutationTypeLabel'] = $mutationTypeLabel;
            $this->data['isAdmin'] = $isAdmin;

            $this->data['breadcrumb'] = [
                'Home'         => $this->config->baseURL.'agent/dashboard',
                'Data Paylater' => $this->config->baseURL.'agent/paylater',
                'Detail'       => '', // Current page, no link
            ];

            $this->view('sales/agent/paylater-detail', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Paylater::detail error: ' . $e->getMessage());
            return redirect()->to('paylater')->with('message', [
                'status' => 'error',
                'message' => 'Gagal memuat detail transaksi.'
            ]);
        }
    }

    /**
     * Single payment form route (GET) and payment processing (POST)
     * 
     * @param int $id Paylater transaction ID
     * @return \CodeIgniter\HTTP\RedirectResponse|ResponseInterface|void
     */
    public function pay(int $id)
    {
        // Handle POST request (payment processing)
        if ($this->request->getMethod() === 'post') {
            return $this->processPayment($id);
        }

        // Handle GET request (show form)
        if ($id <= 0) {
            return redirect()->to('agent/paylater')->with('message', [
                'status' => 'error',
                'message' => 'ID transaksi tidak valid.'
            ]);
        }

        try {
            // Get paylater transaction
            $transaction = $this->model->find($id);
            
            if (!$transaction) {
                return redirect()->to('agent/paylater')->with('message', [
                    'status' => 'error',
                    'message' => 'Data transaksi paylater tidak ditemukan.'
                ]);
            }

            // Only allow payment for purchase type (mutation_type = '1')
            if ($transaction->mutation_type !== '1') {
                return redirect()->to('agent/paylater')->with('message', [
                    'status' => 'error',
                    'message' => 'Hanya transaksi pembelian yang dapat dibayar.'
                ]);
            }

            // Get agent information
            $agent = $this->agentModel->find($transaction->agent_id);
            if (!$agent) {
                return redirect()->to('agent/paylater')->with('message', [
                    'status' => 'error',
                    'message' => 'Data agen tidak ditemukan.'
                ]);
            }

            // Get sales information if sale_id exists
            $sale = null;
            $invoiceNo = $transaction->reference_code ?? 'N/A';
            if (!empty($transaction->sale_id)) {
                $sale = $this->salesModel->find($transaction->sale_id);
                if ($sale) {
                    $invoiceNo = $sale['invoice_no'] ?? $invoiceNo;
                }
            }

            // Get platforms: status='1' and is_agent='1' (or status_agent='1')
            // Note: User specified is_agent, but checking both for compatibility
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
                $gwStatus = (string) ($platform['gw_status'] ?? '0');
                if ($gwStatus === '0') {
                    $platformsManualTransfer[] = $platform;
                } elseif ($gwStatus === '1') {
                    $platformsPaymentGateway[] = $platform;
                }
            }

            // Get amount from transaction
            $amount = (float) ($transaction->amount ?? 0);

            $this->data = array_merge($this->data, [
                'title'         => 'Form Pembayaran Paylater',
                'currentModule' => $this->currentModule,
                'config'        => $this->config,
                'transaction'   => $transaction,
                'agent'         => $agent,
                'sale'          => $sale,
                'invoiceNo'     => $invoiceNo,
                'amount'        => $amount,
                'platforms'     => $allPlatforms,
                'platformsManualTransfer' => $platformsManualTransfer,
                'platformsPaymentGateway' => $platformsPaymentGateway,
                'msg'           => $this->session->getFlashdata('message'),
            ]);

            $this->data['breadcrumb'] = [
                'Home'         => $this->config->baseURL.'agent/dashboard',
                'Data Paylater' => $this->config->baseURL.'agent/paylater',
                'Form Pembayaran' => '',
            ];

            $this->view('sales/agent/paylater-pay', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Paylater::pay error: ' . $e->getMessage());
            return redirect()->to('agent/paylater')->with('message', [
                'status' => 'error',
                'message' => 'Gagal memuat form pembayaran: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Render Bootbox bulk payment form (HTML response)
     *
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function payMass()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'status'  => 'error',
                'message' => 'Request tidak valid.'
            ]);
        }

        try {
            $saleIds = $this->request->getPost('sale_ids');
            if (!is_array($saleIds) || empty($saleIds)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status'  => 'error',
                    'message' => 'Invoice belum dipilih.'
                ]);
            }

            $saleIds = array_values(array_unique(array_filter(array_map('intval', $saleIds))));
            if (empty($saleIds)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status'  => 'error',
                    'message' => 'Invoice tidak valid.'
                ]);
            }

            $sales = $this->salesModel
                ->select('sales.*, customer.name as customer_name, agent.name as agent_name')
                ->join('customer', 'customer.id = sales.customer_id', 'left')
                ->join('agent', 'agent.id = sales.warehouse_id', 'left')
                ->whereIn('sales.id', $saleIds)
                ->findAll();

            if (count($sales) !== count($saleIds)) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status'  => 'error',
                    'message' => 'Sebagian invoice tidak ditemukan.'
                ]);
            }

            $selected = [];
            $agentId = null;
            foreach ($sales as $sale) {
                if (($sale['payment_status'] ?? '') !== '3') {
                    return $this->response->setStatusCode(422)->setJSON([
                        'status'  => 'error',
                        'message' => 'Terdapat invoice yang bukan paylater.'
                    ]);
                }

                $saleAgentId = (int) ($sale['warehouse_id'] ?? 0);
                if ($saleAgentId <= 0) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'status'  => 'error',
                        'message' => 'Data agen tidak valid.'
                    ]);
                }

                if ($agentId === null) {
                    $agentId = $saleAgentId;
                } elseif ($agentId !== $saleAgentId) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'status'  => 'error',
                        'message' => 'Tidak dapat membayar invoice dari agen berbeda.'
                    ]);
                }

                $balance = $this->getSaleOutstandingAmount((int) $sale['id']);
                if ($balance <= 0) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'status'  => 'error',
                        'message' => 'Invoice ' . ($sale['invoice_no'] ?? '') . ' sudah lunas.'
                    ]);
                }

                $selected[] = [
                    'id'            => (int) $sale['id'],
                    'invoice_no'    => $sale['invoice_no'] ?? 'INV-' . $sale['id'],
                    'customer_name' => $sale['customer_name'] ?? '-',
                    'grand_total'   => (float) ($sale['grand_total'] ?? 0),
                    'outstanding'   => round($balance, 2),
                ];
            }

            $mode = count($selected) > 1 ? 'multiple' : 'single';
            $totalOutstanding = array_sum(array_column($selected, 'outstanding'));

            $allPlatforms = $this->platformModel
                ->select('platform.*, platform.status_pos, platform.gw_status, platform.gw_code')
                ->where('status', '1')
                ->where('status_agent', '1')
                ->orderBy('platform', 'ASC')
                ->findAll();

            $platformsManualTransfer = [];
            $platformsPaymentGateway = [];

            foreach ($allPlatforms as $platform) {
                $gwStatus = (string) ($platform['gw_status'] ?? '0');
                if ($gwStatus === '1') {
                    $platformsPaymentGateway[] = $platform;
                } else {
                    $platformsManualTransfer[] = $platform;
                }
            }

            $data = [
                'mode'                    => $mode,
                'selected'                => $selected,
                'totalOutstanding'        => $totalOutstanding,
                'platformsManualTransfer' => $platformsManualTransfer,
                'platformsPaymentGateway' => $platformsPaymentGateway,
                'csrfName'                => csrf_token(),
                'csrfHash'                => csrf_hash(),
            ];

            return view('themes/modern/sales/agent/paylater-bulk-form', $data);
        } catch (\Throwable $e) {
            log_message('error', 'Paylater::payMass error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Gagal memuat form pembayaran.'
            ]);
        }
    }

    /**
     * Process payment for paylater transaction
     * 
     * @param int $id Paylater transaction ID
     * @return ResponseInterface
     */
    public function processPayment(int $id): ResponseInterface
    {
        $isAjax = $this->request->isAJAX();
        
        try {
            if ($id <= 0) {
                $message = 'ID transaksi tidak valid.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->to('agent/paylater')->with('message', [
                    'status' => 'error',
                    'message' => $message
                ]);
            }

            // Get paylater transaction
            $transaction = $this->model->find($id);
            if (!$transaction) {
                $message = 'Data transaksi paylater tidak ditemukan.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->to('agent/paylater')->with('message', [
                    'status' => 'error',
                    'message' => $message
                ]);
            }

            // Only allow payment for purchase type (mutation_type = '1')
            if ($transaction->mutation_type !== '1') {
                $message = 'Hanya transaksi pembelian yang dapat dibayar.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->to('agent/paylater')->with('message', [
                    'status' => 'error',
                    'message' => $message
                ]);
            }

            // Get POST data
            $postData = $this->request->getPost();
            $platformId = !empty($postData['platform_id']) ? (int)$postData['platform_id'] : null;

            if (!$platformId) {
                $message = 'Platform pembayaran harus dipilih.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->back()->withInput()->with('message', [
                    'status' => 'error',
                    'message' => $message
                ]);
            }

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

            // Get amount from transaction
            $amount = (float) ($transaction->amount ?? 0);
            if ($amount <= 0) {
                $message = 'Jumlah pembayaran tidak valid.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->back()->withInput()->with('message', [
                    'status' => 'error',
                    'message' => $message
                ]);
            }

            // Get agent information
            $agent = $this->agentModel->find($transaction->agent_id);
            if (!$agent) {
                $message = 'Data agen tidak ditemukan.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->back()->withInput()->with('message', [
                    'status' => 'error',
                    'message' => $message
                ]);
            }

            // Get sales information for invoice number
            $invoiceNo = $transaction->reference_code ?? 'PAYLATER-' . $transaction->id;
            $sale = null;
            if (!empty($transaction->sale_id)) {
                $sale = $this->salesModel->find($transaction->sale_id);
                if ($sale) {
                    $invoiceNo = $sale['invoice_no'] ?? $invoiceNo;
                }
            }

            // Generate payment invoice number
            $paymentInvoiceNo = 'PAY-' . date('YmdHis') . '-' . $transaction->id;

            // Handle payment gateway API call
            $gatewayResponse = null;
            $isOfflinePlatform = false;
            $settlementTime = null;

            // Only send to gateway if platform.gw_status = '1'
            $gwStatus = $platform['gw_status'] ?? '0';
            $gwStatus = (string)$gwStatus;
            
            if ($gwStatus === '1') {
                // Prepare API request data
                $customerName = $agent->name ?? 'Agent';
                $nameParts = !empty($customerName) ? explode(' ', $customerName, 2) : ['Agent', 'Agent'];
                $firstName = $nameParts[0] ?? 'Agent';
                $lastName = $nameParts[1] ?? $firstName;

                // Check if invoice number has been sent to gateway before
                if ($this->salesGatewayLogModel->invoiceExists($paymentInvoiceNo)) {
                    log_message('warning', 'Paylater::processPayment - Invoice number already sent to gateway: ' . $paymentInvoiceNo . '. Generating new invoice number.');
                    $paymentInvoiceNo = 'PAY-' . date('YmdHis') . '-' . $transaction->id . '-' . rand(1000, 9999);
                }

                // Prepare API payload
                $apiData = [
                    'code'     => $platform['gw_code'] ?? 'QRIS',
                    'orderId'  => $paymentInvoiceNo,
                    'amount'   => (int) round($amount),
                    'customer' => [
                        'firstName' => $firstName,
                        'lastName'  => $lastName,
                        'email'     => $agent->email ?? 'agent@example.com',
                        'phone'     => $agent->phone ?? '',
                    ],
                ];

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

                // Log to sales_gateway_logs
                $gatewayStatus = null;
                if ($gatewayResponse && isset($gatewayResponse['status'])) {
                    $gatewayStatus = $gatewayResponse['status'];
                }
                
                $this->salesGatewayLogModel->logGatewayRequest(
                    $paymentInvoiceNo,
                    $platformId,
                    $amount,
                    $apiData,
                    $gatewayResponse,
                    $gatewayStatus
                );
            } else {
                // Offline/cash platform, bypass gateway
                $isOfflinePlatform = true;
                $settlementTime = date('Y-m-d H:i:s');
            }

            // Start database transaction
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // Create repayment record in agent_paylater (mutation_type = '2', negative amount)
                $repaymentData = [
                    'agent_id' => $transaction->agent_id,
                    'sale_id' => $transaction->sale_id,
                    'mutation_type' => '2', // repayment
                    'amount' => -$amount, // negative value for repayment
                    'description' => 'Pembayaran paylater - Invoice: ' . $invoiceNo,
                    'reference_code' => $paymentInvoiceNo
                ];

                $this->model->skipValidation(true);
                $repaymentInsertResult = $this->model->insert($repaymentData);
                $this->model->skipValidation(false);

                if (!$repaymentInsertResult) {
                    $errors = $this->model->errors();
                    $errorMsg = 'Gagal membuat record pembayaran: ';
                    if ($errors && is_array($errors)) {
                        $errorMsg .= implode(', ', array_map(function($e) {
                            return is_array($e) ? json_encode($e) : $e;
                        }, $errors));
                    }
                    throw new \Exception($errorMsg);
                }

                // Update agent credit_limit (add back the amount)
                $currentCredit = (float) ($agent->credit_limit ?? 0);
                $newCreditLimit = $currentCredit + $amount;

                $this->agentModel->skipValidation(true);
                $updateResult = $this->agentModel->update($transaction->agent_id, [
                    'credit_limit' => $newCreditLimit
                ]);
                $this->agentModel->skipValidation(false);

                if (!$updateResult) {
                    throw new \Exception('Gagal memperbarui limit kredit agen.');
                }

                // Complete transaction
                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Transaksi gagal.');
                }

                // Prepare response data
                $message = 'Pembayaran berhasil diproses.';
                $responseData = ['id' => $id];

                // Include gateway response data if available
                if ($gatewayResponse && !empty($gatewayResponse['url'])) {
                    $totalReceive = $amount;
                    
                    // Calculate totalReceive based on chargeCustomerForPaymentGatewayFee
                    if (isset($gatewayResponse['chargeCustomerForPaymentGatewayFee']) && 
                        isset($gatewayResponse['originalAmount'])) {
                        
                        $chargeCustomer = $gatewayResponse['chargeCustomerForPaymentGatewayFee'];
                        $originalAmount = (float) ($gatewayResponse['originalAmount'] ?? $amount);
                        
                        if ($chargeCustomer === true || $chargeCustomer === 'true' || $chargeCustomer === 1 || $chargeCustomer === '1') {
                            $totalReceive = $originalAmount;
                        } else {
                            $adminFee = (float) ($gatewayResponse['paymentGatewayAdminFee'] ?? 0);
                            $totalReceive = $originalAmount - $adminFee;
                        }
                    }
                    
                    $responseData['gateway'] = [
                        'url' => $gatewayResponse['url'],
                        'status' => $gatewayResponse['status'] ?? 'PENDING',
                        'paymentGatewayAdminFee' => $gatewayResponse['paymentGatewayAdminFee'] ?? 0,
                        'originalAmount' => $gatewayResponse['originalAmount'] ?? $amount,
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

                return redirect()->to("agent/paylater/{$id}")->with('message', [
                    'status' => 'success',
                    'message' => $message
                ]);

            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }

        } catch (\Exception $e) {
            log_message('error', 'Paylater::processPayment error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            
            $message = 'Gagal memproses pembayaran: ' . $e->getMessage();
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
     * Call payment gateway API (similar to Sales controller)
     * 
     * @param array $apiData API request data
     * @return array|null Gateway response or null on failure
     */
    protected function callPaymentGateway(array $apiData): ?array
    {
        $errorDetails = [];
        
        try {
            $client = \Config\Services::curlrequest();
            $apiUrl = 'https://dev.osu.biz.id/mig/esb/v1/api/payments';
            
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
                throw $e;
            }
            
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $responseData = json_decode($body, true);
            
            // Log response details
            log_message('error', 'Paylater::callPaymentGateway - Response Status: ' . $statusCode);
            log_message('error', 'Paylater::callPaymentGateway - Response Body: ' . $body);
            
            if ($statusCode >= 200 && $statusCode < 300 && $responseData) {
                // Return full response data (includes paymentCode, expiredAt, etc.)
                // Don't format it - keep all fields from gateway
                log_message('error', 'Paylater::callPaymentGateway - Gateway response received successfully');
                return $responseData;
            } else {
                $errorMsg = 'Payment gateway returned error';
                if (isset($responseData['message'])) {
                    $errorMsg = $responseData['message'];
                } elseif (!empty($body)) {
                    $errorMsg = 'Response: ' . $body;
                }
                
                // Log detailed error information
                $logMsg = 'Paylater::callPaymentGateway - API Error: ' . $errorMsg . ' | Status: ' . $statusCode . ' | Body: ' . $body;
                log_message('error', $logMsg);
                
                // Also write to a separate debug file to ensure we capture it
                $debugFile = WRITEPATH . 'logs/gateway-debug-' . date('Y-m-d') . '.txt';
                file_put_contents($debugFile, date('Y-m-d H:i:s') . " - " . $logMsg . "\n", FILE_APPEND);
                
                return null;
            }
            
        } catch (\Exception $e) {
            $errorMsg = 'Paylater::callPaymentGateway error: ' . $e->getMessage();
            $traceMsg = 'Paylater::callPaymentGateway trace: ' . $e->getTraceAsString();
            
            log_message('error', $errorMsg);
            log_message('error', $traceMsg);
            
            // Also write to debug file
            $debugFile = WRITEPATH . 'logs/gateway-debug-' . date('Y-m-d') . '.txt';
            file_put_contents($debugFile, date('Y-m-d H:i:s') . " - " . $errorMsg . "\n" . $traceMsg . "\n", FILE_APPEND);
            
            return null;
        }
    }

    /**
     * Handle bulk/single multi-select payment submission (AJAX)
     *
     * @return ResponseInterface
     */
    public function payBulk(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Request tidak valid.'
            ])->setStatusCode(405);
        }

        try {
            $saleIdsInput = $this->request->getPost('sale_ids');
            if (!is_array($saleIdsInput) || empty($saleIdsInput)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invoice belum dipilih.',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(422);
            }

            $saleIds = array_values(array_unique(array_map('intval', $saleIdsInput)));
            $saleIds = array_filter($saleIds);
            if (empty($saleIds)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invoice tidak valid.',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(422);
            }

            $mode = $this->request->getPost('mode') ?? (count($saleIds) > 1 ? 'multiple' : 'single');
            $platformId = (int) ($this->request->getPost('platform_id') ?? 0);
            $note = trim($this->request->getPost('note') ?? '');

            if ($platformId <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Platform pembayaran harus dipilih.',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(422);
            }

            $platform = $this->platformModel->find($platformId);
            if (!$platform || ($platform['status'] ?? '0') !== '1' || ($platform['status_agent'] ?? '0') !== '1') {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Platform pembayaran tidak valid.',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(422);
            }

            $sales = $this->salesModel->whereIn('id', $saleIds)->findAll();
            if (count($sales) !== count($saleIds)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Sebagian invoice tidak ditemukan.',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(404);
            }

            $saleData = [];
            $agentId = null;
            foreach ($sales as $sale) {
                if (($sale['payment_status'] ?? '') !== '3') {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Terdapat invoice yang bukan paylater.',
                        'csrf_hash' => csrf_hash()
                    ])->setStatusCode(422);
                }

                $saleAgentId = (int) ($sale['warehouse_id'] ?? 0);
                if ($saleAgentId <= 0) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Data agen tidak valid.',
                        'csrf_hash' => csrf_hash()
                    ])->setStatusCode(422);
                }

                if ($agentId === null) {
                    $agentId = $saleAgentId;
                } elseif ($agentId !== $saleAgentId) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Tidak dapat membayar invoice dari agen berbeda.',
                        'csrf_hash' => csrf_hash()
                    ])->setStatusCode(422);
                }

                $balance = $this->getSaleOutstandingAmount((int) $sale['id']);
                if ($balance <= 0) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Invoice ' . ($sale['invoice_no'] ?? '') . ' sudah lunas.',
                        'csrf_hash' => csrf_hash()
                    ])->setStatusCode(422);
                }

                $saleData[$sale['id']] = [
                    'id' => (int) $sale['id'],
                    'invoice_no' => $sale['invoice_no'] ?? 'INV-' . $sale['id'],
                    'balance' => round($balance, 2),
                    'agent_id' => $saleAgentId,
                    'customer_name' => $sale['customer_name'] ?? '-'
                ];
            }

            $amountMap = [];
            if (count($saleIds) > 1) {
                $mode = 'multiple';
                foreach ($saleData as $saleId => $info) {
                    $amountMap[$saleId] = $info['balance'];
                }
            } else {
                $mode = 'single';
                $singleSaleId = $saleIds[0];
                $requestedAmount = (float) ($this->request->getPost('amount') ?? 0);
                if ($requestedAmount <= 0 || $requestedAmount > $saleData[$singleSaleId]['balance']) {
                    $requestedAmount = $saleData[$singleSaleId]['balance'];
                }
                $amountMap[$singleSaleId] = round($requestedAmount, 2);
            }

            $totalPayment = array_sum($amountMap);
            if ($totalPayment <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Nominal pembayaran tidak valid.',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(422);
            }

            $agent = $this->agentModel->find($agentId);
            if (!$agent) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data agen tidak ditemukan.',
                    'csrf_hash' => csrf_hash()
                ])->setStatusCode(404);
            }

            $gwStatus = (string) ($platform['gw_status'] ?? '0');
            if ($mode === 'single') {
                $singleSaleId = $saleIds[0];
                if ($gwStatus === '1' && $amountMap[$singleSaleId] < $saleData[$singleSaleId]['balance']) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Pembayaran sebagian hanya tersedia untuk platform transfer manual.',
                        'csrf_hash' => csrf_hash()
                    ])->setStatusCode(422);
                }
            }

            $orderId = $this->generateOrderIdForBulk($mode, $saleData, $amountMap, $agentId);
            $gatewayResponse = null;
            $isOfflinePlatform = ($gwStatus !== '1');
            $settlementTime = null;

            if (!$isOfflinePlatform) {
                $shouldReuseInvoice = false;
                if ($mode === 'single') {
                    $singleSaleId = $saleIds[0];
                    $shouldReuseInvoice = abs($amountMap[$singleSaleId] - $saleData[$singleSaleId]['balance']) < 0.01;
                    if ($shouldReuseInvoice) {
                        $orderId = $saleData[$singleSaleId]['invoice_no'];
                    }
                }

                if ($shouldReuseInvoice && $this->salesGatewayLogModel->invoiceExists($orderId)) {
                    $existingGateway = $this->getPaymentStatusFromGateway($orderId);
                    if ($existingGateway !== null) {
                        $gatewayResponse = $existingGateway;
                    }
                }

                if ($gatewayResponse === null) {
                    if ($this->salesGatewayLogModel->invoiceExists($orderId)) {
                        $orderId = $this->generateUniqueOrderId($orderId);
                    }

                    $customerName = $agent->name ?? 'Agent';
                    $nameParts = !empty($customerName) ? explode(' ', $customerName, 2) : ['Agent', 'Agent'];
                    $firstName = $nameParts[0] ?? 'Agent';
                    $lastName = $nameParts[1] ?? $firstName;

                    $apiData = [
                        'code'     => $platform['gw_code'] ?? 'QRIS',
                        'orderId'  => $orderId,
                        'amount'   => (int) round($totalPayment),
                        'customer' => [
                            'firstName' => $firstName,
                            'lastName'  => $lastName,
                            'email'     => $agent->email ?? 'agent@example.com',
                            'phone'     => $agent->phone ?? '',
                        ],
                    ];

                    $gatewayResponse = $this->callPaymentGateway($apiData);
                    if ($gatewayResponse === null) {
                        $logFile = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.log';
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Gagal mengirim ke payment gateway. Silakan cek log di: ' . $logFile,
                            'csrf_hash' => csrf_hash()
                        ])->setStatusCode(500);
                    }

                    $gatewayStatus = $gatewayResponse['status'] ?? null;
                    $this->salesGatewayLogModel->logGatewayRequest(
                        $orderId,
                        $platformId,
                        $totalPayment,
                        $apiData,
                        $gatewayResponse,
                        $gatewayStatus
                    );
                }
            } else {
                $settlementTime = date('Y-m-d H:i:s');
            }

            $db = \Config\Database::connect();
            $db->transStart();

            try {
                foreach ($amountMap as $saleId => $amountToPay) {
                    $description = 'Pembayaran paylater - Invoice: ' . ($saleData[$saleId]['invoice_no'] ?? '');
                    if ($mode === 'multiple') {
                        $description = 'Pembayaran bulk paylater - Invoice: ' . ($saleData[$saleId]['invoice_no'] ?? '');
                    } elseif ($amountToPay < $saleData[$saleId]['balance']) {
                        $description = 'Pembayaran sebagian paylater - Invoice: ' . ($saleData[$saleId]['invoice_no'] ?? '');
                    }

                    $this->model->skipValidation(true);
                    $insertResult = $this->model->insert([
                        'agent_id'       => $agentId,
                        'sale_id'        => $saleId,
                        'mutation_type'  => '2',
                        'amount'         => -$amountToPay,
                        'description'    => $description,
                        'reference_code' => $orderId
                    ]);
                    $this->model->skipValidation(false);

                    if (!$insertResult) {
                        throw new \RuntimeException('Gagal membuat record pembayaran.');
                    }

                    $remainingAfter = $saleData[$saleId]['balance'] - $amountToPay;
                    if ($remainingAfter <= 0.01) {
                        $this->salesModel->skipValidation(true);
                        $this->salesModel->update($saleId, ['payment_status' => '2']);
                        $this->salesModel->skipValidation(false);
                    }
                }

                $currentCredit = (float) ($agent->credit_limit ?? 0);
                $this->agentModel->skipValidation(true);
                $this->agentModel->update($agentId, [
                    'credit_limit' => $currentCredit + $totalPayment
                ]);
                $this->agentModel->skipValidation(false);

                $db->transComplete();
                if ($db->transStatus() === false) {
                    throw new \RuntimeException('Transaksi gagal.');
                }
            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }

            $responseData = [
                'mode' => $mode,
                'sale_ids' => $saleIds,
                'total_payment' => $totalPayment
            ];

            if ($gatewayResponse && !empty($gatewayResponse['url'])) {
                $totalReceive = $totalPayment;
                if (isset($gatewayResponse['chargeCustomerForPaymentGatewayFee']) && isset($gatewayResponse['originalAmount'])) {
                    $chargeCustomer = $gatewayResponse['chargeCustomerForPaymentGatewayFee'];
                    $originalAmount = (float) ($gatewayResponse['originalAmount'] ?? $totalPayment);
                    if ($chargeCustomer === true || $chargeCustomer === 'true' || $chargeCustomer === 1 || $chargeCustomer === '1') {
                        $totalReceive = $originalAmount;
                    } else {
                        $adminFee = (float) ($gatewayResponse['paymentGatewayAdminFee'] ?? 0);
                        $totalReceive = $originalAmount - $adminFee;
                    }
                }

                $responseData['gateway'] = [
                    'url' => $gatewayResponse['url'],
                    'status' => $gatewayResponse['status'] ?? 'PENDING',
                    'paymentGatewayAdminFee' => $gatewayResponse['paymentGatewayAdminFee'] ?? 0,
                    'originalAmount' => $gatewayResponse['originalAmount'] ?? $totalPayment,
                    'chargeCustomerForPaymentGatewayFee' => $gatewayResponse['chargeCustomerForPaymentGatewayFee'] ?? false,
                    'totalReceive' => $totalReceive
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Pembayaran berhasil diproses.',
                'data' => $responseData,
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Paylater::payBulk error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    /**
     * Calculate outstanding amount for a sale from agent_paylater mutations
     *
     * @param int $saleId
     * @return float
     */
    protected function getSaleOutstandingAmount(int $saleId): float
    {
        if ($saleId <= 0) {
            return 0;
        }

        $db = \Config\Database::connect();
        $row = $db->table('agent_paylater')
            ->selectSum('amount')
            ->where('sale_id', $saleId)
            ->get()
            ->getRow();

        return round((float) ($row->amount ?? 0), 2);
    }

    /**
     * Generate base order ID for bulk/single payment
     *
     * @param string $mode
     * @param array $saleData
     * @param array $amountMap
     * @param int $agentId
     * @return string
     */
    protected function generateOrderIdForBulk(string $mode, array $saleData, array $amountMap, int $agentId): string
    {
        if ($mode === 'single') {
            $first = reset($saleData);
            $singleSaleId = $first['id'] ?? null;
            if ($singleSaleId && isset($saleData[$singleSaleId])) {
                if (abs($amountMap[$singleSaleId] - $saleData[$singleSaleId]['balance']) < 0.01) {
                    return $saleData[$singleSaleId]['invoice_no'] ?? ('PAY-' . date('YmdHis') . '-' . $agentId);
                }
            }
        }

        return 'PAYBULK-' . date('YmdHis') . '-' . $agentId;
    }

    /**
     * Generate unique order ID if base already exists in gateway logs
     *
     * @param string $baseOrderId
     * @return string
     */
    protected function generateUniqueOrderId(string $baseOrderId): string
    {
        $orderId = $baseOrderId;
        while ($this->salesGatewayLogModel->invoiceExists($orderId)) {
            $orderId = $baseOrderId . '-' . rand(1000, 9999);
        }
        return $orderId;
    }

    /**
     * Get payment status from gateway API (GET request)
     *
     * @param string $invoiceNo
     * @return array|null
     */
    protected function getPaymentStatusFromGateway(string $invoiceNo): ?array
    {
        try {
            $client = \Config\Services::curlrequest();
            $apiUrl = 'https://dev.osu.biz.id/mig/esb/v1/api/payments/' . urlencode($invoiceNo);

            log_message('info', 'Agent\Paylater::getPaymentStatusFromGateway - Fetching payment status for: ' . $invoiceNo);

            $response = $client->request('GET', $apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'x-api-key' => 'Lmp1xKoggDE4FH2SKk/d/hqRiF+uxyAZOtO/piLOdox1F0OPr/RyLbhH0JyzNJY2zTI9uEEG4P2Hgeh/i8fiD7ZjsMTEWJXgx8Zgdp74nAOLtel/zi9Z611c+GG4Ra0nMx5K2UjOeZvWFyfXDOuILmu4zYL+MyyW8uSGYO8ug9a17HS6tlmzg7PkdEEb2XzNQ84ahKTRxFTTrxJiFGa34FO0rzLjeNGTV5KihVwUkZjL67DrfiSZweUsKX8NNHgxHy242KPcRWcJ5/sLH/Klus9LRfx9pC3F4gzNr3k1VvoAP5Kv9DTP6IGOZshgDu8WnUAcsvDJG4wtpkZgvYBoUg=='
                ],
                'timeout' => 30,
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $responseData = json_decode($body, true);

            log_message('info', 'Agent\Paylater::getPaymentStatusFromGateway - Response Status: ' . $statusCode);
            log_message('info', 'Agent\Paylater::getPaymentStatusFromGateway - Response Body: ' . $body);

            if ($statusCode >= 200 && $statusCode < 300 && $responseData) {
                return $responseData;
            } else {
                $errorMsg = 'Payment gateway returned error';
                if (isset($responseData['message'])) {
                    $errorMsg = $responseData['message'];
                } elseif (!empty($body)) {
                    $errorMsg = 'Response: ' . $body;
                }
                log_message('error', 'Agent\Paylater::getPaymentStatusFromGateway - API Error: ' . $errorMsg . ' | Status: ' . $statusCode);
                return null;
            }
        } catch (\Exception $e) {
            log_message('error', 'Agent\Paylater::getPaymentStatusFromGateway error: ' . $e->getMessage());
            log_message('error', 'Agent\Paylater::getPaymentStatusFromGateway trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Get DataTables data for paylater transactions
     * Filters by current logged-in agent (permission-based)
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

            // Check if user is admin (has read_all or update_all permission)
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

            $db = \Config\Database::connect();
            
            // Build base query with joins
            $builder = $db->table('agent_paylater')
                ->select('agent_paylater.*, 
                    agent.name as agent_name,
                    sales.invoice_no as sales_invoice_no')
                ->join('agent', 'agent.id = agent_paylater.agent_id', 'left')
                ->join('sales', 'sales.id = agent_paylater.sale_id', 'left');

            // Apply agent filter only for non-admin users
            if (!$isAdmin && !empty($agentIds)) {
                $builder->whereIn('agent_paylater.agent_id', $agentIds);
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

            // Count total records
            $totalRecordsBuilder = $db->table('agent_paylater');
            if (!$isAdmin && !empty($agentIds)) {
                $totalRecordsBuilder->whereIn('agent_paylater.agent_id', $agentIds);
            }
            $totalRecords = $totalRecordsBuilder->countAllResults();

            // Apply search filter
            $totalFiltered = $totalRecords;
            if (!empty($searchValue)) {
                $builder->groupStart()
                      ->like('agent_paylater.description', $searchValue)
                      ->orLike('agent_paylater.reference_code', $searchValue)
                      ->orLike('agent.name', $searchValue)
                      ->orLike('sales.invoice_no', $searchValue)
                      ->groupEnd();

                // Clone query for the count
                $countBuilder = $db->table('agent_paylater')
                    ->select('agent_paylater.id')
                    ->join('agent', 'agent.id = agent_paylater.agent_id', 'left')
                    ->join('sales', 'sales.id = agent_paylater.sale_id', 'left');
                
                if (!$isAdmin && !empty($agentIds)) {
                    $countBuilder->whereIn('agent_paylater.agent_id', $agentIds);
                }
                
                $countBuilder->groupStart()
                           ->like('agent_paylater.description', $searchValue)
                           ->orLike('agent_paylater.reference_code', $searchValue)
                           ->orLike('agent.name', $searchValue)
                           ->orLike('sales.invoice_no', $searchValue)
                           ->groupEnd();

                $totalFiltered = $countBuilder->countAllResults();
            }

            $data = $builder->orderBy('agent_paylater.created_at', 'DESC')
                          ->limit($length, $start)
                          ->get()
                          ->getResultArray();

            // Format for DataTables
            $result = $this->formatPaylaterDataTablesData($data, $start);

            return $this->response->setJSON([
                'draw'            => $draw,
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data'            => $result,
            ]);
        } catch (\Throwable $e) {
            log_message(
                'error',
                'Agent\Paylater::getDataDT error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString()
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
     * Format data for DataTables
     * Columns: No | No Nota | Transaksi | Total | Tipe | Tanggal
     * 
     * @param array $data
     * @param int $start
     * @return array
     */
    protected function formatPaylaterDataTablesData(array $data, int $start): array
    {
        $result = [];
        $no = $start + 1;

        // Mutation type labels
        $mutationTypeLabels = [
            '1' => 'Pembelian',
            '2' => 'Pembayaran',
            '3' => 'Penyesuaian'
        ];

        foreach ($data as $row) {
            // No Nota: reference_code or id if reference_code is null
            $noNota = !empty($row['reference_code']) ? $row['reference_code'] : $row['id'];
            
            // Transaksi: description field
            $transaksi = $row['description'] ?? '-';
            
            // Total: amount formatted
            $total = format_angka((float) ($row['amount'] ?? 0), 2);
            
            // Tipe: mutation_type display
            $mutationType = $row['mutation_type'] ?? '1';
            $tipe = $mutationTypeLabels[$mutationType] ?? 'Unknown';
            
            // Tanggal: created_at formatted
            $tanggal = tgl_indo8($row['created_at'] ?? '');

            $result[] = [
                'ignore_search_urut'    => $no,
                'no_nota'               => esc($noNota),
                'transaksi'              => esc($transaksi),
                'total'                  => $total,
                'tipe'                   => esc($tipe),
                'created_at'             => $tanggal,
                'ignore_search_action'   => '<a href="' . $this->config->baseURL . 'agent/paylater/' . $row['id'] . '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>',
            ];

            $no++;
        }

        return $result;
    }
}

