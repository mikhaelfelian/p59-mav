<?php 
namespace App\Controllers;
use App\Models\ProdukModel;

class Produk extends BaseController
{
	public function __construct() {
		parent::__construct();
		$this->model = new ProdukModel;
	}
	
	public function index()
	{
		$data = $this->data;
		
		if ($this->request->getPost('delete')) 
		{
			$this->cekHakAkses('delete_data');
			
			$delete = $this->model->deleteProdukById($_POST['id']);
			if ($delete) {
				$data['message'] = ['status' => 'ok', 'message' => 'Data produk berhasil dihapus'];
			} else {
				$data['message'] = ['status' => 'warning', 'message' => 'Tidak ada data yang dihapus'];
			}
		}
		
		$data['result'] = $this->model->getProduk( $this->whereOwn() );
		
		if (!$data['result']) {
            $this->errorDataNotfound();
			return;
		}
		
		$this->view('produk-result.php', $data);
	}
	
	public function edit() 
	{
		$this->cekHakAkses('update_data');
		
		if (empty($_GET['id'])) {
            $this->errorDataNotfound();
			return;
		}
		

		if (!empty($_POST['submit'])) {
			$result = $this->saveData();
			$this->data['message'] = $result['message'];
		}

		$produk = $this->model->getProdukById($_GET['id']);

        if (!$produk) {
            $this->errorDataNotfound();
			return;
		}

        $this->data['title'] = 'Edit Data Produk';
        $this->data['produk'] = $produk;
        $this->data['id_produk'] = $produk['id_produk'];
        $this->view('produk-form.php', $this->data);
	}
	
	public function add() {
		
		$this->cekHakAkses('create_data');
		
		$this->data['title'] = 'Tambah Data Produk';
		
		if (!empty($_POST['submit'])) {
			$result = $this->saveData();
			$this->data['message'] = $result['message'];
			$this->data['id_produk'] = $result['id_produk'];
		}

		$this->view('produk-form.php', $this->data);
	}
	
	private function saveData() 
	{
		$result = [];
		$id_produk = '';
		if (!empty($_POST['submit'])) {
			$error = $this->validateForm();
			if ($error) {
				$result['status'] = 'error';
				$result['message'] = $error;
			} else {
				
				$save = $this->model->saveData(@$_POST['id']);
			
				if ($save['query']['message'] == '') {
					$result['status'] = 'ok';
					$result['message'] = 'Data berhasil disimpan';
				} else {
					$result['status'] = 'error';
					$result['message'] = 'Data gagal disimpan';
				}
				
				$id_produk = $save['id_produk'];
			}
		}
		return ['message' => $result, 'id_produk' => $id_produk];
	}
	
	private function validateForm() {
		$validation =  \Config\Services::validation();
		$validation->setRule('nama_produk', 'Nama Produk', 'trim|required');
		$validation->setRule('deskripsi_produk', 'Deskripsi Produk', 'trim|required');
		$validation->withRequest($this->request)->run();
		return $validation->getErrors();
	}
}