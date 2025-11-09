<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle text-success fa-5x mb-3"></i>
                        <h2 class="mb-3">Pembayaran Berhasil!</h2>
                        <p class="lead mb-4">
                            Transaksi Anda dengan nomor invoice <strong><?= esc($sale['invoice_no'] ?? 'N/A') ?></strong> telah berhasil diselesaikan.
                        </p>
                        
                        <?php if (!empty($sale)): ?>
                            <div class="row justify-content-center mt-4 mb-4">
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <h5 class="card-title">Detail Transaksi</h5>
                                            <dl class="row mb-0">
                                                <dt class="col-sm-5">Invoice No:</dt>
                                                <dd class="col-sm-7"><strong><?= esc($sale['invoice_no'] ?? 'N/A') ?></strong></dd>
                                                
                                                <dt class="col-sm-5">Total:</dt>
                                                <dd class="col-sm-7">
                                                    <span class="text-success fw-bold">
                                                        Rp <?= number_format($sale['grand_total'] ?? 0, 0, ',', '.') ?>
                                                    </span>
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
                        
                        <div class="mt-4">
                            <a href="<?= $config->baseURL ?>agent/sales/cart" class="btn btn-primary btn-lg me-2">
                                <i class="fas fa-shopping-cart me-2"></i>Kembali ke Keranjang
                            </a>
                            <a href="<?= $config->baseURL ?>agent/item" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-store me-2"></i>Lanjut Belanja
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

