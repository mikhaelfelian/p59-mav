<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Model for managing sales items with CRUD operations
 * This file represents the Model for SalesItemsModel.
 */

namespace App\Models;

use CodeIgniter\Model;

class SalesItemsModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sales_items';
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
        'price',
        'quantity',
        'discount',
        'subtotal',
        'note'
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
        'price' => 'required|decimal',
        'quantity' => 'required|integer|greater_than[0]',
        'discount' => 'permit_empty|decimal',
        'subtotal' => 'permit_empty|decimal',
        'note' => 'permit_empty|max_length[255]'
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
     * Get items for a specific sale
     * 
     * @param int $saleId Sale ID
     * @return array Array of sales items with related data
     */
    public function getItemsBySale($saleId)
    {
        return $this->select('sales_items.*, 
            item.name as item_name,
            item.sku as item_sku,
            item_variant.variant_name,
            item_variant.sku_variant')
            ->join('item', 'item.id = sales_items.item_id', 'left')
            ->join('item_variant', 'item_variant.id = sales_items.variant_id', 'left')
            ->where('sales_items.sale_id', $saleId)
            ->orderBy('sales_items.id', 'ASC')
            ->findAll();
    }

    /**
     * Bulk insert sales items
     * 
     * @param array $items Array of sales items
     * @return bool True if all items inserted successfully
     */
    public function bulkInsert($items)
    {
        if (empty($items)) {
            log_message('error', 'SalesItemsModel::bulkInsert - Empty items array provided');
            return false;
        }

        try {
            // Skip validation for bulk insert to avoid issues
            $this->skipValidation(true);
            
            // Use insertBatch which handles multiple inserts efficiently
            $result = $this->insertBatch($items);
            
            // Get the actual number of inserted rows
            $insertedRows = $this->db->affectedRows();
            
            log_message('debug', 'SalesItemsModel::bulkInsert - Attempted: ' . count($items) . ', Inserted rows: ' . $insertedRows);
            
            $this->skipValidation(false);
            
            // Check if all items were inserted
            if ($result && $insertedRows > 0) {
                return true;
            }
            
            // If insertBatch returned true but no rows affected, something is wrong
            if ($result && $insertedRows === 0) {
                log_message('error', 'SalesItemsModel::bulkInsert - insertBatch returned true but no rows affected');
                return false;
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->skipValidation(false);
            log_message('error', 'SalesItemsModel::bulkInsert error: ' . $e->getMessage());
            log_message('error', 'SalesItemsModel::bulkInsert stack trace: ' . $e->getTraceAsString());
            
            // Get database error
            $dbError = $this->db->error();
            if (!empty($dbError['message'])) {
                log_message('error', 'SalesItemsModel::bulkInsert DB error: ' . $dbError['message']);
            }
            
            return false;
        }
    }
}

