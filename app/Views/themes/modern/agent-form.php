<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-24
 * Github: github.com/mikhaelfelian
 * Description: Professional agent form with tab-based layout similar to item-form.php
 */
helper('form');
$isModal = $isModal ?? false;
?>
<style>
	.agent-form-section {
		background: #fff;
		border-radius: 8px;
		padding: 1.5rem;
		margin-bottom: 1.5rem;
		box-shadow: 0 1px 3px rgba(0,0,0,0.1);
		border: 1px solid #e9ecef;
	}

	.form-label {
		font-weight: 500;
		color: #495057;
		margin-bottom: 0.5rem;
		font-size: 0.875rem;
	}

	.form-control:focus, .form-select:focus {
		border-color: #4e73df;
		box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
	}

	.card-header.bg-primary {
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
	}

	.card-header.bg-light {
		background-color: #f8f9fa !important;
		border-bottom: 2px solid #dee2e6;
	}

	.input-group-text {
		background-color: #f8f9fa;
		border-color: #ced4da;
		color: #6c757d;
	}

	.btn-lg {
		padding: 0.75rem 1.5rem;
		font-size: 1rem;
		font-weight: 500;
	}

	.nav-tabs .nav-link {
		color: #6c757d;
		font-weight: 500;
		border: none;
		border-bottom: 2px solid transparent;
		padding: 0.75rem 1.25rem;
	}

	.nav-tabs .nav-link:hover {
		border-color: #e9ecef;
		color: #495057;
	}

	.nav-tabs .nav-link.active {
		color: #4e73df;
		border-bottom-color: #4e73df;
		background-color: transparent;
	}

	.form-switch-lg .form-check-input {
		width: 3rem;
		height: 1.5rem;
	}

	.shadow-sm {
		box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
	}
