<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: View for platform form (add/edit) with Bootstrap 5 layout
 */
helper('form');
$isModal = $isModal ?? false;
?>
<?php if (!$isModal): ?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?= $title ?? 'Form Platform' ?></h5>
	</div>
	<div class="card-body">
<?php endif; ?>
		<?php
		if (!empty($message)) {
			if (is_array($message)) {
				show_message($message);
			} else {
				echo '<div class="alert alert-info">' . esc($message) . '</div>';
			}
		}
		?>
		
		<?= form_open('', ['class' => 'form-horizontal p-3', 'id' => 'form-platform']) ?>
			<div class="row mb-3">
				<label class="col-sm-3 col-form-label">Kode</label>
				<div class="col-sm-9">
					<?= form_input([
						'name' => 'code',
						'class' => 'form-control',
						'value' => set_value('code', @$platform['code'] ?? ''),
						'placeholder' => 'Masukkan kode platform',
						'maxlength' => '160'
					]) ?>
					<small class="text-muted">Kode unik untuk platform (opsional)</small>
				</div>
			</div>
			
			<div class="row mb-3">
				<label class="col-sm-3 col-form-label">Nama Platform <span class="text-danger">*</span></label>
				<div class="col-sm-9">
					<?= form_input([
						'name' => 'platform',
						'class' => 'form-control',
						'value' => set_value('platform', @$platform['platform'] ?? ''),
						'placeholder' => 'Masukkan nama platform',
						'maxlength' => '160',
						'required' => 'required'
					]) ?>
					<small class="text-muted">Nama platform (contoh: Tokopedia, Shopee, dll.)</small>
				</div>
			</div>
			
			<div class="row mb-3">
				<label class="col-sm-3 col-form-label">Deskripsi</label>
				<div class="col-sm-9">
					<?= form_textarea([
						'name' => 'description',
						'class' => 'form-control',
						'value' => set_value('description', @$platform['description'] ?? ''),
						'placeholder' => 'Masukkan deskripsi platform',
						'rows' => 4
					]) ?>
					<small class="text-muted">Deskripsi atau informasi tambahan tentang platform</small>
				</div>
			</div>
			
			<div class="row mb-3">
				<label class="col-sm-3 col-form-label">Status</label>
				<div class="col-sm-9">
					<?= form_dropdown('status', [
						'1' => 'Aktif',
						'0' => 'Tidak Aktif'
					], set_value('status', @$platform['status'] ?? '1'), [
						'class' => 'form-control'
					]) ?>
					<small class="text-muted">Status aktif/non-aktif platform</small>
				</div>
			</div>
			
			<div class="row mb-3">
				<label class="col-sm-3 col-form-label">Status Sistem</label>
				<div class="col-sm-9">
					<div class="form-check form-switch">
						<?= form_checkbox([
							'name' => 'status_sys',
							'class' => 'form-check-input',
							'value' => '1',
							'checked' => set_checkbox('status_sys', '1', (@$platform['status_sys'] ?? '0') == '1')
						]) ?>
						<label class="form-check-label" for="status_sys">
							Aktifkan sebagai platform sistem
						</label>
					</div>
					<small class="text-muted">Platform sistem tidak dapat dihapus dan digunakan untuk integrasi internal</small>
				</div>
			</div>
			
		<?= form_close() ?>
<?php if (!$isModal): ?>
	</div>
</div>
<?php endif; ?>

<?php if (!$isModal): ?>
<script>
$(document).ready(function() {
	// Handle form submit using AJAX
	$('#form-platform').on('submit', function(e) {
		e.preventDefault();
		
		var formData = $(this).serialize();
		var submitBtn = $('#btn-submit');
		var originalText = submitBtn.html();
		
		// Disable submit button and show loading
		submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Memproses...');
		
		$.ajax({
			url: '<?= $config->baseURL ?>platform/store',
			type: 'POST',
			data: formData,
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success') {
					// Show success message
					Swal.fire({
						icon: 'success',
						title: 'Berhasil!',
						text: response.message,
						timer: 2000,
						showConfirmButton: false
					}).then(function() {
						// Redirect to list page
						window.location.href = '<?= $config->baseURL ?>platform';
					});
				} else {
					// Show error message
					Swal.fire({
						icon: 'error',
						title: 'Error!',
						text: response.message
					});
				}
			},
			error: function(xhr, status, error) {
				// Show error message
				Swal.fire({
					icon: 'error',
					title: 'Error!',
					text: 'Terjadi kesalahan saat memproses permintaan.'
				});
			},
			complete: function() {
				// Re-enable submit button
				submitBtn.prop('disabled', false).html(originalText);
			}
		});
	});
});
</script>
<?php endif; ?>

