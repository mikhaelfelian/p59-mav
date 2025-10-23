<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Page header layout template for tanpalogin theme
 * This file represents the View Layout.
 */
?>
<div class="title-container shadow-lg">
    <div class="wrapper wrapper-post-single">
        <h1 class="post-title"><?= $page_title ?? $title ?? 'Default Page Title' ?></h1>
        <div class="clearfix post-meta-single">
            <p class="post-description"><?= $page_description ?? $meta_description ?? 'Default page description' ?></p>
        </div>
    </div>
</div>
