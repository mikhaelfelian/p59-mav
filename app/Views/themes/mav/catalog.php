<?= $this->extend('themes/mav/layout/main') ?>

<?= $this->section('content') ?>

<div class="container">
  <div class="section-head">
    <div class="badge">Katalog</div>
    <h1>Katalog Produk</h1>
  </div>
  
  <?php if (!empty($items)): ?>
    <div class="catalog-grid">
      <?php foreach ($items as $item): ?>
        <div class="card">
          <?php if (!empty($item['image'])): ?>
            <img src="<?= base_url('public/uploads/items/' . esc($item['image'])) ?>" alt="<?= esc($item['name']) ?>" loading="lazy">
          <?php else: ?>
            <img src="<?= base_url('/images/produk/dvd.png') ?>" alt="No image" loading="lazy">
          <?php endif; ?>
          
          <div class="card-body">
            <div class="badge" style="margin-bottom: 8px;">
              <?= esc($item['brand_name'] ?? 'Unknown Brand') ?>
            </div>
            <h3 style="margin: 0 0 8px; font-size: 1rem; font-weight: 600;"><?= esc($item['name']) ?></h3>
            <p style="margin: 0 0 8px; color: var(--text-dim); font-size: 0.875rem;">
              <?= esc($item['short_description'] ?? '') ?>
            </p>
            
            <?php if (!empty($item['price'])): ?>
              <div class="price" style="margin-top: 12px;">
                Rp <?= number_format($item['price'], 0, ',', '.') ?>
              </div>
            <?php endif; ?>
            
            <?php if (!empty($item['category_name'])): ?>
              <div style="margin-top: 8px; font-size: 0.75rem; color: var(--text-dim);">
                Kategori: <?= esc($item['category_name']) ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
      <div class="pagination-wrapper" style="margin-top: 40px; display: flex; justify-content: center; align-items: center; gap: 20px; flex-wrap: wrap;">
        <!-- Pagination Info -->
        <div class="pagination-info" style="color: var(--text-dim); font-size: 0.875rem;">
          Menampilkan <?= ($pager->getCurrentPage() * $pager->getPerPage()) - $pager->getPerPage() + 1 ?> - 
          <?= min($pager->getCurrentPage() * $pager->getPerPage(), $pager->getTotal()) ?> 
          dari <?= $pager->getTotal() ?> produk
        </div>
        
        <!-- Pagination Links -->
        <nav aria-label="Page navigation">
          <ul class="pagination" style="display: flex; gap: 8px; list-style: none; margin: 0; padding: 0;">
            <!-- Previous Link -->
            <?php if ($pager->hasPrevious()): ?>
              <li class="page-item">
                <a class="page-link" href="<?= $pager->getPreviousPageURI() ?>" 
                   style="padding: 8px 16px; border: 1px solid var(--border-color, #e0e0e0); border-radius: 4px; text-decoration: none; color: var(--text-color, #333); background: var(--bg-color, #fff); transition: all 0.2s;">
                  &laquo; Sebelumnya
                </a>
              </li>
            <?php else: ?>
              <li class="page-item disabled">
                <span class="page-link" style="padding: 8px 16px; border: 1px solid var(--border-color, #e0e0e0); border-radius: 4px; color: var(--text-dim, #999); background: var(--bg-dim, #f5f5f5); cursor: not-allowed;">
                  &laquo; Sebelumnya
                </span>
              </li>
            <?php endif; ?>
            
            <!-- Page Numbers -->
            <?php 
            $currentPage = $pager->getCurrentPage();
            $totalPages = $pager->getPageCount();
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            // Show first page if not near start
            if ($startPage > 1): ?>
              <li class="page-item">
                <a class="page-link" href="<?= $pager->getPageURI(1) ?>" 
                   style="padding: 8px 12px; border: 1px solid var(--border-color, #e0e0e0); border-radius: 4px; text-decoration: none; color: var(--text-color, #333); background: var(--bg-color, #fff); transition: all 0.2s;">
                  1
                </a>
              </li>
              <?php if ($startPage > 2): ?>
                <li class="page-item disabled">
                  <span class="page-link" style="padding: 8px 12px; border: none; color: var(--text-dim, #999);">
                    ...
                  </span>
                </li>
              <?php endif; ?>
            <?php endif; ?>
            
            <!-- Current page range -->
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
              <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                <?php if ($i == $currentPage): ?>
                  <span class="page-link" 
                        style="padding: 8px 12px; border: 1px solid var(--primary-color, #007bff); border-radius: 4px; color: #fff; background: var(--primary-color, #007bff); font-weight: 600;">
                    <?= $i ?>
                  </span>
                <?php else: ?>
                  <a class="page-link" href="<?= $pager->getPageURI($i) ?>" 
                     style="padding: 8px 12px; border: 1px solid var(--border-color, #e0e0e0); border-radius: 4px; text-decoration: none; color: var(--text-color, #333); background: var(--bg-color, #fff); transition: all 0.2s;">
                    <?= $i ?>
                  </a>
                <?php endif; ?>
              </li>
            <?php endfor; ?>
            
            <!-- Show last page if not near end -->
            <?php if ($endPage < $totalPages): ?>
              <?php if ($endPage < $totalPages - 1): ?>
                <li class="page-item disabled">
                  <span class="page-link" style="padding: 8px 12px; border: none; color: var(--text-dim, #999);">
                    ...
                  </span>
                </li>
              <?php endif; ?>
              <li class="page-item">
                <a class="page-link" href="<?= $pager->getPageURI($totalPages) ?>" 
                   style="padding: 8px 12px; border: 1px solid var(--border-color, #e0e0e0); border-radius: 4px; text-decoration: none; color: var(--text-color, #333); background: var(--bg-color, #fff); transition: all 0.2s;">
                  <?= $totalPages ?>
                </a>
              </li>
            <?php endif; ?>
            
            <!-- Next Link -->
            <?php if ($pager->hasNext()): ?>
              <li class="page-item">
                <a class="page-link" href="<?= $pager->getNextPageURI() ?>" 
                   style="padding: 8px 16px; border: 1px solid var(--border-color, #e0e0e0); border-radius: 4px; text-decoration: none; color: var(--text-color, #333); background: var(--bg-color, #fff); transition: all 0.2s;">
                  Selanjutnya &raquo;
                </a>
              </li>
            <?php else: ?>
              <li class="page-item disabled">
                <span class="page-link" style="padding: 8px 16px; border: 1px solid var(--border-color, #e0e0e0); border-radius: 4px; color: var(--text-dim, #999); background: var(--bg-dim, #f5f5f5); cursor: not-allowed;">
                  Selanjutnya &raquo;
                </span>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    <?php elseif (isset($pager) && $pager->getTotal() > 0): ?>
      <!-- Show info even if only one page -->
      <div class="pagination-wrapper" style="margin-top: 40px; display: flex; justify-content: center; align-items: center;">
        <div class="pagination-info" style="color: var(--text-dim); font-size: 0.875rem;">
          Menampilkan semua <?= $pager->getTotal() ?> produk
        </div>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <div style="text-align: center; padding: 60px 20px; color: var(--text-dim);">
      <p>Tidak ada produk tersedia saat ini.</p>
    </div>
  <?php endif; ?>
</div>

<style>
  .page-link:hover {
    background: var(--primary-color, #007bff) !important;
    color: #fff !important;
    border-color: var(--primary-color, #007bff) !important;
  }
  
  @media (max-width: 768px) {
    .pagination-wrapper {
      flex-direction: column;
      gap: 16px;
    }
    
    .pagination {
      flex-wrap: wrap;
      justify-content: center;
    }
    
    .pagination-info {
      text-align: center;
    }
  }
</style>

<?= $this->endSection() ?>

