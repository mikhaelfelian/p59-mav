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
            <li><a href="<?php echo base_url('about-me'); ?>">Tentang Kami</a></li>
            <li><a href="<?php echo base_url('privacy-policy'); ?>">Kebijakan Privasi</a></li>
          </ul>
        </div>
        <div>
          <h4>Ikuti Kami</h4>
          <?php
          // Load social media settings from database
          if (!isset($settingAplikasi) || !is_array($settingAplikasi)) {
              $settingModel = new \App\Models\Builtin\SettingAppModel();
              $appSettingsRaw = $settingModel->getSettingAplikasi();
              // Convert raw rows to keyed array
              $appSettings = [];
              foreach ($appSettingsRaw as $setting) {
                  $appSettings[$setting['param']] = $setting['value'];
              }
              $socialJson = $appSettings['social'] ?? null;
          } else {
              $socialJson = $settingAplikasi['social'] ?? null;
          }
          
          // Decode JSON string to array
          $socialMedia = !empty($socialJson) ? json_decode($socialJson, true) : null;
          ?>
          <?php if (!empty($socialMedia) && is_array($socialMedia)): ?>
          <div class="socials">
            <?php foreach ($socialMedia as $social): ?>
              <a href="<?= esc($social['link'] ?? '#') ?>" target="_blank" rel="noopener" aria-label="<?= esc($social['title'] ?? '') ?>">
                <i class="<?= esc($social['icon'] ?? '') ?>"></i>
              </a><br/>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </nav>
    </div>
  </footer>

  <?php
    $waNumberRaw = $identitas['no_telp'] ?? '';
    // Replace first character 0 with "62" for WhatsApp link
    $waNumber = preg_replace('/^0/', '62', preg_replace('/\D+/', '', $waNumberRaw));
  ?>
  <a class="whatsapp" href="https://wa.me/<?= esc($waNumber) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
    <img src="<?= base_url('themes/mav/assets/images/whatsapp.gif') ?>" alt="WhatsApp">
  </a>

  <script src="<?= base_url('themes/mav/assets/js/main.js') ?>" defer></script>
</body>

</html>

