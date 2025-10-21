<?php
/**
*	App Name	: Admin Template Codeigniter 4
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2023
*/

namespace App\Controllers;
use App\Models\ApexchartsModel;

class Apexcharts extends BaseController
{
	public function __construct() {
		parent::__construct();
		$this->model = new ApexchartsModel;
		$this->addJs($this->config->baseURL . 'public/vendors/apexcharts/dist/apexcharts.min.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/apexcharts.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/apexcharts/dist/apexcharts.css');
		$this->addStyle($this->config->baseURL . 'public/themes/modern/css/apexcharts-custom.css');
	}
	
	public function index()
	{
		
		$result = $this->model->getListTahun();
		$list_tahun = [];
		foreach ($result as $val) {
			$list_tahun[$val['tahun']] = $val['tahun'];
		}
		
		$tahun = '';
		if (empty($_GET['tahun'])) {
			$tahun = max($list_tahun);
		}
		
		if (!empty($_GET['tahun']) && in_array($_GET['tahun'], $list_tahun)) {
			$tahun = $_GET['tahun']; 
		}
		
		$this->data['list_tahun'] = $list_tahun;
		$this->data['penjualan'] = $this->model->getPenjualan( $tahun );
		$this->data['item_terjual'] = $this->model->getItemTerjual( $tahun );
        $this->data['tahun'] = $tahun;

        $this->data['message']['status'] = 'ok';
        if (empty($this->data['penjualan'])) {
            $this->data['message']['status'] = 'error';
            $this->data['message']['message'] = 'Data tidak ditemukan';
		}
		
		$this->view('apexcharts.php', $this->data);
	}
}