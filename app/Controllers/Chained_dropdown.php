<?php
/**
*	App Name	: Admin Template Codeigniter 4	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2020-2023
*/

namespace App\Controllers;
use App\Models\ChainedDropdownModel;

class Chained_dropdown extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		// $this->mustLoggedIn();
		
		$this->model = new ChainedDropdownModel;	
		$this->data['site_title'] = 'Chained Dropdown';
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/bootstrap-datepicker/js/bootstrap-datepicker.js' );
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/date-picker.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/chained-dropdown.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/bootstrap-datepicker/css/bootstrap-datepicker3.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/wilayah.js');
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
				$data['msg'] = ['status' => 'ok', 'message' => 'Data akta berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'error', 'message' => 'Data akta gagal dihapus'];
			}
		}
		
		$data['result'] = $this->model->getMahasiswa();
		
		$this->view('image-upload-result.php', $data);
	}
	
	public function add() 
	{
		$this->setData();
		
		$data = $this->data;
		$data['title'] = 'Tambah Data Mahasiswa';
		$data['breadcrumb']['Add'] = '';
		
		$data['msg'] = [];
		if (isset($_POST['submit'])) 
		{
			$form_errors = $this->validateForm();
			// $form_errors = false;
							
			if ($form_errors) {
				$data['msg']['status'] = 'error';
				$data['msg']['content'] = $form_errors;
			} else {
				
				$message = $this->model->saveData();
				$data = array_merge($data, $message);
				$data['breadcrumb']['Edit'] = '';
				$data_mahasiswa = $this->model->getMahasiswaById($message['id_mahasiswa']);
				$data = array_merge($data, $data_mahasiswa);
			}
		}
	
		$this->view('chained-dropdown-form.php', $data);
	}
	
	public function edit()
	{		
		$this->data['title'] = 'Edit ' . $this->currentModule['judul_module'];;

		if (empty($_GET['id'])) {
			$this->errorDataNotFound();
			return;
		}
				
		// Submit
		$this->data['msg'] = [];
		if (isset($_POST['submit'])) 
		{
			$form_errors = $this->validateForm();
			// $form_errors = false;
							
			if ($form_errors) {
				$this->data['msg']['status'] = 'error';
				$this->data['msg']['content'] = $form_errors;
			} else {

				$message = $this->model->saveData();
				$this->data = array_merge($this->data, $message);
			}
		}
		
		$this->data['breadcrumb']['Edit'] = '';
		
		$data_mahasiswa = $this->model->getMahasiswaById($_GET['id']);
		if (empty($data_mahasiswa)) {
			$this->errorDataNotFound();
			return;
		}
		
		$this->setData($data_mahasiswa['id_wilayah_kelurahan']);
		$data = array_merge($this->data, $data_mahasiswa);
		
		$this->view('chained-dropdown-form.php', $data);
	}
	

	private function setData($id_wilayah_kelurahan = null) {
		
		$wilayah = new \App\Controllers\Wilayah();
		$data_wilayah = $wilayah->getDataWilayah($id_wilayah_kelurahan);
		$this->data = array_merge($this->data, $data_wilayah);
		return $data_wilayah;
	}

	private function validateForm() {

		$validation =  \Config\Services::validation();
		$validation->setRule('nama', 'Nama Mahasiswa', 'trim|required');
		$validation->setRule('tempat_lahir', 'Tempat Lahir', 'trim|required');
		$validation->setRule('tgl_lahir', 'Tgl. Lahir', 'trim|required');
		$validation->setRule('npm', 'NPM', 'trim|required');
		$validation->setRule('prodi', 'Prodi', 'trim|required');
		$validation->setRule('fakultas', 'Fakultas', 'trim|required');
		$validation->setRule('alamat', 'Alamat', 'trim|required');
		$validation->setRule('id_wilayah_kelurahan', 'Kelurahan', 'trim|required');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		return $form_errors;
	}
}
