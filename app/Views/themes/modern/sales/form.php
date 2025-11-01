<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: View for sales form (create/edit) with items table
 */
helper('form');
$isEdit = !empty($sale);
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?= $title ?? ($isEdit ? 'Edit Penjualan' : 'Tambah Penjualan') ?></h5>
	</div>
	<div class="card-body">
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		?>

		<form method="post" action="" class="form-horizontal" id="form-sales">
			<?= form_hidden('id', @$sale['id'] ?? '') ?>

			<!-- Sale Header -->
			<div class="row mb-3">
				<div class="col-md-6">
					<div class="form-group">
						<label class="form-label">Invoice Code <span class="text-danger">*</span></label>
						<?= form_input([
							'name' => 'invoice_code',
							'class' => 'form-control',
							'value' => set_value('invoice_code', @$sale['invoice_code'] ?? $invoice_code ?? ''),
							'readonly' => 'readonly'
						]) ?>
						<small class="text-muted">Auto-generated</small>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="form-label">Tanggal Invoice <span class="text-danger">*</span></label>
						<?= form_input([
							'name' => 'invoice_date',
							'type' => 'date',
							'class' => 'form-control',
							'value' => set_value('invoice_date', @$sale['invoice_date'] ?? date('Y-m-d')),
							'required' => 'required'
						]) ?>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-6">
					<div class="form-group">
						<label class="form-label">Customer ID <span class="text-danger">*</span></label>
						<?= form_input([
							'name' => 'customer_id',
							'type' => 'number',
							'class' => 'form-control',
							'value' => set_value('customer_id', @$sale['customer_id'] ?? ''),
							'required' => 'required',
							'placeholder' => 'ID Customer'
						]) ?>
						<small class="text-muted">Masukkan ID Customer</small>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="form-label">Agen <span class="text-danger">*</span></label>
						<?php
						$agentOptions = ['' => 'Pilih Agen'];
						foreach ($agents ?? [] as $agent) {
							$agentOptions[$agent['id']] = $agent['code'] . ' - ' . $agent['name'];
						}
						$selectedAgent = set_value('agent_id', @$sale['agent_id'] ?? $locked_agent_id ?? '');
						if ($is_agent_locked ?? false) {
							echo form_input([
								'name' => 'agent_id',
								'type' => 'hidden',
								'value' => $selectedAgent
							]);
							$agentName = '';
							foreach ($agents ?? [] as $agent) {
								if ($agent['id'] == $selectedAgent) {
									$agentName = $agent['code'] . ' - ' . $agent['name'];
									break;
								}
							}
							echo form_input([
								'class' => 'form-control',
								'value' => $agentName,
								'readonly' => 'readonly'
							]);
						} else {
							echo form_dropdown('agent_id', $agentOptions, $selectedAgent, [
								'class' => 'form-control',
								'required' => 'required'
							]);
						}
						?>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-6">
					<div class="form-group">
						<label class="form-label">Warehouse ID</label>
						<?= form_input([
							'name' => 'warehouse_id',
							'type' => 'number',
							'class' => 'form-control',
							'value' => set_value('warehouse_id', @$sale['warehouse_id'] ?? ''),
							'placeholder' => 'Optional'
						]) ?>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="form-label">Status Pembayaran</label>
						<?= form_dropdown('status_payment', [
							'unpaid' => 'Belum Bayar',
							'partial' => 'Cicilan',
							'paid' => 'Lunas',
							'refunded' => 'Dikembalikan'
						], set_value('status_payment', @$sale['status_payment'] ?? 'unpaid'), [
							'class' => 'form-control'
						]) ?>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-6">
					<div class="form-group">
						<label class="form-label">Status Order</label>
						<?= form_dropdown('status_order', [
							'pending' => 'Pending',
							'completed' => 'Selesai',
							'cancelled' => 'Dibatalkan'
						], set_value('status_order', @$sale['status_order'] ?? 'pending'), [
							'class' => 'form-control'
						]) ?>
					</div>
				</div>
			</div>

			<hr>

			<!-- Add Item Section -->
			<h5>Tambah Item</h5>
			<div class="row mb-3">
				<div class="col-md-4">
					<div class="form-group">
						<label class="form-label">Produk</label>
						<select id="select-item" class="form-control">
							<option value="">Pilih Produk</option>
							<?php foreach ($items ?? [] as $item): ?>
								<option value="<?= $item->id ?>" data-name="<?= esc($item->name) ?>" data-price="<?= $item->price ?>"><?= esc($item->name) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label class="form-label">Varian</label>
						<select id="select-variant" class="form-control">
							<option value="">Pilih Varian</option>
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label class="form-label">Serial Number</label>
						<select id="select-sn" class="form-control">
							<option value="">Pilih SN</option>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label class="form-label">&nbsp;</label>
						<button type="button" class="btn btn-primary w-100" id="btn-add-item">Tambah</button>
					</div>
				</div>
			</div>

			<!-- Sales Items Table -->
			<?= view('themes/modern/sales/partials/sales_items_table', [
				'sale_items' => $sale_items ?? [],
				'items' => $items ?? []
			]) ?>

			<!-- Totals Section -->
			<div class="row mt-4">
				<div class="col-md-8"></div>
				<div class="col-md-4">
					<div class="card">
						<div class="card-body">
							<div class="row mb-2">
								<div class="col-6"><strong>Subtotal:</strong></div>
								<div class="col-6 text-end"><span id="subtotal-display">0.00</span></div>
								<?= form_hidden('subtotal', set_value('subtotal', @$sale['subtotal'] ?? 0), ['id' => 'subtotal-input']) ?>
							</div>
							<div class="row mb-2">
								<div class="col-6">
									<label class="form-label">Diskon Type:</label>
									<?= form_dropdown('discount_type', [
										'' => 'Tidak ada',
										'%' => 'Persen (%)',
										'rp' => 'Rupiah'
									], set_value('discount_type', @$sale['discount_type'] ?? ''), [
										'class' => 'form-control form-control-sm',
										'id' => 'discount-type'
									]) ?>
								</div>
								<div class="col-6">
									<label class="form-label">Diskon Value:</label>
									<?= form_input([
										'name' => 'discount_value',
										'type' => 'number',
										'class' => 'form-control form-control-sm',
										'value' => set_value('discount_value', @$sale['discount_value'] ?? 0),
										'step' => '0.01',
										'id' => 'discount-value'
									]) ?>
								</div>
							</div>
							<div class="row mb-2">
								<div class="col-6"><strong>Diskon Total:</strong></div>
								<div class="col-6 text-end"><span id="discount-total-display">0.00</span></div>
								<?= form_hidden('discount_total', set_value('discount_total', @$sale['discount_total'] ?? 0), ['id' => 'discount-total-input']) ?>
							</div>
							<div class="row mb-2">
								<div class="col-6">
									<label class="form-label">Tax Rate (%):</label>
									<?= form_input([
										'name' => 'tax_rate',
										'type' => 'number',
										'class' => 'form-control form-control-sm',
										'value' => set_value('tax_rate', @$sale['tax_rate'] ?? 0),
										'step' => '0.01',
										'id' => 'tax-rate'
									]) ?>
								</div>
								<div class="col-6">
									<label class="form-label">Tax Total:</label>
									<div class="text-end"><span id="tax-total-display">0.00</span></div>
									<?= form_hidden('tax_total', set_value('tax_total', @$sale['tax_total'] ?? 0), ['id' => 'tax-total-input']) ?>
								</div>
							</div>
							<hr>
							<div class="row mb-2">
								<div class="col-6"><strong>Grand Total:</strong></div>
								<div class="col-6 text-end"><strong><span id="grand-total-display">0.00</span></strong></div>
								<?= form_hidden('grand_total', set_value('grand_total', @$sale['grand_total'] ?? 0), ['id' => 'grand-total-input']) ?>
							</div>
							<div class="row mb-2">
								<div class="col-6"><strong>Total Qty:</strong></div>
								<div class="col-6 text-end"><span id="total-qty-display">0</span></div>
								<?= form_hidden('total_qty', set_value('total_qty', @$sale['total_qty'] ?? 0), ['id' => 'total-qty-input']) ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Payment Section -->
			<hr>
			<h5>Pembayaran</h5>
			<div class="row mb-3">
				<div class="col-md-3">
					<div class="form-group">
						<label class="form-label">Metode Pembayaran</label>
						<?= form_dropdown('payment_method', [
							'cash' => 'Cash',
							'transfer' => 'Transfer',
							'qris' => 'QRIS',
							'gateway' => 'Gateway'
						], set_value('payment_method', 'cash'), [
							'class' => 'form-control'
						]) ?>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label class="form-label">Jumlah Pembayaran</label>
						<?= form_input([
							'name' => 'payment_amount',
							'type' => 'number',
							'class' => 'form-control',
							'value' => set_value('payment_amount', ''),
							'step' => '0.01',
							'placeholder' => '0.00'
						]) ?>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label class="form-label">Payment Reference</label>
						<?= form_input([
							'name' => 'payment_ref',
							'class' => 'form-control',
							'value' => set_value('payment_ref', ''),
							'placeholder' => 'Optional'
						]) ?>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label class="form-label">Payment Gateway</label>
						<?= form_input([
							'name' => 'payment_gateway',
							'class' => 'form-control',
							'value' => set_value('payment_gateway', ''),
							'placeholder' => 'Optional'
						]) ?>
					</div>
				</div>
			</div>

			<!-- Form Actions -->
			<div class="row">
				<div class="col-md-12">
					<a href="<?= $config->baseURL ?>sales" class="btn btn-secondary">Batal</a>
					<button type="submit" class="btn btn-primary" id="btn-submit">Simpan</button>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
