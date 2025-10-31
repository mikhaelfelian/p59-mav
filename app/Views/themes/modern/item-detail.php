<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-24
 * Github: github.com/mikhaelfelian
 * Description: View for displaying item details in a modal
 * This file represents the View for item-detail.
 */
?>

<div class="row">
    <div class="col-md-4">
        <!-- Item Image -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Gambar Item</h5>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($item->image)): ?>
                    <img src="<?= base_url('public/uploads/' . $item->image) ?>" 
                         alt="<?= esc($item->name) ?>" 
                         class="img-fluid rounded" 
                         style="max-height: 300px;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div class="text-muted" style="display:none;">
                        <i class="fas fa-image fa-3x"></i>
                        <p class="mt-2">Gambar tidak ditemukan</p>
                    </div>
                <?php else: ?>
                    <div class="text-muted">
                        <i class="fas fa-image fa-3x"></i>
                        <p class="mt-2">Tidak ada gambar</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Item Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Informasi Item</h5>
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
                                <td><strong>Nama Item:</strong></td>
                                <td><?= esc($item->name) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Brand:</strong></td>
                                <td><?= esc($item->brand_name ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Kategori:</strong></td>
                                <td><?= esc($item->category_name ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Harga:</strong></td>
                                <td>Rp <?= format_angka($item->price, 0) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Harga Agen:</strong></td>
                                <td>Rp <?= format_angka($item->agent_price, 0) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Stockable:</strong></td>
                                <td>
                                    <?php if ($item->is_stockable == '1'): ?>
                                        <span class="badge bg-success">Stockable</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Non-Stockable</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <?php if ($item->status == '1'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Slug:</strong></td>
                                <td><?= esc($item->slug ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat:</strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($item->created_at)) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Diupdate:</strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($item->updated_at)) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php if (!empty($item->description)): ?>
                <div class="mt-3">
                    <h6><strong>Deskripsi:</strong></h6>
                    <p><?= nl2br(esc($item->description)) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($item->short_description)): ?>
                <div class="mt-3">
                    <h6><strong>Deskripsi Singkat:</strong></h6>
                    <p><?= nl2br(esc($item->short_description)) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($specifications)): ?>
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Spesifikasi Item</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nama Spesifikasi</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($specifications as $spec): ?>
                            <tr>
                                <td><strong><?= esc($spec->spec_name) ?></strong></td>
                                <td><?= esc($spec->value) ?></td>
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
