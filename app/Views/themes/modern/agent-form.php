<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-24
 * Github: github.com/mikhaelfelian
 * Description: View for agent form (add/edit) with all fields from AgentModel using CI 4.3.1 form helpers
 * This file represents the View for agent-form.
 */

helper('form');
$isModal = $isModal ?? false;
?>
		<?php
			if (!empty($message)) {
				show_message($message);
			} ?>
		<?= form_open('', ['class' => 'form-horizontal p-3', 'id' => 'form-agent']) ?>
			<div class="row mb-3">
				<!-- Kode Agen (Read-only for edit) -->
				<label class="col-sm-3 col-form-label">Kode Agen</label>
				<div class="col-sm-9">
					<?= form_input([
						'name' => 'code',
						'class' => 'form-control',
						'value' => set_value('code', @$agent->code ?? ''),
						'placeholder' => 'Kode akan otomatis dibuat',
						'readonly' => 'readonly'
					]) ?>
				</div>
			</div>
			
			<div class="row mb-3">
				<!-- Nama Agen -->
				<label class="col-sm-3 col-form-label">Nama Agen <span class="text-danger">*</span></label>
				<div class="col-sm-9">
					<?= form_input([
						'name' => 'name',
						'class' => 'form-control',
						'value' => set_value('name', @$agent->name ?? ''),
						'placeholder' => 'Masukkan nama agen',
						'required' => 'required'
					]) ?>
				</div>
			</div>
			
			<div class="row mb-3">
				<!-- Email -->
				<label class="col-sm-3 col-form-label">Email</label>
				<div class="col-sm-9">
					<?= form_input([
						'name' => 'email',
						'type' => 'email',
						'class' => 'form-control',
						'value' => set_value('email', @$agent->email ?? ''),
						'placeholder' => 'Masukkan email agen'
					]) ?>
				</div>
			</div>
			
			<div class="row mb-3">
				<!-- Telepon -->
				<label class="col-sm-3 col-form-label">Telepon</label>
				<div class="col-sm-9">
					<?= form_input([
						'name' => 'phone',
						'class' => 'form-control',
						'value' => set_value('phone', @$agent->phone ?? ''),
						'placeholder' => 'Masukkan nomor telepon'
					]) ?>
				</div>
			</div>
			
			<div class="row mb-3">
				<!-- Alamat -->
				<label class="col-sm-3 col-form-label">Alamat</label>
				<div class="col-sm-9">
					<?= form_textarea([
						'name' => 'address',
						'class' => 'form-control',
						'value' => set_value('address', @$agent->address ?? ''),
						'placeholder' => 'Masukkan alamat lengkap',
						'rows' => 3
					]) ?>
				</div>
			</div>
			
			<div class="row mb-3">
				<!-- Koordinat -->
				<div class="col-sm-5">
					<label class="col-form-label">Latitude</label>
					<?= form_input([
						'name' => 'latitude',
						'class' => 'form-control',
						'id' => 'latitude',
						'value' => set_value('latitude', @$agent->latitude ?? ''),
						'placeholder' => 'Masukkan latitude'
					]) ?>
				</div>
				<div class="col-sm-5">
					<label class="col-form-label">Longitude</label>
					<?= form_input([
						'name' => 'longitude',
						'class' => 'form-control',
						'id' => 'longitude',
						'value' => set_value('longitude', @$agent->longitude ?? ''),
						'placeholder' => 'Masukkan longitude'
					]) ?>
				</div>
				<div class="col-sm-2">
					<label class="col-form-label">&nbsp;</label>
					<button type="button" class="btn btn-info btn-sm w-100" id="getCurrentLocation" title="Gunakan lokasi saat ini">
						<i class="fa fa-map-marker-alt me-1"></i> Lokasi
					</button>
				</div>
			</div>
			
			<div class="row mb-3">
				<!-- Peta -->
				<div class="col-12">
					<label class="col-form-label">Pilih Lokasi di Peta</label>
					<div id="map" style="height: 400px; width: 100%; border: 1px solid #ddd; border-radius: 4px;"></div>
					<small class="text-muted">Klik pada peta untuk memilih koordinat</small>
				</div>
			</div>
			
			<div class="row mb-3">
				<!-- Lokasi -->
				<div class="col-sm-3">
					<label class="col-form-label">Provinsi</label>
					<?= form_dropdown('province_id', $provinceOptions ?? [], set_value('province_id', @$agent->province_id ?? ''), [
						'class' => 'form-control',
						'id' => 'province_id'
					]) ?>
				</div>
				<div class="col-sm-3">
					<label class="col-form-label">Kota/Kabupaten</label>
					<?= form_dropdown('regency_id', $regencyOptions ?? [], set_value('regency_id', @$agent->regency_id ?? ''), [
						'class' => 'form-control',
						'id' => 'regency_id'
					]) ?>
				</div>
				<div class="col-sm-3">
					<label class="col-form-label">Kecamatan</label>
					<?= form_dropdown('district_id', $districtOptions ?? [], set_value('district_id', @$agent->district_id ?? ''), [
						'class' => 'form-control',
						'id' => 'district_id'
					]) ?>
				</div>
				<div class="col-sm-3">
					<label class="col-form-label">Kelurahan</label>
					<?= form_dropdown('village_id', $villageOptions ?? [], set_value('village_id', @$agent->village_id ?? ''), [
						'class' => 'form-control',
						'id' => 'village_id'
					]) ?>
				</div>
			</div>
			
			<div class="row mb-3">
				<!-- Kode Pos dan Negara -->
				<div class="col-sm-6">
					<label class="col-form-label">Kode Pos</label>
					<?= form_input([
						'name' => 'postal_code',
						'class' => 'form-control',
						'value' => set_value('postal_code', @$agent->postal_code ?? ''),
						'placeholder' => 'Masukkan kode pos'
					]) ?>
				</div>
				<div class="col-sm-6">
					<label class="col-form-label">Negara <span class="text-danger">*</span></label>
					<?= form_input([
						'name' => 'country',
						'class' => 'form-control',
						'value' => set_value('country', @$agent->country ?? 'Indonesia'),
						'placeholder' => 'Masukkan negara',
						'required' => 'required'
					]) ?>
				</div>
			</div>
			
			<div class="row mb-3">
				<!-- Nomor Pajak -->
				<label class="col-sm-3 col-form-label">Nomor Pajak</label>
				<div class="col-sm-9">
					<?= form_input([
						'name' => 'tax_number',
						'class' => 'form-control',
						'value' => set_value('tax_number', @$agent->tax_number ?? ''),
						'placeholder' => 'Masukkan nomor pajak'
					]) ?>
				</div>
			</div>
			
			<div class="row mb-3">
				<!-- Limit Kredit dan Syarat Pembayaran -->
				<div class="col-sm-6">
					<label class="col-form-label">Limit Kredit</label>
					<?= form_input([
						'name' => 'credit_limit',
						'type' => 'number',
						'step' => '0.01',
						'class' => 'form-control',
						'value' => set_value('credit_limit', @$agent->credit_limit ?? '0'),
						'placeholder' => 'Masukkan limit kredit'
					]) ?>
				</div>
				<div class="col-sm-6">
					<label class="col-form-label">Syarat Pembayaran (Hari)</label>
					<?= form_input([
						'name' => 'payment_terms',
						'type' => 'number',
						'class' => 'form-control',
						'value' => set_value('payment_terms', @$agent->payment_terms ?? '0'),
						'placeholder' => 'Masukkan syarat pembayaran'
					]) ?>
				</div>
			</div>
			
			<div class="row mb-3">
				<!-- User Assignment -->
				<label class="col-sm-3 col-form-label">User</label>
				<div class="col-sm-6">
					<?= form_dropdown('user_id', $userOptions ?? [], set_value('user_id', @$agent->user_id ?? ''), [
						'class' => 'form-control',
						'id' => 'user_id',
						'placeholder' => 'Select User'
					]) ?>
				</div>
				<div class="col-sm-3">
					<?= form_dropdown('user_role', [
						'1' => 'Owner',
						'2' => 'Staff'
					], set_value('user_role', @$agent->user_role ?? '1'), [
						'class' => 'form-control',
						'id' => 'user_role'
					]) ?>
				</div>
			</div>
			
			<?php if ($canCreate): ?>
			<div class="row mb-3">
				<!-- Status -->
				<label class="col-sm-3 col-form-label">Status</label>
				<div class="col-sm-9">
					<div class="form-check form-switch">
						<?= form_checkbox([
							'name' => 'is_active',
							'value' => '1',
							'class' => 'form-check-input',
							'checked' => set_value('is_active', @$agent->is_active ?? '1') == '1'
						]) ?>
						<label class="form-check-label">Aktif</label>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<div class="row">
				<div class="col-sm-9 offset-sm-3">
					<?= form_hidden('id', @$id ?? '') ?>
				</div>
			</div>
		<?= form_close() ?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Load Leaflet dynamically
