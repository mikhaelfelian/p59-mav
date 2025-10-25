<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-25 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Frontend view for displaying agent locations with map and list
 * This file represents the View.
 */
?>

<?= $this->extend('themes/modern/layout/main') ?>
<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="fas fa-map-marked-alt me-3"></i>Lokasi Agen
                </h1>
                <p class="lead mb-4">Temukan lokasi agen terdekat di sekitar Anda dengan mudah dan cepat</p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-light text-dark fs-6 px-3 py-2">
                        <i class="fas fa-building me-1"></i><?= count($agents) ?> Agen Tersedia
                    </span>
                    <span class="badge bg-light text-dark fs-6 px-3 py-2">
                        <i class="fas fa-map-marker-alt me-1"></i>Seluruh Indonesia
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-map-marked-alt display-1 opacity-75"></i>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-search me-2 text-primary"></i>Cari & Filter Agen
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-6 col-md-12">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-primary text-white border-0">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control border-0 shadow-sm" id="searchLocation" 
                                       placeholder="Cari berdasarkan nama agen, kode, atau lokasi...">
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-8">
                            <select class="form-select form-select-lg border-0 shadow-sm" id="filterProvince">
                                <option value="">🌍 Semua Provinsi</option>
                                <?php 
                                $provinces = array_unique(array_column($agents, 'province_name'));
                                foreach($provinces as $province): 
                                    if(!empty($province)):
                                ?>
                                    <option value="<?= esc($province) ?>"><?= esc($province) ?></option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <button class="btn btn-primary btn-lg w-100 shadow-sm" onclick="filterLocations()">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Map Section -->
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-map-marked-alt me-2 text-primary"></i>Peta Lokasi Agen
                        </h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="fitMapToMarkers()">
                                <i class="fas fa-expand-arrows-alt me-1"></i>Lihat Semua
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleMapView()">
                                <i class="fas fa-layer-group me-1"></i>Satelit
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 600px; width: 100%; border-radius: 0 0 0.375rem 0.375rem;"></div>
                </div>
            </div>
        </div>

        <!-- Agent List Section -->
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2 text-primary"></i>Daftar Agen
                        </h5>
                        <span class="badge bg-primary fs-6 px-3 py-2" id="agentCount"><?= count($agents) ?></span>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    <?php if (!empty($agents)): ?>
                        <div id="agentList">
                            <?php foreach ($agents as $index => $agent): ?>
                                <div class="agent-item border-bottom" data-agent-id="<?= $agent->id ?>" data-lat="<?= $agent->latitude ?>" data-lng="<?= $agent->longitude ?>" data-province="<?= esc($agent->province_name ?? '') ?>">
                                    <div class="p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-2 text-dark">
                                                    <i class="fas fa-building me-2 text-primary"></i>
                                                    <?= esc($agent->name) ?>
                                                </h6>
                                                <span class="badge bg-light text-dark">
                                                    <i class="fas fa-code me-1"></i><?= esc($agent->code) ?>
                                                </span>
                                            </div>
                                            <button class="btn btn-outline-primary btn-sm" onclick="showOnMap(<?= $index ?>)">
                                                <i class="fas fa-map-marker-alt me-1"></i>Lihat
                                            </button>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <p class="text-muted small mb-1">
                                                <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                                <strong>Lokasi:</strong>
                                            </p>
                                            <p class="small mb-0 text-dark">
                                                <?= esc($agent->village_name ?? '') ?>, <?= esc($agent->district_name ?? '') ?><br>
                                                <?= esc($agent->regency_name ?? '') ?>, <?= esc($agent->province_name ?? '') ?>
                                            </p>
                                        </div>
                                        
                                        <?php if (!empty($agent->address)): ?>
                                            <div class="mb-3">
                                                <p class="text-muted small mb-1">
                                                    <i class="fas fa-home me-2 text-info"></i>
                                                    <strong>Alamat:</strong>
                                                </p>
                                                <p class="small mb-0 text-dark"><?= esc($agent->address) ?></p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="row g-2">
                                            <?php if (!empty($agent->phone)): ?>
                                                <div class="col-6">
                                                    <a href="tel:<?= esc($agent->phone) ?>" class="btn btn-outline-success btn-sm w-100">
                                                        <i class="fas fa-phone me-1"></i>Call
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($agent->email)): ?>
                                                <div class="col-6">
                                                    <a href="mailto:<?= esc($agent->email) ?>" class="btn btn-outline-info btn-sm w-100">
                                                        <i class="fas fa-envelope me-1"></i>Email
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-map-marker-alt fa-4x text-muted opacity-50"></i>
                            </div>
                            <h5 class="text-muted mb-3">Tidak ada agen tersedia</h5>
                            <p class="text-muted">Belum ada agen yang terdaftar di sistem.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map;
let markers = [];
let agentData = <?= json_encode($agents) ?>;
let isSatelliteView = false;

