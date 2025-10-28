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
  <?php else: ?>
    <div style="text-align: center; padding: 60px 20px; color: var(--text-dim);">
      <p>Tidak ada produk tersedia saat ini.</p>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>