function loadLeaflet() {
    // Load CSS
    if (!document.querySelector('link[href*="leaflet"]')) {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);
    }
    
    // Load JS
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
    // Wait a bit for the map container to be ready
    setTimeout(function() {
        if (document.getElementById('map')) {
            window.map = L.map('map').setView([-6.200000, 106.816666], 10); // Default to Jakarta
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(window.map);
            
            // Add marker
            window.marker = L.marker([-6.200000, 106.816666]).addTo(window.map);
            
            // Function to update coordinates
            function updateCoordinates(lat, lng) {
                $('#latitude').val(lat);
                $('#longitude').val(lng);
                window.marker.setLatLng([lat, lng]);
                window.map.setView([lat, lng], window.map.getZoom());
            }
            
            // Set initial coordinates if they exist
            var initialLat = parseFloat($('#latitude').val()) || -6.200000;
            var initialLng = parseFloat($('#longitude').val()) || 106.816666;
            
            if ($('#latitude').val() && $('#longitude').val()) {
                updateCoordinates(initialLat, initialLng);
            }
            
            // Map click handler
            window.map.on('click', function(e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;
                updateCoordinates(lat, lng);
            });
            
            // Input change handlers
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
        
        // Show loading state
        button.html('<i class="fa fa-spinner fa-spin me-1"></i> Mendeteksi...');
        button.prop('disabled', true);
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // Success callback
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    
                    // Update coordinates
                    $('#latitude').val(lat);
                    $('#longitude').val(lng);
                    
                    // Update map if it exists
                    if (typeof window.map !== 'undefined' && window.map) {
                        window.marker.setLatLng([lat, lng]);
                        window.map.setView([lat, lng], 15);
                    }
                    
                    // Show success message
                    button.html('<i class="fa fa-check me-1"></i> Berhasil!');
                    setTimeout(function() {
                        button.html(originalText);
                        button.prop('disabled', false);
                    }, 2000);
                },
                function(error) {
                    // Error callback
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
                    
                    button.html('<i class="fa fa-exclamation-triangle me-1"></i> Error');
                    alert(errorMessage);
                    
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
            // Geolocation not supported
            button.html('<i class="fa fa-exclamation-triangle me-1"></i> Tidak Didukung');
            alert('Browser tidak mendukung geolocation');
            
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
        
        // Clear dependent selects
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
        
        // Clear dependent selects
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
    // Load regencies for current province
    loadRegencies(<?= $agent->province_id ?>, <?= $agent->regency_id ?? 'null' ?>);
    <?php endif; ?>
    
    <?php if (!empty($agent) && !empty($agent->regency_id)): ?>
    // Load districts for current regency
    loadDistricts(<?= $agent->regency_id ?>, <?= $agent->district_id ?? 'null' ?>);
    <?php endif; ?>
    
    <?php if (!empty($agent) && !empty($agent->district_id)): ?>
    // Load villages for current district
    loadVillages(<?= $agent->district_id ?>, <?= $agent->village_id ?? 'null' ?>);
    <?php endif; ?>
});
</script>
