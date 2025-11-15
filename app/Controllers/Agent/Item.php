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
use App\Models\ItemCategoryModel;
use App\Models\ItemBrandModel;
use App\Models\ItemSpecIdModel;
use CodeIgniter\HTTP\ResponseInterface;

class Item extends BaseController
{
    protected $itemModel;
    protected $categoryModel;
    protected $brandModel;
    protected $itemSpecIdModel;
    
    /**
     * Initialize models
     */
    public function __construct()
    {
        parent::__construct();
        $this->itemModel = new ItemModel();
        $this->categoryModel = new ItemCategoryModel();
        $this->brandModel = new ItemBrandModel();
        $this->itemSpecIdModel = new ItemSpecIdModel();
    }
    
    /**
     * Display items in grid layout (POS view)
     * 
     * @return void
     */
    public function index(): void
    {
        helper(['form', 'angka']);

        // Collect and sanitize filters
        $filters = $this->gatherFilters();

        // Base query with necessary joins
        $itemQuery = $this->buildFilteredQuery($filters);

        // Pagination & ordering
        $perPage = $filters['per_page'];
        $sortOption = $filters['sort'];
        $this->applySortOption($itemQuery, $sortOption);

        $items = $itemQuery->paginate($perPage, 'agent_item');

        // Convert to arrays for view compatibility
        $items = array_map(static function ($item) {
            return (array) $item;
        }, $items ?? []);

        // Pager & stats
        $pager = $itemQuery->pager;
        $pagerRenderer = new \CodeIgniter\Pager\PagerRenderer($pager->getDetails('agent_item'));

        $currentPage = $pager->getCurrentPage('agent_item');
        $totalPages = $pager->getPageCount('agent_item');
        $totalItems = $pager->getTotal('agent_item');

        // Global price range (for slider defaults)
        $priceStats = $this->itemModel
            ->select('MIN(price) as min_price, MAX(price) as max_price')
            ->where('status', '1')
            ->get()
            ->getRowArray() ?? ['min_price' => 0, 'max_price' => 0];

        // Auxiliary data for filters
        $categories = array_map([$this, 'convertRecordToArray'], $this->categoryModel->getActiveCategories() ?? []);
        $brands = array_map([$this, 'convertRecordToArray'], $this->brandModel->getActiveBrands() ?? []);

        // Prepare view data
        $this->data['title'] = 'Katalog Produk Agen';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['items'] = $items;
        $this->data['filters'] = $filters;
        $this->data['categories'] = $categories;
        $this->data['brands'] = $brands;
        $this->data['pager'] = $pagerRenderer;
        $displayCount = count($items);
        $firstItemNumber = $displayCount > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
        $lastItemNumber = $displayCount > 0 ? $firstItemNumber + $displayCount - 1 : 0;

        $this->data['pagerInfo'] = [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'perPage' => $perPage,
            'firstItem' => $firstItemNumber,
            'lastItem' => $lastItemNumber,
        ];
        $this->data['priceRange'] = [
            'min' => (float) ($priceStats['min_price'] ?? 0),
            'max' => (float) ($priceStats['max_price'] ?? 0),
        ];
        $this->data['sortOptions'] = $this->getSortOptions();
        $this->data['perPageOptions'] = [12, 24, 48, 96];

        // Render view
        $this->view('sales/agent/item-result', $this->data);
    }

    /**
     * Gather filter values from request
     *
     * @return array
     */
    protected function gatherFilters(): array
    {
        $search = trim((string) ($this->request->getGet('q') ?? ''));

        $category = $this->normalizeArrayFilter($this->request->getGet('category'));
        $brand = $this->normalizeArrayFilter($this->request->getGet('brand'));

        $priceMin = $this->sanitizePrice($this->request->getGet('price_min'));
        $priceMax = $this->sanitizePrice($this->request->getGet('price_max'));

        if ($priceMin !== null && $priceMax !== null && $priceMin > $priceMax) {
            [$priceMin, $priceMax] = [$priceMax, $priceMin];
        }

        $stockFilter = $this->request->getGet('availability');
        if (!in_array($stockFilter, ['all', 'stockable', 'non_stock'], true)) {
            $stockFilter = 'all';
        }

        $sort = $this->request->getGet('sort');
        $sortOptions = array_keys($this->getSortOptions());
        if (!in_array($sort, $sortOptions, true)) {
            $sort = 'popular';
        }

        $perPage = (int) ($this->request->getGet('per_page') ?? 12);
        if (!in_array($perPage, [12, 24, 48, 96], true)) {
            $perPage = 12;
        }

        $viewMode = $this->request->getGet('view');
        if (!in_array($viewMode, ['grid', 'list'], true)) {
            $viewMode = 'grid';
        }

        return [
            'search'      => $search,
            'category'    => $category,
            'brand'       => $brand,
            'price_min'   => $priceMin,
            'price_max'   => $priceMax,
            'availability'=> $stockFilter,
            'sort'        => $sort,
            'per_page'    => $perPage,
            'view'        => $viewMode,
        ];
    }

