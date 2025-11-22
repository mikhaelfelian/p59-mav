<?php

/**
 * Receivables Controller
 * 
 * Handles agent receivables monitoring with aging reports, limit checking, and order blocking
 * 
 * @package    App\Controllers
 * @author     Mikhael Felian Waskito <mikhaelfelian@gmail.com>
 * @copyright  2025
 * @license    MIT
 * @version    1.0.0
 * @since      2025-11-20
 */

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AgentPaylaterModel;
use App\Models\AgentModel;
use CodeIgniter\HTTP\ResponseInterface;

class Receivables extends BaseController
{
    protected $agentPaylaterModel;
    protected $agentModel;
    
    /**
     * Initialize models
     */
    public function __construct()
    {
        parent::__construct();
        $this->agentPaylaterModel = new AgentPaylaterModel();
        $this->agentModel = new AgentModel();
    }
    
    /**
     * Display receivables monitoring dashboard
     * 
     * @return void
     */
    public function index(): void
    {
        // Only admins can access this module
        $isAdmin = $this->hasPermission('read_all');
        if (!$isAdmin) {
            $this->printError('Anda tidak memiliki permission untuk mengakses modul ini.');
            return;
        }

        // Get aging summary
        $agingSummary = $this->getAgingSummary();

        $this->data = array_merge($this->data, [
            'title'         => 'Monitoring Piutang Agen',
            'currentModule' => $this->currentModule,
            'config'        => $this->config,
            'msg'           => $this->session->getFlashdata('message'),
            'agingSummary'  => $agingSummary,
        ]);

        $this->data['breadcrumb'] = [
            'Home'                => $this->config->baseURL,
            'Monitoring Piutang Agen' => '',
        ];

        $this->view('receivables/index', $this->data);
    }

    /**
     * Get aging summary for all agents
     * 
     * @return array
     */
    protected function getAgingSummary(): array
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        
        // Get all agents with receivables
        $agents = $this->agentModel->where('is_active', '1')->findAll();
        
        $summary = [
            'current' => 0,
            'overdue_30d' => 0,
            'overdue_60d' => 0,
            'overdue_90d' => 0,
            'overdue_90d_plus' => 0,
            'total_receivables' => 0,
            'total_agents' => 0,
            'blocked_agents' => 0,
        ];

        foreach ($agents as $agent) {
            $aging = $this->getAgingData($agent->id);
            
            if ($aging['total_receivables'] > 0) {
                $summary['total_agents']++;
                $summary['current'] += $aging['current'];
                $summary['overdue_30d'] += $aging['overdue_30d'];
                $summary['overdue_60d'] += $aging['overdue_60d'];
                $summary['overdue_90d'] += $aging['overdue_90d'];
                $summary['overdue_90d_plus'] += $aging['overdue_90d_plus'];
                $summary['total_receivables'] += $aging['total_receivables'];
                
                if (($agent->is_blocked ?? 0) == 1) {
                    $summary['blocked_agents']++;
                }
            }
        }

