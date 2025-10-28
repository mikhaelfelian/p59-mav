<?= $this->extend('themes/mav/layout/main') ?>

<?= $this->section('content') ?>

<section class="hero hero-bg">
  <div class="container hero-inner">
    <div class="hero-badge">Dipercaya oleh 100.000+ Pengguna</div>
    <h1 class="hero-title">
      Garansi Nasional <span class="text-fade">Tanpa</span> Masalah – Kapan saja,
      <br> Kapan pun
    </h1>
    <p class="hero-subtitle">Periksa dan klaim garansi Multi Automobile Vision Anda dalam hitungan detik. Cukup
      masukkan nomor plat kendaraan dan nomor telepon Anda – tanpa perlu faktur.</p>
    <div class="hero-actions">
      <a class="btn btn-amber btn-lg" href="<?= site_url('frontend/cek-garansi') ?>">Cek Garansi Sekarang</a>
    </div>
  </div>
</section>

<section class="features section">
  <div class="container grid-3">
    <article class="feature">
      <div class="feature-icon">🔒</div>
      <h3>Garansi Nasional</h3>
      <p>Berlaku di jaringan toko resmi kami di seluruh Indonesia.</p>
    </article>
    <article class="feature">
      <div class="feature-icon">⚡</div>
      <h3>Proses Cepat</h3>
      <p>Verifikasi hanya dengan plat kendaraan dan nomor telepon.</p>
    </article>
    <article class="feature">
      <div class="feature-icon">💬</div>
      <h3>Dukungan Langsung</h3>
      <p>Tim kami siap membantu kapan saja Anda butuhkan.</p>
    </article>
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

