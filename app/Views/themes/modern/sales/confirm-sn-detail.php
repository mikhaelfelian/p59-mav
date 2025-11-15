<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-10
 * Github: github.com/mikhaelfelian
 * Description: View for confirming and assigning serial numbers to agent orders
 */
helper('angka');
?>
<style>
.confirm-header {
	background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
	color: white;
	padding: 1.5rem;
	border-radius: 8px 8px 0 0;
}

.confirm-header h5 {
	margin: 0;
	font-weight: 600;
	font-size: 1.5rem;
}

.confirm-header .invoice-badge {
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
	margin-bottom: 1rem;
}

.sn-item {
	background: #f8f9fa;
	border: 1px solid #dee2e6;
	border-radius: 8px;
	padding: 0.75rem;
	margin-bottom: 0.5rem;
	display: flex;
	align-items: center;
	justify-content: space-between;
}

.sn-item:last-child {
	margin-bottom: 0;
}

.sn-badge {
	font-family: 'Courier New', monospace;
	font-size: 0.9rem;
	font-weight: 600;
	color: #495057;
}

.sn-status {
	font-size: 0.75rem;
}

.item-card {
	background: #ffffff;
	border: 1px solid #e9ecef;
	border-radius: 12px;
	padding: 1.5rem;
	margin-bottom: 1.5rem;
	box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.item-card:last-child {
	margin-bottom: 0;
}

.item-header {
	border-bottom: 2px solid #e9ecef;
	padding-bottom: 1rem;
	margin-bottom: 1rem;
}

.item-header h6 {
	margin: 0;
	font-weight: 600;
	color: #212529;
}

.item-header .item-meta {
	font-size: 0.875rem;
	color: #6c757d;
	margin-top: 0.5rem;
}

.pending-badge {
	background: #ffc107;
	color: #000;
	padding: 0.25rem 0.75rem;
	border-radius: 12px;
	font-size: 0.75rem;
	font-weight: 600;
}

.confirm-btn {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	border: none;
	color: white;
	padding: 0.75rem 2rem;
	border-radius: 8px;
	font-weight: 600;
	transition: transform 0.2s, box-shadow 0.2s;
}

.confirm-btn:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
	color: white;
}

.confirm-btn:disabled {
	opacity: 0.6;
	cursor: not-allowed;
	transform: none;
}

/* Ensure all action buttons have consistent white text
.btn-secondary.text-white,
.btn-primary.text-white {
	color: #ffffff !important;
}

.btn-secondary.text-white:hover,
.btn-primary.text-white:hover {
	color: #ffffff !important;
} */
</style>

