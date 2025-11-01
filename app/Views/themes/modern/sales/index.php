<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: View for sales list with DataTables
 */
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?= $title ?? 'Data Penjualan' ?></h5>
	</div>

	<div class="card-body">
		<?php if ($this->hasPermissionPrefix('create', true)): ?>
		<a href="<?= $config->baseURL ?>sales/create" class="btn btn-success btn-xs btn-add">
			<i class="fa fa-plus pe-1"></i> Tambah Penjualan
		</a>
		<?php endif; ?>
		<hr />
		<?php
		if (!empty($message)) {
			show_alert($message);
		}

		// Define columns for DataTables
		$column = [
			'ignore_search_urut' => 'No',
			'invoice_code' => 'Invoice Code',
			'invoice_date' => 'Tanggal',
			'customer_name' => 'Customer',
			'agent_name' => 'Agen',
			'grand_total' => 'Total',
			'status_payment' => 'Status Pembayaran',
			'status_order' => 'Status Order',
			'ignore_search_action' => 'Aksi'
		];

		$settings['order'] = [1, 'desc']; // Order by invoice_date descending
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
		<span id="dataTables-url" style="display:none"><?= $config->baseURL ?>sales/getDataDT</span>
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

	// Handle delete button
	$(document).on('click', '.btn-delete-sale', function(e) {
		e.preventDefault();
		var id = $(this).data('id');
		var invoice = $(this).data('invoice');

		Swal.fire({
			title: 'Apakah Anda yakin?',
			text: "Hapus penjualan dengan invoice: " + invoice + "?",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Ya, hapus!'
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: '<?= $config->baseURL ?>sales/delete/' + id,
					type: 'GET',
					dataType: 'json',
					success: function(response) {
						if (response.status === 'success') {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil!',
								text: response.message,
								timer: 2000,
								showConfirmButton: false
							}).then(function() {
								$('#table-result').DataTable().ajax.reload();
							});
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Error!',
								text: response.message
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error!',
							text: 'Terjadi kesalahan saat menghapus data.'
						});
					}
				});
			}
		});
	});
});
</script>

