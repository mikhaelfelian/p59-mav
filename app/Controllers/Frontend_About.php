<?php

namespace App\Controllers;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-26 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Frontend controller for displaying About page on public pages
 * This file represents the Controller.
 */
class Frontend_About extends BaseController
{
    protected $settingModel;

    public function __construct()
    {
        parent::__construct();
        $this->settingModel = new \App\Models\Builtin\SettingAppModel();
    }

    public function index()
    {
        $this->data['current_module'] = $this->currentModule;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        // Load page content from database
        $pageContent = $this->settingModel->getSettingPage('page_about');
        $this->data['page_content'] = $pageContent;
        
        // Layout data for MAV theme - from database
        $judulWeb = $this->model->builder('setting')->where('param', 'judul_web')->get()->getRowArray();
        $this->data['title'] = ($judulWeb['value'] ?? '') . ' | ' . ($this->currentModule['judul_module'] ?? 'Tentang Kami');
        $this->data['meta_description'] = $this->currentModule['deskripsi'] ?? 'Pelajari lebih lanjut tentang perusahaan dan layanan kami.';
        
        // Render using the MAV about template
        return view('themes/mav/about', $this->data);
    }
}

