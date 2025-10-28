<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Multi Automobile Vision – Garansi Nasional Tanpa Masalah') ?></title>
  <meta name="description" content="<?= esc($meta_description ?? 'Periksa dan klaim garansi produk Multi Automobile Vision. Cek lokasi toko, katalog produk, dan status garansi Anda.') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('themes/mav/assets/css/styles.css') ?>">
  <?php if (isset($is_location_page)): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <?php endif; ?>
  <link rel="icon"
    href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='16' fill='%230b0b0c'/><text x='50' y='62' text-anchor='middle' font-size='60' font-family='Arial' fill='%23ffc12e'>B</text></svg>">
</head>

<body>
  <header class="site-header">
    <div class="container header-inner">
      <a class="logo" href="<?= base_url('/') ?>" aria-label="Beranda">
        <img class="logo-img" src="<?= base_url('themes/mav/assets/images/logo.png') ?>" alt="EEBOT">
      </a>
      <nav class="nav" aria-label="Navigasi utama">
        <button class="nav-toggle" aria-expanded="false" aria-controls="nav-menu" aria-label="Buka menu">☰</button>
        <ul id="nav-menu" class="nav-menu">
          <li><a href="<?= base_url('/') ?>">Beranda</a></li>
          <li><a href="<?= base_url('location') ?>">Lokasi</a></li>
          <li><a href="<?= base_url('catalog') ?>">Katalog</a></li>
          <li><a href="<?= base_url('cek-garansi') ?>">Cek Garansi</a></li>
        </ul>
      </nav>
      <a class="btn btn-amber" href="<?php echo base_url('login'); ?>" role="button">Masuk</a>
    </div>
  </header>

  <main>

