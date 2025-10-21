<?php namespace App\Models;
class GroceryCrudDatatablesAjaxModel extends BaseModel {
	
	public function countAllData() {
		$sql = 'SELECT COUNT(*) AS jml FROM ' . $_GET['database_table'];
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData() {

		$columns = $_POST['columns'];

		// Search
		$where = ' WHERE 1 = 1 ';
		$search_all = @$_POST['search']['value'];
		if ($search_all) {
			
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
		$order_data = $_POST['order'];
		$order = '';
		if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by;
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml_data FROM ' . $_GET['database_table'] . ' ' . $where;
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $_POST['start'] ?: 0;
		$length = $_POST['length'] ?: 10;
		$sql = 'SELECT * FROM ' . $_GET['database_table'] . ' 
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
