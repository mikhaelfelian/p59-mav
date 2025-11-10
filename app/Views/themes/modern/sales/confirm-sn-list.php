<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-10
 * Github: github.com/mikhaelfelian
 * Description: View for listing pending agent orders that need SN confirmation
 */
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Verifikasi Order Agent</h5>
		<p class="text-muted mb-0">Daftar penjualan agent yang memerlukan verifikasi dan aktivasi serial number</p>
	</div>

	<div class="card-body">
		<?php
		if (!empty($msg)) {
			show_alert($msg);
		}

		// Define columns for DataTables
		$column = [
			'ignore_search_urut'    => 'No',
			'invoice_no'            => 'No Nota',
			'customer_name'         => 'Pelanggan',
			'agent_name'            => 'Agent',
			'grand_total'           => 'Total',
			'pending_sn_count'     => 'Pending SN',
			'created_at'            => 'Tanggal',
			'ignore_search_action'  => '#'
		];

		$settings['order'] = [6, 'desc']; // Order by created_at descending
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
		<span id="dataTables-url" style="display:none"><?= $config->baseURL ?>agent/sales/confirm/getDataDT</span>
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
});
</script>

