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
		<form method="get" action="" class="form-horizontal mb-5">
			<div class="row mb-3">
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
		
			<div class="row mb-3">
				<div class="col-12 col-md-12 col-lg-12 col-xl-7"  style="overflow-x:auto">
					<div id="bar-container" style="width: 650px;height:400px;margin:auto"></div>
				</div>
				<div class="col-12 col-md-12 col-lg-12 col-xl-5" style="overflow-x:auto">
					<div id="pie-container" style="width: 400px; height:450px;margin:auto;"></div>
				</div>
			</div>	
		<?php
		}
		?>
	</div>
</div>
<?php
$penjualan_perbulan = [];
$pembelian_perbulan = [];
foreach ($penjualan as $val) {
	$penjualan_perbulan[] = $val['total'];
	$pembelian_perbulan[] = $val['total_beli'];
}

$jumlah = [];
foreach ($item_terjual as $val) {
	$jumlah[] = ['value' => $val['jml'], 'name' => $val['nama_barang']];
}
?>

<script type="text/javascript">
let penjualan_perbulan = <?=json_encode($penjualan_perbulan)?>;
let pembelian_perbulan = <?=json_encode($pembelian_perbulan)?>;
let item_terjual = <?=json_encode($jumlah)?>;
let tahun_current = <?=$tahun?>;
</script>
