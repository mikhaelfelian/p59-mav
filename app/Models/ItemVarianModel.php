<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Model for managing item variants with CRUD operations
 * This file represents the Model.
 */
class ItemVarianModel extends Model
{
    protected $table = 'item_variant';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'item_id',
        'variant_name',
        'sku_variant',
        'stock',
        'price',
        'created_at',
        'updated_at'
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
        'variant_name' => 'required|max_length[100]',
        'sku_variant' => 'required|max_length[50]|is_unique[item_variant.sku_variant,id,{id}]',
        'stock' => 'permit_empty|integer',
        'price' => 'permit_empty|decimal'
    ];

    protected $validationMessages = [
        'item_id' => [
            'required' => 'Item ID harus diisi',
            'integer' => 'Item ID harus berupa angka',
            'is_natural_no_zero' => 'Item ID harus berupa angka positif'
        ],
        'variant_name' => [
            'required' => 'Nama varian harus diisi',
            'max_length' => 'Nama varian maksimal 100 karakter'
        ],
        'sku_variant' => [
            'required' => 'SKU Varian harus diisi',
            'max_length' => 'SKU Varian maksimal 50 karakter',
            'is_unique' => 'SKU Varian sudah terdaftar'
        ],
        'stock' => [
            'integer' => 'Stok harus berupa angka'
        ],
        'price' => [
            'decimal' => 'Harga harus berupa angka desimal'
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
     * Get variants for a specific item
     * 
     * @param int $itemId The item ID
     * @return array Array of variant arrays
     */
    public function getByItem($itemId)
    {
        return $this->where('item_id', $itemId)
                   ->orderBy('variant_name', 'ASC')
                   ->findAll();
    }

    /**
     * Check if SKU variant already exists
     * 
     * @param string $skuVariant SKU variant to check
     * @param int|null $excludeId ID to exclude from check (for updates)
     * @return bool True if SKU exists
     */
    public function skuVariantExists($skuVariant, $excludeId = null)
    {
        $builder = $this->where('sku_variant', $skuVariant);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }
}

