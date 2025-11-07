<?php

namespace App\Controllers;

use App\Models\SalesModel;
use App\Models\SalesDetailModel;
use App\Models\CustomerModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Checkout Controller
 * 
 * Handles cart checkout and order processing for frontend catalog
 * 
 * @package    App\Controllers
 * @author     Mikhael Felian Waskito <mikhaelfelian@gmail.com>
 * @copyright  2025
 * @license    MIT
 * @version    1.0.0
 * @since      2025-11-01
 */
class Checkout extends BaseController
{
    protected $salesModel;
    protected $salesDetailModel;
    protected $customerModel;

    public function __construct()
    {
        parent::__construct();
        $this->salesModel = new SalesModel();
        $this->salesDetailModel = new SalesDetailModel();
        $this->customerModel = new CustomerModel();
    }

    /**
     * Display cart page
     * 
     * @return string
     */
    public function cart()
    {
        $this->data['title'] = 'Keranjang Belanja';
        $this->data['current_module'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        return view('themes/mav/cart', $this->data);
    }

    /**
     * Display checkout page
     * 
     * @return string
     */
    public function index()
    {
        $this->data['title'] = 'Checkout';
        $this->data['current_module'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        return view('themes/mav/checkout', $this->data);
    }

    /**
     * Process checkout and create sales order
     * 
     * @return ResponseInterface
     */
    public function process(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Request tidak valid.'
            ]);
        }

        try {
            // Get JSON data from request body
            $jsonData = $this->request->getJSON(true);
            
            if (empty($jsonData)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan.'
                ]);
            }

            // Validate required fields
            if (empty($jsonData['customer_name'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Nama pelanggan wajib diisi.'
                ]);
            }

            if (empty($jsonData['items']) || !is_array($jsonData['items']) || count($jsonData['items']) === 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Keranjang kosong. Minimal harus ada satu item.'
                ]);
            }

            // Get user session (or use default user for frontend orders)
            $userSession = session('user');
            $userId = null;
            
            if (is_array($userSession) && isset($userSession['id_user'])) {
                $userId = $userSession['id_user'];
            } else {
                // For frontend orders without login, use a default user or create guest user
                // You may need to adjust this based on your requirements
                $userId = 1; // Default user ID - adjust as needed
            }

            // Prepare customer data
            $platCode = !empty($jsonData['plate_code']) ? trim($jsonData['plate_code']) : null;
            $platNumber = !empty($jsonData['plate_number']) ? trim($jsonData['plate_number']) : null;
            $platLast = !empty($jsonData['plate_suffix']) ? trim($jsonData['plate_suffix']) : null;
            $customerName = trim($jsonData['customer_name']);
            $phone = !empty($jsonData['phone']) ? trim($jsonData['phone']) : null;

            // Start database transaction
            $db = \Config\Database::connect();
            $db->transStart();

            // Find or create customer
            $customerId = null;
            if ($platCode && $platNumber) {
                $existingCustomer = $this->customerModel->findByPlate($platCode, $platNumber, $platLast);
                if ($existingCustomer) {
                    $customerId = $existingCustomer['id'];
                }
            }

            // Create customer if not found
            if ($customerId === null) {
                $customerData = [
                    'name' => $customerName,
                    'phone' => $phone,
                    'plat_code' => $platCode,
                    'plat_number' => $platNumber,
                    'plat_last' => $platLast ?: null,
                    'status' => 'active'
                ];
                
                $this->customerModel->skipValidation(true);
                $insertResult = $this->customerModel->insert($customerData);
                if (!$insertResult) {
                    throw new \Exception('Gagal membuat customer: ' . implode(', ', $this->customerModel->errors()));
                }
                $customerId = $this->customerModel->getInsertID();
                $this->customerModel->skipValidation(false);
            }

            // Calculate totals
            $subtotal = 0;
            $discount = 0;
            $tax = 0;
            
            foreach ($jsonData['items'] as $item) {
                $itemSubtotal = (float)($item['subtotal'] ?? 0);
                $itemDiscount = (float)($item['discount'] ?? 0);
                $subtotal += $itemSubtotal;
                $discount += $itemDiscount;
            }
            
            $grandTotal = $subtotal - $discount + $tax;

            // Generate invoice number
            $invoiceNo = $this->salesModel->generateInvoiceNo();

            // Create sales record
            $saleData = [
                'invoice_no' => $invoiceNo,
                'user_id' => $userId,
                'customer_id' => $customerId,
                'warehouse_id' => null,
                'sale_channel' => '2', // Online channel
                'total_amount' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'grand_total' => $grandTotal,
                'payment_status' => $jsonData['payment_status'] ?? '0'
            ];

            $this->salesModel->skipValidation(true);
            $insertResult = $this->salesModel->insert($saleData);
            if (!$insertResult) {
                throw new \Exception('Gagal membuat penjualan: ' . implode(', ', $this->salesModel->errors()));
            }
            $saleId = $this->salesModel->getInsertID();
            $this->salesModel->skipValidation(false);

            if (!$saleId || $saleId == 0) {
                throw new \Exception('Gagal mendapatkan ID penjualan setelah insert.');
            }

            // Create sales detail records
            $itemModel = new \App\Models\ItemModel();
            foreach ($jsonData['items'] as $item) {
                // Get item name
                $itemRecord = $itemModel->find((int)$item['item_id']);
                $itemName = 'Unknown';
                if ($itemRecord) {
                    if (is_array($itemRecord)) {
                        $itemName = isset($itemRecord['name']) ? (string)$itemRecord['name'] : 'Unknown';
                    } else {
                        $itemName = isset($itemRecord->name) ? (string)$itemRecord->name : 'Unknown';
                    }
                }

                // Prepare serial numbers
                $snValue = null;
                if (!empty($item['sns']) && is_array($item['sns'])) {
                    $snValue = json_encode($item['sns']);
                }

                // Insert sales detail
                $salesDetailData = [
                    'sale_id' => $saleId,
                    'item_id' => (int)$item['item_id'],
                    'variant_id' => !empty($item['variant_id']) ? (int)$item['variant_id'] : null,
                    'sn' => $snValue,
                    'item' => $itemName,
                    'price' => (float)($item['price'] ?? 0),
                    'qty' => (int)($item['qty'] ?? 1),
                    'disc' => (float)($item['discount'] ?? 0),
                    'amount' => (float)($item['subtotal'] ?? 0)
                ];

                $this->salesDetailModel->skipValidation(true);
                $detailInsertResult = $this->salesDetailModel->insert($salesDetailData);
                if (!$detailInsertResult) {
                    throw new \Exception('Gagal insert sales_detail: ' . implode(', ', $this->salesDetailModel->errors()));
                }
                $this->salesDetailModel->skipValidation(false);
            }

            // Complete transaction
            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi gagal.');
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibuat.',
                'data' => [
                    'sale_id' => $saleId,
                    'invoice_no' => $invoiceNo
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Checkout::process error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal memproses pesanan: ' . $e->getMessage()
            ]);
        }
    }
}

