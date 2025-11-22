<?php

namespace App\Controllers\Warranty;

use CodeIgniter\HTTP\ResponseInterface;

class Review extends BaseWarrantyController
{
    public function list(): void
    {
        // Only admins can see pending claims list
        $isAdmin = $this->hasPermission('read_all');
        if (!$isAdmin) {
            $this->printError('Anda tidak memiliki permission untuk melihat daftar klaim pending.');
            return;
        }

        $this->data = array_merge($this->data, [
            'title'         => 'Daftar Klaim Pending',
            'currentModule' => $this->currentModule,
            'config'        => $this->config,
            'msg'           => $this->session->getFlashdata('message'),
        ]);

        $this->data['breadcrumb'] = [
            'Home'                => $this->config->baseURL,
            'Warranty'            => $this->config->baseURL . 'warranty/history',
            'Daftar Klaim Pending' => '',
        ];

        $this->view('warranty/claim-list', $this->data);
    }

    /**
     * Get DataTables data for pending warranty claims list
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
                    'message' => 'Anda tidak memiliki permission untuk melihat daftar klaim pending.',
                ])->setStatusCode(403);
            }

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
            
            // Build base query for pending claims that are validated (ready for store review)
            $builder = $db->table('warranty_claim')
                ->select('warranty_claim.*, item_sn.sn as serial_number, agent.name as agent_name')
                ->join('item_sn', 'item_sn.id = warranty_claim.old_sn_id', 'left')
                ->join('agent', 'agent.id = warranty_claim.agent_id', 'left')
                ->where('warranty_claim.status', 'pending')
                ->where('warranty_claim.system_validated', 1);

            // Count total records
            $totalRecords = $builder->countAllResults(false);

            // Apply search filter
            $totalFiltered = $totalRecords;
            if (!empty($searchValue)) {
                $builder->groupStart()
                      ->like('warranty_claim.id', $searchValue)
                      ->orLike('item_sn.sn', $searchValue)
                      ->orLike('agent.name', $searchValue)
                      ->orLike('warranty_claim.issue_reason', $searchValue)
                      ->groupEnd();

                // Clone query for count
                $countBuilder = $db->table('warranty_claim')
                    ->select('warranty_claim.id')
                    ->join('item_sn', 'item_sn.id = warranty_claim.old_sn_id', 'left')
                    ->join('agent', 'agent.id = warranty_claim.agent_id', 'left')
                    ->where('warranty_claim.status', 'pending')
                    ->where('warranty_claim.system_validated', 1)
                    ->groupStart()
                      ->like('warranty_claim.id', $searchValue)
                      ->orLike('item_sn.sn', $searchValue)
                      ->orLike('agent.name', $searchValue)
                      ->orLike('warranty_claim.issue_reason', $searchValue)
                      ->groupEnd();

                $totalFiltered = $countBuilder->countAllResults();
            }

            // Get data with pagination
            $data = $builder->orderBy('warranty_claim.created_at', 'DESC')
                          ->limit($length, $start)
                          ->get()
                          ->getResultArray();

            // Format for DataTables
            $result = [];
            $no = $start + 1;

            foreach ($data as $row) {
                $issueReason = $row['issue_reason'] ?? '-';
                if (strlen($issueReason) > 50) {
                    $issueReason = substr($issueReason, 0, 50) . '...';
                }

                $actionButton = '<a href="' . $this->config->baseURL . 'warranty/review/' . $row['id'] . '" ';
                $actionButton .= 'class="btn btn-sm btn-primary" title="Review">';
                $actionButton .= '<i class="fas fa-eye me-1"></i> Review</a>';

                $result[] = [
                    'ignore_search_urut'    => $no,
                    'id'                    => '#' . $row['id'],
                    'serial_number'         => esc($row['serial_number'] ?? '-'),
                    'agent_name'            => esc($row['agent_name'] ?? '-'),
                    'issue_reason'           => esc($issueReason),
                    'created_at'            => !empty($row['created_at'])
                                                ? tgl_indo8($row['created_at'])
                                                : '-',
                    'ignore_search_action'  => $actionButton,
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
                'Warranty\Review::getDataDT error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString()
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

    public function index(?int $claimId = null)
    {
        if ($claimId === null || $claimId <= 0) {
            return redirect()->to('warranty/history')->with('message', [
                'status' => 'error',
                'message' => 'ID klaim tidak valid.'
            ]);
        }

        try {
            $claim = $this->warrantyClaimModel->find($claimId);
            if (!$claim) {
                return redirect()->to('warranty/history')->with('message', [
                    'status' => 'error',
                    'message' => 'Klaim tidak ditemukan.'
                ]);
            }

            $oldSn = $claim->old_sn_id ? $this->itemSnModel->find($claim->old_sn_id) : null;
            $item = null;
            if ($oldSn && $oldSn->item_id) {
                $itemModel = new \App\Models\ItemModel();
                $item = $itemModel->find($oldSn->item_id);
            }

            $this->data = array_merge($this->data, [
                'title'         => 'Review Klaim Garansi',
                'currentModule' => $this->currentModule,
                'config'        => $this->config,
                'msg'           => $this->session->getFlashdata('message'),
                'claim'         => $claim,
                'old_sn'        => $oldSn,
                'item'          => $item,
            ]);

            $this->data['breadcrumb'] = [
                'Home'         => $this->config->baseURL,
                'Warranty'     => $this->config->baseURL . 'warranty/history',
                'Review Klaim' => '',
            ];

            $this->view('warranty/claim-review', $this->data);
        } catch (\Exception $e) {
            return redirect()->to('warranty/history')->with('message', [
                'status' => 'error',
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ]);
        }
    }

    public function approve(int $claimId)
    {
        $isAjax = $this->request->isAJAX();

        try {
            if ($claimId <= 0) {
                return $this->handleAjaxResponse('ID klaim tidak valid.', $isAjax);
            }

            $claim = $this->warrantyClaimModel->find($claimId);
            if (!$claim) {
                return $this->handleAjaxResponse('Klaim tidak ditemukan.', $isAjax);
            }

            // Require new_sn_id for replacement (integrated approve + replacement flow)
            $newSnId = $this->request->getPost('new_sn_id');
            if (empty($newSnId)) {
                return $this->handleAjaxResponse('Serial number pengganti harus dipilih.', $isAjax, true);
            }

            $oldSn = $claim->old_sn_id ? $this->itemSnModel->find($claim->old_sn_id) : null;
            if (!$oldSn) {
                return $this->handleAjaxResponse('Serial number lama tidak ditemukan.', $isAjax);
            }

            $newSn = $this->itemSnModel->find($newSnId);
            if (!$newSn) {
                return $this->handleAjaxResponse('Serial number pengganti tidak ditemukan.', $isAjax, true);
            }

            // Validate new SN is available for replacement
            if ($newSn->is_sell != '0') {
                return $this->handleAjaxResponse('Serial number pengganti sudah terjual.', $isAjax, true);
            }
            if ($newSn->is_activated != '0') {
                return $this->handleAjaxResponse('Serial number pengganti sudah diaktifkan.', $isAjax, true);
            }
            if (!empty($newSn->expired_at)) {
                $newExpiredAt = new \DateTime($newSn->expired_at);
                $now = new \DateTime();
                if ($newExpiredAt <= $now) {
                    return $this->handleAjaxResponse('Serial number pengganti sudah kadaluarsa.', $isAjax, true);
                }
            }

            $storeNote = $this->request->getPost('store_note');

            // Start transaction for approve + replacement
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // 1. Update claim: set store_approved=1
                $updateData = [
                    'store_approved' => 1,
                ];
                if ($storeNote) {
                    $updateData['store_note'] = $storeNote;
                }

                $this->warrantyClaimModel->skipValidation(true);
                $result = $this->warrantyClaimModel->update($claimId, $updateData);
                $this->warrantyClaimModel->skipValidation(false);

                if (!$result) {
                    throw new \Exception('Gagal memperbarui status klaim.');
                }

                // 2. Create replacement record
                $replacementData = [
                    'claim_id'    => $claimId,
                    'new_sn_id'   => $newSnId,
                    'replaced_at' => date('Y-m-d H:i:s'),
                ];

                $this->warrantyClaimReplacementModel->skipValidation(true);
                $replacementId = $this->warrantyClaimReplacementModel->insert($replacementData);
                $this->warrantyClaimReplacementModel->skipValidation(false);

                if (!$replacementId) {
                    throw new \Exception('Gagal membuat record penggantian.');
                }

                // 3. Create history record
                $historyData = [
                    'claim_id' => $claimId,
                    'old_sn_id' => $oldSn->id,
                    'new_sn_id' => $newSnId,
                    'action' => 'replacement',
                ];

                $this->warrantySNHistoryModel->skipValidation(true);
                $historyId = $this->warrantySNHistoryModel->insert($historyData);
                $this->warrantySNHistoryModel->skipValidation(false);

                if (!$historyId) {
                    throw new \Exception('Gagal membuat record history.');
                }

                // 4. Update new SN: activate, set warranty to follow old SN (remaining time)
                $this->itemSnModel->skipValidation(true);
                $newSnUpdateResult = $this->itemSnModel->update($newSnId, [
                    'is_activated' => '1',
                    'activated_at' => date('Y-m-d H:i:s'),
                    'expired_at'   => $oldSn->expired_at, // Warranty follows old SN (remaining time)
                    'is_sell'      => '1', // Deduct stock: mark as sold
                ]);
                $this->itemSnModel->skipValidation(false);

                if (!$newSnUpdateResult) {
                    throw new \Exception('Gagal memperbarui serial number pengganti.');
                }

                // 5. Update old SN: mark as replaced
                $oldSnUpdateResult = $this->itemSnModel->update($oldSn->id, [
                    'replaced_at' => date('Y-m-d H:i:s'),
                    'sn_replaced' => $newSn->sn,
                ]);

                if (!$oldSnUpdateResult) {
                    throw new \Exception('Gagal memperbarui serial number lama.');
                }

                // 6. Create reconciliation record (auto reconciliation between stores/warehouses)
                $reconciliationData = [
                    'claim_id'      => $claimId,
                    'from_store_id' => $oldSn->agent_id,
                    'to_store_id'   => $newSn->agent_id ?? $claim->agent_id,
                    'sn_id'         => $newSnId,
                    'reconciled_at' => date('Y-m-d H:i:s'),
                ];

                $this->warrantyStockReconciliationModel->skipValidation(true);
                $reconciliationId = $this->warrantyStockReconciliationModel->insert($reconciliationData);
                $this->warrantyStockReconciliationModel->skipValidation(false);

                if (!$reconciliationId) {
                    throw new \Exception('Gagal membuat record rekonsiliasi stok.');
                }

                // 7. Update claim status to 'replaced' (final status)
                $this->warrantyClaimModel->skipValidation(true);
                $claimUpdateResult = $this->warrantyClaimModel->update($claimId, ['status' => 'replaced']);
                $this->warrantyClaimModel->skipValidation(false);

                if (!$claimUpdateResult) {
                    throw new \Exception('Gagal memperbarui status klaim.');
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Transaksi gagal.');
                }

                $message = 'Klaim berhasil disetujui dan penggantian serial number berhasil diproses.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'success', 'message' => $message]);
                }

                return redirect()->to("warranty/detail/{$claimId}")->with('message', [
                    'status' => 'success',
                    'message' => $message
                ]);
            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return $this->handleAjaxResponse('Gagal menyetujui klaim: ' . $e->getMessage(), $isAjax, true);
        }
    }

    public function reject(int $claimId)
    {
        $isAjax = $this->request->isAJAX();

        try {
            if ($claimId <= 0) {
                return $this->handleAjaxResponse('ID klaim tidak valid.', $isAjax);
            }

            $claim = $this->warrantyClaimModel->find($claimId);
            if (!$claim) {
                return $this->handleAjaxResponse('Klaim tidak ditemukan.', $isAjax);
            }

            $storeNote = $this->request->getPost('store_note');
            if (empty($storeNote)) {
                return $this->handleAjaxResponse('Catatan penolakan harus diisi.', $isAjax, true);
            }

            $updateData = [
                'status'     => 'rejected',
                'store_note' => $storeNote,
            ];

            $this->warrantyClaimModel->skipValidation(true);
            $result = $this->warrantyClaimModel->update($claimId, $updateData);
            $this->warrantyClaimModel->skipValidation(false);

            if (!$result) {
                throw new \Exception('Gagal memperbarui status klaim.');
            }

            $message = 'Klaim berhasil ditolak.';
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'success', 'message' => $message]);
            }

            return redirect()->to("warranty/review/{$claimId}")->with('message', [
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return $this->handleAjaxResponse('Gagal menolak klaim: ' . $e->getMessage(), $isAjax, true);
        }
    }

    public function processReplacement(int $claimId)
    {
        $isAjax = $this->request->isAJAX();

        try {
            if ($claimId <= 0) {
                return $this->handleAjaxResponse('ID klaim tidak valid.', $isAjax);
            }

            $newSnId = $this->request->getPost('new_sn_id');
            if (empty($newSnId)) {
                return $this->handleAjaxResponse('Serial number pengganti harus dipilih.', $isAjax, true);
            }

            $claim = $this->warrantyClaimModel->find($claimId);
            if (!$claim) {
                return $this->handleAjaxResponse('Klaim tidak ditemukan.', $isAjax);
            }

            $oldSn = $claim->old_sn_id ? $this->itemSnModel->find($claim->old_sn_id) : null;
            if (!$oldSn) {
                return $this->handleAjaxResponse('Serial number lama tidak ditemukan.', $isAjax);
            }

            $newSn = $this->itemSnModel->find($newSnId);
            if (!$newSn) {
                return $this->handleAjaxResponse('Serial number pengganti tidak ditemukan.', $isAjax, true);
            }

            if ($newSn->is_sell != '0') {
                return $this->handleAjaxResponse('Serial number pengganti sudah terjual.', $isAjax, true);
            }
            if ($newSn->is_activated != '0') {
                return $this->handleAjaxResponse('Serial number pengganti sudah diaktifkan.', $isAjax, true);
            }
            if (!empty($newSn->expired_at)) {
                $newExpiredAt = new \DateTime($newSn->expired_at);
                $now = new \DateTime();
                if ($newExpiredAt <= $now) {
                    return $this->handleAjaxResponse('Serial number pengganti sudah kadaluarsa.', $isAjax, true);
                }
            }

            $db = \Config\Database::connect();
            $db->transStart();

            try {
                $replacementData = [
                    'claim_id'    => $claimId,
                    'new_sn_id'   => $newSnId,
                    'replaced_at' => date('Y-m-d H:i:s'),
                ];

                $this->warrantyClaimReplacementModel->skipValidation(true);
                $replacementId = $this->warrantyClaimReplacementModel->insert($replacementData);
                $this->warrantyClaimReplacementModel->skipValidation(false);

                if (!$replacementId) {
                    throw new \Exception('Gagal membuat record penggantian.');
                }

                $historyData = [
                    'claim_id' => $claimId,
                    'old_sn_id' => $oldSn->id,
                    'new_sn_id' => $newSnId,
                    'action' => 'replacement',
                ];

                $this->warrantySNHistoryModel->skipValidation(true);
                $historyId = $this->warrantySNHistoryModel->insert($historyData);
                $this->warrantySNHistoryModel->skipValidation(false);

                if (!$historyId) {
                    throw new \Exception('Gagal membuat record history.');
                }

                $this->itemSnModel->skipValidation(true);
                $newSnUpdateResult = $this->itemSnModel->update($newSnId, [
                    'is_activated' => '1',
                    'activated_at' => date('Y-m-d H:i:s'),
                    'expired_at'   => $oldSn->expired_at,
                ]);
                $this->itemSnModel->skipValidation(false);

                if (!$newSnUpdateResult) {
                    throw new \Exception('Gagal memperbarui serial number pengganti.');
                }

                $oldSnUpdateResult = $this->itemSnModel->update($oldSn->id, [
                    'replaced_at' => date('Y-m-d H:i:s'),
                    'sn_replaced' => $newSn->sn,
                ]);

                if (!$oldSnUpdateResult) {
                    throw new \Exception('Gagal memperbarui serial number lama.');
                }

                $reconciliationData = [
                    'claim_id'      => $claimId,
                    'from_store_id' => $oldSn->agent_id,
                    'to_store_id'   => $newSn->agent_id ?? $claim->agent_id,
                    'sn_id'         => $newSnId,
                    'reconciled_at' => date('Y-m-d H:i:s'),
                ];

                $this->warrantyStockReconciliationModel->skipValidation(true);
                $reconciliationId = $this->warrantyStockReconciliationModel->insert($reconciliationData);
                $this->warrantyStockReconciliationModel->skipValidation(false);

                if (!$reconciliationId) {
                    throw new \Exception('Gagal membuat record rekonsiliasi stok.');
                }

                $this->warrantyClaimModel->skipValidation(true);
                $claimUpdateResult = $this->warrantyClaimModel->update($claimId, ['status' => 'replaced']);
                $this->warrantyClaimModel->skipValidation(false);

                if (!$claimUpdateResult) {
                    throw new \Exception('Gagal memperbarui status klaim.');
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Transaksi gagal.');
                }

                $message = 'Penggantian serial number berhasil diproses.';
                if ($isAjax) {
                    return $this->response->setJSON(['status' => 'success', 'message' => $message]);
                }

                return redirect()->to("warranty/detail/{$claimId}")->with('message', [
                    'status' => 'success',
                    'message' => $message
                ]);
            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return $this->handleAjaxResponse('Gagal memproses penggantian: ' . $e->getMessage(), $isAjax, true);
        }
    }

    private function handleAjaxResponse(string $message, bool $isAjax, bool $withInput = false)
    {
        if ($isAjax) {
            return $this->response->setJSON(['status' => 'error', 'message' => $message]);
        }
        $redirect = redirect()->back();
        if ($withInput) {
            $redirect = $redirect->withInput();
        }
        return $redirect->with('message', [
            'status' => 'error',
            'message' => $message
        ]);
    }
}

