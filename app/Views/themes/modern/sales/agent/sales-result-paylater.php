<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-19
 * Github: github.com/mikhaelfelian
 * Description: View for agent paylater sales list/result with DataTables
 */
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Data Pembayaran Paylater</h5>
	</div>

	<div class="card-body">
		<?php
		if (!empty($msg)) {
			show_alert($msg);
		}
		?>

		<!-- Bayar Semua Button -->
		<div class="mb-3">
			<button type="button" class="btn btn-primary btn-bayar-semua" id="btnBayarSemua">
				<i class="fas fa-money-bill-wave me-2"></i>Bayar Semua
			</button>
		</div>

		<?php
		// Define columns for DataTables
		$column = [
			'ignore_search_urut'    => 'No',
			'invoice_no'            => 'No Nota',
			'customer_name'         => 'Pelanggan',
			'grand_total'           => 'Total',
			'created_at'            => 'Tanggal',
			'ignore_search_action'  => 'Aksi'
		];

		$settings['order'] = [4, 'desc']; // Order by created_at descending
		$index = 0;
		$th = '';
		
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
		// Prepare column data for DataTables
		foreach ($column as $key => $val) {
			$column_dt[] = ['data' => $key];
		}
		?>
		
		<span id="dataTables-column" style="display:none"><?= json_encode($column_dt) ?></span>
		<span id="dataTables-setting" style="display:none"><?= json_encode($settings) ?></span>
		<span id="dataTables-url" style="display:none"><?= $config->baseURL ?>agent/sales-paylater/getDataDT</span>
	</div>
</div>

<script>
$(document).ready(function() {
	// Initialize DataTables
	var column = JSON.parse($('#dataTables-column').text());
	var settings = JSON.parse($('#dataTables-setting').text());
	var url = $('#dataTables-url').text();

	$('#table-result').DataTable({
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": url,
			"type": "POST"
		},
		"columns": column,
		"order": settings.order,
		"columnDefs": settings.columnDefs,
		"pageLength": 10,
		"lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
		"language": {
			"processing": "Memuat...",
			"emptyTable": "Tidak ada data",
			"zeroRecords": "Data tidak ditemukan"
		}
	});

	// Bayar Semua button click handler (placeholder - functionality to be added later)
	$('#btnBayarSemua').on('click', function() {
		// Functionality will be added in next order
		console.log('Bayar Semua clicked');
	});

	// Individual Bayar button click handler (placeholder - functionality to be added later)
	$(document).on('click', '.btn-bayar', function() {
		var saleId = $(this).data('sale-id');
		// Functionality will be added in next order
		console.log('Bayar clicked for sale ID:', saleId);
	});
});
</script>

