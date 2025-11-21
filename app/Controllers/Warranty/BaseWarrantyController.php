<?php

namespace App\Controllers\Warranty;

use App\Controllers\BaseController;
use App\Models\WarrantyClaimModel;
use App\Models\WarrantyClaimReplacementModel;
use App\Models\WarrantySNHistoryModel;
use App\Models\WarrantyStockReconciliationModel;
use App\Models\ItemSnModel;
use App\Models\AgentModel;
use App\Models\UserRoleAgentModel;

class BaseWarrantyController extends BaseController
{
    protected $warrantyClaimModel;
    protected $warrantyClaimReplacementModel;
    protected $warrantySNHistoryModel;
    protected $warrantyStockReconciliationModel;
    protected $itemSnModel;
    protected $agentModel;
    protected $userRoleAgentModel;

    public function __construct()
    {
        parent::__construct();
        $this->warrantyClaimModel = new WarrantyClaimModel();
        $this->warrantyClaimReplacementModel = new WarrantyClaimReplacementModel();
        $this->warrantySNHistoryModel = new WarrantySNHistoryModel();
        $this->warrantyStockReconciliationModel = new WarrantyStockReconciliationModel();
        $this->itemSnModel = new ItemSnModel();
        $this->agentModel = new AgentModel();
        $this->userRoleAgentModel = new UserRoleAgentModel();
    }

    protected function getUserAgentId(): ?int
    {
        $userId = $this->user['id_user'] ?? null;
        if (!$userId) {
            return null;
        }

        $userRoleAgent = $this->userRoleAgentModel->where('user_id', $userId)->first();
        return $userRoleAgent ? $userRoleAgent->agent_id : null;
    }
}

