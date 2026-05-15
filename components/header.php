<?php
// components/header.php — shared site header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' . APP_NAME : APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/reset.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/global.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/nav.css">
    <?php if (isset($pageCSS)): ?>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/<?= e($pageCSS) ?>">
    <?php endif; ?>
</head>
<body>

<nav class="navbar">
    <a href="<?= APP_URL ?>/" class="nav-logo">K HO K</a>
    <ul class="nav-links">
        <li><a href="<?= APP_URL ?>/">Home</a></li>
        <li><a href="<?= APP_URL ?>/boxes">Boxes</a></li>
        <?php if (isLoggedIn()): ?>
        <li><a href="<?= APP_URL ?>/track">Track Order</a></li>
        <li><a href="<?= APP_URL ?>/profile">Profile</a></li>
        <?php if (isAdmin()): ?>
        <li><a href="<?= APP_URL ?>/admin" class="nav-admin">Admin</a></li>
        <?php endif; ?>
        <li><a href="<?= APP_URL ?>/logout" class="nav-btn-outline">Logout</a></li>
        <?php else: ?>
        <li><a href="<?= APP_URL ?>/login" class="nav-btn">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>

<?php
// Flash messages
$flash = getFlash();
if ($flash): ?>
<div class="flash flash-<?= e($flash['type']) ?>">
    <?= e($flash['message']) ?>
</div>
<?php endif; ?>
