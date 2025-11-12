<?php
/**
 * Professional Sales Form View
 * CodeIgniter 4.3.1
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-04
 * Description: Professional sales form with modern UI/UX
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

.summary-input-group .form-control {
	background: rgba(255,255,255,0.95);
	border: none;
}

#sales-items-table {
	font-size: 0.9rem;
}

#sales-items-table thead {
	background-color: #f8f9fa;
}

#sales-items-table thead th {
	font-weight: 600;
	color: #495057;
	border-bottom: 2px solid #dee2e6;
	padding: 0.75rem;
}

#sales-items-table tbody td {
	padding: 0.75rem;
	vertical-align: middle;
}

#sales-items-table tbody tr:hover {
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

.autocomplete-dropdown {
	position: absolute;
	top: 100%;
	left: 0;
	right: 0;
	background: #fff;
	border: 1px solid #dee2e6;
	border-top: none;
	border-radius: 0 0 4px 4px;
	max-height: 300px;
	overflow-y: auto;
	z-index: 1000;
	box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.autocomplete-item {
	padding: 0.75rem;
	cursor: pointer;
	border-bottom: 1px solid #f8f9fa;
	transition: background-color 0.2s;
}

.autocomplete-item:hover,
.autocomplete-item.active {
	background-color: #f8f9fa;
}

.autocomplete-item:last-child {
	border-bottom: none;
}

.autocomplete-item-name {
	font-weight: 600;
	color: #2c3e50;
	margin-bottom: 0.25rem;
}

.autocomplete-item-details {
	font-size: 0.875rem;
	color: #6c757d;
}

.autocomplete-item-details i {
	margin-right: 0.5rem;
	width: 16px;
}

.summary-input-group .form-control {
	background: rgba(255,255,255,0.95);
	border: none;
}

.product-search-wrapper {
	position: relative;
}

.product-search-wrapper i {
	position: absolute;
	left: 1rem;
	top: 50%;
	transform: translateY(-50%);
	color: #6c757d;
	z-index: 10;
}

.product-search-wrapper .form-control {
	padding-left: 2.75rem;
}

.btn-add-item {
	border-radius: 6px;
	font-weight: 600;
	padding: 0.625rem 1.25rem;
	transition: all 0.3s ease;
}

.btn-add-item:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(78, 115, 223, 0.4);
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

.loading-spinner {
	display: inline-block;
	width: 1rem;
	height: 1rem;
	border: 2px solid #f3f3f3;
	border-top: 2px solid #4e73df;
	border-radius: 50%;
	animation: spin 1s linear infinite;
	margin-right: 0.5rem;
}

@keyframes spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}

.alert-validation {
	border-left: 4px solid #dc3545;
	background-color: #f8d7da;
	color: #721c24;
	padding: 0.75rem 1rem;
	border-radius: 4px;
	margin-bottom: 1rem;
	display: none;
}

.sn-badge {
	display: inline-block;
	background: #e9ecef;
	padding: 0.25rem 0.5rem;
	border-radius: 4px;
	font-size: 0.75rem;
	margin: 0.125rem;
	color: #495057;
}
</style>

<!-- Select2 CSS -->
<link rel="stylesheet" href="<?= base_url('public/vendors/jquery.select2/css/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css') ?>">

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

	<form method="post" action="<?= $config->baseURL ?>sales/store" id="form-sales" novalidate>
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
				<div class="col-md-6">
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
							<i class="fas fa-info-circle"></i> Format: <strong>kode</strong> - <strong>nomor</strong> - <strong>kode akhir</strong> (Contoh: H-4575-PBP)<br>
							<i class="fas fa-info-circle"></i> Kode & Nomor wajib diisi, Kode Akhir opsional
						</small>
					</div>
				</div>
			</div>
		</div>

		<!-- Product Search & Selection Section -->
		<div class="sales-form-section">
			<div class="section-title">
				<i class="fas fa-search"></i>
				<span>Cari & Tambah Produk</span>
			</div>
			
			<div class="row g-3">
				<div class="col-md-12">
					<div class="product-search-wrapper">
						<i class="fas fa-barcode"></i>
						<?= form_input([
							'name' => 'product_search',
							'type' => 'text',
							'class' => 'form-control form-control-lg',
							'id' => 'product-search',
							'placeholder' => 'Scan barcode atau ketik nama/SKU produk...',
							'autofocus' => 'autofocus'
						]) ?>
					</div>
				</div>
				
				<div class="col-md-12" id="product-selection" style="display: none;">
					<div class="card border-primary">
						<div class="card-body">
							<div class="row g-3">
								<div class="col-md-5">
									<label class="form-label">Produk</label>
									<select id="select-item" class="form-select form-select-lg">
										<option value="">-- Pilih Produk --</option>
										<?php foreach ($items ?? [] as $item): ?>
											<option value="<?= $item->id ?>" 
												data-name="<?= esc($item->name) ?>" 
												data-price="<?= $item->price ?>" 
												data-sku="<?= esc($item->sku ?? '') ?>">
												<?= esc($item->name) ?> <?= !empty($item->sku) ? '(' . esc($item->sku) . ')' : '' ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-md-5">
									<label class="form-label">Nomor Seri</label>
									<select id="select-sn" class="form-select form-select-lg select2">
										<option value="">-- Pilih Nomor Seri --</option>
									</select>
									<small class="text-muted"><i class="fas fa-info-circle"></i> Pilih nomor seri</small>
								</div>
								<div class="col-md-2">
									<label class="form-label">&nbsp;</label>
									<button type="button" class="btn btn-primary btn-add-item w-100" id="btn-add-item">
										<i class="fas fa-plus-circle"></i> Tambah Item
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Sales Items Table Section -->
		<div class="sales-form-section">
			<div class="section-title">
				<i class="fas fa-shopping-cart"></i>
				<span>Item di Keranjang <span class="badge bg-primary ms-2" id="item-count">0</span></span>
			</div>
			
			<div class="table-responsive">
				<table class="table table-hover align-middle" id="sales-items-table">
					<thead>
						<tr>
							<th width="5%">#</th>
							<th width="25%">Produk</th>
							<th width="10%">Jumlah</th>
							<th width="12%">Harga</th>
							<th width="12%">Diskon</th>
							<th width="16%">Nomor Seri</th>
							<th width="12%">Subtotal</th>
							<th width="8%">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="8" class="empty-state">
								<i class="fas fa-shopping-cart"></i>
								<div>Belum ada item yang ditambahkan. Cari dan tambahkan produk untuk memulai.</div>
							</td>
						</tr>
					</tbody>
				</table>
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
							<label class="form-label">Platform</label>
							<div class="input-group">
								<span class="input-group-text bg-light"><i class="fas fa-store"></i></span>
								<?php
								$platformOptions = ['' => '-- Pilih Platform --'];
								foreach ($platforms ?? [] as $platform) {
									$platformOptions[$platform['id']] = esc($platform['platform']);
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
					
					<?php if (!empty($platforms)): ?>
					<div class="summary-input-group mt-3" id="total-receive-group" style="display: none;">
						<label class="form-label text-white mb-2">Total Diterima</label>
						<div class="text-white" id="total-receive-display">Rp 0.00</div>
						<?= form_input([
							'type' => 'hidden',
							'name' => 'total_receive',
							'id' => 'total-receive-input',
							'value' => '0'
						]) ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Action Buttons -->
		<div class="action-buttons mt-4">
			<div class="d-flex justify-content-between align-items-center">
				<a href="<?= $config->baseURL ?>sales" class="btn btn-secondary btn-action">
					<i class="fas fa-times"></i> Batal
				</a>
				<div class="d-flex gap-2">
					<button type="submit" class="btn btn-success btn-action" id="btn-pay">
						<i class="fas fa-check"></i> Simpan
					</button>
				</div>
			</div>
		</div>
	</form>
</div>

<!-- Select2 JS -->
<script src="<?= base_url('public/vendors/jquery.select2/js/select2.full.min.js') ?>"></script>

<script>
$(document).ready(function() {
	let items = [];
	let isSubmitting = false;
	
	// Store platform data for API check
	let platformsData = <?= json_encode($platforms ?? []) ?>;
	
	/**
	 * Get all used serial number IDs from cart items
	 * @returns {Array} Array of item_sn_id values
	 */
	function getUsedSnIds() {
		let usedSnIds = [];
		items.forEach(function(item) {
			if (item.sns && Array.isArray(item.sns)) {
				item.sns.forEach(function(sn) {
					if (sn.item_sn_id) {
						usedSnIds.push(parseInt(sn.item_sn_id));
					}
				});
			}
		});
		return usedSnIds;
	}
	
	/**
	 * Load and filter serial numbers for selected product
	 * @param {number} itemId - The item ID to load serial numbers for
	 */
	function loadSerialNumbers(itemId) {
		let $snSelect = $('#select-sn');
		let usedSnIds = getUsedSnIds();
		
		// Clear and disable Select2
		$snSelect.empty().append('<option value="">Loading...</option>');
		$snSelect.prop('disabled', true);
		$snSelect.val(null).trigger('change.select2');

		if (itemId) {
			$.ajax({
				url: '<?= $config->baseURL ?>sales/getUnusedSNs',
				type: 'GET',
				data: { item_id: itemId },
				dataType: 'json',
				success: function(response) {
					// Clear existing options
					$snSelect.empty();
					
					// Add placeholder option
					$snSelect.append('<option value="">-- Pilih Nomor Seri --</option>');
					
					if (response.status === 'success' && response.data && response.data.length > 0) {
						// Filter out serial numbers that are already in cart
						let availableSns = response.data.filter(function(sn) {
							let snId = parseInt(sn.id);
							return usedSnIds.indexOf(snId) === -1;
						});
						
						if (availableSns.length > 0) {
							// Add filtered options
							$.each(availableSns, function(i, sn) {
								$snSelect.append(
									$('<option>', {
										value: sn.id,
										'data-sn': sn.sn,
										text: sn.sn
									})
								);
							});
						} else {
							$snSelect.append('<option value="">Semua nomor seri sudah digunakan</option>');
						}
					} else {
						$snSelect.append('<option value="">Tidak ada nomor seri tersedia</option>');
					}
					
					// Re-enable and update Select2
					$snSelect.prop('disabled', false);
					$snSelect.val(null).trigger('change.select2');
				},
				error: function() {
					$snSelect.empty();
					$snSelect.append('<option value="">Gagal memuat nomor seri</option>');
					$snSelect.prop('disabled', false);
					$snSelect.val(null).trigger('change.select2');
				}
			});
		} else {
			$snSelect.empty();
			$snSelect.append('<option value="">-- Pilih Nomor Seri --</option>');
			$snSelect.prop('disabled', false);
			$snSelect.val(null).trigger('change.select2');
		}
	}

	let searchTimeout;
	$('#product-search').on('input', function() {
		clearTimeout(searchTimeout);
		let $this = $(this);
		searchTimeout = setTimeout(function() {
			let searchTerm = $this.val().toLowerCase().trim();
			if (searchTerm.length >= 2) {
				$('#product-selection').slideDown(300);
				filterProductOptions(searchTerm);
			} else if (searchTerm.length === 0) {
				$('#product-selection').slideUp(300);
				$('#select-item option').show();
			}
		}, 300);
	});

	function filterProductOptions(searchTerm) {
		let found = false;
		$('#select-item option').each(function() {
			if ($(this).val() === '') {
				return true;
			}
			let itemText = $(this).text().toLowerCase();
			let itemSku = $(this).data('sku') ? $(this).data('sku').toLowerCase() : '';
			if (itemText.indexOf(searchTerm) !== -1 || itemSku.indexOf(searchTerm) !== -1) {
				$(this).show();
				if (!found) found = true;
			} else {
				$(this).hide();
			}
		});
		if (found && $('#select-item').val() === '') {
			$('#select-item').focus();
		}
	}

	$('#select-item').on('change', function() {
		let itemId = $(this).val();
		loadSerialNumbers(itemId);
	});

	function showToast(message, type = 'success') {
		if (typeof Swal === 'undefined') {
			alert(message);
			return;
		}
		if (type === 'success') {
			const Toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 3000,
				timerProgressBar: true,
				iconColor: 'white',
				customClass: {
					popup: 'bg-success text-light toast p-2'
				},
				didOpen: (toast) => {
					toast.addEventListener('mouseenter', Swal.stopTimer);
					toast.addEventListener('mouseleave', Swal.resumeTimer);
				}
			});
			Toast.fire({
				html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> ' + message + '</div>'
			});
		} else if (type === 'error') {
			Swal.fire({
				icon: 'error',
				title: 'Error!',
				text: message,
				confirmButtonColor: '#d33'
			});
		} else if (type === 'warning') {
			Swal.fire({
				icon: 'warning',
				title: 'Peringatan!',
				text: message,
				confirmButtonColor: '#ffc107'
			});
		} else {
			const Toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 3000,
				timerProgressBar: true,
				iconColor: 'white',
				customClass: {
					popup: 'bg-info text-light toast p-2'
				},
				didOpen: (toast) => {
					toast.addEventListener('mouseenter', Swal.stopTimer);
					toast.addEventListener('mouseleave', Swal.resumeTimer);
				}
			});
			Toast.fire({
				html: '<div class="toast-content"><i class="far fa-info-circle me-2"></i> ' + message + '</div>'
			});
		}
	}

	$('#btn-add-item').on('click', function() {
		let itemId = $('#select-item').val();
		let selectedSnId = $('#select-sn').val();

		if (!itemId) {
			showToast('Pilih produk terlebih dahulu', 'warning');
			return;
		}
		let itemOption = $('#select-item option:selected');
		let itemName = itemOption.data('name');
		let itemPrice = parseFloat(itemOption.data('price')) || 0;

		let sns = [];
		if (selectedSnId) {
			let selectedSnOption = $('#select-sn option:selected');
			sns.push({
				item_sn_id: selectedSnId,
				sn: selectedSnOption.data('sn') || selectedSnOption.text()
			});
		}
		
		let qty = 1;
		let newItem = {
			item_id: itemId,
			variant_id: null,
			item_name: itemName,
			qty: qty,
			price: itemPrice,
			discount: 0,
			subtotal: itemPrice * qty,
			sns: sns,
			note: ''
		};
		items.push(newItem);
		updateItemsTable();
		
		// Refresh serial number dropdown if same product is still selected
		let currentItemId = $('#select-item').val();
		if (currentItemId && currentItemId === itemId) {
			loadSerialNumbers(itemId);
		} else {
			$('#select-item').val('').trigger('change');
			$('#select-sn').empty().append('<option value="">-- Pilih Nomor Seri --</option>').val(null).trigger('change.select2');
		}
		
		$('#product-search').val('').focus();
	});

	function updateItemsTable() {
		let tbody = $('#sales-items-table tbody');
		tbody.empty();
		$('#item-count').text(items.length);
		if (items.length === 0) {
			tbody.append('<tr><td colspan="8" class="empty-state"><i class="fas fa-shopping-cart"></i><div>Belum ada item yang ditambahkan. Cari dan tambahkan produk untuk memulai.</div></td></tr>');
			calculateTotals();
			return;
		}
		items.forEach(function(item, index) {
			let hasSN = item.sns && item.sns.length > 0;
			let qtyReadonly = hasSN ? 'readonly' : '';
			let qtyValue = hasSN ? '1' : item.qty;
			let row = '<tr data-index="' + index + '">';
			row += '<td>' + (index + 1) + '</td>';
			row += '<td>' + escapeHtml(item.item_name) + '</td>';
			row += '<td><input type="number" class="form-control form-control-sm item-qty" value="' + qtyValue + '" step="1" min="1" ' + qtyReadonly + '></td>';
			row += '<td><input type="number" class="form-control form-control-sm item-price" value="' + item.price + '" step="0.01"></td>';
			row += '<td><input type="number" class="form-control form-control-sm item-discount" value="' + (item.discount || 0) + '" step="0.01" min="0"></td>';
			row += '<td>';
			if (hasSN) {
				row += '<div class="sn-badges">';
				item.sns.forEach(function(sn) {
					row += '<span class="sn-badge">' + escapeHtml(sn.sn) + '</span>';
				});
				row += '</div>';
			} else {
				row += '-';
			}
			row += '</td>';
			row += '<td class="item-subtotal">' + formatCurrency(item.subtotal) + '</td>';
			row += '<td>';
			row += '<button type="button" class="btn btn-sm btn-danger btn-remove-item"><i class="fas fa-trash"></i></button>';
			row += '<input type="hidden" name="items[' + index + '][item_id]" value="' + item.item_id + '">';
			row += '<input type="hidden" name="items[' + index + '][variant_id]" value="' + (item.variant_id || '') + '">';
			row += '<input type="hidden" name="items[' + index + '][qty]" class="item-qty-hidden" value="' + qtyValue + '">';
			row += '<input type="hidden" name="items[' + index + '][price]" class="item-price-hidden" value="' + item.price + '">';
			row += '<input type="hidden" name="items[' + index + '][discount]" class="item-discount-hidden" value="' + (item.discount || 0) + '">';
			row += '<input type="hidden" name="items[' + index + '][subtotal]" class="item-subtotal-hidden" value="' + item.subtotal + '">';
			row += '<input type="hidden" name="items[' + index + '][note]" value="' + escapeHtml(item.note || '') + '">';
			row += '<input type="hidden" name="items[' + index + '][sns]" class="item-sns-json" value="' + escapeHtml(JSON.stringify(item.sns || [])) + '">';
			row += '</td>';
			row += '</tr>';
			tbody.append(row);
		});
		calculateTotals();
	}

	$(document).on('click', '.btn-remove-item', function() {
		let index = $(this).closest('tr').data('index');
		let removedItem = items[index];
		items.splice(index, 1);
		updateItemsTable();
		
		// Refresh serial number dropdown if a product is selected
		let currentItemId = $('#select-item').val();
		if (currentItemId) {
			// If removed item was the same product, refresh the dropdown
			if (removedItem && removedItem.item_id && parseInt(removedItem.item_id) === parseInt(currentItemId)) {
				loadSerialNumbers(currentItemId);
			}
		}
	});

	$(document).on('input', '.item-qty, .item-price, .item-discount', function() {
		let row = $(this).closest('tr');
		let index = row.data('index');
		let qtyInput = row.find('.item-qty');
		if (qtyInput.prop('readonly')) {
			qtyInput.val('1');
		}
		let qty = parseFloat(qtyInput.val()) || 1;
		let price = parseFloat(row.find('.item-price').val()) || 0;
		let discount = parseFloat(row.find('.item-discount').val()) || 0;
		let subtotal = (qty * price) - discount;
		items[index].qty = qty;
		items[index].price = price;
		items[index].discount = discount;
		items[index].subtotal = subtotal;
		row.find('.item-qty-hidden').val(qty);
		row.find('.item-price-hidden').val(price);
		row.find('.item-discount-hidden').val(discount);
		row.find('.item-subtotal-hidden').val(subtotal);
		row.find('.item-subtotal').text(formatCurrency(subtotal));
		calculateTotals();
	});

	function calculateTotals() {
		let subtotal = 0;
		items.forEach(function(item) {
			subtotal += parseFloat(item.subtotal || 0);
		});
		let discountAmount = parseFloat($('#discount-input').val()) || 0;
		let taxAmount = parseFloat($('#tax-input').val()) || 0;
		let grandTotal = subtotal - discountAmount + taxAmount;
		$('#subtotal-display').text(formatCurrency(subtotal));
		$('#subtotal-input').val(subtotal);
		$('#discount-display').text(formatCurrency(discountAmount));
		$('#tax-display').text(formatCurrency(taxAmount));
		$('#grand-total-display').text(formatCurrency(grandTotal));
		$('#grand-total-input').val(grandTotal);
	}

	$('#discount-input, #tax-input').on('input', function() {
		calculateTotals();
	});

	function escapeHtml(text) {
		let map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};
		return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
	}

	function formatCurrency(amount) {
		return 'Rp ' + parseFloat(amount || 0).toLocaleString('id-ID', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});
	}

	$('#form-sales').on('submit', function(e) {
		e.preventDefault();
		if (isSubmitting) return;
		let itemIdInputs = $('input[name*="[item_id]"]').filter(function() {
			return $(this).val() && $(this).val() !== '';
		});
		if (itemIdInputs.length === 0) {
			showToast('Minimal harus ada satu item dalam transaksi.', 'warning');
			return;
		}
		isSubmitting = true;
		let $submitBtn = $('#btn-pay');
		let originalText = $submitBtn.html();
		$submitBtn.prop('disabled', true).html('<span class="loading-spinner"></span> Menyimpan...');
		$('#discount-input').val($('#discount-input').val() || '0');
		$('#tax-input').val($('#tax-input').val() || '0');
		
		// Submit form - API call is handled in PHP
		let formData = $('#form-sales').serialize();
		$.ajax({
			url: $('#form-sales').attr('action'),
			type: 'POST',
			data: formData,
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success') {
					// Get redirect URL (to detail page) or construct from sale ID
					let redirectUrl = response.redirect || (response.data && response.data.id ? '<?= $config->baseURL ?>sales/' + response.data.id : '<?= $config->baseURL ?>sales');
					
					// Check if gateway response contains QR code URL
					if (response.data && response.data.gateway && response.data.gateway.url) {
						let gateway = response.data.gateway;
						
						// Calculate totalReceive if gateway response exists (platform.gw_status = 1)
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
							
							// Update totalReceive display
							$('#total-receive-display').text(formatCurrency(totalReceive));
							$('#total-receive-input').val(totalReceive);
							$('#total-receive-group').show();
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
									${gateway.totalReceive !== undefined ? 
										'<p class="text-muted small">Total Diterima: Rp ' + parseFloat(gateway.totalReceive).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</p>' : ''}
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
							showToast(response.message || 'Penjualan berhasil disimpan.', 'success');
							setTimeout(function() {
								window.location.href = redirectUrl;
							}, 1500);
						});
					} else {
						// No gateway response (gw_status = 0 or cash payment), hide totalReceive
						$('#total-receive-group').hide();
						
						showToast(response.message || 'Penjualan berhasil disimpan.', 'success');
						setTimeout(function() {
							window.location.href = redirectUrl;
						}, 1500);
					}
				} else {
					showToast(response.message || 'Gagal menyimpan penjualan.', 'error');
					isSubmitting = false;
					$submitBtn.prop('disabled', false).html(originalText);
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
				showToast(errorMsg, 'error');
				isSubmitting = false;
				$submitBtn.prop('disabled', false).html(originalText);
			}
		});
	});

	updateItemsTable();
	$('#product-search').focus();

	$(document).on('keydown', function(e) {
		if ((e.ctrlKey || e.metaKey) && e.keyCode === 13) {
			$('#btn-pay').click();
		}
		if (e.keyCode === 27) {
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					title: 'Batalkan penjualan?',
					text: 'Apakah Anda yakin ingin membatalkan penjualan ini?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#6c757d',
					confirmButtonText: 'Ya, Batalkan',
					cancelButtonText: 'Tidak'
				}).then((result) => {
					if (result.isConfirmed) {
						window.location.href = '<?= $config->baseURL ?>sales';
					}
				});
			} else {
				if (confirm('Batalkan penjualan ini?')) {
					window.location.href = '<?= $config->baseURL ?>sales';
				}
			}
		}
	});

	// Initialize Select2 for serial number select (single select)
	$('#select-sn').select2({
		theme: 'bootstrap-5',
		width: '100%',
		placeholder: '-- Pilih Nomor Seri --',
		allowClear: true,
		language: {
			noResults: function() {
				return "Tidak ada hasil ditemukan";
			},
			searching: function() {
				return "Mencari...";
			}
		}
	});
});
</script>
