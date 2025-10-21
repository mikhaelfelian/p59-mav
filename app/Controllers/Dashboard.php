<?php
/**
*	App Name	: Admin Template Codeigniter 4
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2023
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
		
		// Baris pertama
		$this->data['total_item_terjual'] = '';
		$this->data['total_jumlah_transaksi'] = '';
		$this->data['total_nilai_penjualan'] = '';
		$this->data['total_pelanggan_aktif'] = '';
		$this->data['penjualan'] = '';
		$this->data['total_penjualan'] = '';
		$this->data['item_terjual'] = '';
		$this->data['kategori_terjual'] = '';
		$this->data['pelanggan_terbesar'] = '';
		
		if ($tahun) {		
			$this->data['total_item_terjual'] = $this->model->getTotalItemTerjual( $tahun );
			$this->data['total_jumlah_transaksi'] = $this->model->getTotalJumlahTransaksi( $tahun );
			$this->data['total_nilai_penjualan'] = $this->model->getTotalNilaiPenjualan( $tahun );
			$this->data['total_pelanggan_aktif'] = $this->model->getTotalPelangganAktif( $tahun );
			
			$this->data['list_tahun'] = $list_tahun;
			$this->data['tahun'] = $tahun;
			
			$this->data['penjualan'] = $this->model->getSeriesPenjualan( $list_tahun );
			$this->data['total_penjualan'] = $this->model->getSeriesTotalPenjualan( $list_tahun );
			$this->data['item_terjual'] = $this->model->getItemTerjual( $tahun );
			$this->data['kategori_terjual'] = $this->model->getKategoriTerjual( $tahun );        
			$this->data['pelanggan_terbesar'] = $this->model->getPembelianPelangganTerbesar( $tahun );
		}
		
		$item_terbaru = $this->model->getItemTerbaru();
		foreach ($item_terbaru as &$val) {
			$val['harga_jual'] = format_number($val['harga_jual']);
		}
		
		$this->data['item_terbaru'] = $item_terbaru;
		
		$this->data['message']['status'] = 'ok';
        if (empty($this->data['penjualan'])) {
            $this->data['message']['status'] = 'error';
            $this->data['message']['message'] = 'Data tidak ditemukan';
		}
		
		$this->view('dashboard.php', $this->data);
	}
	
	public function ajaxGetPenjualan() {
		
		$result = $this->model->getPenjualan( $_GET['tahun'] );
		if (!$result)
			return;
		
		foreach ($result as $val) {
			$total[] = $val['total'];
		}
		
		echo json_encode($total);
	}
	
	public function ajaxGetItemTerjual() {
		
		$result = $this->model->getItemTerjual( $_GET['tahun'] );
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
		$result = $this->model->getKategoriTerjual( $_GET['tahun'] );
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
		$result = $this->model->penjualanTerbaru( $_GET['tahun'] );
		if (!$result)
			return;
		
		foreach ($result as &$val) {
			$val['total_harga'] = format_number($val['total_harga']);
			$val['jml_barang'] = format_number($val['jml_barang']);
			$val['status'] = 'selesai';
		}
		
		echo json_encode($result);
	}
	
	public function ajaxGetPelangganTerbesar() {
		$result = $this->model->getPembelianPelangganTerbesar( $_GET['tahun'] );
		
		if (!$result)
			return;
		
		foreach ($result as &$val) {
			$val['total_harga'] = format_number($val['total_harga']);
			$val['foto'] = '<img src="' . base_url() . '/public/images/pelanggan/' . $val['foto'] . '">';
		}
		
		echo json_encode($result);
		
	}
	
	public function getDataDTPenjualanTerbesar() {
		
		$this->hasPermission('read_all');
		
		$num_data = $this->model->countAllDataPejualanTerbesar( $_GET['tahun'] );
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataPenjualanTerbesar( $_GET['tahun'] );
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