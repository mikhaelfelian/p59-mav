<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?= $current_module['judul_module'] ?></h5>
	</div>

	<div class="card-body">
		<a href="<?= $config->baseURL ?>item/add" class="btn btn-success btn-xs btn-add">
			<i class="fa fa-plus pe-1"></i> Tambah
		</a>
		<hr />
		<?php
		if (!empty($msg)) {
			show_alert($msg);
		}

		// Daftar kolom tabel dengan label dalam Bahasa Indonesia
		$column = [
			'ignore_search_urut' => 'No',
			'sku' => 'SKU',
			'name' => 'Nama Item',
			'brand_name' => 'Brand',
			'category_name' => 'Kategori',
			'price' => 'Harga',
			'is_stockable' => 'Stockable',
			'status' => 'Status',
			'ignore_search_action' => 'Aksi'
		];

		$settings['order'] = [2, 'asc'];
		$index = 0;
		$th = '';
		// Membuat header tabel & pengaturan kolom tidak bisa diurut jika perlu
		foreach ($column as $key => $val) {
			$th .= '<th>' . $val . '</th>';
			if (strpos($key, 'ignore_search') !== false) {
				$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
			}
			$index++;
		}

		?>

		<table id="table-result" class="table display table-striped table-bordered table-hover" style="width:100%">
			<thead>
				<tr>
					<?= $th ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<?= $th ?>
				</tr>
			</tfoot>
		</table>
		<?php
		// Siapkan data kolom untuk DataTables
		foreach ($column as $key => $val) {
			$column_dt[] = ['data' => $key];
		}
		?>
		<span id="dataTables-column" style="display:none"><?= json_encode($column_dt) ?></span>
		<span id="dataTables-setting" style="display:none"><?= json_encode($settings) ?></span>
		<span id="dataTables-url" style="display:none"><?= $config->baseURL ?>item/getItemDT</span>
	</div>
</div>
<!-- JavaScript moved to item-form.js -->
