<?= $this->extend('themes/mav/layout/main') ?>

<?= $this->section('content') ?>

<div class="container">
  <div class="section-head">
    <div class="badge">Checkout</div>
    <h1>Checkout</h1>
  </div>
  
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">
    <!-- Order Summary -->
    <div>
      <h2 style="margin-bottom: 20px;">Ringkasan Pesanan</h2>
      <div id="order-summary" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
        <!-- Order items will be loaded here -->
      </div>
      <div style="margin-top: 20px; padding: 20px; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
          <span>Subtotal:</span>
          <span id="subtotal">Rp 0</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
          <span>Diskon:</span>
          <span id="discount">Rp 0</span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 600; padding-top: 12px; border-top: 2px solid #e0e0e0;">
          <span>Total:</span>
          <span id="grand-total">Rp 0</span>
        </div>
      </div>
    </div>
    
    <!-- Customer Information Form -->
    <div>
      <h2 style="margin-bottom: 20px;">Informasi Pelanggan</h2>
      <form id="checkout-form" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
        <div style="margin-bottom: 16px;">
          <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nama Pelanggan *</label>
          <input type="text" id="customer_name" name="customer_name" required 
                 style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 16px;">
          <label style="display: block; margin-bottom: 8px; font-weight: 600;">No. Telepon</label>
          <input type="tel" id="phone" name="phone" 
                 style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 16px;">
          <label style="display: block; margin-bottom: 8px; font-weight: 600;">Kode Plat</label>
          <input type="text" id="plate_code" name="plate_code" 
                 style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 16px;">
          <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nomor Plat</label>
          <input type="text" id="plate_number" name="plate_number" 
                 style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 16px;">
          <label style="display: block; margin-bottom: 8px; font-weight: 600;">Suffix Plat</label>
          <input type="text" id="plate_suffix" name="plate_suffix" 
                 style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 16px;">
          <label style="display: block; margin-bottom: 8px; font-weight: 600;">Status Pembayaran</label>
          <select id="payment_status" name="payment_status" 
                  style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            <option value="0">Belum Dibayar</option>
            <option value="1">Sebagian Dibayar</option>
            <option value="2">Lunas</option>
          </select>
        </div>
        
        <button type="submit" id="btn-submit-order" 
                style="width: 100%; padding: 12px; background: var(--primary-color, #007bff); color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 1rem; margin-top: 20px;">
          <i class="fas fa-check" style="margin-right: 8px;"></i>Buat Pesanan
        </button>
      </form>
    </div>
  </div>
</div>

<style>
  .order-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #e0e0e0;
  }
  
  .order-item:last-child {
    border-bottom: none;
  }
  
  @media (max-width: 768px) {
    .container > div {
      grid-template-columns: 1fr !important;
    }
  }
</style>

<script>
// Use the same CartManager
const CartManager = {
  storageKey: 'mav_cart',
  
  getCart: function() {
    const cart = localStorage.getItem(this.storageKey);
    return cart ? JSON.parse(cart) : [];
  },
  
  getTotal: function() {
    const cart = this.getCart();
    return cart.reduce((total, item) => total + (parseFloat(item.price) * parseInt(item.qty)), 0);
  },
  
  formatCurrency: function(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
  },
  
  clearCart: function() {
    localStorage.removeItem(this.storageKey);
    const badge = document.getElementById('cart-badge');
    if (badge) {
      badge.style.display = 'none';
    }
  }
};

// Render order summary
function renderOrderSummary() {
  const cart = CartManager.getCart();
  const summaryContainer = document.getElementById('order-summary');
  
  if (cart.length === 0) {
    summaryContainer.innerHTML = '<p>Keranjang kosong. <a href="<?= base_url('catalog') ?>">Kembali ke katalog</a></p>';
    return;
  }
  
  let html = '';
  cart.forEach(item => {
    const subtotal = parseFloat(item.price) * parseInt(item.qty);
    html += `
      <div class="order-item">
        <div>
          <div style="font-weight: 600;">${item.item_name}</div>
          <div style="font-size: 0.875rem; color: #666;">${item.qty} x ${CartManager.formatCurrency(parseFloat(item.price))}</div>
        </div>
        <div style="font-weight: 600;">${CartManager.formatCurrency(subtotal)}</div>
      </div>
    `;
  });
  
  summaryContainer.innerHTML = html;
  
  const total = CartManager.getTotal();
  document.getElementById('subtotal').textContent = CartManager.formatCurrency(total);
  document.getElementById('discount').textContent = CartManager.formatCurrency(0);
  document.getElementById('grand-total').textContent = CartManager.formatCurrency(total);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  renderOrderSummary();
  
  // Check if cart is empty
  const cart = CartManager.getCart();
  if (cart.length === 0) {
    alert('Keranjang kosong!');
    window.location.href = '<?= base_url('catalog') ?>';
    return;
  }
  
  // Form submission
  document.getElementById('checkout-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const cart = CartManager.getCart();
    if (cart.length === 0) {
      alert('Keranjang kosong!');
      return;
    }
    
    const formData = {
      customer_name: document.getElementById('customer_name').value,
      phone: document.getElementById('phone').value,
      plate_code: document.getElementById('plate_code').value,
      plate_number: document.getElementById('plate_number').value,
      plate_suffix: document.getElementById('plate_suffix').value,
      payment_status: document.getElementById('payment_status').value,
      items: cart.map(item => ({
        item_id: item.item_id,
        qty: parseInt(item.qty),
        price: parseFloat(item.price),
        discount: 0,
        subtotal: parseFloat(item.price) * parseInt(item.qty),
        sns: []
      }))
    };
    
    // Disable submit button
    const submitBtn = document.getElementById('btn-submit-order');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Memproses...';
    
    // Send to server
    fetch('<?= base_url('checkout/process') ?>', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        CartManager.clearCart();
        alert('Pesanan berhasil dibuat! Invoice: ' + (data.data?.invoice_no || 'N/A'));
        window.location.href = '<?= base_url('catalog') ?>';
      } else {
        alert('Error: ' + (data.message || 'Gagal membuat pesanan'));
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check" style="margin-right: 8px;"></i>Buat Pesanan';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Terjadi kesalahan saat memproses pesanan');
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="fas fa-check" style="margin-right: 8px;"></i>Buat Pesanan';
    });
  });
});
</script>

<?= $this->endSection() ?>

