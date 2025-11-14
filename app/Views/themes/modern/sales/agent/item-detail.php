<?php
/**
 * Agent Product Detail View
 * Full product detail page for agent catalog
 *
 * @var array $item
 * @var array $specifications
 */
helper(['form', 'angka']);

$item = $item ?? [];
$specifications = $specifications ?? [];
$itemId = $item['id'] ?? null;
$itemName = $item['name'] ?? 'Produk';
$categoryName = $item['category_name'] ?? 'Tanpa Kategori';
$brandName = $item['brand_name'] ?? 'Tanpa Brand';
$agentPrice = (float) ($item['agent_price'] ?? 0);
$displayPrice = $agentPrice > 0 ? $agentPrice : (float) ($item['price'] ?? 0);
$isStockable = ($item['is_stockable'] ?? '0') === '1';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $config->baseURL ?>agent/item"><i class="fas fa-home me-1"></i>Katalog</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= esc($itemName) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Product Image -->
        <div class="col-lg-5 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="text-center">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?= $config->baseURL ?>/uploads/<?= esc($item['image']) ?>"
                                 alt="<?= esc($itemName) ?>"
                                 class="img-fluid rounded"
                                 style="max-width: 100%; max-height: 500px; object-fit: contain;">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center bg-light rounded" style="height: 400px;">
                                <div class="text-center">
                                    <i class="fas fa-image fa-4x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada gambar tersedia</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Information -->
        <div class="col-lg-7 col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-secondary-subtle text-secondary fw-semibold"><?= esc($categoryName) ?></span>
                        <span class="badge bg-info-subtle text-info fw-semibold"><?= esc($brandName) ?></span>
                    </div>

                    <h2 class="mb-3 fw-bold"><?= esc($itemName) ?></h2>

                    <?php if (!empty($item['sku'])): ?>
                        <p class="text-primary fw-semibold mb-3">
                            <i class="fas fa-hashtag me-1"></i>Kode: <?= esc($item['sku']) ?>
                        </p>
                    <?php endif; ?>

                    <div class="mb-4">
                        <div class="text-primary fw-bold" style="font-size: 2rem;">
                            <?= format_angka_rp($displayPrice) ?>
                        </div>
                    </div>

                    <?php if (!empty($item['short_description'])): ?>
                        <div class="mb-4">
                            <p class="text-muted lead"><?= esc($item['short_description']) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-column flex-md-row gap-3 mb-4">
                        <button type="button"
                                class="btn btn-primary btn-lg rounded-pill flex-fill btn-add-cart"
                                data-item-id="<?= esc($itemId) ?>"
                                data-item-name="<?= esc($itemName) ?>"
                                data-item-price="<?= esc($displayPrice) ?>">
                            <i class="fas fa-shopping-cart me-2"></i>Tambah ke Keranjang
                        </button>
                        <a href="<?= $config->baseURL ?>agent/item" class="btn btn-outline-secondary btn-lg rounded-pill">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>

                    <!-- Additional Info -->
                    <div class="border-top pt-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Informasi Produk</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" style="width: 150px;"><strong>Tipe Produk:</strong></td>
                                <td>
                                    <?php if ($isStockable): ?>
                                        <span class="badge bg-success">Produk Stok / Serial</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Produk Non-Stok</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (!empty($item['warranty'])): ?>
                                <tr>
                                    <td class="text-muted"><strong>Garansi:</strong></td>
                                    <td><?= esc($item['warranty']) ?> bulan</td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Description -->
    <?php if (!empty($item['description'])): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-align-left me-2 text-primary"></i>Deskripsi Lengkap</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-muted" style="line-height: 1.8;">
                            <?= strip_tags($item['description']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Specifications -->
    <?php if (!empty($specifications)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-list-ul me-2 text-primary"></i>Spesifikasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%;">Spesifikasi</th>
                                        <th>Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($specifications as $spec): ?>
                                        <tr>
                                            <td><strong><?= esc($spec['name'] ?? $spec->spec_name ?? '-') ?></strong></td>
                                            <td><?= esc($spec['value'] ?? $spec->value ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    const csrfTokenName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    // Handle add-to-cart button
    $('.btn-add-cart').on('click', function() {
        var button = $(this);
        var itemId = button.data('item-id');
        var itemName = button.data('item-name');
        var itemPrice = button.data('item-price');
        var originalText = button.html();

        button.prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');

        var payload = new URLSearchParams({
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
                var errorMessage = data.message || 'Gagal menambahkan produk. Silakan coba lagi.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: errorMessage });
                } else {
                    alert(errorMessage);
                }
            }
        })
        .catch(error => {
            var message = 'Terjadi kesalahan koneksi. Silakan coba lagi.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: message });
            } else {
                alert(message);
            }
            console.error('Add to cart error:', error);
        })
        .finally(() => {
            button.prop('disabled', false);
            button.html(originalText);
        });
    });
});
</script>

