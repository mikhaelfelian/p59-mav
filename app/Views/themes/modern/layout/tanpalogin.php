<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Main layout template for tanpalogin theme using CodeIgniter 4.3.1 layout system
 * This file represents the View Layout.
 */
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <title><?= $title ?? 'Default Title' ?> | <?= $site_title ?? 'Frontend' ?></title>
    <meta name="description" content="<?= $meta_description ?? 'Default Description' ?>"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <link rel="shortcut icon" href="<?= $config->baseURL ?>public/images/favicon.png" />
    <link rel="stylesheet" type="text/css" href="<?= $config->baseURL ?>public/vendors/fontawesome/css/all.css?r=<?= time() ?>"/>
    <link rel="stylesheet" type="text/css" href="<?= $config->baseURL ?>public/vendors/bootstrap/css/bootstrap.min.css?r=<?= time() ?>"/>
    <link rel="stylesheet" type="text/css" href="<?= $config->baseURL ?>public/themes/modern/builtin/css/bootstrap-custom.css?r=<?= time() ?>"/>
    <link rel="stylesheet" type="text/css" href="<?= $config->baseURL ?>public/themes/modern/css/tanpalogin.css?r=<?= time() ?>"/>
    <link rel="stylesheet" type="text/css" href="<?= $config->baseURL ?>public/vendors/overlayscrollbars/OverlayScrollbars.min.css?r=<?= time() ?>"/>
    <link rel="stylesheet" id="font-switch" type="text/css" href="<?= $config->baseURL . 'public/themes/modern/builtin/css/fonts/' . $app_layout['font_family'] . '.css?r=' . time() ?>"/>
    <link rel="stylesheet" id="font-size-switch" type="text/css" href="<?= $config->baseURL . 'public/themes/modern/builtin/css/fonts/font-size-' . $app_layout['font_size'] . '.css?r=' . time() ?>"/>

    <?= $this->renderSection('css') ?>

    <script type="text/javascript" src="<?= $config->baseURL ?>public/vendors/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?= $config->baseURL ?>public/themes/modern/js/site.js?r=<?= time() ?>"></script>
    <script type="text/javascript" src="<?= $config->baseURL ?>public/vendors/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?= $config->baseURL ?>public/vendors/overlayscrollbars/jquery.overlayScrollbars.min.js"></script>
    <script type="text/javascript">
        var base_url = "<?= $config->baseURL ?>";
    </script>
</head>

<body>
    <div class="site-container">
        <!-- Header -->
        <?= $this->include('themes/modern/layout/header') ?>

        <!-- Page Container -->
        <div class="page-container">
            <!-- Page Header -->
            <?= $this->include('themes/modern/layout/page_header') ?>

            <!-- Content Wrapper -->
            <div class="wrapper">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?= $msg ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row article-single-container">
                    <?php 
                    // Check if content is provided directly (like Tanpalogin.php pattern)
                    if (isset($content)) {
                        echo $content;
                    } else {
                        // Fallback to section rendering
                        echo $this->renderSection('content');
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?= $this->include('themes/modern/layout/footer') ?>
    </div>
    <!-- ./site-container -->

    <?= $this->renderSection('js') ?>

    <!-- Flash messages script -->
    <script>
        $(document).ready(function () {
            // Flash messages
            <?php if (session()->getFlashdata('success')): ?>
                if (typeof toastr !== 'undefined') {
                    toastr.success('<?= session()->getFlashdata('success') ?>');
                } else {
                    alert('<?= session()->getFlashdata('success') ?>');
                }
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                if (typeof toastr !== 'undefined') {
                    toastr.error('<?= session()->getFlashdata('error') ?>');
                } else {
                    alert('<?= session()->getFlashdata('error') ?>');
                }
            <?php endif; ?>

            <?php if (session()->getFlashdata('warning')): ?>
                if (typeof toastr !== 'undefined') {
                    toastr.warning('<?= session()->getFlashdata('warning') ?>');
                } else {
                    alert('<?= session()->getFlashdata('warning') ?>');
                }
            <?php endif; ?>

            <?php if (session()->getFlashdata('info')): ?>
                if (typeof toastr !== 'undefined') {
                    toastr.info('<?= session()->getFlashdata('info') ?>');
                } else {
                    alert('<?= session()->getFlashdata('info') ?>');
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>

