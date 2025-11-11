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
		
		<?= form_open('', ['class' => 'form-horizontal p-3', 'id' => 'form-platform', 'enctype' => 'multipart/form-data']) ?>
		<?= form_hidden('id', @$id ?? '') ?>
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
					<div class="form-check form-switch">
						<?= form_checkbox([
							'name' => 'status',
							'class' => 'form-check-input',
							'value' => '1',
							'id' => 'status',
							'checked' => set_value('status', @$platform['status'] ?? '0') === '1'
						]) ?>
						<label class="form-check-label" for="status">
							Aktifkan Platform
						</label>
					</div>
					<small class="text-muted">Status aktif/non-aktif platform</small>
				</div>
			</div>			
			<div class="row mb-3">
				<label class="col-sm-3 col-form-label">Status Kredit</label>
				<div class="col-sm-9">
					<div class="form-check form-switch">
						<?= form_checkbox([
							'name' => 'status_kredit',
							'class' => 'form-check-input',
							'value' => '1',
							'id' => 'status_kredit',
							'checked' => set_value('status_kredit', @$platform['status_kredit'] ?? '0') === '1'
						]) ?>
						<label class="form-check-label" for="status_kredit">
							Izinkan transaksi kredit
						</label>
					</div>
					<small class="text-muted">Aktifkan bila platform mendukung penjualan kredit/cicilan.</small>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-form-label">Aktif di agen</label>
				<div class="col-sm-9">
					<div class="form-check form-switch">
						<?= form_checkbox([
							'name' => 'status_agent',
							'class' => 'form-check-input',
							'value' => '1',
							'id' => 'status_agent',
							'checked' => set_value('status_agent', @$platform['status_agent'] ?? '0') === '1'
						]) ?>
						<label class="form-check-label" for="status_agent">
							Aktifkan untuk Agent
						</label>
					</div>
					<small class="text-muted">Aktifkan platform untuk digunakan di agent/post</small>
				</div>
			</div>			
			<div class="row mb-3">
				<label class="col-sm-3 col-form-label">Aktif di POS</label>
				<div class="col-sm-9">
					<div class="form-check form-switch">
						<?= form_checkbox([
							'name' => 'status_pos',
							'class' => 'form-check-input',
							'value' => '1',
							'id' => 'status_pos',
							'checked' => set_value('status_pos', @$platform['status_pos'] ?? '0') === '1'
						]) ?>
						<label class="form-check-label" for="status_pos">
							Aktifkan untuk POS
						</label>
					</div>
					<small class="text-muted">Aktifkan platform untuk digunakan di POS</small>
				</div>
			</div>
			
			<!-- Gateway Payment Section -->
			<div class="card shadow-sm border-0 mb-3">
				<div class="card-header bg-primary text-white">
					<h6 class="card-title mb-0">
						<i class="fas fa-credit-card me-2"></i>Gateway Pembayaran
					</h6>
				</div>
				<div class="card-body">
					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Kode Gateway <span class="text-danger">*</span></label>
						<div class="col-sm-9">
							<?= form_input([
								'name' => 'gw_code',
								'class' => 'form-control',
								'value' => set_value('gw_code', @$platform['gw_code'] ?? ''),
								'placeholder' => 'Contoh: midtrans, stripe, dll',
								'maxlength' => '50'
							]) ?>
							<small class="text-muted">Kode unik untuk gateway pembayaran (contoh: midtrans, stripe)</small>
						</div>
					</div>
					
					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Status Gateway</label>
						<div class="col-sm-9">
							<div class="form-check form-switch">
								<?= form_checkbox([
									'name' => 'gw_status',
									'class' => 'form-check-input',
									'value' => '1',
									'id' => 'gw_status',
									'checked' => set_value('gw_status', @$platform['gw_status'] ?? '0') === '1'
								]) ?>
								<label class="form-check-label" for="gw_status">
									Aktifkan Gateway
								</label>
							</div>
							<small class="text-muted">Aktifkan gateway pembayaran untuk digunakan di agent/post</small>
							<div class="mt-2" id="gateway-status-container" style="display: <?= !empty($platform['gw_code']) ? 'block' : 'none' ?>;">
								<?php 
								$isActive = (!empty($platform['gw_code']) 
									&& (@$platform['status'] ?? '0') == '1' 
									&& (@$platform['status_agent'] ?? '0') == '1' 
									&& (@$platform['gw_status'] ?? '0') == '1');
								$badgeClass = $isActive ? 'bg-success' : 'bg-secondary';
								$badgeText = $isActive ? 'Aktif untuk Agent/Post' : 'Tidak Aktif';
								?>
								<span class="badge <?= $badgeClass ?>" id="gateway-status-badge">
									<i class="fas fa-<?= $isActive ? 'check-circle' : 'times-circle' ?> me-1"></i>
									<?= $badgeText ?>
								</span>
								<button type="button" class="btn btn-sm btn-outline-primary ms-2" id="btn-check-gateway" title="Cek Status Gateway">
									<i class="fas fa-sync-alt"></i> Cek Status
								</button>
							</div>
						</div>
					</div>
					
					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Logo Gateway</label>
						<div class="col-sm-9">
							<?php if (!empty($platform['logo']) && file_exists(ROOTPATH . 'public/uploads/platform/' . $platform['logo'])): ?>
								<div class="mb-2">
									<img src="<?= base_url('public/uploads/platform/' . $platform['logo']) ?>" 
										alt="Logo" 
										class="img-thumbnail" 
										style="max-width: 150px; max-height: 150px;"
										id="logo-preview">
								</div>
							<?php else: ?>
								<div class="mb-2">
									<img src="" alt="Preview" class="img-thumbnail d-none" 
										style="max-width: 150px; max-height: 150px;"
										id="logo-preview">
								</div>
							<?php endif; ?>
							
							<?= form_upload([
								'name' => 'logo',
								'class' => 'form-control',
								'id' => 'logo-upload',
								'accept' => 'image/*'
							]) ?>
							<small class="text-muted">Upload logo gateway (format: JPG, PNG, maksimal 2MB)</small>
							<?php if (!empty($platform['logo'])): ?>
								<?= form_hidden('logo_old', $platform['logo']) ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			
		<?= form_close() ?>
