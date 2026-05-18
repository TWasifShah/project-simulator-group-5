<?php $success = consume_flash('success'); $error = consume_flash('error'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($title ?? 'Online Food Blog') ?></title>
    <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= url('home') ?>">Online Food Blog</a>
        <button class="menu-toggle" id="menuToggle" type="button">Menu</button>
        <nav class="main-nav" id="mainNav">
            <a href="<?= url('home') ?>">Home</a>
            <a href="<?= url('browse') ?>">Browse</a>
            <a href="<?= url('food-experience') ?>">Food Experience</a>
            <?php if (is_admin()): ?>
                <a href="<?= url('admin/dashboard') ?>">Admin</a>
                <a href="<?= url('admin/restaurants') ?>">Restaurants</a>
                <a href="<?= url('admin/menu-items') ?>">Menu Items</a>
                <a href="<?= url('admin/members') ?>">Members</a>
            <?php endif; ?>
            <?php if (is_logged_in()): ?>
                <a href="<?= url('profile') ?>">Profile</a>
                <a href="<?= url('logout') ?>">Logout</a>
            <?php else: ?>
                <a href="<?= url('login') ?>">Login</a>
                <a class="btn-small" href="<?= url('register') ?>">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container page">
    <?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
