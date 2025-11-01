<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Description: SN (Serial Number) input tab for item form
 */
?>
<?php if (!empty($id)): ?>
	<div id="sn-tab-content">
		<div class="text-center py-4">
			<div class="spinner-border text-primary" role="status">
				<span class="visually-hidden">Loading...</span>
			</div>
		</div>
	</div>
	<script>
	$(document).ready(function() {
		// Load SN tab content when tab is shown
		$('a[href="#tab-input-sn"]').on('shown.bs.tab', function() {
			const itemId = '<?= esc($id) ?>';
			const baseURL = '<?= ($config->baseURL ?? base_url()) ?>';
			
			if (!$('#sn-tab-content').children('.card').length && !$('#sn-tab-content').hasClass('loading')) {
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
							// Try to extract error message from response
							try {
								const response = JSON.parse(xhr.responseText);
								if (response.message) errorMsg = response.message;
							} catch(e) {
								// If not JSON, check if it's HTML error
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
		});
		
		// Load immediately if SN tab is active
		if ($('a[href="#tab-input-sn"]').hasClass('active')) {
			$('a[href="#tab-input-sn"]').trigger('shown.bs.tab');
		}
	});
	</script>
<?php else: ?>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i>
		Simpan item terlebih dahulu untuk menambahkan Serial Number (SN)
	</div>
<?php endif; ?>

