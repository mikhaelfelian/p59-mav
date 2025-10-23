<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Header layout template for tanpalogin theme
 * This file represents the View Layout.
 */
?>
<header class="shadow-sm">
    <div class="menu-wrapper wrapper clearfix">
        <a href="#" id="mobile-menu-btn" class="show-mobile">
            <i class="fa fa-bars"></i>
        </a>
        <div class="nav-left">
            <a href="<?= $config->baseURL ?>" class="logo-header" title="<?= $site_title ?? 'Jagowebdev' ?>">
                <img src="<?= $config->baseURL ?>public/images/logo_login.png" alt="<?= $site_title ?? 'Jagowebdev' ?>"/>
            </a>
        </div>
        <nav class="nav-right nav-header">
            <ul class="main-menu">
                <?php
                // Build menu from modules table - only show modules with no role requirement
                $db = \Config\Database::connect();
                $builder = $db->table('module');
                $builder->select('nama_module, judul_module, deskripsi');
                $builder->where('login', 'N'); // Only modules that don't require login
                $builder->where('id_module_status', 1); // Only active modules
                $publicModules = $builder->get()->getResultArray();
                
                if (!empty($publicModules)): 
                    foreach ($publicModules as $module): 
                        $moduleUrl = $config->baseURL . $module['nama_module'];
                        $moduleIcon = 'fas fa-circle'; // Default icon
                        
                        // Set specific icons for known modules
                        switch($module['nama_module']) {
                            case 'frontend':
                                $moduleIcon = 'fas fa-home';
                                break;
                            case 'item-category':
                                $moduleIcon = 'fas fa-tags';
                                break;
                            case 'item-brand':
                                $moduleIcon = 'fas fa-award';
                                break;
                            case 'item-spec':
                                $moduleIcon = 'fas fa-cogs';
                                break;
                            case 'item':
                                $moduleIcon = 'fas fa-box';
                                break;
                            default:
                                $moduleIcon = 'fas fa-circle';
                        }
                ?>
                    <li class="menu">
                        <a class="depth-0" href="<?= $moduleUrl ?>">
                            <i class="menu-icon <?= $moduleIcon ?>"></i><?= $module['judul_module'] ?>
                        </a>
                    </li>
                <?php 
                    endforeach; 
                else: 
                    // Fallback menu if no public modules found
                ?>
                    <li class="menu">
                        <a class="depth-0" href="<?= $config->baseURL ?>frontend">
                            <i class="menu-icon fas fa-home"></i>Home
                        </a>
                    </li>
                    <li class="menu">
                        <a class="depth-0" href="<?= $config->baseURL ?>frontend/about">
                            <i class="menu-icon fas fa-info-circle"></i>Tentang
                        </a>
                    </li>
                    <li class="menu">
                        <a class="depth-0" href="<?= $config->baseURL ?>frontend/contact">
                            <i class="menu-icon fas fa-envelope"></i>Kontak
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="clearfix"></div>
    </div>
</header>
