<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Model for managing platforms with CRUD operations
 * This file represents the Model for PlatformModel.
 */

namespace App\Models;

use CodeIgniter\Model;

class PlatformModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'platform';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'code',
        'platform',
        'description',
        'status',
        'status_sys',
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
        'code' => 'permit_empty|max_length[160]',
        'platform' => 'permit_empty|max_length[160]',
        'description' => 'permit_empty',
        'status' => 'in_list[0,1]',
        'status_sys' => 'in_list[0,1]',
        'user_id' => 'permit_empty|integer'
    ];
    protected $validationMessages   = [
        'code' => [
            'max_length' => 'Code cannot exceed 160 characters'
        ],
        'platform' => [
            'max_length' => 'Platform name cannot exceed 160 characters'
        ],
        'status' => [
            'in_list' => 'Status must be either 0 or 1'
        ],
        'status_sys' => [
            'in_list' => 'System status must be either 0 or 1'
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
     * Get active platforms
     */
    public function getActivePlatforms()
    {
        return $this->where('status', '1')->findAll();
    }

    /**
     * Get platform by code
     */
    public function getByCode($code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Get platforms by user
     */
    public function getByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * Search platforms by name, code, or description
     */
    public function searchPlatforms($searchTerm)
    {
        return $this->groupStart()
                    ->like('platform', $searchTerm)
                    ->orLike('code', $searchTerm)
                    ->orLike('description', $searchTerm)
                    ->groupEnd()
                    ->findAll();
    }

    /**
     * Get platforms with pagination
     */
    public function getPlatformsPaginated($perPage = 10, $page = 1)
    {
        return $this->paginate($perPage, 'default', $page);
    }

    /**
     * Get platforms count by status
     */
    public function getCountByStatus($status)
    {
        return $this->where('status', $status)->countAllResults();
    }
}

