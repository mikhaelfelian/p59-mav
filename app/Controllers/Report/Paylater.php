<?php

/**
 * Report Paylater Controller
 * 
 * Handles paylater report with filtering capabilities
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
use App\Models\AgentModel;
use App\Models\CustomerModel;
use CodeIgniter\HTTP\ResponseInterface;

class Paylater extends BaseController
{
    protected $model;
    protected $agentModel;
    protected $customerModel;
    
    /**
     * Initialize models
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new SalesModel();
        $this->agentModel = new AgentModel();
        $this->customerModel = new CustomerModel();
        
        // Add DataTables Buttons extension for Excel and PDF export
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/JSZip/jszip.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/pdfmake/pdfmake.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/pdfmake/vfs_fonts.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/buttons.html5.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/buttons.print.min.js');
        $this->addStyle($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css');
    }
    
    /**
     * Display paylater report list page
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
            'title'         => 'Laporan Paylater',
            'currentModule' => $this->currentModule,
            'config'        => $this->config,
            'msg'           => $this->session->getFlashdata('message'),
            'agents'        => $agents,
        ]);

        $this->data['breadcrumb'] = [
            'Home'       => $this->config->baseURL,
            'Laporan'    => $this->config->baseURL.'report/paylater',
            'Paylater'   => '', // Current page, no link
        ];

        $this->view('report/paylater-result', $this->data);
    }

    /**
     * Show paylater sales detail
     * 
     * @param int $id Sale ID
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function detail(int $id)
    {
        if ($id <= 0) {
            return redirect()->to('report/paylater')->with('message', [
                'status' => 'error',
                'message' => 'ID penjualan tidak valid.'
            ]);
        }

        try {
            $sale = $this->model->getSalesWithRelations($id);
            
            if (!$sale) {
                return redirect()->to('report/paylater')->with('message', [
                    'status' => 'error',
                    'message' => 'Data penjualan tidak ditemukan.'
                ]);
            }

            // Verify it's a paylater sale
            if ($sale->payment_status !== '3') {
                return redirect()->to('report/paylater')->with('message', [
                    'status' => 'error',
                    'message' => 'Data ini bukan penjualan paylater.'
                ]);
            }

            // Get items from sales_detail table
            $salesDetailModel = new \App\Models\SalesDetailModel();
            $items = $salesDetailModel->getDetailsBySale($id);

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
            $salesFeeModel = new \App\Models\SalesFeeModel();
            $fees = $salesFeeModel->getFeesBySale($id);

            // Get payment information
            $salesPaymentsModel = new \App\Models\SalesPaymentsModel();
            $payments = $salesPaymentsModel->getPaymentsBySale($id);
            $paymentInfo = null;
            $gatewayResponse = null;
            
            if (!empty($payments)) {
                $payment = $payments[0];
                $paymentInfo = $payment;
                
                if (!empty($payment['response'])) {
                    $gatewayResponse = json_decode($payment['response'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $gatewayResponse = null;
                    }
                }
            }

            $this->data['title'] = 'Detail Penjualan Paylater';
            $this->data['currentModule'] = $this->currentModule;
            $this->data['config'] = $this->config;
            $this->data['sale'] = $sale;
            $this->data['items'] = $items;
            $this->data['fees'] = $fees;
            $this->data['isAgent'] = false;
            $this->data['payment'] = $paymentInfo;
            $this->data['gatewayResponse'] = $gatewayResponse;

            $this->data['breadcrumb'] = [
                'Home'       => $this->config->baseURL,
                'Laporan'    => $this->config->baseURL.'report/paylater',
                'Paylater'   => $this->config->baseURL.'report/paylater',
                'Detail'     => '', // Current page, no link
            ];

            $this->view('sales/agent/sales-detail-paylater', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Report\Paylater::detail error: ' . $e->getMessage());
            return redirect()->to('report/paylater')->with('message', [
                'status' => 'error',
                'message' => 'Gagal memuat detail penjualan.'
            ]);
        }
    }

    /**
     * Get DataTables data for paylater report
     * Supports filters: date range, date, agent
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
            $tanggalRentangStart = $this->request->getPost('tanggal_rentang_start') ?? $this->request->getGet('tanggal_rentang_start') ?? '';
            $tanggalRentangEnd = $this->request->getPost('tanggal_rentang_end') ?? $this->request->getGet('tanggal_rentang_end') ?? '';
            $tanggal = $this->request->getPost('tanggal') ?? $this->request->getGet('tanggal') ?? '';
            $agentId = $this->request->getPost('agent_id') ?? $this->request->getGet('agent_id') ?? '';

            $db = \Config\Database::connect();
            
            // Build base query
            $query = $this->buildPaylaterQuery();

            // Apply filters
            // Date filter: single date takes precedence over date range
            if (!empty($tanggal)) {
                $query->where('DATE(sales.created_at)', $tanggal);
            } elseif (!empty($tanggalRentangStart) && !empty($tanggalRentangEnd)) {
                $query->where('DATE(sales.created_at) >=', $tanggalRentangStart)
                      ->where('DATE(sales.created_at) <=', $tanggalRentangEnd);
            }

            // Agent filter
            if (!empty($agentId) && $agentId > 0) {
                $query->where('sales.warehouse_id', (int)$agentId);
            }

            // Count total records with filters (rebuild query for count)
            $countQuery = $this->buildPaylaterQuery();
            
            // Re-apply filters for count
            if (!empty($tanggal)) {
                $countQuery->where('DATE(sales.created_at)', $tanggal);
            } elseif (!empty($tanggalRentangStart) && !empty($tanggalRentangEnd)) {
                $countQuery->where('DATE(sales.created_at) >=', $tanggalRentangStart)
                          ->where('DATE(sales.created_at) <=', $tanggalRentangEnd);
            }
            if (!empty($agentId) && $agentId > 0) {
                $countQuery->where('sales.warehouse_id', (int)$agentId);
            }
            
            $totalRecords = $countQuery->countAllResults(false);

            // Apply search filter if present
            $totalFiltered = $totalRecords;
            if (!empty($searchValue)) {
                $query->groupStart()
                      ->like('sales.invoice_no', $searchValue)
                      ->orLike('customer.name', $searchValue)
                      ->orLike('agent.name', $searchValue)
                      ->groupEnd();

                // Rebuild query for filtered count
                $countQuery = $this->buildPaylaterQuery();
                
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
                
                $countQuery->groupStart()
                           ->like('sales.invoice_no', $searchValue)
                           ->orLike('customer.name', $searchValue)
                           ->orLike('agent.name', $searchValue)
                           ->groupEnd();

                $totalFiltered = $countQuery->countAllResults(false);
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
                'Report\Paylater::getDataDT error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString()
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
            agent.name as agent_name,
            COALESCE((
                SELECT agent_paylater.description 
                FROM agent_paylater 
                WHERE agent_paylater.sale_id = sales.id 
                AND agent_paylater.mutation_type = "1" 
                LIMIT 1
            ), "") as paylater_description')
            ->join('customer', 'customer.id = sales.customer_id', 'left')
            ->join('user', 'user.id_user = sales.user_id', 'left')
            ->join('agent', 'agent.id = sales.warehouse_id', 'left')
            ->where('sales.payment_status', '3');
    }

    /**
     * Format data for DataTables
     * Columns: No | No Nota | Transaksi | Total | Tipe | Tanggal
     * 
     * @param array $data Raw data from database
     * @param int $start Starting index for row numbering
     * @return array Formatted data for DataTables
     */
    protected function formatPaylaterDataTablesData(array $data, int $start): array
    {
        $result = [];
        $no = $start + 1;

        foreach ($data as $row) {
            // No Nota: invoice_no
            $noNota = $row['invoice_no'] ?? '-';
            
            // Transaksi: paylater_description or customer_name
            $transaksi = !empty($row['paylater_description']) 
                ? $row['paylater_description'] 
                : ($row['customer_name'] ?? '-');
            
            // If transaksi contains "TRX" pattern, format it
            if (empty($row['paylater_description']) && !empty($row['agent_name'])) {
                $transaksi = 'TRX ' . $row['id'] . ' - ' . $row['agent_name'];
            }
            
            // Total: grand_total formatted
            $total = format_angka((float) ($row['grand_total'] ?? 0), 2);
            
            // Tipe: Always "Paylater" for sales with payment_status='3'
            $tipe = 'Paylater';
            
            // Tanggal: created_at formatted
            $tanggal = tgl_indo8($row['created_at'] ?? '');

            $result[] = [
                'ignore_search_urut'    => $no,
                'no_nota'               => esc($noNota),
                'transaksi'             => esc($transaksi),
                'total'                 => $total,
                'tipe'                  => esc($tipe),
                'created_at'            => $tanggal,
                'ignore_search_action'  => '<a href="' . $this->config->baseURL . 'report/paylater/' . $row['id'] . '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>',
            ];

            $no++;
        }

        return $result;
    }
}

