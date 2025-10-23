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
     * Format: XXYY001, XXYY002, etc.
     * XX = prefix from name (first 2 letters, uppercase, e.g. 'CATEGORY' => 'CA')
     * YY = last 2 digits of year (e.g. 2025 => 25)
     * 001 = sort number, 3 digits
     * Example: CA25001 for the first "CATEGORY" in 2025
     *
     * @param string $name The category name for prefix
     * @return string Generated code
     */
    public function generateCode($name)
    {
        // Get the first two uppercase letters from the name
        $prefix = strtoupper(substr(preg_replace('/\s+/', '', $name), 0, 2));

        // Last 2 digits of current year
        $year = date('Y');
        $yearSuffix = substr($year, -2);

        // Build base prefix for LIKE query, e.g. CA25
        $codePrefix = $prefix . $yearSuffix;

        // Get last record for this name/year code prefix
        $lastRecord = $this->like('code', $codePrefix, 'after')->orderBy('id', 'DESC')->first();

        if (!$lastRecord || empty($lastRecord->code)) {
            // If no records or code, start with NNYY001
            return $codePrefix . '001';
        }

        // Extract running number, e.g., CA25001 => 001
        if (preg_match('/^' . preg_quote($codePrefix, '/') . '(\d{3,})$/', $lastRecord->code, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
            return $codePrefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        // Fallback
        return $codePrefix . '001';
    }
}
