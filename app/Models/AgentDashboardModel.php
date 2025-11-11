<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Agent Dashboard Model
 * Provides sales analytics limited to the agent(s) assigned to the current user.
 *
 * @author Mikhael
 */
class AgentDashboardModel extends Model
{
    protected array $agentIds = [];

    /**
     * Assign agent IDs to filter queries.
     *
     * @param array<int> $agentIds
     * @return $this
     */
    public function setAgentIds(array $agentIds): self
    {
        $this->agentIds = array_filter(array_map('intval', $agentIds));
        return $this;
    }

    protected function applyAgentFilter($builder, string $alias = 'sales')
    {
        if (!empty($this->agentIds)) {
            $builder->whereIn($alias . '.warehouse_id', $this->agentIds);
        }
        return $builder;
    }

    public function getListTahun(): array
    {
        $builder = $this->db->table('sales')
            ->select('YEAR(created_at) AS tahun')
            ->groupBy('tahun')
            ->orderBy('tahun', 'DESC');

        $this->applyAgentFilter($builder);

        return $builder->get()->getResultArray();
    }

    public function getTotalItemTerjual(int $tahun): array
    {
        $builder = $this->db->table('sales_detail sd')
            ->selectSum('CASE WHEN YEAR(s.created_at) = ' . (int)$tahun . ' THEN sd.qty ELSE 0 END', 'jml')
            ->selectSum('CASE WHEN YEAR(s.created_at) = ' . (int)($tahun - 1) . ' THEN sd.qty ELSE 0 END', 'jml_prev')
            ->join('sales s', 's.id = sd.sale_id', 'inner');

        $this->applyAgentFilter($builder, 's');

        $result = $builder->get()->getRowArray() ?? ['jml' => 0, 'jml_prev' => 0];
        $jml = (int)($result['jml'] ?? 0);
        $jmlPrev = (int)($result['jml_prev'] ?? 0);
        $growth = $jmlPrev > 0 ? round(($jml - $jmlPrev) / $jmlPrev * 100, 2) : 0;

        return ['jml' => $jml, 'jml_prev' => $jmlPrev, 'growth' => $growth];
    }

    public function getTotalJumlahTransaksi(int $tahun): array
    {
        $builder = $this->db->table('sales')
            ->selectSum('CASE WHEN YEAR(created_at) = ' . (int)$tahun . ' THEN grand_total ELSE 0 END', 'jml')
            ->selectSum('CASE WHEN YEAR(created_at) = ' . (int)($tahun - 1) . ' THEN grand_total ELSE 0 END', 'jml_prev');

        $this->applyAgentFilter($builder);

        $result = $builder->get()->getRowArray() ?? ['jml' => 0, 'jml_prev' => 0];
        $jml = (float)($result['jml'] ?? 0);
        $jmlPrev = (float)($result['jml_prev'] ?? 0);
        $growth = $jmlPrev > 0 ? round(($jml - $jmlPrev) / $jmlPrev * 100, 2) : 0;

        return ['jml' => $jml, 'jml_prev' => $jmlPrev, 'growth' => $growth];
    }

    public function getTotalPelangganAktif(int $tahun): array
    {
        $builderCurr = $this->db->table('sales')
            ->select('customer_id')
            ->where('customer_id IS NOT NULL', null, false)
            ->where('YEAR(created_at)', $tahun)
            ->groupBy('customer_id');
        $this->applyAgentFilter($builderCurr);
        $jml = $builderCurr->countAllResults();

        $builderPrev = $this->db->table('sales')
            ->select('customer_id')
            ->where('customer_id IS NOT NULL', null, false)
            ->where('YEAR(created_at)', $tahun - 1)
            ->groupBy('customer_id');
        $this->applyAgentFilter($builderPrev);
        $jmlPrev = $builderPrev->countAllResults();

        $builderTotal = $this->db->table('sales')
            ->select('customer_id')
            ->where('customer_id IS NOT NULL', null, false)
            ->groupBy('customer_id');
        $this->applyAgentFilter($builderTotal);
        $total = $builderTotal->countAllResults();

        $growth = $jmlPrev > 0 ? round(($jml - $jmlPrev) / $jmlPrev * 100, 2) : 0;

        return ['jml' => $jml, 'jml_prev' => $jmlPrev, 'growth' => $growth, 'total' => $total];
    }

    public function getTotalNilaiPenjualan(int $tahun): array
    {
        return $this->getTotalJumlahTransaksi($tahun);
    }

    public function getPenjualan(int $tahun): array
    {
        $builder = $this->db->table('sales')
            ->select('MONTH(created_at) AS bulan')
            ->select('SUM(grand_total) AS total')
            ->where('YEAR(created_at)', $tahun)
            ->groupBy('MONTH(created_at)')
            ->orderBy('bulan', 'ASC');

        $this->applyAgentFilter($builder);

        return $builder->get()->getResultArray();
    }

    public function getSeriesPenjualan(array $listTahun): array
    {
        $result = [];

        foreach ($listTahun as $tahun) {
            $builder = $this->db->table('sales')
                ->select('MONTH(created_at) AS bulan')
                ->select('COUNT(id) AS JML')
                ->select('SUM(grand_total) AS total')
                ->where('YEAR(created_at)', $tahun)
                ->groupBy('MONTH(created_at)')
                ->orderBy('bulan', 'ASC');

            $this->applyAgentFilter($builder);

            $result[$tahun] = $builder->get()->getResultArray();
        }

        return $result;
    }

