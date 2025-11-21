<?php

namespace App\Controllers\Warranty;

class Detail extends BaseWarrantyController
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
            $replacement = $this->warrantyClaimReplacementModel
                ->where('claim_id', $claimId)
                ->first();
            $newSn = ($replacement && $replacement->new_sn_id)
                ? $this->itemSnModel->find($replacement->new_sn_id)
                : null;

            $snHistory = $this->warrantySNHistoryModel
                ->where('claim_id', $claimId)
                ->orderBy('created_at', 'DESC')
                ->findAll();

            $reconciliation = $this->warrantyStockReconciliationModel
                ->where('claim_id', $claimId)
                ->first();

            $item = null;
            if ($oldSn && $oldSn->item_id) {
                $itemModel = new \App\Models\ItemModel();
                $item = $itemModel->find($oldSn->item_id);
            }

            $this->data = array_merge($this->data, [
                'title'         => 'Detail Klaim Garansi',
                'currentModule' => $this->currentModule,
                'config'        => $this->config,
                'msg'           => $this->session->getFlashdata('message'),
                'claim'         => $claim,
                'old_sn'        => $oldSn,
                'new_sn'        => $newSn,
                'replacement'   => $replacement,
                'sn_history'    => $snHistory,
                'reconciliation'=> $reconciliation,
                'item'          => $item,
            ]);

            $this->data['breadcrumb'] = [
                'Home'         => $this->config->baseURL,
                'Warranty'     => $this->config->baseURL . 'warranty/history',
                'Detail Klaim' => '',
            ];

            $this->view('warranty/claim-detail', $this->data);
        } catch (\Exception $e) {
            return redirect()->to('warranty/history')->with('message', [
                'status' => 'error',
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ]);
        }
    }
}

