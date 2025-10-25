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
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $item): ?>
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                    <div class="product-box card shadow border-0 h-100">
                        <div class="text-center p-3">
                            <?php if (!empty($item->image) && file_exists(FCPATH . 'public/uploads/' . $item->image)): ?>
                                <img 
                                    src="<?= base_url('public/uploads/' . $item->image) ?>" 
                                    class="img-fluid rounded mb-3" 
                                    alt="<?= esc($item->name) ?>" 
                                    title="<?= esc($item->name) ?>"
                                    style="max-width: 100%; height: 200px; object-fit: contain;"
                                >
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center bg-light rounded mb-3" style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-3">
                            <h5 class="card-title text-center mb-2"><?= esc($item->name) ?></h5>
                            <p class="card-text text-center text-muted small mb-2">
                                <?= esc($item->brand_name ?? 'No Brand') ?> - <?= esc($item->category_name ?? 'No Category') ?>
                            </p>
                            <?php if (!empty($item->short_description)): ?>
                                <p class="card-text text-center text-muted small mb-3"><?= esc($item->short_description) ?></p>
                            <?php endif; ?>
                            <div class="text-center">
                                <span class="badge bg-primary fs-6">Rp <?= format_angka($item->price, 0) ?></span>
                                <?php if ($item->is_stockable == '1'): ?>
                                    <span class="badge bg-success ms-1">Stockable</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary ms-1">Non-Stockable</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="<?= base_url('frontend/item/' . $item->id) ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i> Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Tidak ada item tersedia</h4>
                    <p class="text-muted">Belum ada item yang ditambahkan ke katalog.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>