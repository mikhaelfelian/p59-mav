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
        
        // Get active agents data for location display with regional information
        $this->data['agents'] = $this->agentModel->select('agent.*, wilayah_propinsi.nama_propinsi as province_name, wilayah_kabupaten.nama_kabupaten as regency_name, wilayah_kecamatan.nama_kecamatan as district_name, wilayah_kelurahan.nama_kelurahan as village_name')
                                                ->join('wilayah_propinsi', 'wilayah_propinsi.id_wilayah_propinsi = agent.province_id', 'left')
                                                ->join('wilayah_kabupaten', 'wilayah_kabupaten.id_wilayah_kabupaten = agent.regency_id', 'left')
                                                ->join('wilayah_kecamatan', 'wilayah_kecamatan.id_wilayah_kecamatan = agent.district_id', 'left')
                                                ->join('wilayah_kelurahan', 'wilayah_kelurahan.id_wilayah_kelurahan = agent.village_id', 'left')
                                                ->where('agent.is_active', '1')
                                                ->get()
                                                ->getResultArray();
        
        // Layout data for MAV theme - from database
        $this->data['title'] = $this->currentModule['judul_module'] ?? 'Lokasi Agen Terdekat';
        $this->data['meta_description'] = $this->currentModule['deskripsi'] ?? 'Temukan lokasi agen terdekat di sekitar Anda. Lihat peta interaktif dan ribuan titik layanan resmi Multi Automobile Vision.';
        $this->data['is_location_page'] = true; // Flag for Leaflet CSS loading
        
        // Render using the MAV location template
        return view('themes/mav/location', $this->data);
    }
}
