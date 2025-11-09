<?php 
/**
 * 403 Forbidden Error Page
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-09
 * Description: Access denied error page (content only, header/footer handled by BaseController)
 */
?>
<div class="container-fluid">
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-ban text-danger fa-5x mb-3"></i>
                    <h2 class="mb-3"><?= $title ?? 'Access Denied' ?></h2>
                    <p class="lead text-muted mb-4">
                        <?= $message ?? 'You do not have permission to access this page.' ?>
                    </p>
                    <a href="<?= $config->baseURL ?>" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

