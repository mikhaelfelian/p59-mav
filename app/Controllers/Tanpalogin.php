<?php
/**
* PHP Admin Template
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2023
*/

namespace App\Controllers;
use App\Models\TanpaLoginModel;

class Tanpalogin extends BaseController
{
	public function __construct() {
		parent::__construct();
		$this->model = new TanpaLoginModel;
	}
	
	public function index()
	{		
		$artikel = $this->model->getArtikelBySlug('tanpalogin');
		$artikel['konten'] = str_replace('{{BASE_URL}}', $this->config->baseURL, $artikel['konten']);
		$this->data['artikel'] = $artikel;
		return view('themes/modern/tanpalogin.php', $this->data);
	}
}