</style>
<?php if (!$isModal): ?>
	<div class="card">
		<div class="card-header">
			<h5 class="card-title"><?= $title ?? 'Form Agen' ?></h5>
		</div>
		<div class="card-body">
		<?php endif; ?>

		<?php if (!empty($message)): ?>
			<?= show_message($message) ?>
		<?php endif; ?>

		<?= form_open('', ['class' => 'needs-validation', 'id' => 'form-agent', 'novalidate' => '']) ?>
		<?= form_hidden('id', @$id ?? '') ?>

		<!-- Tabs Navigation -->
		<ul class="nav nav-tabs" id="agentTabs" role="tablist">
			<li class="nav-item" role="presentation">
				<a class="nav-link active" data-bs-toggle="tab" href="#tab-info" role="tab">
					<i class="fas fa-info-circle me-1"></i> Informasi Dasar
				</a>
			</li>
			<li class="nav-item" role="presentation">
				<a class="nav-link" data-bs-toggle="tab" href="#tab-location" role="tab">
					<i class="fas fa-map-marker-alt me-1"></i> Lokasi
				</a>
			</li>
			<li class="nav-item" role="presentation">
				<a class="nav-link" data-bs-toggle="tab" href="#tab-business" role="tab">
					<i class="fas fa-briefcase me-1"></i> Bisnis
				</a>
			</li>
			<li class="nav-item" role="presentation">
				<a class="nav-link" data-bs-toggle="tab" href="#tab-user" role="tab">
					<i class="fas fa-users me-1"></i> Pengguna & Status
				</a>
			</li>
		</ul>

		<div class="tab-content mt-3">
			<!-- Informasi Dasar Tab -->
			<div class="tab-pane fade show active" id="tab-info" role="tabpanel">
				<div class="card shadow-sm border-0 mb-4">
					<div class="card-header bg-light">
						<h6 class="card-title mb-0">
							<i class="fas fa-info-circle me-2"></i>Informasi Dasar Agen
						</h6>
					</div>
					<div class="card-body">
						<div class="row g-3">
							<div class="col-md-6">
								<label class="form-label fw-semibold">Kode Agen</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-barcode"></i>
									</span>
									<input class="form-control" type="text" name="code"
										value="<?= set_value('code', @$agent->code ?? '') ?>"
										placeholder="Kode akan otomatis dibuat" readonly />
								</div>
								<small class="text-muted">Kode agen akan dibuat otomatis oleh sistem</small>
							</div>

							<div class="col-md-6">
								<label class="form-label fw-semibold">Nama Agen <span class="text-danger">*</span></label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-building"></i>
									</span>
									<input class="form-control" type="text" name="name"
										value="<?= set_value('name', @$agent->name ?? '') ?>" placeholder="Masukkan nama agen"
										required />
								</div>
							</div>

							<div class="col-md-6">
								<label class="form-label fw-semibold">Email</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-envelope"></i>
									</span>
									<input class="form-control" type="email" name="email"
										value="<?= set_value('email', @$agent->email ?? '') ?>"
										placeholder="contoh@email.com" />
								</div>
							</div>

							<div class="col-md-6">
								<label class="form-label fw-semibold">Telepon</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-phone"></i>
									</span>
									<input class="form-control" type="text" name="phone"
										value="<?= set_value('phone', @$agent->phone ?? '') ?>" placeholder="08xx xxxx xxxx" />
								</div>
							</div>

							<div class="col-md-12">
								<label class="form-label fw-semibold">Alamat Lengkap</label>
								<textarea class="form-control" name="address" rows="3"
									placeholder="Masukkan alamat lengkap agen"><?= set_value('address', @$agent->address ?? '') ?></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Lokasi Tab -->
			<div class="tab-pane fade" id="tab-location" role="tabpanel">
				<!-- Address Hierarchy -->
				<div class="card shadow-sm border-0 mb-4">
					<div class="card-header bg-light">
						<h6 class="card-title mb-0">
							<i class="fas fa-map-marker-alt me-2"></i>Alamat & Lokasi
						</h6>
					</div>
					<div class="card-body">
						<div class="row mb-3">
							<div class="col-md-3">
								<label class="form-label fw-semibold mb-2">Provinsi</label>
								<?= form_dropdown('province_id', $provinceOptions ?? [], set_value('province_id', @$agent->province_id ?? ''), [
									'class' => 'form-select',
									'id' => 'province_id'
								]) ?>
							</div>
							<div class="col-md-3">
								<label class="form-label fw-semibold mb-2">Kota/Kabupaten</label>
								<?= form_dropdown('regency_id', $regencyOptions ?? [], set_value('regency_id', @$agent->regency_id ?? ''), [
									'class' => 'form-select',
									'id' => 'regency_id'
								]) ?>
							</div>
							<div class="col-md-3">
								<label class="form-label fw-semibold mb-2">Kecamatan</label>
								<?= form_dropdown('district_id', $districtOptions ?? [], set_value('district_id', @$agent->district_id ?? ''), [
									'class' => 'form-select',
									'id' => 'district_id'
								]) ?>
							</div>
							<div class="col-md-3">
								<label class="form-label fw-semibold mb-2">Kelurahan</label>
								<?= form_dropdown('village_id', $villageOptions ?? [], set_value('village_id', @$agent->village_id ?? ''), [
									'class' => 'form-select',
									'id' => 'village_id'
								]) ?>
							</div>
						</div>

						<div class="row mb-3">
							<div class="col-md-6">
								<label class="form-label fw-semibold mb-2">Kode Pos</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-mail-bulk"></i>
									</span>
									<input class="form-control" type="text" name="postal_code"
										value="<?= set_value('postal_code', @$agent->postal_code ?? '') ?>"
										placeholder="Masukkan kode pos" />
								</div>
							</div>
							<div class="col-md-6">
								<label class="form-label fw-semibold mb-2">Negara <span class="text-danger">*</span></label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-flag"></i>
									</span>
									<input class="form-control" type="text" name="country"
										value="<?= set_value('country', @$agent->country ?? 'Indonesia') ?>"
										placeholder="Masukkan negara" required />
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Map Section -->
				<div class="card shadow-sm border-0 mb-4">
					<div class="card-header bg-light">
						<div class="d-flex justify-content-between align-items-center">
							<h6 class="card-title mb-0">
								<i class="fas fa-map-marked-alt me-2"></i>Pilih Lokasi di Peta
							</h6>
							<button type="button" class="btn btn-sm btn-primary" id="getCurrentLocation">
								<i class="fas fa-crosshairs me-1"></i>
								Deteksi Lokasi
							</button>
						</div>
					</div>
					<div class="card-body">
						<div id="map" style="height: 400px; width: 100%; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 1rem;"></div>
						<small class="text-muted">
							<i class="fas fa-info-circle me-1"></i>
							Klik pada peta untuk memilih koordinat atau gunakan tombol "Deteksi Lokasi" untuk menggunakan GPS
						</small>
					</div>
				</div>

				<!-- Coordinates Input -->
				<div class="card shadow-sm border-0">
					<div class="card-header bg-light">
						<h6 class="card-title mb-0">
							<i class="fas fa-globe me-2"></i>Koordinat Lokasi
						</h6>
					</div>
					<div class="card-body">
						<div class="row g-3">
							<div class="col-md-6">
								<label class="form-label fw-semibold">Latitude</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-map-pin"></i>
									</span>
									<input class="form-control" type="text" name="latitude" id="latitude"
										value="<?= set_value('latitude', @$agent->latitude ?? '') ?>"
										placeholder="Koordinat latitude" />
								</div>
							</div>
							<div class="col-md-6">
								<label class="form-label fw-semibold">Longitude</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-map-pin"></i>
									</span>
									<input class="form-control" type="text" name="longitude" id="longitude"
										value="<?= set_value('longitude', @$agent->longitude ?? '') ?>"
										placeholder="Koordinat longitude" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Bisnis Tab -->
			<div class="tab-pane fade" id="tab-business" role="tabpanel">
				<div class="card shadow-sm border-0">
					<div class="card-header bg-light">
						<h6 class="card-title mb-0">
							<i class="fas fa-briefcase me-2"></i>Informasi Bisnis
						</h6>
					</div>
					<div class="card-body">
						<div class="row g-3">
							<div class="col-md-6">
								<label class="form-label fw-semibold">Nomor Pajak</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-file-invoice"></i>
									</span>
									<input class="form-control" type="text" name="tax_number"
										value="<?= set_value('tax_number', @$agent->tax_number ?? '') ?>"
										placeholder="Masukkan nomor pajak" />
								</div>
							</div>
							<div class="col-md-6">
								<label class="form-label fw-semibold">Limit Kredit</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-credit-card"></i>
									</span>
									<input class="form-control" type="number" name="credit_limit" step="0.01"
										value="<?= set_value('credit_limit', @$agent->credit_limit ?? '0') ?>"
										placeholder="0" />
									<span class="input-group-text">Rp</span>
								</div>
								<small class="text-muted">
									<i class="fas fa-info-circle me-1"></i>
									Batas maksimal kredit yang dapat diberikan kepada agen ini
								</small>
							</div>

							<div class="col-md-6">
								<label class="form-label fw-semibold">Syarat Pembayaran (Hari)</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-calendar-alt"></i>
									</span>
									<input class="form-control" type="number" name="payment_terms"
										value="<?= set_value('payment_terms', @$agent->payment_terms ?? '0') ?>"
										placeholder="0" />
									<span class="input-group-text">hari</span>
								</div>
								<small class="text-muted">
									<i class="fas fa-info-circle me-1"></i>
									Jangka waktu pembayaran yang diberikan kepada agen
								</small>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Pengguna & Status Tab -->
			<div class="tab-pane fade" id="tab-user" role="tabpanel">
				<!-- User Assignment Section -->
				<div class="card shadow-sm mb-4">
					<div class="card-header bg-light">
						<h6 class="card-title mb-0">
							<i class="fas fa-user me-2"></i>Penugasan User
						</h6>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<label class="form-label fw-semibold mb-2">
										<i class="fas fa-user-tag me-1"></i>User
									</label>
									<?php
									$userDropdownAttrs = [
										'class' => 'form-select',
										'id' => 'user_id'
									];
									// Lock dropdown if user has role id = 4
									if (isset($isRoleLocked) && $isRoleLocked) {
										$userDropdownAttrs['disabled'] = 'disabled';
										$userDropdownAttrs['readonly'] = 'readonly';
									}
									$userValue = set_value('user_id', @$agent->user_id ?? '');
									?>
									<?= form_dropdown('user_id', $userOptions ?? [], $userValue, $userDropdownAttrs) ?>
									<?php if (isset($isRoleLocked) && $isRoleLocked): ?>
										<input type="hidden" name="user_id" value="<?= $userValue ?>">
										<small class="text-muted d-block mt-1">
											<i class="fas fa-lock me-1"></i>Field ini terkunci untuk role Anda
										</small>
									<?php else: ?>
										<small class="text-muted d-block mt-1">Pilih user yang akan ditugaskan ke agen ini</small>
									<?php endif; ?>
									<?php echo form_hidden('user_role', '1'); ?>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Password Update Section -->
				<div class="card shadow-sm mb-4" id="password-update-card" style="<?= (empty($agent) || empty($agent->user_id)) ? 'display: none;' : '' ?>">
					<div class="card-header bg-primary text-white">
						<h6 class="card-title mb-0">
							<i class="fas fa-key me-2"></i>Update Password User
						</h6>
					</div>
					<div class="card-body">
						<?= form_open('agent/updateUserPassword', ['id' => 'form-update-password', 'class' => 'needs-validation', 'novalidate' => '']) ?>
						<?= csrf_field() ?>
						<?= form_hidden('agent_id', @$agent->id ?? @$id ?? '') ?>
						<input type="hidden" name="user_id" id="password-form-user-id" value="<?= @$agent->user_id ?? '' ?>">
						
						<div class="row g-3">
							<div class="col-md-4">
								<label class="form-label fw-semibold" for="old_password">
									Password Lama <span class="text-danger">*</span>
								</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-lock"></i>
									</span>
									<input type="password" class="form-control" id="old_password" name="old_password"
										autocomplete="current-password" placeholder="Masukkan password lama" required minlength="1">
								</div>
								<div class="invalid-feedback">Password lama harus diisi</div>
							</div>
							<div class="col-md-4">
								<label class="form-label fw-semibold" for="new_password">
									Password Baru <span class="text-danger">*</span>
								</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-key"></i>
									</span>
									<input type="password" class="form-control" id="new_password" name="new_password"
										autocomplete="new-password" placeholder="Masukkan password baru" required minlength="3">
								</div>
								<div class="invalid-feedback">Password baru harus diisi (minimal 3 karakter)</div>
								<small class="text-muted">Minimal 3 karakter</small>
							</div>
							<div class="col-md-4">
								<label class="form-label fw-semibold" for="repeat_password">
									Ulangi Password Baru <span class="text-danger">*</span>
								</label>
								<div class="input-group">
									<span class="input-group-text">
										<i class="fas fa-check-double"></i>
									</span>
									<input type="password" class="form-control" id="repeat_password" name="repeat_password"
										autocomplete="new-password" placeholder="Ulangi password baru" required minlength="3">
								</div>
								<div class="invalid-feedback">Ulangi password baru harus diisi dan cocok dengan password baru</div>
							</div>
						</div>
						
						<div class="row mt-4">
							<div class="col-md-12">
								<button type="submit" class="btn btn-primary btn-lg" id="btn-update-password">
									<i class="fas fa-key me-2"></i>Update Password
								</button>
								<button type="reset" class="btn btn-outline-secondary btn-lg ms-2" id="btn-reset-password">
									<i class="fas fa-redo me-2"></i>Reset
								</button>
							</div>
						</div>
						<?= form_close() ?>
					</div>
				</div>

				<!-- Info Message when no user selected -->
				<div id="password-info-message" class="alert alert-info border-0 shadow-sm" style="<?= (!empty($agent) && !empty($agent->user_id)) ? 'display: none;' : '' ?>">
					<div class="d-flex align-items-center">
						<i class="fas fa-info-circle fa-2x me-3"></i>
						<div>
							<strong>Informasi</strong>
							<p class="mb-0">Pilih user terlebih dahulu untuk dapat mengupdate password</p>
						</div>
					</div>
				</div>
			</div>

			<?php if ($canCreate): ?>
				<!-- Status Section -->
				<div class="card shadow-sm">
					<div class="card-header bg-light">
						<h6 class="card-title mb-0">
							<i class="fas fa-toggle-on me-2"></i>Status Agen
						</h6>
					</div>
					<div class="card-body">
						<div class="form-check form-switch form-switch-lg">
							<?= form_checkbox([
								'name' => 'is_active',
								'value' => '1',
								'class' => 'form-check-input',
								'id' => 'is_active',
								'checked' => set_value('is_active', @$agent->is_active ?? '1') == '1'
							]) ?>
							<label class="form-check-label fw-semibold" for="is_active">
								Status Aktif
							</label>
						</div>
						<small class="text-muted d-block mt-2">
							<i class="fas fa-info-circle me-1"></i>
							Agen aktif dapat melakukan transaksi dan akses sistem
						</small>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Form Actions -->
	<div class="row mt-4 mb-4">
		<div class="col-12">
			<div class="card shadow-sm border-0">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<div>
							<h6 class="mb-0 text-muted">
								<i class="fas fa-info-circle me-1"></i>
								Pastikan semua data telah diisi dengan benar sebelum menyimpan
							</h6>
						</div>
						<div class="btn-group" role="group">
							<button type="submit" name="submit" value="agent" class="btn btn-primary btn-lg px-4">
								<i class="fas fa-save me-2"></i>Simpan Data
							</button>
							<a href="<?= $config->baseURL ?>agent" class="btn btn-outline-secondary btn-lg px-4">
								<i class="fas fa-times me-2"></i>Batal
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?= form_close() ?>

	<?php if (!$isModal): ?>
	</div>
	</div>
