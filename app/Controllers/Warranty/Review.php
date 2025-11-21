<?php

namespace App\Controllers\Warranty;

use CodeIgniter\HTTP\ResponseInterface;

class Review extends BaseWarrantyController
{
    public function index(int $claimId)
    {
        if ($claimId <= 0) {
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

            $storeNote = $this->request->getPost('store_note');
            $updateData = [
                'system_validated' => 1,
                'store_approved' => 1,
                'status' => 'approved',
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

            $message = 'Klaim berhasil disetujui.';
            if ($isAjax) {
                return $this->response->setJSON(['status' => 'success', 'message' => $message]);
            }

            return redirect()->to("warranty/review/{$claimId}")->with('message', [
                'status' => 'success',
                'message' => $message
            ]);
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

