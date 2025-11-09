<?php

/**
 * API Sales Controller
 * 
 * Handles API endpoints for sales-related operations
 * - Payment gateway callbacks (Midtrans, etc.)
 * 
 * @package    App\Controllers\Api
 * @author     Mikhael Felian Waskito <mikhaelfelian@gmail.com>
 * @copyright  2025
 * @license    MIT
 * @version    1.0.0
 * @since      2025-11-04
 */

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use App\Models\SalesModel;
use CodeIgniter\HTTP\ResponseInterface;

class Sales extends Controller
{
    /**
     * Model instance
     */
    protected $model;

    /**
     * Initialize models
     */
    public function __construct()
    {
        $this->model = new SalesModel();
    }

    /**
     * Payment gateway callback endpoint
     * Receives callback from payment gateway (e.g., Midtrans)
     * 
     * Expected POST/GET data:
     * {
     *   "orderId": "ORD-0012345",
     *   "status": "PAID",
     *   "settlementTime": "2025-11-07T10:20:00"
     * }
     * 
     * Status values: PAID, PENDING, FAILED, CANCELED, EXPIRED
     * 
     * @return ResponseInterface
     */
    public function callback(): ResponseInterface
    {
        try {
            // Get JSON data from request body or POST data
            $jsonData = null;
            $rawInput = $this->request->getBody();
            
            if (!empty($rawInput)) {
                $jsonData = json_decode($rawInput, true);
            }
            
            // Fallback to POST data if JSON body is empty
            if (empty($jsonData)) {
                $jsonData = $this->request->getPost();
            }
            
            // Fallback to GET data if POST is empty
            if (empty($jsonData)) {
                $jsonData = $this->request->getGet();
            }
            
            // Validate required fields
            if (empty($jsonData['orderId'])) {
                log_message('error', 'Api\Sales::callback - Missing orderId');
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'orderId is required'
                ])->setStatusCode(400);
            }
            
            if (empty($jsonData['status'])) {
                log_message('error', 'Api\Sales::callback - Missing status');
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'status is required'
                ])->setStatusCode(400);
            }
            
            $orderId = trim($jsonData['orderId']);
            $status = strtoupper(trim($jsonData['status']));
            $settlementTime = !empty($jsonData['settlementTime']) ? trim($jsonData['settlementTime']) : null;
            
            // Validate status value
            $validStatuses = ['PAID', 'PENDING', 'FAILED', 'CANCELED', 'EXPIRED'];
            if (!in_array($status, $validStatuses)) {
                log_message('error', 'Api\Sales::callback - Invalid status: ' . $status);
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid status. Must be one of: ' . implode(', ', $validStatuses)
                ])->setStatusCode(400);
            }
            
            // Find sale by invoice_no (orderId)
            $sale = $this->model->where('invoice_no', $orderId)->first();
            
            if (!$sale) {
                log_message('error', 'Api\Sales::callback - Sale not found for orderId: ' . $orderId);
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Sale not found for orderId: ' . $orderId
                ])->setStatusCode(404);
            }
            
            // Map API status to payment_status
            // payment_status: 0=unpaid, 1=partial, 2=paid
            $paymentStatusMap = [
                'PAID' => '2',      // paid
                'PENDING' => '0',   // unpaid
                'FAILED' => '0',    // unpaid
                'CANCELED' => '0',  // unpaid
                'EXPIRED' => '0'    // unpaid
            ];
            
            $paymentStatus = $paymentStatusMap[$status] ?? '0';
            
            // Prepare update data
            $updateData = [
                'payment_status' => $paymentStatus
            ];
            
            // Add settlement_time if provided and status is PAID
            if ($status === 'PAID' && $settlementTime) {
                // Parse settlementTime (ISO 8601 format: 2025-11-07T10:20:00)
                try {
                    $settlementDateTime = new \DateTime($settlementTime);
                    $updateData['settlement_time'] = $settlementDateTime->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    log_message('error', 'Api\Sales::callback - Invalid settlementTime format: ' . $settlementTime);
                    // Continue without settlement_time if parsing fails
                }
            }
            
            // Update sale record
            $this->model->skipValidation(true);
            $updateResult = $this->model->update($sale['id'], $updateData);
            $this->model->skipValidation(false);
            
            if (!$updateResult) {
                $errors = $this->model->errors();
                $errorMsg = 'Failed to update sale: ';
                if ($errors && is_array($errors)) {
                    $errorMsg .= implode(', ', $errors);
                }
                log_message('error', 'Api\Sales::callback - ' . $errorMsg);
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $errorMsg
                ])->setStatusCode(500);
            }
            
            log_message('info', 'Api\Sales::callback - Successfully updated sale ' . $sale['id'] . ' with status: ' . $status);
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Payment status updated successfully',
                'data' => [
                    'orderId' => $orderId,
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'settlement_time' => $updateData['settlement_time'] ?? null
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Api\Sales::callback error: ' . $e->getMessage());
            log_message('error', 'Api\Sales::callback trace: ' . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Internal server error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}

