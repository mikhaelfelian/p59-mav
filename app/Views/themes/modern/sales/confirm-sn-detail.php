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

		<!-- Items with Pending Serial Numbers -->
		<div class="row">
			<div class="col-md-12">
				<h5 class="mb-3"><i class="fas fa-list me-2"></i>Item dengan Serial Number Pending</h5>
				
				<?php if (empty($items)): ?>
					<div class="alert alert-info">
						<i class="fas fa-info-circle me-2"></i>
						Tidak ada serial number yang perlu dikonfirmasi untuk penjualan ini.
					</div>
				<?php else: ?>
					<?php foreach ($items as $item): ?>
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
								<h6 class="mb-3">
									<span class="pending-badge">
										<i class="fas fa-clock me-1"></i>
										<?= count($item['pending_sns'] ?? []) ?> Serial Number Pending
									</span>
								</h6>
								
								<?php foreach ($item['pending_sns'] ?? [] as $sn): ?>
									<div class="sn-item">
										<div>
											<span class="sn-badge"><?= esc($sn['sn'] ?? '-') ?></span>
											<span class="sn-status ms-2 text-muted">
												<i class="fas fa-info-circle me-1"></i>
												Belum diaktifkan
											</span>
										</div>
										<div>
											<span class="badge bg-warning">Pending</span>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
					
					<!-- Confirm Button -->
					<div class="text-center mt-4">
						<form id="confirmSNForm" method="POST" action="<?= $config->baseURL ?>agent/sales-confirm/verify/<?= $sale['id'] ?? '' ?>">
							<?= csrf_field() ?>
							<button type="submit" class="btn confirm-btn" id="confirmBtn">
								<i class="fas fa-check-circle me-2"></i>
								Verifikasi & Aktifkan Serial Number
							</button>
						</form>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Action Buttons -->
		<div class="row mt-4">
			<div class="col-md-12 d-flex gap-3">
				<a href="<?= $config->baseURL ?>agent/sales-confirm" class="btn btn-secondary text-white">
					<i class="fas fa-arrow-left"></i>Kembali ke Daftar
				</a>
				<a href="<?= $config->baseURL ?>sales/print_dm/<?= $sale['id'] ?? '' ?>" target="_blank" class="btn btn-primary text-white">
					<i class="fas fa-print"></i>Print Nota
				</a>
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
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
							// Redirect to list
							window.location.href = '<?= $config->baseURL ?>agent/sales-confirm';
						});
					} else {
						alert(response.message || 'Serial number berhasil diaktifkan');
						window.location.href = '<?= $config->baseURL ?>agent/sales-confirm';
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

