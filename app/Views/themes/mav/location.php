<?= $this->extend('themes/mav/layout/main') ?>

<?= $this->section('content') ?>

<div class="container grid-locations">
  <aside class="store-list" aria-label="Toko terdekat">
    <h2>Agen Terdekat</h2>
    <ul id="stores">
      <?php if (!empty($agents)): ?>
        <?php foreach ($agents as $agent): ?>
          <li class="store" 
              data-lat="<?= esc($agent['latitude'] ?? '') ?>" 
              data-lng="<?= esc($agent['longitude'] ?? '') ?>"
              data-name="<?= esc($agent['name']) ?>"
              data-address="<?= esc($agent['address']) ?>"
              data-phone="<?= esc($agent['phone'] ?? '') ?>"
              data-province="<?= esc($agent['province_name'] ?? '') ?>"
              data-regency="<?= esc($agent['regency_name'] ?? '') ?>"
              data-district="<?= esc($agent['district_name'] ?? '') ?>"
              data-village="<?= esc($agent['village_name'] ?? '') ?>">
            <div class="name"><?= esc($agent['name']) ?></div>
            <div class="meta">
              <?= esc($agent['address']) ?>
              <?php if (!empty($agent['province_name'])): ?>
                <?php
                  $locationParts = array_filter([
                    $agent['village_name'],
                    $agent['district_name'],
                    $agent['regency_name'],
                    $agent['province_name']
                  ]);
                ?>
                <?php if (!empty($locationParts)): ?>
                  <br><?= esc(implode(', ', $locationParts)) ?>
                <?php endif; ?>
              <?php endif; ?>
              <?php if (!empty($agent['phone'])): ?>
                <br>📞 <?= esc($agent['phone']) ?>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li class="store"><div class="meta">Tidak ada data toko tersedia</div></li>
      <?php endif; ?>
    </ul>
  </aside>
  
  <section class="map-wrap">
    <div id="map" class="map"></div>
  </section>
</div>

<style>
.store-list {
  max-height: 540px;
  overflow: auto;
}

.store {
  cursor: pointer;
  transition: all 0.2s;
}

.store:hover {
  background: #222326;
  border-color: var(--amber);
}

/* Custom Leaflet Popup Styling to Match MAV Theme */
.custom-popup {
  background: #141416 !important;
  border: 1px solid #222327 !important;
  border-radius: 12px !important;
  color: #e7e7ea !important;
  font-family: "Plus Jakarta Sans", system-ui, sans-serif;
}

.custom-popup .leaflet-popup-content-wrapper {
  background: #141416 !important;
  border-radius: 12px !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
}

.custom-popup .leaflet-popup-tip {
  background: #141416 !important;
  border: 1px solid #222327 !important;
}

.custom-popup .leaflet-popup-close-button {
  color: #e7e7ea !important;
}
</style>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize map
  const map = L.map('map').setView([-6.2088, 106.8456], 5); // Default to Indonesia
  
  // Add tile layer
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);
  
  const markers = [];
  let activeMarker = null;
  
  // Get all store items
  const storeItems = document.querySelectorAll('.store');
  
  if (storeItems.length > 0) {
    const bounds = L.latLngBounds();
    
    console.log('Total store items found:', storeItems.length);
    
    storeItems.forEach(function(item, index) {
      const lat = parseFloat(item.dataset.lat);
      const lng = parseFloat(item.dataset.lng);
      const name = item.dataset.name;
      const address = item.dataset.address;
      const phone = item.dataset.phone || '';
      
      console.log(`Store ${index + 1}:`, {
        name: name,
        lat: lat,
        lng: lng,
        rawLat: item.dataset.lat,
        rawLng: item.dataset.lng
      });
      
      // Build full address
      const locationParts = [
        item.dataset.village,
        item.dataset.district,
        item.dataset.regency,
        item.dataset.province
      ].filter(Boolean);
      
      let fullAddress = address;
      if (locationParts.length > 0) {
        fullAddress += ', ' + locationParts.join(', ');
      }
      
      if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
        // Create marker with custom icon (optional)
        const marker = L.marker([lat, lng]).addTo(map);
        
        console.log(`Marker created for ${name} at [${lat}, ${lng}]`);
        
        // Build popup content with MAV theme styling
        let popupContent = '<div style="font-weight: 600; margin-bottom: 8px; color: #ffc12e;">' + name + '</div>';
        popupContent += '<div style="margin-bottom: 6px; color: #c6c7cc; font-size: 14px;">' + fullAddress + '</div>';
        if (phone) {
          popupContent += '<div style="color: #7b7d86; font-size: 13px;">📞 ' + phone + '</div>';
        }
        
        marker.bindPopup(popupContent, {
          maxWidth: 280,
          className: 'custom-popup'
        });
        bounds.extend([lat, lng]);
        
        markers.push(marker);
        
        // Add click handler to scroll to store in list
        marker.on('click', function() {
          item.scrollIntoView({ behavior: 'smooth', block: 'center' });
          item.style.background = '#222326';
          item.style.borderColor = 'var(--amber)';
          setTimeout(() => {
            item.style.background = '';
            item.style.borderColor = '';
          }, 1000);
        });
      } else {
        console.warn(`Invalid coordinates for ${name}: lat=${lat}, lng=${lng}`);
      }
    });
    
    console.log(`Total markers created: ${markers.length}`);
    
    // Fit map to show all markers
    if (markers.length > 0) {
      map.fitBounds(bounds);
      console.log('Map bounds adjusted to show all markers');
    } else {
      console.warn('No markers were created. Check if agent data has valid latitude/longitude.');
    }
  } else {
    console.warn('No store items found in the DOM');
  }
});
</script>

<?= $this->endSection() ?>