    /**
     * Build a filtered query for items
     *
     * @param array $filters
     * @return ItemModel
     */
    protected function buildFilteredQuery(array $filters): ItemModel
    {
        $model = new ItemModel();

        $model->select('item.*, item_category.category as category_name, item_brand.name as brand_name')
            ->join('item_category', 'item_category.id = item.category_id', 'left')
            ->join('item_brand', 'item_brand.id = item.brand_id', 'left')
            ->where('item.status', '1')
            ->where('item.is_agen', '1');

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $model->groupStart()
                ->like('item.name', $searchTerm)
                ->orLike('item.sku', $searchTerm)
                ->orLike('item.description', $searchTerm)
                ->groupEnd();
        }

        if (!empty($filters['category'])) {
            $model->whereIn('item.category_id', $filters['category']);
        }

        if (!empty($filters['brand'])) {
            $model->whereIn('item.brand_id', $filters['brand']);
        }

        if ($filters['price_min'] !== null) {
            $model->where('item.price >=', $filters['price_min']);
        }

        if ($filters['price_max'] !== null) {
            $model->where('item.price <=', $filters['price_max']);
        }

        if ($filters['availability'] === 'stockable') {
            $model->where('item.is_stockable', '1');
        } elseif ($filters['availability'] === 'non_stock') {
            $model->where('item.is_stockable', '0');
        }

        return $model;
    }

    /**
     * Apply ordering to the query builder
     *
     * @param ItemModel $model
     * @param string    $sortOption
     * @return void
     */
    protected function applySortOption(ItemModel $model, string $sortOption): void
    {
        switch ($sortOption) {
            case 'newest':
                $model->orderBy('item.created_at', 'DESC');
                break;
            case 'price_low_high':
                $model->orderBy('item.price', 'ASC');
                break;
            case 'price_high_low':
                $model->orderBy('item.price', 'DESC');
                break;
            case 'name_az':
                $model->orderBy('item.name', 'ASC');
                break;
            case 'name_za':
                $model->orderBy('item.name', 'DESC');
                break;
            default:
                // Default "popular" fallback (by updated_at then name)
                $model->orderBy('item.updated_at', 'DESC')
                    ->orderBy('item.name', 'ASC');
                break;
        }
    }

    /**
     * Normalize filter input to array of integers
     *
     * @param mixed $value
     * @return array
     */
    protected function normalizeArrayFilter($value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        if (!is_array($value)) {
            return [];
        }

        $value = array_filter(array_map(static function ($item) {
            $item = trim((string) $item);
            return ctype_digit($item) ? (int) $item : null;
        }, $value));

        return array_values(array_unique(array_filter($value, static function ($item) {
            return $item !== null;
        })));
    }

    /**
     * Sanitize price input into float or null
     *
     * @param mixed $value
     * @return float|null
     */
    protected function sanitizePrice($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }

        if (!is_numeric($value)) {
            return null;
        }

        $floatValue = (float) $value;
        return $floatValue < 0 ? null : $floatValue;
    }

    /**
     * Available sort options
     *
     * @return array
     */
    protected function getSortOptions(): array
    {
        return [
            'popular'        => 'Paling Relevan',
            'newest'         => 'Terbaru',
            'price_low_high' => 'Harga: Rendah ke Tinggi',
            'price_high_low' => 'Harga: Tinggi ke Rendah',
            'name_az'        => 'Nama: A ke Z',
            'name_za'        => 'Nama: Z ke A',
        ];
    }

    /**
     * Convert model record (object/array) to array safely
     *
     * @param mixed $record
     * @return array
     */
    protected function convertRecordToArray($record): array
    {
        if (is_array($record)) {
            return $record;
        }

        if (is_object($record)) {
            return get_object_vars($record);
        }

        return [];
    }

    /**
     * Display product detail page
     * 
     * @param int $id Item ID
     * @return string|ResponseInterface
     */
    public function detail($id)
    {
        helper(['form', 'angka']);

        try {
            // Get item details
            $item = $this->itemModel
                ->select('item.*, item_category.category as category_name, item_brand.name as brand_name')
                ->join('item_category', 'item_category.id = item.category_id', 'left')
                ->join('item_brand', 'item_brand.id = item.brand_id', 'left')
                ->where('item.id', $id)
                ->where('item.status', '1')
                ->where('item.is_agen', '1')
                ->first();

            if (!$item) {
                $this->session->setFlashdata('message', 'Produk tidak ditemukan.');
                return redirect()->to('agent/item');
            }

            // Convert to array
            $itemData = is_array($item) ? $item : (array) $item;

            // Get specifications
            $specifications = $this->itemSpecIdModel->getSpecsForItem($id);
            $specsData = [];
            foreach ($specifications as $spec) {
                $specsData[] = [
                    'name' => $spec->spec_name ?? '',
                    'value' => $spec->value ?? ''
                ];
            }

            // Prepare view data
            $this->data['title'] = ($itemData['name'] ?? 'Detail Produk') . ' - Katalog Produk Agen';
            $this->data['currentModule'] = $this->currentModule;
            $this->data['config'] = $this->config;
            $this->data['item'] = $itemData;
            $this->data['specifications'] = $specsData;

            // Render view
            return $this->view('sales/agent/item-detail', $this->data);

        } catch (\Exception $e) {
            log_message('error', 'Agent\Item::detail error: ' . $e->getMessage());
            $this->session->setFlashdata('message', 'Gagal memuat detail produk: ' . $e->getMessage());
            return redirect()->to('agent/item');
        }
    }
}

