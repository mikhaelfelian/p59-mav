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
use App\Models\SalesPaymentsModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\IncomingRequest;

class Sales extends Controller
{
    /**
     * Model instance
     */
    protected $model;
    protected $salesPaymentsModel;

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
        $this->salesPaymentsModel = new SalesPaymentsModel();
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
            
            // Get raw body first (was logged)
            $rawInput = $request->getBody();
            
            // Check Content-Type header for JSON
            $contentType = $request->getHeaderLine('Content-Type');
            $isJson = strpos($contentType, 'application/json') !== false;
            
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
                // Get base URL for redirect
                $config = config('App');
                $baseURL = $config->baseURL;
                $notFoundUrl = $baseURL . 'agent/payment/not-found';
                
                // Check if this is a browser request
                $acceptHeader = $request->getHeaderLine('Accept');
                $isBrowserRequest = strpos($acceptHeader, 'text/html') !== false;
                
                // If browser request, redirect to not-found page
                if ($isBrowserRequest) {
                    return redirect()->to($notFoundUrl);
                }
                
                return $response->setJSON([
                    'status' => 'error',
                    'message' => 'Sale not found for orderId: ' . $orderId,
                    'redirect_url' => $notFoundUrl
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
            
            // Update sales_payments.response with callback data
            try {
                // Find payment record for this sale
                $paymentRecord = $this->salesPaymentsModel
                    ->where('sale_id', $sale['id'])
                    ->first();
                
                if ($paymentRecord) {
                    // Prepare callback response data
                    $callbackResponse = [
                        'orderId' => $orderId,
                        'status' => $status,
                        'settlementTime' => $settlementTime,
                        'payment_status' => $paymentStatus,
                        'callback_received_at' => date('Y-m-d H:i:s')
                    ];
                    
                    // Encode to JSON
                    $responseJson = json_encode($callbackResponse);
                    
                    // Update payment record
                    $this->salesPaymentsModel->skipValidation(true);
                    $this->salesPaymentsModel->update($paymentRecord['id'], [
                        'response' => $responseJson
                    ]);
                    $this->salesPaymentsModel->skipValidation(false);
                    
                    log_message('info', 'Api\Sales::callback - Updated sales_payments.response for sale_id: ' . $sale['id']);
                }
            } catch (\Exception $e) {
                // Don't fail the callback if payment update fails, just log it
                log_message('error', 'Api\Sales::callback - Failed to update sales_payments.response: ' . $e->getMessage());
            }
            
            // Get base URL for redirect
            $config = config('App');
            $baseURL = $config->baseURL;
            
            // Determine redirect URL based on payment status
            $redirectUrl = null;
            if ($status === 'PAID') {
                // Success - redirect to thankyou page
                $redirectUrl = $baseURL . 'agent/payment/thankyou?orderId=' . urlencode($orderId);
            } elseif (in_array($status, ['FAILED', 'CANCELED', 'EXPIRED'])) {
                // Failed/Canceled/Expired - redirect to status page
                $statusLower = strtolower($status);
                $redirectUrl = $baseURL . 'agent/payment/status?orderId=' . urlencode($orderId) . '&status=' . urlencode($statusLower);
            } elseif ($status === 'PENDING') {
                // Pending - redirect to status page with pending status
                $redirectUrl = $baseURL . 'agent/payment/status?orderId=' . urlencode($orderId) . '&status=pending';
            }
            
            // Check if this is a browser request (has Accept: text/html header)
            $acceptHeader = $request->getHeaderLine('Accept');
            $isBrowserRequest = strpos($acceptHeader, 'text/html') !== false;
            
            // If browser request and redirect URL exists, perform actual redirect
            if ($isBrowserRequest && $redirectUrl) {
                return redirect()->to($redirectUrl);
            }
            
            // Return JSON response using specified structure
            return $response->setJSON([
                'orderId'        => $orderId,
                'status'         => $status,
                'settlementTime' => $updateData['settlement_time'] ?? null
            ]);
            
        } catch (\Exception $e) {
            $response = service('response');
            return $response->setJSON([
                'status' => 'error',
                'message' => 'Internal server error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}

