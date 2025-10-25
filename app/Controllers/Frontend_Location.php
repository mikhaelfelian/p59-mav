<?php

namespace App\Controllers;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-25 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Frontend controller for displaying agent locations on public pages
 * This file represents the Controller.
 */
class Frontend_Location extends BaseController
{
    protected $agentModel;

    public function __construct()
    {
        parent::__construct();
        $this->agentModel = new \App\Models\AgentModel();
    }

    public function index()
    {
        $this->data['current_module'] = $this->currentModule;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        // Get active agents data for location display
        $this->data['agents'] = $this->agentModel->select('agent.*, wilayah_propinsi.nama_propinsi as province_name, wilayah_kabupaten.nama_kabupaten as regency_name, wilayah_kecamatan.nama_kecamatan as district_name, wilayah_kelurahan.nama_kelurahan as village_name')
                                                ->join('wilayah_propinsi', 'wilayah_propinsi.id_wilayah_propinsi = agent.province_id', 'left')
                                                ->join('wilayah_kabupaten', 'wilayah_kabupaten.id_wilayah_kabupaten = agent.regency_id', 'left')
                                                ->join('wilayah_kecamatan', 'wilayah_kecamatan.id_wilayah_kecamatan = agent.district_id', 'left')
                                                ->join('wilayah_kelurahan', 'wilayah_kelurahan.id_wilayah_kelurahan = agent.village_id', 'left')
                                                ->where('agent.is_active', '1')
                                                ->findAll();
        
        // Layout data - use module name for title
        $this->data['meta_description'] = 'Lokasi Agen - Temukan lokasi agen terdekat di sekitar Anda';
        $this->data['page_title'] = $this->currentModule['judul_module'] ?? 'Lokasi Agen';
        $this->data['page_description'] = 'Temukan lokasi agen terdekat di sekitar Anda untuk kemudahan akses';
        $this->data['site_title'] = $this->currentModule['judul_module'] ?? 'Frontend';
        $this->data['navigation'] = [
            ['url' => $this->data['config']->baseURL . 'frontend', 'icon' => 'fas fa-home', 'label' => 'Home'],
            ['url' => $this->data['config']->baseURL . 'frontend/location', 'icon' => 'fas fa-map-marker-alt', 'label' => 'Lokasi Agen'],
            ['url' => $this->data['config']->baseURL . 'frontend/about', 'icon' => 'fas fa-info-circle', 'label' => 'Tentang'],
            ['url' => $this->data['config']->baseURL . 'frontend/contact', 'icon' => 'fas fa-envelope', 'label' => 'Kontak'],
            ['url' => $this->data['config']->baseURL, 'icon' => 'fas fa-sign-in-alt', 'label' => 'Admin']
        ];
        $this->data['footer_contact_email'] = 'support@example.com';
        $this->data['footer_contact_url'] = $this->data['config']->baseURL . 'frontend/contact';
        $this->data['footer_about_text'] = 'Platform terbaik untuk menemukan lokasi agen terdekat dengan kemudahan akses.';
        $this->data['footer_facebook_url'] = '#';
        $this->data['footer_membership_url'] = '#';
        $this->data['footer_blog_url'] = $this->data['config']->baseURL . 'frontend';
        $this->data['footer_copyright_title'] = 'Frontend';
        $this->data['footer_copyright_url'] = $this->data['config']->baseURL . 'frontend';
        $this->data['footer_menu'] = [
            ['url' => $this->data['config']->baseURL . 'frontend', 'label' => 'Home'],
            ['url' => $this->data['config']->baseURL . 'frontend/location', 'label' => 'Lokasi Agen'],
            ['url' => $this->data['config']->baseURL . 'frontend/about', 'label' => 'About'],
            ['url' => $this->data['config']->baseURL . 'frontend/contact', 'label' => 'Contact']
        ];
        
        return view('themes/modern/frontend/location.php', $this->data);
    }
}
