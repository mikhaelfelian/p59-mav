<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-15
 * Github: github.com/mikhaelfelian
 * Description: Model for managing sales gateway logs to track invoice numbers sent to payment gateway.
 * This prevents reusing invoice numbers that have already been sent to the gateway (payment gateway rule).
 */

namespace App\Models;

use CodeIgniter\Model;

class SalesGatewayLogModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sales_gateway_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'invoice_no',
        'order_id',
        'platform_id',
        'amount',
        'payload',
        'response',
        'status',
        'sale_id'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
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
     * Check if invoice number has been sent to payment gateway
     * 
     * @param string $invoiceNo Invoice number to check
     * @return bool True if invoice number exists (has been sent), false otherwise
     */
    public function invoiceExists(string $invoiceNo): bool
    {
        $result = $this->where('invoice_no', $invoiceNo)->first();
        return $result !== null;
    }

    /**
     * Log gateway request/response
     * 
     * @param string $invoiceNo Invoice number (orderId)
     * @param int|null $platformId Platform ID
     * @param float $amount Amount sent
     * @param array $payload Full payload sent to gateway
     * @param array|null $response Gateway response
     * @param string|null $status Status from gateway
     * @return int|false Insert ID on success, false on failure
     */
    public function logGatewayRequest(
        string $invoiceNo,
        ?int $platformId,
        float $amount,
        array $payload,
        ?array $response = null,
        ?string $status = null,
        ?int $saleId = null
    ) {
        $data = [
            'invoice_no' => $invoiceNo,
            'order_id'   => $invoiceNo, // Same as invoice_no for clarity
            'platform_id' => $platformId,
            'amount'     => $amount,
            'payload'    => json_encode($payload),
            'response'   => $response ? json_encode($response) : null,
            'status'     => $status,
            'sale_id'    => $saleId
        ];

        if ($saleId === null) {
            // Ensure we don't violate FK; skip logging when sale not available
            return false;
        }

        $this->skipValidation(true);
        $result = $this->insert($data);
        $this->skipValidation(false);

        return $result ? $this->getInsertID() : false;
    }
}