<?php if (!$isModal): ?>
	</div>

</div>
<?php endif; ?>

<script>
(function($){
	"use strict";

	function getForm($element) {
		var $form = $element.closest('form');
		if ($form.length === 0) {
			$form = $('#form-platform');
		}
		return $form;
	}

	function ensureGatewayContainer($form) {
		var gwCode = $form.find('input[name="gw_code"]').val();
		var $container = $form.find('#gateway-status-container');

		if (gwCode && gwCode.trim() !== '') {
			$container.show();
			if ($form.find('#gateway-status-badge').length === 0) {
				$container.html(
					'<span class="badge bg-secondary" id="gateway-status-badge">' +
					'<i class="fas fa-times-circle me-1"></i>Tidak Aktif' +
					'</span>' +
					'<button type="button" class="btn btn-sm btn-outline-primary ms-2" id="btn-check-gateway" title="Cek Status Gateway">' +
					'<i class="fas fa-sync-alt"></i> Cek Status' +
					'</button>'
				);
			}
		} else {
			$container.hide();
		}
	}

	function updateGatewayBadge($form, isActive) {
		var $badge = $form.find('#gateway-status-badge');
		if (!$badge.length) {
			return;
		}
		if (isActive) {
			$badge.removeClass('bg-secondary').addClass('bg-success')
				.html('<i class="fas fa-check-circle me-1"></i>Aktif untuk Agent/Post');
		} else {
			$badge.removeClass('bg-success').addClass('bg-secondary')
				.html('<i class="fas fa-times-circle me-1"></i>Tidak Aktif');
		}
	}

	function syncGatewayWithKredit($form) {
		var $kredit = $form.find('#status_kredit');
		var $gatewaySwitch = $form.find('#gw_status');
		var $wrapper = $gatewaySwitch.closest('.form-check');

		if (!$kredit.length || !$gatewaySwitch.length) {
			return;
		}

		if ($kredit.is(':checked')) {
			$gatewaySwitch.prop('checked', false).prop('disabled', true).trigger('change');
			$wrapper.addClass('opacity-50 pe-none');
			updateGatewayBadge($form, false);
		} else {
			$gatewaySwitch.prop('disabled', false).trigger('change');
			$wrapper.removeClass('opacity-50 pe-none');
		}
	}

	$(document).on('change', '#logo-upload', function(e) {
		var file = e.target.files[0];
		var $form = getForm($(this));
		var $preview = $form.find('#logo-preview');
		if (file) {
			var reader = new FileReader();
			reader.onload = function(ev) {
				$preview.attr('src', ev.target.result).removeClass('d-none');
			};
			reader.readAsDataURL(file);
		}
	});

	$(document).on('click', '#btn-check-gateway', function() {
		var $form = getForm($(this));
		var gwCode = $form.find('input[name="gw_code"]').val();

		if (!gwCode) {
			Swal.fire({
				icon: 'warning',
				title: 'Peringatan',
				text: 'Silakan masukkan Kode Gateway terlebih dahulu'
			});
			return;
		}

		var $btn = $(this);
		var originalHtml = $btn.html();
		$btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengecek...');

		$.ajax({
			url: '<?= $config->baseURL ?>agent/getGatewayByCode',
			type: 'POST',
			data: { gw_code: gwCode },
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success' && response.data) {
					var isActive = response.is_active;
					updateGatewayBadge($form, isActive);
					Swal.fire({
						icon: isActive ? 'success' : 'info',
						title: isActive ? 'Gateway Aktif' : 'Gateway Tidak Aktif',
						text: isActive
							? 'Gateway ini dapat digunakan di agent/post'
							: 'Pastikan Status dan Status Gateway diaktifkan',
						timer: 2000,
						showConfirmButton: false
					});
				} else {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: response.message || 'Gateway tidak ditemukan'
					});
				}
			},
			error: function(xhr) {
				var errorMsg = 'Terjadi kesalahan saat mengecek status gateway';
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMsg = xhr.responseJSON.message;
				}
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: errorMsg
				});
			},
			complete: function() {
				$btn.prop('disabled', false).html(originalHtml);
			}
		});
	});

	$(document).on('input blur', 'input[name="gw_code"]', function() {
		var $form = getForm($(this));
		ensureGatewayContainer($form);
	});

	$(document).on('change', '#status_kredit', function() {
		var $form = getForm($(this));
		syncGatewayWithKredit($form);
	});

	$(document).on('submit', '#form-platform', function(e) {
		e.preventDefault();
		var $form = $(this);
		var formData = new FormData(this);

		formData.set('status', $form.find('#status').is(':checked') ? '1' : '0');
		formData.set('status_agent', $form.find('#status_agent').is(':checked') ? '1' : '0');
		formData.set('status_pos', $form.find('#status_pos').is(':checked') ? '1' : '0');
		formData.set('status_kredit', $form.find('#status_kredit').is(':checked') ? '1' : '0');
		formData.set('gw_status', $form.find('#status_kredit').is(':checked') ? '0' : ($form.find('#gw_status').is(':checked') ? '1' : '0'));

		var $submitBtn = $form.find('#btn-submit');
		var originalText = $submitBtn.html();
		$submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Memproses...');

		$.ajax({
			url: '<?= $config->baseURL ?>platform/store',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success') {
					Swal.fire({
						icon: 'success',
						title: 'Berhasil!',
						text: response.message,
						timer: 2000,
						showConfirmButton: false
					}).then(function() {
						window.location.href = '<?= $config->baseURL ?>platform';
					});
				} else {
					Swal.fire({
						icon: 'error',
						title: 'Error!',
						text: response.message
					});
				}
			},
			error: function(xhr) {
				var errorMessage = 'Terjadi kesalahan saat memproses permintaan.';
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMessage = xhr.responseJSON.message;
				}
				Swal.fire({
					icon: 'error',
					title: 'Error!',
					text: errorMessage
				});
			},
			complete: function() {
				$submitBtn.prop('disabled', false).html(originalText);
			}
		});
	});

	$(function() {
		var $form = $('#form-platform');
		if ($form.length) {
			ensureGatewayContainer($form);
			syncGatewayWithKredit($form);
		}
	});

})(jQuery);
</script>
