<?php
namespace App\Models;

class DownloadExcelModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getTotalData($table_name) 
	{
		$total_data = $this->db->table($table_name)->countAll();
		return $total_data;
	}
	
	public function getJumlahData($num_rows) 
	{
		$offset = !empty($_GET['data_awal']) ? $_GET['data_awal'] - 1 : 0;
		$sql = 'SELECT COUNT(*) AS jml_data FROM (SELECT * FROM ' . $_GET['nama_tabel'] . ' LIMIT ' . $offset . ', ' . $num_rows . ') AS tabel';
		$jml_data = $this->db->query($sql)->getRowArray();
		return $jml_data;
	}
	
	public function getFields($table) {
		return $this->db->getFieldNames($table);
	}
	
	public function writeExcel($max_data) 
	{
		require_once(ROOTPATH . "/app/ThirdParty/PHPXlsxWriter/xlsxwriter.class.php");
		
		$offset = !empty($_GET['data_awal']) ? $_GET['data_awal'] - 1 : 0;
		$field_meta = $this->db->getFieldData($_GET['nama_tabel']);
			
		$int = ['int', 'tinyint', 'smallint', 'mediumint', 'bigint'];
		$date = ['date', 'year'];
		
		foreach ($field_meta as $field) 
		{
			if (!in_array($field->name, $_GET['fields'])) {
				continue;
			}
			
			$format = 'string';
			if (in_array($field->type, $int)) {
				$format = 'integer';
			} else if (in_array($field->type, $date)) {
				$format = 'date';
			} else if ($field->type == 'datetime') {
				$format = 'datetime';
				
			} else if ($field->type == 'time') {
				$format = 'time';
			} 
			
			$field_data[$field->name] = $format;
		}
		
		if (empty($_GET['data_awal'])) {
			$_GET['data_awal'] = 1;
		}
		
		if (empty($_GET['data_akhir'])) {
			$_GET['data_akhir'] = $max_data;
		}
		
		$num_rows = $_GET['data_akhir'] - ($_GET['data_awal'] - 1);
		$sql = 'SELECT ' . join(', ', $_GET['fields']) . ' FROM ' . $_GET['nama_tabel'] . ' LIMIT ' . $offset . ', ' . $num_rows;
		$query = $this->db->query($sql);
		
		// Excel
		
		$sheet_name = strtoupper($_GET['nama_tabel']);
		$writer = new \XLSXWriter();
		$writer->setAuthor('Some Author');
		$writer->writeSheetHeader($sheet_name,$field_data);
		
		while ($row = $query->getUnbufferedRow('array')) {
			$writer->writeSheetRow($sheet_name, $row);
		}
		
		$filename = 'TABEL ' . (strtoupper($_GET['nama_tabel'])) . '.xlsx';
		header('Content-disposition: attachment; filename="'. \XLSXWriter::sanitize_filename($filename).'"');
		header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');  
		
		$writer->writeToStdOut();
		exit;
	}
}