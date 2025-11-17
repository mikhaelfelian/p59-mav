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
        'admin_note',
        'payment_status',
        'is_receive',
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
        'admin_note'      => 'permit_empty|string',
        'payment_status'  => 'required|in_list[0,1,2]',
        'is_receive'      => 'permit_empty|in_list[0,1]',
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
     * Format: {channel}{rand()}{yy}{mm}{xxx}
     * - channel: 1 (offline) or 2 (online) based on sale_channel
     * - rand(): Random 3 characters/numbers
     * - yy: 2-digit year (e.g., 25 for 2025)
     * - mm: 2-digit month (e.g., 11 for November)
     * - xxx: Sequential order number (001, 002, 003, etc.) based on channel, year, month
     * 
     * @param string|int $saleChannel Sale channel: '1' for offline, '2' for online (default: '1')
     * @return string Unique invoice number
     */
    public function generateInvoiceNo($saleChannel = '1')
    {
        // Normalize sale_channel to string
        $channel = (string) $saleChannel;
        if ($channel !== '1' && $channel !== '2') {
            $channel = '1'; // Default to offline
        }
        
        // Generate random 3 digits (numeric only)
        $random = '';
        for ($i = 0; $i < 3; $i++) {
            $random .= rand(0, 9);
        }
        
        // Get 2-digit year and month
        $yy = date('y'); // 2-digit year (e.g., 25 for 2025)
        $mm = date('m'); // 2-digit month (e.g., 11 for November)
        
        // Find last invoice for this channel, year, and month
        // Pattern: {channel}{rand()}{yy}{mm}{xxx}
        // Total length: 1 (channel) + 3 (rand) + 2 (yy) + 2 (mm) + 3 (seq) = 11
        // We need to find invoices matching: {channel}***{yy}{mm}*** where last 3 are digits
        // SQL LIKE: channel + 3 wildcards + yy + mm + 3 wildcards (but we need last 3 to be digits)
        // Better approach: Get all invoices starting with channel, length 11, then filter in PHP
        $allInvoices = $this->select('invoice_no')
            ->where('invoice_no LIKE', $channel . '%')
            ->where('LENGTH(invoice_no)', 11)
            ->orderBy('id', 'DESC')
            ->findAll();
        
        // Extract sequential number from last matching invoice
        $sequence = 1;
        foreach ($allInvoices as $inv) {
            if (empty($inv['invoice_no'])) {
                continue;
            }
            $invoiceNo = $inv['invoice_no'];
            // Check if invoice matches our pattern: {channel}{rand()}{yy}{mm}{xxx}
            // Positions: 0=channel, 1-3=rand, 4-5=yy, 6-7=mm, 8-10=seq
            if (strlen($invoiceNo) === 11 && 
                substr($invoiceNo, 0, 1) === $channel && 
                substr($invoiceNo, 4, 2) === $yy && 
                substr($invoiceNo, 6, 2) === $mm) {
                // Extract last 3 digits (sequential number)
                $lastSeq = substr($invoiceNo, -3);
                if (preg_match('/^\d{3}$/', $lastSeq)) {
                    $sequence = (int) $lastSeq + 1;
                    break; // Found the last matching invoice, use its sequence + 1
                }
            }
        }
        
        // Format: {channel}{rand()}{yy}{mm}{xxx}
        $newInvoice = $channel . $random . $yy . $mm . str_pad($sequence, 3, '0', STR_PAD_LEFT);
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

