<?php

namespace App\Controllers\Agent;

use App\Controllers\BaseController;
use App\Models\AgentDashboardModel;
use App\Models\UserRoleAgentModel;

class Dashboard extends BaseController
{
    protected AgentDashboardModel $dashboardModel;
    protected UserRoleAgentModel $userRoleAgentModel;
    protected array $agentIds = [];

    public function __construct()
    {
        parent::__construct();
        $this->dashboardModel = new AgentDashboardModel();
        $this->userRoleAgentModel = new UserRoleAgentModel();

        $base = $this->config->baseURL;
        $this->addJs($base . 'public/vendors/chartjs/chart.js');
        $this->addStyle($base . 'public/vendors/material-icons/css.css');
        $this->addJs($base . 'public/vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js');
        $this->addJs($base . 'public/vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js');
        $this->addJs($base . 'public/vendors/datatables/extensions/JSZip/jszip.min.js');
        $this->addJs($base . 'public/vendors/datatables/extensions/pdfmake/pdfmake.min.js');
        $this->addJs($base . 'public/vendors/datatables/extensions/pdfmake/vfs_fonts.js');
        $this->addJs($base . 'public/vendors/datatables/extensions/Buttons/js/buttons.html5.min.js');
        $this->addJs($base . 'public/vendors/datatables/extensions/Buttons/js/buttons.print.min.js');
        $this->addStyle($base . 'public/vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css');
        $this->addStyle($base . 'public/themes/modern/css/dashboard.css');
        $this->addJs($base . 'public/themes/modern/js/dashboard.js');

        if (!empty($this->user['id_user'])) {
            $agentRows = $this->userRoleAgentModel
                ->select('agent_id')
                ->where('user_id', $this->user['id_user'])
                ->findAll();

            $this->agentIds = array_values(array_unique(array_map(
                static function ($row) {
                    if (is_object($row)) {
                        return (int)($row->agent_id ?? 0);
                    }
                    if (is_array($row)) {
                        return (int)($row['agent_id'] ?? 0);
                    }
                    return 0;
                },
                $agentRows
            )));
        }

        $this->dashboardModel->setAgentIds($this->agentIds);
    }

    public function index()
    {
        helper(['number', 'html']);

        if (empty($this->agentIds)) {
            $this->data['message'] = [
                'status'  => 'error',
                'message' => 'Anda belum terdaftar pada agen manapun. Silakan hubungi administrator.'
            ];
            $this->data['title'] = 'Dashboard Agen';
            $this->data['currentModule'] = $this->currentModule;
            $this->data['config'] = $this->config;
            $this->view('sales/agent/dashboard-agent', $this->data);
            return;
        }

        $listTahun = [];
        foreach ($this->dashboardModel->getListTahun() as $row) {
            if (!empty($row['tahun'])) {
                $listTahun[$row['tahun']] = $row['tahun'];
            }
        }

        $tahunAktif = $listTahun ? max($listTahun) : (int)date('Y');

        $this->data['title'] = 'Dashboard Agen';
        $this->data['currentModule'] = $this->currentModule;
        $this->data['config'] = $this->config;
        $this->data['list_tahun'] = $listTahun;
        $this->data['tahun'] = $tahunAktif;

        $this->data['total_item_terjual'] = $this->dashboardModel->getTotalItemTerjual($tahunAktif);
        $this->data['total_jumlah_transaksi'] = $this->dashboardModel->getTotalJumlahTransaksi($tahunAktif);
        $this->data['total_nilai_penjualan'] = $this->dashboardModel->getTotalNilaiPenjualan($tahunAktif);
        $this->data['total_pelanggan_aktif'] = $this->dashboardModel->getTotalPelangganAktif($tahunAktif);

        $this->data['penjualan'] = $this->dashboardModel->getSeriesPenjualan(array_keys($listTahun ?: [$tahunAktif]));
        $this->data['total_penjualan'] = $this->dashboardModel->getSeriesTotalPenjualan(array_keys($listTahun ?: [$tahunAktif]));
        $this->data['item_terjual'] = $this->dashboardModel->getItemTerjual($tahunAktif);
        $this->data['kategori_terjual'] = $this->dashboardModel->getKategoriTerjual($tahunAktif);
        $this->data['item_terbaru'] = $this->dashboardModel->getItemTerbaru();
        $this->data['sales_terbaru'] = $this->dashboardModel->penjualanTerbaru($tahunAktif);

        $this->data['message']['status'] = 'ok';
        if (empty($this->data['penjualan'])) {
            $this->data['message']['status'] = 'error';
            $this->data['message']['message'] = 'Belum ada data penjualan untuk ditampilkan.';
        }

        $this->view('sales/agent/dashboard-agent', $this->data);
    }

