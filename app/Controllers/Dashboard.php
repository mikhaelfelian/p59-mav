<?php
/**
*	App Name	: Admin Template Codeigniter 4
*	Author		: Agus Prawoto Hadi, Modified by [Your Name]
*	Website		: https://jagowebdev.com
*	Year		: 2021-2023
*
*   Adapted for db_p59_beebot.customer, sales, sales_detail schema (MariaDB).
*/

namespace App\Controllers;
use App\Models\DashboardModel;

class Dashboard extends BaseController
{
	public function __construct() {
		parent::__construct();
		$this->model = new DashboardModel;
		$this->addJs($this->config->baseURL . 'public/vendors/chartjs/chart.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/material-icons/css.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/dataTables.buttons.min.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/buttons.bootstrap5.min.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/datatables/extensions/JSZip/jszip.min.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/datatables/extensions/pdfmake/pdfmake.min.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/datatables/extensions/pdfmake/vfs_fonts.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/buttons.html5.min.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/js/buttons.print.min.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/datatables/extensions/Buttons/css/buttons.bootstrap5.min.css');
		
		$this->addStyle($this->config->baseURL . 'public/themes/modern/css/dashboard.css');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/dashboard.js');
	}

	public function index()
	{
		// Get list of years from sales.created_at (timestamp) field
		$result = $this->model->getListTahun();
		$list_tahun = [];
		foreach ($result as $val) {
			$list_tahun[$val['tahun']] = $val['tahun'];
		}

		if ($list_tahun) {
			$tahun = max($list_tahun);
		} else {
			$tahun = '';
		}

		// Set defaults for dashboard first row
		$this->data['total_item_terjual'] = ['jml' => 0, 'jml_prev' => 0, 'growth' => 0];
		$this->data['total_jumlah_transaksi'] = ['jml' => 0, 'jml_prev' => 0, 'growth' => 0];
		$this->data['total_nilai_penjualan'] = ['jml' => 0, 'jml_prev' => 0, 'growth' => 0];
		$this->data['total_pelanggan_aktif'] = ['jml' => 0, 'jml_prev' => 0, 'growth' => 0];
		$this->data['penjualan'] = [];
		$this->data['total_penjualan'] = [];
		$this->data['item_terjual'] = [];
		$this->data['kategori_terjual'] = [];
		$this->data['pelanggan_terbesar'] = [];

		if ($tahun) {		
			// Must adapt queries to sales, sales_detail, customer structure!
			$this->data['total_item_terjual'] = $this->model->getTotalItemTerjual($tahun);
			$this->data['total_jumlah_transaksi'] = $this->model->getTotalJumlahTransaksi($tahun);
			$this->data['total_nilai_penjualan'] = $this->model->getTotalNilaiPenjualan($tahun);
			$this->data['total_pelanggan_aktif'] = $this->model->getTotalPelangganAktif($tahun);

			$this->data['list_tahun'] = $list_tahun;
			$this->data['tahun'] = $tahun;

			$this->data['penjualan'] = $this->model->getSeriesPenjualan($list_tahun);
			$this->data['total_penjualan'] = $this->model->getSeriesTotalPenjualan($list_tahun);
			$this->data['item_terjual'] = $this->model->getItemTerjual($tahun);
			$this->data['kategori_terjual'] = $this->model->getKategoriTerjual($tahun);
			$this->data['pelanggan_terbesar'] = $this->model->getPembelianPelangganTerbesar($tahun);
		}

		// Optionally, get most recent sales item(s). Implemented as needed.
		$item_terbaru = $this->model->getItemTerbaru();
		foreach ($item_terbaru as &$val) {
			if (isset($val['harga_jual'])) {
				$val['harga_jual'] = format_number($val['harga_jual']);
			}
		}
		$this->data['item_terbaru'] = $item_terbaru;

		// Get latest sales transactions for the current year
		$this->data['sales_terbaru'] = [];
		if ($tahun) {
			$this->data['sales_terbaru'] = $this->model->penjualanTerbaru($tahun);
		}

		$this->data['message']['status'] = 'ok';
		if (empty($this->data['penjualan'])) {
			$this->data['message']['status'] = 'error';
			$this->data['message']['message'] = 'Data tidak ditemukan';
		}

		// Debug: Verify data before passing to view
		// echo "<pre>"; print_r($this->data['total_item_terjual']); echo "</pre>"; die();

		$this->view('dashboard', $this->data);
	}