$(document).ready(function() {
	let items = <?= json_encode($sale_items ?? []) ?>;
	let itemList = <?= json_encode(array_map(function($item) { return ['id' => $item->id, 'name' => $item->name, 'price' => $item->price]; }, $items ?? [])) ?>;

	// Load variants when item is selected
	$('#select-item').on('change', function() {
		let itemId = $(this).val();
		$('#select-variant').html('<option value="">Pilih Varian</option>');
		$('#select-sn').html('<option value="">Pilih SN</option>');
		
		if (itemId) {
			$.ajax({
				url: '<?= $config->baseURL ?>item-varian/getByItem/' + itemId,
				type: 'GET',
				dataType: 'json',
				success: function(response) {
					if (response.status === 'success' && response.data) {
						$.each(response.data, function(i, variant) {
							$('#select-variant').append('<option value="' + variant.id + '" data-sku="' + variant.sku_variant + '">' + variant.variant_name + '</option>');
						});
					}
				}
			});
		}
	});

	// Load SNs when variant is selected
	$('#select-variant').on('change', function() {
		let itemId = $('#select-item').val();
		let variantId = $(this).val();
		
		$('#select-sn').html('<option value="">Pilih SN</option>');
		
		if (itemId) {
			$.ajax({
				url: '<?= $config->baseURL ?>sales/getUnusedSNs',
				type: 'GET',
				data: {
					item_id: itemId,
					variant_id: variantId || ''
				},
				dataType: 'json',
				success: function(response) {
					if (response.status === 'success' && response.data) {
						$.each(response.data, function(i, sn) {
							$('#select-sn').append('<option value="' + sn.id + '">' + sn.sn + '</option>');
						});
					}
				}
			});
		}
	});

	// Add item to table
	$('#btn-add-item').on('click', function() {
		let itemId = $('#select-item').val();
		let variantId = $('#select-variant').val();
		let snId = $('#select-sn').val();
		
		if (!itemId) {
			alert('Pilih produk terlebih dahulu');
			return;
		}

		let item = itemList.find(i => i.id == itemId);
		if (!item) return;

		let variantName = $('#select-variant option:selected').text();
		let snValue = $('#select-sn option:selected').text();
		
		// Add to items array
		let newItem = {
			item_id: itemId,
			variant_id: variantId || '',
			sn_id: snId || '',
			item_name: item.name + (variantName && variantName !== 'Pilih Varian' ? ' - ' + variantName : ''),
			qty: 1,
			price: item.price,
			discount_type: '',
			discount_value: 0,
			total_price: item.price
		};
		
		items.push(newItem);
		updateItemsTable();
		
		// Reset selects
		$('#select-item').val('').trigger('change');
	});

	// Update items table
	function updateItemsTable() {
		let tbody = $('#sales-items-table tbody');
		tbody.empty();
		
		items.forEach(function(item, index) {
			let row = '<tr data-index="' + index + '">';
			row += '<td>' + (index + 1) + '</td>';
			row += '<td>' + item.item_name + '</td>';
			row += '<td><input type="number" class="form-control form-control-sm item-qty" value="' + item.qty + '" step="0.01"></td>';
			row += '<td><input type="number" class="form-control form-control-sm item-price" value="' + item.price + '" step="0.01"></td>';
			row += '<td><input type="number" class="form-control form-control-sm item-discount-value" value="' + (item.discount_value || 0) + '" step="0.01"></td>';
			row += '<td class="item-total">' + formatCurrency(item.total_price) + '</td>';
			row += '<td><button type="button" class="btn btn-sm btn-danger btn-remove-item">Hapus</button></td>';
			row += '<input type="hidden" name="items[' + index + '][item_id]" value="' + item.item_id + '">';
			row += '<input type="hidden" name="items[' + index + '][variant_id]" value="' + (item.variant_id || '') + '">';
			row += '<input type="hidden" name="items[' + index + '][sn_id]" value="' + (item.sn_id || '') + '">';
			row += '<input type="hidden" name="items[' + index + '][item_name]" value="' + escapeHtml(item.item_name) + '">';
			row += '<input type="hidden" name="items[' + index + '][qty]" class="item-qty-hidden" value="' + item.qty + '">';
			row += '<input type="hidden" name="items[' + index + '][price]" class="item-price-hidden" value="' + item.price + '">';
			row += '<input type="hidden" name="items[' + index + '][discount_value]" class="item-discount-value-hidden" value="' + (item.discount_value || 0) + '">';
			row += '<input type="hidden" name="items[' + index + '][total_price]" class="item-total-hidden" value="' + item.total_price + '">';
			row += '<input type="hidden" name="items[' + index + '][cost_price]" value="0">';
			row += '<input type="hidden" name="items[' + index + '][profit]" value="0">';
			row += '</tr>';
			tbody.append(row);
		});
		
		calculateTotals();
	}

	// Remove item
	$(document).on('click', '.btn-remove-item', function() {
		let index = $(this).closest('tr').data('index');
		items.splice(index, 1);
		updateItemsTable();
	});

	// Update item calculations
	$(document).on('input', '.item-qty, .item-price, .item-discount-value', function() {
		let row = $(this).closest('tr');
		let index = row.data('index');
		let qty = parseFloat(row.find('.item-qty').val()) || 0;
		let price = parseFloat(row.find('.item-price').val()) || 0;
		let discount = parseFloat(row.find('.item-discount-value').val()) || 0;
		let total = (qty * price) - discount;
		
		items[index].qty = qty;
		items[index].price = price;
		items[index].discount_value = discount;
		items[index].total_price = total;
		
		row.find('.item-qty-hidden').val(qty);
		row.find('.item-price-hidden').val(price);
		row.find('.item-discount-value-hidden').val(discount);
		row.find('.item-total-hidden').val(total);
		row.find('.item-total').text(formatCurrency(total));
		
		calculateTotals();
	});

	// Calculate totals
	function calculateTotals() {
		let subtotal = 0;
		let totalQty = 0;
		
		items.forEach(function(item) {
			subtotal += parseFloat(item.total_price || 0);
			totalQty += parseFloat(item.qty || 0);
		});
		
		let discountType = $('#discount-type').val();
		let discountValue = parseFloat($('#discount-value').val()) || 0;
		let discountTotal = 0;
		
		if (discountType === '%') {
			discountTotal = subtotal * (discountValue / 100);
		} else if (discountType === 'rp') {
			discountTotal = discountValue;
		}
		
		let taxRate = parseFloat($('#tax-rate').val()) || 0;
		let taxTotal = (subtotal - discountTotal) * (taxRate / 100);
		let grandTotal = subtotal - discountTotal + taxTotal;
		
		$('#subtotal-display').text(formatCurrency(subtotal));
		$('#subtotal-input').val(subtotal);
		$('#discount-total-display').text(formatCurrency(discountTotal));
		$('#discount-total-input').val(discountTotal);
		$('#tax-total-display').text(formatCurrency(taxTotal));
		$('#tax-total-input').val(taxTotal);
		$('#grand-total-display').text(formatCurrency(grandTotal));
		$('#grand-total-input').val(grandTotal);
		$('#total-qty-display').text(totalQty);
		$('#total-qty-input').val(totalQty);
	}

	// Update totals when discount or tax changes
	$('#discount-type, #discount-value, #tax-rate').on('change input', function() {
		calculateTotals();
	});

	// Format currency
	function formatCurrency(value) {
		return parseFloat(value || 0).toLocaleString('id-ID', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});
	}

	// Escape HTML
	function escapeHtml(text) {
		var map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};
		return text.replace(/[&<>"']/g, function(m) { return map[m]; });
	}

	// Initialize table
	updateItemsTable();

	// Form submit
	$('#form-sales').on('submit', function(e) {
		e.preventDefault();
		
		let formData = $(this).serialize();
		let url = '<?= $config->baseURL ?>sales/' + (<?= $isEdit ? 'true' : 'false' ?> ? 'update/' + <?= @$sale['id'] ?? 0 ?> : 'store');
		
		$.ajax({
			url: url,
			type: 'POST',
			data: formData,
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
						window.location.href = '<?= $config->baseURL ?>sales';
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
					text: 'Terjadi kesalahan saat menyimpan data.'
				});
			}
		});
	});
});
</script>

