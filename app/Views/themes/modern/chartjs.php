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
				<div class="col-lg-12 col-xl-6 mb-4 p-2" style="overflow-x:auto">
					<canvas id="bar-container" height="400px" style="min-width:500px;margin:auto;width:100%"></canvas>
				</div>
				<div class="col-lg-12 col-xl-6 mb-4" style="overflow-x:auto">
					<div class="overflow-auto">
						<canvas id="pie-container" style="max-width:400px;margin:auto;height:300px"></canvas>
					</div>
				</div>
			</div>
	<?php
	}?>
	</div>
</div>

<?php
$penjualan_perbulan = [];
foreach ($penjualan as $val) {
	$penjualan_perbulan[] = $val['total'];
}

$jumlah = $nama = [];
foreach ($item_terjual as $val) {
	$jumlah[] = $val['jml'];
	$nama[] = $val['nama_barang'];
}
?>

<script type="text/javascript">
let penjualan_perbulan = <?=json_encode($penjualan_perbulan)?>;
let item_terjual = <?=json_encode($jumlah)?>;
let item_terjual_label = <?=json_encode($nama)?>;
let tahun_current = <?=$tahun?>;

function dynamicColors() {
	var r = Math.floor(Math.random() * 255);
	var g = Math.floor(Math.random() * 255);
	var b = Math.floor(Math.random() * 255);
	return "rgba(" + r + "," + g + "," + b + ", 0.8)";
}
</script>