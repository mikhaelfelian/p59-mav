<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Model for managing sales transactions with CRUD operations
 * This file represents the Model for SalesModel.
 */

namespace App\Models;

use CodeIgniter\Model;

class SalesModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sales';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'invoice_no',
        'user_id',
        'customer_id',
        'warehouse_id',
        'sale_channel',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'payment_status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'invoice_no' => 'required|max_length[50]|is_unique[sales.invoice_no,id,{id}]',
        'user_id' => 'required|integer',
        'customer_id' => 'permit_empty|integer',
        'warehouse_id' => 'permit_empty|integer',
        'sale_channel' => 'required|in_list[1,2]',
        'total_amount' => 'permit_empty|decimal',
        'discount_amount' => 'permit_empty|decimal',
        'tax_amount' => 'permit_empty|decimal',
        'grand_total' => 'permit_empty|decimal',
        'payment_status' => 'permit_empty|in_list[0,1,2]'
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
     * Generate unique invoice number
     * 
     * @param string $prefix Prefix for invoice (default: 'INV')
     * @return string Unique invoice number
     */
    public function generateInvoiceNo($prefix = 'INV')
    {
        $date = date('Ymd');
        $lastInvoice = $this->select('invoice_no')
            ->like('invoice_no', $prefix . $date)
            ->orderBy('id', 'DESC')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice['invoice_no'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get sales with related data
     * 
     * @param int|null $id Sale ID (optional)
     * @return array|array[] Sale(s) with related data
     */
    public function getSalesWithRelations($id = null)
    {
        $builder = $this->select('sales.*, 
            customer.name as customer_name, 
            customer.phone as customer_phone,
            customer.plat_code,
            customer.plat_number,
            customer.plat_last,
            user.nama as user_name,
            agent.name as agent_name')
            ->join('customer', 'customer.id = sales.customer_id', 'left')
            ->join('user', 'user.id_user = sales.user_id', 'left')
            ->join('agent', 'agent.id = sales.warehouse_id', 'left');

        if ($id) {
            return $builder->where('sales.id', $id)->first();
        }

        return $builder->orderBy('sales.created_at', 'DESC')->findAll();
    }
}