<?php endif; ?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
	// Load Leaflet dynamically
	function loadLeaflet() {
		if (!document.querySelector('link[href*="leaflet"]')) {
			var link = document.createElement('link');
			link.rel = 'stylesheet';
			link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
			document.head.appendChild(link);
		}

		var script = document.createElement('script');
		script.src = 'https://unpkg.com/leaflet@1.9.4/dist/js/leaflet.js';
		script.onload = function () {
			console.log('Leaflet loaded, initializing map...');
			initializeMap();
		};
		document.head.appendChild(script);
	}

	// Function to initialize map
	function initializeMap() {
		setTimeout(function () {
			if (document.getElementById('map')) {
				window.map = L.map('map').setView([-6.200000, 106.816666], 10);

				L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
					attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
				}).addTo(window.map);

				window.marker = L.marker([-6.200000, 106.816666]).addTo(window.map);

				function updateCoordinates(lat, lng) {
					$('#latitude').val(lat);
					$('#longitude').val(lng);
					window.marker.setLatLng([lat, lng]);
					window.map.setView([lat, lng], window.map.getZoom());
				}

				var initialLat = parseFloat($('#latitude').val()) || -6.200000;
				var initialLng = parseFloat($('#longitude').val()) || 106.816666;

				if ($('#latitude').val() && $('#longitude').val()) {
					updateCoordinates(initialLat, initialLng);
				}

				window.map.on('click', function (e) {
					var lat = e.latlng.lat;
					var lng = e.latlng.lng;
					updateCoordinates(lat, lng);
				});

				$('#latitude, #longitude').on('change', function () {
					var lat = parseFloat($('#latitude').val());
					var lng = parseFloat($('#longitude').val());

					if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
						updateCoordinates(lat, lng);
					}
				});
			}
		}, 100);
	}

	$(document).ready(function () {
		// Form validation
		var form = document.getElementById('form-agent');
		if (form) {
			form.addEventListener('submit', function (event) {
				if (!form.checkValidity()) {
					event.preventDefault();
					event.stopPropagation();
				}
				form.classList.add('was-validated');
			}, false);
		}

		// Check if Leaflet is loaded
		if (typeof L === 'undefined') {
			console.log('Leaflet not loaded, loading dynamically...');
			loadLeaflet();
			return;
		} else {
			console.log('Leaflet already loaded, initializing map...');
			initializeMap();
		}

		// Get current location button handler
		$('#getCurrentLocation').on('click', function () {
			var button = $(this);
			var originalText = button.html();

			button.html('<i class="fas fa-spinner fa-spin me-1"></i> Mendeteksi...');
			button.prop('disabled', true);

			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(
					function (position) {
						var lat = position.coords.latitude;
						var lng = position.coords.longitude;

						$('#latitude').val(lat);
						$('#longitude').val(lng);

						if (typeof window.map !== 'undefined' && window.map) {
							window.marker.setLatLng([lat, lng]);
							window.map.setView([lat, lng], 15);
						}

						button.html('<i class="fas fa-check me-1"></i> Berhasil!');
						setTimeout(function () {
							button.html(originalText);
							button.prop('disabled', false);
						}, 2000);
					},
					function (error) {
						var errorMessage = 'Gagal mendapatkan lokasi: ';
						switch (error.code) {
							case error.PERMISSION_DENIED:
								errorMessage += 'Akses lokasi ditolak';
								break;
							case error.POSITION_UNAVAILABLE:
								errorMessage += 'Lokasi tidak tersedia';
								break;
							case error.TIMEOUT:
								errorMessage += 'Waktu habis';
								break;
							default:
								errorMessage += 'Error tidak diketahui';
								break;
						}

						button.html('<i class="fas fa-exclamation-triangle me-1"></i> Error');
						if (typeof Swal !== 'undefined') {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: errorMessage
							});
						} else {
							alert(errorMessage);
						}

						setTimeout(function () {
							button.html(originalText);
							button.prop('disabled', false);
						}, 2000);
					},
					{
						enableHighAccuracy: true,
						timeout: 10000,
						maximumAge: 60000
					}
				);
			} else {
				button.html('<i class="fas fa-exclamation-triangle me-1"></i> Tidak Didukung');
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'warning',
						title: 'Tidak Didukung',
						text: 'Browser tidak mendukung geolocation'
					});
				} else {
					alert('Browser tidak mendukung geolocation');
				}

				setTimeout(function () {
					button.html(originalText);
					button.prop('disabled', false);
				}, 2000);
			}
		});

		// Helper function to load regencies
		function loadRegencies(provinceId, selectedRegencyId) {
			var regencySelect = $('#regency_id');
			var districtSelect = $('#district_id');
			var villageSelect = $('#village_id');

			districtSelect.empty().append('<option value="">Pilih Kecamatan</option>');
			villageSelect.empty().append('<option value="">Pilih Kelurahan</option>');

			if (provinceId) {
				$.ajax({
					url: '<?= $config->baseURL ?>agent/getRegencies',
					type: 'POST',
					data: { province_id: provinceId },
					dataType: 'json',
					success: function (data) {
						regencySelect.empty().append('<option value="">Pilih Kota/Kabupaten</option>');
						$.each(data, function (key, value) {
							var selected = (selectedRegencyId && key == selectedRegencyId) ? 'selected' : '';
							regencySelect.append('<option value="' + key + '" ' + selected + '>' + value + '</option>');
						});
					}
				});
			}
		}

		// Helper function to load districts
		function loadDistricts(regencyId, selectedDistrictId) {
			var districtSelect = $('#district_id');
			var villageSelect = $('#village_id');

			villageSelect.empty().append('<option value="">Pilih Kelurahan</option>');

			if (regencyId) {
				$.ajax({
					url: '<?= $config->baseURL ?>agent/getDistricts',
					type: 'POST',
					data: { regency_id: regencyId },
					dataType: 'json',
					success: function (data) {
						districtSelect.empty().append('<option value="">Pilih Kecamatan</option>');
						$.each(data, function (key, value) {
							var selected = (selectedDistrictId && key == selectedDistrictId) ? 'selected' : '';
							districtSelect.append('<option value="' + key + '" ' + selected + '>' + value + '</option>');
						});
					}
				});
			}
		}

		// Helper function to load villages
		function loadVillages(districtId, selectedVillageId) {
			var villageSelect = $('#village_id');

			if (districtId) {
				$.ajax({
					url: '<?= $config->baseURL ?>agent/getVillages',
					type: 'POST',
					data: { district_id: districtId },
					dataType: 'json',
					success: function (data) {
						villageSelect.empty().append('<option value="">Pilih Kelurahan</option>');
						$.each(data, function (key, value) {
							var selected = (selectedVillageId && key == selectedVillageId) ? 'selected' : '';
							villageSelect.append('<option value="' + key + '" ' + selected + '>' + value + '</option>');
						});
					}
				});
			}
		}

		// Province change handler
		$('#province_id').on('change', function () {
			var provinceId = $(this).val();
			loadRegencies(provinceId, null);
		});

		// Regency change handler
		$('#regency_id').on('change', function () {
			var regencyId = $(this).val();
			loadDistricts(regencyId, null);
		});

		// District change handler
		$('#district_id').on('change', function () {
			var districtId = $(this).val();
			loadVillages(districtId, null);
		});

		// Auto-load dependent data if editing
		<?php if (!empty($agent) && !empty($agent->province_id)): ?>
			loadRegencies(<?= $agent->province_id ?>, <?= $agent->regency_id ?? 'null' ?>);
		<?php endif; ?>

		<?php if (!empty($agent) && !empty($agent->regency_id)): ?>
			loadDistricts(<?= $agent->regency_id ?>, <?= $agent->district_id ?? 'null' ?>);
		<?php endif; ?>

		<?php if (!empty($agent) && !empty($agent->district_id)): ?>
			loadVillages(<?= $agent->district_id ?>, <?= $agent->village_id ?? 'null' ?>);
		<?php endif; ?>

		// Password update form validation and AJAX submission
		var passwordForm = $('#form-update-password');
		if (passwordForm.length) {
			passwordForm.on('submit', function(event) {
				event.preventDefault();
				var form = this;
				
				// Check HTML5 validation
				if (!form.checkValidity()) {
					form.classList.add('was-validated');
					return false;
				}
				
				// Additional validation: check if passwords match
				var newPassword = $('#new_password').val();
				var repeatPassword = $('#repeat_password').val();
				
				if (newPassword !== repeatPassword) {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Password baru dan ulangi password tidak cocok'
						});
					} else {
						alert('Password baru dan ulangi password tidak cocok');
					}
					return false;
				}
				
				// Check minimum length
				if (newPassword.length < 3) {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Password baru minimal 3 karakter'
						});
					} else {
						alert('Password baru minimal 3 karakter');
					}
					return false;
				}
				
				// Validate user_id is set
				var userId = $('#password-form-user-id').val() || passwordForm.find('input[name="user_id"]').val();
				if (!userId || userId === '') {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'User ID tidak ditemukan. Silakan pilih user terlebih dahulu.'
						});
					} else {
						alert('User ID tidak ditemukan. Silakan pilih user terlebih dahulu.');
					}
					return false;
				}
				
				// Show loading state
				var submitBtn = passwordForm.find('button[type="submit"]');
				var originalBtnText = submitBtn.html();
				submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');
				
				// Submit via AJAX
				$.ajax({
					url: passwordForm.attr('action'),
					type: 'POST',
					data: passwordForm.serialize(),
					dataType: 'json',
					headers: {
						'X-Requested-With': 'XMLHttpRequest'
					},
					success: function(response) {
						submitBtn.prop('disabled', false).html(originalBtnText);
						
						if (response.status === 'success') {
							if (typeof Swal !== 'undefined') {
								Swal.fire({
									icon: 'success',
									title: 'Berhasil',
									text: response.message || 'Password berhasil diupdate',
									timer: 2000,
									showConfirmButton: false
								});
							} else {
								alert(response.message || 'Password berhasil diupdate');
							}
							// Reset form
							passwordForm[0].reset();
							passwordForm.removeClass('was-validated');
						} else {
							if (typeof Swal !== 'undefined') {
								Swal.fire({
									icon: 'error',
									title: 'Error',
									text: response.message || 'Gagal mengupdate password'
								});
							} else {
								alert(response.message || 'Gagal mengupdate password');
							}
						}
					},
					error: function(xhr, status, error) {
						submitBtn.prop('disabled', false).html(originalBtnText);
						
						var errorMessage = 'Terjadi kesalahan saat mengupdate password';
						
						// Try to parse JSON response first
						if (xhr.responseJSON) {
							if (xhr.responseJSON.message) {
								errorMessage = xhr.responseJSON.message;
							} else if (xhr.responseJSON.error) {
								errorMessage = xhr.responseJSON.error;
							}
						} else if (xhr.responseText) {
							// Try to parse HTML response for error messages
							try {
								var parser = new DOMParser();
								var doc = parser.parseFromString(xhr.responseText, 'text/html');
								var alertElement = doc.querySelector('.alert-danger, .alert, [role="alert"]');
								if (alertElement) {
									errorMessage = alertElement.textContent.trim();
								}
							} catch (e) {
								// If parsing fails, use default message
							}
						}
						
						if (typeof Swal !== 'undefined') {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: errorMessage
							});
						} else {
							alert(errorMessage);
						}
					}
				});
			});
		}

		// Update password form when user dropdown changes (for new agents)
		$('#user_id').on('change', function() {
			var userId = $(this).val();
			var passwordForm = $('#form-update-password');
			var passwordFormUserId = $('#password-form-user-id');
			var passwordCard = $('#password-update-card');
			var infoMessage = $('#password-info-message');
			
			if (userId && userId !== '') {
				// Update hidden user_id field in password form
				if (passwordFormUserId.length) {
					passwordFormUserId.val(userId);
				} else {
					// If field doesn't exist, create it
					passwordForm.append('<input type="hidden" name="user_id" id="password-form-user-id" value="' + userId + '">');
				}
				// Show the password form section if it was hidden
				passwordCard.slideDown(300);
				infoMessage.slideUp(300);
			} else {
				// Hide password form if no user selected
				passwordCard.slideUp(300);
				infoMessage.slideDown(300);
				// Clear password form
				passwordForm[0].reset();
				passwordForm.removeClass('was-validated');
			}
		});
		
		// Reset button handler
		$('#btn-reset-password').on('click', function(e) {
			e.preventDefault();
			var passwordForm = $('#form-update-password');
			passwordForm[0].reset();
			passwordForm.removeClass('was-validated');
		});
	});
</script>