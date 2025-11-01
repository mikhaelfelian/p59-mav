<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-21
 * Github: github.com/mikhaelfelian
 * Description: Model for managing items with object return type and SKU generation
 * This file represents the Model for ItemModel.
 */

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'item';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'category_id',
        'brand_id',
        'sku',
        'name',
        'slug',
        'description',
        'short_description',
        'image',
        'price',
        'agent_price',
        'is_stockable',
        'is_catalog',
        'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name'        => 'required|max_length[255]',
        'sku'         => 'permit_empty|max_length[50]',
        'slug'        => 'permit_empty|max_length[255]',
        'price'       => 'permit_empty|decimal',
        'agent_price' => 'permit_empty|decimal',
        'is_catalog'  => 'in_list[0,1]',
        'status'      => 'in_list[0,1]'
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Item name is required',
            'max_length' => 'Item name cannot exceed 255 characters'
        ],
        'sku' => [
            'max_length' => 'SKU cannot exceed 50 characters'
        ],
        'slug' => [
            'max_length' => 'Slug cannot exceed 255 characters'
        ],
        'status' => [
            'in_list'    => 'Status must be either 0 or 1'
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
     * Get active items
     */
    public function getActiveItems()
    {
        return $this->where('status', '1')->findAll();
    }

    /**
     * Get items by category
     */
    public function getByCategory($categoryId)
    {
        return $this->where('category_id', $categoryId)->findAll();
    }

    /**
     * Get items by brand
     */
    public function getByBrand($brandId)
    {
        return $this->where('brand_id', $brandId)->findAll();
    }

    /**
     * Get items by user
     */
    public function getByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * Get item by SKU
     */
    public function getBySku($sku)
    {
        return $this->where('sku', $sku)->first();
    }

    /**
     * Get item by slug
     */
    public function getBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get stockable items
     */
    public function getStockableItems()
    {
        return $this->where('is_stockable', '1')->findAll();
    }

    /**
     * Generate auto-increment SKU (numeric only, 6 characters)
     * Format: 000001, 000002, etc.
     */
    public function generateSku()
    {
        // Get the last record to determine next number
        $lastRecord = $this->orderBy('id', 'DESC')->first();
        
        if (!$lastRecord || empty($lastRecord->sku)) {
            // If no records or no SKU, start with 000001
            return '000001';
        }
        
        // Extract number from last SKU (e.g., 000001 -> 1)
        if (preg_match('/^(\d+)$/', $lastRecord->sku, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
            return str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        }
        
        // Fallback if pattern doesn't match
        return '000001';
    }

    /**
     * Search items by name or description
     */
    public function searchItems($searchTerm)
    {
        return $this->groupStart()
                    ->like('name', $searchTerm)
                    ->orLike('description', $searchTerm)
                    ->orLike('short_description', $searchTerm)
                    ->groupEnd()
                    ->findAll();
    }

    /**
     * Get items with price range
     */
    public function getItemsByPriceRange($minPrice, $maxPrice)
    {
        return $this->where('price >=', $minPrice)
                    ->where('price <=', $maxPrice)
                    ->findAll();
    }
}
