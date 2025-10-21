<?php
namespace App\Models;

class ProdukModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getProduk($where) {
		$sql = 'SELECT * FROM produk' . $where;
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getProdukById($id) {
		$sql = 'SELECT * FROM produk WHERE id_produk = ?';
		$result = $this->db->query($sql, $id)->getRowArray();
		return $result;
	}
	
	public function saveData($id) 
	{
		$data_db['nama_produk'] = $_POST['nama_produk'];
		$data_db['deskripsi_produk'] = $_POST['deskripsi_produk'];
		$data_db['id_user_input'] = $this->session->get('user')['id_user'];
		$id_produk = $id;
		
		$builder = $this->db->table('produk');
		if (empty($id)) {
			$builder->insert($data_db);
			$id_produk = $this->db->insertID();
		} else {
			$builder->update($data_db, ['id_produk' => $_POST['id']]);
		}
		
		return ['query' => $this->db->error(), 'id_produk' => $id_produk];
	}
	
	public function deleteProdukById($id) {
		$delete = $this->db->table('produk')->delete(['id_produk' => $id]);
		// $delete = true;
		return $delete;
	}
}