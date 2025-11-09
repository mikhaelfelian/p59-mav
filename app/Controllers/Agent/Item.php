<?php

/**
 * Agent Item Controller (POS View)
 * 
 * Displays available products for agents in a grid-style POS interface
 * 
 * @package    App\Controllers\Agent
 * @author     Mikhael Felian Waskito <mikhaelfelian@gmail.com>
 * @copyright  2025
 * @license    MIT
 * @version    1.0.0
 * @since      2025-11-04
 */

namespace App\Controllers\Agent;

use App\Controllers\BaseController;
use App\Models\ItemModel;
use CodeIgniter\HTTP\ResponseInterface;

class Item extends BaseController
{
    protected $itemModel;
    
    /**
     * Initialize models
     */
    public function __construct()
    {
        parent::__construct();
        $this->itemModel = new ItemModel();
    }
    
    /**
     * Display items in grid layout (POS view)
     * 
     * @return void
     */
    public function index(): void
    {
        // Get search query
        $searchQuery = $this->request->getGet('q') ?? '';
        $searchQuery = trim($searchQuery);
        
        // Pagination settings
        $perPage = 12;
        
        // Get paginated items with search
        $items = $this->itemModel
            ->search($searchQuery)
            ->where('status', '1')
            ->orderBy('name', 'ASC')
            ->paginate($perPage, 'agent_item');
        
        // Convert objects to arrays for view compatibility
        $items = array_map(function($item) {
            return (array) $item;
        }, $items);
        
        // Get pager instance
        $pager = $this->itemModel->pager;
        $pagerRenderer = new \CodeIgniter\Pager\PagerRenderer($pager->getDetails('agent_item'));
        
        // Get pagination info
        $currentPage = $pager->getCurrentPage('agent_item');
        $totalPages = $pager->getPageCount('agent_item');
        $totalItems = $pager->getTotal('agent_item');
        
        // Prepare view data
        $this->data['title'] = 'Daftar Produk';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['items'] = $items;
        $this->data['searchQuery'] = $searchQuery;
        $this->data['pager'] = $pagerRenderer;
        $this->data['pagerInfo'] = [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'perPage' => $perPage
        ];
        
        // Load helper for currency formatting
        helper('angka');
        
        // Render view
        $this->view('sales/agent/item-result', $this->data);
    }
}

