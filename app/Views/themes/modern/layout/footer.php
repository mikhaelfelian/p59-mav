<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Footer layout template for tanpalogin theme
 * This file represents the View Layout.
 */
?>
<footer>
    <div class="footer-desc">
        <div class="wrapper">
            <div class="row mb-0">
                <div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 mb-2">
                    <h2 class="widget-title">Contact us</h2>
                    <ul class="list">
                        <li><i class="fa fa-envelope me-2"></i>Email: <?= $footer_contact_email ?? 'support@jagowebdev.com' ?></li>
                        <li><i class="fas fa-file-signature me-2"></i><a target="_blank" href="<?= $footer_contact_url ?? 'https://jagowebdev.com/members/contact' ?>">Via Contact form</a></li>
                    </ul>
                </div>
                <div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 mb-2">
                    <h2 class="widget-title">About</h2>
                    <p><?= $footer_about_text ?? 'Pusat belajar Web Development terbaik, dengan berbagai materi berkualitas' ?></p>
                    <ul class="list">
                        <li><i class="fab fa-facebook-square me-2"></i><a href="<?= $footer_facebook_url ?? 'https://web.facebook.com/JagoWebDev' ?>" target="_blank">facebook</a></li>
                    </ul>
                </div>
                <div class="col-sm-4 col-md-4 col-lg-4 col-xl-4">
                    <h2 class="widget-title">More Info</h2>
                    <ul class="list">
                        <li><i class="fa fa-user-plus me-2"></i><a href="<?= $footer_membership_url ?? 'https://jagowebdev.com/members/membership' ?>" target="_blank">Premium Member</a></li>
                        <li><i class="fas fa-external-link-alt me-2"></i><a href="<?= $footer_blog_url ?? 'http://jagowebdev.com/artikel/' ?>" target="_blank">Artikel Blog</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-menu-container">
        <div class="wrapper clearfix">
            <div class="nav-left">Copyright &copy; <?= date('Y') ?> <a title="<?= $footer_copyright_title ?? 'Jagowebdev' ?>" href="<?= $footer_copyright_url ?? 'https://jagowebdev.com' ?>"><?= $footer_copyright_title ?? 'Jagowebdev' ?></a>
            </div>
            <nav class="nav-right nav-footer">
                <ul class=footer-menu>
                    <?php if (isset($footer_menu) && is_array($footer_menu)): ?>
                        <?php foreach ($footer_menu as $menu): ?>
                            <li class="menu">
                                <a class="depth-0" href="<?= $menu['url'] ?>"><?= $menu['label'] ?></a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="menu">
                            <a class="depth-0" href="<?= $config->baseURL ?>">Home</a>
                        </li>
                        <li class="menu">
                            <a class="depth-0" href="tremofuser">Term of Use</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</footer>
