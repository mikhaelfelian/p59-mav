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
		<div class="row">
			<div class="col-md-9">
				<div class="mb-3">
					<label class="control-label mb-2">Kode Agen</label>
					<input class="form-control" type="text" name="code"
						value="<?= set_value('code', @$agent->code ?? '') ?>" 
						placeholder="Kode akan otomatis dibuat" readonly />
				</div>

				<div class="mb-3">
					<label class="control-label mb-2">Nama Agen <span class="text-danger">*</span></label>
					<input class="form-control" type="text" name="name"
						value="<?= set_value('name', @$agent->name ?? '') ?>" 
						placeholder="Masukkan nama agen" required />
				</div>

				<div class="mb-3">
					<label class="control-label mb-2">Email</label>
					<input class="form-control" type="email" name="email"
						value="<?= set_value('email', @$agent->email ?? '') ?>" 
						placeholder="contoh@email.com" />
				</div>

				<div class="mb-3">
					<label class="control-label mb-2">Telepon</label>
					<input class="form-control" type="text" name="phone"
						value="<?= set_value('phone', @$agent->phone ?? '') ?>" 
						placeholder="08xx xxxx xxxx" />
				</div>

				<div class="mb-3">
					<label class="control-label mb-2">Alamat Lengkap</label>
					<textarea class="form-control" name="address" rows="3"
						placeholder="Masukkan alamat lengkap agen"><?= set_value('address', @$agent->address ?? '') ?></textarea>
				</div>
			</div>
		</div>
	</div>

	<!-- Lokasi Tab -->
	<div class="tab-pane fade" id="tab-location" role="tabpanel">
		<!-- Address Hierarchy -->
		<div class="row mb-3">
			<div class="col-md-3">
				<label class="control-label mb-2">Provinsi</label>
				<?= form_dropdown('province_id', $provinceOptions ?? [], set_value('province_id', @$agent->province_id ?? ''), [
					'class' => 'form-control',
					'id' => 'province_id'
				]) ?>
			</div>
			<div class="col-md-3">
				<label class="control-label mb-2">Kota/Kabupaten</label>
				<?= form_dropdown('regency_id', $regencyOptions ?? [], set_value('regency_id', @$agent->regency_id ?? ''), [
					'class' => 'form-control',
					'id' => 'regency_id'
				]) ?>
			</div>
			<div class="col-md-3">
				<label class="control-label mb-2">Kecamatan</label>
				<?= form_dropdown('district_id', $districtOptions ?? [], set_value('district_id', @$agent->district_id ?? ''), [
					'class' => 'form-control',
					'id' => 'district_id'
				]) ?>
			</div>
			<div class="col-md-3">
				<label class="control-label mb-2">Kelurahan</label>
				<?= form_dropdown('village_id', $villageOptions ?? [], set_value('village_id', @$agent->village_id ?? ''), [
					'class' => 'form-control',
					'id' => 'village_id'
				]) ?>
			</div>
		</div>

		<div class="row mb-3">
			<div class="col-md-6">
				<label class="control-label mb-2">Kode Pos</label>
				<input class="form-control" type="text" name="postal_code"
					value="<?= set_value('postal_code', @$agent->postal_code ?? '') ?>" 
					placeholder="Masukkan kode pos" />
			</div>
			<div class="col-md-6">
				<label class="control-label mb-2">Negara <span class="text-danger">*</span></label>
				<input class="form-control" type="text" name="country"
					value="<?= set_value('country', @$agent->country ?? 'Indonesia') ?>" 
					placeholder="Masukkan negara" required />
			</div>
		</div>

		<!-- Map Section -->
		<div class="mb-3">
			<div class="d-flex justify-content-between align-items-center mb-2">
				<label class="control-label mb-0">
					<i class="fas fa-map-marked-alt me-1"></i>
					Pilih Lokasi di Peta
				</label>
				<button type="button" class="btn btn-sm btn-primary" id="getCurrentLocation">
					<i class="fas fa-crosshairs me-1"></i>
					Deteksi Lokasi
				</button>
			</div>
			<div id="map" style="height: 400px; width: 100%; border: 1px solid #ddd; border-radius: 4px;"></div>
			<small class="text-muted">
				<i class="fas fa-info-circle me-1"></i>
				Klik pada peta untuk memilih koordinat atau gunakan tombol "Deteksi Lokasi" untuk menggunakan GPS
			</small>
		</div>

		<!-- Coordinates Input -->
		<div class="row">
			<div class="col-md-6">
				<label class="control-label mb-2">Latitude</label>
				<input class="form-control" type="text" name="latitude" id="latitude"
					value="<?= set_value('latitude', @$agent->latitude ?? '') ?>" 
					placeholder="Koordinat latitude" />
			</div>
			<div class="col-md-6">
				<label class="control-label mb-2">Longitude</label>
				<input class="form-control" type="text" name="longitude" id="longitude"
					value="<?= set_value('longitude', @$agent->longitude ?? '') ?>" 
					placeholder="Koordinat longitude" />
			</div>
		</div>
	</div>

	<!-- Bisnis Tab -->
	<div class="tab-pane fade" id="tab-business" role="tabpanel">
		<div class="row">
			<div class="col-md-6">
				<div class="mb-3">
					<label class="control-label mb-2">Nomor Pajak</label>
					<input class="form-control" type="text" name="tax_number"
						value="<?= set_value('tax_number', @$agent->tax_number ?? '') ?>" 
						placeholder="Masukkan nomor pajak" />
				</div>
			</div>
			<div class="col-md-6">
				<div class="mb-3">
					<label class="control-label mb-2">Limit Kredit</label>
					<input class="form-control" type="number" name="credit_limit" step="0.01"
						value="<?= set_value('credit_limit', @$agent->credit_limit ?? '0') ?>" 
						placeholder="0" />
					<small class="text-muted">
						<i class="fas fa-info-circle me-1"></i>
						Batas maksimal kredit yang dapat diberikan kepada agen ini
					</small>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="mb-3">
					<label class="control-label mb-2">Syarat Pembayaran (Hari)</label>
					<div class="input-group">
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

	<!-- Pengguna & Status Tab -->
	<div class="tab-pane fade" id="tab-user" role="tabpanel">
		<div class="row">
			<div class="col-md-8">
				<div class="mb-3">
					<label class="control-label mb-2">User</label>
					<?= form_dropdown('user_id', $userOptions ?? [], set_value('user_id', @$agent->user_id ?? ''), [
						'class' => 'form-control',
						'id' => 'user_id'
					]) ?>
				</div>
			</div>
			<div class="col-md-4">
				<div class="mb-3">
					<label class="control-label mb-2">Role</label>
					<?= form_dropdown('user_role', [
						'1' => 'Owner',
						'2' => 'Staff'
					], set_value('user_role', @$agent->user_role ?? '1'), [
						'class' => 'form-control',
						'id' => 'user_role'
					]) ?>
				</div>
			</div>
		</div>

		<?php if ($canCreate): ?>
		<div class="row">
			<div class="col-md-12">
				<div class="mb-3">
					<label class="control-label mb-2">Status</label>
					<div class="form-check form-switch">
						<?= form_checkbox([
							'name' => 'is_active',
							'value' => '1',
							'class' => 'form-check-input',
							'id' => 'is_active',
							'checked' => set_value('is_active', @$agent->is_active ?? '1') == '1'
						]) ?>
						<label class="form-check-label" for="is_active">
							Agen aktif dapat melakukan transaksi dan akses sistem
						</label>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>

