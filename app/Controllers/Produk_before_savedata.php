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
			$delete = $this->model->deleteProdukById($_POST['id']);
			if ($delete) {
				$data['message'] = ['status' => 'ok', 'message' => 'Data produk berhasil dihapus'];
			} else {
				$data['message'] = ['status' => 'warning', 'message' => 'Tidak ada data yang dihapus'];
			}
		}
		
		$data['result'] = $this->model->getProduk();
		
		if (!$data['result']) {
            $this->errorDataNotfound();
			return;
		}
		
		$this->view('produk-result.php', $data);
	}
	
	public function edit() 
	{
		if (empty($_GET['id'])) {
            $this->errorDataNotfound();
			return;
		}
		
		$message = [];
		if (!empty($_POST['submit'])) {
			$error = $this->validateForm();
			if ($error) {
				$message['status'] = 'error';
				$message['message'] = $error;
			} else {
				$update = $this->model->updateData();
				if ($update) {
					$message['status'] = 'ok';
					$message['message'] = 'Data berhasil disimpan';
				} else {
					$message['status'] = 'error';
					$message['message'] = 'Data gagal disimpan';
				}
			}
		}

		$produk = $this->model->getProdukById($_GET['id']);

        if (!$produk) {
            $this->errorDataNotfound();
			return;
		}

        $this->data['title'] = 'Edit Data Produk';
        $this->data['produk'] = $produk;
        $this->data['id_produk'] = $produk['id_produk'];
		$this->data['message'] = $message;
        $this->view('produk-form.php', $this->data);
	}
	
	public function add() {
	
		$this->data['title'] = 'Tambah Data Produk';
		
		$message = [];
		if (!empty($_POST['submit']))
		{
			
			
			$error = $this->validateForm();
			if ($error) {
				$message['status'] = 'error';
				$message['message'] = $error;
			} else {
				$id_produk = $this->model->insertData();
				if ($id_produk) {
					$this->data['id_produk'] = $id_produk;
					$message['status'] = 'ok';
					$message['message'] = 'Data berhasil disimpan';
				} else {
					$message['status'] = 'error';
					$message['message'] = 'Data gagal disimpan';
				}
			}
		}
		
		$this->data['message'] = $message;

		$this->view('produk-form.php', $this->data);
	}
	
	private function validateForm() {
		$validation =  \Config\Services::validation();
		$validation->setRule('nama_produk', 'Nama Produk', 'trim|required');
		$validation->setRule('deskripsi_produk', 'Deskripsi Produk', 'trim|required');
		$validation->withRequest($this->request)->run();
		return $validation->getErrors();
	}
}