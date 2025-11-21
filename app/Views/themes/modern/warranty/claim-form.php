<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-20
 * Github: github.com/mikhaelfelian
 * Description: Warranty claim submission form
 */
?>
<div class="card shadow-sm border-0">
	<div class="card-header bg-white py-3">
		<h5 class="card-title mb-0">
			<i class="fas fa-clipboard-list me-2 text-primary"></i>
			<?= esc($title ?? 'Form Klaim Garansi') ?>
		</h5>
	</div>
	<div class="card-body">
		<?php if (!empty($msg)) : ?>
			<?= show_alert($msg); ?>
		<?php endif; ?>

		<form action="<?= $config->baseURL ?>warranty/submit" method="post" enctype="multipart/form-data" class="row g-3 needs-validation" novalidate>
			<?= csrf_field(); ?>

			<div class="col-12">
				<label class="form-label fw-semibold">
					Serial Number <span class="text-danger">*</span>
				</label>
				<input type="text" name="serial_number" class="form-control" placeholder="Masukkan serial number" required value="<?= set_value('serial_number') ?>">
				<div class="invalid-feedback">
					Silakan masukkan serial number.
				</div>
			</div>

			<div class="col-12">
				<label class="form-label fw-semibold">
					Alasan Klaim <span class="text-danger">*</span>
				</label>
				<textarea name="issue_reason" class="form-control" rows="4" placeholder="Jelaskan masalah yang terjadi" required><?= set_value('issue_reason') ?></textarea>
				<div class="invalid-feedback">
					Silakan jelaskan alasan klaim.
				</div>
			</div>

			<div class="col-md-6">
				<label class="form-label fw-semibold">
					Foto Bukti (Opsional)
				</label>
				<input type="file" name="photo" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif">
				<small class="text-muted">
					Format: JPG, PNG, atau GIF. Maksimal 5MB.
				</small>
			</div>

			<?php if (!empty($agent_id)) : ?>
				<input type="hidden" name="agent_id" value="<?= esc($agent_id) ?>">
			<?php endif; ?>

			<div class="col-12 d-flex justify-content-end mt-3">
				<a href="<?= $config->baseURL ?>warranty/history" class="btn btn-light me-2">
					<i class="fas fa-arrow-left me-1"></i> Batal
				</a>
				<button type="submit" class="btn btn-primary">
					<i class="fas fa-paper-plane me-1"></i> Ajukan Klaim
				</button>
			</div>
		</form>
	</div>
</div>

<script>
	(() => {
		'use strict';
		const forms = document.querySelectorAll('.needs-validation');
		Array.prototype.slice.call(forms).forEach((form) => {
			form.addEventListener('submit', (event) => {
				if (!form.checkValidity()) {
					event.preventDefault();
					event.stopPropagation();
				}
				form.classList.add('was-validated');
			}, false);
		});
	})();
</script>

