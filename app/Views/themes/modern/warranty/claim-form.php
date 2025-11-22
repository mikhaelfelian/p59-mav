<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-20
 * Github: github.com/mikhaelfelian
 * Description: Warranty claim submission form
 */
?>
<style>
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
</style>
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

			<div class="col-12">
				<label class="form-label fw-semibold">
					Foto Bukti (Opsional)
				</label>
				<div class="dropzone-area" id="dropzoneArea">
					<i class="fas fa-cloud-upload-alt dropzone-icon"></i>
					<div class="dropzone-text">Drag & Drop foto di sini</div>
					<div class="dropzone-hint">atau klik untuk memilih file</div>
					<input type="file" name="photo" id="photoInput" accept="image/jpeg,image/jpg,image/png,image/gif" style="display: none;">
				</div>
				<div class="file-preview" id="filePreview">
					<img id="previewImage" src="" alt="Preview">
					<div class="file-preview-info" id="fileInfo"></div>
					<button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeFile()">
						<i class="fas fa-times me-1"></i>Hapus
					</button>
				</div>
				<small class="text-muted d-block mt-2">
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

	// Drag & Drop File Upload
	var dropzoneArea = document.getElementById('dropzoneArea');
	var fileInput = document.getElementById('photoInput');
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
</script>

