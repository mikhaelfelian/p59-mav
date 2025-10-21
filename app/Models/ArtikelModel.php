<?php
namespace App\Models;

class ArtikelModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getAllArtikel($where) {
		$sql = 'SELECT * FROM artikel' . $where;
		$artikel = $this->db->query($sql)->getResultArray();
		return $artikel;
	}
	
	public function deleteArtikel() 
	{
		$delete = $this->db->table('artikel')->delete(['id_artikel' => $_POST['id'] ]);
		if ($delete) {
			$message['status'] = 'ok';
			$message['message'] = 'Data berhasil dihapus';
		} else {
			$message['status'] = 'error';
			$message['message'] = 'Data gagal dihapus';
		}
		
		return $message;
	}
	
	public function getArtikelAuthor($in_mask, $in) 
	{
		$sql = 'SELECT * FROM artikel_author 
				LEFT JOIN author USING(id_author)
				WHERE id_artikel IN (' . join(',', $in_mask) . ')';
		$query = $this->db->query($sql, $in)->getResultArray();
		$artikel_author = [];
		foreach($query as $val) {
			$artikel_author[$val['id_artikel']][] = $val['nama_author'];
		}
		
		return $artikel_author;
	}
	
	public function getArtikelKategori($in_mask, $in) 
	{
		$sql = 'SELECT * FROM artikel_kategori 
				LEFT JOIN kategori USING(id_kategori)
				WHERE id_artikel IN (' . join(',', $in_mask) . ')';
		$query = $this->db->query($sql, $in)->getResultArray();
		$artikel_kategori = [];
		foreach($query as $val) {
			$artikel_kategori[$val['id_artikel']][] = $val['judul_kategori'];
		}
		
		return $artikel_kategori;
	}
	
	public function setData($id_artikel, $where) 
	{
		$artikel = [];
		$feature_image = [];
		if ($id_artikel) 
		{
			$sql = 'SELECT * FROM artikel ' . $where . ' AND id_artikel = ?';
			$query = $this->db->query($sql, $id_artikel)->getRowArray();
			if ($query) {
				foreach ($query as $key => $val) {
					$artikel[$key] = $val;
				}
				
				if ($artikel['id_file_picker']) {
					$sql = 'SELECT * FROM file_picker WHERE id_file_picker = ?';
					$feature_image = $this->db->query($sql, $artikel['id_file_picker'])->getRowArray();
				}
			}
		}
		
		// Artikel Author
		$artikel_author = [];
		$id_author = [];
		
		if ($id_artikel) 
		{
			$sql = 'SELECT * FROM artikel_author 
					LEFT JOIN author USING(id_author)
					WHERE id_artikel = ?';
			$query = $this->db->query($sql, $id_artikel)->getResultArray();
			$artikel_author = [];
			$id_author = [];
			foreach($query as $val) {
				$artikel_author[] = $val['nama_author'];
				$id_author[] = $val['id_author'];
			}
		}

		// Kategori
		$artikel_kategori = [];
		$id_kategori = [];
		
		if ($id_artikel) {
			$sql = 'SELECT * FROM artikel_kategori 
					LEFT JOIN kategori USING(id_kategori)
					WHERE id_artikel = ?';
			$query = $this->db->query($sql, $id_artikel)->getResultArray();
			$artikel_kategori = [];
			$id_kategori = [];
			foreach($query as $val) {
				$artikel_kategori[] = $val['judul_kategori'];
				$id_kategori[] = $val['id_kategori'];
			}
		}
		
		// Ref Author
		$sql = 'SELECT * FROM author';
		$query = $this->db->query($sql)->getResultArray();
		$ref_author = [];
		foreach($query as $val) {
			$ref_author[$val['id_author']] = $val['nama_author'];
		}
		
		// Ref Kategori
		$sql = 'SELECT * FROM kategori';
		$query = $this->db->query($sql)->getResultArray();
		$ref_kategori = [];
		foreach($query as $val) {
			$ref_kategori[$val['id_kategori']] = $val['judul_kategori'];
		}
		
		return [
				'artikel' => $artikel
				, 'ref_author' => $ref_author
				, 'ref_kategori' => $ref_kategori
				, 'id_kategori' => $id_kategori
				, 'artikel_kategori' => $artikel_kategori
				, 'id_author' => $id_author
				, 'artikel_author' => $artikel_author
				, 'id_artikel' => $id_artikel
				, 'feature_image' => $feature_image
		];
	}
	
	private function validateForm() 
	{
		$error = false;
		if (empty(trim($_POST['judul_artikel']))) {
			$error[] = 'Judul artikel harus diisi';
		}

		if (empty(trim($_POST['konten']))) {
			$error[] = 'Konten artikel harus diisi';
		}
		
		if (empty(trim($_POST['slug']))) {
			$error[] = 'Slug artikel harus diisi';
		}
		
		if (empty($_POST['id_author'])) {
			$error[] = 'Author harus diisi';
		}
		
		if (empty($_POST['tgl_terbit'])) {
			$error[] = 'Tgl. terbit harus diisi';
		}
		
		if (empty($_POST['status'])) {
			$error[] = 'Status harus diisi';
		}

		return $error;
	}

	public function saveData() 
	{
		$message = [];
		$id_artikel = '';
		$this->db->transBegin();
		
		if (!empty($_POST['submit'])) {
			$error = $this->validateForm();
			if ($error) {
				$message['status'] = 'error';
				$message['message'] = $error;
			} else {
				
				$this->db->table('artikel_kategori')->delete(['id_artikel' => $_POST['id']]);
				$this->db->table('artikel_author')->delete(['id_artikel' => $_POST['id']]);
				
				$data_db['judul_artikel'] = trim($_POST['judul_artikel']);
				$data_db['slug'] = trim($_POST['slug']);
				$data_db['konten'] = trim($_POST['konten']);
				$data_db['excerp'] = trim($_POST['excerp']);
				$data_db['status'] = trim($_POST['status']);
				$data_db['id_file_picker'] = trim($_POST['feature_image']);
				$data_db['search_engine_index'] = trim($_POST['search_engine_index']);
				$data_db['meta_description'] = trim($_POST['meta_description']);
				$data_db['tgl_terbit'] = trim($_POST['tgl_terbit'] . ':59');
						
				if (!empty($_POST['id'])) {
					$data_db['id_user_update'] = $_SESSION['user']['id_user'];
					$data_db['tgl_update'] = date('Y-m-d H:i:s');
					$query = $this->db->table('artikel')->update($data_db, ['id_artikel' => $_POST['id']]);
					$id_artikel = $_POST['id'];
				} else {
					$data_db['tgl_create'] = date('Y-m-d H:i:s');
					$data_db['id_user_create'] = $_SESSION['user']['id_user'];
					$query = $this->db->table('artikel')->insert($data_db);
					$id_artikel = $this->db->insertID();
				}
				
				if (!empty($_POST['id_kategori'])) {
					foreach ($_POST['id_kategori'] as $val) {
						$data_db = ['id_artikel' => $id_artikel, 'id_kategori' => $val];
						$this->db->table('artikel_kategori')->insert($data_db);
					}
				}
				
				if (!empty($_POST['id_author'])) {
					foreach ($_POST['id_author'] as $val) {
						$data_db = ['id_artikel' => $id_artikel, 'id_author' => $val];
						$this->db->table('artikel_author')->insert($data_db);
					}
				}
				
				
				if ($this->db->transComplete()) {
					$message['status'] = 'ok';
					$message['message'] = 'Data berhasil disimpan';
				} else {
					$message['status'] = 'error';
					$message['message'] = 'Data gagal disimpan';
				}
			}
		}
		return ['message' => $message, 'id_artikel' => $id_artikel];
	}
}