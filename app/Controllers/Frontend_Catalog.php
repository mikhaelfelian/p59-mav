<?php

namespace App\Controllers;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-27 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Frontend controller for displaying product catalog on public pages
 * This file represents the Controller.
 */
class Frontend_Catalog extends BaseController
{
    protected $itemModel;

    public function __construct()
    {
        parent::__construct();
        $this->itemModel = new \App\Models\ItemModel();
    }

    public function index()
    {
        $this->data['current_module'] = $this->currentModule;
        $this->data['msg'] = $this->session->getFlashdata('message');
        
        // Items per page
        $perPage = $this->request->getGet('per_page') ?? 12; // Default 12 items per page
        
        // Get active items with brand and category information using pagination
        $items = $this->itemModel->select('item.*, item_brand.name as brand_name, item_category.category as category_name')
                                                ->join('item_brand', 'item_brand.id = item.brand_id', 'left')
                                                ->join('item_category', 'item_category.id = item.category_id', 'left')
                                                ->where('item.status', '1')
                                                ->where('item.is_catalog', '1')
                                                ->orderBy('item.name', 'ASC')
                                                ->paginate($perPage, 'default');
        
        // Convert objects to arrays for view compatibility
        $items = array_map(function($item) {
            return (array) $item;
        }, $items);
        
        // Get pager instance and create renderer for pagination links
        $pager = $this->itemModel->pager;
        $pagerRenderer = new \CodeIgniter\Pager\PagerRenderer($pager->getDetails('default'));
        
        // Get pagination info from Pager instance
        $currentPage = $pager->getCurrentPage('default');
        $totalPages = $pager->getPageCount('default');
        $totalItems = $pager->getTotal('default');
        
        // Layout data for MAV theme - from database
        $this->data['title'] = $this->currentModule['judul_module'] ?? 'Katalog Produk';
        $this->data['meta_description'] = $this->currentModule['deskripsi'] ?? 'Lihat katalog lengkap produk Multi Automobile Vision. Temukan produk berkualitas tinggi dengan harga terbaik.';
        
        // Pass items and pager to view
        $this->data['items'] = $items;
        $this->data['pager'] = $pagerRenderer;
        $this->data['pagerInfo'] = [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'perPage' => $perPage
        ];
        
        // Render using the MAV catalog template
        return view('themes/mav/catalog', $this->data);
    }
}
