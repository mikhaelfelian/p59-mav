<?php

namespace App\Controllers;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-26 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Frontend controller for displaying Privacy Policy page on public pages
 * This file represents the Controller.
 */
class Frontend_Privasi extends BaseController
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
        $pageContent = $this->settingModel->getSettingPage('page_privasi');
        $this->data['page_content'] = $pageContent;
        
        // Layout data for MAV theme - from database
        $judulWeb = $this->model->builder('setting')->where('param', 'judul_web')->get()->getRowArray();
        $this->data['title'] = ($judulWeb['value'] ?? '') . ' | ' . ($this->currentModule['judul_module'] ?? 'Kebijakan Privasi');
        $this->data['meta_description'] = $this->currentModule['deskripsi'] ?? 'Kebijakan privasi dan perlindungan data pribadi kami.';
        
        // Render using the MAV privasi template
        return view('themes/mav/privasi', $this->data);
    }
}

