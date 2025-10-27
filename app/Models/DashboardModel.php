<?php
/**
 * Admin Template Codeigniter 4
 * Author: Agus Prawoto Hadi
 * Website: https://jagowebdev.com
 * Year: 2020-2023
 */

namespace App\Models;

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
class DashboardModel extends BaseModel
{
    protected $table = 'dashboard';
    protected $primaryKey = 'id_dashboard';
    protected $allowedFields = [];
    protected $useTimestamps = false;

    /**
     * Get list of years from transactions
     * 
     * @return array
     */
    public function getListTahun(): array
    {
        return $this->builder('toko_penjualan')
                   ->select('YEAR(tgl_transaksi) AS tahun')
                   ->groupBy('tahun')
                   ->get()
                   ->getResultArray();
    }

    /**
     * Get total items sold with growth calculation
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getTotalItemTerjual(int $tahun): array
    {
        $tahun_curr = $tahun . '%';
        $tahun_prev = ($tahun - 1) . '%';

        $result = $this->builder('toko_penjualan_detail')
                      ->select('COUNT(IF(tgl_transaksi LIKE "' . $tahun_curr . '", id_barang, NULL)) AS jml')
                      ->select('COUNT(IF(tgl_transaksi LIKE "' . $tahun_prev . '", id_barang, NULL)) AS jml_prev')
                      ->join('toko_penjualan', 'toko_penjualan.id_penjualan = toko_penjualan_detail.id_penjualan', 'left')
                      ->where('tgl_transaksi LIKE "' . $tahun_curr . '" OR tgl_transaksi LIKE "' . $tahun_prev . '"', null, false)
                      ->get()
                      ->getResultArray();

        if (empty($result)) {
            return ['jml' => 0, 'jml_prev' => 0, 'growth' => 0];
        }

        $jml = $result[0]['jml'] ?? 0;
        $jml_prev = $result[0]['jml_prev'] ?? 0;
        $growth = $jml_prev > 0 ? round(($jml - $jml_prev) / $jml_prev * 100, 2) : 0;

        return ['jml' => $jml, 'jml_prev' => $jml_prev, 'growth' => $growth];
    }

    /**
     * Get total number of transactions with growth calculation
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getTotalJumlahTransaksi(int $tahun): array
    {
        $tahun_curr = $tahun . '%';
        $tahun_prev = ($tahun - 1) . '%';

        $result = $this->builder('toko_penjualan')
                      ->select('COUNT(IF(tgl_transaksi LIKE "' . $tahun_curr . '", id_penjualan, NULL)) AS jml')
                      ->select('COUNT(IF(tgl_transaksi LIKE "' . $tahun_prev . '", id_penjualan, NULL)) AS jml_prev')
                      ->where('tgl_transaksi LIKE "' . $tahun_curr . '" OR tgl_transaksi LIKE "' . $tahun_prev . '"', null, false)
                      ->get()
                      ->getResultArray();

        if (empty($result)) {
            return ['jml' => 0, 'jml_prev' => 0, 'growth' => 0];
        }

        $jml = $result[0]['jml'] ?? 0;
        $jml_prev = $result[0]['jml_prev'] ?? 0;
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
        $tahun_curr = $tahun . '%';
        $tahun_prev = ($tahun - 1) . '%';

        // Get active customer counts
        $result = $this->builder('toko_penjualan')
                      ->select('MAX(IF(tgl_transaksi LIKE "' . $tahun_curr . '", 1, NULL)) AS jml')
                      ->select('MAX(IF(tgl_transaksi LIKE "' . $tahun_prev . '", 1, NULL)) AS jml_prev')
                      ->where('tgl_transaksi LIKE "' . $tahun_curr . '" OR tgl_transaksi LIKE "' . $tahun_prev . '"', null, false)
                      ->groupBy('id_pelanggan')
                      ->get()
                      ->getResultArray();

        $jml = count(array_filter($result, function($r) { return $r['jml'] == 1; }));
        $jml_prev = count(array_filter($result, function($r) { return $r['jml_prev'] == 1; }));

        // Get total customers
        $total = $this->builder('toko_pelanggan')->countAllResults();

        $growth = $jml_prev > 0 ? round(($jml - $jml_prev) / $jml_prev * 100) : 0;

        return ['jml' => $jml, 'jml_prev' => $jml_prev, 'growth' => $growth, 'total' => $total];
    }

    /**
     * Get total sales value with growth calculation
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getTotalNilaiPenjualan(int $tahun): array
    {
        $tahun_curr = $tahun . '%';
        $tahun_prev = ($tahun - 1) . '%';

        $result = $this->builder('toko_penjualan')
                      ->select('SUM(IF(tgl_transaksi LIKE "' . $tahun_curr . '", total_harga, 0)) AS jml')
                      ->select('SUM(IF(tgl_transaksi LIKE "' . $tahun_prev . '", total_harga, 0)) AS jml_prev')
                      ->where('tgl_transaksi LIKE "' . $tahun_curr . '" OR tgl_transaksi LIKE "' . $tahun_prev . '"', null, false)
                      ->get()
                      ->getRowArray();

        if (!$result) {
            return ['jml' => 0, 'jml_prev' => 0, 'growth' => 0];
        }

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
        return $this->builder('toko_penjualan')
                   ->select('toko_penjualan.id_pelanggan, toko_pelanggan.foto, toko_pelanggan.nama_pelanggan')
                   ->select('SUM(toko_penjualan.total_harga) AS total_harga')
                   ->join('toko_pelanggan', 'toko_pelanggan.id_pelanggan = toko_penjualan.id_pelanggan', 'left')
                   ->where('YEAR(toko_penjualan.tgl_transaksi)', $tahun)
                   ->groupBy('toko_penjualan.id_pelanggan')
                   ->orderBy('total_harga', 'DESC')
                   ->limit(5)
                   ->get()
                   ->getResultArray();
    }

    /**
     * Get sales series by month for multiple years
     * 
     * @param array $list_tahun List of years
     * @return array
     */
    public function getSeriesPenjualan(array $list_tahun): array
    {
        $result = [];
        foreach ($list_tahun as $tahun) {
            $tgl_start = $tahun . '-01-01';
            $tgl_end = $tahun . '-12-31';

            $result[$tahun] = $this->builder('toko_penjualan')
                                  ->select('MONTH(tgl_transaksi) AS bulan')
                                  ->select('COUNT(id_penjualan) as JML')
                                  ->select('SUM(total_harga) as total')
                                  ->where('tgl_transaksi >=', $tgl_start)
                                  ->where('tgl_transaksi <=', $tgl_end)
                                  ->groupBy('MONTH(tgl_transaksi)')
                                  ->get()
                                  ->getResultArray();
        }
        return $result;
    }

