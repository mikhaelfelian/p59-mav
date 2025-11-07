  </main>

  <footer class="site-footer">
    <div class="container footer-inner">
      <div class="brand">
        <div class="logo logo-foot"><img class="logo-img" src="<?= base_url('themes/mav/assets/images/logo.png') ?>" alt="EEBOT"></div>
        <p>Multi Automobile Vision<br>Komplek Duta Permai, Bekasi, Indonesia 17145</p>
      </div>
      <nav class="footer-cols">
        <div>
          <h4>Bahasa</h4>
          <ul>
            <li><a href="#">Inggris</a></li>
            <li><a href="#">Indonesia</a></li>
          </ul>
        </div>
        <div>
          <h4>Perusahaan</h4>
          <ul>
            <li><a href="#">Tentang Kami</a></li>
            <li><a href="#">Kebijakan Privasi</a></li>
          </ul>
        </div>
        <div>
          <h4>Ikuti Kami</h4>
          <div class="socials">
            <a href="#" aria-label="Instagram">&#x1F4F7;</a>
            <a href="#" aria-label="TikTok">&#x1F3A4;</a>
            <a href="#" aria-label="YouTube">&#x25B6;&#xFE0F;</a>
          </div>
        </div>
      </nav>
    </div>
  </footer>

  <a class="whatsapp" href="https://wa.me/6280000000000" target="_blank" rel="noopener" aria-label="WhatsApp">
    <img src="<?= base_url('themes/mav/assets/images/whatsapp.gif') ?>" alt="WhatsApp">
  </a>

  <script src="<?= base_url('themes/mav/assets/js/main.js') ?>" defer></script>
  <script>
  // Global cart badge initialization
  (function() {
    function updateCartBadge() {
      try {
        const cart = localStorage.getItem('mav_cart');
        const cartData = cart ? JSON.parse(cart) : [];
        const count = cartData.reduce((total, item) => total + (parseInt(item.qty) || 0), 0);
        const badge = document.getElementById('cart-badge');
        if (badge) {
          badge.textContent = count;
          badge.style.display = count > 0 ? 'flex' : 'none';
        }
      } catch (e) {
        console.error('Error updating cart badge:', e);
      }
    }
    
    // Update badge on page load
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', updateCartBadge);
    } else {
      updateCartBadge();
    }
    
    // Update badge when storage changes (for cross-tab updates)
    window.addEventListener('storage', function(e) {
      if (e.key === 'mav_cart') {
        updateCartBadge();
      }
    });
  })();
  </script>
</body>

</html>

