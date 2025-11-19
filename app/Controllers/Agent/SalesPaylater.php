<?php

/**
 * Agent SalesPaylater Controller
 * 
 * Handles paylater sales (payment_status = '3')
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
use CodeIgniter\HTTP\ResponseInterface;

class SalesPaylater extends BaseController
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
        $this->platformModel = new PlatformModel();
        $this->salesPaymentLogModel = new SalesPaymentLogModel();
        $this->salesGatewayLogModel = new SalesGatewayLogModel();
    }
    
    /**
     * Display paylater sales list page
     * 
     * @return void
     */
    public function index(): void
    {        
        // Pass permission data to view
        $isAdmin = $this->hasPermission('read_all');
        $this->data['read_all'] = $isAdmin;

        $this->data = array_merge($this->data, [
            'title'         => 'Data Pembayaran Paylater',
            'currentModule' => $this->currentModule,
            'config'        => $this->config,
            'msg'           => $this->session->getFlashdata('message'),
        ]);

        $this->data['breadcrumb'] = [
            'Home'                 => $this->config->baseURL,
            'Data Pembayaran Paylater' => '', // Current page, no link
        ];

        $this->view('sales/agent/sales-result-paylater', $this->data);
    }

    /**
     * Show paylater sale detail
     * 
     * @param int $id Sale ID
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function detail(int $id)
    {
        if ($id <= 0) {
            return redirect()->to('sales-paylater')->with('message', [
                'status' => 'error',
                'message' => 'ID penjualan tidak valid.'
            ]);
        }

        try {
            $sale = $this->model->getSalesWithRelations($id);
            
            if (!$sale) {
                return redirect()->to('sales-paylater')->with('message', [
                    'status' => 'error',
                    'message' => 'Data penjualan tidak ditemukan.'
                ]);
            }

            // Verify payment_status is '3' (paylater)
            if ($sale['payment_status'] !== '3') {
                return redirect()->to('sales-paylater')->with('message', [
                    'status' => 'error',
                    'message' => 'Data ini bukan pembayaran paylater.'
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

            $this->data['title'] = 'Detail Pembayaran Paylater';
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
                'Pembayaran Paylater' => $this->config->baseURL.'agent/sales-paylater',
                'Detail'             => '', // Current page, no link
            ];

            $this->view('sales/agent/sales-detail-paylater', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'SalesPaylater::detail error: ' . $e->getMessage());
            return redirect()->to('sales-paylater')->with('message', [
                'status' => 'error',
                'message' => 'Gagal memuat detail penjualan.'
            ]);
        }
    }

    /**
     * Get DataTables data for paylater sales
     * Filters by payment_status = '3'
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
            
            // Count total records with payment_status = '3' filter
            $totalRecords = $db->table('sales')
                ->where('payment_status', '3')
                ->countAllResults();

            // Main query builder with joins and payment_status filter
            $query = $this->buildPaylaterQuery();

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
                $countQuery = $this->buildPaylaterQuery();
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
                'Agent\SalesPaylater::getDataDT error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString()
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
     * Build base paylater sales query with joins
     * Filters by payment_status = '3'
     * 
     * @return \CodeIgniter\Database\BaseBuilder
     */
    protected function buildPaylaterQuery()
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
            ->where('sales.payment_status', '3');
    }

    /**
     * Format data for DataTables (paylater specific)
     * Columns: No | No Nota | Pelanggan | Total | Tanggal | Aksi
     * 
     * @param array $data
     * @param int $start
     * @return array
     */
    protected function formatPaylaterDataTablesData(array $data, int $start): array
    {
        $result = [];
        $no = $start + 1;

        foreach ($data as $row) {
            $actionButtons = '<div class="btn-group" role="group">';
            $actionButtons .= '<a href="' . $this->config->baseURL . 'agent/sales-paylater/' . $row['id'] . '" ';
            $actionButtons .= 'class="btn btn-sm btn-info" title="Detail">';
            $actionButtons .= '<i class="fas fa-eye"></i></a>';
            
            // Add "Bayar" button (button only, no functionality yet)
            $actionButtons .= '<a href="' . $this->config->baseURL . 'agent/paylater/pay/' . $row['id'] . '" ';
            $actionButtons .= 'class="btn btn-sm btn-success btn-bayar" title="Bayar">';
            $actionButtons .= 'Bayar</a>';
            
            $actionButtons .= '</div>';

            $result[] = [
                'ignore_search_urut'    => $no,
                'invoice_no'            => esc($row['invoice_no'] ?? ''),
                'customer_name'         => esc($row['customer_name'] ?? '-'),
                'grand_total'           => format_angka((float) ($row['grand_total'] ?? 0), 2),
                'created_at'            => tgl_indo8($row['created_at'] ?? ''),
                'ignore_search_action'  => $actionButtons,
            ];

            $no++;
        }

        return $result;
    }
}

