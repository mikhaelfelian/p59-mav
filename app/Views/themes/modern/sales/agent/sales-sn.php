<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-11
 * Github: github.com/mikhaelfelian
 * Description: View for agent sales serial numbers with tabbed interface
 */
?>
<style>
.sn-header {
	font-weight: 700;
	font-size: 1.5rem;
	color: #212529;
	margin-bottom: 1.5rem;
}

.nav-tabs-custom {
	border-bottom: 2px solid #dee2e6;
	margin-bottom: 1.5rem;
}

.nav-tabs-custom .nav-link {
	border: none;
	border-bottom: 2px solid transparent;
	padding: 0.75rem 1.5rem;
	color: #6c757d;
	font-weight: 500;
	background: transparent;
	margin-right: 0.5rem;
}

.nav-tabs-custom .nav-link:hover {
	border-bottom-color: #dee2e6;
	color: #212529;
}

.nav-tabs-custom .nav-link.active {
	color: #212529;
	border-bottom-color: #212529;
	background: #ffffff;
	font-weight: 600;
}

.sn-table {
	border-collapse: collapse;
	width: 100%;
	background: #ffffff;
}

.sn-table thead {
	background: #f8f9fa;
}

.sn-table th {
	padding: 0.75rem 1rem;
	text-align: left;
	font-weight: 600;
	font-size: 0.875rem;
	color: #495057;
	border: 1px solid #dee2e6;
	border-bottom: 2px solid #212529;
}

.sn-table td {
	padding: 0.75rem 1rem;
	border: 1px solid #dee2e6;
	color: #212529;
	vertical-align: middle;
}

.sn-table tbody tr:hover {
	background-color: #f8f9fa;
}

/* Dark theme support for table */
html[data-bs-theme="dark"] .sn-header {
	color: #adb5bd;
}

html[data-bs-theme="dark"] .nav-tabs-custom {
	border-bottom-color: #4a5560;
}

html[data-bs-theme="dark"] .nav-tabs-custom .nav-link {
	color: #adb5bd;
}

html[data-bs-theme="dark"] .nav-tabs-custom .nav-link:hover {
	border-bottom-color: #4a5560;
	color: #d7dbde;
}

html[data-bs-theme="dark"] .nav-tabs-custom .nav-link.active {
	color: #d7dbde;
	border-bottom-color: #d7dbde;
	background: #293042;
}

html[data-bs-theme="dark"] .sn-table {
	background: #293042;
	color: #adb5bd;
}

html[data-bs-theme="dark"] .sn-table thead {
	background: #2a3143;
}

html[data-bs-theme="dark"] .sn-table th {
	color: #d7dbde;
	border-color: #4a5560;
	border-bottom-color: #6c757d;
	background: #2a3143;
}

html[data-bs-theme="dark"] .sn-table td {
	border-color: #4a5560;
	color: #adb5bd;
	background: #293042;
}

html[data-bs-theme="dark"] .sn-table tbody tr:hover {
	background-color: #3a4258;
}

html[data-bs-theme="dark"] .sn-table tbody tr:nth-child(even) {
	background-color: #2a3143;
}

html[data-bs-theme="dark"] .sn-table tbody tr:nth-child(even):hover {
	background-color: #3a4258;
}

</style>

