<?php
/**
 * Agent Sales Form View (Cart & Checkout)
 * CodeIgniter 4.3.1
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-04
 * Description: Simplified sales form for agents - cart and checkout only
 */
?>
<style>
.sales-form-section {
	background: #fff;
	border-radius: 8px;
	padding: 1.5rem;
	margin-bottom: 1.5rem;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	border: 1px solid #e9ecef;
}

.sales-form-section .section-title {
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

.sales-form-section .section-title i {
	color: #6c757d;
}

.form-label {
	font-weight: 500;
	color: #495057;
	margin-bottom: 0.5rem;
	font-size: 0.875rem;
}

.form-control:focus {
	border-color: #4e73df;
	box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

#cart-items-table {
	font-size: 0.9rem;
}

#cart-items-table thead {
	background-color: #f8f9fa;
}

#cart-items-table thead th {
	font-weight: 600;
	color: #495057;
	border-bottom: 2px solid #dee2e6;
	padding: 0.75rem;
}

#cart-items-table tbody td {
	padding: 0.75rem;
	vertical-align: middle;
}

#cart-items-table tbody tr:hover {
	background-color: #f8f9fa;
}

.item-subtotal {
	font-weight: 600;
	color: #28a745;
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

.summary-card .grand-total {
	font-size: 2rem;
	padding-top: 1rem;
	border-top: 2px solid rgba(255,255,255,0.3);
	margin-top: 1rem;
}

.summary-input-group {
	background: rgba(255,255,255,0.15);
	border-radius: 6px;
	padding: 0.75rem;
	margin-bottom: 0.75rem;
}

.action-buttons {
	background: #f8f9fa;
	border-radius: 8px;
	padding: 1.25rem;
	border-top: 1px solid #dee2e6;
}

.btn-action {
	min-width: 120px;
	font-weight: 600;
	padding: 0.75rem 1.5rem;
	border-radius: 6px;
	transition: all 0.3s ease;
}

.btn-action:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.empty-state {
	padding: 3rem 1rem;
	text-align: center;
	color: #6c757d;
}

.empty-state i {
	font-size: 3rem;
	margin-bottom: 1rem;
	opacity: 0.5;
}

.cart-item-image {
	width: 60px;
	height: 60px;
	object-fit: cover;
	border-radius: 4px;
}

.quantity-input {
	width: 80px;
	text-align: center;
}

.btn-quantity {
	width: 35px;
	height: 35px;
	padding: 0;
	display: flex;
	align-items: center;
	justify-content: center;
}
</style>

<div class="container-fluid px-0">
	<?php
	if (!empty($message)) {
		if (is_array($message)) {
			show_message($message);
		} else {
			echo '<div class="alert alert-info alert-dismissible fade show" role="alert">' . esc($message) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
		}
	}
	?>

	<form method="post" action="<?= $config->baseURL ?>agent/sales/store" id="form-sales" novalidate>
		<?= form_hidden('sales_channel', '1') ?>
		
		<!-- Sale Information Section -->
		<div class="sales-form-section">
			<div class="section-title">
				<i class="fas fa-receipt"></i>
				<span>Informasi Penjualan</span>
			</div>
			
			<div class="alert-validation" id="validation-alert"></div>
			
			<div class="row g-3">
				<div class="col-md-3">
					<label class="form-label">Nomor Invoice <span class="text-danger">*</span></label>
					<div class="input-group">
						<span class="input-group-text bg-light"><i class="fas fa-file-invoice"></i></span>
						<?= form_input([
							'name' => 'invoice_no',
							'class' => 'form-control',
							'value' => set_value('invoice_no', @$invoice_no ?? ''),
							'readonly' => 'readonly',
							'id' => 'invoice_no'
						]) ?>
					</div>
					<small class="text-muted"><i class="fas fa-info-circle"></i> Dibuat otomatis oleh sistem</small>
				</div>
				
				<div class="col-md-3">
					<label class="form-label">Agen <span class="text-danger">*</span></label>
					<div class="input-group">
						<span class="input-group-text bg-light"><i class="fas fa-user-tie"></i></span>
						<?php
						$agentOptions = ['' => '-- Pilih Agen --'];
						foreach ($agents ?? [] as $agent) {
							$agentId = is_object($agent) ? $agent->id : $agent['id'];
							$agentName = is_object($agent) ? $agent->name : $agent['name'];
							$agentOptions[$agentId] = esc($agentName);
						}
						$selectedAgent = set_value('agent_id', @$agentId ?? '');
						echo form_dropdown('agent_id', $agentOptions, $selectedAgent, [
							'class' => 'form-control',
							'id' => 'agent_id',
							'required' => 'required'
						]);
						?>
					</div>
				</div>
				
				<div class="col-md-3">
					<label class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
					<div class="input-group">
						<span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
						<?= form_input([
							'name' => 'customer_name',
							'type' => 'text',
							'class' => 'form-control',
							'id' => 'customer_name',
							'value' => set_value('customer_name', ''),
							'placeholder' => 'Masukkan nama pelanggan',
							'required' => 'required'
						]) ?>
					</div>
					<small class="text-muted"><i class="fas fa-info-circle"></i> Customer akan dibuat otomatis saat menyimpan</small>
					<input type="hidden" name="customer_id" id="customer_id" value="">
				</div>
				
				<div class="col-md-3">
					<label class="form-label d-block">Plat Kendaraan <span class="text-danger">*</span></label>
					<div class="row g-2 align-items-center">
						<div class="col-4">
							<div class="input-group">
								<span class="input-group-text bg-light"><i class="fas fa-car"></i></span>
								<?= form_input([
									'name' => 'plate_code',
									'type' => 'text',
									'class' => 'form-control rounded-0 text-uppercase',
									'id' => 'plate_code',
									'value' => set_value('plate_code', ''),
									'placeholder' => 'B',
									'maxlength' => 2,
									'required' => 'required',
									'style' => 'text-transform:uppercase'
								]) ?>
							</div>
						</div>
						<div class="col-4">
							<div class="input-group">
								<?= form_input([
									'name' => 'plate_number',
									'type' => 'text',
									'class' => 'form-control rounded-0',
									'id' => 'plate_number',
									'value' => set_value('plate_number', ''),
									'placeholder' => '4575',
									'maxlength' => 5,
									'required' => 'required'
								]) ?>
							</div>
						</div>
						<div class="col-4">
							<div class="input-group">
								<?= form_input([
									'name' => 'plate_suffix',
									'type' => 'text',
									'class' => 'form-control rounded-0 text-uppercase',
									'id' => 'plate_suffix',
									'value' => set_value('plate_suffix', ''),
									'placeholder' => 'PBP',
									'maxlength' => 4,
									'style' => 'text-transform:uppercase'
								]) ?>
							</div>
						</div>
					</div>
					<div class="text-muted mt-1">
						<small>
							<i class="fas fa-info-circle"></i> Format: <strong>kode</strong> - <strong>nomor</strong> - <strong>kode akhir</strong> (Contoh: H-4575-PBP)
						</small>
					</div>
				</div>
			</div>
		</div>

		<!-- Cart Items Section -->
		<div class="sales-form-section">
			<div class="section-title">
				<i class="fas fa-shopping-cart"></i>
				<span>Keranjang Belanja <span class="badge bg-primary ms-2" id="cart-count"><?= count($cart ?? []) ?></span></span>
			</div>
			
			<div id="cart-items">
				<?php if (!empty($cart) && is_array($cart)): ?>
					<div class="table-responsive">
						<table class="table table-hover align-middle" id="cart-items-table">
							<thead>
								<tr>
									<th width="5%">#</th>
									<th width="10%">Gambar</th>
									<th width="30%">Produk</th>
									<th width="12%">Harga</th>
									<th width="12%">Jumlah</th>
									<th width="15%">Subtotal</th>
									<th width="16%">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php $no = 1; foreach ($cart as $index => $item): ?>
									<tr data-item-id="<?= $item['item_id'] ?>" data-price="<?= $item['price'] ?? 0 ?>">
										<td><?= $no++ ?></td>
										<td>
											<?php if (!empty($item['image'])): ?>
												<img src="<?= $config->baseURL ?>public/images/item/<?= esc($item['image']) ?>" 
													alt="<?= esc($item['item_name']) ?>" 
													class="cart-item-image">
											<?php else: ?>
												<div class="cart-item-image bg-light d-flex align-items-center justify-content-center">
													<i class="fas fa-image text-muted"></i>
												</div>
											<?php endif; ?>
										</td>
										<td>
											<strong><?= esc($item['item_name']) ?></strong>
										</td>
										<td>
											<?= format_angka_rp($item['price'] ?? 0) ?>
										</td>
										<td>
											<div class="input-group input-group-sm">
												<button type="button" class="btn btn-outline-secondary btn-quantity" onclick="updateCartQty(<?= $item['item_id'] ?>, <?= ($item['qty'] ?? 1) - 1 ?>)">
													<i class="fas fa-minus"></i>
												</button>
												<input type="number" 
													class="form-control quantity-input" 
													value="<?= $item['qty'] ?? 1 ?>" 
													min="1" 
													id="qty-<?= $item['item_id'] ?>"
													onchange="updateCartQty(<?= $item['item_id'] ?>, this.value)">
												<button type="button" class="btn btn-outline-secondary btn-quantity" onclick="updateCartQty(<?= $item['item_id'] ?>, <?= ($item['qty'] ?? 1) + 1 ?>)">
													<i class="fas fa-plus"></i>
												</button>
											</div>
										</td>
										<td class="item-subtotal" id="subtotal-<?= $item['item_id'] ?>">
											<?= format_angka_rp($item['subtotal'] ?? 0) ?>
										</td>
										<td>
											<button type="button" class="btn btn-sm btn-danger" onclick="removeFromCart(<?= $item['item_id'] ?>)">
												<i class="fas fa-trash"></i> Hapus
											</button>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<div class="empty-state">
						<i class="fas fa-shopping-cart"></i>
						<div>Keranjang kosong. <a href="<?= $config->baseURL ?>agent/item">Klik di sini untuk menambahkan produk</a></div>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Summary & Payment Section -->
		<div class="row g-3">
			<div class="col-md-8">
				<div class="sales-form-section">
					<div class="section-title">
						<i class="fas fa-calculator"></i>
						<span>Ringkasan Pembayaran</span>
					</div>
					
					<div class="row g-3">
						<div class="col-md-4">
							<label class="form-label">Platform Pembayaran</label>
							<div class="input-group">
								<span class="input-group-text bg-light"><i class="fas fa-store"></i></span>
								<?php
								$platformOptions = ['' => '-- Pilih Platform --'];
								foreach ($platforms ?? [] as $platform) {
									$platformId = is_object($platform) ? $platform->id : $platform['id'];
									$platformName = is_object($platform) ? $platform->platform : $platform['platform'];
									$platformOptions[$platformId] = esc($platformName);
								}
								echo form_dropdown('platform_id', $platformOptions, set_value('platform_id', ''), [
									'class' => 'form-control',
									'id' => 'platform_id'
								]);
								?>
							</div>
							<small class="text-muted"><i class="fas fa-info-circle"></i> Opsional</small>
						</div>
						<div class="col-md-4">
							<label class="form-label">Jumlah Diskon</label>
							<div class="input-group">
								<span class="input-group-text bg-light">Rp</span>
								<?= form_input([
									'name' => 'discount',
									'type' => 'number',
									'class' => 'form-control',
									'value' => set_value('discount', '0'),
									'step' => '0.01',
									'min' => '0',
									'id' => 'discount-input',
									'placeholder' => '0.00'
								]) ?>
							</div>
						</div>
						<div class="col-md-4">
							<label class="form-label">Jumlah Pajak</label>
							<div class="input-group">
								<span class="input-group-text bg-light">Rp</span>
								<?= form_input([
									'name' => 'tax',
									'type' => 'number',
									'class' => 'form-control',
									'value' => set_value('tax', '0'),
									'step' => '0.01',
									'min' => '0',
									'id' => 'tax-input',
									'placeholder' => '0.00'
								]) ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-4">
				<div class="summary-card">
					<div class="summary-label">Subtotal</div>
					<div class="summary-value" id="subtotal-display">Rp 0.00</div>
					<?= form_input([
						'type' => 'hidden',
						'name' => 'subtotal',
						'id' => 'subtotal-input',
						'value' => '0'
					]) ?>
					
					<div class="summary-input-group mt-3">
						<label class="form-label text-white mb-2">Diskon</label>
						<div class="text-white" id="discount-display">Rp 0.00</div>
					</div>
					
					<div class="summary-input-group">
						<label class="form-label text-white mb-2">Pajak</label>
						<div class="text-white" id="tax-display">Rp 0.00</div>
					</div>
					
					<div class="grand-total">
						<div class="summary-label">Grand Total</div>
						<div class="summary-value" id="grand-total-display">Rp 0.00</div>
						<?= form_input([
							'type' => 'hidden',
							'name' => 'grand_total',
							'id' => 'grand-total-input',
							'value' => '0'
						]) ?>
					</div>
				</div>
			</div>
		</div>

		<!-- Action Buttons -->
		<div class="action-buttons mt-4">
			<div class="d-flex justify-content-between align-items-center">
				<a href="<?= $config->baseURL ?>agent/item" class="btn btn-secondary btn-action">
					<i class="fas fa-arrow-left"></i> Kembali ke Produk
				</a>
				<div class="d-flex gap-2">
					<a href="<?= $config->baseURL ?>agent/sales/clearCart" class="btn btn-warning btn-action" onclick="return confirm('Yakin ingin mengosongkan keranjang?')">
						<i class="fas fa-trash"></i> Kosongkan
					</a>
					<button type="submit" class="btn btn-success btn-action" id="btn-pay" <?= empty($cart) ? 'disabled' : '' ?>>
						<i class="fas fa-check"></i> Simpan Transaksi
					</button>
				</div>
			</div>
		</div>
	</form>
</div>

<script>
$(document).ready(function() {
	let isSubmitting = false;
	
	// Store platform data for API check
	let platformsData = <?= json_encode($platforms ?? []) ?>;
	
	// Calculate initial totals
	calculateTotals();
	
	// Update totals when discount or tax changes
	$('#discount-input, #tax-input').on('input', function() {
		calculateTotals();
	});
	
	// Form submission
	$('#form-sales').on('submit', function(e) {
		e.preventDefault();
		
		if (isSubmitting) return;
		
		if ($('#cart-items-table tbody tr').length === 0 || $('#cart-items-table tbody tr').hasClass('empty-state')) {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					icon: 'warning',
					title: 'Keranjang Kosong',
					text: 'Silakan tambahkan item terlebih dahulu.'
				});
			} else {
				alert('Keranjang kosong. Silakan tambahkan item terlebih dahulu.');
			}
			return false;
		}
		
		var form = $(this);
		var submitBtn = form.find('#btn-pay');
		var originalText = submitBtn.html();
		isSubmitting = true;
		submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan...');
		
		// Ensure discount and tax have values
		$('#discount-input').val($('#discount-input').val() || '0');
		$('#tax-input').val($('#tax-input').val() || '0');
		
		// Check if platform is selected and has status_pos=1 and gw_status=1
		let platformId = $('#platform_id').val();
		let selectedPlatform = null;
		if (platformId && platformsData) {
			selectedPlatform = platformsData.find(function(p) {
				return p.id == platformId;
			});
		}
		
		// If platform has status_pos=1 and gw_status=1, call external API
		if (selectedPlatform && selectedPlatform.status_pos == '1' && selectedPlatform.gw_status == '1') {
			// Prepare API request data
			let invoiceNo = $('#invoice_no').val();
			let grandTotalInput = $('#grand-total-input').val() || '0';
			let grandTotal = parseFloat(String(grandTotalInput).replace(/[^\d.-]/g, '')) || 0;
			let customerName = $('#customer_name').val() || '';
			
			// Get customer data
			let customerPhone = '';
			let customerEmail = '';
			
			// Split customer name into firstName and lastName
			let nameParts = customerName.trim().split(/\s+/);
			let firstName = nameParts[0] || '';
			let lastName = nameParts.slice(1).join(' ') || '';
			
			// If no lastName, use firstName as lastName
			if (!lastName) {
				lastName = firstName;
			}
			
			let apiData = {
				code: selectedPlatform.gw_code || 'QRIS',
				orderId: invoiceNo,
				amount: Math.round(grandTotal),
				customer: {
					firstName: firstName,
					lastName: lastName,
					email: customerEmail || 'customer@example.com',
					phone: customerPhone || ''
				}
			};
			
			// Call external API
			submitBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Mengirim ke gateway...');
			$.ajax({
				url: 'https://dev.osu.biz.id/mig/esb/v1/api/payments',
				type: 'POST',
				contentType: 'application/json',
				data: JSON.stringify(apiData),
				dataType: 'json',
				success: function(apiResponse) {
					// Store gateway response data
					let gatewayResponse = {
						code: apiResponse.code || '',
						orderId: apiResponse.orderId || invoiceNo,
						status: apiResponse.status || 'PENDING',
						url: apiResponse.url || '',
						settlementTime: apiResponse.settlementTime || null,
						paymentGatewayAdminFee: apiResponse.paymentGatewayAdminFee || 0,
						chargeFee: apiResponse.chargeFee || 0,
						originalAmount: apiResponse.originalAmount || grandTotal
					};
					
					// Store in hidden field for form submission
					$('#gateway_response').remove();
					$('form').append('<input type="hidden" id="gateway_response" name="gateway_response" value="' + 
						encodeURIComponent(JSON.stringify(gatewayResponse)) + '">');
					
					// If QR code URL exists, show it to user
					if (gatewayResponse.url) {
						if (typeof Swal !== 'undefined') {
							Swal.fire({
								title: 'QR Code Pembayaran',
								html: `
									<div class="text-center">
										<p class="mb-3">Silakan scan QR code berikut untuk melakukan pembayaran:</p>
										<img src="${gatewayResponse.url}" alt="QR Code" class="img-fluid mb-3" style="max-width: 300px;">
										<p class="text-muted small mb-2">Status: <strong>${gatewayResponse.status}</strong></p>
										${gatewayResponse.paymentGatewayAdminFee > 0 ? 
											'<p class="text-muted small">Biaya Admin: Rp ' + gatewayResponse.paymentGatewayAdminFee.toLocaleString('id-ID') + '</p>' : ''}
										<a href="${gatewayResponse.url}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
											<i class="fas fa-external-link-alt"></i> Buka QR Code
										</a>
									</div>
								`,
								icon: 'info',
								showCancelButton: true,
								confirmButtonText: 'Lanjutkan Simpan',
								cancelButtonText: 'Batal',
								confirmButtonColor: '#4e73df',
								width: '500px'
							}).then((result) => {
								if (result.isConfirmed) {
									// Proceed with form submission
									submitForm();
								} else {
									// User cancelled, reset button
									isSubmitting = false;
									submitBtn.prop('disabled', false).html(originalText);
								}
							});
						} else {
							// Fallback if SweetAlert not available
							if (confirm('QR Code: ' + gatewayResponse.url + '\n\nLanjutkan menyimpan transaksi?')) {
								submitForm();
							} else {
								isSubmitting = false;
								submitBtn.prop('disabled', false).html(originalText);
							}
						}
					} else {
						// No QR code URL, proceed directly
						submitForm();
					}
				},
				error: function(xhr) {
					let errorMsg = 'Gagal mengirim ke payment gateway.';
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
		} else {
			// No platform or platform doesn't meet criteria, proceed with normal submission
			submitForm();
		}
		
		// Function to submit the form
		function submitForm() {
			submitBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan...');
			let formData = $('#form-sales').serialize();
			$.ajax({
				url: $('#form-sales').attr('action'),
				type: 'POST',
				data: formData,
				dataType: 'json',
				success: function(response) {
					if (response.status === 'success') {
						if (typeof Swal !== 'undefined') {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: response.message || 'Penjualan berhasil disimpan.',
								confirmButtonColor: '#28a745'
							}).then(() => {
								window.location.href = '<?= $config->baseURL ?>agent/sales/cart';
							});
						} else {
							alert(response.message || 'Penjualan berhasil disimpan.');
							window.location.href = '<?= $config->baseURL ?>agent/sales/cart';
						}
					} else {
						if (typeof Swal !== 'undefined') {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: response.message || 'Gagal menyimpan penjualan.',
								confirmButtonColor: '#dc3545'
							});
						} else {
							alert(response.message || 'Gagal menyimpan penjualan.');
						}
						isSubmitting = false;
						submitBtn.prop('disabled', false).html(originalText);
					}
				},
				error: function(xhr) {
					let errorMsg = 'Gagal menyimpan penjualan.';
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
		}
	});
});

