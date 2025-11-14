<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Description: SN (Serial Number) input tab for item form
 */
?>
<?php if (!empty($id)): ?>
	<!-- Nested Tabs Navigation -->
	<ul class="nav nav-tabs mb-3" id="snSubTabs" role="tablist">
		<li class="nav-item" role="presentation">
			<a class="nav-link active" id="input-sn-tab" data-bs-toggle="tab" href="#sub-tab-input-sn" role="tab" aria-controls="sub-tab-input-sn" aria-selected="true">
				<i class="fas fa-plus-circle me-1"></i> Input SN
			</a>
		</li>
		<li class="nav-item" role="presentation">
			<a class="nav-link" id="sold-sn-tab" data-bs-toggle="tab" href="#sub-tab-sold-sn" role="tab" aria-controls="sub-tab-sold-sn" aria-selected="false">
				<i class="fas fa-shopping-cart me-1"></i> SN Terjual
			</a>
		</li>
	</ul>

	<!-- Nested Tab Content -->
	<div class="tab-content" id="snSubTabContent">
		<!-- Input SN Tab Pane -->
		<div class="tab-pane fade show active" id="sub-tab-input-sn" role="tabpanel" aria-labelledby="input-sn-tab">
			<div id="sn-tab-content">
				<div class="text-center py-4">
					<div class="spinner-border text-primary" role="status">
						<span class="visually-hidden">Loading...</span>
					</div>
				</div>
			</div>
		</div>

		<!-- SN Terjual Tab Pane -->
		<div class="tab-pane fade" id="sub-tab-sold-sn" role="tabpanel" aria-labelledby="sold-sn-tab">
			<div id="sold-sn-tab-content">
				<div class="text-center py-4">
					<div class="spinner-border text-primary" role="status">
						<span class="visually-hidden">Loading...</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
	$(document).ready(function() {
		const itemId = '<?= esc($id) ?>';
		const baseURL = '<?= ($config->baseURL ?? base_url()) ?>';

		// Load Input SN tab content when main tab is shown
		$('a[href="#tab-input-sn"]').on('shown.bs.tab', function() {
			// Load Input SN content if not already loaded
			if (!$('#sn-tab-content').children('.card').length && !$('#sn-tab-content').hasClass('loading')) {
				loadInputSnContent();
			}
		});

		// Load Input SN content when nested tab is shown
		$('#input-sn-tab').on('shown.bs.tab', function() {
			if (!$('#sn-tab-content').children('.card').length && !$('#sn-tab-content').hasClass('loading')) {
				loadInputSnContent();
			}
		});

		// Load Sold SN content when nested tab is shown
		$('#sold-sn-tab').on('shown.bs.tab', function() {
			if (!$('#sold-sn-tab-content').children('.card').length && !$('#sold-sn-tab-content').hasClass('loading')) {
				loadSoldSnContent();
			}
		});

		// Function to load Input SN content
		function loadInputSnContent() {
			$('#sn-tab-content').addClass('loading');
			$.ajax({
				url: baseURL + 'item-sn/' + itemId,
				type: 'GET',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				success: function(html) {
					$('#sn-tab-content').removeClass('loading').html(html || '<div class="alert alert-warning">Tidak ada data yang dikembalikan</div>');
				},
				error: function(xhr, status, error) {
					console.error('SN Tab Load Error:', {xhr: xhr, status: status, error: error});
					let errorMsg = 'Gagal memuat data SN';
					if (xhr.responseText) {
						try {
							const response = JSON.parse(xhr.responseText);
							if (response.message) errorMsg = response.message;
						} catch(e) {
							if (xhr.responseText.includes('error') || xhr.responseText.includes('Error')) {
								errorMsg = 'Terjadi kesalahan saat memuat form SN';
							}
						}
					}
					$('#sn-tab-content').removeClass('loading').html(
						'<div class="alert alert-danger">' +
						'<i class="fas fa-exclamation-triangle me-2"></i>' +
						errorMsg + 
						'<br><small class="text-muted">Status: ' + (xhr.status || 'Unknown') + ' - ' + (error || 'Unknown error') + '</small>' +
						'</div>'
					);
				},
				complete: function() {
					$('#sn-tab-content').removeClass('loading');
				}
			});
		}

		// Function to load Sold SN content
		function loadSoldSnContent() {
			$('#sold-sn-tab-content').addClass('loading');
			$.ajax({
				url: baseURL + 'item-sn/getSoldSnList/' + itemId,
				type: 'GET',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				success: function(html) {
					$('#sold-sn-tab-content').removeClass('loading').html(html || '<div class="alert alert-warning">Tidak ada data yang dikembalikan</div>');
					// Scripts in the loaded HTML will execute automatically
					// Give it a moment to ensure DOM is ready
					setTimeout(function() {
						// Trigger any initialization if needed
						if (typeof window.initSoldSnDataTable === 'function') {
							window.initSoldSnDataTable();
						}
					}, 200);
				},
				error: function(xhr, status, error) {
					console.error('Sold SN Tab Load Error:', {xhr: xhr, status: status, error: error});
					let errorMsg = 'Gagal memuat data SN Terjual';
					if (xhr.responseText) {
						try {
							const response = JSON.parse(xhr.responseText);
							if (response.message) errorMsg = response.message;
						} catch(e) {
							if (xhr.responseText.includes('error') || xhr.responseText.includes('Error')) {
								errorMsg = 'Terjadi kesalahan saat memuat data SN Terjual';
							}
						}
					}
					$('#sold-sn-tab-content').removeClass('loading').html(
						'<div class="alert alert-danger">' +
						'<i class="fas fa-exclamation-triangle me-2"></i>' +
						errorMsg + 
						'<br><small class="text-muted">Status: ' + (xhr.status || 'Unknown') + ' - ' + (error || 'Unknown error') + '</small>' +
						'</div>'
					);
				},
				complete: function() {
					$('#sold-sn-tab-content').removeClass('loading');
				}
			});
		}

		// Load immediately if SN tab is active
		if ($('a[href="#tab-input-sn"]').hasClass('active')) {
			loadInputSnContent();
		}
	});
	</script>
<?php else: ?>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i>
		Simpan item terlebih dahulu untuk menambahkan Serial Number (SN)
	</div>
<?php endif; ?>

