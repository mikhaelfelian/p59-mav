<?php

/**
 * Sales Confirm Controller (Agent)
 * 
 * Handles admin verification and confirmation of agent orders (serial number activation)
 * 
 * @package    App\Controllers\Agent
 * @author     Mikhael Felian Waskito <mikhaelfelian@gmail.com>
 * @copyright  2025
 * @license    MIT
 * @version    1.0.0
 * @since      2025-11-10
 */

namespace App\Controllers\Agent;

use App\Models\SalesModel;
use App\Models\SalesItemSnModel;
use App\Models\SalesPaymentsModel;
use App\Models\SalesDetailModel;
use App\Models\ItemModel;
use App\Models\ItemSnModel;
use CodeIgniter\HTTP\ResponseInterface;

class SalesConfirm extends \App\Controllers\BaseController
{
    /**
     * Model instances
     */
    protected $model;
    protected $salesItemSnModel;
    protected $salesPaymentsModel;
    protected $salesDetailModel;
    protected $itemModel;
    protected $itemSnModel;

    /**
     * Sales channel constants
     */
    protected const CHANNEL_ONLINE = '2';

    /**
     * Initialize models and dependencies
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->model = new SalesModel();
        $this->salesItemSnModel = new SalesItemSnModel();
        $this->salesPaymentsModel = new SalesPaymentsModel();
        $this->salesDetailModel = new SalesDetailModel();
        $this->itemModel = new ItemModel();
        $this->itemSnModel = new ItemSnModel();
    }

    /**
     * List pending agent orders that need SN confirmation
     * 
     * @return void
     */
    public function index(): void
    {
        helper('angka');
        $this->data['title'] = 'Verifikasi Order Agent';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['msg'] = $this->session->getFlashdata('message');
        $this->data['breadcrumb'] = [
            'Home' => $this->config->baseURL,
            'Penjualan' => $this->config->baseURL . 'agent/sales',
            'Verifikasi SN' => ''
        ];
        
        $this->view('sales/confirm-sn-list', $this->data);

        // redirect()->to(base_url('agent/sales'));
    }

