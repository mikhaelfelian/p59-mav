<?= $this->extend('themes/mav/layout/main') ?>

<?= $this->section('content') ?>

<section class="hero hero-bg">
  <div class="container hero-inner">
    <div class="hero-badge">Dipercaya oleh <?php echo $hero_user_count ?? '0'; ?>+ Pengguna</div>
    <?php if (!empty($hero_title)): ?>
      <h1 class="hero-title">
        <?php
        // If title contains HTML tags, output as-is; otherwise escape
        echo (strip_tags($hero_title) !== $hero_title) ? $hero_title : esc($hero_title);
        ?>
      </h1>
    <?php endif; ?>

    <?php if (!empty($hero_subtitle)): ?>
      <p class="hero-subtitle">
        <?php
        echo html_entity_decode($hero_subtitle, ENT_QUOTES, 'UTF-8');
        ?>
      </p>
    <?php endif; ?>

    <?php if (!empty($hero_cta_text) && !empty($hero_cta_link)): ?>
      <div class="hero-actions">
        <?php
        $ctaLink = $hero_cta_link;
        // If link doesn't start with http:// or https://, treat it as relative and use site_url
        if (!preg_match('/^https?:\/\//', $ctaLink)) {
          // Remove leading slash if present to avoid double slashes
          $ctaLink = ltrim($ctaLink, '/');
          $ctaLink = site_url($ctaLink);
        }
        ?>
        <a class="btn btn-amber btn-lg" href="<?= esc($ctaLink) ?>"><?= esc($hero_cta_text) ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="features section">
  <div class="container grid-3">
    <?php
    $featureList = [];
    // $features is already json_decoded on controller
    if (is_string($features)) {
      // If $features is accidentally not decoded
      $featureList = json_decode($features, true);
    } elseif (is_array($features) || is_object($features)) {
      $featureList = (array) $features;
    }

    foreach ($featureList as $feature) {
      // Use icon directly from database (should contain full FontAwesome class like 'fa-lock', 'fa-bolt', etc.)
      $iconClass = $feature['icon'] ?? '';
      ?>
      <article class="feature">
        <?php if (!empty($iconClass)): ?>
        <div class="feature-icon">
          <i class="fa <?= esc($iconClass) ?>"></i>
        </div>
        <?php endif; ?>
        <h3><?= esc($feature['title'] ?? '') ?></h3>
        <p><?= esc($feature['desc'] ?? '') ?></p>
      </article>
    <?php } ?>
  </div>
</section>

<section class="cta section">
  <div class="container cta-inner">
    <div>
      <h2>Temukan Agen Terdekat</h2>
      <p>Lihat peta interaktif dan ribuan titik layanan resmi.</p>
    </div>
    <a class="btn btn-outline" href="<?= site_url('location') ?>">Buka Peta</a>
  </div>
</section>

<?= $this->endSection() ?>