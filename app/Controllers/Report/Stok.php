<?php

/**
 * Report Stok Controller
 * 
 * Handles stock report with filtering capabilities
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
use App\Models\ItemSnModel;
use App\Models\ItemModel;
use App\Models\AgentModel;
use CodeIgniter\HTTP\ResponseInterface;

class Stok extends BaseController
{
    protected $model;
    protected $itemModel;
    protected $agentModel;
    
    /**
     * Initialize models
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new ItemSnModel();
        $this->itemModel = new ItemModel();
        $this->agentModel = new AgentModel();
        
        // Add DataTables Buttons extension for Excel and PDF export
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/JSZip/jszip.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/pdfmake/pdfmake.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/pdfmake/vfs_fonts.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/buttons.html5.min.js');
        $this->addJs($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/buttons.print.min.js');
        $this->addStyle($this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css');
        
        // Add Select2 for dropdown search
        $this->addJs($this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js');
        $this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css');
        $this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css');
        
        // Add flatpickr for date range picker
        $this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
        $this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
    }
    
    /**
     * Display stock report list page
     * 
     * @return void
     */
    public function index(): void
    {
        // Get all active agents for Pemilik dropdown
        $agents = $this->agentModel
            ->select('agent.id, agent.code, agent.name')
            ->where('agent.is_active', '1')
            ->orderBy('agent.name', 'ASC')
            ->findAll();

        // Get all active items for Item dropdown
        $items = $this->itemModel
            ->select('item.id, item.name, item.sku')
            ->where('item.status', '1')
            ->orderBy('item.name', 'ASC')
            ->findAll();

        $this->data = array_merge($this->data, [
            'title'         => 'Laporan Stok',
            'currentModule' => $this->currentModule,
            'config'        => $this->config,
            'msg'           => $this->session->getFlashdata('message'),
            'agents'        => $agents,
            'items'         => $items,
        ]);

        $this->data['breadcrumb'] = [
            'Home'       => $this->config->baseURL,
            'Laporan'    => $this->config->baseURL.'report/items',
            'Stok'       => '', // Current page, no link
        ];

        $this->view('report/item-result', $this->data);
    }

    /**
     * Show item serial number detail
     * 
     * @param int $id Item SN ID
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function detail(int $id)
    {
        if ($id <= 0) {
            return redirect()->to('report/items')->with('message', [
                'status' => 'error',
                'message' => 'ID serial number tidak valid.'
            ]);
        }

        try {
            // Get item SN with relations
            $itemSn = $this->model->find($id);
            
            if (!$itemSn) {
                return redirect()->to('report/items')->with('message', [
                    'status' => 'error',
                    'message' => 'Data serial number tidak ditemukan.'
                ]);
            }

            // Get item info
            $item = $this->itemModel->find($itemSn->item_id);
            
            // Get agent info
            $agent = null;
            if (!empty($itemSn->agent_id) && $itemSn->agent_id > 0) {
                $agent = $this->agentModel->find($itemSn->agent_id);
            }

            $this->data['title'] = 'Detail Serial Number';
            $this->data['currentModule'] = $this->currentModule;
            $this->data['config'] = $this->config;
            $this->data['itemSn'] = $itemSn;
            $this->data['item'] = $item;
            $this->data['agent'] = $agent;

            $this->data['breadcrumb'] = [
                'Home'       => $this->config->baseURL,
                'Laporan'    => $this->config->baseURL.'report/items',
                'Stok'       => $this->config->baseURL.'report/items',
                'Detail'     => '', // Current page, no link
            ];

            // For now, we'll create a simple detail view or reuse existing
            // You can create a dedicated view later if needed
            $this->view('report/item-detail', $this->data);
        } catch (\Exception $e) {
            log_message('error', 'Report\Stok::detail error: ' . $e->getMessage());
            return redirect()->to('report/items')->with('message', [
                'status' => 'error',
                'message' => 'Gagal memuat detail serial number.'
            ]);
        }
    }

    /**
     * Get DataTables data for stock report
     * Supports filters: date range, date, pemilik (agent), pusat/agent
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
            $itemId = $this->request->getPost('item_id') ?? $this->request->getGet('item_id') ?? '';
            $pemilik = $this->request->getPost('pemilik') ?? $this->request->getGet('pemilik') ?? '';
            $pusatAgent = $this->request->getPost('pusat_agent') ?? $this->request->getGet('pusat_agent') ?? '';

            $db = \Config\Database::connect();
            
            // Build base query
            $query = $this->buildStockQuery();

            // Apply date range filter
            // Parse date_range from flatpickr format: "YYYY-MM-DD to YYYY-MM-DD"
            if (!empty($dateRange)) {
                $dateParts = preg_split('/\s+to\s+/i', trim($dateRange));
                if (count($dateParts) === 2) {
                    $tanggalRentangStart = trim($dateParts[0]);
                    $tanggalRentangEnd = trim($dateParts[1]);
                    if (!empty($tanggalRentangStart) && !empty($tanggalRentangEnd)) {
                        $query->where('DATE(item_sn.created_at) >=', $tanggalRentangStart)
                              ->where('DATE(item_sn.created_at) <=', $tanggalRentangEnd);
                    }
                }
            }

            // Item filter
            if (!empty($itemId) && $itemId > 0) {
                $query->where('item_sn.item_id', (int)$itemId);
            }

            // Pemilik filter (agent_id)
            if (!empty($pemilik) && $pemilik > 0) {
                $query->where('item_sn.agent_id', (int)$pemilik);
            }

            // Pusat/Agent filter
            if ($pusatAgent === 'pusat') {
                $query->where('item_sn.agent_id', 0);
            } elseif ($pusatAgent === 'agent') {
                $query->where('item_sn.agent_id >', 0);
            }

            // Count total records with filters (rebuild query for count)
            $countQuery = $this->buildStockQuery();
            
            // Re-apply date range filter for count
            if (!empty($dateRange)) {
                $dateParts = preg_split('/\s+to\s+/i', trim($dateRange));
                if (count($dateParts) === 2) {
                    $tanggalRentangStart = trim($dateParts[0]);
                    $tanggalRentangEnd = trim($dateParts[1]);
                    if (!empty($tanggalRentangStart) && !empty($tanggalRentangEnd)) {
                        $countQuery->where('DATE(item_sn.created_at) >=', $tanggalRentangStart)
                                  ->where('DATE(item_sn.created_at) <=', $tanggalRentangEnd);
                    }
                }
            }
            if (!empty($itemId) && $itemId > 0) {
                $countQuery->where('item_sn.item_id', (int)$itemId);
            }
            if (!empty($pemilik) && $pemilik > 0) {
                $countQuery->where('item_sn.agent_id', (int)$pemilik);
            }
            if ($pusatAgent === 'pusat') {
                $countQuery->where('item_sn.agent_id', 0);
            } elseif ($pusatAgent === 'agent') {
                $countQuery->where('item_sn.agent_id >', 0);
            }
            
            $totalRecords = $countQuery->countAllResults(false);

            // Apply search filter if present
            $totalFiltered = $totalRecords;
            if (!empty($searchValue)) {
                $query->groupStart()
                      ->like('item.name', $searchValue)
                      ->orLike('item.sku', $searchValue)
                      ->orLike('item_sn.sn', $searchValue)
                      ->orLike('item_sn.barcode', $searchValue)
                      ->orLike('agent.name', $searchValue)
                      ->groupEnd();

                // Rebuild query for filtered count
                $countQuery = $this->buildStockQuery();
                
                // Re-apply date range filter
                if (!empty($dateRange)) {
                    $dateParts = preg_split('/\s+to\s+/i', trim($dateRange));
                    if (count($dateParts) === 2) {
                        $tanggalRentangStart = trim($dateParts[0]);
                        $tanggalRentangEnd = trim($dateParts[1]);
                        if (!empty($tanggalRentangStart) && !empty($tanggalRentangEnd)) {
                            $countQuery->where('DATE(item_sn.created_at) >=', $tanggalRentangStart)
                                      ->where('DATE(item_sn.created_at) <=', $tanggalRentangEnd);
                        }
                    }
                }
                if (!empty($itemId) && $itemId > 0) {
                    $countQuery->where('item_sn.item_id', (int)$itemId);
                }
                if (!empty($pemilik) && $pemilik > 0) {
                    $countQuery->where('item_sn.agent_id', (int)$pemilik);
                }
                if ($pusatAgent === 'pusat') {
                    $countQuery->where('item_sn.agent_id', 0);
                } elseif ($pusatAgent === 'agent') {
                    $countQuery->where('item_sn.agent_id >', 0);
                }
                
                $countQuery->groupStart()
                           ->like('item.name', $searchValue)
                           ->orLike('item.sku', $searchValue)
                           ->orLike('item_sn.sn', $searchValue)
                           ->orLike('item_sn.barcode', $searchValue)
                           ->orLike('agent.name', $searchValue)
                           ->groupEnd();

                $totalFiltered = $countQuery->countAllResults(false);
            }

            $data = $query->orderBy('item_sn.created_at', 'DESC')
                          ->limit($length, $start)
                          ->get()
                          ->getResultArray();

            // Format for DataTables
            $result = $this->formatStockDataTablesData($data, $start);

            return $this->response->setJSON([
                'draw'            => $draw,
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data'            => $result,
            ]);
        } catch (\Throwable $e) {
            log_message(
                'error',
                'Report\Stok::getDataDT error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString()
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
     * Build base stock query with joins
     * 
     * @return \CodeIgniter\Database\BaseBuilder
     */
    protected function buildStockQuery()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('item_sn');
        
        return $builder->select('item_sn.*, 
            item.name as item_name,
            item.sku as item_sku,
            agent.name as agent_name,
            agent.code as agent_code')
            ->join('item', 'item.id = item_sn.item_id', 'left')
            ->join('agent', 'agent.id = item_sn.agent_id', 'left');
    }

    /**
     * Format data for DataTables
     * Columns: No | Item Name | SKU | Serial Number | Barcode | Pemilik | Status | Tanggal | Aksi
     * 
     * @param array $data Raw data from database
     * @param int $start Starting index for row numbering
     * @return array Formatted data for DataTables
     */
    protected function formatStockDataTablesData(array $data, int $start): array
    {
        $result = [];
        $no = $start + 1;

        foreach ($data as $row) {
            // Item Name
            $itemName = $row['item_name'] ?? '-';
            
            // SKU
            $sku = $row['item_sku'] ?? '-';
            
            // Serial Number
            $sn = $row['sn'] ?? '-';
            
            // Barcode
            $barcode = !empty($row['barcode']) ? $row['barcode'] : '-';
            
            // Pemilik: agent.name if agent_id > 0, or "Pusat" if agent_id = 0
            $pemilik = 'Pusat';
            if (!empty($row['agent_id']) && $row['agent_id'] > 0) {
                $pemilik = $row['agent_name'] ?? 'Agent #' . $row['agent_id'];
            }
            
            // Status badges
            $isSell = (int)($row['is_sell'] ?? 0);
            $isActivated = (int)($row['is_activated'] ?? 0);
            
            $statusBadges = [];
            if ($isSell == 1) {
                $statusBadges[] = '<span class="badge bg-danger">Terjual</span>';
            }
            if ($isActivated == 1) {
                $statusBadges[] = '<span class="badge bg-success">Aktif</span>';
            } else {
                $statusBadges[] = '<span class="badge bg-warning">Belum Aktif</span>';
            }
            $status = !empty($statusBadges) ? implode(' ', $statusBadges) : '-';
            
            // Tanggal: created_at formatted
            $tanggal = tgl_indo8($row['created_at'] ?? '');

            $result[] = [
                'ignore_search_urut'    => $no,
                'item_name'             => esc($itemName),
                'item_sku'              => esc($sku),
                'sn'                    => esc($sn),
                'barcode'               => esc($barcode),
                'pemilik'               => esc($pemilik),
                'status'                => $status,
                'created_at'            => $tanggal,
                'ignore_search_action'  => '<a href="' . $this->config->baseURL . 'report/items/' . $row['id'] . '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>',
            ];

            $no++;
        }

        return $result;
    }
}

