<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-21
 * Github: github.com/mikhaelfelian
 * Description: Model for managing item categories with object return type
 * This file represents the Model for ItemCategoryModel.
 */

namespace App\Models;

use CodeIgniter\Model;

class ItemCategoryModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'item_category';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'code',
        'category',
        'slug',
        'description',
        'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'category' => 'required|max_length[100]',
        'code' => 'permit_empty|max_length[50]',
        'slug' => 'permit_empty|max_length[100]',
        'status' => 'in_list[0,1]'
    ];
    protected $validationMessages   = [
        'category' => [
            'required' => 'Category name is required',
            'max_length' => 'Category name cannot exceed 100 characters'
        ],
        'code' => [
            'max_length' => 'Code cannot exceed 50 characters'
        ],
        'slug' => [
            'max_length' => 'Slug cannot exceed 100 characters'
        ],
        'status' => [
            'in_list' => 'Status must be either 0 or 1'
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
     * Get all active categories
     * 
     * @return object
     */
    public function getActiveCategories()
    {
        return $this->where('status', '1')->findAll();
    }

    /**
     * Get category by slug
     * 
     * @param string $slug
     * @return object|null
     */
    public function getBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get category by code
     * 
     * @param string $code
     * @return object|null
     */
    public function getByCode($code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Get categories by user
     * 
     * @param int $userId
     * @return object
     */
    public function getByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * Generate auto-increment code for category
     * Format: CAT001, CAT002, etc.
     */
    public function generateCategoryCode()
    {
        // Get the last record to determine next number
        $lastRecord = $this->orderBy('id', 'DESC')->first();
        
        if (!$lastRecord || empty($lastRecord->code)) {
            // If no records or no code, start with CAT001
            return 'CAT001';
        }
        
        // Extract number from last code (e.g., CAT001 -> 001)
        if (preg_match('/CAT(\d+)/', $lastRecord->code, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
            return 'CAT' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
        
        // Fallback if pattern doesn't match
        return 'CAT001';
    }
}
