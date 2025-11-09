<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-04
 * Github: github.com/mikhaelfelian
 * Description: POS-style grid view for agent items/products
 */
?>
<div class="container-fluid px-3 py-4">
	<div class="row mb-4">
		<div class="col-12">
			<h4 class="mb-3"><?= $title ?? 'Daftar Produk' ?></h4>
			
			<!-- Filter Bar -->
			<div class="card shadow-sm mb-4">
				<div class="card-body">
					<form method="GET" action="<?= $config->baseURL ?>agent/item" id="filter-form">
						<div class="row g-3 align-items-end">
							<div class="col-md-8">
								<label for="filter" class="form-label fw-semibold">Filter</label>
								<input type="text" 
									class="form-control form-control-lg" 
									id="filter" 
									name="q" 
									value="<?= esc($searchQuery ?? '') ?>" 
									placeholder="Cari produk..." 
									autocomplete="off">
							</div>
							<div class="col-md-4">
								<button type="submit" class="btn btn-primary btn-lg w-100">
									<i class="fas fa-search me-2"></i>Cari
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- Product Grid -->
	<?php if (!empty($items) && is_array($items)): ?>
		<div class="row g-4 mb-4">
			<?php foreach ($items as $item): ?>
				<div class="col-6 col-md-4 col-lg-3 col-xl-2">
					<div class="card shadow-sm h-100 product-card" style="border: 1px solid #dee2e6;">
						<!-- Product Image -->
						<div class="card-img-top position-relative" style="height: 180px; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; overflow: hidden;">
							<?php if (!empty($item['image'])): ?>
								<img src="<?= $config->baseURL ?>public/images/item/<?= esc($item['image']) ?>" 
									alt="<?= esc($item['name']) ?>" 
									class="img-fluid" 
									style="max-height: 100%; width: auto; object-fit: contain;">
							<?php else: ?>
								<div class="text-muted text-center p-3">
									<i class="fas fa-image fa-3x mb-2"></i>
									<p class="small mb-0">No Image</p>
								</div>
							<?php endif; ?>
						</div>
						
						<!-- Product Info -->
						<div class="card-body d-flex flex-column p-3">
							<h6 class="card-title mb-2" style="font-size: 0.9rem; line-height: 1.3; min-height: 2.6em; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
								<?= esc($item['name']) ?>
							</h6>
							
							<div class="mb-3">
								<strong class="text-primary" style="font-size: 1rem;">
									<?= format_angka_rp($item['price'] ?? 0) ?>
								</strong>
							</div>
							
							<!-- Buy Button -->
							<button type="button" 
								class="btn btn-primary w-100 mt-auto beli-btn" 
								data-item-id="<?= $item['id'] ?>"
								data-item-name="<?= esc($item['name']) ?>"
								data-item-price="<?= $item['price'] ?? 0 ?>">
								<i class="fas fa-shopping-cart me-2"></i>Beli
							</button>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Pagination -->
		<?php if (isset($pager) && $pagerInfo['totalPages'] > 1): ?>
			<div class="row">
				<div class="col-12">
					<nav aria-label="Page navigation">
						<ul class="pagination justify-content-center">
							<?php if ($pager->hasPrevious()): ?>
								<li class="page-item">
									<a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="First">
										<span aria-hidden="true">&laquo;&laquo;</span>
									</a>
								</li>
								<li class="page-item">
									<a class="page-link" href="<?= $pager->getPrevious() ?>" aria-label="Previous">
										<span aria-hidden="true">&laquo;</span>
									</a>
								</li>
							<?php else: ?>
								<li class="page-item disabled">
									<span class="page-link" aria-hidden="true">&laquo;&laquo;</span>
								</li>
								<li class="page-item disabled">
									<span class="page-link" aria-hidden="true">&laquo;</span>
								</li>
							<?php endif; ?>
							
							<?php 
							$pager->setSurroundCount(2);
							foreach ($pager->links() as $link): ?>
								<li class="page-item <?= $link['active'] ? 'active' : '' ?>">
									<?php if ($link['active']): ?>
										<span class="page-link"><?= $link['title'] ?></span>
									<?php else: ?>
										<a class="page-link" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
							
							<?php if ($pager->hasNext()): ?>
								<li class="page-item">
									<a class="page-link" href="<?= $pager->getNext() ?>" aria-label="Next">
										<span aria-hidden="true">&raquo;</span>
									</a>
								</li>
								<li class="page-item">
									<a class="page-link" href="<?= $pager->getLast() ?>" aria-label="Last">
										<span aria-hidden="true">&raquo;&raquo;</span>
									</a>
								</li>
							<?php else: ?>
								<li class="page-item disabled">
									<span class="page-link" aria-hidden="true">&raquo;</span>
								</li>
								<li class="page-item disabled">
									<span class="page-link" aria-hidden="true">&raquo;&raquo;</span>
								</li>
							<?php endif; ?>
						</ul>
					</nav>
					
					<!-- Pagination Info -->
					<div class="text-center text-muted mt-2">
						<small>
							Menampilkan <?= count($items) ?> dari <?= $pagerInfo['totalItems'] ?> produk
							(Halaman <?= $pagerInfo['currentPage'] ?> dari <?= $pagerInfo['totalPages'] ?>)
						</small>
					</div>
				</div>
			</div>
		<?php endif; ?>
		
	<?php else: ?>
		<!-- No Items Found -->
		<div class="row">
			<div class="col-12">
				<div class="card shadow-sm">
					<div class="card-body text-center py-5">
						<i class="fas fa-box-open fa-3x text-muted mb-3"></i>
						<h5 class="text-muted">Tidak ada produk ditemukan</h5>
						<?php if (!empty($searchQuery)): ?>
							<p class="text-muted">Coba gunakan kata kunci lain untuk pencarian</p>
							<a href="<?= $config->baseURL ?>agent/item" class="btn btn-outline-primary mt-2">
								<i class="fas fa-redo me-2"></i>Reset Filter
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>

