<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-24
 * Github: github.com/mikhaelfelian
 * Description: Model for managing agent master data with location and contact information
 * This file represents the Model for AgentModel.
 */
class AgentModel extends Model
{
    protected $table            = 'agent';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'code',
        'name',
        'email',
        'phone',
        'address',
        'latitude',
        'longitude',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'postal_code',
        'country',
        'tax_number',
        'credit_limit',
        'payment_terms',
        'is_active'
    ];

    // Dates
    protected $useTimestamps  = true;
    protected $dateFormat     = 'datetime';
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';
    protected $deletedField   = 'deleted_at';

    // Validation
    protected $validationRules = [];

    protected $validationMessages = [];

    protected $skipValidation = false;
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
     * Generate unique agent code
     * 
     * @return string
     */
    public function generateCode()
    {
        $prefix = 'AGT';
        
        // Get all existing codes with the prefix
        $existingCodes = $this->select('code')
                              ->like('code', $prefix, 'after')
                              ->findAll();
        
        // Extract numbers from existing codes
        $existingNumbers = [];
        foreach ($existingCodes as $code) {
            $number = (int) substr($code->code, 3);
            $existingNumbers[] = $number;
        }
        
        // Find the next available number
        $newNumber = 1;
        while (in_array($newNumber, $existingNumbers)) {
            $newNumber++;
        }
        
        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get active agents
     * 
     * @return array
     */
    public function getActiveAgents()
    {
        return $this->where('is_active', '1')
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Get agents by location
     * 
     * @param int $provinceId
     * @param int $regencyId
     * @return array
     */
    public function getAgentsByLocation($provinceId = null, $regencyId = null)
    {
        $query = $this->where('is_active', '1');
        
        if ($provinceId) {
            $query->where('province_id', $provinceId);
        }
        
        if ($regencyId) {
            $query->where('regency_id', $regencyId);
        }
        
        return $query->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Search agents by name or code
     * 
     * @param string $search
     * @return array
     */
    public function searchAgents($search)
    {
        return $this->where('is_active', '1')
                    ->groupStart()
                        ->like('name', $search)
                        ->orLike('code', $search)
                        ->orLike('email', $search)
                    ->groupEnd()
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Get agent statistics
     * 
     * @return object
     */
    public function getAgentStats()
    {
        $total = $this->countAllResults();
        $active = $this->where('is_active', '1')->countAllResults();
        $inactive = $total - $active;
        
        return (object) [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive
        ];
    }
}
