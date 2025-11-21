<?php

namespace App\Controllers\Warranty;

class History extends BaseWarrantyController
{
    public function index(): void
    {
        $isAdmin = $this->hasPermission('read_all');
        $readOwn = $this->hasPermission('read_own');

        $agentId = null;
        if ($readOwn && !$isAdmin) {
            $agentId = $this->getUserAgentId();
        }

        $query = $this->warrantyClaimModel
            ->select('warranty_claim.*, item_sn.sn as serial_number, agent.name as agent_name')
            ->join('item_sn', 'item_sn.id = warranty_claim.old_sn_id', 'left')
            ->join('agent', 'agent.id = warranty_claim.agent_id', 'left')
            ->orderBy('warranty_claim.created_at', 'DESC');

        if ($agentId) {
            $query->where('warranty_claim.agent_id', $agentId);
        }

        $claims = $query->findAll();

        $this->data = array_merge($this->data, [
            'title'         => 'Riwayat Klaim Garansi',
            'currentModule' => $this->currentModule,
            'config'        => $this->config,
            'msg'           => $this->session->getFlashdata('message'),
            'claims'        => $claims,
            'read_all'      => $isAdmin,
        ]);

        $this->data['breadcrumb'] = [
            'Home'                   => $this->config->baseURL,
            'Riwayat Klaim Garansi'  => '',
        ];

        $this->view('warranty/claim-history', $this->data);
    }
}

