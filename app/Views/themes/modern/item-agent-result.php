<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?= $current_module['judul_module'] ?></h5>
	</div>

	<div class="card-body">
		<a href="<?= $config->baseURL ?>item-agent/add" class="btn btn-success btn-xs">
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
			'name' => 'Nama Produk',
			'price' => 'Harga Default',
			'agent_price' => 'Harga Agen',
			'status' => 'Status',
			'ignore_search_action' => 'Aksi'
		];

		$settings['order'] = [1, 'asc'];
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
		<span id="dataTables-url" style="display:none"><?= $config->baseURL ?>item-agent/getItemAgentDT</span>
	</div>
</div>

<script>
	$(document).ready(function () {
		// Inisialisasi DataTables
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

		// Event tombol tambah data: redirect ke halaman tambah
		$('.btn-add').on('click', function (e) {
			// Tidak perlu preventDefault - biarkan link berjalan default
		});

		// Event tombol edit: redirect ke halaman edit
		$(document).on('click', '.btn-edit', function (e) {
			e.preventDefault();
			var id = $(this).data('id');
			if (id) {
				window.location.href = "<?= $config->baseURL ?>item-agent/edit/" + id;
			}
		});

		// Event tombol hapus
		$(document).on('click', '.btn-delete', function (e) {
			e.preventDefault();
			var id = $(this).data('id');
			var name = $(this).data('name');

			Swal.fire({
				title: 'Apakah Anda yakin?',
				text: "Data yang sudah dihapus tidak dapat dikembalikan!",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Ya, hapus!'
			}).then((result) => {
				if (result.isConfirmed) {
					deleteItem(id);
				}
			});
		});

		// Fungsi untuk menghapus data item agent
		function deleteItem(id) {
			$.ajax({
				url: '<?= $config->baseURL ?>item-agent/delete',
				type: 'POST',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				data: { id: id },
				dataType: 'json',
				success: function (response) {
					if (response.status === 'success') {
						Swal.fire('Dihapus!', response.message, 'success');
						$('#table-result').DataTable().ajax.reload();
					} else {
						Swal.fire('Error!', response.message, 'error');
					}
				},
				error: function (xhr, status, error) {
					console.log('Delete Error:', xhr.responseText);
					Swal.fire('Error!', 'Gagal menghapus data: ' + error, 'error');
				}
			});
		}

		// Handle status toggle switch
		$(document).on('change', '.switch', function() {
			var id = $(this).data('module-id');
			var status = $(this).is(':checked') ? '1' : '0';
			var $switch = $(this);
			
			// Disable switch while processing
			$switch.prop('disabled', true);
			
			$.ajax({
				url: '<?= $config->baseURL ?>item-agent/toggleStatus',
				type: 'POST',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				data: { 
					id: id, 
					status: status 
				},
				dataType: 'json',
				success: function (response) {
					if (response.status === 'success') {
						// Show success toast
						const Toast = Swal.mixin({
							toast: true,
							position: 'top-end',
							showConfirmButton: false,
							timer: 2000,
							timerProgressBar: true,
							iconColor: 'white',
							customClass: {
								popup: 'bg-success text-light toast p-2'
							}
						});
						Toast.fire({
							html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Status berhasil diubah</div>'
						});
					} else {
						// Revert switch state on error
						$switch.prop('checked', !$switch.prop('checked'));
						Swal.fire({
							icon: 'error',
							title: 'Error!',
							text: response.message || 'Gagal mengubah status'
						});
					}
				},
				error: function (xhr) {
					// Revert switch state on error
					$switch.prop('checked', !$switch.prop('checked'));
					Swal.fire({
						icon: 'error',
						title: 'Error!',
						text: 'Terjadi kesalahan saat mengubah status'
					});
				},
				complete: function() {
					// Re-enable switch
					$switch.prop('disabled', false);
				}
			});
		});
	});
</script>