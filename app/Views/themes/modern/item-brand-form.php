<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-21
 * Github: github.com/mikhaelfelian
 * Description: View for item brand form (add/edit) with AJAX POST and CI 4.3.1 form helpers
 * This file represents the View for item-brand-form.
 */

helper('form');
$isModal = $isModal ?? false;
?>
<?php if (!$isModal): ?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	<div class="card-body">
<?php endif; ?>
		<?php
			if (!empty($message)) {
				show_message($message);
			} ?>
		<?= form_open('', ['class' => 'form-horizontal p-3', 'id' => 'form-item-brand']) ?>
			<div class="row mb-3">
				<!-- Label for Name in Indonesian -->
				<label class="col-sm-3 col-form-label">Nama Brand <span class="text-danger">*</span></label>
				<div class="col-sm-9">
					<?= form_input([
						'name' => 'name',
						'class' => 'form-control',
						'value' => set_value('name', @$item_brand->name ?? ''),
						'placeholder' => 'Masukkan nama brand',
						'required' => 'required'
					]) ?>
				</div>
			</div>
			<div class="row mb-3">
				<!-- Label for Slug in Indonesian -->
				<label class="col-sm-3 col-form-label">Slug</label>
				<div class="col-sm-9">
					<?= form_input([
						'name' => 'slug',
						'class' => 'form-control',
						'value' => set_value('slug', @$item_brand->slug ?? ''),
						'placeholder' => 'Masukkan slug brand'
					]) ?>
				</div>
			</div>
			<div class="row mb-3">
				<!-- Label for Description in Indonesian -->
				<label class="col-sm-3 col-form-label">Deskripsi</label>
				<div class="col-sm-9">
					<?= form_textarea([
						'name' => 'description',
						'class' => 'form-control',
						'value' => set_value('description', @$item_brand->description ?? ''),
						'placeholder' => 'Masukkan deskripsi brand',
						'rows' => 3
					]) ?>
				</div>
			</div>
			<div class="row mb-3">
				<!-- Label for Status in Indonesian -->
				<label class="col-sm-3 col-form-label">Status</label>
				<div class="col-sm-9">
					<?= form_dropdown('status', [
						'1' => 'Aktif',
						'0' => 'Tidak Aktif'
					], set_value('status', @$item_brand->status ?? '1'), [
						'class' => 'form-control'
					]) ?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-9 offset-sm-3">
					<?= form_hidden('id', @$id ?? '') ?>
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
    $('#form-item-brand').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var submitBtn = $('#btn-submit');
        var originalText = submitBtn.text();
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).text('Memproses...');
        
        $.ajax({
            url: '<?=current_url(true)?>',
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
                        window.location.href = '<?=$config->baseURL?>item-brand';
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
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>
<?php endif; ?>