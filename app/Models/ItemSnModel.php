<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Model for managing item serial numbers (SN) with CRUD operations
 * This file represents the Model.
 */
class ItemSnModel extends Model
{
    protected $table = 'item_sn';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'item_id',
        'variant_id',
        'agent_id',
        'user_id',
        'sn',
        'sn_replaced',
        'is_sell',
        'is_activated',
        'activated_at',
        'expired_at',
        'replaced_at'
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
        'agent_id' => 'required|integer|greater_than_equal_to[0]',
        'user_id' => 'required|integer|is_natural_no_zero',
        'sn' => 'required|max_length[100]|is_unique[item_sn.sn,id,{id}]',
        'sn_replaced' => 'permit_empty|max_length[100]',
        'is_sell' => 'in_list[0,1]',
        'is_activated' => 'in_list[0,1]',
        'activated_at' => 'permit_empty|valid_date',
        'expired_at' => 'permit_empty|valid_date',
        'replaced_at' => 'permit_empty|valid_date'
    ];

    protected $validationMessages = [
        'item_id' => [
            'required' => 'Item ID harus diisi',
            'integer' => 'Item ID harus berupa angka',
            'is_natural_no_zero' => 'Item ID harus berupa angka positif'
        ],
        'agent_id' => [
            'required' => 'Agent ID harus diisi',
            'integer' => 'Agent ID harus berupa angka',
            'greater_than_equal_to' => 'Agent ID harus berupa angka 0 atau lebih'
        ],
        'user_id' => [
            'required' => 'User ID harus diisi',
            'integer' => 'User ID harus berupa angka',
            'is_natural_no_zero' => 'User ID harus berupa angka positif'
        ],
        'sn' => [
            'required' => 'Serial Number harus diisi',
            'max_length' => 'Serial Number maksimal 100 karakter',
            'is_unique' => 'Serial Number sudah terdaftar'
        ],
        'sn_replaced' => [
            'max_length' => 'SN Replaced maksimal 100 karakter'
        ],
        'is_sell' => [
            'in_list' => 'Status jual harus 0 atau 1'
        ],
        'is_activated' => [
            'in_list' => 'Status aktivasi harus 0 atau 1'
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
     * Check if SN already exists
     * 
     * @param string $sn Serial number to check
     * @param int|null $excludeId ID to exclude from check (for updates)
     * @return bool True if SN exists
     */
    public function snExists($sn, $excludeId = null)
    {
        $builder = $this->where('sn', $sn);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * Get SNs for a specific item
     * 
     * @param int $itemId The item ID
     * @param int|null $agentId Optional agent ID filter
     * @return array Array of SN objects
     */
    public function getSnForItem($itemId, $agentId = null)
    {
        $builder = $this->select('item_sn.*, agent.name as agent_name, agent.code as agent_code, user.nama as user_name')
                       ->join('agent', 'agent.id = item_sn.agent_id', 'left')
                       ->join('user', 'user.id_user = item_sn.user_id', 'left')
                       ->where('item_sn.item_id', $itemId);
        
        if ($agentId) {
            $builder->where('item_sn.agent_id', $agentId);
        }
        
        return $builder->orderBy('item_sn.created_at', 'DESC')->findAll();
    }

    /**
     * Get SNs for a specific agent
     * 
     * @param int $agentId The agent ID
     * @return array Array of SN objects
     */
    public function getSnForAgent($agentId)
    {
        return $this->select('item_sn.*, item.name as item_name, item.sku as item_sku')
                   ->join('item', 'item.id = item_sn.item_id', 'left')
                   ->where('item_sn.agent_id', $agentId)
                   ->orderBy('item_sn.created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get SNs for a specific user
     * 
     * @param int $userId The user ID
     * @return array Array of SN objects
     */
    public function getSnForUser($userId)
    {
        return $this->select('item_sn.*, item.name as item_name, item.sku as item_sku, agent.name as agent_name')
                   ->join('item', 'item.id = item_sn.item_id', 'left')
                   ->join('agent', 'agent.id = item_sn.agent_id', 'left')
                   ->where('item_sn.user_id', $userId)
                   ->orderBy('item_sn.created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Bulk insert SNs
     * 
     * @param array $data Array of SN data
     * @return array Array with success count and errors
     */
    public function bulkInsert($data)
    {
        $successCount = 0;
        $errors = [];
        
        foreach ($data as $index => $row) {
            if (!array_key_exists('agent_id', $row) || $row['agent_id'] === null || $row['agent_id'] === '') {
                $row['agent_id'] = 0;
            }

            // Check if SN already exists
            if ($this->snExists($row['sn'])) {
                $errors[] = [
                    'row' => $index + 1,
                    'sn' => $row['sn'],
                    'message' => 'SN sudah terdaftar'
                ];
                continue;
            }
            
            // Validate required fields
            if (
                (!array_key_exists('item_id', $row) || $row['item_id'] === null || $row['item_id'] === '') ||
                (!array_key_exists('agent_id', $row) || $row['agent_id'] === null || $row['agent_id'] === '') ||
                (!array_key_exists('user_id', $row) || $row['user_id'] === null || $row['user_id'] === '') ||
                (empty($row['sn']))
            ) {
                $errors[] = [
                    'row' => $index + 1,
                    'sn' => $row['sn'] ?? '',
                    'message' => 'Data tidak lengkap (item_id, agent_id, user_id, sn wajib diisi)'
                ];
                continue;
            }
            
            // Set defaults
            $row['is_sell'] = $row['is_sell'] ?? '0';
            $row['is_activated'] = $row['is_activated'] ?? '0';
            
            // Insert
            if ($this->insert($row)) {
                $successCount++;
            } else {
                $errors[] = [
                    'row' => $index + 1,
                    'sn' => $row['sn'],
                    'message' => implode(', ', $this->errors())
                ];
            }
        }
        
        return [
            'success' => $successCount,
            'errors' => $errors
        ];
    }
}

