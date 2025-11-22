<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-20
 * Github: github.com/mikhaelfelian
 * Description: Pending warranty claims list for admin review
 */
?>
<div class="card shadow-sm border-0">
	<div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
		<div>
			<h5 class="card-title mb-0">
				<i class="fas fa-clock me-2 text-warning"></i>
				<?= esc($title ?? 'Daftar Klaim Pending') ?>
			</h5>
			<small class="text-muted">Menampilkan klaim garansi yang menunggu review.</small>
		</div>
		<div class="d-flex gap-2">
			<a href="<?= $config->baseURL ?>warranty/history" class="btn btn-light">
				<i class="fas fa-history me-1"></i> Riwayat
			</a>
		</div>
	</div>
	<div class="card-body">
		<?php if (!empty($msg)) : ?>
			<?= show_alert($msg); ?>
		<?php endif; ?>

		<?php
		// Define columns for DataTables
		$column = [
			'ignore_search_urut'    => 'No',
			'id'                    => 'ID Klaim',
			'serial_number'         => 'Serial Number',
			'agent_name'            => 'Agent',
			'issue_reason'          => 'Alasan',
			'created_at'            => 'Tanggal Klaim',
			'ignore_search_action'  => 'Aksi'
		];

		$settings['order'] = [5, 'desc']; // Order by created_at descending
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
		$column_dt = [];
		foreach ($column as $key => $val) {
			$column_dt[] = ['data' => $key];
		}
		?>

		<span id="dataTables-column" style="display:none"><?= json_encode($column_dt) ?></span>
		<span id="dataTables-setting" style="display:none"><?= json_encode($settings) ?></span>
		<span id="dataTables-url" style="display:none"><?= $config->baseURL ?>warranty/review/getDataDT</span>
	</div>
</div>

<script>
	$(document).ready(function () {
		// Initialize DataTables
		var column = JSON.parse($('#dataTables-column').text());
		var settings = JSON.parse($('#dataTables-setting').text());
		var url = $('#dataTables-url').text();

		var table = $('#table-result').DataTable({
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
				"emptyTable": "Tidak ada klaim garansi yang pending",
				"zeroRecords": "Data tidak ditemukan"
			}
		});
	});
</script>

