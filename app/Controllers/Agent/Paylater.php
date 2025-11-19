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
use CodeIgniter\HTTP\ResponseInterface;

class Paylater extends BaseController
{
    protected $model;
    protected $agentModel;
    protected $salesModel;
    protected $userRoleAgentModel;
    
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
            
            // Get sales info if sales_id exists
            $sale = null;
            if (!empty($transaction->sales_id)) {
                $sale = $this->salesModel->find($transaction->sales_id);
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
     * Single payment form route (placeholder)
     * 
     * @param int $id Paylater transaction ID
     * @return void
     */
    public function pay(int $id): void
    {
        // Placeholder - will be implemented in next order
        $this->data = array_merge($this->data, [
            'title'         => 'Form Pembayaran',
            'currentModule' => $this->currentModule,
            'config'        => $this->config,
        ]);

        $this->data['breadcrumb'] = [
            'Home'         => $this->config->baseURL,
            'Data Paylater' => $this->config->baseURL.'agent/paylater',
            'Form Pembayaran' => '',
        ];

        // Placeholder view - will be created in next order
        echo "Payment form for transaction ID: {$id} - To be implemented";
    }

    /**
     * Bulk payment form route (placeholder)
     * 
     * @param int $id Agent ID
     * @return void
     */
    public function payBulk(int $id): void
    {
        // Placeholder - will be implemented in next order
        $this->data = array_merge($this->data, [
            'title'         => 'Form Pembayaran Bulk',
            'currentModule' => $this->currentModule,
            'config'        => $this->config,
        ]);

        $this->data['breadcrumb'] = [
            'Home'         => $this->config->baseURL,
            'Data Paylater' => $this->config->baseURL.'agent/paylater',
            'Form Pembayaran Bulk' => '',
        ];

        // Placeholder view - will be created in next order
        echo "Bulk payment form for agent ID: {$id} - To be implemented";
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
                ->join('sales', 'sales.id = agent_paylater.sales_id', 'left');

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
                    ->join('sales', 'sales.id = agent_paylater.sales_id', 'left');
                
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
            $tanggal = !empty($row['created_at'])
                ? date('d/m/Y H:i', strtotime($row['created_at']))
                : '-';

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

