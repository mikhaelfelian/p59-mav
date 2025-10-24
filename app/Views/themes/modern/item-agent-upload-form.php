<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-01-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: View for uploading item agent prices via Excel/CSV files
 * This file represents the View.
 */
?>
<?= $this->extend('themes/modern/layout/main') ?>

<?= $this->section('css') ?>
<link rel="stylesheet" href="<?= base_url('public/vendors/select2/dist/css/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('public/vendors/select2/dist/css/select2-bootstrap-5-theme.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Upload Harga Agen dari Excel/CSV</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($msg)): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <?= $msg ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('item-agent/upload') ?>" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="agent_id" class="form-label">Pilih Agen <span class="text-danger">*</span></label>
                            <select id="agent_id" name="agent_id" class="form-control select2" required>
                                <option value="">Pilih Agen</option>
                                <?php foreach ($agents as $agent): ?>
                                    <option value="<?= $agent->id ?>"><?= $agent->nama ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="excel_file" class="form-label">Pilih File Excel/CSV <span class="text-danger">*</span></label>
                            <input class="form-control" type="file" id="excel_file" name="excel_file" accept=".xls,.xlsx,.csv" required>
                            <div class="form-text">
                                <strong>Format file yang didukung:</strong> .xls, .xlsx, .csv<br>
                                <strong>Format kolom:</strong><br>
                                • Kolom A: Nama Produk<br>
                                • Kolom B: Harga Agen (Angka)<br>
                                • Kolom C: Status (Aktif/Tidak Aktif atau 1/0)<br>
                                <strong>Baris pertama akan diabaikan (header).</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Petunjuk Upload:</h6>
                                <ol class="mb-0">
                                    <li>Pastikan nama produk di file Excel/CSV sama persis dengan nama produk di sistem</li>
                                    <li>Harga agen harus berupa angka (tanpa format mata uang)</li>
                                    <li>Status bisa diisi dengan: "Aktif", "Tidak Aktif", "1", atau "0"</li>
                                    <li>File maksimal 2MB</li>
                                </ol>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                            <a href="<?= base_url('item-agent') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="<?= base_url('public/vendors/select2/dist/js/select2.min.js') ?>"></script>
<script>
$(document).ready(function() {
    $('#agent_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Pilih Agen',
        allowClear: true
    });
});
</script>
<?= $this->endSection() ?>
