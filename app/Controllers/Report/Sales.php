<?php

/**
 * Report Sales Controller
 * 
 * Handles sales report with filtering capabilities
 * 
 * @package    App\Controllers\Report
 * @author     Mikhael Felian Waskito <mikhaelfelian@gmail.com>
 * @copyright  2025
 * @license    MIT
 * @version    1.0.0
 * @since      2025-11-19
 */

namespace App\Controllers\Report;

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
        $this->salesDetailModel = new SalesDetailModel();
        $this->salesFeeModel = new SalesFeeModel();
        $this->feeTypeModel = new FeeTypeModel();
        $this->itemModel = new ItemModel();
        $this->itemSnModel = new ItemSnModel();
        $this->agentModel = new AgentModel();
        $this->customerModel = new CustomerModel();
        $this->userRoleAgentModel = new UserRoleAgentModel();
        
        // Add DataTables Buttons extension for Excel and PDF export
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/JSZip/jszip.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/pdfmake/pdfmake.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/pdfmake/vfs_fonts.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/buttons.html5.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/buttons.print.min.js');
        $this->addStyle($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css');
        
        // Add flatpickr for date range picker
        $this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
        $this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
    }
    
    /**
     * Display sales report list page
     * 
     * @return void
     */
    public function index(): void
    {
        // Get all active agents for dropdown
        $agents = $this->agentModel
            ->select('agent.id, agent.code, agent.name')
            ->where('agent.is_active', '1')
            ->orderBy('agent.name', 'ASC')
            ->findAll();

        $this->data = array_merge($this->data, [
            'title'         => 'Laporan Penjualan',
            'currentModule' => $this->currentModule,
            'config'        => $this->config,
            'msg'           => $this->session->getFlashdata('message'),
            'agents'        => $agents,
        ]);

        $this->data['breadcrumb'] = [
            'Home'       => $this->config->baseURL,
            'Laporan'    => $this->config->baseURL.'report/sales',
            'Penjualan'  => '', // Current page, no link
        ];

        $this->view('report/sales-result', $this->data);
    }

    /**
     * Show sales detail
     * 
     * @param int $id Sale ID
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function detail(int $id)
    {
        if ($id <= 0) {
            return redirect()->to('report/sales')->with('message', [
                'status' => 'error',
                'message' => 'ID penjualan tidak valid.'
            ]);
        }

        try {
            $sale = $this->model->getSalesWithRelations($id);
            
            if (!$sale) {
                return redirect()->to('report/sales')->with('message', [
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

            $this->data['title'] = 'Detail Penjualan';
            $this->data['currentModule'] = $this->currentModule;
            $this->data['config'] = $this->config;
            $this->data['sale'] = $sale;
            $this->data['items'] = $items;
            $this->data['fees'] = $fees;
            $this->data['feeTypes'] = $feeTypes;
            $this->data['isAgent'] = false;
            $this->data['payment'] = $paymentInfo;
            $this->data['gatewayResponse'] = $gatewayResponse;

            $this->data['breadcrumb'] = [
                'Home'             => $this->config->baseURL,
                'Laporan Penjualan' => $this->config->baseURL.'report/sales',
                'Detail'          => '', // Current page, no link
            ];

            $this->view('sales/sales-detail', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Report\Sales::detail error: ' . $e->getMessage());
            return redirect()->to('report/sales')->with('message', [
                'status' => 'error',
                'message' => 'Gagal memuat detail penjualan.'
            ]);
        }
    }

    /**
     * Get DataTables data for sales report
     * Supports filters: date range, date, agent, platform (payment_status), channel
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

            // Get filter values
            $dateRange = $this->request->getPost('date_range') ?? $this->request->getGet('date_range') ?? '';
            $agentId = $this->request->getPost('agent_id') ?? $this->request->getGet('agent_id') ?? '';
            $platform = $this->request->getPost('platform') ?? $this->request->getGet('platform') ?? '';
            $channel = $this->request->getPost('channel') ?? $this->request->getGet('channel') ?? '';

            $db = \Config\Database::connect();
            
            // Build base query
            $query = $this->buildSalesQuery();

            // Apply date range filter
            // Parse date_range from flatpickr format: "YYYY-MM-DD to YYYY-MM-DD"
            if (!empty($dateRange)) {
                $dateParts = preg_split('/\s+to\s+/i', trim($dateRange));
                if (count($dateParts) === 2) {
                    $tanggalRentangStart = trim($dateParts[0]);
                    $tanggalRentangEnd = trim($dateParts[1]);
                    if (!empty($tanggalRentangStart) && !empty($tanggalRentangEnd)) {
                        $query->where('DATE(sales.created_at) >=', $tanggalRentangStart)
                              ->where('DATE(sales.created_at) <=', $tanggalRentangEnd);
                    }
                }
            }

            // Agent filter
            if (!empty($agentId) && $agentId > 0) {
                $query->where('sales.warehouse_id', (int)$agentId);
            }

            // Platform filter (payment_status)
            if (!empty($platform)) {
                $query->where('sales.payment_status', $platform);
            }

            // Channel filter (sale_channel)
            if (!empty($channel)) {
                $query->where('sales.sale_channel', $channel);
            }

            // Count total records with filters (rebuild query for count)
            $countQuery = $this->buildSalesQuery();
            
            // Re-apply date range filter for count
            if (!empty($dateRange)) {
                $dateParts = preg_split('/\s+to\s+/i', trim($dateRange));
                if (count($dateParts) === 2) {
                    $tanggalRentangStart = trim($dateParts[0]);
                    $tanggalRentangEnd = trim($dateParts[1]);
                    if (!empty($tanggalRentangStart) && !empty($tanggalRentangEnd)) {
                        $countQuery->where('DATE(sales.created_at) >=', $tanggalRentangStart)
                                  ->where('DATE(sales.created_at) <=', $tanggalRentangEnd);
                    }
                }
            }
            if (!empty($agentId) && $agentId > 0) {
                $countQuery->where('sales.warehouse_id', (int)$agentId);
            }
            if (!empty($platform)) {
                $countQuery->where('sales.payment_status', $platform);
            }
            if (!empty($channel)) {
                $countQuery->where('sales.sale_channel', $channel);
            }
            
            $totalRecords = $countQuery->countAllResults(false);

            // Apply search filter if present
            $totalFiltered = $totalRecords;
            if (!empty($searchValue)) {
                $query->groupStart()
                      ->like('sales.invoice_no', $searchValue)
                      ->orLike('customer.name', $searchValue)
                      ->orLike('user.nama', $searchValue)
                      ->orLike('agent.name', $searchValue)
                      ->groupEnd();

                // Rebuild query for filtered count
                $countQuery = $this->buildSalesQuery();
                
                // Re-apply filters
                if (!empty($tanggal)) {
                    $countQuery->where('DATE(sales.created_at)', $tanggal);
                } elseif (!empty($tanggalRentangStart) && !empty($tanggalRentangEnd)) {
                    $countQuery->where('DATE(sales.created_at) >=', $tanggalRentangStart)
                              ->where('DATE(sales.created_at) <=', $tanggalRentangEnd);
                }
                if (!empty($agentId) && $agentId > 0) {
                    $countQuery->where('sales.warehouse_id', (int)$agentId);
                }
                if (!empty($platform)) {
                    $countQuery->where('sales.payment_status', $platform);
                }
                if (!empty($channel)) {
                    $countQuery->where('sales.sale_channel', $channel);
                }
                
                $countQuery->groupStart()
                           ->like('sales.invoice_no', $searchValue)
                           ->orLike('customer.name', $searchValue)
                           ->orLike('user.nama', $searchValue)
                           ->orLike('agent.name', $searchValue)
                           ->groupEnd();

                $totalFiltered = $countQuery->countAllResults(false);
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
                'Report\Sales::getDataDT error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString()
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
            agent.name as agent_name,
            COALESCE(sales.balance_due, sales.grand_total - COALESCE(sales.total_payment, 0)) as balance_due')
            ->join('customer', 'customer.id = sales.customer_id', 'left')
            ->join('user', 'user.id_user = sales.user_id', 'left')
            ->join('agent', 'agent.id = sales.warehouse_id', 'left');
    }

    /**
     * Format data for DataTables
     * 
     * @param array $data Raw data from database
     * @param int $start Starting index for row numbering
     * @return array Formatted data for DataTables
     */
    protected function formatDataTablesData(array $data, int $start): array
    {
        $result = [];
        $no = $start + 1;

        foreach ($data as $row) {
            $result[] = [
                'ignore_search_urut'    => $no++,
                'invoice_no'            => esc($row['invoice_no'] ?? '-'),
                'customer_name'         => esc($row['customer_name'] ?? '-'),
                'agent_name'            => esc($row['agent_name'] ?? '-'),
                'grand_total'           => format_angka($row['grand_total'] ?? 0, 2),
                'payment_status'        => $this->getPaymentStatusBadge($row['payment_status'] ?? '0', $row['id'] ?? null),
                'sale_channel'          => $this->getChannelBadge($row['sale_channel'] ?? '1'),
                'created_at'            => tgl_indo8($row['created_at'] ?? ''),
                'ignore_search_action'  => '<a href="' . $this->config->baseURL . 'report/sales/' . ($row['id'] ?? '') . '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>'
            ];
        }

        return $result;
    }

    /**
     * Get payment status badge HTML
     * 
     * @param string $status Payment status
     * @param int|null $saleId Sale ID
     * @return string HTML badge
     */
    protected function getPaymentStatusBadge(string $status, ?int $saleId = null): string
    {
        $badges = [
            '0' => '<span class="badge bg-warning">Unpaid</span>',
            '1' => '<span class="badge bg-info">Partial</span>',
            '2' => '<span class="badge bg-success">Paid</span>',
            '3' => '<span class="badge bg-secondary">Paylater</span>',
        ];
        
        return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get channel badge HTML
     * 
     * @param string $channel Sale channel
     * @return string HTML badge
     */
    protected function getChannelBadge(string $channel): string
    {
        $badges = [
            '1' => '<span class="badge bg-primary">Offline</span>',
            '2' => '<span class="badge bg-info">Online</span>',
        ];
        
        return $badges[$channel] ?? '<span class="badge bg-secondary">Unknown</span>';
    }
}

