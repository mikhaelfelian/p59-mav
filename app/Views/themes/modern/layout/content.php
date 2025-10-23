<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Content layout template for tanpalogin theme using section-based layout
 * This file represents the View Layout.
 */
?>

<?= $this->extend('themes/modern/layout/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid" style="margin-top:2rem; margin-bottom:2rem;">
    <div class="row">
        <!-- Product 1 -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="product-box card shadow border-0 h-100">
                <div class="text-center p-3">
                    <img 
                        src="<?= base_url('public/images/produk/dvd_drive.png') ?>" 
                        class="img-fluid rounded mb-3" 
                        alt="DVD Drive" 
                        title="DVD Drive"
                        style="max-width: 100%; height: 200px; object-fit: contain;"
                    >
                </div>
                <div class="card-body p-3">
                    <h5 class="card-title text-center mb-2">DVD Drive</h5>
                    <p class="card-text text-center text-muted small mb-3">External DVD Drive</p>
                    <div class="text-center">
                        <span class="badge bg-primary">$29.99</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product 2 -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="product-box card shadow border-0 h-100">
                <div class="text-center p-3">
                    <img 
                        src="<?= base_url('public/images/produk/dvd_drive.png') ?>" 
                        class="img-fluid rounded mb-3" 
                        alt="DVD Drive" 
                        title="DVD Drive"
                        style="max-width: 100%; height: 200px; object-fit: contain;"
                    >
                </div>
                <div class="card-body p-3">
                    <h5 class="card-title text-center mb-2">USB Drive</h5>
                    <p class="card-text text-center text-muted small mb-3">Portable USB Drive</p>
                    <div class="text-center">
                        <span class="badge bg-primary">$19.99</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product 3 -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="product-box card shadow border-0 h-100">
                <div class="text-center p-3">
                    <img 
                        src="<?= base_url('public/images/produk/dvd_drive.png') ?>" 
                        class="img-fluid rounded mb-3" 
                        alt="DVD Drive" 
                        title="DVD Drive"
                        style="max-width: 100%; height: 200px; object-fit: contain;"
                    >
                </div>
                <div class="card-body p-3">
                    <h5 class="card-title text-center mb-2">Hard Drive</h5>
                    <p class="card-text text-center text-muted small mb-3">External Hard Drive</p>
                    <div class="text-center">
                        <span class="badge bg-primary">$79.99</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product 4 -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="product-box card shadow border-0 h-100">
                <div class="text-center p-3">
                    <img 
                        src="<?= base_url('public/images/produk/dvd_drive.png') ?>" 
                        class="img-fluid rounded mb-3" 
                        alt="DVD Drive" 
                        title="DVD Drive"
                        style="max-width: 100%; height: 200px; object-fit: contain;"
                    >
                </div>
                <div class="card-body p-3">
                    <h5 class="card-title text-center mb-2">SSD Drive</h5>
                    <p class="card-text text-center text-muted small mb-3">Solid State Drive</p>
                    <div class="text-center">
                        <span class="badge bg-primary">$129.99</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product 5 -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="product-box card shadow border-0 h-100">
                <div class="text-center p-3">
                    <img 
                        src="<?= base_url('public/images/produk/dvd_drive.png') ?>" 
                        class="img-fluid rounded mb-3" 
                        alt="DVD Drive" 
                        title="DVD Drive"
                        style="max-width: 100%; height: 200px; object-fit: contain;"
                    >
                </div>
                <div class="card-body p-3">
                    <h5 class="card-title text-center mb-2">Memory Card</h5>
                    <p class="card-text text-center text-muted small mb-3">SD Memory Card</p>
                    <div class="text-center">
                        <span class="badge bg-primary">$24.99</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product 6 -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="product-box card shadow border-0 h-100">
                <div class="text-center p-3">
                    <img 
                        src="<?= base_url('public/images/produk/dvd_drive.png') ?>" 
                        class="img-fluid rounded mb-3" 
                        alt="DVD Drive" 
                        title="DVD Drive"
                        style="max-width: 100%; height: 200px; object-fit: contain;"
                    >
                </div>
                <div class="card-body p-3">
                    <h5 class="card-title text-center mb-2">Flash Drive</h5>
                    <p class="card-text text-center text-muted small mb-3">USB Flash Drive</p>
                    <div class="text-center">
                        <span class="badge bg-primary">$14.99</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product 7 -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="product-box card shadow border-0 h-100">
                <div class="text-center p-3">
                    <img 
                        src="<?= base_url('public/images/produk/dvd_drive.png') ?>" 
                        class="img-fluid rounded mb-3" 
                        alt="DVD Drive" 
                        title="DVD Drive"
                        style="max-width: 100%; height: 200px; object-fit: contain;"
                    >
                </div>
                <div class="card-body p-3">
                    <h5 class="card-title text-center mb-2">Network Drive</h5>
                    <p class="card-text text-center text-muted small mb-3">NAS Network Drive</p>
                    <div class="text-center">
                        <span class="badge bg-primary">$199.99</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product 8 -->
        <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
            <div class="product-box card shadow border-0 h-100">
                <div class="text-center p-3">
                    <img 
                        src="<?= base_url('public/images/produk/dvd_drive.png') ?>" 
                        class="img-fluid rounded mb-3" 
                        alt="DVD Drive" 
                        title="DVD Drive"
                        style="max-width: 100%; height: 200px; object-fit: contain;"
                    >
                </div>
                <div class="card-body p-3">
                    <h5 class="card-title text-center mb-2">Optical Drive</h5>
                    <p class="card-text text-center text-muted small mb-3">Blu-ray Drive</p>
                    <div class="text-center">
                        <span class="badge bg-primary">$89.99</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>