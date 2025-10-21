<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	<form method="get">
	<?php helper('html')?>
	</form>
	<div class="card-body">
		<?php
		if ($message['status'] == 'error') {
			show_message($message);
		}
		?>
		<form method="get" action="" class="mb-4">
			<div class="row">
				<label class="col-sm-2 col-md-2 col-lg-2 col-xl-1 col-form-label">Tahun</label>
				<div class="col-sm-5 form-inline">
					<?=options(['name' => 'tahun'], $list_tahun, $tahun )?>
					<button type="submit" class="btn btn-primary">Submit</button>
				</div>
			</div>
		</form>
		<?php
		if ($message['status'] == 'ok') {
			?>
			<div class="row">
				<div class="col-lg-12 col-xl-6 mb-4" style="overflow-x:auto">
					<div id="chart-container" style="min-width:550px; margin:auto"></div>
				</div>
				<div class="col-lg-12 col-xl-6 mb-4" style="overflow-x:auto">
					<div id="pie-container" style="min-width:400px; margin:auto"></div>
				</div>
			</div>	
		<?php
		}?>
	</div>
</div>

<?php
$penjualan_perbulan = [];
$pembelian_perbulan = [];
$profit_perbulan = [];
foreach ($penjualan as $val) {
	$penjualan_perbulan[] = $val['total'];
	$pembelian_perbulan[] = $val['total_beli'];
	$profit_perbulan[] = $val['total'] - $val['total_beli'];
}

$jumlah = $nama_barang = [];
foreach ($item_terjual as $val) {
	$jumlah[] = $val['jml'];
	$nama_barang[] = $val['nama_barang'];
}
?>

<script type="text/javascript">
let penjualan_perbulan = <?=json_encode($penjualan_perbulan)?>;
let pembelian_perbulan = <?=json_encode($pembelian_perbulan)?>;
let profit_perbulan = <?=json_encode($profit_perbulan)?>;
let item_terjual = <?=json_encode($jumlah, JSON_NUMERIC_CHECK)?>;
let item_terjual_label = <?=json_encode($nama_barang)?>;
let tahun_current = <?=$tahun?>;
</script>