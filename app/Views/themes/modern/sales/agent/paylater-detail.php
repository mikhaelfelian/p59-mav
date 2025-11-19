<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-19
 * Github: github.com/mikhaelfelian
 * Description: View for paylater transaction detail
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
	color: white;
}

.invoice-badge {
	display: inline-block;
	background: rgba(255, 255, 255, 0.2);
	padding: 0.5rem 1rem;
	border-radius: 20px;
	margin-top: 0.5rem;
	font-size: 0.9rem;
}

.info-section {
	background: #f8f9fa;
	padding: 1.25rem;
	border-radius: 8px;
	border: 1px solid #e9ecef;
	margin-bottom: 1rem;
}

.info-section h6 {
	color: #495057;
	margin-bottom: 1rem;
	font-weight: 600;
}

.info-section dt {
	font-weight: 600;
	color: #6c757d;
}

.info-section dd {
	color: #212529;
}

.currency {
	font-weight: 600;
	color: #28a745;
}

.btn-back {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	border: none;
	padding: 0.5rem 1.5rem;
}

.btn-back:hover {
	background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
	transform: translateY(-2px);
	box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
</style>
<div class="card shadow-sm border-0">
	<div class="detail-header">
		<h5><i class="fas fa-file-invoice me-2"></i><?= esc($title ?? 'Detail Transaksi Paylater') ?></h5>
		<?php if (!empty($transaction->reference_code)): ?>
			<span class="invoice-badge"><i class="fas fa-hashtag me-1"></i><?= esc($transaction->reference_code) ?></span>
		<?php else: ?>
			<span class="invoice-badge"><i class="fas fa-hashtag me-1"></i>ID: <?= esc($transaction->id) ?></span>
		<?php endif; ?>
	</div>
	<div class="card-body p-4">
		<div class="row mb-4">
			<div class="col-md-6 mb-3">
				<div class="info-section">
					<h6><i class="fas fa-receipt me-2"></i> Informasi Transaksi</h6>
					<dl class="row mb-0">
						<dt class="col-sm-5 mb-3">Agen:</dt>
						<dd class="col-sm-7 mb-3">
							<strong><?= esc($agent->name ?? '-') ?></strong>
						</dd>
						
						<dt class="col-sm-5 mb-3">Tipe Mutasi:</dt>
						<dd class="col-sm-7 mb-3">
							<?php
							$typeBadges = [
								'1' => 'bg-primary',
								'2' => 'bg-success',
								'3' => 'bg-warning'
							];
							$badgeClass = $typeBadges[$transaction->mutation_type ?? '1'] ?? 'bg-secondary';
							?>
							<span class="badge <?= $badgeClass ?>"><?= esc($mutationTypeLabel ?? 'Unknown') ?></span>
						</dd>
						
						<dt class="col-sm-5 mb-3">Jumlah:</dt>
						<dd class="col-sm-7 mb-3">
							<span class="currency">Rp <?= number_format((float)($transaction->amount ?? 0), 0, ',', '.') ?></span>
						</dd>
						
						<?php if (!empty($transaction->description)): ?>
							<dt class="col-sm-5 mb-3">Deskripsi:</dt>
							<dd class="col-sm-7 mb-3">
								<?= esc($transaction->description) ?>
							</dd>
						<?php endif; ?>
					</dl>
				</div>
			</div>
			<div class="col-md-6 mb-3">
				<div class="info-section">
					<h6><i class="fas fa-info-circle me-2"></i> Informasi Lainnya</h6>
					<dl class="row mb-0">
						<dt class="col-sm-5 mb-3">Tanggal Transaksi:</dt>
						<dd class="col-sm-7 mb-3">
							<i class="fas fa-calendar me-1 text-muted"></i>
							<?php
							if (!empty($transaction->created_at)) {
								$date = new \DateTime($transaction->created_at);
								echo esc($date->format('d/m/Y H:i:s'));
							} else {
								echo '-';
							}
							?>
						</dd>
						
						<?php if (!empty($transaction->updated_at) && $transaction->updated_at !== $transaction->created_at): ?>
							<dt class="col-sm-5 mb-3">Diperbarui:</dt>
							<dd class="col-sm-7 mb-3">
								<i class="fas fa-clock me-1 text-muted"></i>
								<?php
								$date = new \DateTime($transaction->updated_at);
								echo esc($date->format('d/m/Y H:i:s'));
								?>
							</dd>
						<?php endif; ?>
						
						<?php if (!empty($transaction->reference_code)): ?>
							<dt class="col-sm-5 mb-3">Kode Referensi:</dt>
							<dd class="col-sm-7 mb-3">
								<code><?= esc($transaction->reference_code) ?></code>
							</dd>
						<?php endif; ?>
						
						<?php if (!empty($agent->phone)): ?>
							<dt class="col-sm-5 mb-3">Telepon Agen:</dt>
							<dd class="col-sm-7 mb-3">
								<a href="tel:<?= esc($agent->phone) ?>" class="text-decoration-none">
									<i class="fas fa-phone me-1 text-primary"></i><?= esc($agent->phone) ?>
								</a>
							</dd>
						<?php endif; ?>
					</dl>
				</div>
			</div>
		</div>

		<?php if (!empty($sale)): ?>
			<div class="info-section mb-4">
				<h6><i class="fas fa-shopping-cart me-2"></i> Informasi Penjualan Terkait</h6>
				<dl class="row mb-0">
					<dt class="col-sm-3 mb-3">Invoice No:</dt>
					<dd class="col-sm-9 mb-3">
						<strong><?= esc($sale->invoice_no ?? '-') ?></strong>
					</dd>
					
					<dt class="col-sm-3 mb-3">Total Penjualan:</dt>
					<dd class="col-sm-9 mb-3">
						<span class="currency">Rp <?= number_format((float)($sale->grand_total ?? 0), 0, ',', '.') ?></span>
					</dd>
					
					<dt class="col-sm-3 mb-3">Status Pembayaran:</dt>
					<dd class="col-sm-9 mb-3">
						<?php
						$statusLabels = [
							'0' => ['label' => 'Belum Bayar', 'class' => 'bg-danger'],
							'1' => ['label' => 'Sebagian', 'class' => 'bg-warning'],
							'2' => ['label' => 'Lunas', 'class' => 'bg-success'],
							'3' => ['label' => 'Paylater', 'class' => 'bg-info']
						];
						$status = $statusLabels[$sale->payment_status ?? '0'] ?? $statusLabels['0'];
						?>
						<span class="badge <?= $status['class'] ?>"><?= esc($status['label']) ?></span>
					</dd>
				</dl>
			</div>
		<?php endif; ?>

		<div class="row mt-4">
			<div class="col-md-12 d-flex gap-3">
				<a href="<?= $config->baseURL ?>agent/paylater" class="btn btn-back text-white">
					<i class="fas fa-arrow-left"></i> Kembali
				</a>
			</div>
		</div>
	</div>
</div>

