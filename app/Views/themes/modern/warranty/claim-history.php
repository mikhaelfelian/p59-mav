<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-20
 * Github: github.com/mikhaelfelian
 * Description: Warranty claim history list
 */
?>
<div class="card shadow-sm border-0">
	<div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
		<div>
			<h5 class="card-title mb-0">
				<i class="fas fa-history me-2 text-primary"></i>
				<?= esc($title ?? 'Riwayat Klaim Garansi') ?>
			</h5>
			<small class="text-muted">Menampilkan seluruh pengajuan klaim garansi Anda.</small>
		</div>
		<a href="<?= $config->baseURL ?>warranty/claim" class="btn btn-primary">
			<i class="fas fa-plus me-1"></i> Ajukan Klaim
		</a>
	</div>
	<div class="card-body">
		<?php if (!empty($msg)) : ?>
			<?= show_alert($msg); ?>
		<?php endif; ?>

		<?php if (empty($claims)) : ?>
			<div class="alert alert-info mb-0">
				Belum ada data klaim garansi.
			</div>
		<?php else : ?>
			<div class="table-responsive">
				<table class="table table-bordered align-middle">
					<thead class="table-light">
						<tr>
							<th width="60">No</th>
							<th>ID Klaim</th>
							<th>Serial Number</th>
							<th>Agen</th>
							<th>Alasan</th>
							<th>Status</th>
							<th>Tanggal</th>
							<th width="120">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($claims as $index => $claim) : ?>
							<tr>
								<td><?= $index + 1; ?></td>
								<td>#<?= esc($claim->id) ?></td>
								<td><?= esc($claim->serial_number ?? '-') ?></td>
								<td><?= esc($claim->agent_name ?? '-') ?></td>
								<td><?= esc($claim->issue_reason ?? '-') ?></td>
								<td>
									<span class="badge bg-<?= $claim->status === 'replaced' ? 'success' : ($claim->status === 'rejected' ? 'danger' : 'primary'); ?>">
										<?= ucfirst($claim->status) ?>
									</span>
								</td>
								<td><?= esc(tgl_indo8($claim->created_at ?? date('Y-m-d H:i:s'))) ?></td>
								<td>
									<a href="<?= $config->baseURL ?>warranty/detail/<?= esc($claim->id) ?>" class="btn btn-sm btn-outline-primary">
										<i class="fas fa-eye"></i>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>

