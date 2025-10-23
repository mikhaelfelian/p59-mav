<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Model for managing item-specification relationships data
 * This file represents the Model.
 */
class ItemSpecIdModel extends Model
{
    protected $table = 'item_spec_id';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'item_id',
        'item_spec_id',
        'user_id',
        'value'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'item_id' => 'required|integer|is_natural_no_zero',
        'item_spec_id' => 'required|integer|is_natural_no_zero',
        'user_id' => 'required|integer|is_natural_no_zero'
    ];

    protected $validationMessages = [
        'item_id' => [
            'required' => 'Item ID harus diisi',
            'integer' => 'Item ID harus berupa angka',
            'is_natural_no_zero' => 'Item ID harus berupa angka positif'
        ],
        'item_spec_id' => [
            'required' => 'Item Spec ID harus diisi',
            'integer' => 'Item Spec ID harus berupa angka',
            'is_natural_no_zero' => 'Item Spec ID harus berupa angka positif'
        ],
        'user_id' => [
            'required' => 'User ID harus diisi',
            'integer' => 'User ID harus berupa angka',
            'is_natural_no_zero' => 'User ID harus berupa angka positif'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Generate unique 6-digit numeric code for item-specification relationship
     * 
     * @param int $itemId The item ID
     * @param int $specId The specification ID
     * @return string Generated code
     */
    public function generateCode($itemId, $specId)
    {
        // Create a unique identifier from item_id and spec_id
        $uniqueId = $itemId . $specId;
        
        // Get current timestamp for uniqueness
        $timestamp = time();
        
        // Generate 6-digit numeric code
        $code = str_pad(substr($uniqueId . $timestamp, -6), 6, '0', STR_PAD_LEFT);
        
        // Ensure uniqueness by checking if code already exists
        $counter = 1;
        $originalCode = $code;
        
        while ($this->where('code', $code)->countAllResults() > 0) {
            $code = str_pad(substr($originalCode . $counter, -6), 6, '0', STR_PAD_LEFT);
            $counter++;
        }
        
        return $code;
    }

    /**
     * Get specifications for a specific item
     * 
     * @param int $itemId The item ID
     * @return array Array of specification objects
     */
    public function getSpecsForItem($itemId)
    {
        return $this->select('item_spec_id.*, item_spec.name as spec_name, item_spec.description as spec_description')
                   ->join('item_spec', 'item_spec.id = item_spec_id.item_spec_id')
                   ->where('item_spec_id.item_id', $itemId)
                   ->findAll();
    }

    /**
     * Get items for a specific specification
     * 
     * @param int $specId The specification ID
     * @return array Array of item objects
     */
    public function getItemsForSpec($specId)
    {
        return $this->select('item_spec_id.*, item.name as item_name, item.sku as item_sku')
                   ->join('item', 'item.id = item_spec_id.item_id')
                   ->where('item_spec_id.item_spec_id', $specId)
                   ->findAll();
    }

    /**
     * Check if relationship already exists
     * 
     * @param int $itemId The item ID
     * @param int $specId The specification ID
     * @return bool True if relationship exists
     */
    public function relationshipExists($itemId, $specId)
    {
        return $this->where('item_id', $itemId)
                   ->where('item_spec_id', $specId)
                   ->countAllResults() > 0;
    }
}
