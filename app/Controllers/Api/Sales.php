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
use CodeIgniter\HTTP\IncomingRequest;

class Sales extends Controller
{
    /**
     * Model instance
     */
    protected $model;

    /**
     * Request instance
     */
    protected $request;

    /**
     * Response instance
     */
    protected $response;

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
            // Get JSON data from request body
            // CodeIgniter 4's getJSON() automatically handles Content-Type: application/json
            $jsonData = null;
            $jsonError = null;
            
            // Get request instance (IncomingRequest)
            $request = service('request');
            $response = service('response');
            
            // Get raw body first for logging
            $rawInput = $request->getBody();
            
            // Check Content-Type header for JSON
            $contentType = $request->getHeaderLine('Content-Type');
            $isJson = strpos($contentType, 'application/json') !== false;
            
            // Log raw input for debugging
            log_message('info', 'Api\Sales::callback - Content-Type: ' . ($contentType ?: 'not set'));
            log_message('info', 'Api\Sales::callback - Raw body: ' . substr($rawInput, 0, 500));
            
            if ($isJson || !empty($rawInput)) {
                // Try to parse JSON from body
                if ($isJson) {
                    // Use getJSON() for proper JSON parsing
                    // getJSON(true) returns associative array
                    $jsonData = $request->getJSON(true);
                    
                    // If getJSON returns null, try manual decode to get error details
                    if ($jsonData === null && !empty($rawInput)) {
                        $jsonData = json_decode($rawInput, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $jsonError = json_last_error_msg();
                            $jsonData = null;
                        }
                    }
                } else {
                    // Try to get JSON from raw body if Content-Type is not set correctly
                    if (!empty($rawInput)) {
                        $jsonData = json_decode($rawInput, true);
                        // Check if JSON decode was successful
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $jsonError = json_last_error_msg();
                            $jsonData = null;
                        }
                    }
                }
            }
            
            // If JSON parsing failed, return specific error
            if ($jsonError !== null) {
                log_message('error', 'Api\Sales::callback - JSON parse error: ' . $jsonError);
                return $response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid JSON format: ' . $jsonError . '. Please check your JSON syntax (e.g., remove trailing commas).',
                    'raw_body' => substr($rawInput, 0, 200) // Show first 200 chars for debugging
                ])->setStatusCode(400);
            }
            
            // Fallback to POST data if JSON body is empty (but not if we tried to parse JSON)
            if (empty($jsonData) && empty($rawInput)) {
                $jsonData = $request->getPost();
            }
            
            // Fallback to GET data if POST is empty
            if (empty($jsonData)) {
                $jsonData = $request->getGet();
            }
            
            // Log received data for debugging
            log_message('info', 'Api\Sales::callback - Parsed data: ' . json_encode($jsonData));
            
            // Validate required fields
            if (empty($jsonData) || !is_array($jsonData)) {
                return $response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid request data. Expected JSON body with orderId and status. ' . 
                                ($rawInput ? 'Received: ' . substr($rawInput, 0, 100) : 'No data received.')
                ])->setStatusCode(400);
            }
            
            if (empty($jsonData['orderId'])) {
                return $response->setJSON([
                    'status' => 'error',
                    'message' => 'orderId is required',
                    'received_data' => $jsonData
                ])->setStatusCode(400);
            }
            
            if (empty($jsonData['status'])) {
                return $response->setJSON([
                    'status' => 'error',
                    'message' => 'status is required',
                    'received_data' => $jsonData
                ])->setStatusCode(400);
            }
            
            $orderId = trim($jsonData['orderId']);
            $status = strtoupper(trim($jsonData['status']));
            $settlementTime = !empty($jsonData['settlementTime']) ? trim($jsonData['settlementTime']) : null;
            
            // Validate status value
            $validStatuses = ['PAID', 'PENDING', 'FAILED', 'CANCELED', 'EXPIRED'];
            if (!in_array($status, $validStatuses)) {
                return $response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid status. Must be one of: ' . implode(', ', $validStatuses)
                ])->setStatusCode(400);
            }
            
            // Find sale by invoice_no (orderId)
            $sale = $this->model->where('invoice_no', $orderId)->first();
            
            if (!$sale) {
                return $response->setJSON([
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
                return $response->setJSON([
                    'status' => 'error',
                    'message' => $errorMsg
                ])->setStatusCode(500);
            }
            
            return $response->setJSON([
                'status'  => 'success',
                'message' => 'Payment status updated successfully',
                'data'    => [
                    'orderId'        => $orderId,
                    'status'         => $status,
                    'payment_status' => $paymentStatus,
                    'settlement_time'=> $updateData['settlement_time'] ?? null,
                ],
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Api\Sales::callback exception: ' . $e->getMessage());
            log_message('error', 'Api\Sales::callback trace: ' . $e->getTraceAsString());
            
            $response = service('response');
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Internal server error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}

