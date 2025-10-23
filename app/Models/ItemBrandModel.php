<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-21
 * Github: github.com/mikhaelfelian
 * Description: Model for managing item brands with object return type and code generation
 * This file represents the Model for ItemBrandModel.
 */

namespace App\Models;

use CodeIgniter\Model;

class ItemBrandModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'item_brand';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'code',
        'name',
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
        'name' => 'required|max_length[100]',
        'code' => 'permit_empty|max_length[50]',
        'slug' => 'permit_empty|max_length[100]',
        'status' => 'in_list[0,1]'
    ];
    protected $validationMessages   = [
        'name' => [
            'required' => 'Brand name is required',
            'max_length' => 'Brand name cannot exceed 100 characters'
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
     * Get active brands
     */
    public function getActiveBrands()
    {
        return $this->where('status', '1')->findAll();
    }

    /**
     * Get brand by code
     */
    public function getByCode($code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Get brand by slug
     */
    public function getBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get brands by user
     */
    public function getByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * Search brands by name or description
     */
    public function searchBrands($searchTerm)
    {
        return $this->groupStart()
                    ->like('name', $searchTerm)
                    ->orLike('description', $searchTerm)
                    ->groupEnd()
                    ->findAll();
    }

    /**
     * Generate auto-increment code for brand
     * Format: XXYY001, XXYY002, etc.
     * XX = prefix from name (first 2 letters, uppercase, e.g. 'BRAND' => 'BR')
     * YY = last 2 digits of year (e.g. 2025 => 25)
     * 001 = sort number, 3 digits
     * Example: BR25001 for the first "BRAND" in 2025
     *
     * @param string $name The brand name for prefix
     * @return string Generated code
     */
    public function generateCode($name)
    {
        // Get the first two uppercase letters from the name
        $prefix = strtoupper(substr(preg_replace('/\s+/', '', $name), 0, 2));

        // Last 2 digits of current year
        $year = date('Y');
        $yearSuffix = substr($year, -2);

        // Build base prefix for LIKE query, e.g. BR25
        $codePrefix = $prefix . $yearSuffix;

        // Get last record for this name/year code prefix
        $lastRecord = $this->like('code', $codePrefix, 'after')->orderBy('id', 'DESC')->first();

        if (!$lastRecord || empty($lastRecord->code)) {
            // If no records or code, start with NNYY001
            return $codePrefix . '001';
        }

        // Extract running number, e.g., BR25001 => 001
        if (preg_match('/^' . preg_quote($codePrefix, '/') . '(\d{3,})$/', $lastRecord->code, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
            return $codePrefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        // Fallback
        return $codePrefix . '001';
    }

    /**
     * Get brands with pagination
     */
    public function getBrandsPaginated($perPage = 10, $page = 1)
    {
        return $this->paginate($perPage, 'default', $page);
    }

    /**
     * Get brands count by status
     */
    public function getCountByStatus($status)
    {
        return $this->where('status', $status)->countAllResults();
    }
}
