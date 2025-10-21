<?php
namespace App\Models;

class EchartsModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getListTahun() {
		$sql= 'SELECT YEAR(tgl_transaksi) AS tahun
				FROM toko_penjualan
				GROUP BY tahun';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getPenjualan($tahun) {
		
		 $sql = 'SELECT MONTH(tgl_transaksi) AS bulan, SUM(total_harga_beli) as total_beli, SUM(total_harga) total
				FROM toko_penjualan
				WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"
				GROUP BY MONTH(tgl_transaksi)';
		
        $penjualan = $this->db->query($sql, $tahun)->getResultArray();
		return $penjualan;
	}
	
	public function getItemTerjual($tahun) {
		$sql = 'SELECT id_barang, nama_barang, COUNT(id_barang) AS jml
				FROM toko_penjualan_detail
				LEFT JOIN toko_penjualan USING(id_penjualan)
				LEFT JOIN toko_barang USING(id_barang)
				WHERE tgl_transaksi >= "' . $tahun . '-01-01" AND tgl_transaksi <= "' . $tahun . '-12-31"
				GROUP BY id_barang
				ORDER BY jml DESC LIMIT 7';

        $item_terjual = $this->db->query($sql)->getResultArray();
		return $item_terjual;
	}
}