<?php

namespace App\Controllers;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Frontend controller for public-facing pages using tanpalogin template
 * This file represents the Controller.
 */
class Frontend extends BaseController
{
    protected $model;

    public function __construct() 
    {
        parent::__construct();
        $this->model = new \App\Models\ItemModel();
    }

    public function index()
    {
        $this->data['current_module'] = $this->currentModule;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        // Get items data for display
        $this->data['items'] = $this->model->select('item.*, item_brand.name as brand_name, item_category.category as category_name')
                                          ->join('item_brand', 'item_brand.id = item.brand_id', 'left')
                                          ->join('item_category', 'item_category.id = item.category_id', 'left')
                                          ->where('item.status', '1')
                                          ->findAll();
        
        // Layout data for MAV theme - from database
        $this->data['title'] = $this->currentModule['judul_module'] ?? 'Multi Automobile Vision – Garansi Nasional Tanpa Masalah';
        $this->data['meta_description'] = $this->currentModule['deskripsi'] ?? 'Periksa dan klaim garansi produk Multi Automobile Vision. Cek lokasi toko, katalog produk, dan status garansi Anda.';
        
        // Render using the MAV home template
        return view('themes/mav/home', $this->data);
    }

    public function store()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'name' => 'required|max_length[100]',
            'email' => 'required|valid_email|max_length[100]',
            'message' => 'required|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            $errors = $validation->getErrors();
            $message = implode('<br>', $errors);
            
