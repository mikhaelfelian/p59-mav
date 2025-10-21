<?php
/**
*	App Name	: Admin Template Codeigniter 4	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2020-2023
*/

namespace App\Controllers;
use App\Models\ImageUploadModel;

class Image_upload extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		// $this->mustLoggedIn();
		
		$this->model = new ImageUploadModel;	
		$this->data['site_title'] = 'Image Upload';
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/bootstrap-datepicker/js/bootstrap-datepicker.js' );
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/date-picker.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/image-upload.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/bootstrap-datepicker/css/bootstrap-datepicker3.css');
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
				
				// $query = false;
				$message = $this->model->saveData();
				
				$data = array_merge($data, $message);
				$data['breadcrumb']['Edit'] = '';
				$data_mahasiswa = $this->model->getMahasiswaById($message['id_mahasiswa']);
				$data = array_merge($data, $data_mahasiswa);
			}
		}
	
		$this->view('image-upload-form.php', $data);
	}
	
	public function edit()
	{
		$this->data['title'] = 'Edit ' . $this->currentModule['judul_module'];;
		$data = $this->data;
		
		if (empty($_GET['id'])) {
			$this->errorDataNotFound();
			return;
		}
				
		// Submit
		$data['msg'] = [];
		if (isset($_POST['submit'])) 
		{
		
			$form_errors = $this->validateForm();
			// $form_errors = false;
							
			if ($form_errors) {
				$data['msg']['status'] = 'error';
				$data['msg']['content'] = $form_errors;
			} else {
				
				// $query = false;
				$message = $this->model->saveData();
				
				$data = array_merge($data, $message);
			}
		}
		
		$data['breadcrumb']['Edit'] = '';
		
		$data_mahasiswa = $this->model->getMahasiswaById($_GET['id']);
		if (empty($data_mahasiswa)) {
			$this->errorDataNotFound();
			return;
		}
		$data = array_merge($data, $data_mahasiswa);
		
		$this->view('image-upload-form.php', $data);
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
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		return $form_errors;
	}
	
}
