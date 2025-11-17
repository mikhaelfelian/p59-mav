<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: View for sales detail
 */
?>
<style>
.detail-header {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	padding: 1.5rem;
	border-radius: 8px 8px 0 0;
}

.detail-header h5 {
	margin: 0;
	font-weight: 600;
	font-size: 1.5rem;
}

.detail-header .invoice-badge {
	background: rgba(255, 255, 255, 0.2);
	padding: 0.5rem 1rem;
	border-radius: 20px;
	font-size: 0.9rem;
	margin-top: 0.5rem;
	display: inline-block;
}

.info-section {
	background: #ffffff;
	border-radius: 12px;
	padding: 1.5rem;
	border: 1px solid #e9ecef;
	box-shadow: 0 2px 4px rgba(0,0,0,0.05);
	transition: box-shadow 0.3s ease;
	height: 100%;
}

.info-section:hover {
	box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.info-section h6 {
	font-size: 0.75rem;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 1px;
	color: #6c757d;
	margin-bottom: 1.25rem;
	padding-bottom: 0.75rem;
	border-bottom: 2px solid #f8f9fa;
}

.info-section dl dt {
	font-size: 0.85rem;
	font-weight: 600;
	color: #495057;
}

.info-section dl dd {
	font-size: 0.95rem;
	color: #212529;
}

.info-section .badge {
	font-weight: 600;
	padding: 0.4rem 0.8rem;
	font-size: 0.85rem;
	border-radius: 6px;
}

.items-section {
	background: #ffffff;
	border-radius: 12px;
	padding: 1.5rem;
	border: 1px solid #e9ecef;
	box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.items-section h6 {
	font-size: 1rem;
	font-weight: 700;
	color: #2c3e50;
	margin-bottom: 1.25rem;
	padding-bottom: 0.75rem;
	border-bottom: 2px solid #f8f9fa;
}

.items-table {
	margin: 0;
}

.items-table thead {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
}

.items-table thead th {
	border: none;
	padding: 1rem;
	font-weight: 600;
	font-size: 0.875rem;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.items-table tbody td {
	padding: 1rem;
	vertical-align: middle;
	border-bottom: 1px solid #f8f9fa;
}

.items-table tbody tr:hover {
	background-color: #f8f9fa;
}

.items-table tbody tr:last-child td {
	border-bottom: none;
}

.serial-number {
	background: #e7f3ff;
	color: #0066cc;
	padding: 0.25rem 0.5rem;
	border-radius: 4px;
	font-size: 0.8rem;
	font-weight: 500;
	display: inline-block;
	margin: 0.15rem 0.15rem 0.15rem 0;
}

.summary-card {
	background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
	border-radius: 12px;
	padding: 1.5rem;
	color: white;
	box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
}

.summary-card .summary-label {
	font-size: 0.875rem;
	opacity: 0.95;
	font-weight: 500;
}

.summary-card .summary-value {
	font-size: 1.1rem;
	font-weight: 700;
}

.summary-card .grand-total {
	font-size: 1.5rem;
	padding-top: 1rem;
	border-top: 2px solid rgba(255,255,255,0.3);
	margin-top: 1rem;
}

.summary-card .grand-total-label {
	font-size: 1rem;
	opacity: 0.95;
}

.summary-card .grand-total-value {
	font-size: 1.75rem;
	font-weight: 800;
}

.currency {
	font-family: 'Courier New', monospace;
	font-weight: 700;
}

.btn-back {
	background: #6c757d;
	border: none;
	padding: 0.75rem 2rem;
	border-radius: 8px;
	font-weight: 600;
	transition: all 0.3s ease;
}

.btn-back:hover {
	background: #5a6268;
	transform: translateY(-2px);
	box-shadow: 0 4px 8px rgba(0,0,0,0.15);
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
	<div class="detail-header" style="color: #fff;">
		<!-- <h5 style="color: #fff;"><i class="fas fa-file-invoice me-2"></i><?= esc($title) ?></h5> -->
		<?php if (!empty($sale['invoice_no'])): ?>
			<span class="invoice-badge" style="color: #fff;"><i class="fas fa-hashtag me-1"></i><?= esc($sale['invoice_no']) ?></span>
		<?php endif; ?>
	</div>
	<div class="card-body p-4">
		<div class="row mb-4">
			<div class="col-md-6 mb-3">
				<div class="info-section">
					<h6><i class="fas fa-receipt me-2"></i> Informasi Transaksi</h6>
					<dl class="row mb-0">
						<dt class="col-sm-5 mb-3">Pelanggan:</dt>
						<dd class="col-sm-7 mb-3">
							<i class="fas fa-user-tie me-1 text-muted"></i><?= esc($sale['agent_name'] ?? '-') ?>
						</dd>
					</dl>
					<dl class="row mb-0">
						<dt class="col-sm-5 mb-3">Alamat:</dt>
						<dd class="col-sm-7 mb-3">
							<i class="fas fa-address-card me-1 text-muted"></i><?= esc($sale['delivery_address'] ?? '-') ?>
						</dd>
					</dl>
				</div>
			</div>
			<div class="col-md-6 mb-3">
				<div class="info-section">
					<h6><i class="fas fa-info-circle me-2"></i> Informasi Lainnya</h6>
					<dl class="row mb-0">
						<dt class="col-sm-5 mb-3">Dibuat pada:</dt>
						<dd class="col-sm-7 mb-3">
							<i class="fas fa-calendar me-1 text-muted"></i><?= date('d/m/Y H:i', strtotime($sale['created_at'] ?? '')) ?>
						</dd>
						
						<dt class="col-sm-5 mb-3">Status:</dt>
						<dd class="col-sm-7 mb-3">
							<span class="badge bg-success"><?= esc($sale['status'] ?? 'Aktif') ?></span>
						</dd>
						
						<?php if (!empty($sale['customer_phone'])): ?>
							<dt class="col-sm-5 mb-3">Telepon:</dt>
							<dd class="col-sm-7 mb-3">
								<a href="tel:<?= esc($sale['customer_phone']) ?>" class="text-decoration-none">
									<i class="fas fa-phone me-1 text-primary"></i><?= esc($sale['customer_phone']) ?>
								</a>
							</dd>
						<?php endif; ?>
					</dl>
				</div>
			</div>
		</div>

		<div class="items-section mb-4">
			<h6><i class="fas fa-clipboard-check me-2"></i> SN Menunggu Penerimaan</h6>
			<div class="table-responsive">
				<table class="table items-table" id="receivableSnTable">
					<thead>
						<tr>
							<th style="width: 60px;">No</th>
							<th style="width: 150px;">SN</th>
							<th style="width: 150px;">SKU</th>
							<th>Item</th>
							<th style="width: 120px;" class="text-center">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($receivableSn)): ?>
							<?php foreach ($receivableSn as $index => $sn): ?>
								<tr data-sn-row="<?= $sn['id'] ?>">
									<td class="text-center"><?= $index + 1 ?></td>
									<td><?= esc($sn['sn'] ?? $sn['original_sn'] ?? '-') ?></td>
									<td><?= esc($sn['sku'] ?? '-') ?></td>
									<td><?= esc($sn['item_name'] ?? '-') ?></td>
									<td class="text-center">
										<button type="button" class="btn btn-sm btn-success btn-receive-sn" data-sn-id="<?= $sn['id'] ?>">
											<i class="fas fa-check"></i> Terima
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="5" class="text-center py-4 text-muted">
									<i class="fas fa-check-circle me-2"></i>Tidak ada SN menunggu penerimaan.
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="row mt-4">
			<div class="col-md-12 d-flex gap-3">
				<a href="<?= $config->baseURL ?>agent/gudang/stok-masuk" class="btn btn-back text-white">
					<i class="fas fa-arrow-left"></i>Kembali
				</a>
				<a href="<?= $config->baseURL ?>sales/print_dm/<?= $sale['id'] ?? '' ?>" target="_blank" class="btn btn-primary text-white">
					<i class="fas fa-print"></i>Print
				</a>
			</div>
		</div>
		
		<script>
		var saleId = <?= $sale['id'] ?? 0 ?>;
		var feeTypes = <?= json_encode($feeTypes ?? []) ?>;
		var isAgent = <?= !empty($isAgent) && $isAgent ? 'true' : 'false' ?>;

		$(function() {
			$(document).on('click', '.btn-receive-sn', function () {
				var button = $(this);
				var snId = button.data('sn-id');
				if (!snId) {
					return;
				}

				var originalHtml = button.html();
				button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

				$.ajax({
					url: '<?= $config->baseURL ?>agent/gudang/stok-terima',
					type: 'POST',
					data: {
						sn_id: snId,
						<?= csrf_token() ?>: '<?= csrf_hash() ?>'
					},
					headers: {
						'X-Requested-With': 'XMLHttpRequest'
					},
					dataType: 'json',
					success: function(response) {
						if (response.status === 'success') {
							var $row = $('tr[data-sn-row="' + snId + '"]');
							$row.fadeOut(200, function() {
								$(this).remove();
								if ($('#receivableSnTable tbody tr').length === 0) {
									$('#receivableSnTable tbody').append(
										'<tr><td colspan="5" class="text-center py-4 text-muted">' +
										'<i class="fas fa-check-circle me-2"></i>Tidak ada SN menunggu penerimaan.' +
										'</td></tr>'
									);
								}
							});

							if (typeof Swal !== 'undefined') {
								Swal.fire({
									icon: 'success',
									title: 'Berhasil',
									text: response.message || 'SN berhasil diterima.',
									timer: 1500,
									showConfirmButton: false
								});
							}
						} else {
							button.prop('disabled', false).html(originalHtml);
							var errorMsg = response.message || 'Gagal menerima SN.';
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
					},
					error: function(xhr) {
						button.prop('disabled', false).html(originalHtml);
						var errorMsg = 'Terjadi kesalahan saat memperbarui SN.';
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
			});
		});

		function copyToClipboard(text) {
			navigator.clipboard.writeText(text).then(function() {
				// Show success message
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'success',
						title: 'Berhasil',
						text: 'Kode pembayaran berhasil disalin!',
						timer: 2000,
						showConfirmButton: false,
						toast: true,
						position: 'top-end'
					});
				} else {
					alert('Kode pembayaran berhasil disalin!');
				}
			}, function(err) {
				// Fallback for older browsers
				var textArea = document.createElement("textarea");
				textArea.value = text;
				textArea.style.position = "fixed";
				textArea.style.left = "-999999px";
				document.body.appendChild(textArea);
				textArea.focus();
				textArea.select();
				try {
					document.execCommand('copy');
					alert('Kode pembayaran berhasil disalin!');
				} catch (err) {
					alert('Gagal menyalin kode pembayaran.');
				}
				document.body.removeChild(textArea);
			});
		}

		<?php if (!empty($isAgent) && $isAgent && !empty($feeTypes)): ?>
		// Fee Management JavaScript
		$(document).ready(function() {
			// Show add fee modal
			$('#btnAddFee').on('click', function() {
				showFeeModal();
			});

			// Edit fee
			$(document).on('click', '.edit-fee-btn', function() {
				var feeId = $(this).data('fee-id');
				var $row = $('tr[data-fee-id="' + feeId + '"]');
				var feeTypeName = $row.find('td:eq(1)').text().trim();
				var feeName = $row.find('td:eq(2)').text().trim();
				var amountText = $row.find('td:eq(3)').text().trim();
				var amount = parseFloat(amountText.replace(/[Rp\s.,]/g, '').replace(',', '.'));

				// Find fee type ID by name
				var feeTypeId = null;
				$.each(feeTypes, function(i, ft) {
					if (ft.name === feeTypeName || ft.code === feeTypeName) {
						feeTypeId = ft.id;
						return false;
					}
				});

				showFeeModal(feeId, feeTypeId, feeName, amount);
			});

			// Delete fee
			$(document).on('click', '.delete-fee-btn', function() {
				var feeId = $(this).data('fee-id');
				deleteFee(feeId);
			});

			// Save note
			$('#btnSaveNote').on('click', function() {
				saveNote();
			});
		});

		function showFeeModal(feeId = null, feeTypeId = null, feeName = '', amount = 0) {
			var title = feeId ? 'Edit Biaya Tambahan' : 'Tambah Biaya Tambahan';
			var feeTypeOptions = '<option value="">-- Pilih Jenis Biaya --</option>';
			$.each(feeTypes, function(i, ft) {
				var selected = (feeTypeId && ft.id == feeTypeId) ? 'selected' : '';
				feeTypeOptions += '<option value="' + ft.id + '" ' + selected + '>' + (ft.name || ft.code) + '</option>';
			});

			var modalHtml = '<form id="feeForm">' +
				'<div class="mb-3">' +
				'<label class="form-label">Jenis Biaya <span class="text-danger">*</span></label>' +
				'<select class="form-select" id="modalFeeTypeId" name="fee_type_id" required>' + feeTypeOptions + '</select>' +
				'</div>' +
				'<div class="mb-3">' +
				'<label class="form-label">Nama Biaya (Opsional)</label>' +
				'<input type="text" class="form-control" id="modalFeeName" name="fee_name" placeholder="Nama biaya (opsional)" value="' + (feeName || '') + '">' +
				'</div>' +
				'<div class="mb-3">' +
				'<label class="form-label">Jumlah <span class="text-danger">*</span></label>' +
				'<div class="input-group">' +
				'<span class="input-group-text">Rp</span>' +
				'<input type="number" class="form-control" id="modalFeeAmount" name="amount" min="0" step="0.01" value="' + (amount || 0) + '" required>' +
				'</div>' +
				'</div>' +
				'</form>';

			if (typeof bootbox !== 'undefined') {
				bootbox.dialog({
					title: title,
					message: modalHtml,
					size: 'medium',
					buttons: {
						cancel: {
							label: 'Batal',
							className: 'btn-secondary'
						},
						submit: {
							label: feeId ? 'Simpan' : 'Tambah',
							className: 'btn-primary',
							callback: function() {
								var form = $('#feeForm');
								if (form[0].checkValidity()) {
									if (feeId) {
										updateFee(feeId);
									} else {
										addFee();
									}
									return false; // Keep modal open if validation fails
								} else {
									form[0].reportValidity();
									return false;
								}
							}
						}
					}
				});
			} else {
				alert('Bootbox library tidak tersedia. Silakan refresh halaman.');
			}
		}

		function addFee() {
			var feeTypeId = $('#modalFeeTypeId').val();
			var feeName = $('#modalFeeName').val();
			var amount = $('#modalFeeAmount').val();

			if (!feeTypeId || !amount || parseFloat(amount) <= 0) {
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Mohon lengkapi semua field yang wajib diisi.'
					});
				} else {
					alert('Mohon lengkapi semua field yang wajib diisi.');
				}
				return;
			}

			$.ajax({
				url: '<?= $config->baseURL ?>agent/sales/addFee/' + saleId,
				type: 'POST',
				data: {
					fee_type_id: feeTypeId,
					fee_name: feeName,
					amount: amount,
					<?= csrf_token() ?>: '<?= csrf_hash() ?>'
				},
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				dataType: 'json',
				success: function(response) {
					if (response.status === 'success') {
						if (typeof bootbox !== 'undefined') {
							bootbox.hideAll();
						}
						if (typeof Swal !== 'undefined') {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: response.message,
								timer: 2000,
								showConfirmButton: false
							}).then(function() {
								location.reload();
							});
						} else {
							alert(response.message);
							location.reload();
						}
					} else {
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
					var errorMsg = 'Terjadi kesalahan saat menambahkan biaya tambahan.';
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

		function updateFee(feeId) {
			var feeTypeId = $('#modalFeeTypeId').val();
			var feeName = $('#modalFeeName').val();
			var amount = $('#modalFeeAmount').val();

			if (!feeTypeId || !amount || parseFloat(amount) <= 0) {
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Mohon lengkapi semua field yang wajib diisi.'
					});
				} else {
					alert('Mohon lengkapi semua field yang wajib diisi.');
				}
				return;
			}

			$.ajax({
				url: '<?= $config->baseURL ?>agent/sales/updateFee/' + saleId + '/' + feeId,
				type: 'POST',
				data: {
					fee_type_id: feeTypeId,
					fee_name: feeName,
					amount: amount,
					<?= csrf_token() ?>: '<?= csrf_hash() ?>'
				},
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				dataType: 'json',
				success: function(response) {
					if (response.status === 'success') {
						if (typeof bootbox !== 'undefined') {
							bootbox.hideAll();
						}
						if (typeof Swal !== 'undefined') {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: response.message,
								timer: 2000,
								showConfirmButton: false
							}).then(function() {
								location.reload();
							});
						} else {
							alert(response.message);
							location.reload();
						}
					} else {
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
					var errorMsg = 'Terjadi kesalahan saat mengubah biaya tambahan.';
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

		function deleteFee(feeId) {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					icon: 'question',
					title: 'Konfirmasi',
					text: 'Apakah Anda yakin ingin menghapus biaya tambahan ini?',
					showCancelButton: true,
					confirmButtonText: 'Ya, Hapus',
					cancelButtonText: 'Batal'
				}).then(function(result) {
					if (result.isConfirmed) {
						performDeleteFee(feeId);
					}
				});
			} else {
				if (confirm('Apakah Anda yakin ingin menghapus biaya tambahan ini?')) {
					performDeleteFee(feeId);
				}
			}
		}

		function performDeleteFee(feeId) {
			$.ajax({
				url: '<?= $config->baseURL ?>agent/sales/deleteFee/' + saleId + '/' + feeId,
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
								location.reload();
							});
						} else {
							alert(response.message);
							location.reload();
						}
					} else {
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
					var errorMsg = 'Terjadi kesalahan saat menghapus biaya tambahan.';
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

		function saveNote() {
			var note = $('#saleNote').val();

			$.ajax({
				url: '<?= $config->baseURL ?>agent/sales/updateNote/' + saleId,
				type: 'POST',
				data: {
					note: note,
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
								showConfirmButton: false,
								toast: true,
								position: 'top-end'
							});
						} else {
							alert(response.message);
						}
					} else {
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
					var errorMsg = 'Terjadi kesalahan saat menyimpan catatan.';
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
		<?php endif; ?>

		<?php if (!empty($isAdmin) && $isAdmin): ?>
		// Save Admin Note
		function saveAdminNote() {
			var adminNote = $('#adminNote').val().trim();

			$.ajax({
				url: '<?= $config->baseURL ?>agent/sales/updateAdminNote/<?= $sale['id'] ?>',
				type: 'POST',
				data: {
					<?= csrf_token() ?>: '<?= csrf_hash() ?>',
					admin_note: adminNote
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
								showConfirmButton: false,
								toast: true,
								position: 'top-end'
							});
						} else {
							alert(response.message);
						}
					} else {
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
					var errorMsg = 'Terjadi kesalahan saat menyimpan catatan admin.';
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

		$(document).ready(function() {
			$('#btnSaveAdminNote').on('click', function() {
				saveAdminNote();
			});
		});
		<?php endif; ?>

		// Refresh Payment Status
		function refreshPaymentStatus() {
			var btnRefresh = $('#btnRefreshPayment');
			var refreshIcon = $('#refreshIcon');
			var saleId = <?= $sale['id'] ?? 0 ?>;

			if (saleId === 0) {
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'ID penjualan tidak valid.'
					});
				} else {
					alert('ID penjualan tidak valid.');
				}
				return;
			}

			// Disable button and show loading state
			btnRefresh.prop('disabled', true);
			refreshIcon.addClass('fa-spin');

			$.ajax({
				url: '<?= $config->baseURL ?>agent/sales/bayar/cek/' + saleId,
				type: 'POST',
				data: {
					<?= csrf_token() ?>: '<?= csrf_hash() ?>'
				},
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				dataType: 'json',
				success: function(response) {
					if (response.status === 'success' && response.gatewayResponse) {
						var gatewayResponse = response.gatewayResponse;
						
						// Update payment status badge
						var status = (gatewayResponse.status || 'UNKNOWN').toUpperCase();
						var statusClass = 'secondary';
						if (status === 'PAID') statusClass = 'success';
						else if (status === 'PENDING') statusClass = 'warning';
						else if (['FAILED', 'CANCELED', 'EXPIRED'].includes(status)) statusClass = 'danger';
						
						$('#paymentStatusBadge')
							.removeClass('bg-secondary bg-success bg-warning bg-danger')
							.addClass('bg-' + statusClass)
							.text(status);

						// Update payment code if exists
						if (gatewayResponse.paymentCode) {
							var paymentCodeEl = $('#paymentCode');
							if (paymentCodeEl.length) {
								paymentCodeEl.text(gatewayResponse.paymentCode);
							}
						}

						// Update expired date if exists
						if (gatewayResponse.expiredAt) {
							var expiredAtEl = $('#paymentExpiredAt');
							if (expiredAtEl.length) {
								try {
									var expiredDate = new Date(gatewayResponse.expiredAt);
									var formattedDate = expiredDate.toLocaleDateString('id-ID', {
										day: '2-digit',
										month: '2-digit',
										year: 'numeric',
										hour: '2-digit',
										minute: '2-digit'
									});
									expiredAtEl.text(formattedDate);
								} catch (e) {
									expiredAtEl.text(gatewayResponse.expiredAt);
								}
							}
						}

						// Show success message
						if (typeof Swal !== 'undefined') {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: response.message || 'Status pembayaran berhasil diperbarui.',
								timer: 2000,
								showConfirmButton: false,
								toast: true,
								position: 'top-end'
							});
						} else {
							alert(response.message || 'Status pembayaran berhasil diperbarui.');
						}
					} else {
						if (typeof Swal !== 'undefined') {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: response.message || 'Gagal memperbarui status pembayaran.'
							});
						} else {
							alert(response.message || 'Gagal memperbarui status pembayaran.');
						}
					}
				},
				error: function(xhr) {
					var errorMsg = 'Terjadi kesalahan saat memperbarui status pembayaran.';
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
				},
				complete: function() {
					// Re-enable button and remove loading state
					btnRefresh.prop('disabled', false);
					refreshIcon.removeClass('fa-spin');
				}
			});
		}
		</script>
	</div>
</div>
