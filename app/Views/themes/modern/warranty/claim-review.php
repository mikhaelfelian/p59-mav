<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-20
 * Github: github.com/mikhaelfelian
 * Description: Warranty claim review view
 */
helper('date');
$statusBadges = [
	'pending'  => 'bg-warning text-dark',
	'approved' => 'bg-primary',
	'rejected' => 'bg-danger',
	'replaced' => 'bg-success',
	'invalid'  => 'bg-secondary'
];
?>
<div class="card shadow-sm border-0">
	<div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
		<div>
			<h5 class="card-title mb-0">
				<i class="fas fa-search me-2 text-primary"></i>
				<?= esc($title ?? 'Review Klaim Garansi') ?>
			</h5>
			<small class="text-muted">ID Klaim: #<?= esc($claim->id ?? '-') ?></small>
		</div>
		<span class="badge px-3 py-2 <?= $statusBadges[$claim->status] ?? 'bg-light text-dark' ?>">
			<?= ucfirst($claim->status) ?>
		</span>
	</div>
	<div class="card-body">
		<?php if (!empty($msg)) : ?>
			<?= show_alert($msg); ?>
		<?php endif; ?>

		<div class="row g-4">
			<div class="col-lg-8">
				<div class="info-section mb-4">
					<h6 class="mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Informasi Klaim</h6>
					<dl class="row mb-0">
						<dt class="col-sm-4">Agen Pengaju</dt>
						<dd class="col-sm-8"><?= esc($claim->agent_id ?? '-') ?></dd>

						<dt class="col-sm-4">Serial Number</dt>
						<dd class="col-sm-8"><?= esc($old_sn->sn ?? '-') ?></dd>

						<dt class="col-sm-4">Tanggal Klaim</dt>
						<dd class="col-sm-8"><?= esc(tgl_indo8($claim->created_at ?? date('Y-m-d H:i:s'))) ?></dd>

						<dt class="col-sm-4">Alasan Klaim</dt>
						<dd class="col-sm-8"><?= esc($claim->issue_reason ?? '-') ?></dd>
					</dl>
				</div>

				<div class="info-section mb-4">
					<h6 class="mb-3"><i class="fas fa-microchip me-2 text-primary"></i>Informasi Serial Number</h6>
					<dl class="row mb-0">
						<dt class="col-sm-4">Item</dt>
						<dd class="col-sm-8"><?= esc($item->name ?? '-') ?></dd>

						<dt class="col-sm-4">Tanggal Aktivasi</dt>
						<dd class="col-sm-8"><?= !empty($old_sn->activated_at) ? esc(tgl_indo8($old_sn->activated_at)) : '-' ?></dd>

						<dt class="col-sm-4">Masa Garansi Berakhir</dt>
						<dd class="col-sm-8"><?= !empty($old_sn->expired_at) ? esc(tgl_indo8($old_sn->expired_at)) : '-' ?></dd>

						<dt class="col-sm-4">Lokasi / Gudang</dt>
						<dd class="col-sm-8"><?= esc($claim->routed_store_id ?? '-') ?></dd>
					</dl>
				</div>

				<div class="info-section">
					<h6 class="mb-3"><i class="fas fa-shield-alt me-2 text-primary"></i>Status Validasi Sistem</h6>
					<dl class="row mb-0">
						<dt class="col-sm-4">Validasi Sistem</dt>
						<dd class="col-sm-8">
							<?php if ($claim->system_validated == 1) : ?>
								<span class="badge bg-success">Valid</span>
							<?php elseif ($claim->status === 'invalid') : ?>
								<span class="badge bg-danger">Invalid</span>
							<?php else : ?>
								<span class="badge bg-warning text-dark">Pending</span>
							<?php endif; ?>
						</dd>

						<?php if (!empty($claim->system_validation_note)) : ?>
							<dt class="col-sm-4">Catatan Validasi</dt>
							<dd class="col-sm-8"><?= esc($claim->system_validation_note) ?></dd>
						<?php endif; ?>
					</dl>
				</div>
			</div>

			<div class="col-lg-4">
				<?php if (!empty($claim->photo_path)) : ?>
					<div class="info-section text-center mb-4">
						<h6 class="mb-3"><i class="fas fa-image me-2 text-primary"></i>Foto Bukti</h6>
						<img src="<?= $config->baseURL . 'public/uploads/' . esc($claim->photo_path) ?>" alt="Bukti" class="img-fluid rounded shadow-sm">
					</div>
				<?php endif; ?>

				<?php if ($claim->status === 'pending' && $claim->system_validated == 1) : ?>
					<div class="info-section">
						<h6 class="mb-3"><i class="fas fa-check-circle me-2 text-primary"></i>Aksi</h6>

						<form action="<?= $config->baseURL ?>warranty/approve/<?= esc($claim->id) ?>" method="post" class="mb-3">
							<?= csrf_field(); ?>
							<div class="mb-3">
								<label class="form-label">Serial Number Pengganti <span class="text-danger">*</span></label>
								<input type="number" name="new_sn_id" class="form-control" placeholder="Masukkan ID serial number baru" required>
								<small class="text-muted">Serial number akan diaktifkan dan garansi mengikuti sisa waktu serial lama</small>
							</div>
							<div class="mb-3">
								<label class="form-label">Catatan (Opsional)</label>
								<textarea name="store_note" class="form-control" rows="2" placeholder="Tambahkan catatan jika diperlukan"><?= set_value('store_note', $claim->store_note ?? '') ?></textarea>
							</div>
							<button type="submit" class="btn btn-success w-100">
								<i class="fas fa-check me-1"></i> Setujui & Proses Penggantian
							</button>
						</form>

						<form action="<?= $config->baseURL ?>warranty/reject/<?= esc($claim->id) ?>" method="post" onsubmit="return confirm('Yakin menolak klaim ini?');">
							<?= csrf_field(); ?>
							<div class="mb-3">
								<label class="form-label">Catatan Penolakan <span class="text-danger">*</span></label>
								<textarea name="store_note" class="form-control" rows="2" placeholder="Berikan alasan penolakan" required><?= set_value('store_note') ?></textarea>
							</div>
							<button type="submit" class="btn btn-danger w-100">
								<i class="fas fa-times me-1"></i> Tolak Klaim
							</button>
						</form>
					</div>
				<?php elseif ($claim->status === 'pending' && $claim->system_validated != 1) : ?>
					<div class="alert alert-warning">
						<i class="fas fa-exclamation-triangle me-2"></i>
						Klaim menunggu validasi sistem. Validasi akan dilakukan otomatis setelah klaim dibuat.
					</div>
				<?php endif; ?>
			</div>
		</div>


		<div class="mt-4 d-flex justify-content-between">
			<a href="<?= $config->baseURL ?>warranty/history" class="btn btn-light">
				<i class="fas fa-arrow-left me-1"></i> Kembali
			</a>
		</div>
	</div>
</div>

