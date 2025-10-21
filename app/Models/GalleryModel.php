<?php
namespace App\Models;

class GalleryModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getKategori() 
	{
		$list = ['gallery_active' => 'Y', 'gallery_inactive' => 'N'];
		$gallery = [];
		foreach ($list as $type => $val) {
			$sql = 'SELECT *, 
						(SELECT COUNT(id_gallery_kategori) FROM gallery 
							WHERE id_gallery_kategori = gk.id_gallery_kategori
						) 
						AS jml_gambar 
				FROM gallery_kategori AS gk 
				WHERE aktif = ? ORDER BY urut';
				
			$result[$type] = $this->db->query($sql, $val)->getResultArray();
		}
		
		return $result;
	}
	
	public function getKategoriById($id) 
	{
		$sql = 'SELECT * FROM gallery_kategori WHERE id_gallery_kategori = ?';
		$kategori = $this->db->query($sql, $id)->getRowArray();
		
		return $kategori;
	}
	
	public function getAllKategori() 
	{
		$sql = 'SELECT * FROM gallery_kategori';
		$result = $this->db->query($sql)->getResultArray();
		$kategori[''] = 'Semua kategori';
		foreach ($result as $val) {
			$kategori[$val['id_gallery_kategori']] = $val['judul_kategori'];
		}
		
		return $kategori;
	}
	
	public function getGallery() 
	{
		$sql = 'SELECT * FROM gallery_kategori 
					LEFT JOIN gallery USING (id_gallery_kategori) 
					LEFT JOIN file_picker ON (gallery.id_file_picker = file_picker.id_file_picker)
					
					ORDER BY id_gallery_kategori, gallery.urut';
		$query = $this->db->query($sql)->getResultArray();
		
		$gallery = [];
		foreach ($query as $val) {
			$gallery[$val['id_gallery_kategori']][] = $val;
		}
		
		return $gallery;
	}
	
	public function getGalleryByKategori($id_kategori = null) {
		$sql = 'SELECT * FROM gallery 
				LEFT JOIN file_picker USING(id_file_picker)';
		
		if ($id_kategori) {
			$sql .= 'WHERE id_gallery_kategori = ' . $id_kategori;
		}
		
		$sql .= '  ORDER BY urut'; 
		$gallery = $this->db->query($sql)->getResultArray();
		return $gallery;
	}
	
	public function saveKategori() 
	{
		$data_db['judul_kategori'] = trim($_POST['judul_kategori']);
		$data_db['deskripsi'] = trim($_POST['deskripsi']);
		$data_db['aktif'] = trim($_POST['aktif']);
		$data_db['layout'] = trim($_POST['layout']);
		
		$sql = 'SELECT MAX(urut) AS max_urut FROM gallery_kategori';
		$result = $this->db->query($sql)->getRowArray();
		$data_db['urut'] = $result['max_urut'] + 1;
	   
		if (!empty($_POST['id'])) {
			$data_db['id_user_update'] = $_SESSION['user']['id_user'];
			$data_db['tgl_update'] = date('Y-m-d H:i:s');
			$query = $this->db->table('gallery_kategori')->update($data_db, ['id_gallery_kategori' => $_POST['id']]);
			$id_gallery_kategori = $_POST['id'];
		} else {
			$data_db['tgl_create'] = date('Y-m-d H:i:s');
			$data_db['id_user_create'] = $_SESSION['user']['id_user'];
			$query = $this->db->table('gallery_kategori')->insert($data_db);
			$id_gallery_kategori = $this->db->insertID();
		}
		
		if ($query) {
			$message['status'] = 'ok';
			$message['message'] = 'Data berhasil disimpan';
		} else {
			$message['status'] = 'error';
			$message['message'] = 'Data gagal disimpan';
		}
		
		return ['message' => $message, 'id_gallery_kategori' => $id_gallery_kategori];
	}
	
	public function updateKategoriSort() 
	{
		$error = false;
		$this->db->transBegin();
		
		// Active / inactive
		$update = $this->db->table('gallery_kategori')
						->update($_POST['param'], ['id_gallery_kategori' => $_POST['id']]);
		
		if (!$update) {
			$error = true;
		}
		
		// Sort
		$list_id = json_decode($_POST['urut'], true);
		foreach ( $list_id as $index => $id) {
			$update = $this->db->table('gallery_kategori')
								->update(['urut' => ($index + 1)], ['id_gallery_kategori' => $id]);
								
			if (!$update) {
				$error = true;
			}
		}
		
		if ($error) {
			$this->db->transRollback();
			$result['status'] = 'error';
			$result['message'] = 'Data status gallery gagal disimpan';
		} else {
			$this->db->transCommit();
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		}
		
		return $result;
	}
	
	public function deleteKategori() 
	{
		$this->db->transBegin();
		$delete_gallery = $this->db->table('gallery')->delete(['id_gallery_kategori' => $_POST['id']]);
		$delete_kategori = $this->db->table('gallery_kategori')->delete(['id_gallery_kategori' => $_POST['id']]);

		if ($delete_gallery && $delete_kategori) {
			$this->db->transCommit();
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$this->db->transRollback();
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
	
	// Gallery
	public function galleryChangeImageOrder() 
	{
		$error = false;
		
		$this->db->transBegin();
		
		$list_id = json_decode($_POST['urut'], true);
		foreach ( $list_id as $index => $id) {
			$update = $this->db->table('gallery')->update(['urut' => ($index + 1)], ['id_gallery' => $id]);
			if (!$update) {
				$error = true;
			}
		}
		
		if ($error) {
			$this->db->transRollback();
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		} else {
			$this->db->transCommit();
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		}
		
		return $result;
	}
	
	public function galleryChangeImageCategory() 
	{
		$update = $this->db->table('gallery')->update(['id_gallery_kategori' => $_POST['id_gallery_kategori'] ], ['id_gallery' => $_POST['id']]);

		if ($update) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data status gallery gagal disimpan';
		}
		
		return $result;
	}
	
	public function galleryDeleteImage() 
	{
		$delete = $this->db->table('gallery')->delete(['id_gallery' => $_POST['id'] ]);

		if ($delete) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil dihapus';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data status gallery gagal dihapus';
		}
		
		return $result;
	}
	
	public function galleryAddImage() 
	{
		$error = false;
		
		$this->db->transStart();
				
		$insert= false;
		if ($_POST['id_file_picker']) {
			
			$sql = 'UPDATE gallery SET urut = urut + 1 WHERE id_gallery_kategori = ?';
			$update_urut = $this->db->query($sql, $_POST['id_gallery_kategori']);
			
			$data_db['id_gallery_kategori'] = $_POST['id_gallery_kategori'];
			$data_db['id_file_picker'] = $_POST['id_file_picker'];
			$data_db['id_user_input'] = $_SESSION['user']['id_user'];
			$data_db['tgl_input'] = date('Y-m-d H:i:s');
			$data_db['urut'] = 1;
			
			$insert = $this->db->table('gallery')->insert($data_db);
			$result['id_gallery'] = $this->db->insertID();
		}
		
		if (!$insert) {
			$error = true;
		}
		
		if ($error) {
			$this->db->transRollback();
			$result['status'] = 'error';
			$result['message'] = 'Data gallery gagal disimpan';
		} else {
			$this->db->transCommit();
			$result['status'] = 'ok';
			$result['message'] = 'Data gallery berhasil disimpan';
		}
		
		return $result;
	}
}