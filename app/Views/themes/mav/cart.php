<?= $this->extend('themes/mav/layout/main') ?>

<?= $this->section('content') ?>

<div class="container">
  <div class="section-head">
    <div class="badge">Keranjang</div>
    <h1>Keranjang Belanja</h1>
  </div>
  
  <div id="cart-container">
    <div id="cart-items" style="margin-top: 20px;">
      <!-- Cart items will be loaded here by JavaScript -->
    </div>
    
    <div id="cart-summary" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; display: none;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0;">Total: <span id="cart-total">Rp 0</span></h3>
      </div>
      <button id="btn-checkout" class="btn-checkout" style="width: 100%; padding: 12px; background: var(--primary-color, #007bff); color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 1rem;">
        <i class="fas fa-credit-card" style="margin-right: 8px;"></i>Checkout
      </button>
    </div>
    
    <div id="cart-empty" style="text-align: center; padding: 60px 20px; display: none;">
      <i class="fas fa-shopping-cart" style="font-size: 64px; color: var(--text-dim, #999); margin-bottom: 20px;"></i>
      <p style="color: var(--text-dim); font-size: 1.1rem; margin-bottom: 20px;">Keranjang Anda kosong</p>
      <a href="<?= base_url('catalog') ?>" style="display: inline-block; padding: 12px 24px; background: var(--primary-color, #007bff); color: #fff; text-decoration: none; border-radius: 4px; font-weight: 600;">
        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>Kembali ke Katalog
      </a>
    </div>
  </div>
</div>

<style>
  .cart-item {
    display: flex;
    gap: 20px;
    padding: 20px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 16px;
    align-items: center;
  }
  
  .cart-item-image {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 4px;
  }
  
  .cart-item-details {
    flex: 1;
  }
  
  .cart-item-name {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 8px;
  }
  
  .cart-item-price {
    color: var(--primary-color, #007bff);
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 12px;
  }
  
  .cart-item-controls {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .quantity-control {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .quantity-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #ddd;
    background: #fff;
    cursor: pointer;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .quantity-input {
    width: 60px;
    padding: 6px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
  }
  
  .remove-btn {
    padding: 8px 16px;
    background: #dc3545;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
  }
  
  .remove-btn:hover {
    background: #c82333;
  }
  
  @media (max-width: 768px) {
    .cart-item {
      flex-direction: column;
    }
    
    .cart-item-image {
      width: 100%;
      height: auto;
    }
  }
</style>

<script>
// Use the same CartManager from catalog page
const CartManager = {
  storageKey: 'mav_cart',
  
  getCart: function() {
    const cart = localStorage.getItem(this.storageKey);
    return cart ? JSON.parse(cart) : [];
  },
  
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
    this.renderCart();
    return cart;
  },
  
  removeItem: function(itemId) {
    const cart = this.getCart();
    const filteredCart = cart.filter(item => item.item_id != itemId);
    localStorage.setItem(this.storageKey, JSON.stringify(filteredCart));
    this.updateCartBadge();
    this.renderCart();
    return filteredCart;
  },
  
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
      this.renderCart();
    }
    return cart;
  },
  
  clearCart: function() {
    localStorage.removeItem(this.storageKey);
    this.updateCartBadge();
    this.renderCart();
  },
  
  getTotal: function() {
    const cart = this.getCart();
    return cart.reduce((total, item) => total + (parseFloat(item.price) * parseInt(item.qty)), 0);
  },
  
  getCount: function() {
    const cart = this.getCart();
    return cart.reduce((count, item) => count + parseInt(item.qty), 0);
  },
  
  updateCartBadge: function() {
    const count = this.getCount();
    const badge = document.getElementById('cart-badge');
    if (badge) {
      badge.textContent = count;
      badge.style.display = count > 0 ? 'inline-block' : 'none';
    }
  },
  
  formatCurrency: function(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
  },
  
  renderCart: function() {
    const cart = this.getCart();
    const cartItemsContainer = document.getElementById('cart-items');
    const cartSummary = document.getElementById('cart-summary');
    const cartEmpty = document.getElementById('cart-empty');
    
    if (cart.length === 0) {
      cartItemsContainer.innerHTML = '';
      cartSummary.style.display = 'none';
      cartEmpty.style.display = 'block';
      return;
    }
    
    cartEmpty.style.display = 'none';
    cartSummary.style.display = 'block';
    
    let html = '';
    cart.forEach(item => {
      const imageUrl = item.image.startsWith('/') 
        ? '<?= base_url() ?>' + item.image 
        : '<?= base_url('public/uploads/items/') ?>' + item.image;
      
      html += `
        <div class="cart-item" data-item-id="${item.item_id}">
          <img src="${imageUrl}" alt="${item.item_name}" class="cart-item-image" onerror="this.src='<?= base_url('/images/produk/dvd.png') ?>'">
          <div class="cart-item-details">
            <div class="cart-item-name">${item.item_name}</div>
            <div class="cart-item-price">${this.formatCurrency(parseFloat(item.price))}</div>
            <div class="cart-item-controls">
              <div class="quantity-control">
                <button class="quantity-btn" onclick="CartManager.updateQuantity(${item.item_id}, ${parseInt(item.qty) - 1})">-</button>
                <input type="number" class="quantity-input" value="${item.qty}" min="1" 
                       onchange="CartManager.updateQuantity(${item.item_id}, this.value)">
                <button class="quantity-btn" onclick="CartManager.updateQuantity(${item.item_id}, ${parseInt(item.qty) + 1})">+</button>
              </div>
              <button class="remove-btn" onclick="CartManager.removeItem(${item.item_id})">
                <i class="fas fa-trash"></i> Hapus
              </button>
            </div>
          </div>
        </div>
      `;
    });
    
    cartItemsContainer.innerHTML = html;
    
    const total = this.getTotal();
    document.getElementById('cart-total').textContent = this.formatCurrency(total);
  }
};

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', function() {
  CartManager.renderCart();
  CartManager.updateCartBadge();
  
  // Checkout button handler
  document.getElementById('btn-checkout').addEventListener('click', function() {
    const cart = CartManager.getCart();
    if (cart.length === 0) {
      alert('Keranjang kosong!');
      return;
    }
    
    // Redirect to checkout page
    window.location.href = '<?= base_url('checkout') ?>';
  });
});
</script>

<?= $this->endSection() ?>