        return $summary;
    }

    /**
     * Calculate aging buckets for an agent
     * 
     * @param int $agentId
     * @return array
     */
    public function getAgingData(int $agentId): array
    {
        $agent = $this->agentModel->find($agentId);
        if (!$agent) {
            return [
                'current' => 0,
                'overdue_30d' => 0,
                'overdue_60d' => 0,
                'overdue_90d' => 0,
                'overdue_90d_plus' => 0,
                'total_receivables' => 0,
            ];
        }

        $db = \Config\Database::connect();
        $now = new \DateTime();
        
        // Get payment terms (default 30 days if not set)
        $paymentTerms = (int) ($agent->payment_terms ?? 30);
        
        // Get all purchase transactions (mutation_type = '1')
        $purchases = $db->table('agent_paylater')
            ->where('agent_id', $agentId)
            ->where('mutation_type', '1')
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();

        $aging = [
            'current' => 0,
            'overdue_30d' => 0,
            'overdue_60d' => 0,
            'overdue_90d' => 0,
            'overdue_90d_plus' => 0,
            'total_receivables' => 0,
        ];

        foreach ($purchases as $purchase) {
            $amount = (float) ($purchase['amount'] ?? 0);
            if ($amount <= 0) {
                continue;
            }

            // Calculate due date: created_at + payment_terms days
            $createdAt = new \DateTime($purchase['created_at']);
            $dueDate = clone $createdAt;
            $dueDate->modify('+' . $paymentTerms . ' days');
            
            // Calculate days overdue
            $daysOverdue = $now->diff($dueDate)->days;
            
            // Check if overdue (due date is in the past)
            if ($dueDate < $now) {
                // Overdue
                if ($daysOverdue <= 30) {
                    $aging['overdue_30d'] += $amount;
                } elseif ($daysOverdue <= 60) {
                    $aging['overdue_60d'] += $amount;
                } elseif ($daysOverdue <= 90) {
                    $aging['overdue_90d'] += $amount;
                } else {
                    $aging['overdue_90d_plus'] += $amount;
                }
            } else {
                // Current (not yet due)
                $aging['current'] += $amount;
            }
            
            $aging['total_receivables'] += $amount;
        }

        return $aging;
    }

    /**
     * Check agent status (limit exceeded or due date passed)
     * 
     * @param int $agentId
     * @return array
     */
    public function checkAgentStatus(int $agentId): array
    {
        $agent = $this->agentModel->find($agentId);
        if (!$agent) {
            return [
                'status' => 'error',
                'message' => 'Agen tidak ditemukan.'
            ];
        }

        // Calculate current debt
        $db = \Config\Database::connect();
        $debtBalance = $db->table('agent_paylater')
            ->selectSum('amount')
            ->where('agent_id', $agentId)
            ->get()
            ->getRow();

        $currentDebt = (float) ($debtBalance->amount ?? 0);
        $creditLimit = (float) ($agent->credit_limit ?? 0);

        // Check if limit exceeded
        $limitExceeded = $creditLimit > 0 && $currentDebt > $creditLimit;

        // Check if due date passed (oldest unpaid receivable)
        $paymentTerms = (int) ($agent->payment_terms ?? 30);
        $now = new \DateTime();
        
        $oldestPurchase = $db->table('agent_paylater')
            ->where('agent_id', $agentId)
            ->where('mutation_type', '1')
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getRowArray();

        $dueDatePassed = false;
        if ($oldestPurchase) {
            $createdAt = new \DateTime($oldestPurchase['created_at']);
            $dueDate = clone $createdAt;
            $dueDate->modify('+' . $paymentTerms . ' days');
            $dueDatePassed = $dueDate < $now;
        }

        $shouldBlock = $limitExceeded || $dueDatePassed;

        return [
            'status' => 'success',
            'agent_id' => $agentId,
            'current_debt' => $currentDebt,
            'credit_limit' => $creditLimit,
            'limit_exceeded' => $limitExceeded,
            'due_date_passed' => $dueDatePassed,
            'should_block' => $shouldBlock,
            'is_blocked' => ($agent->is_blocked ?? 0) == 1,
        ];
    }

    /**
     * Block agent orders
     * 
     * @param int $agentId
     * @return ResponseInterface
     */
    public function blockAgent(int $agentId): ResponseInterface
    {
        $isAjax = $this->request->isAJAX();

        try {
            // Only admins can block agents
            $isAdmin = $this->hasPermission('update_all');
            if (!$isAdmin) {
                return $this->handleResponse('Anda tidak memiliki permission untuk memblokir agen.', $isAjax, false);
            }

            $agent = $this->agentModel->find($agentId);
            if (!$agent) {
                return $this->handleResponse('Agen tidak ditemukan.', $isAjax, false);
            }

            // Check status
            $status = $this->checkAgentStatus($agentId);
            if (!$status['should_block']) {
                return $this->handleResponse('Agen tidak memenuhi kondisi untuk diblokir.', $isAjax, false);
            }

            $reason = 'Limit kredit terlampaui atau jatuh tempo pembayaran.';
            if ($status['limit_exceeded']) {
                $reason = 'Limit kredit terlampaui. Hutang: Rp ' . number_format($status['current_debt'], 0, ',', '.') . ', Limit: Rp ' . number_format($status['credit_limit'], 0, ',', '.');
            } elseif ($status['due_date_passed']) {
                $reason = 'Jatuh tempo pembayaran telah terlewati.';
            }

            $this->agentModel->skipValidation(true);
            $result = $this->agentModel->update($agentId, [
                'is_blocked' => 1,
                'blocked_reason' => $reason,
                'blocked_at' => date('Y-m-d H:i:s'),
            ]);
            $this->agentModel->skipValidation(false);

            if (!$result) {
                throw new \Exception('Gagal memblokir agen.');
            }

            return $this->handleResponse('Agen berhasil diblokir.', $isAjax, true);
        } catch (\Exception $e) {
            return $this->handleResponse('Gagal memblokir agen: ' . $e->getMessage(), $isAjax, false);
        }
    }

    /**
     * Unblock agent orders
     * 
     * @param int $agentId
     * @return ResponseInterface
     */
    public function unblockAgent(int $agentId): ResponseInterface
    {
        $isAjax = $this->request->isAJAX();

        try {
            // Only admins can unblock agents
            $isAdmin = $this->hasPermission('update_all');
            if (!$isAdmin) {
                return $this->handleResponse('Anda tidak memiliki permission untuk membuka blokir agen.', $isAjax, false);
            }

            $agent = $this->agentModel->find($agentId);
            if (!$agent) {
                return $this->handleResponse('Agen tidak ditemukan.', $isAjax, false);
            }

            $this->agentModel->skipValidation(true);
            $result = $this->agentModel->update($agentId, [
                'is_blocked' => 0,
                'blocked_reason' => null,
                'blocked_at' => null,
            ]);
            $this->agentModel->skipValidation(false);

            if (!$result) {
                throw new \Exception('Gagal membuka blokir agen.');
            }

            return $this->handleResponse('Blokir agen berhasil dibuka.', $isAjax, true);
        } catch (\Exception $e) {
            return $this->handleResponse('Gagal membuka blokir agen: ' . $e->getMessage(), $isAjax, false);
        }
    }

    /**
     * Send payment reminder
     * 
     * @param int $agentId
     * @return ResponseInterface
     */
    public function sendReminder(int $agentId): ResponseInterface
    {
        $isAjax = $this->request->isAJAX();

        try {
            // Only admins can send reminders
            $isAdmin = $this->hasPermission('update_all');
            if (!$isAdmin) {
                return $this->handleResponse('Anda tidak memiliki permission untuk mengirim reminder.', $isAjax, false);
            }

            $agent = $this->agentModel->find($agentId);
            if (!$agent) {
                return $this->handleResponse('Agen tidak ditemukan.', $isAjax, false);
            }

            // Get aging data
            $aging = $this->getAgingData($agentId);
            $status = $this->checkAgentStatus($agentId);

            // TODO: Implement actual email/notification sending
            // For now, just update last_reminder_sent timestamp
            $this->agentModel->skipValidation(true);
            $result = $this->agentModel->update($agentId, [
                'last_reminder_sent' => date('Y-m-d H:i:s'),
            ]);
            $this->agentModel->skipValidation(false);

            if (!$result) {
                throw new \Exception('Gagal mengirim reminder.');
            }

            // Log reminder (could be stored in a reminders table)
            log_message('info', 'Payment reminder sent to agent ID: ' . $agentId . ', Debt: Rp ' . number_format($status['current_debt'], 0, ',', '.'));

            return $this->handleResponse('Reminder pembayaran berhasil dikirim.', $isAjax, true);
        } catch (\Exception $e) {
            return $this->handleResponse('Gagal mengirim reminder: ' . $e->getMessage(), $isAjax, false);
        }
    }

    /**
     * Get DataTables data for receivables list
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
            // Only admins can access this endpoint
            $isAdmin = $this->hasPermission('read_all');
            if (!$isAdmin) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Anda tidak memiliki permission untuk melihat data piutang.',
                ])->setStatusCode(403);
            }

            $draw   = (int) ($this->request->getPost('draw') ?? $this->request->getGet('draw') ?? 0);
            $start  = (int) ($this->request->getPost('start') ?? $this->request->getGet('start') ?? 0);
            $length = (int) ($this->request->getPost('length') ?? $this->request->getGet('length') ?? 10);

            if ($start < 0) {
                $start = 0;
            }
            if ($length < 1 || $length > 100) {
                $length = 10;
            }

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
            
            // Build base query for agents with receivables
            $builder = $db->table('agent')
                ->select('agent.*, 
                    COALESCE(SUM(CASE WHEN agent_paylater.mutation_type = "1" THEN agent_paylater.amount ELSE 0 END), 0) as total_receivables')
                ->join('agent_paylater', 'agent_paylater.agent_id = agent.id', 'left')
                ->where('agent.is_active', '1')
                ->groupBy('agent.id')
                ->having('total_receivables >', 0);

            // Count total records
            $totalRecords = $db->table('agent')
                ->select('agent.id')
                ->join('agent_paylater', 'agent_paylater.agent_id = agent.id', 'left')
                ->where('agent.is_active', '1')
                ->groupBy('agent.id')
                ->having('COALESCE(SUM(CASE WHEN agent_paylater.mutation_type = "1" THEN agent_paylater.amount ELSE 0 END), 0) >', 0)
                ->countAllResults(false);

            // Apply search filter
            $totalFiltered = $totalRecords;
            if (!empty($searchValue)) {
                $builder->groupStart()
                      ->like('agent.name', $searchValue)
                      ->orLike('agent.code', $searchValue)
                      ->orLike('agent.email', $searchValue)
                      ->groupEnd();

                $countBuilder = $db->table('agent')
                    ->select('agent.id')
                    ->join('agent_paylater', 'agent_paylater.agent_id = agent.id', 'left')
                    ->where('agent.is_active', '1')
                    ->groupStart()
                      ->like('agent.name', $searchValue)
                      ->orLike('agent.code', $searchValue)
                      ->orLike('agent.email', $searchValue)
                      ->groupEnd()
                    ->groupBy('agent.id')
                    ->having('COALESCE(SUM(CASE WHEN agent_paylater.mutation_type = "1" THEN agent_paylater.amount ELSE 0 END), 0) >', 0);

                $totalFiltered = $countBuilder->countAllResults();
            }

            // Get data with pagination
            $data = $builder->orderBy('total_receivables', 'DESC')
                          ->limit($length, $start)
                          ->get()
                          ->getResultArray();

            // Format for DataTables
            $result = [];
            $no = $start + 1;

            foreach ($data as $row) {
                $agentId = (int) $row['id'];
                $aging = $this->getAgingData($agentId);
                $status = $this->checkAgentStatus($agentId);

                $blockButton = '';
                if (($row['is_blocked'] ?? 0) == 1) {
                    $blockButton = '<button class="btn btn-sm btn-warning unblock-agent" data-agent-id="' . $agentId . '" title="Buka Blokir">';
                    $blockButton .= '<i class="fas fa-unlock me-1"></i> Buka Blokir</button>';
                } else {
                    if ($status['should_block']) {
                        $blockButton = '<button class="btn btn-sm btn-danger block-agent" data-agent-id="' . $agentId . '" title="Blokir">';
                        $blockButton .= '<i class="fas fa-lock me-1"></i> Blokir</button>';
                    }
                }

                $reminderButton = '<button class="btn btn-sm btn-info send-reminder" data-agent-id="' . $agentId . '" title="Kirim Reminder">';
                $reminderButton .= '<i class="fas fa-envelope me-1"></i> Reminder</button>';

                $actions = '<div class="btn-group">' . $blockButton . $reminderButton . '</div>';

                $result[] = [
                    'ignore_search_urut'    => $no,
                    'agent_code'            => esc($row['code'] ?? '-'),
                    'agent_name'            => esc($row['name'] ?? '-'),
                    'total_receivables'     => 'Rp ' . number_format((float)$row['total_receivables'], 0, ',', '.'),
                    'current'               => 'Rp ' . number_format($aging['current'], 0, ',', '.'),
                    'overdue_30d'           => 'Rp ' . number_format($aging['overdue_30d'], 0, ',', '.'),
                    'overdue_60d'           => 'Rp ' . number_format($aging['overdue_60d'], 0, ',', '.'),
                    'overdue_90d'           => 'Rp ' . number_format($aging['overdue_90d'], 0, ',', '.'),
                    'overdue_90d_plus'      => 'Rp ' . number_format($aging['overdue_90d_plus'], 0, ',', '.'),
                    'status'                => (($row['is_blocked'] ?? 0) == 1) 
                                                ? '<span class="badge bg-danger">Diblokir</span>' 
                                                : ($status['should_block'] 
                                                    ? '<span class="badge bg-warning">Perlu Diblokir</span>' 
                                                    : '<span class="badge bg-success">Normal</span>'),
                    'ignore_search_action'  => $actions,
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
                'Receivables::getDataDT error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString()
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
     * Handle response (AJAX or redirect)
     * 
     * @param string $message
     * @param bool $isAjax
     * @param bool $success
     * @return ResponseInterface
     */
    private function handleResponse(string $message, bool $isAjax, bool $success): ResponseInterface
    {
        if ($isAjax) {
            return $this->response->setJSON([
                'status' => $success ? 'success' : 'error',
                'message' => $message
            ]);
        }

        return redirect()->to('receivables')->with('message', [
            'status' => $success ? 'success' : 'error',
            'message' => $message
        ]);
    }
}

