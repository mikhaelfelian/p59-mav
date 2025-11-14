<?php
/**
 * Agent Product Catalog View
 * Professional catalogue with advanced filtering & responsive layout.
 *
 * @var array $filters
 * @var array $categories
 * @var array $brands
 * @var array $items
 * @var array $pagerInfo
 * @var array $priceRange
 * @var array $sortOptions
 * @var array $perPageOptions
 */

$filters = $filters ?? [];
$selectedCategories = $filters['category'] ?? [];
$selectedBrands = $filters['brand'] ?? [];
$searchValue = trim((string) ($filters['search'] ?? ''));
$availability = $filters['availability'] ?? 'all';
$viewMode = $filters['view'] ?? 'grid';

$priceMinValue = $filters['price_min'] ?? null;
$priceMaxValue = $filters['price_max'] ?? null;

$appliedFilterCount = 0;
$appliedFilterCount += $searchValue !== '' ? 1 : 0;
$appliedFilterCount += !empty($selectedCategories) ? 1 : 0;
$appliedFilterCount += !empty($selectedBrands) ? 1 : 0;
$appliedFilterCount += ($priceMinValue !== null || $priceMaxValue !== null) ? 1 : 0;
$appliedFilterCount += ($availability !== 'all') ? 1 : 0;
$hasActiveFilters = $appliedFilterCount > 0;

$totalItems = $pagerInfo['totalItems'] ?? count($items);
$firstItem = $pagerInfo['firstItem'] ?? 0;
$lastItem = $pagerInfo['lastItem'] ?? 0;
$currentPage = $pagerInfo['currentPage'] ?? 1;
$totalPages = $pagerInfo['totalPages'] ?? 1;
?>

