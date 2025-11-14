<?php

namespace App\Controllers;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Controller for managing item serial numbers (SN) with CRUD operations and Excel import/export
 * This file represents the Controller.
 */
class Item_Sn extends BaseController
{
    protected $model;
    protected $agentModel;
    protected $itemModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new \App\Models\ItemSnModel();
        $this->agentModel = new \App\Models\AgentModel();
        $this->itemModel = new \App\Models\ItemModel();
    }

    /**
     * Load SN tab content for item form
     * 
     * @param int $item_id Item ID
     * @return string View content
     */
    public function index($item_id)
    {
        // Check if user has permission (use return=true to avoid exit on failure)
        if (!$this->hasPermissionPrefix('read', true)) {
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Tidak memiliki izin untuk melihat data SN.</div>';
            }
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki izin untuk melihat data.'
            ]);
        }
        
        // Ensure userPermission is set
        if (empty($this->userPermission)) {
            $this->userPermission = [];
        }

        // Get agents based on user role
        if (in_array('read_all', $this->userPermission) || !in_array('read_own', $this->userPermission)) {
            // Admin or user with read_all permission - can select any active agent
            $agents = $this->agentModel
                ->select('agent.id, agent.code, agent.name')
                ->where('agent.is_active', '1')
                ->orderBy('agent.name', 'ASC')
                ->findAll();
            $isAgentLocked = false;
            $lockedAgentId = null;
        } else {
            // Agent - locked to own account
            // Get agent ID from user_role_agent table
            $db = \Config\Database::connect();
            $userRoleAgent = $db->table('user_role_agent')
                                ->where('user_id', $this->user['id_user'])
                                ->get()
                                ->getRow();
            
            if ($userRoleAgent) {
                $lockedAgentId = $userRoleAgent->agent_id;
                $agents = $this->agentModel
                    ->select('agent.id, agent.code, agent.name')
                    ->where('agent.id', $lockedAgentId)
                    ->where('agent.is_active', '1')
                    ->findAll();
                $isAgentLocked = true;
            } else {
                $agents = [];
                $isAgentLocked = true;
                $lockedAgentId = null;
            }
        }

        // Get variant_id from query string (if coming from "Manage SN" button)
        $variantId = $this->request->getGet('variant_id') ?? null;
        
        // If variant_id is provided, get variant info for display
        $variantInfo = null;
        if ($variantId) {
            $variantModel = new \App\Models\ItemVarianModel();
            $variantInfo = $variantModel->find($variantId);
        }

        $data = [
            'item_id' => $item_id,
            'variant_id' => $variantId,
            'variant_info' => $variantInfo,
            'agents' => $agents,
            'is_agent_locked' => $isAgentLocked,
            'locked_agent_id' => $lockedAgentId,
            'config' => $this->config
        ];

        try {
            $html = view('themes/modern/item/input_sn', $data);
            
            // For AJAX requests, return the HTML string directly
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return $html;
            }
            
            return $html;
        } catch (\Exception $e) {
            log_message('error', 'Item_Sn::index error: ' . $e->getMessage());
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Error loading SN form: ' . esc($e->getMessage()) . '</div>';
            }
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error loading SN form: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store SNs (manual input or batch)
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function store()
    {
        // Check permission
        if (!$this->hasPermissionPrefix('create')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki izin untuk menyimpan data.'
            ]);
        }

        $itemId = $this->request->getPost('item_id');
        $variantId = $this->request->getPost('variant_id') ?: null;
        $snList = $this->request->getPost('sn_list');
        // Always default to '0' - these fields cannot be set via form for security
        $isSell = '0';
        $isActivated = '0';
        $activatedAt = null;
        $expiredAt = null;

        if (empty($itemId) || empty($snList)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Item dan Serial Number harus diisi.'
            ]);
        }

        // Parse SN list (textarea input - one per line or comma separated)
        $sns = [];
        $lines = preg_split('/[\r\n,]+/', $snList);
        foreach ($lines as $line) {
            $sn = trim($line);
            if (!empty($sn)) {
                $sns[] = $sn;
            }
        }

        if (empty($sns)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Serial Number tidak boleh kosong.'
            ]);
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($sns as $sn) {
            // Check if SN already exists
            if ($this->model->snExists($sn)) {
                $errorCount++;
                $errors[] = "SN {$sn} sudah terdaftar";
                continue;
            }

            $data = [
                'item_id' => $itemId,
                'variant_id' => $variantId,
                'agent_id' => 0,
                'user_id' => $this->user['id_user'],
                'sn' => $sn,
                'is_sell' => $isSell,
                'is_activated' => $isActivated,
                'activated_at' => $activatedAt ? date('Y-m-d H:i:s', strtotime($activatedAt)) : null,
                'expired_at' => $expiredAt ? date('Y-m-d H:i:s', strtotime($expiredAt)) : null,
            ];

            if ($this->model->insert($data)) {
                $successCount++;
            } else {
                $errorCount++;
                $errors[] = "SN {$sn}: " . implode(', ', $this->model->errors());
            }
        }

        $message = "Berhasil menyimpan {$successCount} SN";
        if ($errorCount > 0) {
            $message .= ", {$errorCount} gagal: " . implode(', ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' dan ' . (count($errors) - 5) . ' error lainnya';
            }
        }

        return $this->response->setJSON([
            'status' => $errorCount == 0 ? 'success' : ($successCount > 0 ? 'partial' : 'error'),
            'message' => $message,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors
        ]);
    }

    /**
     * Import SNs from Excel file
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function importExcel()
    {
        // Check permission
        if (!$this->hasPermissionPrefix('create')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki izin untuk mengimpor data.'
            ]);
        }

        $file = $this->request->getFile('excel_file');
        $itemId = $this->request->getPost('item_id');
        $variantId = $this->request->getPost('variant_id') ?: null;
        $agentId = 0;

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File tidak valid atau gagal diupload.'
            ]);
        }

        $extension = strtolower($file->getClientExtension());
        if (!in_array($extension, ['xlsx', 'xls'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File harus berupa Excel (.xlsx atau .xls).'
            ]);
        }

        // If variant_id is provided, validate it belongs to the item
        if ($variantId) {
            $variantModel = new \App\Models\ItemVarianModel();
            $variant = $variantModel->find($variantId);
            if (!$variant || $variant['item_id'] != $itemId) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Varian tidak valid untuk item ini.'
                ]);
            }
        }

        try {
            // Read Excel file
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(ucfirst($extension));
            $spreadsheet = $reader->load($file->getTempName());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            array_shift($rows);

            $data = [];
            foreach ($rows as $index => $row) {
                // Skip empty rows
                if (empty($row[0])) continue;

                $sn = trim($row[0] ?? '');
                if (empty($sn)) continue;

                // Parse fields - only SN and SN_Replaced are allowed
                $snReplaced = null;
                // Always default to '0' and null - these cannot be set via import for security
                $isSell = '0';
                $isActivated = '0';
                $activatedAt = null;
                $expiredAt = null;

                // Column mapping: SN | SN_Replaced
                // Note: All other fields (Activated_At, Expired_At, Is_Sell, Is_Activated) 
                // are ignored and always set to defaults for security
                if (!empty($row[1])) {
                    $snReplaced = trim($row[1]);
                }
                // Additional columns (row[2], row[3], etc.) are ignored

                $data[] = [
                    'item_id' => $itemId,
                    'variant_id' => $variantId,
                    'agent_id' => 0,
                    'user_id' => $this->user['id_user'],
                    'sn' => $sn,
                    'sn_replaced' => $snReplaced,
                    'is_sell' => $isSell,
                    'is_activated' => $isActivated,
                    'activated_at' => $activatedAt,
                    'expired_at' => $expiredAt,
                ];
            }

            if (empty($data)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak ada data SN yang valid dalam file Excel.'
                ]);
            }

            // Bulk insert
            $result = $this->model->bulkInsert($data);

            $message = "Berhasil mengimpor {$result['success']} SN";
            if (!empty($result['errors'])) {
                $message .= ", " . count($result['errors']) . " gagal";
            }

            return $this->response->setJSON([
                'status' => $result['success'] > 0 ? ($result['errors'] ? 'partial' : 'success') : 'error',
                'message' => $message,
                'success_count' => $result['success'],
                'error_count' => count($result['errors']),
                'errors' => array_slice($result['errors'], 0, 10) // Limit errors shown
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download Excel template
     * 
     * @return \CodeIgniter\HTTP\DownloadResponse
     */
    public function downloadTemplate()
    {
        // Check if user has permission (use return=true to avoid exit)
        if (!$this->hasPermissionPrefix('read', true)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Tidak memiliki izin untuk mengunduh template.'
                ]);
            }
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Tidak memiliki izin untuk mengunduh template.']);
        }

        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers - only SN and SN_Replaced allowed
            $headers = ['SN', 'SN_Replaced'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $sheet->getColumnDimension($col)->setAutoSize(true);
                $col++;
            }

            // Add example row
            $sheet->setCellValue('A2', 'SN001234567');
            $sheet->setCellValue('B2', '');

            // Generate Excel file to output
            $filename = 'item_sn_template.xlsx';
            
            // Set response headers
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Write directly to output
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            log_message('error', 'Item_Sn::downloadTemplate error: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal membuat template: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Gagal membuat template: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete SN record
     * 
     * @param int $id SN ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete($id)
    {
        // Check permission
        if (!$this->hasPermissionPrefix('delete')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki izin untuk menghapus data.'
            ]);
        }

        // Get SN record
        $sn = $this->model->find($id);
        if (!$sn) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data SN tidak ditemukan.'
            ]);
        }

        // Check if agent can only delete their own SNs
        if (in_array('read_own', $this->userPermission) && !in_array('read_all', $this->userPermission)) {
            if ($sn->agent_id != $this->getUserAgentId()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Anda hanya dapat menghapus SN milik Anda sendiri.'
                ]);
            }
        }

        if ($this->model->delete($id)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'SN berhasil dihapus.'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus SN: ' . implode(', ', $this->model->errors())
            ]);
        }
    }

    /**
     * Helper: Parse Excel date value
     * 
     * @param mixed $value Date value from Excel
     * @return string|null Formatted date string or null
     */
    private function parseExcelDate($value)
    {
        if (empty($value)) return null;

        // If it's already a DateTime object
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        // If it's a numeric Excel date
        if (is_numeric($value)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $date->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // Try parsing as string
                $timestamp = strtotime($value);
                if ($timestamp !== false) {
                    return date('Y-m-d H:i:s', $timestamp);
                }
            }
        }

        // Try parsing as string
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d H:i:s', $timestamp);
        }

        return null;
    }

    /**
     * Get SN list for DataTable (AJAX)
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getSnList()
    {
        // Check permission
        if (!$this->hasPermissionPrefix('read')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak memiliki izin untuk melihat data.',
                'data' => []
            ]);
        }

        $itemId = $this->request->getPost('item_id');
        if (empty($itemId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Item ID tidak valid.',
                'data' => []
            ]);
        }

        // Build query
        $query = $this->model->select('item_sn.*, agent.name as agent_name, agent.code as agent_code, item_variant.variant_name, item_variant.sku_variant')
                            ->join('agent', 'agent.id = item_sn.agent_id', 'left')
                            ->join('item_variant', 'item_variant.id = item_sn.variant_id', 'left')
                            ->where('item_sn.item_id', $itemId)
                            ->where('item_sn.agent_id', '0')
                            ->where('item_sn.is_sell', '0');

        // Filter by agent if user is agent (can only see own SNs)
        if (in_array('read_own', $this->userPermission) && !in_array('read_all', $this->userPermission)) {
            $agentId = $this->getUserAgentId();
            if ($agentId) {
                $query->where('item_sn.agent_id', $agentId);
            } else {
                // Agent user but no agent assigned
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => '',
                    'data' => []
                ]);
            }
        }

        $sns = $query->orderBy('item_sn.created_at', 'DESC')->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'message' => '',
            'data' => $sns
        ]);
    }

    /**
     * Helper: Get agent ID for current user
     * 
     * @return int|null Agent ID or null
     */
    private function getUserAgentId()
    {
        $db = \Config\Database::connect();
        $userRoleAgent = $db->table('user_role_agent')
                            ->where('user_id', $this->user['id_user'])
                            ->get()
                            ->getRow();
        
        return $userRoleAgent ? $userRoleAgent->agent_id : null;
    }

    /**
     * Get sold SN list - handles both GET (view) and POST (DataTable) requests
     * 
     * @param int $item_id Item ID
     * @return string|\CodeIgniter\HTTP\ResponseInterface View HTML or JSON response
     */
    public function getSoldSnList($item_id)
    {
        // Check if this is a DataTable POST request
        // DataTables always sends 'draw' parameter in POST requests
        $hasDrawParam = $this->request->getPost('draw') !== null;
        $isPostMethod = strtoupper($this->request->getMethod()) === 'POST';
        $isDataTableRequest = $isPostMethod && $hasDrawParam;
        
        // Handle POST request (DataTable server-side processing) - must return JSON
        if ($isDataTableRequest) {
            // Ensure JSON response
            $this->response->setContentType('application/json');
            
            // Check permission
            if (!$this->hasPermissionPrefix('read', true)) {
                return $this->response->setJSON([
                    'draw' => (int) ($this->request->getPost('draw') ?? 0),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Tidak memiliki izin untuk melihat data.'
                ]);
            }
            
            try {
                return $this->getSoldSnDataTable($item_id);
            } catch (\Exception $e) {
                log_message('error', 'Item_Sn::getSoldSnList POST error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
                return $this->response->setJSON([
                    'draw' => (int) ($this->request->getPost('draw') ?? 0),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
        }

        // Handle GET request (return view) - only if NOT a DataTable request
        // Check permission
        if (!$this->hasPermissionPrefix('read', true)) {
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Tidak memiliki izin untuk melihat data SN terjual.</div>';
            }
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Tidak memiliki izin untuk melihat data.']);
        }

        try {
            $data = [
                'item_id' => $item_id,
                'config' => $this->config
            ];

            $html = view('themes/modern/item/sold_sn_table', $data);
            
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return $html;
            }
            
            return $html;
        } catch (\Exception $e) {
            log_message('error', 'Item_Sn::getSoldSnList GET error: ' . $e->getMessage());
            if ($this->request->isAJAX() || $this->request->getHeader('X-Requested-With')) {
                return '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Error loading sold SN list: ' . esc($e->getMessage()) . '</div>';
            }
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error loading sold SN list: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get sold SN data for DataTable (server-side processing)
     * 
     * @param int $item_id Item ID
     * @return \CodeIgniter\HTTP\ResponseInterface JSON response
     */
    private function getSoldSnDataTable($item_id)
    {
        try {
            $draw = (int) ($this->request->getPost('draw') ?? 0);
            $start = (int) ($this->request->getPost('start') ?? 0);
            $length = (int) ($this->request->getPost('length') ?? 10);

            // Defensive bounds for pagination
            if ($start < 0) {
                $start = 0;
            }
            if ($length < 1 || $length > 100) {
                $length = 10;
            }

            // Retrieve search value from DataTables
            $search = $this->request->getPost('search');
            $searchValue = '';
            if (is_array($search) && isset($search['value'])) {
                $searchValue = trim($search['value']);
            } elseif (is_string($search)) {
                $searchValue = trim($search);
            }

            // Get order column and direction
            $order = $this->request->getPost('order');
            $orderColumn = 0;
            $orderDir = 'desc';
            if (!empty($order) && is_array($order) && isset($order[0])) {
                $orderColumn = (int) ($order[0]['column'] ?? 0);
                $orderDir = strtolower($order[0]['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
            }

            $db = \Config\Database::connect();
            
            // Build base query for counting total records (before filtering)
            $countBuilder = $db->table('item_sn')
                ->join('sales_item_sn', 'sales_item_sn.item_sn_id = item_sn.id', 'inner')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
                ->join('sales', 'sales.id = sales_detail.sale_id', 'inner')
                ->where('item_sn.item_id', $item_id)
                ->where('item_sn.is_sell', '1');
            
            $recordsTotal = $countBuilder->countAllResults();
            
            // Build main query for data retrieval
            $builder = $db->table('item_sn')
                ->select('item_sn.id,
                    item_sn.sn,
                    item_sn.barcode,
                    agent.name as agent_name,
                    sales.created_at as sale_date,
                    sales.invoice_no,
                    sales_item_sn.no_hp,
                    sales_item_sn.plat_code,
                    sales_item_sn.plat_number,
                    customer.name as customer_name,
                    customer.phone as customer_phone')
                ->join('sales_item_sn', 'sales_item_sn.item_sn_id = item_sn.id', 'inner')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
                ->join('sales', 'sales.id = sales_detail.sale_id', 'inner')
                ->join('agent', 'agent.id = sales.warehouse_id', 'left')
                ->join('customer', 'customer.id = sales.customer_id', 'left')
                ->where('item_sn.item_id', $item_id)
                ->where('item_sn.is_sell', '1');

            // Apply search filter
            if (!empty($searchValue)) {
                $builder->groupStart()
                    ->like('item_sn.sn', $searchValue)
                    ->orLike('item_sn.barcode', $searchValue)
                    ->orLike('agent.name', $searchValue)
                    ->orLike('sales.invoice_no', $searchValue)
                    ->orLike('customer.name', $searchValue)
                    ->orLike('sales_item_sn.no_hp', $searchValue)
                    ->groupEnd();
            }

            // Get filtered count
            $filteredBuilder = $db->table('item_sn')
                ->join('sales_item_sn', 'sales_item_sn.item_sn_id = item_sn.id', 'inner')
                ->join('sales_detail', 'sales_detail.id = sales_item_sn.sales_item_id', 'inner')
                ->join('sales', 'sales.id = sales_detail.sale_id', 'inner')
                ->join('agent', 'agent.id = sales.warehouse_id', 'left')
                ->join('customer', 'customer.id = sales.customer_id', 'left')
                ->where('item_sn.item_id', $item_id)
                ->where('item_sn.is_sell', '1');
            
            if (!empty($searchValue)) {
                $filteredBuilder->groupStart()
                    ->like('item_sn.sn', $searchValue)
                    ->orLike('item_sn.barcode', $searchValue)
                    ->orLike('agent.name', $searchValue)
                    ->orLike('sales.invoice_no', $searchValue)
                    ->orLike('customer.name', $searchValue)
                    ->orLike('sales_item_sn.no_hp', $searchValue)
                    ->groupEnd();
            }
            
            $recordsFiltered = $filteredBuilder->countAllResults();

            // Apply ordering
            $orderColumns = [
                1 => 'item_sn.sn',
                2 => 'item_sn.barcode',
                3 => 'agent.name',
                4 => 'sales.created_at',
                6 => 'sales.invoice_no'
            ];
            
            if (isset($orderColumns[$orderColumn])) {
                $builder->orderBy($orderColumns[$orderColumn], $orderDir);
            } else {
                $builder->orderBy('sales.created_at', 'desc'); // Default order
            }

            // Apply pagination
            $builder->limit($length, $start);

            // Execute query
            $results = $builder->get()->getResultArray();

            // Format data for DataTable
            $data = [];
            foreach ($results as $row) {
                // Format customer info
                $customerInfo = [];
                if (!empty($row['customer_name'])) {
                    $customerInfo[] = $row['customer_name'];
                }
                if (!empty($row['customer_phone'])) {
                    $customerInfo[] = '<i class="fas fa-phone me-1"></i>' . $row['customer_phone'];
                }
                if (!empty($row['no_hp'])) {
                    $customerInfo[] = '<i class="fas fa-mobile-alt me-1"></i>' . $row['no_hp'];
                }
                if (!empty($row['plat_code']) && !empty($row['plat_number'])) {
                    $customerInfo[] = '<i class="fas fa-car me-1"></i>' . $row['plat_code'] . ' ' . $row['plat_number'];
                }
                
                $customerInfoStr = !empty($customerInfo) ? implode('<br>', $customerInfo) : '-';

                $data[] = [
                    'sn' => $row['sn'] ?? '-',
                    'barcode' => !empty($row['barcode']) ? $row['barcode'] : '-',
                    'agent_name' => $row['agent_name'] ?? '-',
                    'sale_date' => $row['sale_date'] ?? null,
                    'customer_info' => $customerInfoStr,
                    'invoice_no' => $row['invoice_no'] ?? '-'
                ];
            }

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Item_Sn::getSoldSnDataTable error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'draw' => (int) ($this->request->getPost('draw') ?? 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage()
            ]);
        }
    }
}
