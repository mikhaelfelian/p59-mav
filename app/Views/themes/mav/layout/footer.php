  </main>

  <footer class="site-footer">
    <div class="container footer-inner">
      <div class="brand">
        <?php 
        // Load settings directly if not available
        // Dapatkan judul_web dan logo_app dari setting (cache jika available, fallback ke query)
        if (!isset($settingAplikasi) || !is_array($settingAplikasi)) {
            $settingModel = new \App\Models\Builtin\SettingAppModel();
            $appSettings  = $settingModel->getSettingAplikasi();
            $judulWeb     = $appSettings['judul_web'] ?? null;
            $logoApp      = $appSettings['logo_app'] ?? null;
        } else {
            $judulWeb     = $settingAplikasi['judul_web'] ?? null;
            $logoApp      = $settingAplikasi['logo_app'] ?? null;
        }
        ?>
        <?php if (!empty($logoApp)): ?>
        <div class="logo logo-foot">
          <img class="logo-img" src="<?= esc(base_url('public/images/' . $logoApp)) ?>" alt="<?= esc($judulWeb ?? '') ?>">
        </div>
        <?php endif; ?>
        <?php if (!empty($judulWeb)): ?>
        <p><?= esc($identitas['nama']) ?><br><?= esc($identitas['alamat']) ?></p>
        <?php endif; ?>
      </div>
      <nav class="footer-cols">
        <div>
          <h4>Halaman</h4>
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
</body>

</html>

