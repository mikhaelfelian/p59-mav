<?= $this->extend('themes/mav/layout/main') ?>

<?= $this->section('content') ?>

<section class="section">
  <div class="container">
    <div class="page-content">
      <?php if (!empty($page_content)): ?>
        <?php
        // Decode HTML entities and display content from TinyMCE
        echo html_entity_decode($page_content, ENT_QUOTES, 'UTF-8');
        ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<?= $this->endSection() ?>

