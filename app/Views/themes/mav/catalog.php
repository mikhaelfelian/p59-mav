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
            
            <button class="btn-add-to-cart" 
                    data-item-id="<?= esc($item['id']) ?>"
                    data-item-name="<?= esc($item['name']) ?>"
                    data-item-price="<?= esc($item['price'] ?? 0) ?>"
                    data-item-image="<?= !empty($item['image']) ? esc($item['image']) : '/images/produk/dvd.png' ?>"
                    style="margin-top: 12px; width: 100%; padding: 10px; background: var(--primary-color, #007bff); color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
              <i class="fas fa-cart-plus" style="margin-right: 8px;"></i>Tambah ke Keranjang
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if (isset($pager) && isset($pagerInfo) && $pagerInfo['totalPages'] > 1): ?>
      <?php 
      $currentPage = $pagerInfo['currentPage'];
      $totalPages = $pagerInfo['totalPages'];
      $totalItems = $pagerInfo['totalItems'];
      $perPage = $pagerInfo['perPage'];
      $startPage = max(1, $currentPage - 2);
      $endPage = min($totalPages, $currentPage + 2);
      ?>
      <div class="pagination-wrapper" style="margin-top: 40px; display: flex; justify-content: center; align-items: center; gap: 20px; flex-wrap: wrap;">
        <!-- Pagination Info -->
        <div class="pagination-info" style="color: var(--text-dim); font-size: 0.875rem;">
          Menampilkan <?= ($currentPage - 1) * $perPage + 1 ?> - 
          <?= min($currentPage * $perPage, $totalItems) ?> 
          dari <?= $totalItems ?> produk
        </div>
        
        <!-- Pagination Links -->
        <nav aria-label="Page navigation">
          <ul class="pagination" style="display: flex; gap: 8px; list-style: none; margin: 0; padding: 0;">
            <!-- Previous Link -->
            <?php if ($pager->hasPrevious()): ?>
              <li class="page-item">
                <a class="page-link" href="<?= $pager->getPrevious() ?>" 
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
            // Show first page if not near start
            if ($startPage > 1): ?>
              <li class="page-item">
                <a class="page-link" href="<?= $pager->getFirst() ?>" 
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
            
            <!-- Current page range using PagerRenderer links -->
            <?php 
            $pager->setSurroundCount(2);
            foreach ($pager->links() as $link): ?>
              <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <?php if ($link['active']): ?>
                  <span class="page-link" 
                        style="padding: 8px 12px; border: 1px solid var(--primary-color, #007bff); border-radius: 4px; color: #fff; background: var(--primary-color, #007bff); font-weight: 600;">
                    <?= $link['title'] ?>
                  </span>
                <?php else: ?>
                  <a class="page-link" href="<?= $link['uri'] ?>" 
                     style="padding: 8px 12px; border: 1px solid var(--border-color, #e0e0e0); border-radius: 4px; text-decoration: none; color: var(--text-color, #333); background: var(--bg-color, #fff); transition: all 0.2s;">
                    <?= $link['title'] ?>
                  </a>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
            
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
                <a class="page-link" href="<?= $pager->getLast() ?>" 
                   style="padding: 8px 12px; border: 1px solid var(--border-color, #e0e0e0); border-radius: 4px; text-decoration: none; color: var(--text-color, #333); background: var(--bg-color, #fff); transition: all 0.2s;">
                  <?= $totalPages ?>
                </a>
              </li>
            <?php endif; ?>
            
            <!-- Next Link -->
            <?php if ($pager->hasNext()): ?>
              <li class="page-item">
                <a class="page-link" href="<?= $pager->getNext() ?>" 
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
    <?php elseif (isset($pagerInfo) && $pagerInfo['totalItems'] > 0): ?>
      <!-- Show info even if only one page -->
      <div class="pagination-wrapper" style="margin-top: 40px; display: flex; justify-content: center; align-items: center;">
        <div class="pagination-info" style="color: var(--text-dim); font-size: 0.875rem;">
          Menampilkan semua <?= $pagerInfo['totalItems'] ?> produk
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

<script>
// Cart functionality using localStorage
const CartManager = {
  storageKey: 'mav_cart',
  
  // Get all cart items
  getCart: function() {
    const cart = localStorage.getItem(this.storageKey);
    return cart ? JSON.parse(cart) : [];
  },
  
  // Add item to cart
  addItem: function(item) {
    const cart = this.getCart();
    const existingItem = cart.find(cartItem => cartItem.item_id === item.item_id);
    
    if (existingItem) {
      existingItem.qty += item.qty || 1;
    } else {
      cart.push({
        item_id: item.item_id,
        item_name: item.item_name,
        price: parseFloat(item.price) || 0,
        qty: item.qty || 1,
        image: item.image || '/images/produk/dvd.png'
      });
    }
    
    localStorage.setItem(this.storageKey, JSON.stringify(cart));
    this.updateCartBadge();
    return cart;
  },
  
  // Remove item from cart
  removeItem: function(itemId) {
    const cart = this.getCart();
    const filteredCart = cart.filter(item => item.item_id != itemId);
    localStorage.setItem(this.storageKey, JSON.stringify(filteredCart));
    this.updateCartBadge();
    return filteredCart;
  },
  
  // Update item quantity
  updateQuantity: function(itemId, qty) {
    const cart = this.getCart();
    const item = cart.find(cartItem => cartItem.item_id == itemId);
    if (item) {
      if (qty <= 0) {
        return this.removeItem(itemId);
      }
      item.qty = parseInt(qty);
      localStorage.setItem(this.storageKey, JSON.stringify(cart));
      this.updateCartBadge();
    }
    return cart;
  },
  
  // Clear cart
  clearCart: function() {
    localStorage.removeItem(this.storageKey);
    this.updateCartBadge();
  },
  
  // Get cart total
  getTotal: function() {
    const cart = this.getCart();
    return cart.reduce((total, item) => total + (parseFloat(item.price) * parseInt(item.qty)), 0);
  },
  
  // Get cart count
  getCount: function() {
    const cart = this.getCart();
    return cart.reduce((count, item) => count + parseInt(item.qty), 0);
  },
  
  // Update cart badge in header/navigation
  updateCartBadge: function() {
    const count = this.getCount();
    const badge = document.getElementById('cart-badge');
    if (badge) {
      badge.textContent = count;
      badge.style.display = count > 0 ? 'inline-block' : 'none';
    }
  }
};

// Initialize cart badge on page load
document.addEventListener('DOMContentLoaded', function() {
  CartManager.updateCartBadge();
  
  // Add event listeners to all "Add to Cart" buttons
  document.querySelectorAll('.btn-add-to-cart').forEach(button => {
    button.addEventListener('click', function() {
      const itemId = this.getAttribute('data-item-id');
      const itemName = this.getAttribute('data-item-name');
      const itemPrice = this.getAttribute('data-item-price');
      const itemImage = this.getAttribute('data-item-image');
      
      CartManager.addItem({
        item_id: itemId,
        item_name: itemName,
        price: itemPrice,
        qty: 1,
        image: itemImage
      });
      
      // Show success message
      const originalText = this.innerHTML;
      this.innerHTML = '<i class="fas fa-check" style="margin-right: 8px;"></i>Ditambahkan!';
      this.style.background = '#28a745';
      
      setTimeout(() => {
        this.innerHTML = originalText;
        this.style.background = '';
      }, 2000);
    });
  });
});
</script>

<?= $this->endSection() ?>

