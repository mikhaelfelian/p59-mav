<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-19
 * Github: github.com/mikhaelfelian
 * Description: View for paylater single payment form
 */
?>
<style>
.payment-form-section {
	background: #fff;
	border-radius: 8px;
	padding: 1.5rem;
	margin-bottom: 1.5rem;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	border: 1px solid #e9ecef;
}

.payment-form-section .section-title {
	font-size: 1.1rem;
	font-weight: 600;
	color: #2c3e50;
	margin-bottom: 1.25rem;
	padding-bottom: 0.75rem;
	border-bottom: 2px solid #f8f9fa;
	display: flex;
	align-items: center;
	gap: 0.5rem;
}

.summary-card {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	border-radius: 12px;
	color: #fff;
	padding: 1.5rem;
	box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.summary-card .summary-label {
	font-size: 0.875rem;
	opacity: 0.9;
	margin-bottom: 0.25rem;
}

.summary-card .summary-value {
	font-size: 1.5rem;
	font-weight: 700;
}

.info-badge {
	display: inline-block;
	background: rgba(102, 126, 234, 0.1);
	padding: 0.5rem 1rem;
	border-radius: 8px;
	margin-bottom: 1rem;
	font-size: 0.9rem;
	color: #667eea;
}
</style>

<?php
if (!empty($msg)) {
	show_alert($msg);
}
?>

<div class="row g-3">
	<div class="col-md-8">
		<div class="payment-form-section">
			<div class="section-title">
				<i class="fas fa-info-circle"></i>
				<span>Informasi Transaksi</span>
			</div>
			
			<div class="info-badge">
				<i class="fas fa-hashtag me-1"></i>
				<strong>Invoice:</strong> <?= esc($invoiceNo) ?>
			</div>
			
			<dl class="row mb-0">
				<dt class="col-sm-4 mb-3">Agen:</dt>
				<dd class="col-sm-8 mb-3">
					<strong><?= esc($agent->name ?? '-') ?></strong>
				</dd>
				
				<dt class="col-sm-4 mb-3">Jumlah Hutang:</dt>
				<dd class="col-sm-8 mb-3">
					<span class="text-danger fw-bold">Rp <?= number_format($amount, 0, ',', '.') ?></span>
				</dd>
				
				<?php if (!empty($transaction->description)): ?>
					<dt class="col-sm-4 mb-3">Deskripsi:</dt>
					<dd class="col-sm-8 mb-3">
						<?= esc($transaction->description) ?>
					</dd>
				<?php endif; ?>
			</dl>
		</div>

		<form id="form-payment" method="POST" action="<?= $config->baseURL ?>agent/paylater/pay/<?= $transaction->id ?>">
			<?= csrf_field() ?>
			
			<div class="payment-form-section">
				<div class="section-title">
					<i class="fas fa-credit-card"></i>
					<span>Pembayaran</span>
				</div>
				
				<div class="row g-3">
					<div class="col-12">
						<label class="form-label fw-semibold">Platform Pembayaran</label>
						<div class="input-group">
							<span class="input-group-text bg-light"><i class="fas fa-store"></i></span>
							<?php
							$platformOptions = ['' => '-- Pilih Platform --'];
							// Combine manual transfer and payment gateway platforms
							$allPlatformsForDropdown = array_merge($platformsManualTransfer ?? [], $platformsPaymentGateway ?? []);
							foreach ($allPlatformsForDropdown as $platform) {
								$platformId = is_object($platform) ? $platform->id : $platform['id'];
								$platformName = is_object($platform) ? $platform->platform : $platform['platform'];
								$platformOptions[$platformId] = esc($platformName);
							}
							echo form_dropdown('platform_id', $platformOptions, set_value('platform_id', ''), [
								'class' => 'form-control',
								'id' => 'platform_id',
								'required' => 'required'
							]);
							?>
						</div>
						<small class="text-muted"><i class="fas fa-info-circle"></i> Pilih platform pembayaran</small>
					</div>
				</div>
			</div>

			<div class="d-flex justify-content-between align-items-center mt-4">
				<a href="<?= $config->baseURL ?>agent/sales-paylater" class="btn btn-secondary">
					<i class="fas fa-arrow-left"></i> Kembali
				</a>
				<button type="submit" class="btn btn-success btn-lg">
					<i class="fas fa-money-bill-wave"></i> Bayar
				</button>
			</div>
		</form>
	</div>
	
	<div class="col-md-4">
		<div class="summary-card">
			<div class="summary-label">Total Pembayaran</div>
			<div class="summary-value">Rp <?= number_format($amount, 0, ',', '.') ?></div>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
	let isSubmitting = false;
	
	// Store platform data for API check
	let platformsData = <?= json_encode($platforms ?? []) ?>;
	
	// Form submission
	$('#form-payment').on('submit', function(e) {
		e.preventDefault();
		
		if (isSubmitting) {
			return false;
		}
		
		let platformId = $('#platform_id').val();
		if (!platformId) {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					icon: 'warning',
					title: 'Peringatan',
					text: 'Silakan pilih platform pembayaran.',
					confirmButtonColor: '#4e73df'
				});
			} else {
				alert('Silakan pilih platform pembayaran.');
			}
			return false;
		}
		
		// Show loading
		let form = $(this);
		let submitBtn = form.find('button[type="submit"]');
		let originalText = submitBtn.html();
		isSubmitting = true;
		submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
		
		// Submit form
		let formData = form.serialize();
		$.ajax({
			url: form.attr('action'),
			type: 'POST',
			data: formData,
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success') {
					// Check if gateway response contains QR code URL
					if (response.data && response.data.gateway && response.data.gateway.url) {
						let gateway = response.data.gateway;
						
						// Calculate totalReceive if gateway response exists (platform.gw_status = 1)
						let totalReceiveText = '';
						if (gateway.originalAmount !== undefined && gateway.chargeCustomerForPaymentGatewayFee !== undefined) {
							let totalReceive = 0;
							if (gateway.chargeCustomerForPaymentGatewayFee === true || gateway.chargeCustomerForPaymentGatewayFee === 'true') {
								// Customer is charged the fee, so totalReceive = originalAmount
								totalReceive = parseFloat(gateway.originalAmount) || 0;
							} else {
								// Customer is NOT charged the fee, so totalReceive = originalAmount - paymentGatewayAdminFee
								let originalAmount = parseFloat(gateway.originalAmount) || 0;
								let adminFee = parseFloat(gateway.paymentGatewayAdminFee) || 0;
								totalReceive = originalAmount - adminFee;
							}
							
							if (totalReceive > 0) {
								totalReceiveText = '<p class="text-muted small"><strong>Total Diterima: Rp ' + totalReceive.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></p>';
							}
						}
						
						Swal.fire({
							title: 'QR Code Pembayaran',
							html: `
								<div class="text-center">
									<p class="mb-3">Silakan scan QR code berikut untuk melakukan pembayaran:</p>
									<img src="${gateway.url}" alt="QR Code" class="img-fluid mb-3" style="max-width: 300px;">
									<p class="text-muted small mb-2">Status: <strong>${gateway.status}</strong></p>
									${gateway.paymentGatewayAdminFee > 0 ? 
										'<p class="text-muted small">Biaya Admin: Rp ' + gateway.paymentGatewayAdminFee.toLocaleString('id-ID') + '</p>' : ''}
									${totalReceiveText}
									<a href="${gateway.url}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
										<i class="fas fa-external-link-alt"></i> Buka QR Code
									</a>
								</div>
							`,
							icon: 'info',
							confirmButtonText: 'OK',
							confirmButtonColor: '#4e73df',
							width: '500px'
						}).then(() => {
							// Redirect to paylater detail page
							var redirectUrl = '<?= $config->baseURL ?>agent/paylater/<?= $transaction->id ?>';
							
							if (typeof Swal !== 'undefined') {
								Swal.fire({
									icon: 'success',
									title: 'Berhasil',
									text: response.message || 'Pembayaran berhasil diproses.',
									confirmButtonColor: '#28a745'
								}).then(() => {
									window.location.href = redirectUrl;
								});
							} else {
								alert(response.message || 'Pembayaran berhasil diproses.');
								window.location.href = redirectUrl;
							}
						});
					} else {
						// Redirect to paylater detail page if sale ID is available
						var redirectUrl = '<?= $config->baseURL ?>agent/paylater/<?= $transaction->id ?>';
						
						if (typeof Swal !== 'undefined') {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: response.message || 'Pembayaran berhasil diproses.',
								confirmButtonColor: '#28a745'
							}).then(() => {
								window.location.href = redirectUrl;
							});
						} else {
							alert(response.message || 'Pembayaran berhasil diproses.');
							window.location.href = redirectUrl;
						}
					}
				} else {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.message || 'Gagal memproses pembayaran.',
							confirmButtonColor: '#dc3545'
						});
					} else {
						alert(response.message || 'Gagal memproses pembayaran.');
					}
					isSubmitting = false;
					submitBtn.prop('disabled', false).html(originalText);
				}
			},
			error: function(xhr, status, error) {
				let errorMsg = 'Gagal memproses pembayaran.';
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMsg = xhr.responseJSON.message;
				} else if (xhr.responseText) {
					try {
						let error = JSON.parse(xhr.responseText);
						if (error.message) {
							errorMsg = error.message;
						}
					} catch(e) {
						// Use default error message
					}
				}
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMsg,
						confirmButtonColor: '#dc3545'
					});
				} else {
					alert(errorMsg);
				}
				isSubmitting = false;
				submitBtn.prop('disabled', false).html(originalText);
			}
		});
		
		return false;
	});
});
</script>

