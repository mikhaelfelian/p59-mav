<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-04
 * Github: github.com/mikhaelfelian
 * Description: Model for managing product rules with CRUD operations
 * This file represents the Model for ProductRuleModel.
 */

namespace App\Models;

use CodeIgniter\Model;

class ProductRuleModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'item_rule';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'item_id',
        'rule_type',
        'threshold_amount',
        'cashback_amount',
        'min_qty',
        'bonus_item_id',
        'bonus_qty',
        'is_multiple',
        'notes',
        'is_active',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'item_id' => 'required|integer|is_natural_no_zero',
        'rule_type' => 'required|in_list[cashback,buy_get]',
        'threshold_amount' => 'permit_empty|decimal',
        'cashback_amount' => 'permit_empty|decimal',
        'min_qty' => 'permit_empty|integer',
        'bonus_item_id' => 'permit_empty|integer|is_natural_no_zero',
        'bonus_qty' => 'permit_empty|integer',
        'is_multiple' => 'in_list[0,1]',
        'is_active' => 'in_list[0,1]',
        'notes' => 'permit_empty'
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
     * Get product rule by item ID
     * 
     * @param int $itemId
     * @return object|null
     */
    public function getByItemId($itemId)
    {
        return $this->where('item_id', $itemId)->first();
    }

    /**
     * Get active product rule by item ID
     * 
     * @param int $itemId
     * @return object|null
     */
    public function getActiveByItemId($itemId)
    {
        return $this->where('item_id', $itemId)
                    ->where('is_active', 1)
                    ->first();
    }
}

