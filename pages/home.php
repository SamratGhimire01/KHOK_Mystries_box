<?php
// pages/home.php — K HO K Home Page
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();

$pageTitle = 'Mystery Awaits';
$pageCSS   = 'home.css';
$pageJS    = 'home.js';

require_once __DIR__ . '/../components/header.php';
?>

<!-- ═══════════════════════════════════════
     HERO SECTION
═══════════════════════════════════════ -->
<section class="hero">
    <div class="hero-bg-grid"></div>
    <div class="hero-orb hero-orb--1"></div>
    <div class="hero-orb hero-orb--2"></div>

    <div class="hero-inner container">
        <div class="hero-eyebrow">
            <span class="eyebrow-dot"></span>
            Nepal's First Mystery Reward Platform
        </div>

        <h1 class="hero-title">
            You Don't Choose<br>
            <span class="hero-title--accent">The Mystery</span><br>
            Chooses You.
        </h1>

        <p class="hero-subtitle">
            Buy a box. Receive premium tech, gaming gear &amp; lifestyle products.<br>
            Every order is a surprise. Every box is an experience.
        </p>

        <div class="hero-cta">
            <a href="<?= APP_URL ?>/boxes" class="btn-hero-primary">
                Open a Mystery Box
                <span class="btn-arrow">→</span>
            </a>
            <a href="#how-it-works" class="btn-hero-ghost">See How It Works</a>
        </div>

        <div class="hero-stats">
            <div class="stat">
                <span class="stat-number">6</span>
                <span class="stat-label">Box Tiers</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat">
                <span class="stat-number">33+</span>
                <span class="stat-label">Products Inside</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat">
                <span class="stat-number">Rs.999</span>
                <span class="stat-label">Starting From</span>
            </div>
        </div>
    </div>

    <!-- Animated mystery box -->
    <div class="mystery-box-wrap">
        <div class="mystery-box" id="mysteryBox">
            <div class="box-face box-top">
                <span class="box-logo">K HO K</span>
            </div>
            <div class="box-face box-front">
                <div class="box-qmark">?</div>
            </div>
            <div class="box-face box-side"></div>
            <div class="box-glow"></div>
        </div>
        <div class="box-shadow"></div>
        <div class="box-particles" id="boxParticles"></div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     BOXES PREVIEW SECTION
═══════════════════════════════════════ -->
<section class="section boxes-preview">
    <div class="container">
        <div class="section-header">
            <p class="section-eyebrow">Choose Your Tier</p>
            <h2 class="section-title">Six Boxes. Infinite Possibilities.</h2>
        </div>

        <div class="boxes-grid">
            <?php foreach (BOXES as $slug => $box): ?>
            <a href="<?= APP_URL ?>/boxes" class="box-card box-card--<?= $slug ?>">
                <div class="box-card-glow"></div>
                <div class="box-card-inner">
                    <div class="box-card-top">
                        <span class="box-card-badge"><?= $slug === 'god' ? '👑 LEGENDARY' : strtoupper($slug) ?></span>
                    </div>
                    <h3 class="box-card-name"><?= e($box['name']) ?></h3>
                    <p class="box-card-tagline"><?= e($box['tagline']) ?></p>
                    <div class="box-card-price"><?= formatPrice($box['price']) ?></div>
                    <div class="box-card-count"><?= $box['min'] ?>–<?= $box['max'] ?> products inside</div>
                </div>
                <div class="box-card-arrow">→</div>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="boxes-preview-cta">
            <a href="<?= APP_URL ?>/boxes" class="btn-primary">View All Boxes</a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     HOW IT WORKS
═══════════════════════════════════════ -->
<section class="section how-it-works" id="how-it-works">
    <div class="container">
        <div class="section-header">
            <p class="section-eyebrow">The Process</p>
            <h2 class="section-title">How K HO K Works</h2>
        </div>

        <div class="steps-grid">
            <div class="step">
                <div class="step-number">01</div>
                <div class="step-icon">📦</div>
                <h3 class="step-title">Pick a Box</h3>
                <p class="step-desc">Choose your tier from Shadow Box at Rs.999 to the legendary GOD BOX at Rs.99,999.</p>
            </div>
            <div class="step-connector"></div>
            <div class="step">
                <div class="step-number">02</div>
                <div class="step-icon">💳</div>
                <h3 class="step-title">Pay Securely</h3>
                <p class="step-desc">Pay via eSewa or Fonepay. Your order is confirmed instantly.</p>
            </div>
            <div class="step-connector"></div>
            <div class="step">
                <div class="step-number">03</div>
                <div class="step-icon">🎲</div>
                <h3 class="step-title">Mystery Assigned</h3>
                <p class="step-desc">Our weighted reward engine selects your products. Could be AirPods. Could be an iPhone.</p>
            </div>
            <div class="step-connector"></div>
            <div class="step">
                <div class="step-number">04</div>
                <div class="step-icon">🚀</div>
                <h3 class="step-title">Delivered to You</h3>
                <p class="step-desc">Track your order in real time. Delivered across Nepal with WhatsApp updates.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     PRODUCTS TEASER
═══════════════════════════════════════ -->
<section class="section products-teaser">
    <div class="container">
        <div class="section-header">
            <p class="section-eyebrow">What's Inside</p>
            <h2 class="section-title">Premium Products. Real Brands.</h2>
            <p class="section-sub">Every product is genuine. Every brand is real. No cheap imitations.</p>
        </div>

        <div class="brands-ticker">
            <div class="ticker-track">
                <?php
                $brands = ['Apple','Logitech','Razer','JBL','HyperX','Xbox','DJI','GoPro','Nintendo','Yamaha','Xiaomi','Fantech','Redragon','Ultima','Black Shark'];
                $repeated = array_merge($brands, $brands);
                foreach ($repeated as $brand): ?>
                <span class="ticker-item"><?= e($brand) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="products-preview-grid">
            <div class="product-teaser-card">
                <div class="ptc-emoji">🎮</div>
                <span>Gaming Gear</span>
            </div>
            <div class="product-teaser-card">
                <div class="ptc-emoji">🎧</div>
                <span>Audio</span>
            </div>
            <div class="product-teaser-card">
                <div class="ptc-emoji">📱</div>
                <span>Smartphones</span>
            </div>
            <div class="product-teaser-card">
                <div class="ptc-emoji">⌚</div>
                <span>Wearables</span>
            </div>
            <div class="product-teaser-card">
                <div class="ptc-emoji">🎸</div>
                <span>Lifestyle</span>
            </div>
            <div class="product-teaser-card ptc-mystery">
                <div class="ptc-emoji">?</div>
                <span>???</span>
            </div>
        </div>
    </div>
</section>



<!-- ═══════════════════════════════════════
     FINAL CTA
═══════════════════════════════════════ -->
<section class="section final-cta">
    <div class="container">
        <div class="final-cta-box glass-card">
            <div class="final-cta-orb"></div>
            <h2 class="final-cta-title">Ready to Open Your Box?</h2>
            <p class="final-cta-sub">Join thousands of mystery hunters across Nepal.</p>
            <div class="final-cta-buttons">
                <a href="<?= APP_URL ?>/boxes" class="btn-primary">Start the Mystery</a>
                <?php if (!isLoggedIn()): ?>
                <a href="<?= APP_URL ?>/register" class="btn-outline">Create Account</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../components/footer.php'; ?>