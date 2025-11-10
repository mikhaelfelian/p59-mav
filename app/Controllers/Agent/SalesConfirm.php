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
        
        $this->view('sales/confirm-sn-list', $this->data);
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
            
            // Get sales with online channel that have sales_item_sn but item_sn.is_sell = '0'
            // This means they have serial numbers assigned but not yet activated
            $builder = $db->table('sales')
                ->select('sales.*, 
                    customer.name as customer_name,
                    user.nama as user_name,
                    agent.name as agent_name,
                    COUNT(DISTINCT sales_item_sn.id) as pending_sn_count')
                ->join('customer', 'customer.id = sales.customer_id', 'left')
                ->join('user', 'user.id_user = sales.user_id', 'left')
                ->join('agent', 'agent.id = sales.warehouse_id', 'left')
                ->join('sales_detail', 'sales_detail.sale_id = sales.id', 'inner')
                ->join('sales_item_sn', 'sales_item_sn.sales_item_id = sales_detail.id', 'inner')
                ->join('item_sn', 'item_sn.id = sales_item_sn.item_sn_id', 'inner')
                ->where('sales.sale_channel', self::CHANNEL_ONLINE)
                ->where('item_sn.is_sell', '0')
                ->groupBy('sales.id');

            // Apply search filter
            if (!empty($searchValue)) {
                $builder->groupStart()
                      ->like('sales.invoice_no', $searchValue)
                      ->orLike('customer.name', $searchValue)
                      ->orLike('user.nama', $searchValue)
                      ->orLike('agent.name', $searchValue)
                      ->groupEnd();
            }

            // Get total count
            $totalRecords = $db->table('sales')
                ->select('sales.id')
                ->join('sales_detail', 'sales_detail.sale_id = sales.id', 'inner')
                ->join('sales_item_sn', 'sales_item_sn.sales_item_id = sales_detail.id', 'inner')
                ->join('item_sn', 'item_sn.id = sales_item_sn.item_sn_id', 'inner')
                ->where('sales.sale_channel', self::CHANNEL_ONLINE)
                ->where('item_sn.is_sell', '0')
                ->groupBy('sales.id')
                ->countAllResults(false);

            // Get filtered count
            $countQuery = clone $builder;
            $totalFiltered = $countQuery->countAllResults(false);

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
                $actionButtons .= '<a href="' . $this->config->baseURL . 'agent/sales-confirm/' . $row['id'] . '" ';
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
            return redirect()->to('agent/sales-confirm')->with('message', [
                'status' => 'error',
                'message' => 'ID penjualan tidak valid.'
            ]);
        }

        try {
            // Get sale with relations
            $sale = $this->model->getSalesWithRelations($id);
            
            if (!$sale) {
                return redirect()->to('agent/sales-confirm')->with('message', [
                    'status' => 'error',
                    'message' => 'Data penjualan tidak ditemukan.'
                ]);
            }

            // Verify it's an online sale (agent order)
            if ($sale['sale_channel'] != self::CHANNEL_ONLINE) {
                return redirect()->to('agent/sales-confirm')->with('message', [
                    'status' => 'error',
                    'message' => 'Hanya penjualan online (agent) yang memerlukan verifikasi SN.'
                ]);
            }

            // Get items from sales_detail
            $items = $this->salesDetailModel->getDetailsBySale($id);

            // Get serial numbers for each item that need confirmation
            $itemsWithSN = [];
            foreach ($items as $item) {
                // Get sales_item_sn records for this sales_detail
                $salesItemSns = $this->salesItemSnModel
                    ->select('sales_item_sn.*, item_sn.sn, item_sn.is_sell, item_sn.is_activated, item_sn.activated_at, item_sn.expired_at, item_sn.item_id')
                    ->join('item_sn', 'item_sn.id = sales_item_sn.item_sn_id', 'inner')
                    ->where('sales_item_sn.sales_item_id', $item['id'])
                    ->findAll();

                // Filter only those with is_sell = '0' (not yet activated)
                $pendingSns = [];
                foreach ($salesItemSns as $salesItemSn) {
                    if (($salesItemSn['is_sell'] ?? '0') === '0') {
                        $pendingSns[] = $salesItemSn;
                    }
                }

                if (!empty($pendingSns)) {
                    $item['pending_sns'] = $pendingSns;
                    $itemsWithSN[] = $item;
                }
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

            $this->data['title'] = 'Verifikasi Order Agent';
            $this->data['currentModule'] = $this->currentModule;
            $this->data['config'] = $this->config;
            $this->data['sale'] = $sale;
            $this->data['items'] = $itemsWithSN;
            $this->data['payment'] = $paymentInfo;
            $this->data['gatewayResponse'] = $gatewayResponse;

            $this->view('sales/confirm-sn-detail', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Agent\SalesConfirm::detail error: ' . $e->getMessage());
            return redirect()->to('agent/sales-confirm')->with('message', [
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
            return redirect()->to('agent/sales-confirm')->with('message', [
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
                return redirect()->to('agent/sales-confirm')->with('message', ['status' => 'error', 'message' => $message]);
            }

            if ($sale['sale_channel'] != self::CHANNEL_ONLINE) {
                $message = 'Hanya penjualan online (agent) yang memerlukan verifikasi SN.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'error', 'message' => $message]);
                }
                return redirect()->to('agent/sales-confirm')->with('message', ['status' => 'error', 'message' => $message]);
            }

            // Get items with pending serial numbers
            $items = $this->salesDetailModel->getDetailsBySale($id);
            
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                $itemModel = new \App\Models\ItemModel();
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

                    $itemSnModel = new \App\Models\ItemSnModel();
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

                        $itemSnModel->skipValidation(true);
                        $itemSnModel->update($itemSnId, $updateData);
                        $itemSnModel->skipValidation(false);
                        
                        $activatedCount++;
                    }
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Transaksi gagal.');
                }

                $message = "Serial number berhasil diaktifkan. Total: {$activatedCount} SN";
                
                if ($isAjax) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => $message,
                        'activated_count' => $activatedCount
                    ]);
                }

                return redirect()->to('agent/sales-confirm')->with('message', [
                    'status' => 'success',
                    'message' => $message
                ]);

            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }

        } catch (\Exception $e) {
            log_message('error', 'Agent\SalesConfirm::verify error: ' . $e->getMessage());
            
            $message = 'Gagal mengaktifkan serial number: ' . $e->getMessage();
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'error', 'message' => $message]);
            }
            
            return redirect()->to('agent/sales-confirm')->with('message', [
                'status' => 'error',
                'message' => $message
            ]);
        }
    }
}

