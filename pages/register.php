<?php
// pages/register.php — K HO K Register
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();
if (isLoggedIn()) redirect('/');

$pageTitle = 'Create Account';
$pageCSS   = 'auth.css';
$pageJS    = 'auth.js';

require_once __DIR__ . '/../components/header.php';
?>

<section class="auth-section">
    <div class="auth-orb auth-orb--1"></div>
    <div class="auth-orb auth-orb--2"></div>

    <div class="auth-card glass-card auth-card--wide">
        <div class="auth-logo">K HO K</div>
        <h1 class="auth-title">Join the Mystery</h1>
        <p class="auth-sub">Create your account and start unboxing</p>

        <form class="auth-form" id="registerForm" action="<?= APP_URL ?>/api/auth/register.php" method="POST">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf'] ?? '') ?>">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="full_name">Full Name</label>
                    <input class="form-input" type="text" id="full_name" name="full_name"
                           placeholder="Your full name" required autocomplete="name">
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">WhatsApp / Phone</label>
                    <input class="form-input" type="tel" id="phone" name="phone"
                           placeholder="98XXXXXXXX" required autocomplete="tel">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input class="form-input" type="email" id="email" name="email"
                       placeholder="you@email.com" required autocomplete="email">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="city">City</label>
                    <select class="form-input form-select" id="city" name="city" required>
                        <option value="" disabled selected>Select city</option>
                        <option>Kathmandu</option>
                        <option>Lalitpur</option>
                        <option>Bhaktapur</option>
                        <option>Pokhara</option>
                        <option>Biratnagar</option>
                        <option>Birgunj</option>
                        <option>Butwal</option>
                        <option>Nepalgunj</option>
                        <option>Dharan</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="address">Delivery Address</label>
                    <input class="form-input" type="text" id="address" name="address"
                           placeholder="Street / Area" autocomplete="street-address">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="reg_password">Password</label>
                    <div class="input-wrap">
                        <input class="form-input" type="password" id="reg_password" name="password"
       placeholder="Min 8 characters, mix letters & numbers"
       required minlength="8" autocomplete="new-password">
<div style="margin-top:.4rem">
    <div style="height:3px;background:var(--border);border-radius:100px;overflow:hidden">
        <div id="strengthBar" style="height:100%;width:0;transition:all .3s;border-radius:100px"></div>
    </div>
    <span id="strengthText" style="font-size:.7rem;margin-top:.2rem;display:block"></span>
</div>
                        <button type="button" class="input-eye" id="togglePwd" aria-label="Show password">👁</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input class="form-input" type="password" id="confirm_password" name="confirm_password"
                           placeholder="Repeat password" required autocomplete="new-password">
                </div>
            </div>

            <div id="formError" class="form-error" style="display:none"></div>

            <button type="submit" class="btn-primary btn-full" id="registerBtn">
                <span class="btn-text">Create Account</span>
                <span class="btn-loader" style="display:none">...</span>
            </button>
        </form>

        <p class="auth-switch">
            Already have an account?
            <a href="<?= APP_URL ?>/login">Login →</a>
        </p>
    </div>
</section>

<?php require_once __DIR__ . '/../components/footer.php'; ?>