<div class="row mt-4">
	<div class="col-sm-12">
		<button type="submit" name="submit" value="agent" class="btn btn-primary">
			<i class="fas fa-save me-1"></i> Simpan
		</button>
		<a href="<?= $config->baseURL ?>agent" class="btn btn-secondary">
			<i class="fas fa-times me-1"></i> Batal
		</a>
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
    script.onload = function() {
        console.log('Leaflet loaded, initializing map...');
        initializeMap();
    };
    document.head.appendChild(script);
}

// Function to initialize map
function initializeMap() {
    setTimeout(function() {
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
            
            window.map.on('click', function(e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;
                updateCoordinates(lat, lng);
            });
            
            $('#latitude, #longitude').on('change', function() {
                var lat = parseFloat($('#latitude').val());
                var lng = parseFloat($('#longitude').val());
                
                if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                    updateCoordinates(lat, lng);
                }
            });
        }
    }, 100);
}

$(document).ready(function() {
    // Form validation
    var form = document.getElementById('form-agent');
    if (form) {
        form.addEventListener('submit', function(event) {
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
    $('#getCurrentLocation').on('click', function() {
        var button = $(this);
        var originalText = button.html();
        
        button.html('<i class="fas fa-spinner fa-spin me-1"></i> Mendeteksi...');
        button.prop('disabled', true);
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    
                    $('#latitude').val(lat);
                    $('#longitude').val(lng);
                    
                    if (typeof window.map !== 'undefined' && window.map) {
                        window.marker.setLatLng([lat, lng]);
                        window.map.setView([lat, lng], 15);
                    }
                    
                    button.html('<i class="fas fa-check me-1"></i> Berhasil!');
                    setTimeout(function() {
                        button.html(originalText);
                        button.prop('disabled', false);
                    }, 2000);
                },
                function(error) {
                    var errorMessage = 'Gagal mendapatkan lokasi: ';
                    switch(error.code) {
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
                    
                    setTimeout(function() {
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
            
            setTimeout(function() {
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
                success: function(data) {
                    regencySelect.empty().append('<option value="">Pilih Kota/Kabupaten</option>');
                    $.each(data, function(key, value) {
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
                success: function(data) {
                    districtSelect.empty().append('<option value="">Pilih Kecamatan</option>');
                    $.each(data, function(key, value) {
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
                success: function(data) {
                    villageSelect.empty().append('<option value="">Pilih Kelurahan</option>');
                    $.each(data, function(key, value) {
                        var selected = (selectedVillageId && key == selectedVillageId) ? 'selected' : '';
                        villageSelect.append('<option value="' + key + '" ' + selected + '>' + value + '</option>');
                    });
                }
            });
        }
    }

    // Province change handler
    $('#province_id').on('change', function() {
        var provinceId = $(this).val();
        loadRegencies(provinceId, null);
    });

    // Regency change handler
    $('#regency_id').on('change', function() {
        var regencyId = $(this).val();
        loadDistricts(regencyId, null);
    });

    // District change handler
    $('#district_id').on('change', function() {
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
});
</script>