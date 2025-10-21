<?php
/**
*	App Name	: Admin Template Codeigniter 4
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2023
*/

namespace App\Controllers;
use App\Models\ChartjsModel;

class Chartjs extends BaseController
{
	public function __construct() {
		parent::__construct();
		$this->model = new ChartjsModel;
		$this->addJs($this->config->baseURL . 'public/vendors/chartjs/chart.js');
		$this->addJs($this->config->baseURL . 'public/themes/modern/js/chartjs.js');
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
		$this->data['penjualan'] = $this->model->getPenjualanPerbulan( $tahun );
		$this->data['item_terjual'] = $this->model->getItemTerjual( $tahun );
        $this->data['tahun'] = $tahun;
		
		$this->data['message']['status'] = 'ok';
        if (empty($this->data['penjualan'])) {
            $this->data['message']['status'] = 'error';
            $this->data['message']['message'] = 'Data tidak ditemukan';
		}
		
		$this->view('chartjs.php', $this->data);
	}
}