    /**
     * Get DataTables data for pending agent orders (need SN confirmation)
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
            
            // Get sales with online channel that have at least one pending serial number
            // First, get distinct sale IDs that have pending SNs
            $saleIdsWithPendingSN = $db->table('sales')
                ->select('sales.id')
                ->join('sales_detail', 'sales_detail.sale_id = sales.id', 'inner')
                ->join('sales_item_sn', 'sales_item_sn.sales_item_id = sales_detail.id', 'inner')
                ->join('item_sn', 'item_sn.id = sales_item_sn.item_sn_id', 'inner')
                ->where('sales.sale_channel', self::CHANNEL_ONLINE)
                ->where('item_sn.is_sell', '0')
                ->groupBy('sales.id')
                ->get()
                ->getResultArray();
            
            $saleIds = array_column($saleIdsWithPendingSN, 'id');
            
            if (empty($saleIds)) {
                // No sales with pending SNs
                return $this->response->setJSON([
                    'draw'            => $draw,
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => [],
                ]);
            }
            
            // Now get the full sale details with pending SN counts
            $builder = $db->table('sales')
                ->select('sales.*, 
                    customer.name as customer_name,
                    user.nama as user_name,
                    agent.name as agent_name,
                    (SELECT COUNT(DISTINCT sales_item_sn.id) 
                     FROM sales_detail 
                     INNER JOIN sales_item_sn ON sales_item_sn.sales_item_id = sales_detail.id
                     INNER JOIN item_sn ON item_sn.id = sales_item_sn.item_sn_id
                     WHERE sales_detail.sale_id = sales.id 
                       AND item_sn.is_sell = \'0\') as pending_sn_count')
                ->join('customer', 'customer.id = sales.customer_id', 'left')
                ->join('user', 'user.id_user = sales.user_id', 'left')
                ->join('agent', 'agent.id = sales.warehouse_id', 'left')
                ->where('sales.sale_channel', self::CHANNEL_ONLINE)
                ->whereIn('sales.id', $saleIds);

            // Apply search filter
            if (!empty($searchValue)) {
                $builder->groupStart()
                      ->like('sales.invoice_no', $searchValue)
                      ->orLike('customer.name', $searchValue)
                      ->orLike('user.nama', $searchValue)
                      ->orLike('agent.name', $searchValue)
                      ->groupEnd();
            }

            // Get total count using subquery
            $totalRecordsQuery = $db->query("
                SELECT COUNT(DISTINCT sales.id) as total
                FROM sales
                INNER JOIN sales_detail ON sales_detail.sale_id = sales.id
                INNER JOIN sales_item_sn ON sales_item_sn.sales_item_id = sales_detail.id
                INNER JOIN item_sn ON item_sn.id = sales_item_sn.item_sn_id
                WHERE sales.sale_channel = '" . self::CHANNEL_ONLINE . "'
                  AND item_sn.is_sell = '0'
            ");
            $totalRecords = $totalRecordsQuery->getRow()->total ?? 0;

            // Get filtered count (same as total if no search, otherwise apply search)
            if (!empty($searchValue)) {
                $countQuery = clone $builder;
                $totalFiltered = $countQuery->countAllResults(false);
            } else {
                $totalFiltered = $totalRecords;
            }

            // Get data
            $data = $builder->orderBy('sales.created_at', 'DESC')
                          ->limit($length, $start)
                          ->get()
                          ->getResultArray();

            // Format for DataTables
            helper('angka');
            $result = [];
            $no = $start + 1;
            foreach ($data as $row) {
                $actionButtons = '<div class="btn-group" role="group">';
                $actionButtons .= '<a href="' . $this->config->baseURL . 'agent/sales/confirm/' . $row['id'] . '" ';
                $actionButtons .= 'class="btn btn-sm btn-primary" title="Verifikasi & Assign SN">';
                $actionButtons .= '<i class="fas fa-check-circle"></i> Verifikasi</a>';
                $actionButtons .= '</div>';

                $result[] = [
                    'ignore_search_urut'    => $no,
                    'invoice_no'            => esc($row['invoice_no'] ?? ''),
                    'customer_name'         => esc($row['customer_name'] ?? '-'),
                    'agent_name'            => esc($row['agent_name'] ?? '-'),
                    'grand_total'           => format_angka((float) ($row['grand_total'] ?? 0), 2),
                    'pending_sn_count'     => '<span class="badge bg-warning">' . ($row['pending_sn_count'] ?? 0) . ' SN</span>',
                    'created_at'            => !empty($row['created_at'])
                                                ? date('d/m/Y H:i', strtotime($row['created_at']))
                                                : '-',
                    'ignore_search_action'  => $actionButtons,
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
                'Agent\SalesConfirm::getDataDT error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString()
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
     * Show verification page for assigning serial numbers to agent order
     * 
     * @param int $id Sale ID
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function detail(int $id)
    {
        helper('angka');
        if ($id <= 0) {
            return redirect()->to('agent/sales/confirm')->with('message', [
                'status' => 'error',
                'message' => 'ID penjualan tidak valid.'
            ]);
        }

        try {
            // Get sale with relations
            $sale = $this->model->getSalesWithRelations($id);
            
            if (!$sale) {
                return redirect()->to('agent/sales/confirm')->with('message', [
                    'status' => 'error',
                    'message' => 'Data penjualan tidak ditemukan.'
                ]);
            }

            // Verify it's an online sale (agent order)
            if ($sale['sale_channel'] != self::CHANNEL_ONLINE) {
                return redirect()->to('agent/sales/confirm')->with('message', [
                    'status' => 'error',
                    'message' => 'Hanya penjualan online (agent) yang memerlukan verifikasi SN.'
                ]);
            }

            // Get items from sales_detail
            $items = $this->salesDetailModel->getDetailsBySale($id);

            // Get serial numbers for each item (both assigned and available)
            $itemsWithSN = [];
            foreach ($items as $item) {
                // Check if this item has any serial numbers in item_sn table
                $hasSerialNumbers = $this->itemSnModel
                    ->where('item_id', $item['item_id'])
                    ->countAllResults() > 0;

                // Get sales_item_sn records for this sales_detail (already assigned)
                $salesItemSns = $this->salesItemSnModel
                    ->select('sales_item_sn.*, item_sn.sn, item_sn.is_sell, item_sn.is_activated, item_sn.activated_at, item_sn.expired_at, item_sn.item_id, item_sn.agent_id')
                    ->join('item_sn', 'item_sn.id = sales_item_sn.item_sn_id', 'inner')
                    ->where('sales_item_sn.sales_item_id', $item['id'])
                    ->orderBy('sales_item_sn.is_receive', 'ASC')
                    ->orderBy('sales_item_sn.created_at', 'ASC')
                    ->findAll();

                // Filter only those with is_sell = '0' (not yet activated)
                $pendingSns = [];
                foreach ($salesItemSns as $salesItemSn) {
                    if (($salesItemSn['is_sell'] ?? '0') === '0') {
                        $pendingSns[] = $salesItemSn;
                    }
                }

                // Only fetch available serial numbers if the item has serial numbers
                $availableSnsArray = [];
                if ($hasSerialNumbers) {
                    // Get available unused serial numbers for this item
                    // Exclude serial numbers that are already assigned to this sales_item_id
                    $availableSns = $this->itemSnModel
                        ->select('item_sn.*')
                        ->where('item_sn.item_id', $item['item_id'])
                        ->where('item_sn.is_sell', '0')
                        ->join('sales_item_sn', 'sales_item_sn.item_sn_id = item_sn.id AND sales_item_sn.sales_item_id = ' . (int)$item['id'], 'left')
                        ->where('sales_item_sn.id IS NULL')
                        ->orderBy('item_sn.created_at', 'ASC')
                        ->findAll();
                    
                    // Additional filter: exclude SNs that have is_receive='1' in any sales_item_sn record
                    // This prevents assigning SNs that have already been received, regardless of which sales_item_id
                    $receivedSnIds = $this->salesItemSnModel
                        ->select('item_sn_id')
                        ->where('is_receive', '1')
                        ->findAll();
                    
                    $receivedSnIdArray = [];
                    foreach ($receivedSnIds as $receivedSn) {
                        $snId = is_object($receivedSn) ? ($receivedSn->item_sn_id ?? null) : ($receivedSn['item_sn_id'] ?? null);
                        if ($snId) {
                            $receivedSnIdArray[] = (int)$snId;
                        }
                    }
                    
                    // Filter out received SNs from available list
                    if (!empty($receivedSnIdArray)) {
                        $availableSns = array_filter($availableSns, function($sn) use ($receivedSnIdArray) {
                            $snId = is_object($sn) ? ($sn->id ?? null) : ($sn['id'] ?? null);
                            return $snId && !in_array((int)$snId, $receivedSnIdArray, true);
                        });
                        // Re-index array after filtering
                        $availableSns = array_values($availableSns);
                    }

                    // Convert objects to arrays properly
                    foreach ($availableSns as $sn) {
                        if (is_object($sn)) {
                            $availableSnsArray[] = [
                                'id' => $sn->id ?? null,
                                'sn' => $sn->sn ?? '',
                                'item_id' => $sn->item_id ?? null,
                                'is_sell' => $sn->is_sell ?? '0',
                                'is_activated' => $sn->is_activated ?? '0',
                                'created_at' => $sn->created_at ?? null,
                            ];
                        } else {
                            $availableSnsArray[] = $sn;
                        }
                    }
                }

                $item['pending_sns'] = $pendingSns;
                $item['available_sns'] = $availableSnsArray;
                $item['has_serial_numbers'] = $hasSerialNumbers;
                $item['needs_assignment'] = $hasSerialNumbers && (int)($item['qty'] ?? 1) > count($pendingSns);
                $itemsWithSN[] = $item;
            }

            // Get payment information
            $payments = $this->salesPaymentsModel->getPaymentsBySale($id);
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

            // Check if current user is agent (same logic as Agent\Sales::sn())
            $userPermission = is_array($this->userPermission) ? $this->userPermission : [];
            $isAdmin = key_exists('read_all', $userPermission) || key_exists('update_all', $userPermission);
            $isAgent = !$isAdmin; // If not admin, then agent

            $this->data['title'] = 'Verifikasi Order Agent';
            $this->data['currentModule'] = $this->currentModule;
            $this->data['config'] = $this->config;
            $this->data['sale'] = $sale;
            $this->data['items'] = $itemsWithSN;
            $this->data['payment'] = $paymentInfo;
            $this->data['gatewayResponse'] = $gatewayResponse;
            $this->data['agentId'] = $sale['warehouse_id'] ?? null; // Agent ID from sales.warehouse_id
            $this->data['isAgent'] = $isAgent; // Pass isAgent flag to view

            $this->data['breadcrumb'] = [
                'Home'                                   => $this->config->baseURL . 'agent/dashboard',
                ($isAdmin ? 'Penjualan' : 'Pembelian')   => $this->config->baseURL . 'agent/sales',
                'Assign SN'                      => '', // Current page, no link
            ];
            
            $this->view('sales/confirm-sn-detail', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Agent\SalesConfirm::detail error: ' . $e->getMessage());
            return redirect()->to('agent/sales/confirm')->with('message', [
                'status' => 'error',
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Handle SN verification and activation
     * 
     * @param int $id Sale ID
     * @return \CodeIgniter\HTTP\RedirectResponse|ResponseInterface
     */
    public function verify(int $id)
    {
        if ($id <= 0) {
            return redirect()->to('agent/sales/confirm')->with('message', [
                'status' => 'error',
                'message' => 'ID penjualan tidak valid.'
            ]);
        }

        $isAjax = $this->request->isAJAX();

        try {
            // Verify sale exists and is online
            $sale = $this->model->getSalesWithRelations($id);
            
            if (!$sale) {
                $message = 'Data penjualan tidak ditemukan.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->to('agent/sales/confirm')->with('message', ['status' => 'error', 'message' => $message]);
            }

            if ($sale['sale_channel'] != self::CHANNEL_ONLINE) {
                $message = 'Hanya penjualan online (agent) yang memerlukan verifikasi SN.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->to('agent/sales/confirm')->with('message', ['status' => 'error', 'message' => $message]);
            }

            // Get items with pending serial numbers
            $items = $this->salesDetailModel->getDetailsBySale($id);
            
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // Reuse shared models to avoid repeated instantiation
                $itemModel = $this->itemModel;
                $itemSnModel = $this->itemSnModel;

                // Disable validation during bulk updates to prevent partial data issues
                $itemSnModel->skipValidation(true);

                $activatedCount = 0;

                foreach ($items as $item) {
                    // Get pending serial numbers for this sales_detail
                    $salesItemSns = $this->salesItemSnModel
                        ->select('sales_item_sn.*, item_sn.sn, item_sn.is_sell, item_sn.item_id')
                        ->join('item_sn', 'item_sn.id = sales_item_sn.item_sn_id', 'inner')
                        ->where('sales_item_sn.sales_item_id', $item['id'])
                        ->where('item_sn.is_sell', '0')
                        ->findAll();

                    if (empty($salesItemSns)) {
                        continue;
                    }

                    // Get item record for warranty
                    $itemRecord = $itemModel->find((int)$item['item_id']);
                    $itemWarranty = 0;
                    if ($itemRecord) {
                        if (is_array($itemRecord)) {
                            $itemWarranty = isset($itemRecord['warranty']) ? (int)$itemRecord['warranty'] : 0;
                        } else {
                            $itemWarranty = isset($itemRecord->warranty) ? (int)$itemRecord->warranty : 0;
                        }
                    }

                    $activatedAt = date('Y-m-d H:i:s');

                    // Calculate expired_at if warranty exists
                    $expiredAt = null;
                    if ($itemWarranty > 0) {
                        $expiredAt = (new \DateTime($activatedAt))
                            ->modify('+' . $itemWarranty . ' months')
                            ->format('Y-m-d H:i:s');
                    }

                    // Activate each serial number
                    foreach ($salesItemSns as $salesItemSn) {
                        $itemSnId = (int)$salesItemSn['item_sn_id'];
                        
                        $updateData = [
                            'is_sell'      => '1',
                            'is_activated' => '1',
                            'activated_at'  => $activatedAt,
                        ];

                        if ($expiredAt) {
                            $updateData['expired_at'] = $expiredAt;
                        }

                        $updateSuccess = $itemSnModel->update($itemSnId, $updateData);

                        if ($updateSuccess === false) {
                            $dbError = $db->error();
                            $errorMessage = $dbError['message'] ?? 'Gagal memperbarui status serial number.';
                            throw new \RuntimeException($errorMessage);
                        }
                        
                        $activatedCount++;
                    }
                }

                // Restore validation state after updates
                $itemSnModel->skipValidation(false);

                // Update sales.status to '1' (completed) after SN confirmed and assigned
                if ($activatedCount > 0) {
                    $this->model->skipValidation(true);
                    $this->model->update($id, ['status' => '1']);
                    $this->model->skipValidation(false);
                }

                $db->transComplete();

                $dbError = $db->error();
                if ($db->transStatus() === false && (!empty($dbError['code']) || !empty($dbError['message']))) {
                    $errorMessage = $dbError['message'] ?? 'Transaksi gagal.';
                    throw new \RuntimeException($errorMessage);
                }

                $message = "Serial number berhasil diaktifkan. Total: {$activatedCount} SN";
                
                if ($isAjax) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => $message,
                        'activated_count' => $activatedCount
                    ]);
                }

                return redirect()->to('agent/sales/confirm')->with('message', [
                    'status' => 'success',
                    'message' => $message
                ]);

            } catch (\Exception $e) {
                if (isset($itemSnModel)) {
                    $itemSnModel->skipValidation(false);
                }
                $db->transRollback();
                throw $e;
            }

        } catch (\Exception $e) {
            $errorMessage = trim($e->getMessage() ?? '');
            if ($errorMessage === '') {
                $errorMessage = 'Terjadi kesalahan yang tidak diketahui. Silakan periksa log server.';
            }

            log_message('error', 'Agent\SalesConfirm::verify error: ' . $errorMessage);
            
            $message = 'Gagal mengaktifkan serial number: ' . $errorMessage;
            if ($isAjax) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $message
                ]);
            }
            
            return redirect()->to('agent/sales/confirm')->with('message', [
                'status' => 'error',
                'message' => $message
            ]);
        }
    }

    /**
     * Assign serial numbers to a sale item
     * 
     * @param int $id Sale ID
     * @return ResponseInterface
     */
    public function assignSN(int $id): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Request tidak valid.'
            ]);
        }

        if ($id <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID penjualan tidak valid.'
            ]);
        }

        try {
            // Verify sale exists and is online
            $sale = $this->model->getSalesWithRelations($id);
            
            if (!$sale) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data penjualan tidak ditemukan.'
                ]);
            }

            if ($sale['sale_channel'] != self::CHANNEL_ONLINE) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Hanya penjualan online (agent) yang dapat di-assign serial number.'
                ]);
            }

            $postData = $this->request->getPost();
            $salesItemId = (int)($postData['sales_item_id'] ?? 0);
            $itemSnIds = $postData['item_sn_ids'] ?? [];

            if ($salesItemId <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Sales item ID tidak valid.'
                ]);
            }

            if (empty($itemSnIds) || !is_array($itemSnIds)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Serial number harus dipilih.'
                ]);
            }

            // Verify sales_detail belongs to this sale
            $salesDetail = $this->salesDetailModel->find($salesItemId);
            if (!$salesDetail || (int)$salesDetail['sale_id'] !== $id) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Sales detail tidak ditemukan atau tidak sesuai dengan sale.'
                ]);
            }

            // Check if this item has any serial numbers in item_sn table
            $hasSerialNumbers = $this->itemSnModel
                ->where('item_id', $salesDetail['item_id'])
                ->countAllResults() > 0;

            // If item has no serial numbers, return success (no SNs needed)
            if (!$hasSerialNumbers) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Item ini tidak memerlukan serial number.'
                ]);
            }

            // Start transaction early to prevent race conditions
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // Lock sales_detail record to prevent concurrent quantity checks
                // Use raw query with FOR UPDATE to lock the row
                $lockedSalesDetail = $db->query(
                    "SELECT * FROM sales_detail WHERE id = ? AND sale_id = ? FOR UPDATE",
                    [$salesItemId, $id]
                )->getRowArray();
                
                if (!$lockedSalesDetail) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Sales detail tidak ditemukan atau tidak sesuai dengan sale.'
                    ]);
                }

                $requiredQty = (int)($lockedSalesDetail['qty'] ?? 1);
                $itemId = (int)($lockedSalesDetail['item_id'] ?? 0);

                // Count currently assigned serial numbers for this sales_item (inside transaction)
                $currentAssigned = $this->salesItemSnModel
                    ->where('sales_item_id', $salesItemId)
                    ->countAllResults();

                // Validate and lock item_sn records, then assign
                $assignedCount = 0;
                $validItemSnIds = [];
                
                foreach ($itemSnIds as $itemSnId) {
                    $itemSnId = (int)$itemSnId;
                    if ($itemSnId <= 0) {
                        continue;
                    }

                    // Lock item_sn record with SELECT FOR UPDATE to prevent concurrent assignment
                    // This ensures only one admin can assign this SN at a time
                    $lockedItemSn = $db->query(
                        "SELECT * FROM item_sn WHERE id = ? FOR UPDATE",
                        [$itemSnId]
                    )->getRowArray();

                    if (!$lockedItemSn) {
                        continue; // SN doesn't exist
                    }

                    // Re-validate: SN must be available (is_sell = '0')
                    if (($lockedItemSn['is_sell'] ?? '0') !== '0') {
                        continue; // Already sold
                    }

                    // Re-validate: SN must belong to the same item
                    if ((int)($lockedItemSn['item_id'] ?? 0) !== $itemId) {
                        continue; // Wrong item
                    }

                    // Re-validate: Check if already assigned to this sales_item (inside transaction)
                    $existing = $this->salesItemSnModel
                        ->where('sales_item_id', $salesItemId)
                        ->where('item_sn_id', $itemSnId)
                        ->first();

                    if ($existing) {
                        continue; // Already assigned
                    }

                    // Re-validate: Check if SN has is_receive='1' in any sales_item_sn record (already received)
                    $receivedSn = $this->salesItemSnModel
                        ->where('item_sn_id', $itemSnId)
                        ->where('is_receive', '1')
                        ->first();

                    if ($receivedSn) {
                        continue; // Already received, cannot assign again
                    }

                    // This is a valid SN to assign
                    $validItemSnIds[] = [
                        'id' => $itemSnId,
                        'sn' => $lockedItemSn['sn'] ?? ''
                    ];
                }

                // Re-validate quantity limit inside transaction
                $totalAfterAssignment = $currentAssigned + count($validItemSnIds);
                if ($totalAfterAssignment > $requiredQty) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => "Jumlah serial number yang di-assign melebihi quantity. Quantity: {$requiredQty}, Sudah di-assign: {$currentAssigned}, Akan di-assign: " . count($validItemSnIds) . ", Total: {$totalAfterAssignment}. Maksimal: {$requiredQty} SN (1 SN = 1 Qty)"
                    ]);
                }

                // Perform inserts for all valid SNs
                foreach ($validItemSnIds as $validSn) {
                    try {
                        $this->salesItemSnModel->skipValidation(true);
                        $insertResult = $this->salesItemSnModel->insert([
                            'sale_id'       => $id,
                            'sales_item_id' => $salesItemId,
                            'item_sn_id' => $validSn['id'],
                            'sn' => $validSn['sn']
                        ]);
                        $this->salesItemSnModel->skipValidation(false);
                        
                        if (!$insertResult) {
                            $errors = $this->salesItemSnModel->errors();
                            $dbError = $db->error();
                            $errorMsg = 'Gagal menyimpan serial number.';
                            if ($errors && is_array($errors)) {
                                $errorMsg .= ' ' . implode(', ', $errors);
                            }
                            if (!empty($dbError['message'])) {
                                $errorMsg .= ' Database: ' . $dbError['message'];
                            }
                            throw new \Exception($errorMsg);
                        }
                        
                        $assignedCount++;
                    } catch (\Exception $e) {
                        // Handle duplicate key errors (MySQL error 1062) or other database errors
                        $dbError = $db->error();
                        if (!empty($dbError['code']) && $dbError['code'] == 1062) {
                            // Duplicate entry - another admin already assigned this SN
                            continue; // Skip this SN and continue with others
                        }
                        // Re-throw other errors
                        throw $e;
                    }
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    $dbError = $db->error();
                    $errorMsg = 'Transaksi gagal.';
                    if (!empty($dbError['message'])) {
                        $errorMsg .= ' Database: ' . $dbError['message'];
                    }
                    throw new \Exception($errorMsg);
                }

                if ($assignedCount === 0) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Tidak ada serial number yang berhasil di-assign. Mungkin sudah di-assign oleh admin lain atau tidak memenuhi syarat.'
                    ]);
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => "Serial number berhasil di-assign. Total: {$assignedCount} SN",
                    'assigned_count' => $assignedCount
                ]);

            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }

        } catch (\Exception $e) {
            log_message('error', 'Agent\SalesConfirm::assignSN error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal meng-assign serial number: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Receive single serial number (AJAX endpoint)
     * Updates sales_item_sn.is_receive='1' and item_sn.agent_id
     * 
     * @param int $saleId Sale ID
     * @param int $salesItemSnId Sales Item SN ID
     * @return ResponseInterface
     */
    public function receiveSN(int $saleId, int $salesItemSnId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ])->setStatusCode(400);
        }

        try {
            // Check if user is agent (only agents can receive SN)
            $userPermission = is_array($this->userPermission) ? $this->userPermission : [];
            $isAdmin = key_exists('read_all', $userPermission) || key_exists('update_all', $userPermission);
            if ($isAdmin) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Hanya agent yang dapat menerima serial number.'
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

            // Get agent ID from sales.warehouse_id
            $agentId = (int)($sale['warehouse_id'] ?? 0);
            if ($agentId <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Agent ID tidak valid.'
                ]);
            }

            // Verify sales_item_sn exists and belongs to this sale
            $salesItemSn = $this->salesItemSnModel
                ->select('sales_item_sn.*, sales_detail.sale_id, sales_item_sn.item_sn_id')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
                ->where('sales_item_sn.id', $salesItemSnId)
                ->where('sales_detail.sale_id', $saleId)
                ->first();

            if (!$salesItemSn) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Serial number tidak ditemukan atau tidak sesuai dengan penjualan.'
                ])->setStatusCode(404);
            }

            // Check if already received
            if (($salesItemSn['is_receive'] ?? '0') === '1') {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Serial number ini sudah diterima sebelumnya.'
                ]);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // Update sales_item_sn.is_receive = '1' and set receive_at timestamp
                $this->salesItemSnModel->update($salesItemSnId, [
                    'is_receive' => '1',
                    'receive_at' => date('Y-m-d H:i:s')
                ]);

                // Update item_sn.agent_id to match agent from sales.warehouse_id
                $itemSnId = (int)($salesItemSn['item_sn_id'] ?? 0);
                if ($itemSnId > 0) {
                    $this->itemSnModel->update($itemSnId, [
                        'agent_id' => $agentId
                    ]);
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Transaksi gagal.');
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Serial number berhasil diterima.',
                    'sales_item_sn_id' => $salesItemSnId
                ]);

            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }

        } catch (\Exception $e) {
            log_message('error', 'Agent\SalesConfirm::receiveSN error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menerima serial number: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Receive all serial numbers for a sale (AJAX endpoint)
     * Updates all sales_item_sn.is_receive='1' and corresponding item_sn.agent_id
     * 
     * @param int $saleId Sale ID
     * @return ResponseInterface
     */
    public function receiveAllSN(int $saleId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ])->setStatusCode(400);
        }

        try {
            // Verify sale exists
            $sale = $this->model->find($saleId);
            if (!$sale) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data penjualan tidak ditemukan.'
                ])->setStatusCode(404);
            }

            // Get agent ID from sales.warehouse_id
            $agentId = (int)($sale['warehouse_id'] ?? 0);
            if ($agentId <= 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Agent ID tidak valid.'
                ]);
            }

            // Get all sales_item_sn records for this sale where is_receive='0'
            $unreceivedSns = $this->salesItemSnModel
                ->select('sales_item_sn.id, sales_item_sn.item_sn_id, sales_detail.sale_id')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
                ->where('sales_detail.sale_id', $saleId)
                ->where('sales_item_sn.is_receive', '0')
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
                $itemSnIds = [];

                foreach ($unreceivedSns as $salesItemSn) {
                    $salesItemSnId = (int)($salesItemSn['id'] ?? 0);
                    $itemSnId = (int)($salesItemSn['item_sn_id'] ?? 0);

                    if ($salesItemSnId <= 0) {
                        continue;
                    }

                    // Update sales_item_sn.is_receive = '1' and set receive_at timestamp
                    $this->salesItemSnModel->update($salesItemSnId, [
                        'is_receive' => '1',
                        'receive_at' => date('Y-m-d H:i:s')
                    ]);

                    // Collect item_sn_ids to update
                    if ($itemSnId > 0 && !in_array($itemSnId, $itemSnIds)) {
                        $itemSnIds[] = $itemSnId;
                    }

                    $updatedCount++;
                }

                // Update all item_sn.agent_id in batch
                if (!empty($itemSnIds)) {
                    $this->itemSnModel->whereIn('id', $itemSnIds)
                        ->set('agent_id', $agentId)
                        ->update();
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
            log_message('error', 'Agent\SalesConfirm::receiveAllSN error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menerima serial number: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get DataTables data for serial numbers filtered by sale_id
     * Supports 4 filter types: 'unreceived', 'received', 'unused', 'used'
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
            ])->setStatusCode(400);
        }

        try {
            $draw   = (int) ($this->request->getPost('draw') ?? $this->request->getGet('draw') ?? 0);
            $start  = (int) ($this->request->getPost('start') ?? $this->request->getGet('start') ?? 0);
            $length = (int) ($this->request->getPost('length') ?? $this->request->getGet('length') ?? 10);
            $filter = $this->request->getPost('filter') ?? 'unreceived'; // 'unreceived', 'received', 'unused', 'used'

            // Validate filter
            if (!in_array($filter, ['unreceived', 'received', 'unused', 'used'], true)) {
                $filter = 'unreceived';
            }

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

            // Check if user is agent (for action button visibility)
            $userPermission = is_array($this->userPermission) ? $this->userPermission : [];
            $isAdmin = key_exists('read_all', $userPermission) || key_exists('update_all', $userPermission);
            $isAgent = !$isAdmin;

            // Verify sale exists
            $sale = $this->model->find($saleId);
            if (!$sale) {
                return $this->response->setJSON([
                    'draw'            => $draw,
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => [],
                    'error'           => 'Data penjualan tidak ditemukan.',
                ]);
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
                    item_sn.sn,
                    item_sn.barcode,
                    sales.sale_channel,
                    sales.warehouse_id')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
                ->join('sales', 'sales.id = sales_detail.sale_id', 'inner')
                ->join('item', 'item.id = sales_detail.item_id', 'left')
                ->join('item_sn', 'item_sn.id = sales_item_sn.item_sn_id', 'left')
                ->where('sales.id', $saleId)
                ->where('sales.sale_channel', self::CHANNEL_ONLINE);

            // Apply filter based on type
            if ($filter === 'unreceived') {
                $builder->where('sales_item_sn.is_receive', '0');
            } elseif ($filter === 'received') {
                $builder->where('sales_item_sn.is_receive', '1');
            } elseif ($filter === 'unused') {
                $builder->where('sales_item_sn.activated_at IS NULL');
            } elseif ($filter === 'used') {
                $builder->where('sales_item_sn.activated_at IS NOT NULL');
            }

            // Count total records for this sale
            $totalRecordsBuilder = $db->table('sales_item_sn')
                ->select('sales_item_sn.id')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
                ->join('sales', 'sales.id = sales_detail.sale_id', 'inner')
                ->where('sales.id', $saleId)
                ->where('sales.sale_channel', self::CHANNEL_ONLINE);

            if ($filter === 'unreceived') {
                $totalRecordsBuilder->where('sales_item_sn.is_receive', '0');
            } elseif ($filter === 'received') {
                $totalRecordsBuilder->where('sales_item_sn.is_receive', '1');
            } elseif ($filter === 'unused') {
                $totalRecordsBuilder->where('sales_item_sn.activated_at IS NULL');
            } elseif ($filter === 'used') {
                $totalRecordsBuilder->where('sales_item_sn.activated_at IS NOT NULL');
            }

            $totalRecords = $totalRecordsBuilder->countAllResults();

            // Apply search filter
            $totalFiltered = $totalRecords;
            if (!empty($searchValue)) {
                $builder->groupStart()
                      ->like('item_sn.sn', $searchValue)
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
                
                // Get barcode
                $barcode = !empty($row['barcode']) ? $row['barcode'] : '-';
                
                // Get SN
                $sn = !empty($row['sn']) ? $row['sn'] : '-';
                
                // Action button
                $actionButton = '';
                if ($filter === 'unreceived' && $isAgent) {
                    // Show receive button for unreceived SNs (agent only)
                    $actionButton = '<button type="button" class="btn btn-sm btn-success receive-sn-btn" '
                        . 'data-sales-item-sn-id="' . $row['id'] . '" '
                        . 'data-sn="' . esc($sn) . '" '
                        . 'title="Terima Serial Number">'
                        . '<i class="fas fa-check me-1"></i>Terima</button>';
                } elseif ($filter === 'received') {
                    $actionButton = '<span class="badge bg-success">Diterima</span>';
                } elseif ($filter === 'unused') {
                    $actionButton = '<span class="badge bg-warning">Belum Aktif</span>';
                } elseif ($filter === 'used') {
                    $actionButton = '<span class="badge bg-info">Aktif</span>';
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
        } catch (\Exception $e) {
            log_message('error', 'Agent\SalesConfirm::getSnDataDTForSale error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'draw'            => $draw ?? 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage(),
            ]);
        }
    }
}

