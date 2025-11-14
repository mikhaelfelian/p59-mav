<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Model for managing sales transactions with CRUD operations
 * This file maps to table `sales`.
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
        'total_payment',
        'balance_due',
        'discount_amount',
        'tax_amount',
        'tax_type',
        'grand_total',
        'delivery_address',
        'note',
        'payment_status',
        'settlement_time'
    ];

    // Dates
    protected $useTimestamps = false;
    // DB columns: created_at and updated_at are TIMESTAMP type
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'invoice_no'      => 'required|max_length[50]|is_unique[sales.invoice_no,id,{id}]',
        'user_id'         => 'required|integer',
        'customer_id'     => 'permit_empty|integer',
        'warehouse_id'    => 'permit_empty|integer',
        'sale_channel'    => 'required|in_list[1,2]',
        'total_amount'    => 'required|decimal',
        'total_payment'   => 'required|decimal',
        'balance_due'     => 'required|decimal',
        'discount_amount' => 'required|decimal',
        'tax_amount'      => 'required|decimal',
        'tax_type'        => 'required|in_list[0,1,2]',
        'grand_total'     => 'required|decimal',
        'delivery_address'=> 'permit_empty|string',
        'note'            => 'permit_empty|string',
        'payment_status'  => 'required|in_list[0,1,2]',
        'settlement_time' => 'permit_empty|valid_date'
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
     * Format: XXXXXXYYYYMMDD (6-digit sequence + date), numeric-only
     * 
     * @param string $prefix Prefix for invoice (deprecated, kept for backward compatibility)
     * @return string Unique invoice number
     */
    public function generateInvoiceNo($prefix = '')
    {
        $date = date('Ymd'); // YYYYMMDD format
        
        // Find last invoice for current date (ending with YYYYMMDD)
        // Match pattern: XXXXXXYYYYMMDD where XXXXXX is 6-digit sequence
        // Find invoices that end with current date and are exactly 14 characters (6 digits + 8 date)
        $lastInvoice = $this->select('invoice_no')
            ->where('invoice_no LIKE', '%' . $date)
            ->where('LENGTH(invoice_no)', 14) // Ensure it's exactly 14 characters (6 digits + 8 date)
            ->orderBy('id', 'DESC')
            ->first();

        if ($lastInvoice && !empty($lastInvoice['invoice_no'])) {
            $invoiceNo = $lastInvoice['invoice_no'];
            // Check if invoice ends with current date
            if (substr($invoiceNo, -8) === $date) {
                // Extract 6-digit sequence from beginning
                $sequence = substr($invoiceNo, 0, 6);
                if (preg_match('/^\d{6}$/', $sequence)) {
                    $lastNumber = (int) $sequence;
                    $newNumber = $lastNumber + 1;
                } else {
                    // If format doesn't match, start from 1
                    $newNumber = 1;
                }
            } else {
                // Invoice doesn't match current date format, start from 1
                $newNumber = 1;
            }
        } else {
            // No invoice found for current date, start from 1
            $newNumber = 1;
        }

        // Format: 6-digit sequence + YYYYMMDD
        $newInvoice = str_pad($newNumber, 6, '0', STR_PAD_LEFT) . $date;
        return $newInvoice;
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

