<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Model for managing warranty stock reconciliation
 * This file represents the Model for WarrantyStockReconciliation.
 */
class WarrantyStockReconciliationModel extends Model
{
    protected $table = 'warranty_stock_reconciliation';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'claim_id',
        'from_store_id',
        'to_store_id',
        'sn_id',
        'reconciled_at',
        'created_at'
    ];

    // Dates - only created_at, no updated_at
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = null; // No updated_at field
    protected $deletedField = 'deleted_at';
}

