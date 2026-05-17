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
     TESTIMONIALS SECTION
     Paste this block in pages/home.php
     before the final-cta section
═══════════════════════════════════════ -->

<section class="testi-section">
    <div class="container">
        <div class="section-header">
            <p class="section-eyebrow">Real People · Real Unboxings</p>
            <h2 class="section-title">What Nepal is Saying</h2>
        </div>

        <!-- Slider wrapper -->
        <div class="testi-slider-wrap">
            <!-- Prev button -->
            <button class="testi-nav testi-nav--prev" id="testiPrev">&#8592;</button>

            <!-- Viewport -->
            <div class="testi-viewport" id="testiViewport">
                <div class="testi-track" id="testiTrack">

                    <?php
                    // Fetch testimonials
                    $testimonials = [];
                    try {
                        $dbT  = getDB();
                        $stmT = $dbT->query('
                            SELECT t.*, u.full_name, u.city
                            FROM testimonials t
                            JOIN users u ON u.id = t.user_id
                            WHERE t.is_approved = 1
                            ORDER BY t.created_at DESC
                            LIMIT 20
                        ');
                        $testimonials = $stmT->fetchAll();
                    } catch(Exception $e) { $testimonials = []; }

                    // If no testimonials yet show placeholders
                    if (empty($testimonials)) {
                        $testimonials = [
                            ['full_name'=>'Aarav Sharma',   'city'=>'Kathmandu', 'rating'=>5, 'review'=>'Got Apple AirPods in my Elite Box! Could not believe it. K HO K is 100% real and genuine.', 'box_slug'=>'elite',   'user_id'=>0],
                            ['full_name'=>'Priya Thapa',    'city'=>'Pokhara',   'rating'=>5, 'review'=>'Ordered Phantom Box and received a Nintendo Switch Lite. Best Rs.24,999 I ever spent!',    'box_slug'=>'phantom', 'user_id'=>0],
                            ['full_name'=>'Roshan KC',      'city'=>'Lalitpur',  'rating'=>4, 'review'=>'Started with Core Box for Rs.2,999 and got a JBL Go Speaker. Super happy with the value.', 'box_slug'=>'core',    'user_id'=>0],
                            ['full_name'=>'Sita Gurung',    'city'=>'Biratnagar','rating'=>5, 'review'=>'HyperX headset in my Pulse Box. Packaging was premium and delivery was super fast.',         'box_slug'=>'pulse',   'user_id'=>0],
                            ['full_name'=>'Bikash Rai',     'city'=>'Butwal',    'rating'=>5, 'review'=>'GOD BOX changed my life. iPhone 13 arrived at my door. This platform is legendary.',        'box_slug'=>'god',     'user_id'=>0],
                            ['full_name'=>'Manish Lama',    'city'=>'Dharan',    'rating'=>5, 'review'=>'Received a Razer DeathAdder in my Pulse Box. Delivery was fast and packaging was fire!',    'box_slug'=>'pulse',   'user_id'=>0],
                        ];
                    }

                    foreach ($testimonials as $t):
                        $stars   = str_repeat('★', (int)$t['rating']) . str_repeat('☆', 5 - (int)$t['rating']);
                        $initial = strtoupper(substr($t['full_name'], 0, 1));
                        $boxLabel = $t['box_slug'] ? ucfirst($t['box_slug']) . ' Box' : 'Mystery Box';
                        $profileUrl = $t['user_id'] ? APP_URL . '/profile?view=' . $t['user_id'] : '#';
                    ?>
                    <div class="testi-slide">
                        <div class="testi-card">
                            <!-- Quote mark -->
                            <div class="testi-quote">"</div>

                            <!-- Stars -->
                            <div class="testi-stars"><?= $stars ?></div>

                            <!-- Review text -->
                            <p class="testi-text"><?= e($t['review']) ?></p>

                            <!-- User info -->
                            <div class="testi-user">
                                <a href="<?= $profileUrl ?>" class="testi-avatar">
                                    <?= $initial ?>
                                </a>
                                <div class="testi-user-info">
                                    <p class="testi-name"><?= e($t['full_name']) ?></p>
                                    <p class="testi-meta">
                                        <span class="testi-box-tag"><?= e($boxLabel) ?></span>
                                        <?php if ($t['city']): ?>
                                        · <?= e($t['city']) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div><!-- end track -->
            </div><!-- end viewport -->

            <!-- Next button -->
            <button class="testi-nav testi-nav--next" id="testiNext">&#8594;</button>
        </div>

        <!-- Dots -->
        <div class="testi-dots" id="testiDots"></div>

    </div>
</section>

<style>
/* ── TESTIMONIALS SECTION ── */
.testi-section {
    padding:5rem 0;
    background:var(--bg-secondary);
    overflow:hidden;
}

.testi-slider-wrap {
    position:relative;
    display:flex;
    align-items:center;
    gap:1rem;
    margin-top:2.5rem;
}

.testi-viewport {
    overflow:hidden;
    flex:1;
    border-radius:var(--radius-md);
}

.testi-track {
    display:flex;
    gap:1.25rem;
    transition:transform 0.5s cubic-bezier(0.25,0.46,0.45,0.94);
    will-change:transform;
}

.testi-slide {
    flex:0 0 calc(33.333% - 0.85rem);
    min-width:0;
}

/* ── CARD ── */
.testi-card {
    background:var(--bg-card);
    border:1px solid var(--border);
    border-radius:var(--radius-md);
    padding:1.75rem 1.5rem 1.5rem;
    height:100%;
    position:relative;
    transition:border-color 0.3s, transform 0.3s, box-shadow 0.3s;
    display:flex;
    flex-direction:column;
    gap:0.85rem;
}
.testi-card:hover {
    border-color:var(--accent);
    transform:translateY(-4px);
    box-shadow:0 16px 40px rgba(168,85,247,0.12);
}

.testi-quote {
    font-size:4rem;
    line-height:1;
    color:var(--accent);
    font-family:'Syne',sans-serif;
    font-weight:800;
    margin-bottom:-0.75rem;
    opacity:0.6;
}

.testi-stars {
    color:#F59E0B;
    font-size:0.9rem;
    letter-spacing:0.1em;
}

.testi-text {
    font-size:0.875rem;
    color:var(--text-secondary);
    line-height:1.7;
    flex:1;
}

.testi-user {
    display:flex;
    align-items:center;
    gap:0.85rem;
    border-top:1px solid var(--border);
    padding-top:1rem;
    margin-top:auto;
}

.testi-avatar {
    width:44px;
    height:44px;
    border-radius:50%;
    background:linear-gradient(135deg,var(--accent),var(--accent-dark));
    display:flex;
    align-items:center;
    justify-content:center;
    font-family:'Syne',sans-serif;
    font-size:1.1rem;
    font-weight:800;
    color:#fff;
    flex-shrink:0;
    text-decoration:none;
    transition:box-shadow 0.2s, transform 0.2s;
}
.testi-avatar:hover {
    box-shadow:0 0 0 3px var(--accent);
    transform:scale(1.08);
}

.testi-name {
    font-weight:700;
    font-size:0.875rem;
    color:var(--text-primary);
    margin-bottom:0.2rem;
}
.testi-meta {
    font-size:0.75rem;
    color:var(--text-muted);
}
.testi-box-tag {
    color:var(--accent);
    font-weight:600;
}

/* ── NAV BUTTONS ── */
.testi-nav {
    width:44px;
    height:44px;
    border-radius:50%;
    background:var(--bg-card);
    border:1px solid var(--border);
    color:var(--text-primary);
    font-size:1.1rem;
    cursor:pointer;
    transition:all 0.2s;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
    z-index:2;
}
.testi-nav:hover {
    background:var(--accent);
    border-color:var(--accent);
    color:#fff;
}
.testi-nav:disabled {
    opacity:0.3;
    cursor:not-allowed;
}

/* ── DOTS ── */
.testi-dots {
    display:flex;
    justify-content:center;
    gap:0.5rem;
    margin-top:1.75rem;
}
.testi-dot {
    width:8px;
    height:8px;
    border-radius:50%;
    background:var(--border);
    cursor:pointer;
    transition:all 0.3s;
    border:none;
}
.testi-dot.active {
    background:var(--accent);
    width:24px;
    border-radius:100px;
}

/* ── RESPONSIVE ── */
@media(max-width:900px) {
    .testi-slide { flex:0 0 calc(50% - 0.65rem); }
}
@media(max-width:600px) {
    .testi-slide { flex:0 0 100%; }
    .testi-nav   { display:none; }
}
</style>

<script>
(function() {
    const track    = document.getElementById('testiTrack');
    const viewport = document.getElementById('testiViewport');
    const dotsWrap = document.getElementById('testiDots');
    const btnPrev  = document.getElementById('testiPrev');
    const btnNext  = document.getElementById('testiNext');

    if (!track) return;

    const slides      = Array.from(track.querySelectorAll('.testi-slide'));
    const total       = slides.length;
    let   current     = 0;
    let   autoTimer   = null;
    let   perView     = getPerView();

    const totalGroups = Math.ceil(total / perView);

    // Build dots
    for (let i = 0; i < totalGroups; i++) {
        const dot = document.createElement('button');
        dot.className = 'testi-dot' + (i === 0 ? ' active' : '');
        dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
        dot.addEventListener('click', () => goTo(i));
        dotsWrap.appendChild(dot);
    }

    function getPerView() {
        if (window.innerWidth <= 600) return 1;
        if (window.innerWidth <= 900) return 2;
        return 3;
    }

    function goTo(idx) {
        current = Math.max(0, Math.min(idx, totalGroups - 1));

        // Calculate slide width including gap
        const slideW = slides[0].offsetWidth + 20; // 20 = gap 1.25rem approx
        track.style.transform = `translateX(-${current * perView * slideW}px)`;

        // Update dots
        dotsWrap.querySelectorAll('.testi-dot').forEach((d, i) => {
            d.classList.toggle('active', i === current);
        });

        btnPrev.disabled = current === 0;
        btnNext.disabled = current >= totalGroups - 1;
    }

    function next() { goTo(current < totalGroups - 1 ? current + 1 : 0); }
    function prev() { goTo(current > 0 ? current - 1 : totalGroups - 1); }

    btnNext.addEventListener('click', () => { next(); resetAuto(); });
    btnPrev.addEventListener('click', () => { prev(); resetAuto(); });

    function startAuto() {
        autoTimer = setInterval(next, 4000);
    }
    function resetAuto() {
        clearInterval(autoTimer);
        startAuto();
    }

    // Pause on hover
    viewport.addEventListener('mouseenter', () => clearInterval(autoTimer));
    viewport.addEventListener('mouseleave', startAuto);

    // Touch swipe
    let touchStartX = 0;
    viewport.addEventListener('touchstart', e => touchStartX = e.touches[0].clientX, {passive:true});
    viewport.addEventListener('touchend', e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) { diff > 0 ? next() : prev(); resetAuto(); }
    });

    // Recalculate on resize
    window.addEventListener('resize', () => {
        perView = getPerView();
        goTo(0);
    });

    goTo(0);
    startAuto();
})();
</script>

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