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
				<button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">
					Belum diterima
				</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="unused-tab" data-bs-toggle="tab" data-bs-target="#unused" type="button" role="tab" aria-controls="unused" aria-selected="false">
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
			<!-- Belum diterima Tab -->
			<div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
				<?php
				// Show "Terima Semua" button if there are unreceived SNs and user is agent
				$hasUnreceivedSNs = !empty($totalUnreceivedCount) && $totalUnreceivedCount > 0;
				$showReceiveAllBtn = !empty($isAgent) && $isAgent && $hasUnreceivedSNs;
				?>
				
				<?php if ($showReceiveAllBtn): ?>
				<div class="mb-3 d-flex justify-content-end">
					<button type="button" class="btn btn-success text-white" id="btnReceiveAllUnreceived">
						<i class="fas fa-check-double me-1"></i>Terima Semua (<?= $totalUnreceivedCount ?> SN)
					</button>
				</div>
				<?php endif; ?>

				<?php
				// Define columns for DataTables
				$column_all = [
					'ignore_search_urut'    => 'No',
					'invoice_no'            => 'No Nota',
					'sn'                    => 'SN',
					'item_name'             => 'Item',
					'item_sku'              => 'Item Code',
					'ignore_search_action'  => 'Aksi'
				];

				$settings_all['order'] = [1, 'desc']; // Order by SN descending
				$index_all = 0;
				$th_all = '';
				
				foreach ($column_all as $key => $val) {
					$th_all .= '<th>' . $val . '</th>';
					if (strpos($key, 'ignore_search') !== false) {
						$settings_all['columnDefs'][] = ["targets" => $index_all, "orderable" => false];
					}
					$index_all++;
				}
				?>

				<table id="table-all" class="table display table-striped table-bordered table-hover sn-table" style="width:100%">
					<thead>
						<tr>
							<?= $th_all ?>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<?= $th_all ?>
						</tr>
					</tfoot>
				</table>
				
				<?php
				// Prepare column data for DataTables
				$column_dt_all = [];
				foreach ($column_all as $key => $val) {
					$column_dt_all[] = ['data' => $key];
				}
				?>
				
				<span id="dataTables-column-all" style="display:none"><?= json_encode($column_dt_all) ?></span>
				<span id="dataTables-setting-all" style="display:none"><?= json_encode($settings_all) ?></span>
				<span id="dataTables-url-all" style="display:none"><?= $config->baseURL ?>agent/sales/getSnDataDT</span>
			</div>

			<!-- Belum Digunakan Tab -->
			<div class="tab-pane fade" id="unused" role="tabpanel" aria-labelledby="unused-tab">
				<?php
				// Define columns for DataTables
				$column = [
					'ignore_search_urut'    => 'No',
					'invoice_no'            => 'No Nota',
					'sn'                    => 'SN',
					'item_name'             => 'Item',
					'item_sku'              => 'Item Code',
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
					'item_sku'              => 'Item Code',
					'barcode'               => 'Barcode',
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
	// Initialize DataTables for all tab (Belum diterima)
	var columnAll = JSON.parse($('#dataTables-column-all').text());
	var settingsAll = JSON.parse($('#dataTables-setting-all').text());
	var urlAll = $('#dataTables-url-all').text();

	var tableAll = $('#table-all').DataTable({
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": urlAll,
			"type": "POST",
			"data": function(d) {
				d.filter = 'unreceived';
			}
		},
		"columns": columnAll,
		"order": settingsAll.order,
		"columnDefs": settingsAll.columnDefs,
		"pageLength": 10,
		"lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
		"language": {
			"processing": "Memuat...",
			"emptyTable": "Tidak ada data serial number yang belum diterima",
			"zeroRecords": "Data tidak ditemukan"
		}
	});

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
	$('#all-tab').on('shown.bs.tab', function() {
		tableAll.columns.adjust().draw();
	});

	$('#used-tab').on('shown.bs.tab', function() {
		tableUsed.columns.adjust().draw();
	});

	$('#unused-tab').on('shown.bs.tab', function() {
		tableUnused.columns.adjust().draw();
	});

	// Handle receive SN button click
	$(document).on('click', '.receive-sn-btn', function() {
		var $btn = $(this);
		var salesItemSnId = $btn.data('sales-item-sn-id');
		var saleId = $btn.data('sale-id');
		var sn = $btn.data('sn');

		if (!salesItemSnId || !saleId) {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Data tidak valid.'
				});
			} else {
				alert('Data tidak valid.');
			}
			return;
		}

		if (typeof Swal !== 'undefined') {
			Swal.fire({
				icon: 'question',
				title: 'Konfirmasi',
				text: 'Apakah Anda yakin ingin menerima serial number ' + sn + '?',
				showCancelButton: true,
				confirmButtonText: 'Ya, Terima',
				cancelButtonText: 'Batal'
			}).then(function(result) {
				if (result.isConfirmed) {
					performReceiveSN(saleId, salesItemSnId, sn, $btn);
				}
			});
		} else {
			if (confirm('Apakah Anda yakin ingin menerima serial number ' + sn + '?')) {
				performReceiveSN(saleId, salesItemSnId, sn, $btn);
			}
		}
	});

	function performReceiveSN(saleId, salesItemSnId, sn, $btn) {
		$btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Memproses...');

		$.ajax({
			url: '<?= $config->baseURL ?>agent/sales/confirm/receiveSN/' + saleId + '/' + salesItemSnId,
			type: 'POST',
			data: {
				<?= csrf_token() ?>: '<?= csrf_hash() ?>'
			},
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			},
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success') {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message,
							timer: 2000,
							showConfirmButton: false
						}).then(function() {
							tableAll.ajax.reload();
						});
					} else {
						alert(response.message);
						tableAll.ajax.reload();
					}
				} else {
					$btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Terima');
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.message
						});
					} else {
						alert(response.message);
					}
				}
			},
			error: function(xhr) {
				$btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Terima');
				var errorMsg = 'Terjadi kesalahan saat menerima serial number.';
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMsg = xhr.responseJSON.message;
				}
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMsg
					});
				} else {
					alert(errorMsg);
				}
			}
		});
	}

	// Handle "Terima Semua" button click
	$('#btnReceiveAllUnreceived').on('click', function() {
		var $btn = $(this);
		var originalText = $btn.html();
		var unreceivedCount = <?= $totalUnreceivedCount ?? 0 ?>;

		if (unreceivedCount <= 0) {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					icon: 'info',
					title: 'Info',
					text: 'Tidak ada serial number yang perlu diterima.'
				});
			} else {
				alert('Tidak ada serial number yang perlu diterima.');
			}
			return;
		}

		// Confirm action
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				icon: 'question',
				title: 'Konfirmasi',
				text: 'Apakah Anda yakin ingin menerima semua ' + unreceivedCount + ' serial number?',
				showCancelButton: true,
				confirmButtonText: 'Ya, Terima Semua',
				cancelButtonText: 'Batal'
			}).then(function(result) {
				if (result.isConfirmed) {
					performReceiveAllUnreceived($btn, originalText);
				}
			});
		} else {
			if (confirm('Apakah Anda yakin ingin menerima semua ' + unreceivedCount + ' serial number?')) {
				performReceiveAllUnreceived($btn, originalText);
			}
		}
	});

	function performReceiveAllUnreceived($btn, originalText) {
		// Disable button and show loading
		$btn.prop('disabled', true);
		$btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Memproses...');

		$.ajax({
			url: '<?= $config->baseURL ?>agent/sales/receiveAllUnreceivedSN',
			type: 'POST',
			data: {
				<?= csrf_token() ?>: '<?= csrf_hash() ?>'
			},
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			},
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success' || response.status === 'info') {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: response.status === 'success' ? 'success' : 'info',
							title: response.status === 'success' ? 'Berhasil' : 'Info',
							text: response.message || 'Serial number berhasil diterima',
							timer: 2000,
							showConfirmButton: false
						}).then(function() {
							// Reload the DataTable
							tableAll.ajax.reload();
							// Reload page to update count
							location.reload();
						});
					} else {
						alert(response.message || 'Serial number berhasil diterima');
						tableAll.ajax.reload();
						location.reload();
					}
				} else {
					$btn.prop('disabled', false);
					$btn.html(originalText);
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.message || 'Gagal menerima serial number'
						});
					} else {
						alert(response.message || 'Gagal menerima serial number');
					}
				}
			},
			error: function(xhr) {
				$btn.prop('disabled', false);
				$btn.html(originalText);
				var errorMsg = 'Terjadi kesalahan saat menerima serial number.';
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMsg = xhr.responseJSON.message;
				}
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMsg
					});
				} else {
					alert(errorMsg);
				}
			}
		});
	}
});
</script>

