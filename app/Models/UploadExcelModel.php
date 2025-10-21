<?php
namespace App\Models;
// use App\Spout;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;


class UploadExcelModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function uploadExcel() 
	{
		helper(['upload_file', 'format']);
		$path = ROOTPATH . 'public/tmp/';
		
		
		$file = $this->request->getFile('file_excel');
		if (! $file->isValid())
		{
			throw new RuntimeException($file->getErrorString().'('.$file->getError().')');
		}
				
		require_once 'app/ThirdParty/Spout/src/Spout/Autoloader/autoload.php';
		
		$filename = upload_file($path, $_FILES['file_excel']);
		$reader = ReaderEntityFactory::createReaderFromFile($path . $filename);
		$reader->open($path . $filename);

		foreach ($reader->getSheetIterator() as $sheet) 
		{
			$total_row = 0;
			foreach ($sheet->getRowIterator() as $num_row => $row) 
			{
				$cols = $row->toArray();
								
				if ($num_row == 1) {
					$field_table = $cols;
					$field_name = array_map('strtolower', $field_table);
					continue;
				}
				
				$data_value = [];
				
				foreach ($field_name as $num_col => $field) 
				{
					$val = null;
					if (key_exists($num_col, $cols) && $cols[$num_col] != '') {
						$val = $cols[$num_col];
					}
					
					if ($val instanceof \DateTime) {
						$val = $val->format('Y-m-d H:i:s');
					}
					
					$data_value[$field] = $val;
				}
				
				$data_db[] = $data_value;
				$total_row += 1;
				if ($num_row % 2000 == 0) {
					$query = $this->db->table($this->request->getPost('nama_tabel'))->insertBatch($data_db);
					$data_db = [];
				}
			}
			
			if ($data_db) {
				$query = $this->db->table($this->request->getPost('nama_tabel'))->insertBatch($data_db);
			}
		}
		$reader->close();
		delete_file($path . $filename);
		
		$result = ['status' => '', 'content'];
		if ($query) {
			$result['status'] = 'ok';
			$result['content'] = 'Data berhasil di masukkan ke dalam tabel <strong>' . $_POST['nama_tabel'] . '</strong> sebanyak ' . format_ribuan($total_row) . ' baris';
		}
		
		return $result;
	}
}