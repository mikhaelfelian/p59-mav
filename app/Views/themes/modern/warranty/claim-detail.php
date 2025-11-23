<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-20
 * Github: github.com/mikhaelfelian
 * Description: Warranty claim detail view
 */
$statusSteps = [
	'pending'  => 'Menunggu Verifikasi',
	'approved' => 'Disetujui',
	'rejected' => 'Ditolak',
	'replaced' => 'Diganti'
];
?>
<div class="card shadow-sm border-0">
	<div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
		<div>
			<h5 class="card-title mb-0">
				<i class="fas fa-file-alt me-2 text-primary"></i>
				<?= esc($title ?? 'Detail Klaim Garansi') ?>
			</h5>
			<small class="text-muted">ID Klaim: #<?= esc($claim->id ?? '-') ?></small>
		</div>
		<span class="badge bg-primary px-3 py-2"><?= ucfirst($claim->status) ?></span>
	</div>
	<div class="card-body">
		<?php if (!empty($msg)) : ?>
			<?= show_alert($msg); ?>
		<?php endif; ?>

		<div class="row g-4">
			<div class="col-lg-8">
				<div class="info-section mb-4">
					<h6 class="mb-3"><i class="fas fa-timeline me-2 text-primary"></i>Timeline Status</h6>
					<ul class="timeline list-unstyled mb-0">
						<li class="mb-3">
							<div class="d-flex">
								<div class="me-3 text-primary"><i class="fas fa-circle"></i></div>
								<div>
									<strong>Pengajuan Klaim</strong>
									<div class="text-muted small"><?= esc(tgl_indo3($claim->created_at ?? date('Y-m-d H:i:s'))) ?></div>
								</div>
							</div>
						</li>
						<?php if (!empty($claim->store_approved)) : ?>
							<li class="mb-3">
								<div class="d-flex">
									<div class="me-3 text-success"><i class="fas fa-circle"></i></div>
									<div>
										<strong><?= $statusSteps['approved'] ?></strong>
										<div class="text-muted small">Catatan: <?= esc($claim->store_note ?? '-') ?></div>
									</div>
								</div>
							</li>
						<?php endif; ?>
						<?php if (!empty($replacement)) : ?>
							<li class="mb-3">
								<div class="d-flex">
									<div class="me-3 text-success"><i class="fas fa-circle"></i></div>
									<div>
										<strong><?= $statusSteps['replaced'] ?></strong>
										<div class="text-muted small"><?= esc(tgl_indo3($replacement->replaced_at)) ?></div>
									</div>
								</div>
							</li>
						<?php endif; ?>
					</ul>
				</div>

				<div class="info-section mb-4">
					<h6 class="mb-3"><i class="fas fa-microchip me-2 text-primary"></i>Serial Lama</h6>
					<dl class="row mb-0">
						<dt class="col-sm-4">Serial Number</dt>
						<dd class="col-sm-8"><?= esc($old_sn->sn ?? '-') ?></dd>
						<dt class="col-sm-4">Item</dt>
						<dd class="col-sm-8"><?= esc($item->name ?? '-') ?></dd>
						<dt class="col-sm-4">Aktivasi</dt>
						<dd class="col-sm-8"><?= !empty($old_sn->activated_at) ? esc(tgl_indo3($old_sn->activated_at)) : '-' ?></dd>
						<dt class="col-sm-4">Garansi Berakhir</dt>
						<dd class="col-sm-8"><?= !empty($old_sn->expired_at) ? esc(tgl_indo3($old_sn->expired_at)) : '-' ?></dd>
					</dl>
				</div>

				<?php if (!empty($new_sn)) : ?>
					<div class="info-section mb-4">
						<h6 class="mb-3"><i class="fas fa-sync me-2 text-primary"></i>Serial Pengganti</h6>
						<dl class="row mb-0">
							<dt class="col-sm-4">Serial Number Baru</dt>
							<dd class="col-sm-8"><?= esc($new_sn->sn) ?></dd>
							<dt class="col-sm-4">Aktivasi</dt>
							<dd class="col-sm-8"><?= !empty($new_sn->activated_at) ? esc(tgl_indo3($new_sn->activated_at)) : '-' ?></dd>
							<dt class="col-sm-4">Mengikuti Garansi</dt>
							<dd class="col-sm-8"><?= !empty($new_sn->expired_at) ? esc(tgl_indo3($new_sn->expired_at)) : '-' ?></dd>
						</dl>
					</div>
				<?php endif; ?>

				<?php if (!empty($sn_history)) : ?>
					<div class="info-section">
						<h6 class="mb-3"><i class="fas fa-history me-2 text-primary"></i>Riwayat SN</h6>
						<ul class="list-group list-group-flush">
							<?php foreach ($sn_history as $history) : ?>
								<?php
								// Handle both object and array formats
								$action = is_object($history) ? ($history->action ?? '') : ($history['action'] ?? '');
								$oldSnText = is_object($history) ? ($history->old_sn_text ?? null) : ($history['old_sn_text'] ?? null);
								$oldSnId = is_object($history) ? ($history->old_sn_id ?? null) : ($history['old_sn_id'] ?? null);
								$oldSnReplaced = is_object($history) ? ($history->old_sn_replaced ?? null) : ($history['old_sn_replaced'] ?? null);
								$newSnText = is_object($history) ? ($history->new_sn_text ?? null) : ($history['new_sn_text'] ?? null);
								$newSnId = is_object($history) ? ($history->new_sn_id ?? null) : ($history['new_sn_id'] ?? null);
								$createdAt = is_object($history) ? ($history->created_at ?? '') : ($history['created_at'] ?? '');
								?>
								<li class="list-group-item px-0 d-flex justify-content-between">
									<span>
										<strong><?= esc(ucfirst($action)) ?></strong> &mdash;
										<?= esc($oldSnText ?? ('ID: ' . $oldSnId)) ?>
										<?php if (!empty($oldSnReplaced)) : ?>
											<small class="text-muted">(diganti dengan: <?= esc($oldSnReplaced) ?>)</small>
										<?php endif; ?>
										→ <?= esc($newSnText ?? ('ID: ' . $newSnId)) ?>
									</span>
									<small class="text-muted"><?= esc(tgl_indo3($createdAt)) ?></small>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>

			<div class="col-lg-4">
				<?php if (!empty($claim->photo_path)) : ?>
					<div class="info-section text-center mb-4">
						<h6 class="mb-3"><i class="fas fa-image me-2 text-primary"></i>Foto Bukti</h6>
						<img src="<?= $config->baseURL . 'public/uploads/' . esc($claim->photo_path) ?>" alt="Bukti" class="img-fluid rounded shadow-sm">
					</div>
				<?php endif; ?>

				<div class="info-section mb-4">
					<h6 class="mb-3"><i class="fas fa-warehouse me-2 text-primary"></i>Rekonsiliasi Stok</h6>
					<?php if (!empty($reconciliation)) : ?>
						<dl class="row mb-0">
							<dt class="col-sm-5">Dari</dt>
							<dd class="col-sm-7"><?= esc($reconciliation->from_store_id ?? '-') ?></dd>
							<dt class="col-sm-5">Ke</dt>
							<dd class="col-sm-7"><?= esc($reconciliation->to_store_id ?? '-') ?></dd>
							<dt class="col-sm-5">Tanggal</dt>
							<dd class="col-sm-7"><?= esc(tgl_indo3($reconciliation->reconciled_at)) ?></dd>
						</dl>
					<?php else : ?>
						<p class="text-muted mb-0">Belum ada data rekonsiliasi.</p>
					<?php endif; ?>
				</div>

				<div class="info-section">
					<h6 class="mb-3"><i class="fas fa-user me-2 text-primary"></i>Informasi Agen</h6>
					<dl class="row mb-0">
						<dt class="col-sm-5">ID Agen</dt>
						<dd class="col-sm-7"><?= esc($claim->agent_id ?? '-') ?></dd>
						<dt class="col-sm-5">Catatan</dt>
						<dd class="col-sm-7"><?= esc($claim->store_note ?? '-') ?></dd>
					</dl>
				</div>
			</div>
		</div>

		<div class="mt-4">
			<a href="<?= $config->baseURL ?>warranty/history" class="btn btn-light">
				<i class="fas fa-arrow-left me-1"></i> Kembali ke Riwayat
			</a>
		</div>
	</div>
</div>

