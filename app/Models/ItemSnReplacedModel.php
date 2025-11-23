<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-21
 * Github: github.com/mikhaelfelian
 * Description: Model for managing item serial number replacement tracking
 * This table records each replacement event, allowing tracking of how many times a serial number has been replaced.
 * This file represents the Model for ItemSnReplaced.
 */
class ItemSnReplacedModel extends Model
{
    protected $table = 'item_sn_replaced';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'item_id',
        'sn_old',
        'sn_replaced',
        'created_at'
    ];

    // Dates
    protected $useTimestamps = false; // Only created_at, no updated_at
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = null;
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'item_id' => 'required|integer|is_natural_no_zero',
        'sn_old' => 'required|max_length[100]',
        'sn_replaced' => 'required|max_length[100]',
    ];

    protected $validationMessages = [
        'item_id' => [
            'required' => 'Item ID harus diisi',
            'integer' => 'Item ID harus berupa angka',
            'is_natural_no_zero' => 'Item ID harus berupa angka positif'
        ],
        'sn_old' => [
            'required' => 'Serial Number lama harus diisi',
            'max_length' => 'Serial Number lama maksimal 100 karakter',
        ],
        'sn_replaced' => [
            'required' => 'Serial Number pengganti harus diisi',
            'max_length' => 'Serial Number pengganti maksimal 100 karakter',
        ],
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
     * Get replacement count for a specific serial number
     * 
     * @param string $sn Serial number to check
     * @return int Number of times this SN has been replaced
     */
    public function getReplacementCount(string $sn): int
    {
        return $this->where('sn_old', $sn)->countAllResults();
    }

    /**
     * Get all replacements for a specific serial number
     * 
     * @param string $sn Serial number to check
     * @return array Array of replacement records
     */
    public function getReplacementsBySn(string $sn): array
    {
        return $this->where('sn_old', $sn)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}

