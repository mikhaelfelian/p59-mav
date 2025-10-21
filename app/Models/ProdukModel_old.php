<?php
namespace App\Models;

class ProdukModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getProduk() {
		$sql = 'SELECT * FROM produk';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getProdukById($id) {
		$sql = 'SELECT * FROM produk WHERE id_produk = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		return $result;
	}
	
	public function updateData($id) {
		$data_db['nama_produk'] = $_POST['nama_produk'];
		$data_db['deskripsi_produk'] = $_POST['deskripsi_produk'];
		$update = $this->db->table('produk')->update($data_db, ['id_produk' => $id]);
		return $update;
	}
	
	public function insertData() {
		$data_db['nama_produk'] = $_POST['nama_produk'];
		$data_db['deskripsi_produk'] = $_POST['deskripsi_produk'];
		$query = $this->db->table('produk')->insert($data_db);
		if ($query) 
			return $this->db->insertID();
			
		return $query;
	}
	
	public function deleteProdukById($id) {
		$delete = $this->db->table('produk')->delete(['id_produk' => $id]);
		$delete = true;
		return $delete;
	}
}