    /**
     * Get total sales series for multiple years
     * 
     * @param array $list_tahun List of years
     * @return array
     */
    public function getSeriesTotalPenjualan(array $list_tahun): array
    {
        $result = [];
        foreach ($list_tahun as $tahun) {
            $tgl_start = $tahun . '-01-01';
            $tgl_end = $tahun . '-12-31';

            $result[$tahun] = $this->builder('toko_penjualan')
                                  ->select('SUM(total_harga) AS total')
                                  ->where('tgl_transaksi >=', $tgl_start)
                                  ->where('tgl_transaksi <=', $tgl_end)
                                  ->get()
                                  ->getResultArray();
        }
        return $result;
    }

    /**
     * Get items sold in a year
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getItemTerjual(int $tahun): array
    {
        $tgl_start = $tahun . '-01-01';
        $tgl_end = $tahun . '-12-31';

        return $this->builder('toko_penjualan_detail')
                   ->select('toko_barang.id_barang, toko_barang.nama_barang')
                   ->select('COUNT(toko_penjualan_detail.id_barang) AS jml')
                   ->join('toko_penjualan', 'toko_penjualan.id_penjualan = toko_penjualan_detail.id_penjualan', 'left')
                   ->join('toko_barang', 'toko_barang.id_barang = toko_penjualan_detail.id_barang', 'left')
                   ->where('toko_penjualan.tgl_transaksi >=', $tgl_start)
                   ->where('toko_penjualan.tgl_transaksi <=', $tgl_end)
                   ->groupBy('toko_barang.id_barang')
                   ->orderBy('jml', 'DESC')
                   ->limit(7)
                   ->get()
                   ->getResultArray();
    }

    /**
     * Get categories sold in a year
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getKategoriTerjual(int $tahun): array
    {
        $tgl_start = $tahun . '-01-01';
        $tgl_end = $tahun . '-12-31';

        return $this->builder('toko_penjualan_detail')
                   ->select('toko_barang_kategori.id_kategori, toko_barang_kategori.nama_kategori')
                   ->select('COUNT(toko_penjualan_detail.id_barang) AS jml')
                   ->select('SUM(toko_penjualan_detail.harga) AS nilai')
                   ->join('toko_penjualan', 'toko_penjualan.id_penjualan = toko_penjualan_detail.id_penjualan', 'left')
                   ->join('toko_barang', 'toko_barang.id_barang = toko_penjualan_detail.id_barang', 'left')
                   ->join('toko_barang_kategori', 'toko_barang_kategori.id_kategori = toko_barang.id_kategori', 'left')
                   ->where('toko_penjualan.tgl_transaksi >=', $tgl_start)
                   ->where('toko_penjualan.tgl_transaksi <=', $tgl_end)
                   ->groupBy('toko_barang_kategori.id_kategori')
                   ->orderBy('nilai', 'DESC')
                   ->limit(7)
                   ->get()
                   ->getResultArray();
    }

    /**
     * Get latest items
     * 
     * @return array
     */
    public function getItemTerbaru(): array
    {
        return $this->builder('toko_barang')
                   ->orderBy('tgl_input', 'DESC')
                   ->limit(5)
                   ->get()
                   ->getResultArray();
    }

