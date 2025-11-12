<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-12
 * Github: github.com/mikhaelfelian
 * Description: Model for managing sales fees with CRUD operations
 */

namespace App\Models;

use CodeIgniter\Model;

class SalesFeeModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sales_fee';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sale_id',
        'fee_type_id',
        'fee_name',
        'amount',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'sale_id' => 'required|integer',
        'fee_type_id' => 'required|integer',
        'fee_name' => 'permit_empty|max_length[255]',
        'amount' => 'required|decimal|greater_than_equal_to[0]',
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
     * Get fees by sale ID with fee type information
     * 
     * @param int $saleId Sale ID
     * @return array
     */
    public function getFeesBySale(int $saleId): array
    {
        return $this->select('sales_fee.*, fee_type.code as fee_type_code, fee_type.name as fee_type_name')
                    ->join('fee_type', 'fee_type.id = sales_fee.fee_type_id', 'left')
                    ->where('sales_fee.sale_id', $saleId)
                    ->orderBy('sales_fee.created_at', 'ASC')
                    ->findAll();
    }

    /**
     * Get total fees amount for a sale
     * 
     * @param int $saleId Sale ID
     * @return float
     */
    public function getTotalFeesBySale(int $saleId): float
    {
        $result = $this->selectSum('amount')
                      ->where('sale_id', $saleId)
                      ->first();
        
        return (float) ($result['amount'] ?? 0);
    }

    /**
     * Delete all fees for a sale
     * 
     * @param int $saleId Sale ID
     * @return bool
     */
    public function deleteBySale(int $saleId): bool
    {
        return $this->where('sale_id', $saleId)->delete();
    }
}
