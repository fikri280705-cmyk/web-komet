<?php
require PATH_VIEWS . '/layouts/header.php';
require PATH_VIEWS . '/layouts/sidebar.php';
?>
<div class="main-wrapper">
    <?php require PATH_VIEWS . '/layouts/navbar.php'; ?>
    
    <main class="page-content">
        <?php require PATH_VIEWS . '/components/flash.php'; ?>
        <?= $content ?>
    </main>

    <?php require PATH_VIEWS . '/layouts/footer.php'; ?>