function calculateTotals() {
	var subtotal = 0;
	
	// Calculate subtotal from cart items
	$('#cart-items-table tbody tr').each(function() {
		var $row = $(this);
		if ($row.hasClass('empty-state')) return;
		
		// Get price from data attribute (raw value)
		var price = parseFloat($row.data('price')) || 0;
		var qty = parseFloat($row.find('.quantity-input').val()) || 0;
		var itemSubtotal = qty * price;
		subtotal += itemSubtotal;
		
		// Update item subtotal display
		$row.find('.item-subtotal').text(formatCurrency(itemSubtotal));
	});
	
	var discount = parseFloat($('#discount-input').val()) || 0;
	var tax = parseFloat($('#tax-input').val()) || 0;
	var grandTotal = subtotal - discount + tax;
	
	// Update displays
	$('#subtotal-display').text(formatCurrency(subtotal));
	$('#subtotal-input').val(subtotal);
	$('#discount-display').text(formatCurrency(discount));
	$('#tax-display').text(formatCurrency(tax));
	$('#grand-total-display').text(formatCurrency(grandTotal));
	$('#grand-total-input').val(grandTotal);
}

function updateCartQty(itemId, qty) {
	if (qty < 1) {
		if (confirm('Hapus item dari keranjang?')) {
			removeFromCart(itemId);
		}
		return;
	}
	
	$.ajax({
		url: '<?= $config->baseURL ?>agent/sales/updateCart',
		type: 'POST',
		data: {
			item_id: itemId,
			qty: qty
		},
		dataType: 'json',
		headers: {
			'X-Requested-With': 'XMLHttpRequest'
		},
		success: function(response) {
			if (response.status === 'success') {
				// Reload page to update cart display
				location.reload();
			} else {
				alert(response.message || 'Gagal memperbarui keranjang');
			}
		},
		error: function() {
			alert('Terjadi kesalahan saat memperbarui keranjang');
		}
	});
}

function removeFromCart(itemId) {
	if (!confirm('Yakin ingin menghapus item ini dari keranjang?')) {
		return;
	}
	
	$.ajax({
		url: '<?= $config->baseURL ?>agent/sales/removeFromCart',
		type: 'POST',
		data: {
			item_id: itemId
		},
		dataType: 'json',
		headers: {
			'X-Requested-With': 'XMLHttpRequest'
		},
		success: function(response) {
			if (response.status === 'success') {
				// Reload page to update cart display
				location.reload();
			} else {
				alert(response.message || 'Gagal menghapus item');
			}
		},
		error: function() {
			alert('Terjadi kesalahan saat menghapus item');
		}
	});
}

function formatCurrency(amount) {
	// Format with thousands separator (dots) and no decimal places
	var num = parseFloat(amount) || 0;
	return 'Rp ' + num.toLocaleString('id-ID', {
		minimumFractionDigits: 0,
		maximumFractionDigits: 0,
		useGrouping: true
	});
}
</script>

