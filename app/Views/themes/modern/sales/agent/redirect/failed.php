<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-times-circle text-danger fa-5x mb-3"></i>
                        <h2 class="mb-3">Pembayaran <?= esc($status) ?>!</h2>
                        <p class="lead mb-4">
                            Maaf, pembayaran untuk transaksi dengan nomor invoice <strong><?= esc($sale['invoice_no'] ?? 'N/A') ?></strong> tidak dapat diselesaikan.
                        </p>
                        
                        <?php if (!empty($sale)): ?>
                            <div class="row justify-content-center mt-4 mb-4">
                                <div class="col-md-6">
                                    <div class="card border-danger">
                                        <div class="card-body">
                                            <h5 class="card-title">Detail Transaksi</h5>
                                            <dl class="row mb-0">
                                                <dt class="col-sm-5">Invoice No:</dt>
                                                <dd class="col-sm-7"><strong><?= esc($sale['invoice_no'] ?? 'N/A') ?></strong></dd>
                                                
                                                <dt class="col-sm-5">Total:</dt>
                                                <dd class="col-sm-7">
                                                    <span class="text-danger fw-bold">
                                                        Rp <?= number_format($sale['grand_total'] ?? 0, 0, ',', '.') ?>
                                                    </span>
                                                </dd>
                                                
                                                <dt class="col-sm-5">Status:</dt>
                                                <dd class="col-sm-7">
                                                    <span class="badge bg-danger"><?= esc($status) ?></span>
                                                </dd>
                                                
                                                <?php if (!empty($sale['customer_name'])): ?>
                                                    <dt class="col-sm-5">Customer:</dt>
                                                    <dd class="col-sm-7"><?= esc($sale['customer_name']) ?></dd>
                                                <?php endif; ?>
                                                
                                                <dt class="col-sm-5">Tanggal:</dt>
                                                <dd class="col-sm-7">
                                                    <?php
                                                    if (!empty($sale['created_at'])) {
                                                        $date = new \DateTime($sale['created_at']);
                                                        echo esc($date->format('d/m/Y H:i'));
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-warning mt-4" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Penting:</strong> Jika Anda sudah melakukan pembayaran, silakan hubungi customer service untuk verifikasi.
                        </div>
                        
                        <div class="mt-4">
                            <a href="<?= $config->baseURL ?>agent/sales/cart" class="btn btn-outline-primary btn-lg me-2">
                                <i class="fas fa-redo me-2"></i>Coba Lagi
                            </a>
                            <a href="<?= $config->baseURL ?>agent/item" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Produk
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

