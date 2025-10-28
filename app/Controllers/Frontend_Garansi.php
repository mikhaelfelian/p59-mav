<?php

namespace App\Controllers;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-25
 * Github: github.com/mikhaelfelian
 * Description: Frontend controller for warranty checking functionality
 * This file represents the Controller.
 */
class Frontend_Garansi extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->data['current_module'] = $this->currentModule;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        // Layout data for MAV theme
        $this->data['title'] = $this->currentModule['judul_module'] ?? 'Cek Garansi';
        $this->data['meta_description'] = $this->currentModule['deskripsi'] ?? 'Periksa status garansi produk Multi Automobile Vision Anda dengan mudah. Cukup masukkan nomor plat dan nomor telepon.';
        
        // Render using the MAV warranty template
        return view('themes/mav/cek-garansi', $this->data);
    }
}
