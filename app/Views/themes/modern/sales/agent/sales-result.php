<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-10
 * Github: github.com/mikhaelfelian
 * Description: View for agent sales list/result with DataTables
 */
?>
<div class="card">
	<div class="card-header">
		<?php
		// Determine permission and title based on 'read_all'
		if (!empty($read_all) && $read_all > 0) {
			$title = 'Penjualan (Online)';
		} else {
			$title = 'Pembelian';
		}
		?>
		<h5 class="card-title">Data <?= $title ?></h5>
	</div>

	<div class="card-body">	
		<?php
		if (!empty($msg)) {
			show_alert($msg);
		}

		// Filter Form (Admin Only)
		if (!empty($read_all) && $read_all > 0):
		?>
		<div class="card mb-3">
			<div class="card-body">
				<form id="filterForm" class="row g-3">
					<!-- Agent Filter -->
					<div class="col-md-4">
						<label for="filter_agent_id" class="form-label">Agent</label>
						<select class="form-select" id="filter_agent_id" name="filter_agent_id">
							<option value="">Semua Agent</option>
							<?php if (!empty($agents)): ?>
								<?php foreach ($agents as $agent): ?>
									<option value="<?= esc($agent->id) ?>"><?= esc($agent->code . ' - ' . $agent->name) ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
					<!-- Platform Filter -->
					<div class="col-md-4">
						<label for="filter_platform" class="form-label">Platform</label>
						<select class="form-select" id="filter_platform" name="filter_platform">
							<option value="">Semua Platform</option>
							<option value="2">Paid</option>
							<option value="3">Paylater</option>
							<option value="0">Unpaid</option>
						</select>
					</div>
					<!-- Action Buttons -->
					<div class="col-md-4 d-flex align-items-end gap-2">
						<button type="button" class="btn btn-primary" id="btnFilter">
							<i class="fas fa-filter me-2"></i>Filter
						</button>
						<button type="button" class="btn btn-secondary" id="btnReset">
							<i class="fas fa-redo me-2"></i>Reset
						</button>
					</div>
				</form>
			</div>
		</div>
		<?php endif; ?>

		<?php
		// Statistics Cards (Agent Only)
		if (empty($read_all) || $read_all == 0):
			if (!empty($statistics)):
		?>
		<div class="row mb-3">
			<!-- Total Loan -->
			<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 mb-3">
				<div class="card text-white bg-warning shadow">
					<div class="card-body card-stats">
						<div class="description">
							<h5 class="card-title h4"><?= format_number($statistics['total_loan']) ?></h5>
							<p class="card-text">Total Loan</p>
						</div>
						<div class="icon">
							<i class="material-icons">account_balance_wallet</i>
						</div>
					</div>
				</div>
			</div>
			<!-- Total Paid -->
			<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 mb-3">
				<div class="card text-white bg-success shadow">
					<div class="card-body card-stats">
						<div class="description">
							<h5 class="card-title h4"><?= format_number($statistics['total_paid']) ?></h5>
							<p class="card-text">Total Paid</p>
						</div>
						<div class="icon">
							<i class="material-icons">payments</i>
						</div>
					</div>
				</div>
			</div>
			<!-- Total Amount -->
			<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 mb-3">
				<div class="card text-white bg-primary shadow">
					<div class="card-body card-stats">
						<div class="description">
							<h5 class="card-title h4"><?= format_number($statistics['total_amount']) ?></h5>
							<p class="card-text">Total Amount</p>
						</div>
						<div class="icon">
							<i class="material-icons">attach_money</i>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
			endif;
		endif;
		?>

		<?php
		// Define columns for DataTables
		$column = [
			'ignore_search_urut'    => 'No',
			'invoice_no'            => 'No Nota',
			'customer_name'         => 'Agen',
			'grand_total'           => 'Total',
			'balance_due'           => 'Kurang Bayar',
			'payment_status'        => 'Status',
			'created_at'            => 'Tanggal',
			'ignore_search_action'  => '#'
		];

		$settings['order'] = [7, 'desc']; // Order by created_at descending
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
		<span id="dataTables-url" style="display:none"><?= $config->baseURL ?>agent/sales/getDataDT</span>
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
			"type": "POST",
			"data": function (d) {
				// Add filter values to DataTables request
				d.filter_agent_id = $('#filter_agent_id').val();
				d.filter_platform = $('#filter_platform').val();
			}
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

	// Filter button click
	$('#btnFilter').on('click', function () {
		table.ajax.reload();
	});

	// Reset button click
	$('#btnReset').on('click', function () {
		$('#filterForm')[0].reset();
		table.ajax.reload();
	});

	// Prevent form submission on Enter key
	$('#filterForm').on('submit', function (e) {
		e.preventDefault();
		table.ajax.reload();
	});
});
</script>
