<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Model for managing warranty claims
 * This file represents the Model for WarrantyClaim.
 */
class WarrantyClaimModel extends Model
{
    protected $table = 'warranty_claim';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'agent_id',
        'old_sn_id',
        'issue_reason',
        'photo_path',
        'system_validated',
        'system_validation_note',
        'routed_store_id',
        'store_approved',
        'store_note',
        'status',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // No validation rules - skipValidation used in controller
}

