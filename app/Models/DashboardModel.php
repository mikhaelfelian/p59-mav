<?php
/**
 * Admin Template Codeigniter 4
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Dashboard Model
 * Handles dashboard analytics and statistics
 * 
 * CI 4.3.1 compliant | PSR-4
 * 
 * @package    CodeIgniter
 * @category   Model
 * @author     Agus Prawoto Hadi
 * @version    4.3.1
 *
 * Modified by Mikhael Felian Waskito
 * @link       https://github.com/mikhaelfelian/p59-mav
 * @notes      Refactored to CI4-compliant structure using BaseModel inheritance and Query Builder
 */
class DashboardModel extends Model
{
    // These properties are not directly used but are kept for compatibility
    protected $table = 'dashboard';
    protected $primaryKey = 'id_dashboard';
    protected $allowedFields = [];
    protected $useTimestamps = false;

    /**
     * Get list of years from sales (using created_at)
     * 
     * @return array
     */
    public function getListTahun(): array
    {
        return $this->db->table('sales')
            ->select('YEAR(created_at) AS tahun')
            ->groupBy('tahun')
            ->orderBy('tahun', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get total items sold with growth calculation
     * Using sales_item_sn for item count, based on its created_at
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getTotalItemTerjual(int $tahun): array
    {
        $builder = $this->db->table('sales_item_sn');
        $builder->select([
            "(SELECT COUNT(id) FROM sales_item_sn WHERE YEAR(created_at) = {$tahun}) AS jml",
            "(SELECT COUNT(id) FROM sales_item_sn WHERE YEAR(created_at) = " . ($tahun - 1) . ") AS jml_prev"
        ]);
        $result = $builder->get()->getRowArray();
        $jml = (int)($result['jml'] ?? 0);
        $jml_prev = (int)($result['jml_prev'] ?? 0);
        $growth = $jml_prev > 0 ? round(($jml - $jml_prev) / $jml_prev * 100, 2) : 0;
        return ['jml' => $jml, 'jml_prev' => $jml_prev, 'growth' => $growth];
    }

    /**
     * Get total transactions value (sum of grand_total) with growth calculation
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getTotalJumlahTransaksi(int $tahun): array
    {
        $builder = $this->db->table('sales')
            ->select([
                "(SELECT COALESCE(SUM(grand_total),0) FROM sales WHERE YEAR(created_at) = {$tahun}) AS jml",
                "(SELECT COALESCE(SUM(grand_total),0) FROM sales WHERE YEAR(created_at) = " . ($tahun - 1) . ") AS jml_prev"
            ]);
        $result = $builder->get()->getRowArray();
        $jml = floatval($result['jml'] ?? 0);
        $jml_prev = floatval($result['jml_prev'] ?? 0);
        $growth = $jml_prev > 0 ? round(($jml - $jml_prev) / $jml_prev * 100, 2) : 0;
        return ['jml' => $jml, 'jml_prev' => $jml_prev, 'growth' => $growth];
    }

    /**
     * Get total active customers with growth calculation
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getTotalPelangganAktif(int $tahun): array
    {
        // current year
        $result_curr = $this->db->table('sales')
            ->select('customer_id')
            ->where('customer_id IS NOT NULL', null, false)
            ->where('YEAR(created_at)', $tahun)
            ->groupBy('customer_id')
            ->get()->getResultArray();
        $jml = count($result_curr);

        // previous year
        $result_prev = $this->db->table('sales')
            ->select('customer_id')
            ->where('customer_id IS NOT NULL', null, false)
            ->where('YEAR(created_at)', $tahun - 1)
            ->groupBy('customer_id')
            ->get()->getResultArray();
        $jml_prev = count($result_prev);

        // total unique customers (with non-null customer_id ever in sales)
        $result_total = $this->db->table('sales')
            ->select('customer_id')
            ->where('customer_id IS NOT NULL', null, false)
            ->groupBy('customer_id')
            ->get()->getResultArray();
        $total = count($result_total);

        $growth = $jml_prev > 0 ? round(($jml - $jml_prev) / $jml_prev * 100) : 0;

        return ['jml' => $jml, 'jml_prev' => $jml_prev, 'growth' => $growth, 'total' => $total];
    }

    /**
     * Get total sales value with growth calculation (grand_total)
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getTotalNilaiPenjualan(int $tahun): array
    {
        $tahun_curr = intval($tahun);
        $tahun_prev = intval($tahun) - 1;
        $builder = $this->db->table('sales');
        $builder->select([
            "(SELECT COALESCE(SUM(grand_total),0) FROM sales WHERE YEAR(created_at) = $tahun_curr) AS jml",
            "(SELECT COALESCE(SUM(grand_total),0) FROM sales WHERE YEAR(created_at) = $tahun_prev) AS jml_prev"
        ]);
        $result = $builder->get()->getRowArray();
        $jml = floatval($result['jml'] ?? 0);
        $jml_prev = floatval($result['jml_prev'] ?? 0);
        $growth = $jml_prev > 0 ? round(($jml - $jml_prev) / $jml_prev * 100, 2) : 0;
        return ['jml' => $jml, 'jml_prev' => $jml_prev, 'growth' => $growth];
    }

    /**
     * Get top customers by purchase
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getPembelianPelangganTerbesar(int $tahun): array
    {
        // customer_id can be null
        return $this->db->table('sales')
            ->select('sales.customer_id')
            ->select('customer.name')
            ->select('customer.name AS nama_pelanggan')
            ->select('customer.plat_code')
            ->select('customer.plat_number')
            ->select('customer.plat_last')
            ->select('SUM(sales.grand_total) AS total_harga')
            ->join('customer', 'customer.id = sales.customer_id', 'left')
            ->where('YEAR(sales.created_at)', $tahun)
            ->where('sales.customer_id IS NOT NULL', null, false)
            ->groupBy('sales.customer_id')
            ->orderBy('total_harga', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
    }

    /**
     * Get sales per month for a single year (for AJAX endpoint)
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getPenjualan(int $tahun): array
    {
        $tgl_start = $tahun . '-01-01 00:00:00';
        $tgl_end = $tahun . '-12-31 23:59:59';

        return $this->db->table('sales')
            ->select('MONTH(created_at) AS bulan')
            ->select('SUM(grand_total) AS total')
            ->where('created_at >=', $tgl_start)
            ->where('created_at <=', $tgl_end)
            ->groupBy('MONTH(created_at)')
            ->orderBy('bulan', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get sales series by month for multiple years from 'sales' table
     * 
     * @param array $list_tahun List of years
     * @return array
     */
    public function getSeriesPenjualan(array $list_tahun): array
    {
        $result = [];
        foreach ($list_tahun as $tahun) {
            $tgl_start = $tahun . '-01-01 00:00:00';
            $tgl_end = $tahun . '-12-31 23:59:59';

            $result[$tahun] = $this->db->table('sales')
                ->select('MONTH(created_at) AS bulan')
                ->select('COUNT(id) AS JML')
                ->select('SUM(grand_total) AS total')
                ->where('created_at >=', $tgl_start)
                ->where('created_at <=', $tgl_end)
                ->groupBy('MONTH(created_at)')
                ->orderBy('bulan', 'ASC')
                ->get()
                ->getResultArray();
        }
        return $result;
    }

    /**
     * Get total sales (grand_total) series for multiple years from sales table
     * 
     * @param array $list_tahun List of years
     * @return array
     */
    public function getSeriesTotalPenjualan(array $list_tahun): array
    {
        $result = [];
        foreach ($list_tahun as $tahun) {
            $tgl_start = $tahun . '-01-01 00:00:00';
            $tgl_end = $tahun . '-12-31 23:59:59';
            $result[$tahun] = $this->db->table('sales')
                ->select('SUM(grand_total) AS total')
                ->where('created_at >=', $tgl_start)
                ->where('created_at <=', $tgl_end)
                ->get()
                ->getRowArray(); // only one row per year
        }
        return $result;
    }

    /**
     * Get top 7 items sold in a year (from `sales_detail` and `item` table)
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getItemTerjual(int $tahun): array
    {
        $tgl_start = $tahun . '-01-01 00:00:00';
        $tgl_end = $tahun . '-12-31 23:59:59';

        return $this->db->table('sales_detail')
            ->select('sales_detail.item_id')
            ->select('COALESCE(sales_detail.item, item.name) AS nama_barang')
            ->select('SUM(sales_detail.qty) AS jml')
            ->join('sales', 'sales.id = sales_detail.sale_id', 'left')
            ->join('item', 'item.id = sales_detail.item_id', 'left')
            ->where('sales.created_at >=', $tgl_start)
            ->where('sales.created_at <=', $tgl_end)
            ->groupBy('sales_detail.item_id')
            ->orderBy('jml', 'DESC')
            ->limit(7)
            ->get()
            ->getResultArray();
    }

    /**
     * Get categories sold in a year
     * (Assumes there is item_category table, item table with category_id)
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getKategoriTerjual(int $tahun): array
    {
        $tgl_start = $tahun . '-01-01 00:00:00';
        $tgl_end = $tahun . '-12-31 23:59:59';

        // Only join if you have item_category setup
        return $this->db->table('sales_detail')
            ->select('item.category_id AS id_kategori, item_category.category AS nama_kategori')
            ->select('COUNT(sales_detail.item_id) AS jml')
            ->select('SUM(sales_detail.amount) AS nilai')
            ->join('sales', 'sales.id = sales_detail.sale_id', 'left')
            ->join('item', 'item.id = sales_detail.item_id', 'left')
            ->join('item_category', 'item_category.id = item.category_id', 'left')
            ->where('sales.created_at >=', $tgl_start)
            ->where('sales.created_at <=', $tgl_end)
            ->groupBy('item.category_id')
            ->orderBy('nilai', 'DESC')
            ->limit(7)
            ->get()
            ->getResultArray();
    }

    /**
     * Get latest items (item table must have created_at)
     * 
     * @return array
     */
    public function getItemTerbaru(): array
    {
        return $this->db->table('item')
            ->select('item.*, item.name AS nama_barang, item.price AS harga_jual')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
    }

    /**
     * Get latest sales transactions for a given year (from sales and sales_detail)
     * @param int $tahun Year
     * @return array
     */
    public function penjualanTerbaru(int $tahun): array
    {
        $start_date = $tahun . '-01-01 00:00:00';
        $end_date = $tahun . '-12-31 23:59:59';

        return $this->db->table('sales')
            ->select('COALESCE(customer.name, "Umum") AS nama_pelanggan')
            ->select('COALESCE(SUM(sales_detail.qty), 0) AS jumlah_item')
            ->select('COALESCE(SUM(sales_detail.qty), 0) AS jml_barang')
            ->select('sales.grand_total')
            ->select('sales.total_amount')
            ->select('sales.payment_status')
            ->select('sales.created_at')
            ->select('sales.created_at AS tgl_transaksi')
            ->select('sales.id AS id_penjualan')
            ->join('sales_detail', 'sales_detail.sale_id = sales.id', 'left')
            ->join('customer', 'customer.id = sales.customer_id', 'left')
            ->where('sales.created_at >=', $start_date)
            ->where('sales.created_at <=', $end_date)
            ->groupBy('sales.id')
            ->orderBy('sales.created_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();
    }

    /**
     * Count all distinct sold items from sales_detail+sales for a given year
     * @param int $tahun Year
     * @return int
     */
    public function countAllDataPejualanTerbesar(int $tahun): int
    {
        $start_date = $tahun . '-01-01 00:00:00';
        $end_date   = $tahun . '-12-31 23:59:59';

        return $this->db->table('sales_detail')
            ->select('sales_detail.item_id')
            ->join('sales', 'sales.id = sales_detail.sale_id', 'left')
            ->where('sales.created_at >=', $start_date)
            ->where('sales.created_at <=', $end_date)
            ->groupBy('sales_detail.item_id')
            ->countAllResults();
    }

    /**
     * Get list of best selling items (by sales amount) for a given year, with paging/search/sort
     *
     * @param int $tahun Year
     * @return array
     */
    public function getListDataPenjualanTerbesar(int $tahun): array
    {
        $request = \Config\Services::request();
        $columns    = $request->getPost('columns');
        $search_all = $request->getPost('search')['value'] ?? '';
        $order_data = $request->getPost('order') ?? [];
        $start      = $request->getPost('start') ?? 0;
        $length     = $request->getPost('length') ?? 10;

        $start_date = $tahun . '-01-01 00:00:00';
        $end_date   = $tahun . '-12-31 23:59:59';

        // Get total sales (sum of all 'amount') for this year, for contribution calculation
        $total_sales_row = $this->db->table('sales_detail')
            ->select('SUM(amount) as total')
            ->join('sales', 'sales.id = sales_detail.sale_id', 'left')
            ->where('sales.created_at >=', $start_date)
            ->where('sales.created_at <=', $end_date)
            ->get()
            ->getRowArray();
        $total_penjualan = $total_sales_row && isset($total_sales_row['total']) ? (float)$total_sales_row['total'] : 1;

         // Build main query (group by item)
         $builder = $this->db->table('sales_detail')
             ->select('sales_detail.item_id')
             ->select('COALESCE(sales_detail.item, item.name) AS nama_barang')
             ->select('SUM(sales_detail.qty) AS jml_terjual')
             ->select('SUM(sales_detail.amount) AS total_harga')
             ->select('MAX(sales_detail.price) AS harga_satuan')
             ->join('sales', 'sales.id = sales_detail.sale_id', 'left')
             ->join('item', 'item.id = sales_detail.item_id', 'left')
             ->where('sales.created_at >=', $start_date)
             ->where('sales.created_at <=', $end_date)
             ->groupBy('sales_detail.item_id');

        // Apply search
        if ($search_all && !empty($columns)) {
            $builder->groupStart();
            foreach ($columns as $val) {
                if (strpos($val['data'] ?? '', 'ignore') === false && isset($val['data']) && !empty($val['data'])) {
                    $builder->orLike($val['data'], $search_all);
                }
            }
            $builder->groupEnd();
        }

        // Apply ordering
        if (!empty($order_data) && isset($columns[$order_data[0]['column']])) {
            $dir = strtoupper($order_data[0]['dir'] ?? 'ASC');
            $column = $columns[$order_data[0]['column']]['data'] ?? '';
            if ($column && strpos($column, 'ignore_search') === false) {
                $builder->orderBy($column, $dir);
            }
        } else {
            // Default order: biggest total_harga first
            $builder->orderBy('total_harga', 'DESC');
        }

        // Get total filtered
        $total_filtered = $builder->countAllResults(false);

        // Get paginated data
        $data = $builder->limit($length, $start)->get()->getResultArray();

        // Add contribution percentage to each row
        foreach ($data as &$row) {
            $row['kontribusi'] = $total_penjualan > 0
                ? round((($row['total_harga'] ?? 0) / $total_penjualan) * 100, 0)
                : 0;
        }

        return ['data' => $data, 'total_filtered' => $total_filtered];
    }
}
