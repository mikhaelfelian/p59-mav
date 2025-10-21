<?php
/**
*	App Name	: Admin Template Codeigniter 4	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

namespace App\Models;

class FormAjaxModel extends \App\Models\BaseModel
{
	private $fotoPath;
	
	public function __construct() {
		parent::__construct();
		$this->fotoPath = 'public/images/foto/';
	}
	
	public function deleteData() {
		$sql = 'SELECT foto FROM mahasiswa WHERE id_mahasiswa = ?';
		$img = $this->db->query($sql, $_POST['id'])->getRowArray();
		if ($img['foto']) {
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
	
	public function getMahasiswaById($id) {
		$sql = 'SELECT * FROM mahasiswa WHERE id_mahasiswa = ?';
		$result = $this->db->query($sql, trim($id))->getRowArray();
		return $result;
	}
	
	public function saveData() 
	{
		$exp = explode('-', $_POST['tgl_lahir']);
		$tgl_lahir = $exp[2].'-'.$exp[1].'-'.$exp[0];
		$data_db['nama'] = $_POST['nama'];
		$data_db['tempat_lahir'] = $_POST['tempat_lahir'];
		$data_db['tgl_lahir'] = $tgl_lahir;
		$data_db['npm'] = $_POST['npm'];
		$data_db['prodi'] = $_POST['prodi'];
		$data_db['fakultas'] = $_POST['fakultas'];
		$data_db['alamat'] = $_POST['alamat'];
		
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
					$data['message'] = 'Gagal menghapus gambar lama';
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
							$result['status'] = 'error';
							$result['message'] = 'Gagal menghapus gambar lama';
							return $result;
						}
					}
				}
			}
			
			helper('upload_file');
			$new_name =  get_filename($file->getName(), $path);
			$file->move($path, $new_name);
				
			if (!$file->hasMoved()) {
				$result['status'] = 'error';
				$result['message'] = 'Error saat memperoses gambar';
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
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
	
	public function countAllData($where) {
		$sql = 'SELECT COUNT(*) AS jml FROM mahasiswa' . $where;
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData($where) {

		$columns = $this->request->getPost('columns');

		// Search
		$search_all = @$this->request->getPost('search')['value'];
		if ($search_all) {
			// Additional Search
			$columns[]['data'] = 'tempat_lahir';
			foreach ($columns as $val) {
				
				if (strpos($val['data'], 'ignore_search') !== false) 
					continue;
				
				if (strpos($val['data'], 'ignore') !== false)
					continue;
				
				$where_col[] = $val['data'] . ' LIKE "%' . $search_all . '%"';
			}
			 $where .= ' AND (' . join(' OR ', $where_col) . ') ';
		}
		
		// Order		
		$order_data = $this->request->getPost('order');
		$order = '';
		if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by;
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml_data FROM mahasiswa ' . $where;
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT * FROM mahasiswa 
				' . $where . $order  . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>