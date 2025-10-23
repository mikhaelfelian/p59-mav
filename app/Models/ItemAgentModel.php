<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Model for managing item agent pricing data with CRUD operations
 * This file represents the Model.
 */
class ItemAgentModel extends Model
{
    protected $table = 'item_agent';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'item_id',
        'user_id',
        'price',
        'is_active'
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
        'user_id' => 'required|integer|is_natural_no_zero',
        'price' => 'required|decimal|greater_than_equal_to[0]',
        'is_active' => 'in_list[0,1]'
    ];

    protected $validationMessages = [
        'item_id' => [
            'required' => 'Item ID harus diisi',
            'integer' => 'Item ID harus berupa angka',
            'is_natural_no_zero' => 'Item ID harus berupa angka positif'
        ],
        'user_id' => [
            'required' => 'User ID harus diisi',
            'integer' => 'User ID harus berupa angka',
            'is_natural_no_zero' => 'User ID harus berupa angka positif'
        ],
        'price' => [
            'required' => 'Harga harus diisi',
            'decimal' => 'Harga harus berupa angka desimal',
            'greater_than_equal_to' => 'Harga harus lebih besar atau sama dengan 0'
        ],
        'is_active' => [
            'in_list' => 'Status aktif harus 0 atau 1'
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
     * Generate unique 6-digit numeric code for item agent
     * 
     * @param int $itemId The item ID
     * @param int $userId The user ID
     * @return string Generated code
     */
    public function generateCode($itemId, $userId)
    {
        // Create a unique identifier from item_id and user_id
        $uniqueId = $itemId . $userId;
        
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
     * Get agents for a specific item
     * 
     * @param int $itemId The item ID
     * @return array Array of agent objects
     */
    public function getAgentsForItem($itemId)
    {
        return $this->select('item_agent.*, user.nama as user_name, user.email as user_email')
                   ->join('user', 'user.id = item_agent.user_id')
                   ->where('item_agent.item_id', $itemId)
                   ->findAll();
    }

    /**
     * Get items for a specific agent (user)
     * 
     * @param int $userId The user ID
     * @return array Array of item objects
     */
    public function getItemsForAgent($userId)
    {
        return $this->select('item_agent.*, item.name as item_name, item.sku as item_sku')
                   ->join('item', 'item.id = item_agent.item_id')
                   ->where('item_agent.user_id', $userId)
                   ->findAll();
    }

    /**
     * Check if agent relationship already exists
     * 
     * @param int $itemId The item ID
     * @param int $userId The user ID
     * @return bool True if relationship exists
     */
    public function relationshipExists($itemId, $userId)
    {
        return $this->where('item_id', $itemId)
                   ->where('user_id', $userId)
                   ->countAllResults() > 0;
    }

    /**
     * Get active agents for an item
     * 
     * @param int $itemId The item ID
     * @return array Array of active agent objects
     */
    public function getActiveAgentsForItem($itemId)
    {
        return $this->select('item_agent.*, user.nama as user_name, user.email as user_email')
                   ->join('user', 'user.id = item_agent.user_id')
                   ->where('item_agent.item_id', $itemId)
                   ->where('item_agent.is_active', 1)
                   ->findAll();
    }
}
