<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-11
 * Github: github.com/mikhaelfelian
 * Description: View for activating serial number
 */
?>
<style>
.activation-header {
	/* Default blue - will be overridden by color scheme CSS */
	background-color: #1976d2;
	color: white;
	padding: 1.5rem;
	border-radius: 8px 8px 0 0;
}

/* Match header background color from color scheme */
/* Each color scheme CSS file sets header { background: #color; } */
/* We'll use JavaScript to sync the colors, but provide fallbacks for each scheme */

.activation-header h5 {
	margin: 0;
	font-weight: 600;
	font-size: 1.5rem;
	color: white;
}

/* Dark theme support */
html[data-bs-theme="dark"] .activation-header {
	background-color: var(--computed-header-bg, #0a58ca);
	opacity: 0.9;
}

html[data-bs-theme="dark"] .activation-header h5 {
	color: white;
}

.activation-form-label {
	font-weight: 600;
	color: #495057;
	margin-bottom: 0.5rem;
	font-size: 0.875rem;
}

.activation-form-input {
	border: 1px solid #dee2e6;
	border-radius: 6px;
	padding: 0.5rem 0.75rem;
	transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.activation-form-input:focus {
	border-color: #0d6efd;
	box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
	outline: 0;
}

.dropzone-area {
	border: 2px dashed #dee2e6;
	border-radius: 8px;
	padding: 2rem;
	text-align: center;
	background-color: #f8f9fa;
	transition: all 0.3s ease;
	cursor: pointer;
	min-height: 200px;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
}

.dropzone-area:hover {
	border-color: #0d6efd;
	background-color: #e7f1ff;
}

.dropzone-area.dragover {
	border-color: #0d6efd;
	background-color: #cfe2ff;
}

.dropzone-icon {
	font-size: 3rem;
	color: #6c757d;
	margin-bottom: 1rem;
}

.dropzone-text {
	color: #6c757d;
	font-size: 0.9rem;
	margin-bottom: 0.5rem;
}

.dropzone-hint {
	color: #adb5bd;
	font-size: 0.8rem;
}

.file-preview {
	margin-top: 1rem;
	padding: 1rem;
	background: #ffffff;
	border: 1px solid #dee2e6;
	border-radius: 6px;
	display: none;
}

.file-preview img {
	max-width: 100%;
	max-height: 200px;
	border-radius: 4px;
}

.file-preview-info {
	margin-top: 0.5rem;
	font-size: 0.85rem;
	color: #6c757d;
}

.form-section {
	background: #ffffff;
	border-radius: 8px;
	padding: 1.5rem;
	border: 1px solid #e9ecef;
	box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
</style>

<div class="card shadow-sm border-0">
	<div class="activation-header">
		<h5><i class="fas fa-check-circle me-2"></i>Form Aktivasi SN</h5>
	</div>
	<div class="card-body p-4">
		<?php if (!empty($msg)): ?>
			<?= show_message($msg) ?>
		<?php endif; ?>

		<?= form_open('agent/sales/activateSN/' . ($sn['id'] ?? ''), ['class' => 'needs-validation', 'id' => 'formActivateSN', 'enctype' => 'multipart/form-data', 'novalidate' => '']) ?>
			<input type="hidden" name="sn_id" value="<?= $sn['id'] ?? '' ?>">
			
			<div class="row">
				<!-- Left Column -->
				<div class="col-md-6">
					<div class="form-section mb-3">
						<!-- SN Field -->
						<div class="mb-3">
							<label class="activation-form-label">SN</label>
							<input type="text" class="form-control activation-form-input" value="<?= esc($sn['sn'] ?? '') ?>" readonly>
						</div>

						<!-- No HP -->
						<div class="mb-3">
							<label class="activation-form-label">No HP</label>
							<input type="text" class="form-control activation-form-input" name="no_hp" id="no_hp" 
								value="<?= set_value('no_hp', $sn['no_hp'] ?? '') ?>" 
								placeholder="Masukkan nomor HP" maxlength="20">
						</div>

						<!-- No Plat (Segmented) -->
						<div class="mb-3">
							<label class="activation-form-label">No Plat</label>
							<div class="row g-2">
								<div class="col-4">
									<input type="text" class="form-control activation-form-input" name="plat_code" id="plat_code" 
										value="<?= set_value('plat_code', $sn['plat_code'] ?? '') ?>" 
										placeholder="Code" maxlength="10">
								</div>
								<div class="col-4">
									<input type="text" class="form-control activation-form-input" name="plat_number" id="plat_number" 
										value="<?= set_value('plat_number', $sn['plat_number'] ?? '') ?>" 
										placeholder="Number" maxlength="10">
								</div>
								<div class="col-4">
									<input type="text" class="form-control activation-form-input" name="plat_last" id="plat_last" 
										value="<?= set_value('plat_last', $sn['plat_last'] ?? '') ?>" 
										placeholder="Suffix" maxlength="10">
								</div>
							</div>
						</div>

						<!-- Photo Upload (Drag & Drop) -->
						<div class="mb-3">
							<label class="activation-form-label">No Plat (Foto)</label>
							<div class="dropzone-area" id="dropzoneArea">
								<i class="fas fa-cloud-upload-alt dropzone-icon"></i>
								<div class="dropzone-text">Drag & Drop foto di sini</div>
								<div class="dropzone-hint">atau klik untuk memilih file</div>
								<input type="file" name="file" id="fileInput" accept="image/*" style="display: none;">
							</div>
							<div class="file-preview" id="filePreview">
								<img id="previewImage" src="" alt="Preview">
								<div class="file-preview-info" id="fileInfo"></div>
								<button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeFile()">
									<i class="fas fa-times me-1"></i>Hapus
								</button>
							</div>
							<?php if (!empty($sn['file'])): ?>
								<div class="mt-2">
									<small class="text-muted">File yang sudah diupload:</small>
									<div class="mt-1">
										<img src="<?= base_url('public/uploads/' . $sn['file']) ?>" class="img-thumbnail" style="max-width: 200px; max-height: 200px;" alt="Current file">
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<!-- Right Column -->
				<div class="col-md-6">
					<div class="form-section mb-3">
						<!-- Tanggal Aktif -->
						<div class="mb-3">
							<label class="activation-form-label">Tanggal Aktif <span class="text-danger">*</span></label>
							<input type="date" class="form-control activation-form-input" name="activated_at" id="activated_at" 
								value="<?= set_value('activated_at', !empty($sn['activated_at']) ? date('Y-m-d', strtotime($sn['activated_at'])) : '') ?>" 
								required>
						</div>

						<!-- Tanggal Exp -->
						<div class="mb-3">
							<label class="activation-form-label">Tanggal Exp</label>
							<input type="date" class="form-control activation-form-input" name="expired_at" id="expired_at" 
								value="<?= set_value('expired_at', !empty($sn['expired_at']) ? date('Y-m-d', strtotime($sn['expired_at'])) : '') ?>">
						</div>
					</div>
				</div>
			</div>

			<div class="row mt-4">
				<div class="col-12 d-flex gap-3">
					<a href="<?= $config->baseURL ?>agent/sales/sn" class="btn btn-secondary">
						<i class="fas fa-arrow-left me-1"></i>Kembali
					</a>
					<button type="submit" class="btn btn-primary">
						<i class="fas fa-check me-1"></i>Simpan
					</button>
				</div>
			</div>
		<?= form_close() ?>
	</div>
</div>

<script>
// Drag & Drop File Upload
var dropzoneArea = document.getElementById('dropzoneArea');
var fileInput = document.getElementById('fileInput');
var filePreview = document.getElementById('filePreview');
var previewImage = document.getElementById('previewImage');
var fileInfo = document.getElementById('fileInfo');

if (dropzoneArea && fileInput) {
	// Click to select file
	dropzoneArea.addEventListener('click', function() {
		fileInput.click();
	});

	// File input change
	fileInput.addEventListener('change', function(e) {
		handleFileSelect(e.target.files[0]);
	});

	// Drag & Drop events
	dropzoneArea.addEventListener('dragover', function(e) {
		e.preventDefault();
		dropzoneArea.classList.add('dragover');
	});

	dropzoneArea.addEventListener('dragleave', function(e) {
		e.preventDefault();
		dropzoneArea.classList.remove('dragover');
	});

	dropzoneArea.addEventListener('drop', function(e) {
		e.preventDefault();
		dropzoneArea.classList.remove('dragover');
		
		if (e.dataTransfer.files.length > 0) {
			var file = e.dataTransfer.files[0];
			// Create a new FileList-like object and assign to input
			var dataTransfer = new DataTransfer();
			dataTransfer.items.add(file);
			fileInput.files = dataTransfer.files;
			handleFileSelect(file);
		}
	});
}

function handleFileSelect(file) {
	if (!file) return;

	// Validate file type
	if (!file.type.match('image.*')) {
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				icon: 'error',
				title: 'Format File Tidak Valid',
				text: 'Hanya file gambar yang diperbolehkan (JPG, PNG, GIF)'
			});
		} else {
			alert('Hanya file gambar yang diperbolehkan');
		}
		return;
	}

	// Validate file size (5MB)
	if (file.size > 5242880) {
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				icon: 'error',
				title: 'Ukuran File Terlalu Besar',
				text: 'Ukuran file maksimal 5MB'
			});
		} else {
			alert('Ukuran file maksimal 5MB');
		}
		return;
	}

	// Show preview
	var reader = new FileReader();
	reader.onload = function(e) {
		previewImage.src = e.target.result;
		fileInfo.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
		filePreview.style.display = 'block';
	};
	reader.readAsDataURL(file);
}

function removeFile() {
	fileInput.value = '';
	filePreview.style.display = 'none';
	previewImage.src = '';
	fileInfo.textContent = '';
}

function formatFileSize(bytes) {
	if (bytes === 0) return '0 Bytes';
	var k = 1024;
	var sizes = ['Bytes', 'KB', 'MB', 'GB'];
	var i = Math.floor(Math.log(bytes) / Math.log(k));
	return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Form validation
(function() {
	'use strict';
	var form = document.getElementById('formActivateSN');
	if (form) {
		form.addEventListener('submit', function(event) {
			if (!form.checkValidity()) {
				event.preventDefault();
				event.stopPropagation();
			}
			form.classList.add('was-validated');
		}, false);
	}
})();

// Sync activation header color with main header color scheme
(function() {
	var header = document.querySelector('header.nav-header');
	var activationHeader = document.querySelector('.activation-header');
	
	if (header && activationHeader) {
		var headerBg = window.getComputedStyle(header).backgroundColor;
		if (headerBg && headerBg !== 'rgba(0, 0, 0, 0)' && headerBg !== 'transparent') {
			activationHeader.style.backgroundColor = headerBg;
		}
	}
})();
</script>

