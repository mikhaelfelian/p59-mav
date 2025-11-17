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
			<h6><i class="fas fa-box me-2"></i> Daftar Item</h6>
			<div class="table-responsive">
				<table class="table items-table">
					<thead>
						<tr>
							<th style="width: 50px;">No</th>
							<th>Produk</th>
							<th style="width: 80px;" class="text-center">Qty</th>
							<th style="width: 120px;" class="text-end">Harga</th>
							<th style="width: 120px;" class="text-end">Diskon</th>
							<th style="width: 150px;" class="text-end">Subtotal</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($items)): ?>
							<?php foreach ($items as $index => $item): ?>
								<tr>
									<td class="text-center"><?= $index + 1 ?></td>
									<td><strong><?= esc($item['item'] ?? $item['item_name'] ?? '-') ?></strong></td>
									<td class="text-center"><?= esc($item['qty'] ?? $item['quantity'] ?? '0') ?></td>
									<td class="text-end currency">Rp <?= number_format($item['price'] ?? 0, 0, ',', '.') ?></td>
									<td class="text-end currency">Rp <?= number_format($item['disc'] ?? $item['discount'] ?? 0, 0, ',', '.') ?></td>
									<td class="text-end currency"><strong>Rp <?= number_format($item['amount'] ?? $item['subtotal'] ?? 0, 0, ',', '.') ?></strong></td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="7" class="text-center py-4 text-muted">
									<i class="fas fa-inbox me-2"></i>Tidak ada item
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Biaya Tambahan Section -->
		<?php if (!empty($isAdmin) && $isAdmin): ?>
			<div class="items-section mb-4">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<h6 class="mb-0"><i class="fas fa-receipt me-2"></i> Biaya Tambahan</h6>
					<?php if (!empty($isAgent) && $isAgent && !empty($feeTypes)): ?>
						<button type="button" class="btn btn-sm btn-primary" id="btnAddFee">
							<i class="fas fa-plus me-1"></i>Tambah Biaya
						</button>
					<?php endif; ?>
				</div>
				
				<?php if (!empty($fees) && is_array($fees) && count($fees) > 0): ?>
				<div class="table-responsive">
					<table class="table items-table" id="feesTable">
						<thead>
							<tr>
								<th style="width: 50px;">No</th>
								<th>Jenis Biaya</th>
								<th>Nama Biaya (Opsional)</th>
								<th style="width: 150px;" class="text-end">Jumlah</th>
								<?php if (!empty($isAgent) && $isAgent): ?>
								<th style="width: 80px;" class="text-center">Aksi</th>
								<?php endif; ?>
							</tr>
						</thead>
						<tbody id="feesTableBody">
							<?php foreach ($fees as $index => $fee): ?>
								<tr data-fee-id="<?= $fee['id'] ?>">
									<td class="text-center"><?= $index + 1 ?></td>
									<td><strong><?= esc($fee['fee_type_name'] ?? $fee['fee_type_code'] ?? '-') ?></strong></td>
									<td><?= esc($fee['fee_name'] ?? '-') ?></td>
									<td class="text-end currency"><strong>Rp <?= number_format($fee['amount'] ?? 0, 0, ',', '.') ?></strong></td>
									<?php if (!empty($isAgent) && $isAgent): ?>
									<td class="text-center">
										<button type="button" class="btn btn-sm btn-warning text-white edit-fee-btn" data-fee-id="<?= $fee['id'] ?>" title="Edit">
											<i class="fas fa-edit"></i>
										</button>
										<button type="button" class="btn btn-sm btn-danger text-white delete-fee-btn" data-fee-id="<?= $fee['id'] ?>" title="Hapus">
											<i class="fas fa-trash"></i>
										</button>
									</td>
									<?php endif; ?>
								</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="<?= (!empty($isAgent) && $isAgent) ? '4' : '3' ?>" class="text-end"><strong>Total Biaya:</strong></td>
								<td class="text-end currency">
									<strong>Rp <?= number_format(array_sum(array_column($fees, 'amount')), 0, ',', '.') ?></strong>
								</td>
								<?php if (!empty($isAgent) && $isAgent): ?>
								<td></td>
								<?php endif; ?>
							</tr>
						</tfoot>
					</table>
				</div>
				<?php else: ?>
					<?php if (!empty($isAgent) && $isAgent && !empty($feeTypes)): ?>
						<div class="alert alert-info mb-0">
							<i class="fas fa-info-circle me-2"></i>Belum ada biaya tambahan. Klik "Tambah Biaya" untuk menambahkan.
						</div>
					<?php else: ?>
						<div class="alert alert-info mb-0">
							<i class="fas fa-info-circle me-2"></i>Tidak ada biaya tambahan.
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- Note Field -->
		<div class="items-section mb-4">
			<h6><i class="fas fa-sticky-note me-2"></i> Catatan Agen</h6>
			<div class="mb-0">
				<p class="mb-0"><?= nl2br(esc($sale['note'])) ?></p>
			</div>
		</div>

		<!-- Admin Note Field (Courier/AWB) - Admin Only -->
		<?php if (!empty($isAdmin) && $isAdmin): ?>
		<div class="items-section mb-4">
			<h6><i class="fas fa-truck me-2"></i> Catatan Admin (Kurir, AWB, dll)</h6>
			<div class="mb-3">
				<textarea class="form-control" id="adminNote" rows="3" placeholder="Masukkan informasi kurir, AWB, tracking number, dll..."><?= esc($sale['admin_note'] ?? '') ?></textarea>
				<small class="text-muted">Catatan ini hanya untuk admin, tidak terlihat oleh agen.</small>
			</div>
			<button type="button" class="btn btn-sm btn-primary" id="btnSaveAdminNote">
				<i class="fas fa-save me-1"></i>Simpan
			</button>
		</div>
		<?php elseif (!empty($sale['admin_note'])): ?>
		<div class="items-section mb-4">
			<h6><i class="fas fa-truck me-2"></i> Catatan Admin</h6>
			<div class="mb-0">
				<p class="mb-0 text-muted"><?= nl2br(esc($sale['admin_note'])) ?></p>
			</div>
		</div>
		<?php endif; ?>

		<div class="row">
			<div class="col-md-8"></div>
			<div class="col-md-4">
				<div class="summary-card">
					<div class="d-flex justify-content-between mb-3">
						<span class="summary-label">Subtotal:</span>
						<span class="summary-value currency">Rp <?= number_format($sale['total_amount'] ?? $sale['subtotal'] ?? 0, 0, ',', '.') ?></span>
					</div>
					<div class="d-flex justify-content-between mb-3">
						<span class="summary-label">Diskon:</span>
						<span class="summary-value currency">Rp <?= number_format($sale['discount_amount'] ?? $sale['discount'] ?? 0, 0, ',', '.') ?></span>
					</div>
					<div class="d-flex justify-content-between mb-3">
						<span class="summary-label">Pajak:</span>
						<span class="summary-value currency">Rp <?= number_format($sale['tax_amount'] ?? $sale['tax'] ?? 0, 0, ',', '.') ?></span>
					</div>
					<div class="grand-total">
						<div class="d-flex justify-content-between">
							<span class="grand-total-label">Total Akhir:</span>
							<span class="grand-total-value currency">Rp <?= number_format($sale['grand_total'] ?? 0, 0, ',', '.') ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php if (!empty($payment)): ?>
			<div class="items-section mb-4">
				<h6><i class="fas fa-credit-card me-2"></i> Informasi Pembayaran</h6>
				<div class="row">
					<div class="col-md-6">
						<dl class="row mb-0">
							
							<dt class="col-sm-5 mb-3">Platform:</dt>
							<dd class="col-sm-7 mb-3">
								<strong><?= esc($payment['platform_name'] ?? '-') ?></strong>
							</dd>
							
							<dt class="col-sm-5 mb-3">Jumlah:</dt>
							<dd class="col-sm-7 mb-3">
								<span class="currency">Rp <?= number_format($payment['amount'] ?? 0, 0, ',', '.') ?></span>
							</dd>
						</dl>
					</div>
					
					<?php if (!empty($gatewayResponse)): ?>
						<div class="col-md-6">
							<dl class="row mb-0">
								<dt class="col-sm-5 mb-3">Status Pembayaran:</dt>
								<dd class="col-sm-7 mb-3">
									<?php
									$status = strtoupper($gatewayResponse['status'] ?? 'UNKNOWN');
									$statusClass = 'secondary';
									if ($status === 'PAID') $statusClass = 'success';
									elseif ($status === 'PENDING') $statusClass = 'warning';
									elseif (in_array($status, ['FAILED', 'CANCELED', 'EXPIRED'])) $statusClass = 'danger';
									?>
									<span class="badge bg-<?= $statusClass ?>" id="paymentStatusBadge"><?= esc($status) ?></span>
									<button type="button" class="btn btn-sm btn-outline-primary ms-2" id="btnRefreshPayment" onclick="refreshPaymentStatus()" title="Refresh Status Pembayaran">
										<i class="fas fa-sync-alt" id="refreshIcon"></i> Refresh
									</button>
								</dd>
								
								<?php if (!empty($gatewayResponse['paymentCode'])): ?>
									<dt class="col-sm-5 mb-3">Kode Pembayaran:</dt>
									<dd class="col-sm-7 mb-3">
										<code class="bg-light p-2 rounded d-inline-block" style="font-size: 0.9rem;" id="paymentCode">
											<?= esc($gatewayResponse['paymentCode']) ?>
										</code>
										<button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('<?= addslashes(esc($gatewayResponse['paymentCode'])) ?>')">
											<i class="fas fa-copy"></i> Salin
										</button>
									</dd>
								<?php endif; ?>
								
								<?php if (!empty($gatewayResponse['expiredAt'])): ?>
									<dt class="col-sm-5 mb-3">Kedaluwarsa:</dt>
									<dd class="col-sm-7 mb-3">
										<i class="fas fa-clock me-1 text-muted"></i>
										<span id="paymentExpiredAt">
											<?php
											try {
												$expiredDate = new \DateTime($gatewayResponse['expiredAt']);
												echo esc($expiredDate->format('d/m/Y H:i'));
											} catch (\Exception $e) {
												echo esc($gatewayResponse['expiredAt']);
											}
											?>
										</span>
									</dd>
								<?php endif; ?>
							</dl>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Payment Success Thank You Message -->
		<?php
		$isPaymentPaid = false;
		if (!empty($gatewayResponse) && isset($gatewayResponse['status'])) {
			$isPaymentPaid = (strtoupper($gatewayResponse['status']) === 'PAID');
		} elseif (!empty($sale['payment_status']) && $sale['payment_status'] === '2') {
			$isPaymentPaid = true;
		} elseif (!empty($payment) && isset($payment['payment_status']) && $payment['payment_status'] === '2') {
			$isPaymentPaid = true;
		}
		?>
		<?php if ($isPaymentPaid): ?>
		<div class="items-section mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
			<div class="text-center py-4">
				<i class="fas fa-check-circle fa-4x mb-3" style="color: #28a745; background: white; border-radius: 50%; padding: 1rem;"></i>
				<h2 class="mb-3" style="color: white;">Pembayaran Berhasil!</h2>
				<p class="lead mb-4" style="color: rgba(255,255,255,0.9);">
					Transaksi Anda dengan nomor invoice <strong><?= esc($sale['invoice_no'] ?? 'N/A') ?></strong> telah berhasil diselesaikan.
				</p>
				
				<div class="row justify-content-center mt-4">
					<div class="col-md-8">
						<div class="card" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);">
							<div class="card-body">
								<h5 class="card-title" style="color: white;">Detail Transaksi</h5>
								<dl class="row mb-0">
									<dt class="col-sm-5" style="color: rgba(255,255,255,0.9);">Invoice No:</dt>
									<dd class="col-sm-7" style="color: white;"><strong><?= esc($sale['invoice_no'] ?? 'N/A') ?></strong></dd>
									
									<dt class="col-sm-5" style="color: rgba(255,255,255,0.9);">Total:</dt>
									<dd class="col-sm-7">
										<span class="fw-bold" style="color: #28a745; background: white; padding: 0.25rem 0.75rem; border-radius: 4px;">
											Rp <?= number_format($sale['grand_total'] ?? 0, 0, ',', '.') ?>
										</span>
									</dd>
									
									<?php if (!empty($sale['agent_name'])): ?>
										<dt class="col-sm-5" style="color: rgba(255,255,255,0.9);">Agen:</dt>
										<dd class="col-sm-7" style="color: white;"><?= esc($sale['agent_name']) ?></dd>
									<?php endif; ?>
									
									<dt class="col-sm-5" style="color: rgba(255,255,255,0.9);">Tanggal:</dt>
									<dd class="col-sm-7" style="color: white;">
										<?php
										if (!empty($sale['created_at'])) {
											$date = new \DateTime($sale['created_at']);
											echo esc($date->format('d/m/Y H:i'));
										} else {
											echo 'N/A';
										}
										?>
									</dd>
								</dl>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<div class="row mt-4">
			<div class="col-md-12 d-flex gap-3">
				<a href="<?= $config->baseURL ?>agent/sales" class="btn btn-back text-white">
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