    public function getSeriesTotalPenjualan(array $listTahun): array
    {
        $result = [];

        foreach ($listTahun as $tahun) {
            $builder = $this->db->table('sales')
                ->select('SUM(grand_total) AS total')
                ->where('YEAR(created_at)', $tahun);

            $this->applyAgentFilter($builder);

            $result[$tahun] = $builder->get()->getRowArray();
        }

        return $result;
    }

    public function getItemTerjual(int $tahun): array
    {
        $builder = $this->db->table('sales_detail sd')
            ->select('sd.item_id')
            ->select('COALESCE(sd.item, item.name) AS nama_barang')
            ->select('SUM(sd.qty) AS jml')
            ->join('sales s', 's.id = sd.sale_id', 'inner')
            ->join('item', 'item.id = sd.item_id', 'left')
            ->where('YEAR(s.created_at)', $tahun)
            ->groupBy('sd.item_id')
            ->orderBy('jml', 'DESC')
            ->limit(7);

        $this->applyAgentFilter($builder, 's');

        return $builder->get()->getResultArray();
    }

    public function getKategoriTerjual(int $tahun): array
    {
        $builder = $this->db->table('sales_detail sd')
            ->select('item.category_id AS id_kategori, item_category.category AS nama_kategori')
            ->select('COUNT(sd.item_id) AS jml')
            ->select('SUM(sd.amount) AS nilai')
            ->join('sales s', 's.id = sd.sale_id', 'inner')
            ->join('item', 'item.id = sd.item_id', 'left')
            ->join('item_category', 'item_category.id = item.category_id', 'left')
            ->where('YEAR(s.created_at)', $tahun)
            ->groupBy('item.category_id')
            ->orderBy('nilai', 'DESC')
            ->limit(7);

        $this->applyAgentFilter($builder, 's');

        return $builder->get()->getResultArray();
    }

    public function getItemTerbaru(): array
    {
        return $this->db->table('item')
            ->select('item.*, item.name AS nama_barang, item.price AS harga_jual')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
    }

    public function penjualanTerbaru(int $tahun): array
    {
        $builder = $this->db->table('sales s')
            ->select('COALESCE(customer.name, "Umum") AS nama_pelanggan')
            ->select('COALESCE(SUM(sd.qty), 0) AS jumlah_item')
            ->select('COALESCE(SUM(sd.qty), 0) AS jml_barang')
            ->select('s.grand_total')
            ->select('s.total_amount')
            ->select('s.payment_status')
            ->select('s.created_at')
            ->select('s.created_at AS tgl_transaksi')
            ->select('s.id AS id_penjualan')
            ->join('sales_detail sd', 'sd.sale_id = s.id', 'left')
            ->join('customer', 'customer.id = s.customer_id', 'left')
            ->where('YEAR(s.created_at)', $tahun)
            ->groupBy('s.id')
            ->orderBy('s.created_at', 'DESC')
            ->limit(50);

        $this->applyAgentFilter($builder, 's');

        return $builder->get()->getResultArray();
    }

    public function countAllDataPejualanTerbesar(int $tahun): int
    {
        $builder = $this->db->table('sales_detail sd')
            ->join('sales s', 's.id = sd.sale_id', 'inner')
            ->where('YEAR(s.created_at)', $tahun)
            ->groupBy('sd.item_id');

        $this->applyAgentFilter($builder, 's');

        return $builder->countAllResults();
    }

    public function getListDataPenjualanTerbesar(int $tahun): array
    {
        $request = \Config\Services::request();
        $columns    = $request->getPost('columns');
        $search_all = $request->getPost('search')['value'] ?? '';
        $order_data = $request->getPost('order') ?? [];
        $start      = $request->getPost('start') ?? 0;
        $length     = $request->getPost('length') ?? 10;

        $builder = $this->db->table('sales_detail sd')
            ->select('sd.item_id, COALESCE(sd.item, item.name) AS nama_barang')
            ->select('AVG(sd.price) AS harga_satuan')
            ->select('SUM(sd.qty) AS jml_terjual')
            ->select('SUM(sd.amount) AS total_harga')
            ->join('sales s', 's.id = sd.sale_id', 'inner')
            ->join('item', 'item.id = sd.item_id', 'left')
            ->where('YEAR(s.created_at)', $tahun)
            ->groupBy('sd.item_id');

        $this->applyAgentFilter($builder, 's');

        if ($search_all) {
            $builder->groupStart()
                ->like('sd.item', $search_all)
                ->orLike('item.name', $search_all)
                ->groupEnd();
        }

        if (!empty($order_data)) {
            foreach ($order_data as $order) {
                $colIndex = $order['column'];
                $dir = $order['dir'] ?? 'asc';
                $columnName = $columns[$colIndex]['data'] ?? null;
                if ($columnName) {
                    $builder->orderBy($columnName, $dir);
                }
            }
        } else {
            $builder->orderBy('total_harga', 'DESC');
        }

        $recordsFilteredBuilder = clone $builder;
        $recordsFiltered = $recordsFilteredBuilder->countAllResults(false);

        $builder->limit($length, $start);
        $data = $builder->get()->getResultArray();

        $totalSales = array_sum(array_column($data, 'total_harga')) ?: 1;

        foreach ($data as &$row) {
            $amount = (float)($row['total_harga'] ?? 0);
            $row['kontribusi'] = round(($amount / $totalSales) * 100, 2);
        }

        return [
            'data' => $data,
            'total_filtered' => $recordsFiltered,
        ];
    }
}