<div class="card shadow-sm border-0">
	<div class="card-body p-4">
		<h5 class="sn-header">Data Serial Number</h5>

		<?php if (!empty($message)): ?>
			<?= show_message($message) ?>
		<?php endif; ?>

		<!-- Tabs Navigation -->
		<ul class="nav nav-tabs nav-tabs-custom" id="snTabs" role="tablist">
			<li class="nav-item" role="presentation">
				<button class="nav-link active" id="unused-tab" data-bs-toggle="tab" data-bs-target="#unused" type="button" role="tab" aria-controls="unused" aria-selected="true">
					Belum Digunakan
				</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="used-tab" data-bs-toggle="tab" data-bs-target="#used" type="button" role="tab" aria-controls="used" aria-selected="false">
					Sudah Digunakan
				</button>
			</li>
		</ul>

		<!-- Tab Content -->
		<div class="tab-content" id="snTabContent">
			<!-- Belum Digunakan Tab -->
			<div class="tab-pane fade show active" id="unused" role="tabpanel" aria-labelledby="unused-tab">
				<?php
				// Define columns for DataTables
				$column = [
					'ignore_search_urut'    => 'No',
					'sn'                    => 'SN',
					'item_name'             => 'Item',
					'qty'                   => 'Qty',
					'ignore_search_action'  => 'Aksi'
				];

				$settings['order'] = [1, 'desc']; // Order by SN descending
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

				<table id="table-unused" class="table display table-striped table-bordered table-hover sn-table" style="width:100%">
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
				
				<span id="dataTables-column-unused" style="display:none"><?= json_encode($column_dt) ?></span>
				<span id="dataTables-setting-unused" style="display:none"><?= json_encode($settings) ?></span>
				<span id="dataTables-url-unused" style="display:none"><?= $config->baseURL ?>agent/sales/getSnDataDT</span>
			</div>

			<!-- Sudah Digunakan Tab -->
			<div class="tab-pane fade" id="used" role="tabpanel" aria-labelledby="used-tab">
				<?php
				// Define columns for DataTables (same as unused)
				$column_used = [
					'ignore_search_urut'    => 'No',
					'sn'                    => 'SN',
					'item_name'             => 'Item',
					'qty'                   => 'Qty',
					'ignore_search_action'  => 'Aksi'
				];

				$settings_used['order'] = [1, 'desc']; // Order by SN descending
				$index_used = 0;
				$th_used = '';
				
				foreach ($column_used as $key => $val) {
					$th_used .= '<th>' . $val . '</th>';
					if (strpos($key, 'ignore_search') !== false) {
						$settings_used['columnDefs'][] = ["targets" => $index_used, "orderable" => false];
					}
					$index_used++;
				}
				?>

				<table id="table-used" class="table display table-striped table-bordered table-hover sn-table" style="width:100%">
					<thead>
						<tr>
							<?= $th_used ?>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<?= $th_used ?>
						</tr>
					</tfoot>
				</table>
				
				<?php
				// Prepare column data for DataTables
				$column_dt_used = [];
				foreach ($column_used as $key => $val) {
					$column_dt_used[] = ['data' => $key];
				}
				?>
				
				<span id="dataTables-column-used" style="display:none"><?= json_encode($column_dt_used) ?></span>
				<span id="dataTables-setting-used" style="display:none"><?= json_encode($settings_used) ?></span>
				<span id="dataTables-url-used" style="display:none"><?= $config->baseURL ?>agent/sales/getSnDataDT</span>
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
	// Initialize DataTables for unused tab
	var columnUnused = JSON.parse($('#dataTables-column-unused').text());
	var settingsUnused = JSON.parse($('#dataTables-setting-unused').text());
	var urlUnused = $('#dataTables-url-unused').text();

	var tableUnused = $('#table-unused').DataTable({
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": urlUnused,
			"type": "POST",
			"data": function(d) {
				d.filter = 'unused';
			}
		},
		"columns": columnUnused,
		"order": settingsUnused.order,
		"columnDefs": settingsUnused.columnDefs,
		"pageLength": 10,
		"lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
		"language": {
			"processing": "Memuat...",
			"emptyTable": "Tidak ada data serial number yang belum digunakan",
			"zeroRecords": "Data tidak ditemukan"
		}
	});

	// Initialize DataTables for used tab
	var columnUsed = JSON.parse($('#dataTables-column-used').text());
	var settingsUsed = JSON.parse($('#dataTables-setting-used').text());
	var urlUsed = $('#dataTables-url-used').text();

	var tableUsed = $('#table-used').DataTable({
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": urlUsed,
			"type": "POST",
			"data": function(d) {
				d.filter = 'used';
			}
		},
		"columns": columnUsed,
		"order": settingsUsed.order,
		"columnDefs": settingsUsed.columnDefs,
		"pageLength": 10,
		"lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
		"language": {
			"processing": "Memuat...",
			"emptyTable": "Tidak ada data serial number yang sudah digunakan",
			"zeroRecords": "Data tidak ditemukan"
		}
	});

	// Reload DataTables when tab is shown
	$('#used-tab').on('shown.bs.tab', function() {
		tableUsed.columns.adjust().draw();
	});

	$('#unused-tab').on('shown.bs.tab', function() {
		tableUnused.columns.adjust().draw();
	});
});
</script>

