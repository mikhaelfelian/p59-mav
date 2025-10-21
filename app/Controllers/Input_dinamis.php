<?php
/**
*	App Name	: Admin Template Codeigniter 4	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2020-2023
*/

namespace App\Controllers;
use App\Models\InputDinamisModel;

class Input_dinamis extends \App\Controllers\BaseController
{
	protected $model;
	private $formValidation;
	
	public function __construct() {
		
		parent::__construct();
		// $this->mustLoggedIn();
		
		$this->model = new InputDinamisModel;	
		$this->data['site_title'] = 'Input Dinamis';
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/penghadap.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$data = $this->data;
		if (!empty($_POST['delete'])) 
		{
			$result = $this->model->deleteData();
			// $result = true;
			if ($result) {
				$data['message'] = ['status' => 'ok', 'message' => 'Data penghadap berhasil dihapus'];
			} else {
				$data['message'] = ['status' => 'error', 'message' => 'Data penghadap gagal dihapus'];
			}
		}
		
		$data['result'] = $this->model->getPenghadap();
		
		if (!$data['result']) {
			$data['message']['status'] = 'error';
			$data['message']['content'] = 'Data tidak ditemukan';
		}
		$this->view('penghadap-result.php', $data);
	}
	
	public function add() 
	{
		$this->hasPermissionPrefix('create');
		
		$data = $this->data;
		$data['title'] = 'Tambah Data Penghadap';
		if (empty($_GET['id'])) 
		{
			$breadcrumb['Add'] = '';
		}
		
		// Submit
		$data['message'] = [];
		if (isset($_POST['submit'])) 
		{
			$form_errors = $this->validateForm();
			$form_errors = false;
							
			if ($form_errors) {
				$data['message']['status'] = 'error';
				$data['message']['content'] = $form_errors;
			} else {
				
				$result = $this->model->saveData();
			
				if ($result) {
					$data['message']['status'] = 'ok';
					$data['message']['content'] = 'Data berhasil disimpan';
				} else {
					$data['message']['status'] = 'error';
					$data['message']['content'] = 'Data gagal disimpan';
				}
				
				$data['title'] = 'Edit Data Penghadap';
			}
		}

		$this->view('penghadap-form.php', $data);
	}
	
	public function edit()
	{
		$data = $this->data;
		$data['title'] = 'Edit Data';
		
		$data['message'] = [];
		if (isset($_POST['submit'])) 
		{
			$form_errors = $this->validateForm();
			$form_errors = false;
							
			if ($form_errors) {
				$data['message']['status'] = 'error';
				$data['message']['content'] = $form_errors;
			} else {
				
				$result = $this->model->saveData();
			
				if ($result) {
					$data['message']['status'] = 'ok';
					$data['message']['content'] = 'Data berhasil disimpan';
				} else {
					$data['message']['status'] = 'error';
					$data['message']['content'] = 'Data gagal disimpan';
				}
				
				$data['title'] = 'Edit Data Penghadap';
			}
		}
		
		if (!empty($_GET['id'])) 
		{
			if (empty($_POST['nama_penghadap'])) {
				$result = $this->model->getPenghadapById();
				if ($result) {
					foreach ($result as $arr) {
						foreach ($arr as $key => $val) {
							$_POST[$key][]	= $val;
						}
					}
				} else {
					$data['message']['status'] = 'error';
					$data['message']['content'] = 'Data penghadap tidak ditemukan';
				}	
			}
		} else {
			$data['message']['status'] = 'error';
			$data['message']['content'] = 'Data penghadap tidak ditemukan';
		}
		$this->view('penghadap-form.php', $data);
	}
	
	private function saveData() 
	{
		$form_errors = $this->validateForm();
	
		if ($form_errors) {
			$data['message']['status'] = 'error';
			$data['form_errors'] = $form_errors;
			$data['message']['message'] = $form_errors;
		} else {
			$save = $this->model->saveData();
			if ($save['status'] == 'ok') {
				$data['message']['status'] = 'ok';
				$data['message']['message'] = 'Data berhasil disimpan';
			} else {
				$data['message']['status'] = 'error';
				$data['message']['message'] = $save['message'];
			}
		}
		
		return $data;
	}
	
	private function validateForm() {

		$validation =  \Config\Services::validation();
		$validation->setRule('nama_penghadap[]', 'Nama Penghadap', 'required');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		return $form_errors;
	}
	
}