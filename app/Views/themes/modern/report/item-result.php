<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-19
 * Github: github.com/mikhaelfelian
 * Description: View for stock report list with filters and DataTables
 */
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?= $title ?></h5>
	</div>

	<div class="card-body">
		<?php
		if (!empty($msg)) {
			show_alert($msg);
		}
		?>

		<!-- Filter Form -->
		<div class="card mb-3">
			<div class="card-body">
				<form id="filterForm" class="row g-3">
					<!-- Date Range Picker -->
					<div class="col-md-3">
						<label for="date_range" class="form-label">Tanggal Rentang</label>
						<input type="text" class="form-control" id="date_range" name="date_range" placeholder="Pilih tanggal rentang">
					</div>
					<!-- Item -->
					<div class="col-md-3">
						<label for="item_id" class="form-label">Item</label>
						<select class="form-select select2" id="item_id" name="item_id" style="width: 100%;">
							<option value="">Semua Item</option>
							<?php if (!empty($items)): ?>
								<?php foreach ($items as $item): ?>
									<option value="<?= esc($item->id) ?>" data-sku="<?= esc($item->sku ?? '') ?>"><?= esc($item->name . ($item->sku ? ' (' . $item->sku . ')' : '')) ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
					<!-- Pemilik -->
					<div class="col-md-3">
						<label for="pemilik" class="form-label">Pemilik</label>
						<select class="form-select select2" id="pemilik" name="pemilik" style="width: 100%;">
							<option value="">Semua Pemilik</option>
							<?php if (!empty($agents)): ?>
								<?php foreach ($agents as $agent): ?>
									<option value="<?= esc($agent->id) ?>"><?= esc($agent->code . ' - ' . $agent->name) ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
					<!-- Pusat / Agent -->
					<div class="col-md-3">
						<label for="pusat_agent" class="form-label">Pusat / Agent</label>
						<select class="form-select" id="pusat_agent" name="pusat_agent">
							<option value="">Semua</option>
							<option value="pusat">Pusat</option>
							<option value="agent">Agent</option>
						</select>
					</div>
					<!-- Action Buttons -->
					<div class="col-md-12 d-flex align-items-end gap-2">
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

		<?php
		// Define columns for DataTables
		$column = [
			'ignore_search_urut'    => 'No',
			'item_name'             => 'Item Name',
			'item_sku'              => 'SKU',
			'sn'                    => 'Serial Number',
			'barcode'               => 'Barcode',
			'pemilik'               => 'Pemilik',
			'status'                => 'Status',
			'created_at'            => 'Tanggal',
			'ignore_search_action'  => 'Aksi'
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
		$column_dt = [];
		foreach ($column as $key => $val) {
			$column_dt[] = ['data' => $key];
		}
		?>

		<span id="dataTables-column" style="display:none"><?= json_encode($column_dt) ?></span>
		<span id="dataTables-setting" style="display:none"><?= json_encode($settings) ?></span>
		<span id="dataTables-url" style="display:none"><?= $config->baseURL ?>report/items/getDataDT</span>
	</div>
</div>

<script>
	$(document).ready(function () {
		const baseURL = '<?= $config->baseURL ?>';
		const csrfName = '<?= csrf_token() ?>';
		let csrfHash = '<?= csrf_hash() ?>';

		// Initialize DataTables
		var column = JSON.parse($('#dataTables-column').text());
		var settings = JSON.parse($('#dataTables-setting').text());
		var url = $('#dataTables-url').text();

		var table = $('#table-result').DataTable({
			"processing": true,
			"serverSide": true,
			"dom": "Bfrtip",
			"ajax": {
				"url": url,
				"type": "POST",
				"data": function (d) {
					// Add filter values to DataTables request
					d.date_range = $('#date_range').val();
					d.item_id = $('#item_id').val();
					d.pemilik = $('#pemilik').val();
					d.pusat_agent = $('#pusat_agent').val();
					d[csrfName] = csrfHash;
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
			},
			"buttons": [
				{
					"extend": "excel",
					"title": "Laporan Stok",
					"text": "<i class='far fa-file-excel me-1'></i> Excel",
					"className": "btn-light me-1",
					"exportOptions": {
						"columns": [0, 1, 2, 3, 4, 5, 6, 7],
						"modifier": {
							"selected": null
						},
						"format": {
							"body": function(data, row, column, node) {
								// Remove HTML tags from exported data
								if (typeof data === 'string') {
									var tmp = $('<div>').html(data).text();
									return tmp || data;
								}
								return data;
							}
						}
					}
				},
				{
					"extend": "pdf",
					"title": "Laporan Stok",
					"text": "<i class='far fa-file-pdf me-1'></i> PDF",
					"className": "btn-light me-1",
					"orientation": "portrait",
					"pageSize": "A4",
					"exportOptions": {
						"columns": [0, 1, 2, 3, 4, 5, 6, 7],
						"modifier": {
							"selected": null
						},
						"format": {
							"body": function(data, row, column, node) {
								// Remove HTML tags from exported data
								if (typeof data === 'string') {
									var tmp = $('<div>').html(data).text();
									return tmp || data;
								}
								return data;
							}
						}
					},
					"customize": function(doc) {
						doc.defaultStyle.fontSize = 9;
						doc.styles.tableHeader.fontSize = 10;
						doc.styles.tableHeader.alignment = 'center';
						doc.styles.tableHeader.fillColor = '#4e73df';
						doc.styles.tableHeader.color = '#ffffff';
						
						// Add header/kop
						doc.content.splice(0, 0, {
							margin: [0, 0, 0, 12],
							alignment: 'center',
							fontSize: 14,
							text: 'Laporan Stok',
							bold: true
						});
					}
				}
			]
		});

		// Place buttons container
		table.buttons().container()
			.appendTo('#table-result_wrapper .col-md-6:eq(0)');

		// Filter button click
		$('#btnFilter').on('click', function () {
			table.ajax.reload();
		});

		// Reset button click
		$('#btnReset').on('click', function () {
			$('#filterForm')[0].reset();
			// Clear Select2 dropdowns
			$('#item_id').val(null).trigger('change');
			$('#pemilik').val(null).trigger('change');
			table.ajax.reload();
		});

		// Prevent form submission on Enter key
		$('#filterForm').on('submit', function (e) {
			e.preventDefault();
			table.ajax.reload();
		});

		// Update CSRF token on DataTables response
		table.on('xhr.dt', function (e, settings, json) {
			if (json && json.csrf_hash) {
				csrfHash = json.csrf_hash;
			}
		});

		// Initialize Select2 for Item dropdown
		$('#item_id').select2({
			placeholder: 'Pilih Item',
			allowClear: true,
			width: '100%',
			language: {
				noResults: function() {
					return "Item tidak ditemukan";
				},
				searching: function() {
					return "Mencari...";
				}
			}
		});

		// Initialize Select2 for Pemilik dropdown
		$('#pemilik').select2({
			placeholder: 'Pilih Pemilik',
			allowClear: true,
			width: '100%',
			language: {
				noResults: function() {
					return "Pemilik tidak ditemukan";
				},
				searching: function() {
					return "Mencari...";
				}
			}
		});

		// Initialize flatpickr for date range
		$('#date_range').flatpickr({
			mode: "range",
			dateFormat: "Y-m-d",
			locale: "id",
			allowInput: true
		});
	});
</script>

