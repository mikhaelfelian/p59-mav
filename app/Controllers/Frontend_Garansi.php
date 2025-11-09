<?php

namespace App\Controllers;

use App\Models\ItemSnModel;
use App\Models\ItemModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-25
 * Github: github.com/mikhaelfelian
 * Description: Frontend controller for warranty checking functionality
 * This file represents the Controller.
 */
class Frontend_Garansi extends BaseController
{
    protected $itemSnModel;
    protected $itemModel;

    public function __construct()
    {
        parent::__construct();
        $this->itemSnModel = new ItemSnModel();
        $this->itemModel = new ItemModel();
    }

    public function index()
    {
        $this->data['current_module'] = $this->currentModule;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        // Layout data for MAV theme
        $this->data['title'] = $this->currentModule['judul_module'] ?? 'Cek Garansi';
        $this->data['meta_description'] = $this->currentModule['deskripsi'] ?? 'Periksa status garansi produk Multi Automobile Vision Anda dengan mudah. Cukup masukkan nomor seri produk.';
        
        // Render using the MAV warranty template
        return view('themes/mav/cek-garansi', $this->data);
    }

    /**
     * Check warranty by serial number
     * 
     * @return ResponseInterface
     */
    public function check()
    {
        // Always treat as AJAX for JSON response
        $isAjax = true;
        
        $serialNumber = trim($this->request->getPost('serial_number') ?? $this->request->getJSON(true)['serial_number'] ?? '');
        
        if (empty($serialNumber)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Nomor seri wajib diisi.'
            ]);
        }

        try {
            // Simple warranty check using direct database query
            $db = \Config\Database::connect();
            
            $query = $db->query("
                SELECT 
                    item_sn.*,
                    item.name as item_name,
                    item.sku as item_sku
                FROM item_sn
                LEFT JOIN item ON item.id = item_sn.item_id
                WHERE item_sn.sn = ?
                AND item_sn.activated_at IS NOT NULL
                AND item_sn.expired_at IS NOT NULL
                LIMIT 1
            ", [$serialNumber]);
            
            $warranty = $query->getRowArray();

            if (empty($warranty)) {
                return $this->response->setJSON([
                    'status' => 'not_found',
                    'message' => 'Garansi tidak ditemukan untuk nomor seri tersebut.',
                    'data' => null
                ]);
            }

            // Check if warranty is expired
            $expiredAt = $warranty['expired_at'];
            $activatedAt = $warranty['activated_at'];
            $now = date('Y-m-d H:i:s');
            
            $isExpired = !empty($expiredAt) && strtotime($expiredAt) < strtotime($now);
            $status = $isExpired ? 'expired' : 'active';

            $result = [
                'status' => $status,
                'serial_number' => $warranty['sn'] ?? $serialNumber,
                'item_name' => $warranty['item_name'] ?? 'Unknown Product',
                'item_sku' => $warranty['item_sku'] ?? '',
                'activated_at' => $activatedAt ?? '',
                'expired_at' => $expiredAt ?? '',
                'is_activated' => $warranty['is_activated'] ?? 0,
                'is_sell' => $warranty['is_sell'] ?? 0
            ];

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $isExpired ? 'Garansi telah berakhir.' : 'Garansi aktif.',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Frontend_Garansi::check error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memeriksa garansi. Silakan coba lagi.'
            ]);
        }
    }
}
