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

    /**
     * System validation of warranty claim
     * Validates serial number (warranty, status) and sets system_validated flag
     * Called automatically after claim submission or manually by admin
     * 
     * @param int $claimId
     * @return bool True if valid, false if invalid
     */
    protected function validateClaim(int $claimId): bool
    {
        try {
            $claim = $this->warrantyClaimModel->find($claimId);
            if (!$claim) {
                return false;
            }

            // Skip if already validated
            if ($claim->system_validated == 1) {
                return true;
            }

            $oldSn = $claim->old_sn_id ? $this->itemSnModel->find($claim->old_sn_id) : null;
            if (!$oldSn) {
                // Invalid: Serial number not found
                $this->warrantyClaimModel->skipValidation(true);
                $this->warrantyClaimModel->update($claimId, [
                    'status' => 'invalid',
                    'system_validation_note' => 'Serial number tidak ditemukan.',
                ]);
                $this->warrantyClaimModel->skipValidation(false);
                return false;
            }

            $validationErrors = [];

            // Validate: Serial must be activated
            if ($oldSn->is_activated != '1') {
                $validationErrors[] = 'Serial number belum diaktifkan.';
            }

            // Validate: Serial must be sold
            if ($oldSn->is_sell != '1') {
                $validationErrors[] = 'Serial number belum terjual.';
            }

            // Validate: Serial must have warranty period
            if (empty($oldSn->expired_at)) {
                $validationErrors[] = 'Serial number tidak memiliki masa garansi.';
            } else {
                // Validate: Warranty must not be expired
                $expiredAt = new \DateTime($oldSn->expired_at);
                $now = new \DateTime();
                if ($expiredAt <= $now) {
                    $validationErrors[] = 'Masa garansi serial number sudah habis.';
                }
            }

            if (!empty($validationErrors)) {
                // Invalid: Set status and validation note
                $this->warrantyClaimModel->skipValidation(true);
                $this->warrantyClaimModel->update($claimId, [
                    'status' => 'invalid',
                    'system_validation_note' => implode(' ', $validationErrors),
                ]);
                $this->warrantyClaimModel->skipValidation(false);
                return false;
            }

            // Valid: Set system_validated=1, keep status='pending', route to store
            $this->warrantyClaimModel->skipValidation(true);
            $this->warrantyClaimModel->update($claimId, [
                'system_validated' => 1,
                'routed_store_id' => $oldSn->agent_id, // Route to store that owns the stock
                // Keep status='pending' for store review
            ]);
            $this->warrantyClaimModel->skipValidation(false);

            return true;
        } catch (\Exception $e) {
            log_message('error', 'BaseWarrantyController::validateClaim error: ' . $e->getMessage());
            return false;
        }
    }
}

