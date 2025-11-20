<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-19
 * Github: github.com/mikhaelfelian
 * Description: View for item serial number detail
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

.sn-badge {
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
		<h5><i class="fas fa-barcode me-2"></i><?= esc($title ?? 'Detail Serial Number') ?></h5>
		<?php if (!empty($itemSn->sn)): ?>
			<span class="sn-badge"><i class="fas fa-hashtag me-1"></i><?= esc($itemSn->sn) ?></span>
		<?php endif; ?>
	</div>
	<div class="card-body p-4">
		<div class="row mb-4">
			<div class="col-md-6 mb-3">
				<div class="info-section">
					<h6><i class="fas fa-box me-2"></i> Informasi Item</h6>
					<dl class="row mb-0">
						<dt class="col-sm-5 mb-3">Nama Item:</dt>
						<dd class="col-sm-7 mb-3">
							<strong><?= esc($item->name ?? '-') ?></strong>
						</dd>
						
						<dt class="col-sm-5 mb-3">SKU:</dt>
						<dd class="col-sm-7 mb-3">
							<?= esc($item->sku ?? '-') ?>
						</dd>
					</dl>
				</div>
			</div>
			
			<div class="col-md-6 mb-3">
				<div class="info-section">
					<h6><i class="fas fa-info-circle me-2"></i> Informasi Serial Number</h6>
					<dl class="row mb-0">
						<dt class="col-sm-5 mb-3">Serial Number:</dt>
						<dd class="col-sm-7 mb-3">
							<strong><?= esc($itemSn->sn ?? '-') ?></strong>
						</dd>
						
						<dt class="col-sm-5 mb-3">Barcode:</dt>
						<dd class="col-sm-7 mb-3">
							<?= esc($itemSn->barcode ?? '-') ?>
						</dd>
						
						<dt class="col-sm-5 mb-3">Pemilik:</dt>
						<dd class="col-sm-7 mb-3">
							<?php if (!empty($itemSn->agent_id) && $itemSn->agent_id > 0 && !empty($agent)): ?>
								<i class="fas fa-user-tie me-1"></i><?= esc($agent->name ?? 'Agent #' . $itemSn->agent_id) ?>
							<?php else: ?>
								<i class="fas fa-warehouse me-1"></i>Pusat
							<?php endif; ?>
						</dd>
					</dl>
				</div>
			</div>
		</div>
		
		<div class="row mb-4">
			<div class="col-md-6 mb-3">
				<div class="info-section">
					<h6><i class="fas fa-toggle-on me-2"></i> Status</h6>
					<dl class="row mb-0">
						<dt class="col-sm-5 mb-3">Status Jual:</dt>
						<dd class="col-sm-7 mb-3">
							<?php if ($itemSn->is_sell == '1'): ?>
								<span class="badge bg-danger">Terjual</span>
							<?php else: ?>
								<span class="badge bg-success">Belum Terjual</span>
							<?php endif; ?>
						</dd>
						
						<dt class="col-sm-5 mb-3">Status Aktivasi:</dt>
						<dd class="col-sm-7 mb-3">
							<?php if ($itemSn->is_activated == '1'): ?>
								<span class="badge bg-success">Aktif</span>
							<?php else: ?>
								<span class="badge bg-warning">Belum Aktif</span>
							<?php endif; ?>
						</dd>
					</dl>
				</div>
			</div>
			
			<div class="col-md-6 mb-3">
				<div class="info-section">
					<h6><i class="fas fa-calendar me-2"></i> Tanggal</h6>
					<dl class="row mb-0">
						<dt class="col-sm-5 mb-3">Dibuat:</dt>
						<dd class="col-sm-7 mb-3">
							<?= !empty($itemSn->created_at) ? tgl_indo8($itemSn->created_at) : '-' ?>
						</dd>
						
						<?php if (!empty($itemSn->activated_at)): ?>
							<dt class="col-sm-5 mb-3">Diaktifkan:</dt>
							<dd class="col-sm-7 mb-3">
								<?= tgl_indo8($itemSn->activated_at) ?>
							</dd>
						<?php endif; ?>
						
						<?php if (!empty($itemSn->expired_at)): ?>
							<dt class="col-sm-5 mb-3">Kadaluarsa:</dt>
							<dd class="col-sm-7 mb-3">
								<?= tgl_indo8($itemSn->expired_at) ?>
							</dd>
						<?php endif; ?>
					</dl>
				</div>
			</div>
		</div>
		
		<div class="d-flex justify-content-between align-items-center">
			<a href="<?= $config->baseURL ?>report/items" class="btn btn-back text-white">
				<i class="fas fa-arrow-left me-2"></i>Kembali
			</a>
		</div>
	</div>
</div>

