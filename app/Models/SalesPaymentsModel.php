<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Model for managing sales payments with CRUD operations
 * This file represents the Model for SalesPaymentsModel.
 */

namespace App\Models;

use CodeIgniter\Model;

class SalesPaymentsModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sales_payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sale_id',
        'platform_id',
        'method',
        'amount',
        'note'
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
        'platform_id' => 'permit_empty|integer',
        'method' => 'required|in_list[cash,transfer,qris,credit,other]',
        'amount' => 'required|decimal|greater_than[0]',
        'note' => 'permit_empty|max_length[255]'
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
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get payments for a specific sale
     * 
     * @param int $saleId Sale ID
     * @return array Array of payment records
     */
    public function getPaymentsBySale($saleId)
    {
        return $this->select('sales_payments.*, platform.platform as platform_name')
            ->join('platform', 'platform.id = sales_payments.platform_id', 'left')
            ->where('sales_payments.sale_id', $saleId)
            ->orderBy('sales_payments.created_at', 'ASC')
            ->findAll();
    }

    /**
     * Calculate total paid amount for a sale
     * 
     * @param int $saleId Sale ID
     * @return float Total paid amount
     */
    public function getTotalPaid($saleId)
    {
        $result = $this->selectSum('amount')
            ->where('sale_id', $saleId)
            ->first();

        return (float) ($result['amount'] ?? 0);
    }

    /**
     * Bulk insert payments
     * 
     * @param array $payments Array of payment data
     * @return bool True if all payments inserted successfully
     */
    public function bulkInsert($payments)
    {
        if (empty($payments)) {
            return false;
        }

        return $this->insertBatch($payments);
    }
}

