<?php
// components/admin_sidebar.php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function isActive(string $path, string $current): string {
    return str_contains($current, $path) ? 'active' : '';
}
?>
<aside class="admin-sidebar">
    <div class="sidebar-logo">
        <span class="sidebar-logo-text">K HO K</span>
        <span class="sidebar-logo-badge">ADMIN</span>
    </div>

    <nav class="sidebar-nav">
        <p class="sidebar-nav-label">Overview</p>
        <a href="<?= APP_URL ?>/admin"
           class="sidebar-link <?= (rtrim($currentPath,'/') === '/khok/admin') ? 'active' : '' ?>">
            <span class="sidebar-icon">📊</span> Dashboard
        </a>

        <p class="sidebar-nav-label">Manage</p>
        <a href="<?= APP_URL ?>/admin/orders"
           class="sidebar-link <?= isActive('/admin/orders', $currentPath) ?>">
            <span class="sidebar-icon">📦</span> Orders
        </a>
        <a href="<?= APP_URL ?>/admin/products"
           class="sidebar-link <?= isActive('/admin/products', $currentPath) ?>">
            <span class="sidebar-icon">🎁</span> Products
        </a>
        <a href="<?= APP_URL ?>/admin/users"
           class="sidebar-link <?= isActive('/admin/users', $currentPath) ?>">
            <span class="sidebar-icon">👥</span> Users
        </a>
        <a href="<?= APP_URL ?>/admin/delivery"
           class="sidebar-link <?= isActive('/admin/delivery', $currentPath) ?>">
            <span class="sidebar-icon">🚚</span> Delivery
        </a>

        <p class="sidebar-nav-label">Account</p>
        <a href="<?= APP_URL ?>/profile"
           class="sidebar-link">
            <span class="sidebar-icon">👤</span> My Profile
        </a>
        <a href="<?= APP_URL ?>/logout"
           class="sidebar-link sidebar-link--danger">
            <span class="sidebar-icon">🚪</span> Logout
        </a>
    </nav>
</aside>