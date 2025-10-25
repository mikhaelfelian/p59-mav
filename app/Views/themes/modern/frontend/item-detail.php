<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-24
 * Github: github.com/mikhaelfelian
 * Description: Frontend item detail view for displaying individual item information
 * This file represents the View for frontend item detail.
 */
?>

<div class="container-fluid" style="margin-top:2rem; margin-bottom:2rem;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('frontend') ?>">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= esc($item->name) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Item Image -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <?php if (!empty($item->image) && file_exists(FCPATH . 'public/uploads/' . $item->image)): ?>
                        <img 
                            src="<?= base_url('public/uploads/' . $item->image) ?>" 
                            class="img-fluid rounded" 
                            alt="<?= esc($item->name) ?>" 
                            style="max-width: 100%; max-height: 500px; object-fit: contain;"
                        >
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
        
        <!-- Item Information -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h2 class="card-title mb-0"><?= esc($item->name) ?></h2>
                </div>
                <div class="card-body">
                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>SKU:</strong></div>
                        <div class="col-sm-8"><?= esc($item->sku) ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Brand:</strong></div>
                        <div class="col-sm-8"><?= esc($item->brand_name ?? 'Tidak ada brand') ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Kategori:</strong></div>
                        <div class="col-sm-8"><?= esc($item->category_name ?? 'Tidak ada kategori') ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Harga:</strong></div>
                        <div class="col-sm-8">
                            <span class="h4 text-primary">Rp <?= format_angka($item->price, 0) ?></span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Status:</strong></div>
                        <div class="col-sm-8">
                            <?php if ($item->is_stockable == '1'): ?>
                                <span class="badge bg-success fs-6">Stockable</span>
                            <?php else: ?>
                                <span class="badge bg-secondary fs-6">Non-Stockable</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Short Description -->
                    <?php if (!empty($item->short_description)): ?>
                    <div class="mb-3">
                        <h5>Deskripsi Singkat</h5>
                        <p class="text-muted"><?= nl2br(esc($item->short_description)) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-4">
                        <button class="btn btn-primary btn-lg me-md-2" type="button">
                            <i class="fas fa-shopping-cart me-2"></i>Beli Sekarang
                        </button>
                        <button class="btn btn-outline-secondary btn-lg" type="button">
                            <i class="fas fa-heart me-2"></i>Wishlist
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Description -->
    <?php if (!empty($item->description)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Deskripsi Lengkap</h4>
                </div>
                <div class="card-body">
                    <div class="content">
                        <?= $item->description ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Additional Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Informasi Tambahan</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>SKU:</strong></td>
                                    <td><?= esc($item->sku) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Brand:</strong></td>
                                    <td><?= esc($item->brand_name ?? 'Tidak ada brand') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Kategori:</strong></td>
                                    <td><?= esc($item->category_name ?? 'Tidak ada kategori') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status Item:</strong></td>
                                    <td>
                                        <?php if ($item->is_stockable == '1'): ?>
                                            <span class="badge bg-success">Stockable</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Non-Stockable</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Harga:</strong></td>
                                    <td><span class="h5 text-primary">Rp <?= format_angka($item->price, 0) ?></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Dibuat:</strong></td>
                                    <td><?= date('d F Y', strtotime($item->created_at)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Diupdate:</strong></td>
                                    <td><?= date('d F Y', strtotime($item->updated_at)) ?></td>
                                </tr>
                                <?php if (!empty($item->slug)): ?>
                                <tr>
                                    <td><strong>Slug:</strong></td>
                                    <td><code><?= esc($item->slug) ?></code></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Back to Products -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="<?= base_url('frontend') ?>" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Produk
            </a>
        </div>
    </div>
</div>

<style>
.content {
    line-height: 1.6;
}

.content h1, .content h2, .content h3, .content h4, .content h5, .content h6 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.content p {
    margin-bottom: 1rem;
}

.content ul, .content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.375rem;
}

.breadcrumb {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: ">";
    color: #6c757d;
}

.breadcrumb-item a {
    color: #007bff;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #0056b3;
    text-decoration: underline;
}
</style>