    public function ajaxGetPenjualan()
    {
        $tahun = (int)($this->request->getGet('tahun') ?? date('Y'));
        $result = $this->dashboardModel->getPenjualan($tahun);
        return $this->response->setJSON($result);
    }

    public function ajaxGetItemTerjual()
    {
        $tahun = (int)($this->request->getGet('tahun') ?? date('Y'));
        $result = $this->dashboardModel->getItemTerjual($tahun);

        $labels = array_column($result, 'nama_barang');
        $values = array_map(static fn($row) => (int)($row['jml'] ?? 0), $result);

        return $this->response->setJSON([
            'total' => $values,
            'nama_item' => $labels,
        ]);
    }

    public function ajaxGetKategoriTerjual()
    {
        $tahun = (int)($this->request->getGet('tahun') ?? date('Y'));
        $result = $this->dashboardModel->getKategoriTerjual($tahun);

        $labels = array_column($result, 'nama_kategori');
        $values = array_map(static fn($row) => (int)($row['jml'] ?? 0), $result);

        return $this->response->setJSON([
            'total' => $values,
            'nama_kategori' => $labels,
            'item_terjual' => $result,
        ]);
    }

    public function ajaxGetPenjualanTerbaru()
    {
        $tahun = (int)($this->request->getGet('tahun') ?? date('Y'));
        $result = $this->dashboardModel->penjualanTerbaru($tahun);

        $formatted = array_map(static function ($row) {
            $row['grand_total'] = isset($row['grand_total']) ? number_format($row['grand_total'], 0, ',', '.') : '0';
            $row['total_amount'] = isset($row['total_amount']) ? number_format($row['total_amount'], 0, ',', '.') : '0';
            $row['jumlah_item'] = isset($row['jumlah_item']) ? number_format($row['jumlah_item'], 0, ',', '.') : '0';
            $row['tanggal'] = isset($row['created_at']) ? date('d-m-Y H:i', strtotime($row['created_at'])) : '';
            return $row;
        }, $result);

        return $this->response->setJSON($formatted);
    }

    public function ajaxGetPelangganTerbesar()
    {
        $tahun = (int)($this->request->getGet('tahun') ?? date('Y'));
        $result = $this->dashboardModel->getPembelianPelangganTerbesar($tahun);

        $formatted = array_map(static function ($row) {
            $row['total_harga'] = number_format($row['total_harga'] ?? 0, 0, ',', '.');
            return $row;
        }, $result);

        return $this->response->setJSON($formatted);
    }

    public function getDataDTPenjualanTerbesar()
    {
        $this->hasPermission('read_all');
        $tahun = (int)($this->request->getGet('tahun') ?? date('Y'));

        $result = [
            'draw' => (int)($this->request->getPost('draw') ?? 1),
            'recordsTotal' => $this->dashboardModel->countAllDataPejualanTerbesar($tahun),
        ];

        $query = $this->dashboardModel->getListDataPenjualanTerbesar($tahun);
        $result['recordsFiltered'] = $query['total_filtered'];

        helper('html');

        $data = $query['data'];
        $total_penjualan = array_sum(array_column($data, 'total_harga')) ?: 1;
        $no = (int)($this->request->getPost('start') ?? 0) + 1;

        foreach ($data as &$row) {
            $row['ignore_search_urut'] = $no++;
            $row['harga_satuan'] = number_format($row['harga_satuan'] ?? 0, 0, ',', '.');
            $row['jml_terjual'] = number_format($row['jml_terjual'] ?? 0, 0, ',', '.');
            $row['total_harga'] = number_format($row['total_harga'] ?? 0, 0, ',', '.');
            $row['kontribusi'] = round((($row['total_harga'] ?? 0) / $total_penjualan) * 100, 2) . '%';
        }

        $result['data'] = $data;
        return $this->response->setJSON($result);
    }
}