<div class="container-fluid py-4 agent-catalog-page">
    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-12 col-xl-3">
            <form id="agentCatalogFilter" class="card shadow-sm border-0 sticky-top filter-card" style="top: 92px;" method="get" action="<?= $config->baseURL ?>agent/item">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-1 d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-filter me-2 text-primary"></i>Filter Produk</span>
                        <?php if ($hasActiveFilters): ?>
                            <span class="badge bg-primary rounded-pill"><?= $appliedFilterCount ?></span>
                        <?php endif; ?>
                    </h5>
                    <p class="text-muted small mb-0">Sesuaikan pencarian produk sesuai kebutuhan Anda.</p>
                </div>

                <div class="card-body">
                    <input type="hidden" name="view" id="viewModeInput" value="<?= esc($viewMode) ?>">

                    <!-- Search -->
                    <div class="mb-4">
                        <label for="catalogSearch" class="form-label fw-semibold text-uppercase small text-muted">Pencarian Kata Kunci</label>
                        <div class="input-group input-group-lg rounded-pill shadow-sm">
                            <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-primary"></i></span>
                            <input type="text"
                                   id="catalogSearch"
                                   name="q"
                                   value="<?= esc($searchValue) ?>"
                                   class="form-control border-0"
                                   placeholder="Cari nama, SKU, deskripsi..."
                                   autocomplete="off">
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label fw-semibold text-uppercase small text-muted mb-0">Kategori</label>
                            <?php if (!empty($categories)): ?>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none text-muted clear-section" data-target="category">
                                    Reset
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($categories)): ?>
                            <div class="filter-checkbox-list">
                                <?php foreach ($categories as $category): ?>
                                    <?php
                                    $categoryId = $category['id'] ?? null;
                                    if (!$categoryId) {
                                        continue;
                                    }
                                    $isChecked = in_array((int) $categoryId, $selectedCategories, true);
                                    ?>
                                    <div class="form-check">
                                        <input class="form-check-input auto-submit" type="checkbox" name="category[]" value="<?= esc($categoryId) ?>" id="cat-<?= esc($categoryId) ?>" <?= $isChecked ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="cat-<?= esc($categoryId) ?>">
                                            <?= esc($category['category'] ?? 'Kategori') ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small fst-italic mb-0">Kategori belum tersedia.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Brands -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label fw-semibold text-uppercase small text-muted mb-0">Brand</label>
                            <?php if (!empty($brands)): ?>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none text-muted clear-section" data-target="brand">
                                    Reset
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($brands)): ?>
                            <div class="filter-checkbox-list">
                                <?php foreach ($brands as $brand): ?>
                                    <?php
                                    $brandId = $brand['id'] ?? null;
                                    if (!$brandId) {
                                        continue;
                                    }
                                    $isChecked = in_array((int) $brandId, $selectedBrands, true);
                                    ?>
                                    <div class="form-check">
                                        <input class="form-check-input auto-submit" type="checkbox" name="brand[]" value="<?= esc($brandId) ?>" id="brand-<?= esc($brandId) ?>" <?= $isChecked ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="brand-<?= esc($brandId) ?>">
                                            <?= esc($brand['name'] ?? 'Brand') ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small fst-italic mb-0">Brand belum tersedia.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-uppercase small text-muted">Rentang Harga (Rp)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="form-floating">
                                    <input type="number"
                                           min="0"
                                           step="1000"
                                           class="form-control"
                                           id="priceMin"
                                           name="price_min"
                                           value="<?= $priceMinValue !== null ? esc((int) $priceMinValue) : '' ?>"
                                           placeholder="Min">
                                    <label for="priceMin">Minimal</label>
                                </div>
                                <small class="text-muted d-block mt-1">≥ <?= format_angka_rp($priceRange['min'] ?? 0) ?></small>
                            </div>
                            <div class="col-6">
                                <div class="form-floating">
                                    <input type="number"
                                           min="0"
                                           step="1000"
                                           class="form-control"
                                           id="priceMax"
                                           name="price_max"
                                           value="<?= $priceMaxValue !== null ? esc((int) $priceMaxValue) : '' ?>"
                                           placeholder="Max">
                                    <label for="priceMax">Maksimal</label>
                                </div>
                                <small class="text-muted d-block mt-1">≤ <?= format_angka_rp($priceRange['max'] ?? 0) ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Availability -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-uppercase small text-muted">Ketersediaan</label>
                        <select class="form-select auto-submit" name="availability">
                            <option value="all" <?= $availability === 'all' ? 'selected' : '' ?>>Semua Produk</option>
                            <option value="stockable" <?= $availability === 'stockable' ? 'selected' : '' ?>>Produk Stok / Serial</option>
                            <option value="non_stock" <?= $availability === 'non_stock' ? 'selected' : '' ?>>Produk Non-Stok</option>
                        </select>
                    </div>
                </div>

                <div class="card-footer bg-light border-0 d-flex flex-column gap-2">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill">
                            <i class="fas fa-filter me-2"></i>Terapkan Filter
                        </button>
                    </div>
                    <div class="d-grid">
                        <a href="<?= $config->baseURL ?>agent/item" class="btn btn-outline-secondary rounded-pill<?= $hasActiveFilters ? '' : ' disabled' ?>">
                            <i class="fas fa-undo me-2"></i>Reset Filter
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Product Catalogue -->
        <div class="col-12 col-xl-9">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body pb-2">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div>
                            <h4 class="mb-1"><?= esc($title ?? 'Katalog Produk') ?></h4>
                            <div class="text-muted small">
                                <?php if ($totalItems > 0): ?>
                                    Menampilkan <strong><?= format_angka($firstItem) ?></strong> – <strong><?= format_angka($lastItem) ?></strong> dari <strong><?= format_angka($totalItems) ?></strong> produk
                                    (Halaman <?= format_angka($currentPage) ?> dari <?= format_angka($totalPages) ?>)
                                <?php else: ?>
                                    Tidak ada produk yang sesuai dengan filter Anda.
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="btn-group" role="group" aria-label="View mode">
                                <button type="button" class="btn btn-outline-secondary view-toggle<?= $viewMode === 'grid' ? ' active' : '' ?>" data-view="grid" title="Tampilan Grid">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary view-toggle<?= $viewMode === 'list' ? ' active' : '' ?>" data-view="list" title="Tampilan List">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php if ($searchValue !== ''): ?>
                        <div class="alert alert-info alert-dismissible fade show mt-3 mb-0" role="alert">
                            <i class="fas fa-search me-2"></i>
                            Hasil pencarian untuk: <strong><?= esc($searchValue) ?></strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($items)): ?>
                <?php if ($viewMode === 'list'): ?>
                    <div class="list-group product-list-view mb-4">
                        <?php foreach ($items as $item): ?>
                            <?php
                            $itemId = $item['id'] ?? null;
                            $itemName = $item['name'] ?? 'Produk';
                            $categoryName = $item['category_name'] ?? 'Tanpa Kategori';
                            $brandName = $item['brand_name'] ?? 'Tanpa Brand';
                            $agentPrice = (float) ($item['agent_price'] ?? 0);
                            $displayPrice = $agentPrice > 0 ? $agentPrice : (float) ($item['price'] ?? 0);
                            $isStockable = ($item['is_stockable'] ?? '0') === '1';
                            ?>
                            <div class="list-group-item product-card-list border-0 shadow-sm mb-3 rounded-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-12 col-md-3">
                                        <div class="product-thumb ratio ratio-4x3 rounded-3 overflow-hidden bg-light d-flex align-items-center justify-content-center">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="<?= $config->baseURL ?>public/images/item/<?= esc($item['image']) ?>"
                                                     alt="<?= esc($itemName) ?>"
                                                     class="img-fluid">
                                            <?php else: ?>
                                                <div class="text-center text-muted">
                                                    <i class="fas fa-image fa-2x mb-2"></i>
                                                    <p class="small mb-0">Tidak ada gambar</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-secondary-subtle text-secondary fw-semibold"><?= esc($categoryName) ?></span>
                                            <span class="badge bg-info-subtle text-info fw-semibold"><?= esc($brandName) ?></span>
                                        </div>
                                        <h5 class="mb-2 text-dark fw-semibold"><?= esc($itemName) ?></h5>
                                        <?php if (!empty($item['sku'])): ?>
                                            <p class="text-primary fw-semibold mb-2"><i class="fas fa-hashtag me-1"></i>Kode: <?= esc($item['sku']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($item['short_description'])): ?>
                                            <p class="text-muted small mb-2"><?= esc($item['short_description']) ?></p>
                                        <?php elseif (!empty($item['description'])): ?>
                                            <?php
                                            $plainDescription = strip_tags((string) $item['description']);
                                            $shortDescription = mb_strlen($plainDescription) > 120
                                                ? mb_substr($plainDescription, 0, 117) . '...'
                                                : $plainDescription;
                                            ?>
                                            <p class="text-muted small mb-2"><?= esc($shortDescription) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-12 col-md-3 text-md-end">
                                        <div class="mb-3">
                                            <div class="text-primary fw-bold fs-5"><?= format_angka_rp($displayPrice) ?></div>
                                        </div>
                                        <button type="button"
                                                class="btn btn-primary btn-lg rounded-pill w-100 btn-add-cart"
                                                data-item-id="<?= esc($itemId) ?>"
                                                data-item-name="<?= esc($itemName) ?>"
                                                data-item-price="<?= esc($displayPrice) ?>">
                                            <i class="fas fa-shopping-cart me-2"></i>Tambah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxl-4 g-4 mb-4 product-grid-view">
                        <?php foreach ($items as $item): ?>
                            <?php
                            $itemId = $item['id'] ?? null;
                            $itemName = $item['name'] ?? 'Produk';
                            $categoryName = $item['category_name'] ?? 'Tanpa Kategori';
                            $brandName = $item['brand_name'] ?? 'Tanpa Brand';
                            $agentPrice = (float) ($item['agent_price'] ?? 0);
                            $displayPrice = $agentPrice > 0 ? $agentPrice : (float) ($item['price'] ?? 0);
                            $isStockable = ($item['is_stockable'] ?? '0') === '1';
                            ?>
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm product-card">
                                    <div class="ratio ratio-4x3 rounded-top overflow-hidden bg-light position-relative">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?= $config->baseURL ?>public/images/item/<?= esc($item['image']) ?>"
                                                 alt="<?= esc($itemName) ?>"
                                                 class="img-fluid product-image">
                                        <?php else: ?>
                                            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                                                <i class="fas fa-image fa-2x mb-2"></i>
                                                <span class="small">Tidak ada gambar</span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-secondary bg-opacity-75 text-white"><?= esc($categoryName) ?></span>
                                        </div>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-info bg-opacity-75 text-white"><?= esc($brandName) ?></span>
                                        </div>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title fw-semibold text-truncate"><?= esc($itemName) ?></h6>
                                        <?php if (!empty($item['sku'])): ?>
                                            <p class="text-primary fw-semibold mb-2"><i class="fas fa-hashtag me-1"></i>Kode: <?= esc($item['sku']) ?></p>
                                        <?php endif; ?>

                                        <div class="mb-3">
                                            <div class="text-primary fw-bold fs-5"><?= format_angka_rp($displayPrice) ?></div>
                                        </div>

                                        <div class="mt-auto">
                                            <button type="button"
                                                    class="btn btn-primary w-100 rounded-pill btn-add-cart"
                                                    data-item-id="<?= esc($itemId) ?>"
                                                    data-item-name="<?= esc($itemName) ?>"
                                                    data-item-price="<?= esc($displayPrice) ?>">
                                                <i class="fas fa-shopping-cart me-2"></i>Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if (isset($pager) && ($pagerInfo['totalPages'] ?? 1) > 1): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                <nav aria-label="Navigasi halaman katalog" class="flex-grow-1">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php if ($pager->hasPrevious()): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="Halaman pertama">
                                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= $pager->getPrevious() ?>" aria-label="Halaman sebelumnya">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled"><span class="page-link">&laquo;&laquo;</span></li>
                                            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                                        <?php endif; ?>

                                        <?php
                                        $pager->setSurroundCount(2);
                                        foreach ($pager->links() as $link): ?>
                                            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                                                <?php if ($link['active']): ?>
                                                    <span class="page-link"><?= esc($link['title']) ?></span>
                                                <?php else: ?>
                                                    <a class="page-link" href="<?= esc($link['uri']) ?>"><?= esc($link['title']) ?></a>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>

                                        <?php if ($pager->hasNext()): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= $pager->getNext() ?>" aria-label="Halaman selanjutnya">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="<?= $pager->getLast() ?>" aria-label="Halaman terakhir">
                                                    <span aria-hidden="true">&raquo;&raquo;</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                                            <li class="page-item disabled"><span class="page-link">&raquo;&raquo;</span></li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                                <div class="input-group input-group-sm" style="min-width: 160px;">
                                    <label class="input-group-text" for="perPageSelectPagination"><i class="fas fa-layer-group"></i></label>
                                    <select id="perPageSelectPagination" name="per_page" class="form-select auto-submit" form="agentCatalogFilter">
                                        <?php foreach ($perPageOptions as $option): ?>
                                            <option value="<?= esc($option) ?>" <?= (int) ($filters['per_page'] ?? 12) === (int) $option ? 'selected' : '' ?>>
                                                <?= $option ?> / halaman
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted mb-2">Produk tidak ditemukan</h5>
                        <p class="text-muted mb-4">Coba ubah kata kunci atau atur ulang filter untuk melihat produk lainnya.</p>
                        <a href="<?= $config->baseURL ?>agent/item" class="btn btn-outline-primary rounded-pill">
                            <i class="fas fa-redo me-2"></i>Reset Semua Filter
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('agentCatalogFilter');
    const csrfTokenName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    // Auto-submit on change
    document.querySelectorAll('#agentCatalogFilter .auto-submit').forEach(function (element) {
        element.addEventListener('change', function () {
            filterForm.submit();
        });
    });

    // Clear specific filter section
    document.querySelectorAll('#agentCatalogFilter .clear-section').forEach(function (button) {
        button.addEventListener('click', function () {
            const target = button.getAttribute('data-target');
            filterForm.querySelectorAll(`[name="${target}[]"]`).forEach(function (checkbox) {
                checkbox.checked = false;
            });
            filterForm.submit();
        });
    });

    // View mode toggle
    const viewInput = document.getElementById('viewModeInput');
    document.querySelectorAll('.view-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            const selectedView = button.getAttribute('data-view');
            if (viewInput.value !== selectedView) {
                viewInput.value = selectedView;
                filterForm.submit();
            }
        });
    });

    // Handle add-to-cart buttons
    document.querySelectorAll('.btn-add-cart').forEach(function (button) {
        button.addEventListener('click', function () {
            const itemId = button.getAttribute('data-item-id');
            const itemName = button.getAttribute('data-item-name');
            const itemPrice = button.getAttribute('data-item-price');
            const originalText = button.innerHTML;

            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

            const payload = new URLSearchParams({
                item_id: itemId,
                item_name: itemName,
                item_price: itemPrice,
                qty: 1
            });

            if (csrfTokenName) {
                payload.append(csrfTokenName, csrfHash);
            }

            fetch('<?= $config->baseURL ?>agent/sales/addToCart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload.toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.csrf_hash) {
                    csrfHash = data.csrf_hash;
                }

                if (data.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message || 'Produk ditambahkan ke keranjang.',
                            showCancelButton: true,
                            confirmButtonText: 'Lihat Keranjang',
                            cancelButtonText: 'Lanjut Belanja',
                            confirmButtonColor: '#2563eb',
                            cancelButtonColor: '#6b7280'
                        }).then(result => {
                            if (result.isConfirmed) {
                                window.location.href = '<?= $config->baseURL ?>agent/sales/cart';
                            }
                        });
                    } else {
                        if (confirm((data.message || 'Produk ditambahkan ke keranjang.') + '\n\nBuka keranjang sekarang?')) {
                            window.location.href = '<?= $config->baseURL ?>agent/sales/cart';
                        }
                    }
                } else {
                    const errorMessage = data.message || 'Gagal menambahkan produk. Silakan coba lagi.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', text: errorMessage });
                    } else {
                        alert(errorMessage);
                    }
                }
            })
            .catch(error => {
                const message = 'Terjadi kesalahan koneksi. Silakan coba lagi.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: message });
                } else {
                    alert(message);
                }
                console.error('Add to cart error:', error);
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        });
    });
});
</script>

<style>
.agent-catalog-page .filter-card {
    max-height: calc(100vh - 108px);
    overflow-y: auto;
}

.agent-catalog-page .filter-checkbox-list {
    max-height: 210px;
    overflow-y: auto;
    padding-right: 4px;
}

.agent-catalog-page .filter-checkbox-list .form-check {
    margin-bottom: 0.35rem;
}

.agent-catalog-page .product-card,
.agent-catalog-page .product-card-list {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.agent-catalog-page .product-card:hover,
.agent-catalog-page .product-card-list:hover {
    transform: translateY(-4px);
    box-shadow: 0px 12px 24px rgba(15, 23, 42, 0.12) !important;
}

.agent-catalog-page .product-image {
    object-fit: cover;
    transition: transform 0.3s ease;
    width: 100%;
    height: 100%;
}

.agent-catalog-page .product-card:hover .product-image {
    transform: scale(1.05);
}

.agent-catalog-page .btn-add-cart {
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .4px;
}

@media (max-width: 991.98px) {
    .agent-catalog-page .filter-card {
        position: static !important;
        max-height: unset;
    }

    .agent-catalog-page .product-card-list {
        margin-bottom: 1.25rem;
    }
}
</style>

