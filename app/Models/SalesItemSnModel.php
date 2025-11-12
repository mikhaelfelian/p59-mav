<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Model for managing sales item serial numbers with CRUD operations
 * This file represents the Model for SalesItemSnModel.
 */

namespace App\Models;

use CodeIgniter\Model;

class SalesItemSnModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sales_item_sn';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sales_item_id',
        'item_sn_id',
        'sn',
        'no_hp',
        'plat_code',
        'plat_number',
        'plat_last',
        'file',
        'activated_at',
        'expired_at'
    ];

    // Dates
    protected $useTimestamps = true; // Enable timestamps for created_at and updated_at
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'sales_item_id' => 'required|integer',
        'item_sn_id' => 'required|integer',
        'sn' => 'required|max_length[100]',
        'no_hp' => 'permit_empty|max_length[20]',
        'plat_code' => 'permit_empty|max_length[10]',
        'plat_number' => 'permit_empty|max_length[10]',
        'plat_last' => 'permit_empty|max_length[10]',
        'file' => 'permit_empty|max_length[255]',
        'activated_at' => 'permit_empty|valid_date',
        'expired_at' => 'permit_empty|valid_date'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get SNs for a specific sales item
     * 
     * @param int $salesItemId Sales item ID
     * @return array Array of SN records
     */
    public function getSnBySalesItem($salesItemId)
    {
        return $this->select('sales_item_sn.*, item_sn.sn as original_sn')
            ->join('item_sn', 'item_sn.id = sales_item_sn.item_sn_id', 'left')
            ->where('sales_item_sn.sales_item_id', $salesItemId)
            ->orderBy('sales_item_sn.id', 'ASC')
            ->findAll();
    }

    /**
     * Bulk insert sales item SNs
     * 
     * @param array $sns Array of SN data
     * @return bool True if all SNs inserted successfully
     */
    public function bulkInsert($sns)
    {
        if (empty($sns)) {
            return false;
        }

        try {
            // Skip validation for bulk insert to avoid issues
            $this->skipValidation(true);
            $result = $this->insertBatch($sns);
            $this->skipValidation(false);
            return $result;
        } catch (\Exception $e) {
            $this->skipValidation(false);
            log_message('error', 'SalesItemSnModel::bulkInsert error: ' . $e->getMessage());
            return false;
        }
    }
}

