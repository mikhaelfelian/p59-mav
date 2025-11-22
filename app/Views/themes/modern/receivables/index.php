<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-20
 * Github: github.com/mikhaelfelian
 * Description: View for agent receivables monitoring with aging report
 */
?>
<div class="card shadow-sm border-0">
	<div class="card-header bg-white py-3">
		<h5 class="card-title mb-0">
			<i class="fas fa-chart-line me-2 text-primary"></i>
			<?= esc($title ?? 'Monitoring Piutang Agen') ?>
		</h5>
	</div>

	<div class="card-body">
		<?php if (!empty($msg)) : ?>
			<?= show_alert($msg); ?>
		<?php endif; ?>

		<!-- Aging Summary Cards -->
		<div class="row g-3 mb-4">
			<div class="col-md-3">
				<div class="card border-0 shadow-sm">
					<div class="card-body">
						<div class="d-flex justify-content-between align-items-center">
							<div>
								<h6 class="text-muted mb-1">Total Piutang</h6>
								<h4 class="mb-0 text-primary">Rp <?= number_format($agingSummary['total_receivables'] ?? 0, 0, ',', '.') ?></h4>
							</div>
							<div class="text-primary">
								<i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card border-0 shadow-sm">
					<div class="card-body">
						<div class="d-flex justify-content-between align-items-center">
							<div>
								<h6 class="text-muted mb-1">Current</h6>
								<h4 class="mb-0 text-success">Rp <?= number_format($agingSummary['current'] ?? 0, 0, ',', '.') ?></h4>
							</div>
							<div class="text-success">
								<i class="fas fa-check-circle fa-2x opacity-50"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card border-0 shadow-sm">
					<div class="card-body">
						<div class="d-flex justify-content-between align-items-center">
							<div>
								<h6 class="text-muted mb-1">Overdue 30d</h6>
								<h4 class="mb-0 text-warning">Rp <?= number_format($agingSummary['overdue_30d'] ?? 0, 0, ',', '.') ?></h4>
							</div>
							<div class="text-warning">
								<i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card border-0 shadow-sm">
					<div class="card-body">
						<div class="d-flex justify-content-between align-items-center">
							<div>
								<h6 class="text-muted mb-1">Overdue 60d+</h6>
								<h4 class="mb-0 text-danger">Rp <?= number_format(($agingSummary['overdue_60d'] ?? 0) + ($agingSummary['overdue_90d'] ?? 0) + ($agingSummary['overdue_90d_plus'] ?? 0), 0, ',', '.') ?></h4>
							</div>
							<div class="text-danger">
								<i class="fas fa-ban fa-2x opacity-50"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Agents with Receivables Table -->
		<?php
		// Define columns for DataTables
		$column = [
			'ignore_search_urut'    => 'No',
			'agent_code'            => 'Kode Agen',
			'agent_name'            => 'Nama Agen',
			'total_receivables'     => 'Total Piutang',
			'current'               => 'Current',
			'overdue_30d'           => '30d',
			'overdue_60d'           => '60d',
			'overdue_90d'           => '90d',
			'overdue_90d_plus'      => '90d+',
			'status'                => 'Status',
			'ignore_search_action'  => 'Aksi'
		];

		$settings['order'] = [3, 'desc']; // Order by total_receivables descending
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
		<span id="dataTables-url" style="display:none"><?= $config->baseURL ?>receivables/getDataDT</span>
	</div>
</div>

<script>
$(document).ready(function() {
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
			"emptyTable": "Tidak ada data piutang",
			"zeroRecords": "Data tidak ditemukan"
		}
	});

	// Handle block agent
	$(document).on('click', '.block-agent', function() {
		var agentId = $(this).data('agent-id');
		if (confirm('Yakin ingin memblokir agen ini? Agen tidak akan bisa melakukan order baru.')) {
			$.ajax({
				url: '<?= $config->baseURL ?>receivables/block/' + agentId,
				type: 'POST',
				data: {
					<?= csrf_token() ?>: '<?= csrf_hash() ?>'
				},
				success: function(response) {
					if (response.status === 'success') {
						alert(response.message);
						table.ajax.reload();
					} else {
						alert(response.message);
					}
				},
				error: function() {
					alert('Terjadi kesalahan saat memblokir agen.');
				}
			});
		}
	});

	// Handle unblock agent
	$(document).on('click', '.unblock-agent', function() {
		var agentId = $(this).data('agent-id');
		if (confirm('Yakin ingin membuka blokir agen ini?')) {
			$.ajax({
				url: '<?= $config->baseURL ?>receivables/unblock/' + agentId,
				type: 'POST',
				data: {
					<?= csrf_token() ?>: '<?= csrf_hash() ?>'
				},
				success: function(response) {
					if (response.status === 'success') {
						alert(response.message);
						table.ajax.reload();
					} else {
						alert(response.message);
					}
				},
				error: function() {
					alert('Terjadi kesalahan saat membuka blokir agen.');
				}
			});
		}
	});

	// Handle send reminder
	$(document).on('click', '.send-reminder', function() {
		var agentId = $(this).data('agent-id');
		if (confirm('Yakin ingin mengirim reminder pembayaran ke agen ini?')) {
			$.ajax({
				url: '<?= $config->baseURL ?>receivables/reminder/' + agentId,
				type: 'POST',
				data: {
					<?= csrf_token() ?>: '<?= csrf_hash() ?>'
				},
				success: function(response) {
					if (response.status === 'success') {
						alert(response.message);
					} else {
						alert(response.message);
					}
				},
				error: function() {
					alert('Terjadi kesalahan saat mengirim reminder.');
				}
			});
		}
	});
});
</script>

