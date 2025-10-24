<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-24
 * Github: github.com/mikhaelfelian
 * Description: View for displaying agent data in DataTables with CRUD operations
 * This file represents the View for agent-result.
 */
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?= $current_module['judul_module'] ?></h5>
	</div>

	<div class="card-body">
		<?php if ($canCreate): ?>
		<a href="<?= $config->baseURL ?>agent/add" class="btn btn-success btn-xs btn-add">
			<i class="fa fa-plus pe-1"></i> Tambah
		</a>
		<?php endif; ?>
		<?php if ($canCreate): ?>
		<a href="<?= $config->baseURL ?>agent/upload" class="btn btn-info btn-xs">
			<i class="fa fa-upload pe-1"></i> Import Excel/CSV
		</a>
		<?php endif; ?>
		<hr />
		<?php
		if (!empty($msg)) {
			show_alert($msg);
		}

		// Daftar kolom tabel dengan label dalam Bahasa Indonesia
		$column = [
			'ignore_search_urut' => 'No',
			'code' => 'Kode',
			'name' => 'Agen',
			'address' => 'Alamat',
			'ignore_search_action' => 'Aksi'
		];

		$settings['order'] = [2, 'asc'];
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
		<span id="dataTables-url" style="display:none"><?= $config->baseURL ?>agent/getAgentDT</span>
	</div>
</div>

<!-- Bootbox akan menangani modal -->

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
				"type": "POST",
				"error": function(xhr, error, thrown) {
					console.log('DataTables Ajax Error:', xhr.responseText);
					console.log('Error:', error);
					console.log('Thrown:', thrown);
					console.log('URL:', url);
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

		// Bootbox akan menangani event modal

		// Event tombol tambah data
		$('.btn-add').on('click', function (e) {
			e.preventDefault();
			showForm('add');
		});

		// Event tombol detail
		$(document).on('click', '.btn-detail', function (e) {
			e.preventDefault();
			var id = $(this).data('id');
			showDetail(id);
		});

		// Event tombol edit
		$(document).on('click', '.btn-edit', function (e) {
			e.preventDefault();
			var id = $(this).data('id');
			showForm('edit', id);
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

		// Fungsi untuk menampilkan detail agen
		function showDetail(id) {
			var current_url = '<?= $config->baseURL ?>agent';
			var $bootbox = bootbox.dialog({
				title: 'Detail Agen',
				message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
				buttons: {
					success: {
						label: 'Tutup',
						className: 'btn-secondary'
					}
				}
			});
			$bootbox.find('.modal-dialog').css('max-width', '800px');
			var $button = $bootbox.find('button').prop('disabled', true);
			
			var detailUrl = current_url + '/detail?id=' + id;
			
			$.ajax({
				url: detailUrl,
				type: 'GET',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				success: function(html){
					$button.prop('disabled', false);
					$bootbox.find('.modal-body').empty().append(html);
				},
				error: function(xhr, status, error) {
					$button.prop('disabled', false);
					console.log('Detail Error:', xhr.responseText);
					$bootbox.find('.modal-body').html('<div class="alert alert-danger">Error loading detail: ' + error + '</div>');
				}
			});
		}

		// Fungsi untuk menampilkan form tambah/edit di modal
		function showForm(type = 'add', id = '') {
			var current_url = '<?= $config->baseURL ?>agent';
			var $bootbox = bootbox.dialog({
				title: type == 'add' ? 'Tambah Agen' : 'Edit Agen',
				message: '<div class="text-center text-secondary"><div class="spinner-border"></div></div>',
				buttons: {
					cancel: {
						label: 'Batal'
					},
					success: {
						label: 'Simpan',
						className: 'btn-success submit',
						callback: function() {
							$bootbox.find('.alert').remove();
							var $button_submit = $bootbox.find('button.submit');
							var $button = $bootbox.find('button');
							$button_submit.prepend('<i class="fas fa-circle-notch fa-spin me-2 fa-lg"></i>');
							$button.prop('disabled', true);
							
							var form = $bootbox.find('form')[0];
							$.ajax({
								type: 'POST',
								url: current_url + '/store',
								data: new FormData(form),
								processData: false,
								contentType: false,
								dataType: 'json',
								success: function (data) {
									$bootbox.modal('hide');
									if (data.status == 'success') {
										const Toast = Swal.mixin({
											toast: true,
											position: 'top-end',
											showConfirmButton: false,
											timer: 2500,
											timerProgressBar: true,
											iconColor: 'white',
											customClass: {
												popup: 'bg-success text-light toast p-2'
											},
											didOpen: (toast) => {
												toast.addEventListener('mouseenter', Swal.stopTimer)
												toast.addEventListener('mouseleave', Swal.resumeTimer)
											}
										})
										Toast.fire({
											html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> ' + data.message + '</div>'
										})
										$('#table-result').DataTable().ajax.reload();
									} else {
										Swal.fire({
											icon: 'error',
											title: 'Error!',
											text: data.message
										});
									}
								},
								error: function (xhr) {
									Swal.fire({
										icon: 'error',
										title: 'Error!',
										text: 'Terjadi kesalahan saat memproses permintaan Anda.'
									});
									console.log(xhr.responseText);
								}
							})
							return false;
						}
					}
				}
			});
			$bootbox.find('.modal-dialog').css('max-width', '800px');
			var $button = $bootbox.find('button').prop('disabled', true);
			var $button_submit = $bootbox.find('button.submit');
			
			var formUrl = current_url + '/' + (type == 'add' ? 'add' : 'edit');
			if (id) {
				formUrl += '?id=' + id;
			}
			
			$.get(formUrl, function(html){
				$button.prop('disabled', false);
				$bootbox.find('.modal-body').empty().append(html);
			});
		}

		// Fungsi untuk menghapus data agen
		function deleteItem(id) {
			$.ajax({
				url: '<?= $config->baseURL ?>agent/delete',
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
				url: '<?= $config->baseURL ?>agent/toggleStatus',
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

		// Submit form ditangani oleh Bootbox
	});
</script>
