<?php

/**
 * Agent Payment Controller
 * 
 * Handles payment result redirect pages for agent checkout
 * 
 * @package    App\Controllers\Agent
 * @author     Mikhael Felian Waskito <mikhaelfelian@gmail.com>
 * @copyright  2025
 * @license    MIT
 * @version    1.0.0
 * @since      2025-11-09
 */

namespace App\Controllers\Agent;

use App\Controllers\BaseController;
use App\Models\SalesModel;
use CodeIgniter\HTTP\ResponseInterface;

class Payment extends BaseController
{
    protected $model;

    /**
     * Initialize models
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new SalesModel();
    }

    /**
     * Payment success page
     * 
     * @return ResponseInterface|string
     */
    public function thankyou()
    {
        $orderId = $this->request->getGet('orderId');
        
        if (empty($orderId)) {
            return redirect()->to('agent/payment/not-found');
        }

        // Find sale by invoice_no (orderId from payment gateway)
        $sale = $this->model->where('invoice_no', $orderId)->first();
        
        if (!$sale) {
            return redirect()->to('agent/payment/not-found');
        }

        // Get sale with relations for display
        $saleData = $this->model->getSalesWithRelations($sale['id']);

        $this->data['title'] = 'Pembayaran Berhasil';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['sale'] = $saleData;
        $this->data['orderId'] = $orderId;

        helper('angka');
        $this->view('sales/agent/redirect/thankyou', $this->data);
    }

    /**
     * Payment status page (failed, canceled, expired, pending)
     * 
     * @return ResponseInterface|string
     */
    public function status()
    {
        $orderId = $this->request->getGet('orderId');
        $status = $this->request->getGet('status');
        $validStatuses = ['failed', 'canceled', 'expired', 'pending'];

        if (empty($orderId)) {
            return redirect()->to('agent/payment/not-found');
        }

        // Find sale by invoice_no (orderId from payment gateway)
        $sale = $this->model->where('invoice_no', $orderId)->first();

        if (!$sale) {
            return redirect()->to('agent/payment/not-found');
        }

        // Validate status
        if (!in_array(strtolower($status), $validStatuses)) {
            $status = 'failed';
        }

        // Get sale with relations for display
        $saleData = $this->model->getSalesWithRelations($sale['id']);

        $this->data['title'] = 'Status Pembayaran';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['sale'] = $saleData;
        $this->data['status'] = ucfirst(strtolower($status));
        $this->data['orderId'] = $orderId;

        helper('angka');
        $this->view('sales/agent/redirect/failed', $this->data);
    }

    /**
     * Order not found page
     * 
     * @return ResponseInterface|string
     */
    public function notFound()
    {
        $this->data['title'] = 'Order Tidak Ditemukan';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;

        $this->view('sales/agent/redirect/not_found', $this->data);
    }
}

