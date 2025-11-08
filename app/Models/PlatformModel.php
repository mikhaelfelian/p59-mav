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
        'status_agent',
        'status_pos',
        'gw_code',
        'gw_status',
        'logo',
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
        'status_agent' => 'in_list[0,1]',
        'status_pos' => 'in_list[0,1]',
        'gw_code' => 'permit_empty|max_length[50]',
        'gw_status' => 'in_list[0,1]',
        'logo' => 'permit_empty|max_length[255]',
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
     * Get active gateway platforms (for payment gateway)
     * Must be active (status=1), active for agent (status_agent=1), and gateway active (gw_status=1)
     */
    public function getActiveGateways()
    {
        return $this->where('status', '1')
                    ->where('status_agent', '1')
                    ->where('gw_status', '1')
                    ->findAll();
    }

    /**
     * Get platform by gateway code
     * Must be active (status=1), active for agent (status_agent=1), and gateway active (gw_status=1)
     */
    public function getByGatewayCode($gwCode)
    {
        return $this->where('gw_code', $gwCode)
                    ->where('status', '1')
                    ->where('status_agent', '1')
                    ->where('gw_status', '1')
                    ->first();
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

