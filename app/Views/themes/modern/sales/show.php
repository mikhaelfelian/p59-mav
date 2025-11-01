<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: View for sales detail/show
 */
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?= $title ?? 'Detail Penjualan' ?></h5>
	</div>
	<div class="card-body">
		<div class="row mb-3">
			<div class="col-md-12">
				<a href="<?= $config->baseURL ?>sales" class="btn btn-secondary">
					<i class="fas fa-arrow-left me-1"></i> Kembali
				</a>
				<?php if ($this->hasPermissionPrefix('update', true)): ?>
				<a href="<?= $config->baseURL ?>sales/edit/<?= $sale['id'] ?>" class="btn btn-warning">
					<i class="fas fa-edit me-1"></i> Edit
				</a>
				<?php endif; ?>
			</div>
		</div>

		<!-- Sale Header Info -->
		<div class="row mb-4">
			<div class="col-md-6">
				<table class="table table-borderless">
					<tr>
						<td width="40%"><strong>Invoice Code:</strong></td>
						<td><?= esc($sale['invoice_code']) ?></td>
					</tr>
					<tr>
						<td><strong>Tanggal Invoice:</strong></td>
						<td><?= date('d/m/Y', strtotime($sale['invoice_date'])) ?></td>
					</tr>
					<tr>
						<td><strong>Customer:</strong></td>
						<td><?= esc($sale['customer_name'] ?? '-') ?></td>
					</tr>
					<tr>
						<td><strong>Agen:</strong></td>
						<td><?= esc($sale['agent_code'] ?? '') ?> - <?= esc($sale['agent_name'] ?? '-') ?></td>
					</tr>
					<tr>
						<td><strong>Petugas:</strong></td>
						<td><?= esc($sale['user_name'] ?? '-') ?></td>
					</tr>
				</table>
			</div>
			<div class="col-md-6">
				<table class="table table-borderless">
					<tr>
						<td width="40%"><strong>Status Pembayaran:</strong></td>
						<td>
							<?php
							$statusBadge = [
								'unpaid' => '<span class="badge bg-danger">Belum Bayar</span>',
								'partial' => '<span class="badge bg-warning">Cicilan</span>',
								'paid' => '<span class="badge bg-success">Lunas</span>',
								'refunded' => '<span class="badge bg-secondary">Dikembalikan</span>'
							];
							echo $statusBadge[$sale['status_payment']] ?? $sale['status_payment'];
							?>
						</td>
					</tr>
					<tr>
						<td><strong>Status Order:</strong></td>
						<td>
							<?php
							$orderBadge = [
								'pending' => '<span class="badge bg-warning">Pending</span>',
								'completed' => '<span class="badge bg-success">Selesai</span>',
								'cancelled' => '<span class="badge bg-danger">Dibatalkan</span>'
							];
							echo $orderBadge[$sale['status_order']] ?? $sale['status_order'];
							?>
						</td>
					</tr>
					<tr>
						<td><strong>Total Qty:</strong></td>
						<td><?= number_format($sale['total_qty'], 2) ?></td>
					</tr>
					<tr>
						<td><strong>Subtotal:</strong></td>
						<td><?= number_format($sale['subtotal'], 2, ',', '.') ?></td>
					</tr>
					<tr>
						<td><strong>Diskon:</strong></td>
						<td><?= number_format($sale['discount_total'], 2, ',', '.') ?></td>
					</tr>
					<tr>
						<td><strong>Pajak:</strong></td>
						<td><?= number_format($sale['tax_total'], 2, ',', '.') ?></td>
					</tr>
					<tr>
						<td><strong>Grand Total:</strong></td>
						<td><strong><?= number_format($sale['grand_total'], 2, ',', '.') ?></strong></td>
					</tr>
					<tr>
						<td><strong>Total Bayar:</strong></td>
						<td><?= number_format($sale['total_payment'], 2, ',', '.') ?></td>
					</tr>
					<tr>
						<td><strong>Sisa Tagihan:</strong></td>
						<td><?= number_format($sale['balance_due'], 2, ',', '.') ?></td>
					</tr>
				</table>
			</div>
		</div>

		<!-- Sales Items -->
		<h5>Item Penjualan</h5>
		<div class="table-responsive mb-4">
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>No</th>
						<th>Nama Item</th>
						<th>Varian</th>
						<th>SN</th>
						<th>Qty</th>
						<th>Harga</th>
						<th>Diskon</th>
						<th>Total</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($items)): ?>
						<?php foreach ($items as $index => $item): ?>
							<tr>
								<td><?= $index + 1 ?></td>
								<td><?= esc($item['item_name']) ?></td>
								<td><?= esc($item['variant_name'] ?? '-') ?></td>
								<td><?= esc($item['sn'] ?? '-') ?></td>
								<td><?= number_format($item['qty'], 2) ?></td>
								<td><?= number_format($item['price'], 2, ',', '.') ?></td>
								<td><?= number_format($item['discount_value'] ?? 0, 2, ',', '.') ?></td>
								<td><?= number_format($item['total_price'], 2, ',', '.') ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="8" class="text-center text-muted">Tidak ada item</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<!-- Payments -->
		<h5>Riwayat Pembayaran</h5>
		<div class="table-responsive">
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>No</th>
						<th>Tanggal</th>
						<th>Metode</th>
						<th>Jumlah</th>
						<th>Reference</th>
						<th>Gateway</th>
						<th>Status</th>
						<th>Petugas</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($payments)): ?>
						<?php foreach ($payments as $index => $payment): ?>
							<tr>
								<td><?= $index + 1 ?></td>
								<td><?= date('d/m/Y H:i', strtotime($payment['payment_date'])) ?></td>
								<td><?= esc(ucfirst($payment['payment_method'])) ?></td>
								<td><?= number_format($payment['amount'], 2, ',', '.') ?></td>
								<td><?= esc($payment['payment_ref'] ?? '-') ?></td>
								<td><?= esc($payment['payment_gateway'] ?? '-') ?></td>
								<td>
									<?php
									$paymentStatusBadge = [
										'waiting' => '<span class="badge bg-warning">Menunggu</span>',
										'paid' => '<span class="badge bg-success">Dibayar</span>',
										'failed' => '<span class="badge bg-danger">Gagal</span>',
										'refunded' => '<span class="badge bg-secondary">Dikembalikan</span>'
									];
									echo $paymentStatusBadge[$payment['status']] ?? $payment['status'];
									?>
								</td>
								<td><?= esc($payment['user_name'] ?? '-') ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="8" class="text-center text-muted">Belum ada pembayaran</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