    /**
     * Get latest sales transactions
     * 
     * @param int $tahun Year
     * @return array
     */
    public function penjualanTerbaru(int $tahun): array
    {
        $tahun_str = $tahun . '%';

        return $this->builder('toko_penjualan')
                   ->select('toko_pelanggan.nama_pelanggan')
                   ->select('SUM(toko_penjualan_detail.jml_barang) AS jml_barang')
                   ->select('MAX(toko_penjualan.total_harga) AS total_harga')
                   ->select('MAX(toko_penjualan.tgl_transaksi) AS tgl_transaksi')
                   ->select('toko_penjualan.id_penjualan')
                   ->join('toko_penjualan_detail', 'toko_penjualan_detail.id_penjualan = toko_penjualan.id_penjualan', 'left')
                   ->join('toko_pelanggan', 'toko_pelanggan.id_pelanggan = toko_penjualan.id_pelanggan', 'left')
                   ->like('toko_penjualan.tgl_transaksi', $tahun_str)
                   ->groupBy('toko_penjualan.id_penjualan')
                   ->orderBy('toko_penjualan.tgl_transaksi', 'DESC')
                   ->limit(50)
                   ->get()
                   ->getResultArray();
    }

    /**
     * Count all sales data
     * 
     * @param int $tahun Year
     * @return int
     */
    public function countAllDataPejualanTerbesar(int $tahun): int
    {
        $tgl_start = $tahun . '-01-01';
        $tgl_end = $tahun . '-12-31';

        return $this->builder('toko_penjualan_detail')
                   ->select('id_barang')
                   ->join('toko_penjualan', 'toko_penjualan.id_penjualan = toko_penjualan_detail.id_penjualan', 'left')
                   ->where('toko_penjualan.tgl_transaksi >=', $tgl_start)
                   ->where('toko_penjualan.tgl_transaksi <=', $tgl_end)
                   ->groupBy('id_barang')
                   ->countAllResults();
    }

    /**
     * Get list of sales data with pagination
     * 
     * @param int $tahun Year
     * @return array
     */
    public function getListDataPenjualanTerbesar(int $tahun): array
    {
        $columns = $this->request->getPost('columns');
        $search_all = $this->request->getPost('search')['value'] ?? '';
        $order_data = $this->request->getPost('order') ?? [];
        $start = $this->request->getPost('start') ?? 0;
        $length = $this->request->getPost('length') ?? 10;

        $tgl_start = $tahun . '-01-01';
        $tgl_end = $tahun . '-12-31';

        // Get total sales for contribution calculation
        $total_sales = $this->builder('toko_penjualan_detail')
                          ->select('SUM(harga) as total')
                          ->join('toko_penjualan', 'toko_penjualan.id_penjualan = toko_penjualan_detail.id_penjualan', 'left')
                          ->where('toko_penjualan.tgl_transaksi >=', $tgl_start)
                          ->where('toko_penjualan.tgl_transaksi <=', $tgl_end)
                          ->get()
                          ->getRowArray();
        $total_penjualan = $total_sales['total'] ?? 1;

        // Build main query
        $builder = $this->builder('toko_penjualan_detail')
                       ->select('toko_barang.id_barang, toko_barang.nama_barang, toko_barang.harga_satuan')
                       ->select('COUNT(toko_penjualan_detail.id_barang) AS jml_terjual')
                       ->select('SUM(toko_penjualan_detail.harga) AS total_harga')
                       ->join('toko_penjualan', 'toko_penjualan.id_penjualan = toko_penjualan_detail.id_penjualan', 'left')
                       ->join('toko_barang', 'toko_barang.id_barang = toko_penjualan_detail.id_barang', 'left')
                       ->where('toko_penjualan.tgl_transaksi >=', $tgl_start)
                       ->where('toko_penjualan.tgl_transaksi <=', $tgl_end)
                       ->groupBy('toko_barang.id_barang');

        // Apply search
        if ($search_all && !empty($columns)) {
            $builder->groupStart();
            foreach ($columns as $val) {
                if (strpos($val['data'] ?? '', 'ignore') === false) {
                    $builder->orLike($val['data'], $search_all);
                }
            }
            $builder->groupEnd();
        }

        // Apply ordering
        if (!empty($order_data) && isset($columns[$order_data[0]['column']])) {
            $dir = strtoupper($order_data[0]['dir'] ?? 'ASC');
            $column = $columns[$order_data[0]['column']]['data'] ?? '';
            if (strpos($column, 'ignore_search') === false) {
                $builder->orderBy($column, $dir);
            }
        }

        // Get total filtered
        $total_filtered = $builder->countAllResults(false);

        // Get paginated data
        $data = $builder->limit($length, $start)->get()->getResultArray();

        // Add contribution percentage
        foreach ($data as &$row) {
            $row['kontribusi'] = round(($row['total_harga'] / $total_penjualan) * 100, 0);
        }

        return ['data' => $data, 'total_filtered' => $total_filtered];
    }
}