            return redirect()->back()->withInput()->with('message', $message);
        }

        // Get form data
        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $message = $this->request->getPost('message');

        // Here you would typically save to a contact/feedback table
        // For now, we'll just show a success message
        $data = [
            'name' => $name,
            'email' => $email,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Simulate save operation
        $result = true; // Replace with actual model save operation
        
        if ($result) {
            $message = 'Pesan berhasil dikirim';
            return redirect()->to('frontend')->with('message', $message);
        } else {
            $message = 'Gagal mengirim pesan';
            return redirect()->back()->withInput()->with('message', $message);
        }
    }

    public function about()
    {
        $this->data['current_module'] = $this->currentModule;
        
        // Layout data - use module name for title
        $this->data['meta_description'] = 'Tentang Kami - Pelajari lebih lanjut tentang perusahaan kami';
        $this->data['page_title'] = $this->currentModule['judul_module'] ?? 'Tentang Kami';
        $this->data['page_description'] = 'Pelajari lebih lanjut tentang perusahaan dan layanan kami';
        $this->data['site_title'] = $this->currentModule['judul_module'] ?? 'Frontend';
        $this->data['navigation'] = [
            ['url' => $this->data['config']->baseURL . 'frontend', 'icon' => 'fas fa-home', 'label' => 'Home'],
            ['url' => $this->data['config']->baseURL . 'frontend/about', 'icon' => 'fas fa-info-circle', 'label' => 'Tentang'],
            ['url' => $this->data['config']->baseURL . 'frontend/contact', 'icon' => 'fas fa-envelope', 'label' => 'Kontak'],
            ['url' => $this->data['config']->baseURL, 'icon' => 'fas fa-sign-in-alt', 'label' => 'Admin']
        ];
        $this->data['footer_contact_email'] = 'support@example.com';
        $this->data['footer_contact_url'] = $this->data['config']->baseURL . 'frontend/contact';
        $this->data['footer_about_text'] = 'Platform terbaik untuk menemukan item berkualitas tinggi dengan harga terbaik.';
        $this->data['footer_facebook_url'] = '#';
        $this->data['footer_membership_url'] = '#';
        $this->data['footer_blog_url'] = $this->data['config']->baseURL . 'frontend';
        $this->data['footer_copyright_title'] = 'Frontend';
        $this->data['footer_copyright_url'] = $this->data['config']->baseURL . 'frontend';
        $this->data['footer_menu'] = [
            ['url' => $this->data['config']->baseURL . 'frontend', 'label' => 'Home'],
            ['url' => $this->data['config']->baseURL . 'frontend/about', 'label' => 'About'],
            ['url' => $this->data['config']->baseURL . 'frontend/contact', 'label' => 'Contact']
        ];
        
        $this->data['content'] = view('frontend/about', $this->data);
        return view('themes/modern/layout/content.php', $this->data);
    }

    public function contact()
    {
        $this->data['current_module'] = $this->currentModule;
        
        // Layout data - use module name for title
        $this->data['meta_description'] = 'Kontak Kami - Hubungi kami untuk informasi lebih lanjut';
        $this->data['page_title'] = $this->currentModule['judul_module'] ?? 'Kontak Kami';
        $this->data['page_description'] = 'Hubungi kami untuk informasi lebih lanjut atau pertanyaan Anda';
        $this->data['site_title'] = $this->currentModule['judul_module'] ?? 'Frontend';
        $this->data['navigation'] = [
            ['url' => $this->data['config']->baseURL . 'frontend', 'icon' => 'fas fa-home', 'label' => 'Home'],
            ['url' => $this->data['config']->baseURL . 'frontend/about', 'icon' => 'fas fa-info-circle', 'label' => 'Tentang'],
            ['url' => $this->data['config']->baseURL . 'frontend/contact', 'icon' => 'fas fa-envelope', 'label' => 'Kontak'],
            ['url' => $this->data['config']->baseURL, 'icon' => 'fas fa-sign-in-alt', 'label' => 'Admin']
        ];
        $this->data['footer_contact_email'] = 'support@example.com';
        $this->data['footer_contact_url'] = $this->data['config']->baseURL . 'frontend/contact';
        $this->data['footer_about_text'] = 'Platform terbaik untuk menemukan item berkualitas tinggi dengan harga terbaik.';
        $this->data['footer_facebook_url'] = '#';
        $this->data['footer_membership_url'] = '#';
        $this->data['footer_blog_url'] = $this->data['config']->baseURL . 'frontend';
        $this->data['footer_copyright_title'] = 'Frontend';
        $this->data['footer_copyright_url'] = $this->data['config']->baseURL . 'frontend';
        $this->data['footer_menu'] = [
            ['url' => $this->data['config']->baseURL . 'frontend', 'label' => 'Home'],
            ['url' => $this->data['config']->baseURL . 'frontend/about', 'label' => 'About'],
            ['url' => $this->data['config']->baseURL . 'frontend/contact', 'label' => 'Contact']
        ];
        
        $this->data['content'] = view('frontend/contact', $this->data);
        return view('themes/modern/layout/content.php', $this->data);
    }

    public function item($id = null)
    {
        if (!$id) {
            return redirect()->to('frontend');
        }

        $item = $this->model->select('item.*, item_brand.name as brand_name, item_category.category as category_name')
                           ->join('item_brand', 'item_brand.id = item.brand_id', 'left')
                           ->join('item_category', 'item_category.id = item.category_id', 'left')
                           ->where('item.id', $id)
                           ->where('item.status', '1')
                           ->first();

        if (!$item) {
            return redirect()->to('frontend');
        }

        $this->data['current_module'] = $this->currentModule;
        $this->data['item'] = $item;
        
        // Layout data - use module name for title
        $this->data['meta_description'] = $item->short_description ?? $item->name;
        $this->data['page_title'] = $this->currentModule['judul_module'] ?? $item->name;
        $this->data['page_description'] = $item->short_description ?? 'Detail produk terbaru dari koleksi kami';
        $this->data['site_title'] = $this->currentModule['judul_module'] ?? 'Frontend';
        $this->data['navigation'] = [
            ['url' => $this->data['config']->baseURL . 'frontend', 'icon' => 'fas fa-home', 'label' => 'Home'],
            ['url' => $this->data['config']->baseURL . 'frontend/about', 'icon' => 'fas fa-info-circle', 'label' => 'Tentang'],
            ['url' => $this->data['config']->baseURL . 'frontend/contact', 'icon' => 'fas fa-envelope', 'label' => 'Kontak'],
            ['url' => $this->data['config']->baseURL, 'icon' => 'fas fa-sign-in-alt', 'label' => 'Admin']
        ];
        $this->data['footer_contact_email'] = 'support@example.com';
        $this->data['footer_contact_url'] = $this->data['config']->baseURL . 'frontend/contact';
        $this->data['footer_about_text'] = 'Platform terbaik untuk menemukan item berkualitas tinggi dengan harga terbaik.';
        $this->data['footer_facebook_url'] = '#';
        $this->data['footer_membership_url'] = '#';
        $this->data['footer_blog_url'] = $this->data['config']->baseURL . 'frontend';
        $this->data['footer_copyright_title'] = 'Frontend';
        $this->data['footer_copyright_url'] = $this->data['config']->baseURL . 'frontend';
        $this->data['footer_menu'] = [
            ['url' => $this->data['config']->baseURL . 'frontend', 'label' => 'Home'],
            ['url' => $this->data['config']->baseURL . 'frontend/about', 'label' => 'About'],
            ['url' => $this->data['config']->baseURL . 'frontend/contact', 'label' => 'Contact']
        ];
        
        $this->data['content'] = view('themes/modern/frontend/item-detail', $this->data);
        return view('themes/modern/layout/content.php', $this->data);
    }
}
