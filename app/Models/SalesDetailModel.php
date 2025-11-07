<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-05
 * Github: github.com/mikhaelfelian
 * Description: Model for managing sales detail with CRUD operations
 * This file represents the Model for SalesDetailModel.
 */

namespace App\Models;

use CodeIgniter\Model;

class SalesDetailModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sales_detail';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sale_id',
        'item_id',
        'variant_id',
        'sn',
        'item',
        'price',
        'qty',
        'disc',
        'amount'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'sale_id' => 'required|integer',
        'item_id' => 'required|integer',
        'variant_id' => 'permit_empty|integer',
        'sn' => 'permit_empty|max_length[255]',
        'item' => 'permit_empty|max_length[255]',
        'price' => 'required|decimal',
        'qty' => 'required|integer|greater_than[0]',
        'disc' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'amount' => 'required|decimal|greater_than_equal_to[0]'
    ];
    protected $validationMessages   = [
        'sale_id' => [
            'required' => 'Sale ID is required',
            'integer' => 'Sale ID must be an integer'
        ],
        'item_id' => [
            'required' => 'Item ID is required',
            'integer' => 'Item ID must be an integer'
        ],
        'price' => [
            'required' => 'Price is required',
            'decimal' => 'Price must be a decimal number'
        ],
        'qty' => [
            'required' => 'Quantity is required',
            'integer' => 'Quantity must be an integer',
            'greater_than' => 'Quantity must be greater than 0'
        ],
        'amount' => [
            'required' => 'Amount is required',
            'decimal' => 'Amount must be a decimal number',
            'greater_than_equal_to' => 'Amount must be greater than or equal to 0'
        ]
    ];
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
     * Get details for a specific sale
     * 
     * @param int $saleId Sale ID
     * @return array Array of sales details with related data
     */
    public function getDetailsBySale($saleId)
    {
        return $this->select('sales_detail.*, 
            item.name as item_name,
            item.sku as item_sku,
            item_variant.variant_name,
            item_variant.sku_variant')
            ->join('item', 'item.id = sales_detail.item_id', 'left')
            ->join('item_variant', 'item_variant.id = sales_detail.variant_id', 'left')
            ->where('sales_detail.sale_id', $saleId)
            ->orderBy('sales_detail.id', 'ASC')
            ->findAll();
    }

    /**
     * Bulk insert sales details
     * 
     * @param array $details Array of sales details
     * @return bool True if all details inserted successfully
     */
    public function bulkInsert($details)
    {
        if (empty($details)) {
            log_message('error', 'SalesDetailModel::bulkInsert - Empty details array provided');
            return false;
        }

        try {
            // Skip validation for bulk insert to avoid issues
            $this->skipValidation(true);
            
            // Use insertBatch which handles multiple inserts efficiently
            $result = $this->insertBatch($details);
            
            // Get the actual number of inserted rows
            $insertedRows = $this->db->affectedRows();
            
            log_message('debug', 'SalesDetailModel::bulkInsert - Attempted: ' . count($details) . ', Inserted rows: ' . $insertedRows);
            
            $this->skipValidation(false);
            
            // Check if all details were inserted
            if ($result && $insertedRows > 0) {
                return true;
            }
            
            // If insertBatch returned true but no rows affected, something is wrong
            if ($result && $insertedRows === 0) {
                log_message('error', 'SalesDetailModel::bulkInsert - insertBatch returned true but no rows affected');
                return false;
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->skipValidation(false);
            log_message('error', 'SalesDetailModel::bulkInsert error: ' . $e->getMessage());
            log_message('error', 'SalesDetailModel::bulkInsert stack trace: ' . $e->getTraceAsString());
            
            // Get database error
            $dbError = $this->db->error();
            if (!empty($dbError['message'])) {
                log_message('error', 'SalesDetailModel::bulkInsert DB error: ' . $dbError['message']);
            }
            
            return false;
        }
    }

    /**
     * Get sales details with item information
     * 
     * @param int|null $saleId Sale ID (optional)
     * @return array|array[] Sales details with related data
     */
    public function getDetailsWithRelations($saleId = null)
    {
        $builder = $this->select('sales_detail.*, 
            item.name as item_name, 
            item.sku as item_sku,
            item_variant.variant_name,
            item_variant.sku_variant')
            ->join('item', 'item.id = sales_detail.item_id', 'left')
            ->join('item_variant', 'item_variant.id = sales_detail.variant_id', 'left');

        if ($saleId) {
            return $builder->where('sales_detail.sale_id', $saleId)->findAll();
        }

        return $builder->orderBy('sales_detail.created_at', 'DESC')->findAll();
    }
}

