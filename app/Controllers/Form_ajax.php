<?php
/**
*	App Name	: Admin Template Codeigniter 4	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2020-2023
*/

namespace App\Controllers;
use App\Models\FormAjaxModel;

class Form_ajax extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new FormAjaxModel;	
		$this->data['site_title'] = 'Form ajax';
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/image-upload.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/form-ajax.js');
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$data = $this->data;
		$this->view('form-ajax-result.php', $data);
	}
	
	public function ajaxDeleteData() {

		$result = $this->model->deleteData();
		// $result = true;
		if ($result) {
			$result = ['status' => 'ok', 'message' => 'Data Mahasiswa berhasil dihapus'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data Mahasiswa gagal dihapus'];
		}
		
		echo json_encode($result);
	}
	
	public function add() 
	{
		$data = $this->data;
		$data['title'] = 'Tambah Data Mahasiswa';
		$data['breadcrumb']['Add'] = '';
		
		$data['msg'] = [];
		if (isset($_POST['submit'])) 
		{
			$form_errors = false;
							
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
	
		$this->view('image-upload-form.php', $data);
	}
	
	public function ajaxGetFormData() {
		$data = [];
		if (isset($_GET['id'])) {
			if ($_GET['id']) {
				$data = $this->model->getMahasiswaById($_GET['id']);
				if (!$data)
					return;
			}
		}
		
		$data = array_merge($data, $this->data);
		echo view('themes/modern/form-ajax.php', $data);
	}
	
	public function ajaxUpdateData() {

		$message = $this->model->saveData();
		echo json_encode($message);
	}
	
	public function edit()
	{
		$this->hasPermissionPrefix('update', 'mahasiswa');
		
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
			$form_errors = false;
							
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
	
	public function getDataDT() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllData( $this->whereOwn() );
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListData( $this->whereOwn() );
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$image = 'noimage.png';
			if ($val['foto']) {
				if (file_exists('public/images/foto/' . $val['foto'])) {
					$image = $val['foto'];
				}
			}
			
			$val['ignore_search_foto'] = '<div class="list-foto"><img src="'. $this->config->baseURL.'public/images/foto/' . $image . '"/></div>';
			$val['tgl_lahir'] = $val['tempat_lahir'] . ', '. format_tanggal($val['tgl_lahir']);
			
			$val['ignore_search_urut'] = $no;
			$val['ignore_search_action'] = '<div class="form-inline btn-action-group">'
										. btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_mahasiswa']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_mahasiswa']
																	, 'data-delete-title' => 'Hapus data mahasiswa: <strong>' . $val['nama'] . '</strong>'
																]
													, 'label' => 'Delete'
												]) . 
										
										'</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