	public function ajaxGetPenjualan()
	{
		// Penjualan per bulan (grand_total via sales)
		$result = $this->model->getPenjualan($_GET['tahun']);
		if (!$result)
			return;

		$total = [];
		foreach ($result as $val) {
			$total[] = $val['total'];
		}
		echo json_encode($total);
	}


	public function ajaxGetItemTerjual()
	{
		// Top item terjual per tahun (by sales_detail & item)
		$result = $this->model->getItemTerjual($_GET['tahun']);
		if (!$result)
			return;

		$total = [];
		$nama_item = [];
		foreach ($result as $val) {
			$total[] = $val['jml'];
			$nama_item[] = $val['nama_barang'];
		}
		echo json_encode(['total' => $total, 'nama_item' => $nama_item]);
	}

	public function ajaxGetKategoriTerjual() 
	{
		// Top kategori terjual per tahun (if kategori available)
		$result = $this->model->getKategoriTerjual($_GET['tahun']);
		if (!$result)
			return;

		$total = [];
		$nama_kategori = [];
		foreach ($result as &$val) {
			$total[] = $val['jml'];
			$nama_kategori[] = $val['nama_kategori'];
			$val['jml'] = format_number($val['jml']);
			$val['nilai'] = format_number($val['nilai']);
		}
		echo json_encode(['total' => $total, 'nama_kategori' => $nama_kategori, 'item_terjual' => $result]);
	}

	public function ajaxGetPenjualanTerbaru()
	{
		// Penjualan terbaru, join sales, customer
		$result = $this->model->penjualanTerbaru($_GET['tahun']);
		if (!$result)
			return;

		foreach ($result as &$val) {
			$val['grand_total'] = isset($val['grand_total']) ? format_number($val['grand_total']) : 0;
			$val['total_amount'] = isset($val['total_amount']) ? format_number($val['total_amount']) : 0;
			$val['jumlah_item'] = isset($val['jumlah_item']) ? format_number($val['jumlah_item']) : 0;
			$val['status'] = $val['payment_status'] === '2' ? 'Lunas' : ($val['payment_status'] === '1' ? 'Cicil' : 'Belum Lunas');
			$val['tanggal'] = isset($val['created_at']) ? date('d-m-Y H:i', strtotime($val['created_at'])) : '';
		}
		echo json_encode($result);
	}

	public function ajaxGetPelangganTerbesar()
	{
		// Pelanggan terbesar (customer yang melakukan sales terbanyak/grand_total paling besar)
		$result = $this->model->getPembelianPelangganTerbesar($_GET['tahun']);
		if (!$result)
			return;

		foreach ($result as &$val) {
			$val['total_harga'] = format_number($val['total_harga']);
			// Show plat code/number/last and/or name/photo if available
			$detail = htmlspecialchars(($val['plat_code'] ?? '') . ' ' . ($val['plat_number'] ?? '') . ' ' . ($val['plat_last'] ?? ''));
			$val['identitas'] = $val['name'] . ' (' . trim($detail) . ')';
		}
		echo json_encode($result);
	}

	public function getDataDTPenjualanTerbesar()
	{
		$this->hasPermission('read_all');
		$num_data = $this->model->countAllDataPejualanTerbesar($_GET['tahun']);
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;

		$query = $this->model->getListDataPenjualanTerbesar($_GET['tahun']);
		$result['recordsFiltered'] = $query['total_filtered'];

		helper('html');

		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['ignore_search_urut'] = $no;
			$val['harga_satuan'] = format_number($val['harga_satuan']);
			$val['jml_terjual'] = format_number($val['jml_terjual']);
			$val['total_harga'] = format_number($val['total_harga']);
			$val['kontribusi'] = $val['kontribusi'] . '%';
			$no++;
		}

		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
}