<div class="card">
	<div class="confirm-header">
		<h5><i class="fas fa-check-circle me-2"></i>Verifikasi Order Agent</h5>
		<div class="invoice-badge">
			<i class="fas fa-file-invoice me-1"></i>
			<?= esc($sale['invoice_no'] ?? '-') ?>
		</div>
	</div>

	<div class="card-body">
		<?php
		if (!empty($msg)) {
			show_alert($msg);
		}
		?>

		<!-- Sale Information -->
		<div class="row mb-4">
			<div class="col-md-6">
				<div class="info-section">
					<h6><i class="fas fa-user me-2"></i>Informasi Pelanggan</h6>
					<dl class="row mb-0">
						<dt class="col-sm-5">Nama</dt>
						<dd class="col-sm-7 mb-2"><?= esc($sale['customer_name'] ?? '-') ?></dd>
						
						<?php if (!empty($sale['customer_phone'])): ?>
						<dt class="col-sm-5">Telepon</dt>
						<dd class="col-sm-7 mb-2"><?= esc($sale['customer_phone']) ?></dd>
						<?php endif; ?>
						
						<?php if (!empty($sale['customer_email'])): ?>
						<dt class="col-sm-5">Email</dt>
						<dd class="col-sm-7 mb-2"><?= esc($sale['customer_email']) ?></dd>
						<?php endif; ?>
					</dl>
				</div>
			</div>
			
			<div class="col-md-6">
				<div class="info-section">
					<h6><i class="fas fa-store me-2"></i>Informasi Penjualan</h6>
					<dl class="row mb-0">
						<dt class="col-sm-5">Agent</dt>
						<dd class="col-sm-7 mb-2"><?= esc($sale['agent_name'] ?? '-') ?></dd>
						
						<dt class="col-sm-5">Tanggal</dt>
						<dd class="col-sm-7 mb-2">
							<?= !empty($sale['created_at']) ? date('d/m/Y H:i', strtotime($sale['created_at'])) : '-' ?>
						</dd>
						
						<dt class="col-sm-5">Total</dt>
						<dd class="col-sm-7 mb-2">
							<strong><?= format_angka((float)($sale['grand_total'] ?? 0), 2) ?></strong>
						</dd>
					</dl>
				</div>
			</div>
		</div>

		<!-- Payment Information -->
		<?php if ($payment): ?>
		<div class="row mb-4">
			<div class="col-md-12">
				<div class="info-section">
					<h6><i class="fas fa-credit-card me-2"></i>Informasi Pembayaran</h6>
					<dl class="row mb-0">
						<dt class="col-sm-3">Status</dt>
						<dd class="col-sm-9 mb-2">
							<?php
							$paymentStatus = $payment['method'] ?? 'unknown';
							$statusBadge = [
								'qris' => '<span class="badge bg-info">QRIS</span>',
								'transfer' => '<span class="badge bg-primary">Transfer</span>',
								'cash' => '<span class="badge bg-success">Tunai</span>',
								'other' => '<span class="badge bg-secondary">Lainnya</span>'
							];
							echo $statusBadge[$paymentStatus] ?? '<span class="badge bg-secondary">' . esc($paymentStatus) . '</span>';
							?>
						</dd>
						
						<?php if ($gatewayResponse && !empty($gatewayResponse['status'])): ?>
						<dt class="col-sm-3">Gateway Status</dt>
						<dd class="col-sm-9 mb-2">
							<?php
							$gwStatus = strtoupper($gatewayResponse['status']);
							if ($gwStatus === 'PAID') {
								echo '<span class="badge bg-success">PAID</span>';
							} elseif (in_array($gwStatus, ['PENDING', 'WAITING'])) {
								echo '<span class="badge bg-warning">PENDING</span>';
							} else {
								echo '<span class="badge bg-danger">' . esc($gwStatus) . '</span>';
							}
							?>
						</dd>
						<?php endif; ?>
					</dl>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<!-- Items with Serial Numbers -->
		<div class="row">
			<div class="col-md-12">
				<h5 class="mb-3"><i class="fas fa-list me-2"></i>Item Penjualan</h5>
				
				<?php if (empty($items)): ?>
					<div class="alert alert-info">
						<i class="fas fa-info-circle me-2"></i>
						Tidak ada item dalam penjualan ini.
					</div>
				<?php else: ?>
					<?php 
					$hasPendingSNs = false;
					$hasUnreceivedSNs = false;
					$totalUnreceivedCount = 0;
					foreach ($items as $item): 
						if (!empty($item['pending_sns'])) {
							$hasPendingSNs = true;
							// Count unreceived SNs
							foreach ($item['pending_sns'] as $sn) {
								if (($sn['is_receive'] ?? '0') === '0') {
									$hasUnreceivedSNs = true;
									$totalUnreceivedCount++;
								}
							}
						}
					?>
						<div class="item-card">
							<div class="item-header">
								<h6><?= esc($item['item'] ?? 'Unknown Item') ?></h6>
								<div class="item-meta">
									<span class="me-3">Qty: <strong><?= (int)($item['qty'] ?? 1) ?></strong></span>
									<span class="me-3">Harga: <strong><?= format_angka((float)($item['price'] ?? 0), 2) ?></strong></span>
									<span>Subtotal: <strong><?= format_angka((float)($item['amount'] ?? 0), 2) ?></strong></span>
								</div>
							</div>
							
							<div class="mt-3">
								<!-- Assigned Serial Numbers -->
								<?php if (!empty($item['pending_sns'])): 
									// Separate received and unreceived SNs
									$receivedSns = [];
									$unreceivedSns = [];
									foreach ($item['pending_sns'] as $sn) {
										if (($sn['is_receive'] ?? '0') === '1') {
											$receivedSns[] = $sn;
										} else {
											$unreceivedSns[] = $sn;
										}
									}
									$totalAssigned = count($item['pending_sns']);
									$receivedCount = count($receivedSns);
									$unreceivedCount = count($unreceivedSns);
								?>
									<h6 class="mb-3">
										<span class="pending-badge">
											<i class="fas fa-list me-1"></i>
											<?= $totalAssigned ?> Serial Number Assigned
											<?php if ($receivedCount > 0): ?>
												<span class="text-success">(<?= $receivedCount ?> Diterima)</span>
											<?php endif; ?>
											<?php if ($unreceivedCount > 0): ?>
												<span class="text-warning">(<?= $unreceivedCount ?> Belum Diterima)</span>
											<?php endif; ?>
										</span>
									</h6>
									
									<!-- Unreceived Serial Numbers -->
									<?php if (!empty($unreceivedSns)): ?>
										<div class="mb-3">
											<small class="text-muted d-block mb-2">
												<i class="fas fa-clock me-1"></i>Belum Diterima:
											</small>
											<?php foreach ($unreceivedSns as $sn): ?>
												<div class="sn-item mb-2" data-sales-item-sn-id="<?= $sn['id'] ?? '' ?>">
													<div>
														<span class="sn-badge"><?= esc($sn['sn'] ?? '-') ?></span>
														<span class="sn-status ms-2 text-muted">
															<i class="fas fa-info-circle me-1"></i>
															Belum diterima
														</span>
													</div>
													<div>
														<button type="button" class="btn btn-sm btn-success receive-sn-btn" 
															data-sales-item-sn-id="<?= $sn['id'] ?? '' ?>" 
															data-sn="<?= esc($sn['sn'] ?? '-') ?>"
															title="Terima Serial Number">
															<i class="fas fa-check me-1"></i>Terima
														</button>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
									
									<!-- Received Serial Numbers -->
									<?php if (!empty($receivedSns)): ?>
										<div class="mb-3">
											<small class="text-muted d-block mb-2">
												<i class="fas fa-check-circle me-1 text-success"></i>Sudah Diterima:
											</small>
											<?php foreach ($receivedSns as $sn): ?>
												<div class="sn-item mb-2">
													<div>
														<span class="sn-badge"><?= esc($sn['sn'] ?? '-') ?></span>
														<span class="sn-status ms-2 text-success">
															<i class="fas fa-check-circle me-1"></i>
															Diterima
															<?php if (!empty($sn['receive_at'])): ?>
																- <?= date('d/m/Y H:i', strtotime($sn['receive_at'])) ?>
															<?php endif; ?>
														</span>
													</div>
													<div>
														<span class="badge bg-success">Diterima</span>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								<?php endif; ?>
								
								<!-- Assign Serial Numbers Section -->
								<?php 
								$hasSerialNumbers = !empty($item['has_serial_numbers']) || !empty($item['available_sns']);
								
								// Only show SN assignment section if item has serial numbers
								if ($hasSerialNumbers):
									$requiredQty = (int)($item['qty'] ?? 1);
									$assignedCount = count($item['pending_sns'] ?? []);
									$needsMore = $requiredQty > $assignedCount;
									
									// Collect assigned serial number IDs from pending_sns
									$assignedSnIds = [];
									if (!empty($item['pending_sns'])) {
										foreach ($item['pending_sns'] as $pendingSn) {
											// Handle both object and array
											$itemSnId = is_object($pendingSn) ? ($pendingSn->item_sn_id ?? null) : ($pendingSn['item_sn_id'] ?? null);
											if ($itemSnId) {
												$assignedSnIds[] = (int)$itemSnId;
											}
										}
									}
									
									// Filter available_sns to only show is_sell='0' serial numbers that are not already assigned
									$filteredAvailableSns = [];
									if (!empty($item['available_sns'])) {
										foreach ($item['available_sns'] as $sn) {
											// Handle both object and array
											$isSell = is_object($sn) ? ($sn->is_sell ?? '0') : ($sn['is_sell'] ?? '0');
											$snId = is_object($sn) ? ($sn->id ?? null) : ($sn['id'] ?? null);
											
											// Only include serial numbers where is_sell='0' and not already assigned
											if (($isSell === '0' || $isSell === 0) && $snId && !in_array((int)$snId, $assignedSnIds, true)) {
												$filteredAvailableSns[] = $sn;
											}
										}
									}
									$availableCount = count($filteredAvailableSns);
								?>
									
									<?php if ($needsMore): ?>
										<div class="mt-4 pt-3 border-top">
											<h6 class="mb-3">
												<i class="fas fa-plus-circle me-2 text-primary"></i>
												Assign Serial Number
												<small class="text-muted">(Perlu: <?= $requiredQty ?>, Sudah: <?= $assignedCount ?>, Tersedia: <?= $availableCount ?>)</small>
											</h6>
											
											<?php if ($availableCount > 0): ?>
												<form class="assign-sn-form" data-sales-item-id="<?= $item['id'] ?? '' ?>">
													<?= csrf_field() ?>
													<div class="mb-3">
														<label class="form-label">Pilih Serial Number:</label>
														<div class="mb-2">
															<input type="text" class="form-control form-control-sm sn-search-input" 
																id="sn-search-<?= $item['id'] ?? '' ?>" 
																placeholder="Cari serial number..." 
																autocomplete="off">
														</div>
														<select class="form-select form-select-sm sn-select" 
															id="sn-select-<?= $item['id'] ?? '' ?>" 
															name="item_sn_ids[]" 
															multiple 
															size="5" 
															required>
															<?php foreach ($filteredAvailableSns as $sn): 
																// Handle both object and array
																$snId = is_object($sn) ? ($sn->id ?? '') : ($sn['id'] ?? '');
																$snValue = is_object($sn) ? ($sn->sn ?? '-') : ($sn['sn'] ?? '-');
															?>
																<option value="<?= $snId ?>" data-sn="<?= esc($snValue) ?>"><?= esc($snValue) ?></option>
															<?php endforeach; ?>
														</select>
														<small class="text-muted">Gunakan Ctrl/Cmd untuk memilih multiple</small>
													</div>
													<button type="submit" class="btn btn-sm btn-primary text-white">
														<i class="fas fa-plus me-1"></i>Assign Serial Number
													</button>
												</form>
											<?php else: ?>
												<div class="alert alert-warning">
													<i class="fas fa-exclamation-triangle me-2"></i>
													Tidak ada serial number tersedia untuk item ini.
												</div>
											<?php endif; ?>
										</div>
									<?php elseif ($assignedCount >= $requiredQty): ?>
										<div class="alert alert-success mt-3">
											<i class="fas fa-check-circle me-2"></i>
											Serial number sudah lengkap untuk item ini.
										</div>
									<?php endif; ?>
								<?php else: ?>
									<!-- Item doesn't have serial numbers -->
									<div class="alert alert-info mt-3">
										<i class="fas fa-info-circle me-2"></i>
										Item ini tidak memerlukan serial number.
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>

		<!-- Action Buttons -->
		<div class="row mt-4">
			<div class="col-md-12 d-flex gap-3">
				<a href="<?= $config->baseURL ?>agent/sales" class="btn btn-secondary text-black">
					&laquo; Kembali
				</a>
				<?php if ($hasUnreceivedSNs && $totalUnreceivedCount > 0): ?>
					<button type="button" class="btn btn-success text-white" id="btnReceiveAll">
						<i class="fas fa-check-double me-1"></i>Terima Semua (<?= $totalUnreceivedCount ?> SN)
					</button>
				<?php endif; ?>
				<a href="<?= $config->baseURL ?>sales/print_dm/<?= $sale['id'] ?? '' ?>" target="_blank" class="btn btn-primary text-white">
					<i class="fas fa-print me-1"></i>Print Nota
				</a>
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
	// Filter serial number dropdown based on search input
	$(document).on('input', '.sn-search-input', function() {
		var searchTerm = $(this).val().toLowerCase();
		var searchId = $(this).attr('id');
		var selectId = searchId.replace('sn-search-', 'sn-select-');
		var $select = $('#' + selectId);
		
		if ($select.length === 0) {
			return;
		}
		
		// Store all options if not already stored
		if (!$select.data('all-options')) {
			var allOptions = [];
			$select.find('option').each(function() {
				allOptions.push({
					value: $(this).val(),
					text: $(this).text(),
					dataSn: $(this).data('sn') || $(this).text(),
					selected: $(this).prop('selected')
				});
			});
			$select.data('all-options', allOptions);
		}
		
		var allOptions = $select.data('all-options');
		var selectedValues = [];
		
		// Get currently selected values to preserve selection
		$select.find('option:selected').each(function() {
			selectedValues.push($(this).val());
		});
		
		// Clear and repopulate select with filtered options
		$select.empty();
		
		$.each(allOptions, function(index, option) {
			var snValue = option.dataSn.toLowerCase();
			if (searchTerm === '' || snValue.indexOf(searchTerm) !== -1) {
				var $newOption = $('<option></option>')
					.attr('value', option.value)
					.attr('data-sn', option.dataSn)
					.text(option.text);
				
				// Restore selection if it was previously selected
				if (selectedValues.indexOf(option.value) !== -1) {
					$newOption.prop('selected', true);
				}
				
				$select.append($newOption);
			}
		});
	});
	
	// Handle assign SN form submission
	$('.assign-sn-form').on('submit', function(e) {
		e.preventDefault();
		
		var $form = $(this);
		var $btn = $form.find('button[type="submit"]');
		var originalText = $btn.html();
		var salesItemId = $form.data('sales-item-id');
		var saleId = <?= $sale['id'] ?? 0 ?>;
		
		// Disable button and show loading
		$btn.prop('disabled', true);
		$btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Memproses...');
		
		// Get selected SN IDs
		var selectedSNs = $form.find('select[name="item_sn_ids[]"]').val();
		if (!selectedSNs || selectedSNs.length === 0) {
			alert('Pilih minimal satu serial number');
			$btn.prop('disabled', false);
			$btn.html(originalText);
			return;
		}
		
		// Prepare form data
		// Filter out item_sn_ids[] from serialized data to avoid duplication
		var formData = $form.serializeArray().filter(function(field) {
			return field.name !== 'item_sn_ids[]';
		});
		formData.push({name: 'sales_item_id', value: salesItemId});
		// Add each selected SN ID (only once)
		$.each(selectedSNs, function(i, snId) {
			formData.push({name: 'item_sn_ids[]', value: snId});
		});
		
		// Convert to object for jQuery
		var dataObj = {};
		$.each(formData, function(i, field) {
			if (field.name.endsWith('[]')) {
				if (!dataObj[field.name]) {
					dataObj[field.name] = [];
				}
				dataObj[field.name].push(field.value);
			} else {
				dataObj[field.name] = field.value;
			}
		});
		
		// Submit via AJAX
		$.ajax({
			url: '<?= $config->baseURL ?>agent/sales/confirm/assignSN/' + saleId,
			type: 'POST',
			data: dataObj,
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
							text: response.message || 'Serial number berhasil di-assign',
							confirmButtonText: 'OK'
						}).then(function() {
							// Redirect to /agent/sales after assign
							window.location.href = '<?= $config->baseURL ?>agent/sales';
						});
					} else {
						alert(response.message || 'Serial number berhasil di-assign');
						window.location.href = '<?= $config->baseURL ?>agent/sales';
					}
				} else {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.message || 'Gagal meng-assign serial number'
						});
					} else {
						alert(response.message || 'Gagal meng-assign serial number');
					}
					$btn.prop('disabled', false);
					$btn.html(originalText);
				}
			},
			error: function(xhr, status, error) {
				var errorMsg = 'Terjadi kesalahan saat memproses permintaan';
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
				
				$btn.prop('disabled', false);
				$btn.html(originalText);
			}
		});
	});

	// Receive SN functionality
	var saleId = <?= $sale['id'] ?? 0 ?>;

	// Individual receive button
	$(document).on('click', '.receive-sn-btn', function() {
		var $btn = $(this);
		var salesItemSnId = $btn.data('sales-item-sn-id');
		var snValue = $btn.data('sn');
		var originalText = $btn.html();

		if (!salesItemSnId) {
			alert('ID serial number tidak valid.');
			return;
		}

		// Disable button and show loading
		$btn.prop('disabled', true);
		$btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Memproses...');

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
							text: response.message || 'Serial number berhasil diterima',
							timer: 2000,
							showConfirmButton: false,
							toast: true,
							position: 'top-end'
						}).then(function() {
							location.reload();
						});
					} else {
						alert(response.message || 'Serial number berhasil diterima');
						location.reload();
					}
				} else {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.message || 'Gagal menerima serial number'
						});
					} else {
						alert(response.message || 'Gagal menerima serial number');
					}
					$btn.prop('disabled', false);
					$btn.html(originalText);
				}
			},
			error: function(xhr) {
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
				$btn.prop('disabled', false);
				$btn.html(originalText);
			}
		});
	});

	// Receive All button
	$('#btnReceiveAll').on('click', function() {
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
					performReceiveAll($btn, originalText);
				}
			});
		} else {
			if (confirm('Apakah Anda yakin ingin menerima semua ' + unreceivedCount + ' serial number?')) {
				performReceiveAll($btn, originalText);
			}
		}
	});

	function performReceiveAll($btn, originalText) {
		// Disable button and show loading
		$btn.prop('disabled', true);
		$btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Memproses...');

		$.ajax({
			url: '<?= $config->baseURL ?>agent/sales/confirm/receiveAllSN/' + saleId,
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
							location.reload();
						});
					} else {
						alert(response.message || 'Serial number berhasil diterima');
						location.reload();
					}
				} else {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.message || 'Gagal menerima serial number'
						});
					} else {
						alert(response.message || 'Gagal menerima serial number');
					}
					$btn.prop('disabled', false);
					$btn.html(originalText);
				}
			},
			error: function(xhr) {
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
				$btn.prop('disabled', false);
				$btn.html(originalText);
			}
		});
	}
	
	$('#confirmSNForm').on('submit', function(e) {
		e.preventDefault();
		
		var $btn = $('#confirmBtn');
		var originalText = $btn.html();
		
		// Disable button and show loading
		$btn.prop('disabled', true);
		$btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');
		
		// Submit form via AJAX
		$.ajax({
			url: $(this).attr('action'),
			type: 'POST',
			data: $(this).serialize(),
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success') {
					// Show success message
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message || 'Serial number berhasil diaktifkan',
							confirmButtonText: 'OK'
						}).then(function() {
							// Redirect to /agent/sales instead of confirm page
							window.location.href = '<?= $config->baseURL ?>agent/sales';
						});
					} else {
						alert(response.message || 'Serial number berhasil diaktifkan');
						window.location.href = '<?= $config->baseURL ?>agent/sales';
					}
				} else {
					// Show error message
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.message || 'Gagal mengaktifkan serial number'
						});
					} else {
						alert(response.message || 'Gagal mengaktifkan serial number');
					}
					
					// Re-enable button
					$btn.prop('disabled', false);
					$btn.html(originalText);
				}
			},
			error: function(xhr, status, error) {
				var errorMsg = 'Terjadi kesalahan saat memproses permintaan';
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
				
				// Re-enable button
				$btn.prop('disabled', false);
				$btn.html(originalText);
			}
		});
	});
});
</script>

