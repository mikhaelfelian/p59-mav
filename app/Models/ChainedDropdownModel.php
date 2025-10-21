<?php
/**
*	App Name	: Admin Template Codeigniter 4	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2020-2023
*/

namespace App\Models;

class ChainedDropdownModel extends \App\Models\BaseModel
{
	private $fotoPath;
	
	public function __construct() {
		parent::__construct();
		$this->fotoPath = 'public/images/foto/';
	}
	
	public function deleteData() {
		$sql = 'SELECT foto FROM mahasiswa WHERE id_mahasiswa = ?';
		$img = $this->db->query($sql, $_POST['id'])->getRowArray();
		if ($img) {
			if (file_exists($this->fotoPath . $img['foto'])) {
				$unlink = delete_file($this->fotoPath . $img['foto']);
				if (!$unlink) {
					return false;
				}
			}
		}
		$result = $this->db->table('mahasiswa')->delete(['id_mahasiswa' => $_POST['id']]);
		return $result;
	}
	
	public function getMahasiswa() 
	{
		$sql = 'SELECT * FROM mahasiswa ORDER BY nama';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getMahasiswaById($id) {
		$sql = 'SELECT * FROM mahasiswa WHERE id_mahasiswa = ?';
		$result = $this->db->query($sql, trim($id))->getRowArray();
		return $result;
	}
	
	public function saveData() {
		
		$exp = explode('-', $_POST['tgl_lahir']);
		$tgl_lahir = $exp[2].'-'.$exp[1].'-'.$exp[0];
		$data_db['nama'] = $_POST['nama'];
		$data_db['tempat_lahir'] = $_POST['tempat_lahir'];
		$data_db['tgl_lahir'] = $tgl_lahir;
		$data_db['npm'] = $_POST['npm'];
		$data_db['prodi'] = $_POST['prodi'];
		$data_db['fakultas'] = $_POST['fakultas'];
		$data_db['alamat'] = $_POST['alamat'];
		$data_db['id_wilayah_kelurahan'] = $_POST['id_wilayah_kelurahan'];
		
		$query = false;
		
		$new_name = '';
		$img_db['foto'] = '';
		
		$path = ROOTPATH . 'public/images/foto/';
		
		if ($_POST['id']) {
			$sql = 'SELECT foto FROM mahasiswa WHERE id_mahasiswa = ?';
			$img_db = $this->db->query($sql, $_POST['id'])->getRowArray();
			$new_name = $img_db['foto'];
			
			if ($_POST['foto_delete_img']) {
				$del = delete_file($path . $img_db['foto']);
				$new_name = '';
				if (!$del) {
					$data['msg']['message'] = 'Gagal menghapus gambar lama';
					$error = true;
				}
			}
		}
		
		$file = $this->request->getFile('foto');
		
		if ($file && $file->getName())
		{
			//old file
			if ($_POST['id']) {
				if ($img_db['foto']) {
					if (file_exists($path . $img_db['foto'])) {
						$unlink = delete_file($path . $img_db['foto']);
						if (!$unlink) {
							$result['msg']['status'] = 'error';
							$result['msg']['content'] = 'Gagal menghapus gambar lama';
							return $result;
						}
					}
				}
			}
			
			helper('upload_file');
			$new_name =  get_filename($file->getName(), $path);
			$file->move($path, $new_name);
				
			if (!$file->hasMoved()) {
				$result['msg']['status'] = 'error';
				$result['msg']['content'] = 'Error saat memperoses gambar';
				return $result;
			}
		}
		
		$data_db['foto'] = $new_name;
		
		if ($_POST['id']) 
		{
			$data_db['tgl_edit'] = date('Y-m-d');
			$data_db['id_user_edit'] = $_SESSION['user']['id_user'];
			$query = $this->db->table('mahasiswa')->update($data_db, ['id_mahasiswa' => $_POST['id']]);	
		} else {
			$data_db['tgl_input'] = date('Y-m-d');
			$data_db['id_user_input'] = $_SESSION['user']['id_user'];
			$query = $this->db->table('mahasiswa')->insert($data_db);
			$result['id_mahasiswa'] = '';
			if ($query) {
				$result['id_mahasiswa'] = $this->db->insertID();
			} 
		}
		
		if ($query) {
			$result['msg']['status'] = 'ok';
			$result['msg']['content'] = 'Data berhasil disimpan';
		} else {
			$result['msg']['status'] = 'error';
			$result['msg']['content'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
}
?>