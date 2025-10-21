<?php
/**
* PHP Admin Template
* Author	: Agus Prawoto Hadi
* Website	: https://jagowebdev.com
* Year		: 2021-2022
*/

namespace App\Models;

class TanpaLoginModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getArtikelBySlug( $slug ) {
		$sql = 'SELECT * FROM artikel WHERE slug = ?';
		$result = $this->db->query($sql, $slug)->getRowArray();
		return $result;
	}
}