// Initialize map
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map centered on Indonesia
    map = L.map('map').setView([-2.5489, 118.0149], 5);
    
    // Add OpenStreetMap tiles
    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    });
    
    // Add satellite layer
    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '© Esri'
    });
    
    // Add default layer
    osmLayer.addTo(map);
    
    // Store layers for toggling
    map.osmLayer = osmLayer;
    map.satelliteLayer = satelliteLayer;
    
    // Add markers for each agent
    agentData.forEach((agent, index) => {
        if (agent.latitude && agent.longitude) {
            // Create custom icon
            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div class="marker-pin">
                    <i class="fas fa-building"></i>
                </div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });
            
            const marker = L.marker([parseFloat(agent.latitude), parseFloat(agent.longitude)], {
                icon: customIcon
            })
            .addTo(map)
            .bindPopup(`
                <div class="popup-content">
                    <h6 class="fw-bold mb-2 text-primary">${agent.name}</h6>
                    <p class="small mb-1"><strong>Kode:</strong> ${agent.code}</p>
                    <p class="small mb-1"><strong>Lokasi:</strong> ${agent.village_name || ''}, ${agent.district_name || ''}</p>
                    <p class="small mb-1"><strong>Kota:</strong> ${agent.regency_name || ''}, ${agent.province_name || ''}</p>
                    ${agent.phone ? `<p class="small mb-1"><strong>Telp:</strong> <a href="tel:${agent.phone}" class="text-success">${agent.phone}</a></p>` : ''}
                    ${agent.email ? `<p class="small mb-1"><strong>Email:</strong> <a href="mailto:${agent.email}" class="text-info">${agent.email}</a></p>` : ''}
                </div>
            `);
            
            markers.push(marker);
        }
    });
    
    // Fit map to show all markers
    if (markers.length > 0) {
        const group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
});

// Show specific agent on map
function showOnMap(index) {
    const agent = agentData[index];
    if (agent.latitude && agent.longitude) {
        map.setView([parseFloat(agent.latitude), parseFloat(agent.longitude)], 15);
        
        // Highlight the selected agent item
        document.querySelectorAll('.agent-item').forEach(item => {
            item.classList.remove('highlighted');
        });
        document.querySelector(`[data-agent-id="${agent.id}"]`).classList.add('highlighted');
        
        // Open popup for the marker
        markers.forEach(marker => {
            const lat = marker.getLatLng().lat;
            const lng = marker.getLatLng().lng;
            if (Math.abs(lat - parseFloat(agent.latitude)) < 0.0001 && 
                Math.abs(lng - parseFloat(agent.longitude)) < 0.0001) {
                marker.openPopup();
            }
        });
    }
}

// Fit map to show all markers
function fitMapToMarkers() {
    if (markers.length > 0) {
        const group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
}

// Toggle map view between normal and satellite
function toggleMapView() {
    if (isSatelliteView) {
        map.removeLayer(map.satelliteLayer);
        map.addLayer(map.osmLayer);
        isSatelliteView = false;
    } else {
        map.removeLayer(map.osmLayer);
        map.addLayer(map.satelliteLayer);
        isSatelliteView = true;
    }
}

// Filter locations
function filterLocations() {
    const searchTerm = document.getElementById('searchLocation').value.toLowerCase();
    const selectedProvince = document.getElementById('filterProvince').value;
    const agentItems = document.querySelectorAll('.agent-item');
    let visibleCount = 0;
    
    agentItems.forEach(item => {
        const agentName = item.querySelector('h6').textContent.toLowerCase();
        const agentProvince = item.dataset.province.toLowerCase();
        const agentContent = item.textContent.toLowerCase();
        
        const matchesSearch = agentName.includes(searchTerm) || agentContent.includes(searchTerm);
        const matchesProvince = !selectedProvince || agentProvince.includes(selectedProvince.toLowerCase());
        
        if (matchesSearch && matchesProvince) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    document.getElementById('agentCount').textContent = visibleCount;
}

// Search as you type
document.getElementById('searchLocation').addEventListener('input', filterLocations);
document.getElementById('filterProvince').addEventListener('change', filterLocations);
</script>

<style>
/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="0,0 1000,0 1000,100 0,80"/></svg>') no-repeat bottom;
    background-size: cover;
}

/* Agent Items */
.agent-item {
    transition: all 0.3s ease;
    cursor: pointer;
    border-left: 4px solid transparent;
}

.agent-item:hover {
    transform: translateX(5px);
    border-left-color: #007bff;
    background-color: #f8f9fa;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.agent-item.highlighted {
    border-left-color: #28a745;
    background-color: #e8f5e8;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
}

/* Map Styling */
#map {
    border-radius: 0 0 0.375rem 0.375rem;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

/* Custom Map Markers */
.custom-marker {
    background: transparent;
    border: none;
}

.marker-pin {
    width: 30px;
    height: 30px;
    background: #007bff;
    border: 3px solid #fff;
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.marker-pin i {
    color: white;
    font-size: 12px;
    transform: rotate(45deg);
}

/* Popup Styling */
.leaflet-popup-content-wrapper {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.popup-content {
    min-width: 200px;
}

/* Scrollbar Styling */
.card-body::-webkit-scrollbar {
    width: 8px;
}

.card-body::-webkit-scrollbar-track {
    background: #f1f3f4;
    border-radius: 4px;
}

.card-body::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #007bff, #0056b3);
    border-radius: 4px;
}

.card-body::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #0056b3, #004085);
}

/* Form Controls */
.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Badge Styling */
.badge {
    font-weight: 500;
    letter-spacing: 0.5px;
}

/* Button Hover Effects */
.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Card Hover Effects */
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-section {
        padding: 2rem 0;
    }
    
    .hero-section h1 {
        font-size: 2rem;
    }
    
    .agent-item {
        margin-bottom: 1rem;
    }
    
    #map {
        height: 400px !important;
    }
}

/* Loading Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.agent-item {
    animation: fadeInUp 0.6s ease forwards;
}

.agent-item:nth-child(1) { animation-delay: 0.1s; }
.agent-item:nth-child(2) { animation-delay: 0.2s; }
.agent-item:nth-child(3) { animation-delay: 0.3s; }
.agent-item:nth-child(4) { animation-delay: 0.4s; }
.agent-item:nth-child(5) { animation-delay: 0.5s; }
</style>
<?= $this->endSection() ?>
