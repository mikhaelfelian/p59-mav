<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-exclamation-triangle text-warning fa-5x mb-3"></i>
                        <h2 class="mb-3">Transaksi Tidak Ditemukan</h2>
                        <p class="lead mb-4">
                            Kami tidak dapat menemukan transaksi yang Anda cari. Silakan periksa kembali link atau nomor invoice Anda.
                        </p>
                        
                        <div class="alert alert-info mt-4" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            Jika Anda yakin transaksi tersebut ada, silakan hubungi customer service untuk bantuan lebih lanjut.
                        </div>
                        
                        <div class="mt-4">
                            <a href="<?= $config->baseURL ?>agent/sales/cart" class="btn btn-secondary btn-lg me-2">
                                <i class="fas fa-shopping-cart me-2"></i>Kembali ke Keranjang
                            </a>
                            <a href="<?= $config->baseURL ?>agent/item" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-store me-2"></i>Kembali ke Produk
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

