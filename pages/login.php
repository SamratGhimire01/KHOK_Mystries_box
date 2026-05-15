<?php
// pages/login.php — K HO K Login
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();
if (isLoggedIn()) redirect('/');

$pageTitle = 'Login';
$pageCSS   = 'auth.css';
$pageJS    = 'auth.js';

require_once __DIR__ . '/../components/header.php';
?>

<section class="auth-section">
    <div class="auth-orb auth-orb--1"></div>
    <div class="auth-orb auth-orb--2"></div>

    <div class="auth-card glass-card">
        <div class="auth-logo">K HO K</div>
        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-sub">Enter the mystery</p>

        <form class="auth-form" id="loginForm" action="<?= APP_URL ?>/api/auth/login.php" method="POST">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf'] ?? '') ?>">

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input class="form-input" type="email" id="email" name="email"
                       placeholder="you@email.com" required autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrap">
                    <input class="form-input" type="password" id="password" name="password"
                           placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="input-eye" id="togglePwd" aria-label="Show password">👁</button>
                </div>
            </div>

            <div id="formError" class="form-error" style="display:none"></div>

            <button type="submit" class="btn-primary btn-full" id="loginBtn">
                <span class="btn-text">Login</span>
                <span class="btn-loader" style="display:none">...</span>
            </button>
        </form>

        <p class="auth-switch">
            Don't have an account?
            <a href="<?= APP_URL ?>/register">Create one →</a>
        </p>
    </div>
</section>

<?php require_once __DIR__ . '/../components/footer.php'; ?>