<script>
$(document).ready(function() {
	// Get CSRF token
	const csrfTokenName = '<?= csrf_token() ?>';
	let csrfHash = '<?= csrf_hash() ?>';
	
	// Auto-submit filter on Enter key
	$('#filter').on('keypress', function(e) {
		if (e.which === 13) {
			e.preventDefault();
			$('#filter-form').submit();
		}
	});
	
	// Handle Buy button click - Add to cart via AJAX
	$('.beli-btn').on('click', function() {
		var $btn = $(this);
		var itemId = $btn.data('item-id');
		var itemName = $btn.data('item-name');
		var itemPrice = $btn.data('item-price');
		
		// Disable button during request
		$btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menambahkan...');
		
		// Add item to cart via AJAX
		$.ajax({
			url: '<?= $config->baseURL ?>agent/sales/addToCart',
			type: 'POST',
			data: {
				item_id: itemId,
				item_name: itemName,
				item_price: itemPrice,
				qty: 1,
				[csrfTokenName]: csrfHash
			},
			dataType: 'json',
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			},
			success: function(response) {
				// Update CSRF token if provided in response
				if (response.csrf_hash) {
					csrfHash = response.csrf_hash;
				}
				
				if (response.status === 'success') {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'success',
							title: 'Berhasil',
							text: response.message || 'Item berhasil ditambahkan ke keranjang',
							showCancelButton: true,
							confirmButtonText: 'Lihat Keranjang',
							cancelButtonText: 'Lanjutkan Belanja',
							confirmButtonColor: '#3085d6',
							cancelButtonColor: '#6c757d'
						}).then((result) => {
							if (result.isConfirmed) {
								window.location.href = '<?= $config->baseURL ?>agent/sales/cart';
							}
						});
					} else {
						if (confirm(response.message + '\n\nLihat keranjang sekarang?')) {
							window.location.href = '<?= $config->baseURL ?>agent/sales/cart';
						}
					}
				} else {
					if (typeof Swal !== 'undefined') {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: response.message || 'Gagal menambahkan item ke keranjang'
						});
					} else {
						alert(response.message || 'Gagal menambahkan item ke keranjang');
					}
				}
			},
			error: function(xhr) {
				var errorMsg = 'Terjadi kesalahan saat menambahkan item';
				
				// Update CSRF token if provided in error response
				if (xhr.responseJSON && xhr.responseJSON.csrf_hash) {
					csrfHash = xhr.responseJSON.csrf_hash;
				}
				
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMsg = xhr.responseJSON.message;
				} else if (xhr.status === 403) {
					errorMsg = 'Akses ditolak. Silakan refresh halaman dan coba lagi.';
				} else if (xhr.status === 404) {
					errorMsg = 'Endpoint tidak ditemukan. Silakan hubungi administrator.';
				} else if (xhr.status === 500) {
					errorMsg = 'Terjadi kesalahan server. Silakan coba lagi nanti.';
				}
				
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: errorMsg
					});
				} else {
					alert(errorMsg);
				}
			},
			complete: function() {
				$btn.prop('disabled', false).html('<i class="fas fa-shopping-cart me-2"></i>Beli');
			}
		});
	});
	
	// Helper function to format currency
	function formatCurrency(amount) {
		return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
	}
});
</script>

<style>
.product-card {
	transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover {
	transform: translateY(-5px);
	box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}

.beli-btn {
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.card-img-top img {
	transition: transform 0.3s;
}

.product-card:hover .card-img-top img {
	transform: scale(1.05);
}
</style>

