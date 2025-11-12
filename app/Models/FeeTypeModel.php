<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-12
 * Github: github.com/mikhaelfelian
 * Description: Model for managing fee types with CRUD operations
 */

namespace App\Models;

use CodeIgniter\Model;

class FeeTypeModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'fee_type';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'code',
        'name',
        'description',
        'status',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'code' => 'required|max_length[50]|is_unique[fee_type.code,id,{id}]',
        'name' => 'required|max_length[160]',
        'description' => 'permit_empty',
        'status' => 'required|in_list[0,1]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind       = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get active fee types
     * 
     * @return array
     */
    public function getActiveFeeTypes()
    {
        return $this->where('status', '1')
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Get fee type by code
     * 
     * @param string $code Fee type code
     * @return array|null
     */
    public function getByCode(string $code)
    {
        return $this->where('code', $code)->first();
    }
}
