<?php 
namespace App\Controllers;
use App\Models\UploadExcelModel;

class Uploadexcel extends BaseController
{
	protected $model;
	
	public function __construct() {
		parent::__construct();
		$this->model = new UploadExcelModel;
		
		$this->data['title'] = 'Upload Excel';
		$this->data['tabel'] = ['mahasiswa' => ['file_excel' => ['url' => $this->config->baseURL . 'public/files/Format Data Mahasiswa.xlsx'
															, 'display' => 'Format Data Mahasiswa.xlsx'
														  ]
										  , 'display' => 'Mahasiswa'
										],
							'transaksi' => ['file_excel' => ['url' => $this->config->baseURL . 'public/files/Format Data Transaksi.xlsx'
																	, 'display' => 'Format Data Transaksi.xlsx'
																  ]
													, 'display' => 'Transaksi'
												]
						];
		
		foreach ($this->data['tabel'] as $key => $val) {
			$this->data['tabel_options'][$key] = $val['display'];
		}

		$this->addJs($this->config->baseURL . 'public/themes/modern/js/uploadexcel.js');
		$this->addJs(['print' => true, 'script' => 'var tabel = ' . json_encode($this->data['tabel'])]);
	}
	
	public function index()
	{
		if (isset($_POST['submit'])) 
		{
			$form_errors = $this->validateForm();
			if ($form_errors) {
				$this->data['message']['status'] = 'error';
				$this->data['message']['content'] = $form_errors;
			} else {
				$this->data['message'] = $this->model->uploadExcel();	
			}
		}
		
		$this->view('uploadexcel.php', $this->data);
	}
	
	function validateForm() {

		$form_errors = [];

		if ($_FILES['file_excel']['name']) 
		{
				$file_type = $_FILES['file_excel']['type'];
				$allowed = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
				
				if (!in_array($file_type, $allowed)) {
					$form_errors['file_excel'] = 'Tipe file harus ' . join(', ', $allowed);
				}
		} else {
			$form_errors['file_excel'] = 'File excel belum dipilih';
		}
		
		return $form_errors;
	}
}