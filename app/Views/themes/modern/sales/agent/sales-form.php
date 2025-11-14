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

.checkout-panel {
	background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
	border: 1px solid #e3e8ef;
	border-radius: 12px;
	padding: 1.35rem;
	box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
	min-height: 100%;
}

.checkout-title {
	font-size: 0.95rem;
	font-weight: 600;
	color: #1f2937;
	display: flex;
	align-items: center;
	gap: 0.5rem;
	margin-bottom: 1rem;
	text-transform: uppercase;
	letter-spacing: 0.04em;
}

.checkout-title i {
	color: #4e73df;
	font-size: 1.05rem;
}

.checkout-field-hint {
	font-size: 0.75rem;
	color: #6c757d;
	margin-top: 0.35rem;
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
		<?= form_hidden('agent_id', $agentId ?? '') ?>
		<?= form_hidden('invoice_no', $invoice_no ?? '') ?>

		<!-- Cart Items Section -->
		<div class="sales-form-section">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<div class="section-title mb-0">
					<i class="fas fa-shopping-cart"></i>
					<span>Keranjang Belanja <span class="badge bg-primary ms-2" id="cart-count"><?= count($cart ?? []) ?></span></span>
				</div>
				<a href="<?= $config->baseURL ?>agent/item" class="btn btn-outline-primary">
					<i class="fas fa-arrow-left me-1"></i> Lanjut Belanja
				</a>
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
					
					<!-- Payment Selection -->
					<div class="row g-3 mb-3">
						<div class="col-12">
							<label class="form-label fw-semibold">Pilihan Pembayaran</label>
							<div class="d-flex gap-3">
								<div class="form-check">
									<input class="form-check-input" type="radio" name="payment_type" id="payment_paynow" value="paynow" checked>
									<label class="form-check-label" for="payment_paynow">
										Pay Now
									</label>
								</div>
								<?php if ($hasCreditLimit ?? false): ?>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="payment_type" id="payment_paylater" value="paylater">
									<label class="form-check-label" for="payment_paylater">
										Pay Later
									</label>
								</div>
								<?php endif; ?>
							</div>
						</div>
						
						<!-- Platform Selection (shown when Pay Now selected) -->
						<div class="col-12" id="platform-selection-wrapper">
							<label class="form-label">Platform Pembayaran</label>
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
									'id' => 'platform_id'
								]);
								?>
							</div>
							<small class="text-muted"><i class="fas fa-info-circle"></i> Opsional</small>
						</div>
					</div>
					
					<!-- Address Selection -->
					<div class="row g-3 mb-3">
						<div class="col-12">
							<label class="form-label fw-semibold">Alamat Pengiriman</label>
							<div class="mb-2">
								<div class="form-check">
									<input class="form-check-input" type="radio" name="address_type" id="address_current" value="current" checked>
									<label class="form-check-label" for="address_current">
										Gunakan Alamat saat ini
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="address_type" id="address_other" value="other">
									<label class="form-check-label" for="address_other">
										Alamat lain
									</label>
								</div>
							</div>
							
							<!-- Current Address Display -->
							<div id="current-address-display" class="mb-2">
								<?php if (!empty($agentAddress)): ?>
								<textarea class="form-control" rows="4" readonly><?= esc($agentAddress) ?></textarea>
								<input type="hidden" id="delivery_address_current" value="<?= esc($agentAddress) ?>">
								<?php else: ?>
								<div class="alert alert-warning">
									<i class="fas fa-exclamation-triangle me-2"></i>Alamat agen belum terdaftar. Silakan pilih "Alamat lain" atau lengkapi data agen terlebih dahulu.
								</div>
								<input type="hidden" id="delivery_address_current" value="">
								<?php endif; ?>
							</div>
							
							<!-- Other Address Input -->
							<div id="other-address-input" class="mb-2" style="display: none;">
								<textarea class="form-control" id="delivery_address_other" rows="4" placeholder="Masukkan alamat pengiriman"></textarea>
							</div>
							
							<!-- Hidden field for form submission -->
							<input type="hidden" name="delivery_address" id="delivery_address_hidden" value="<?= esc($agentAddress ?? '') ?>">
						</div>
					</div>
					
					<!-- Note Field -->
					<div class="row g-3">
						<div class="col-12">
							<label class="form-label fw-semibold">Catatan</label>
							<textarea class="form-control" name="note" id="note" rows="3" placeholder="Tambahkan catatan untuk pesanan ini (opsional)"><?= set_value('note', '') ?></textarea>
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
					
					<!-- Diskon dan Pajak dihapus dari ringkasan -->
					
					<div class="grand-total mt-4">
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
					<i class="fas fa-arrow-left"></i> Kembali
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
	
	// Handle payment type selection
	$('input[name="payment_type"]').on('change', function() {
		var paymentType = $(this).val();
		if (paymentType === 'paynow') {
			$('#platform-selection-wrapper').show();
		} else if (paymentType === 'paylater') {
			$('#platform-selection-wrapper').hide();
			$('#platform_id').val('');
		}
	});
	
	// Handle address type selection
	$('input[name="address_type"]').on('change', function() {
		var addressType = $(this).val();
		if (addressType === 'current') {
			$('#current-address-display').show();
			$('#other-address-input').hide();
			$('#delivery_address_other').val('');
			// Update hidden field with current address
			var currentAddress = $('#delivery_address_current').val() || '';
			$('#delivery_address_hidden').val(currentAddress);
		} else if (addressType === 'other') {
			$('#current-address-display').hide();
			$('#other-address-input').show();
			// Clear hidden field, will be updated from textarea
			$('#delivery_address_hidden').val('');
		}
	});
	
	// Update delivery_address hidden field when other address changes
	$('#delivery_address_other').on('input', function() {
		if ($('#address_other').is(':checked')) {
			$('#delivery_address_hidden').val($(this).val());
		}
	});
	
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
		
		// Handle delivery address based on selection
		var addressType = $('input[name="address_type"]:checked').val();
		var deliveryAddress = '';
		if (addressType === 'current') {
			deliveryAddress = $('#delivery_address_current').val() || '';
		} else if (addressType === 'other') {
			deliveryAddress = $('#delivery_address_other').val() || '';
		}
		
		// Update hidden field with selected address
		$('#delivery_address_hidden').val(deliveryAddress);
		
		// Submit form - API call is handled in PHP
		let formData = $('#form-sales').serialize();
		$.ajax({
			url: $('#form-sales').attr('action'),
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
						});
					} else {
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
	var baseAmount = subtotal - discount;
	
	// Calculate tax based on tax_type
	var taxType = $('#tax-type-input').val() || '0';
	var taxAmount = 0;
	var ppnPercentage = <?= floatval($ppnPercentage ?? 11) ?>;
	
	if (taxType === '1') {
		// Include tax (PPN termasuk): tax is included in baseAmount
		// Tax = baseAmount - (baseAmount / (1 + ppn/100))
		taxAmount = baseAmount - (baseAmount / (1 + (ppnPercentage / 100)));
	} else if (taxType === '2') {
		// Added tax (PPN ditambahkan): tax is added on top
		taxAmount = baseAmount * (ppnPercentage / 100);
	} else {
		// No tax (tax_type = '0')
		taxAmount = 0;
	}
	
	var grandTotal = baseAmount;
	if (taxType === '2') {
		// For added tax, add tax to grand total
		grandTotal = baseAmount + taxAmount;
	} else if (taxType === '1') {
		// For include tax, grand total is baseAmount (tax already included)
		grandTotal = baseAmount;
	}
	
	// Update displays
	$('#subtotal-display').text(formatCurrency(subtotal));
	$('#subtotal-input').val(subtotal);
	$('#discount-display').text(formatCurrency(discount));
	$('#tax-display').text(formatCurrency(taxAmount));
	$('#tax-input').val(taxAmount.toFixed(2));
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
				// Update header cart count
				updateHeaderCartCount(response.cart_count || 0);
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
				// Update header cart count
				updateHeaderCartCount(response.cart_count || 0);
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

function updateHeaderCartCount(count) {
	var headerCartBadge = $('#header-cart-count');
	if (count > 0) {
		if (headerCartBadge.length) {
			headerCartBadge.text(count);
		} else {
			// Create badge if it doesn't exist
			$('.icon-link[href*="agent/sales/cart"]').append('<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="header-cart-count">' + count + '</span>');
		}
	} else {
		headerCartBadge.remove();
	}
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

