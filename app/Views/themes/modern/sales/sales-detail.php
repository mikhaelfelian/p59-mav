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
</style>
<div class="card shadow-sm border-0">
	<div class="detail-header">
		<h5><i class="fas fa-file-invoice me-2"></i><?= $title ?? 'Detail Penjualan' ?></h5>
		<?php if (!empty($sale['invoice_no'])): ?>
			<span class="invoice-badge"><i class="fas fa-hashtag me-1"></i><?= esc($sale['invoice_no']) ?></span>
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
							<strong><?= esc($sale['customer_name'] ?? '-') ?></strong>
						</dd>
						
						<?php if (!empty($sale['plat_code']) && !empty($sale['plat_number'])): ?>
							<dt class="col-sm-5 mb-3">Plat Kendaraan:</dt>
							<dd class="col-sm-7 mb-3">
								<span class="badge bg-dark">
									<i class="fas fa-car me-1"></i><?= esc($sale['plat_code']) ?>-<?= esc($sale['plat_number']) ?>
									<?php if (!empty($sale['plat_last'])): ?>
										-<?= esc($sale['plat_last']) ?>
									<?php endif; ?>
								</span>
							</dd>
						<?php endif; ?>
						
						<dt class="col-sm-5 mb-3">Agen:</dt>
						<dd class="col-sm-7 mb-3">
							<i class="fas fa-user-tie me-1 text-muted"></i><?= esc($sale['agent_name'] ?? '-') ?>
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
							<th>Nomor Seri</th>
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
									<td>
										<?php if (!empty($item['sns'])): ?>
											<?php foreach ($item['sns'] as $sn): ?>
												<span class="serial-number">
													<i class="fas fa-barcode me-1"></i><?= esc($sn['sn'] ?? '') ?>
												</span>
											<?php endforeach; ?>
										<?php else: ?>
											<span class="text-muted">-</span>
										<?php endif; ?>
									</td>
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
							<dt class="col-sm-5 mb-3">Metode Pembayaran:</dt>
							<dd class="col-sm-7 mb-3">
								<?php
								$methodLabels = [
									'cash' => 'Tunai',
									'transfer' => 'Transfer',
									'qris' => 'QRIS',
									'credit' => 'Kredit',
									'other' => 'Lainnya'
								];
								$methodLabel = $methodLabels[$payment['method'] ?? 'other'] ?? 'Lainnya';
								?>
								<span class="badge bg-primary"><?= esc($methodLabel) ?></span>
							</dd>
							
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
									<span class="badge bg-<?= $statusClass ?>"><?= esc($status) ?></span>
								</dd>
								
								<?php if (!empty($gatewayResponse['paymentCode'])): ?>
									<dt class="col-sm-5 mb-3">Kode Pembayaran:</dt>
									<dd class="col-sm-7 mb-3">
										<code class="bg-light p-2 rounded d-inline-block" style="font-size: 0.9rem;">
											<?= esc($gatewayResponse['paymentCode']) ?>
										</code>
										<button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('<?= addslashes(esc($gatewayResponse['paymentCode'])) ?>')">
											<i class="fas fa-copy"></i> Salin
										</button>
									</dd>
								<?php endif; ?>
								
								<?php if (!empty($gatewayResponse['paymentGatewayAdminFee']) && $gatewayResponse['paymentGatewayAdminFee'] > 0): ?>
									<dt class="col-sm-5 mb-3">Biaya Admin:</dt>
									<dd class="col-sm-7 mb-3">
										<span class="currency">Rp <?= number_format($gatewayResponse['paymentGatewayAdminFee'], 0, ',', '.') ?></span>
									</dd>
								<?php endif; ?>
								
								<?php if (!empty($gatewayResponse['expiredAt'])): ?>
									<dt class="col-sm-5 mb-3">Kedaluwarsa:</dt>
									<dd class="col-sm-7 mb-3">
										<i class="fas fa-clock me-1 text-muted"></i>
										<?php
										try {
											$expiredDate = new \DateTime($gatewayResponse['expiredAt']);
											echo esc($expiredDate->format('d/m/Y H:i'));
										} catch (\Exception $e) {
											echo esc($gatewayResponse['expiredAt']);
										}
										?>
									</dd>
								<?php endif; ?>
							</dl>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="row mt-4">
			<div class="col-md-12 d-flex gap-2">
				<a href="<?= $config->baseURL ?>sales" class="btn btn-back text-white">
					<i class="fas fa-arrow-left me-2"></i>Kembali
				</a>
				<a href="<?= $config->baseURL ?>sales/print_dm/<?= $sale['id'] ?? '' ?>" target="_blank" class="btn btn-primary text-white">
					<i class="fas fa-print me-2"></i>Print
				</a>
			</div>
		</div>
		
		<script>
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
		</script>
	</div>
</div>
