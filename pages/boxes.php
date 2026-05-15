<?php
// pages/boxes.php — K HO K Boxes Page
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();

$pageTitle = 'Mystery Boxes';
$pageCSS   = 'boxes.css';
$pageJS    = 'boxes.js';

require_once __DIR__ . '/../components/header.php';
?>

<!-- ═══ PAGE HERO ═══ -->
<section class="page-hero">
    <div class="page-hero-orb"></div>
    <div class="container">
        <p class="section-eyebrow">Choose Your Fate</p>
        <h1 class="page-hero-title">Mystery Boxes</h1>
        <p class="page-hero-sub">Six tiers. Infinite possibilities. One guaranteed surprise.</p>
    </div>
</section>

<!-- ═══ BOXES LISTING ═══ -->
<section class="section boxes-section">
    <div class="container">
        <div class="boxes-list">

            <!-- SHADOW BOX -->
            <div class="box-listing-card" data-box="shadow">
                <div class="blc-left">
                    <div class="blc-badge blc-badge--shadow">SHADOW</div>
                    <h2 class="blc-name">Shadow Box</h2>
                    <p class="blc-tagline">रहस्यको सुरुवात</p>
                    <p class="blc-desc">The beginning of your mystery journey. Perfect for first-timers. Get 1–2 surprise products from our entry-level pool including earbuds, accessories, and gaming gear.</p>
                    <div class="blc-meta">
                        <span class="blc-meta-item">📦 1–2 Products</span>
                        <span class="blc-meta-item">🎲 High chance of earbuds &amp; accessories</span>
                        <span class="blc-meta-item">⚡ Rare chance of gaming gear</span>
                    </div>
                    <div class="blc-actions">
                        <a href="<?= APP_URL ?>/checkout?box=shadow" class="btn-primary">
                            Open Shadow Box — <?= formatPrice(999) ?>
                        </a>
                    </div>
                </div>
                <div class="blc-right">
                    <div class="blc-box-visual blc-box-visual--shadow">
                        <span class="blc-qmark">?</span>
                        <div class="blc-price-tag"><?= formatPrice(999) ?></div>
                    </div>
                    <div class="blc-rarity-bar">
                        <div class="rarity-item">
                            <span class="rarity-label">Common</span>
                            <div class="rarity-track"><div class="rarity-fill" style="width:92%"></div></div>
                            <span class="rarity-pct">92%</span>
                        </div>
                        <div class="rarity-item">
                            <span class="rarity-label">Rare</span>
                            <div class="rarity-track"><div class="rarity-fill rarity-fill--rare" style="width:7%"></div></div>
                            <span class="rarity-pct">7%</span>
                        </div>
                        <div class="rarity-item">
                            <span class="rarity-label">Ultra Rare</span>
                            <div class="rarity-track"><div class="rarity-fill rarity-fill--ultra" style="width:1%"></div></div>
                            <span class="rarity-pct">1%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CORE BOX -->
            <div class="box-listing-card" data-box="core">
                <div class="blc-left">
                    <div class="blc-badge blc-badge--core">CORE</div>
                    <h2 class="blc-name">Core Box</h2>
                    <p class="blc-tagline">असली खेल सुरु हुन्छ</p>
                    <p class="blc-desc">The real game begins here. Expect gaming mice, premium earbuds, mechanical keyboards and JBL speakers. 1–3 products packed with value.</p>
                    <div class="blc-meta">
                        <span class="blc-meta-item">📦 1–3 Products</span>
                        <span class="blc-meta-item">🎲 Gaming mice, premium earbuds</span>
                        <span class="blc-meta-item">⚡ Chance of mechanical keyboard</span>
                    </div>
                    <div class="blc-actions">
                        <a href="<?= APP_URL ?>/checkout?box=core" class="btn-primary">
                            Open Core Box — <?= formatPrice(2999) ?>
                        </a>
                    </div>
                </div>
                <div class="blc-right">
                    <div class="blc-box-visual blc-box-visual--core">
                        <span class="blc-qmark">?</span>
                        <div class="blc-price-tag"><?= formatPrice(2999) ?></div>
                    </div>
                    <div class="blc-rarity-bar">
                        <div class="rarity-item">
                            <span class="rarity-label">Common</span>
                            <div class="rarity-track"><div class="rarity-fill" style="width:78%"></div></div>
                            <span class="rarity-pct">78%</span>
                        </div>
                        <div class="rarity-item">
                            <span class="rarity-label">Rare</span>
                            <div class="rarity-track"><div class="rarity-fill rarity-fill--rare" style="width:19%"></div></div>
                            <span class="rarity-pct">19%</span>
                        </div>
                        <div class="rarity-item">
                            <span class="rarity-label">Ultra Rare</span>
                            <div class="rarity-track"><div class="rarity-fill rarity-fill--ultra" style="width:3%"></div></div>
                            <span class="rarity-pct">3%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PULSE BOX -->
            <div class="box-listing-card" data-box="pulse">
                <div class="blc-left">
                    <div class="blc-badge blc-badge--pulse">PULSE</div>
                    <h2 class="blc-name">Pulse Box</h2>
                    <p class="blc-tagline">धड्कन बढ्छ</p>
                    <p class="blc-desc">Your pulse rises with every unboxing. HyperX headsets, Razer mice, Logitech webcams and smartwatches. 2–4 products, serious value.</p>
                    <div class="blc-meta">
                        <span class="blc-meta-item">📦 2–4 Products</span>
                        <span class="blc-meta-item">🎲 HyperX, Razer, Logitech gear</span>
                        <span class="blc-meta-item">⚡ Chance of smartwatch</span>
                    </div>
                    <div class="blc-actions">
                        <a href="<?= APP_URL ?>/checkout?box=pulse" class="btn-primary">
                            Open Pulse Box — <?= formatPrice(4999) ?>
                        </a>
                    </div>
                </div>
                <div class="blc-right">
                    <div class="blc-box-visual blc-box-visual--pulse">
                        <span class="blc-qmark">?</span>
                        <div class="blc-price-tag"><?= formatPrice(4999) ?></div>
                    </div>
                    <div class="blc-rarity-bar">
                        <div class="rarity-item">
                            <span class="rarity-label">Common</span>
                            <div class="rarity-track"><div class="rarity-fill" style="width:62%"></div></div>
                            <span class="rarity-pct">62%</span>
                        </div>
                        <div class="rarity-item">
                            <span class="rarity-label">Rare</span>
                            <div class="rarity-track"><div class="rarity-fill rarity-fill--rare" style="width:30%"></div></div>
                            <span class="rarity-pct">30%</span>
                        </div>
                        <div class="rarity-item">
                            <span class="rarity-label">Ultra Rare</span>
                            <div class="rarity-track"><div class="rarity-fill rarity-fill--ultra" style="width:8%"></div></div>
                            <span class="rarity-pct">8%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ELITE BOX -->
            <div class="box-listing-card" data-box="elite">
                <div class="blc-left">
                    <div class="blc-badge blc-badge--elite">ELITE</div>
                    <h2 class="blc-name">Elite Box</h2>
                    <p class="blc-tagline">छनोट भएकाहरूको लागि</p>
                    <p class="blc-desc">For the chosen ones. Apple AirPods, JBL Flip speakers, Xbox controllers, Yamaha guitars. 2–5 premium products selected for those who mean business.</p>
                    <div class="blc-meta">
                        <span class="blc-meta-item">📦 2–5 Products</span>
                        <span class="blc-meta-item">🎲 Apple AirPods, JBL Flip</span>
                        <span class="blc-meta-item">⚡ Chance of Yamaha guitar</span>
                    </div>
                    <div class="blc-actions">
                        <a href="<?= APP_URL ?>/checkout?box=elite" class="btn-primary">
                            Open Elite Box — <?= formatPrice(9999) ?>
                        </a>
                    </div>
                </div>
                <div class="blc-right">
                    <div class="blc-box-visual blc-box-visual--elite">
                        <span class="blc-qmark">?</span>
                        <div class="blc-price-tag"><?= formatPrice(9999) ?></div>
                    </div>
                    <div class="blc-rarity-bar">
                        <div class="rarity-item">
                            <span class="rarity-label">Common</span>
                            <div class="rarity-track"><div class="rarity-fill" style="width:42%"></div></div>
                            <span class="rarity-pct">42%</span>
                        </div>
                        <div class="rarity-item">
                            <span class="rarity-label">Rare</span>
                            <div class="rarity-track"><div class="rarity-fill rarity-fill--rare" style="width:43%"></div></div>
                            <span class="rarity-pct">43%</span>
                        </div>
                        <div class="rarity-item">
                            <span class="rarity-label">Ultra Rare</span>
                            <div class="rarity-track"><div class="rarity-fill rarity-fill--ultra" style="width:15%"></div></div>
                            <span class="rarity-pct">15%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PHANTOM BOX -->
            <div class="box-listing-card" data-box="phantom">
                <div class="blc-left">
                    <div class="blc-badge blc-badge--phantom">PHANTOM</div>
                    <h2 class="blc-name">Phantom Box</h2>
                    <p class="blc-tagline">सपनाको छेउमा</p>
                    <p class="blc-desc">On the edge of your dream. Apple Watch SE, iPad, DJI Drone, GoPro, Nintendo Switch Lite. 3–6 products. This is where legends are made.</p>
                    <div class="blc-meta">
                        <span class="blc-meta-item">📦 3–6 Products</span>
                        <span class="blc-meta-item">🎲 Apple Watch, iPad, GoPro</span>
                        <span class="blc-meta-item">⚡ Chance of DJI Drone</span>
                    </div>
                    <div class="blc-actions">
                        <a href="<?= APP_URL ?>/checkout?box=phantom" class="btn-primary">
                            Open Phantom Box — <?= formatPrice(24999) ?>
                        </a>
                    </div>
                </div>
                <div class="blc-right">
                    <div class="blc-box-visual blc-box-visual--phantom">
                        <span class="blc-qmark">?</span>
                        <div class="blc-price-tag"><?= formatPrice(24999) ?></div>
                    </div>
                    <div class="blc-rarity-bar">
                        <div class="rarity-item">
                            <span class="rarity-label">Common</span>
                            <div class="rarity-track"><div class="rarity-fill" style="width:18%"></div></div>
                            <span class="rarity-pct">18%</span>
                        </div>
                        <div class="rarity-item">
                            <span class="rarity-label">Rare</span>
                            <div class="rarity-track"><div class="rarity-fill rarity-fill--rare" style="width:47%"></div></div>
                            <span class="rarity-pct">47%</span>
                        </div>
                        <div class="rarity-item">
                            <span class="rarity-label">Ultra Rare</span>
                            <div class="rarity-track"><div class="rarity-fill rarity-fill--ultra" style="width:35%"></div></div>
                            <span class="rarity-pct">35%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GOD BOX -->
            <div class="box-listing-card box-listing-card--god" data-box="god">
                <div class="blc-god-crown">👑</div>
                <div class="blc-left">
                    <div class="blc-badge blc-badge--god">LEGENDARY</div>
                    <h2 class="blc-name blc-name--god">GOD BOX</h2>
                    <p class="blc-tagline blc-tagline--god">देवताको छनोट</p>
                    <p class="blc-desc">Chosen by the gods. 5–10 products from our entire premium pool. iPhone 13 or iPhone 14 legendary trigger. Apple Watch, iPad, DJI Drone guaranteed pool. This is the ultimate mystery experience.</p>
                    <div class="blc-meta">
                        <span class="blc-meta-item">📦 5–10 Products</span>
                        <span class="blc-meta-item">🎲 Full premium product pool</span>
                        <span class="blc-meta-item">👑 Legendary iPhone trigger active</span>
                    </div>
                    <div class="blc-actions">
                        <a href="<?= APP_URL ?>/checkout?box=god" class="btn-god">
                            👑 Open GOD BOX — <?= formatPrice(99999) ?>
                        </a>
                    </div>
                </div>
                <div class="blc-right">
                    <div class="blc-box-visual blc-box-visual--god">
                        <span class="blc-qmark blc-qmark--god">👑</span>
                        <div class="blc-price-tag blc-price-tag--god"><?= formatPrice(99999) ?></div>
                    </div>
                    <div class="blc-rarity-bar">
                        <div class="rarity-item">
                            <span class="rarity-label">Common</span>
                            <div class="rarity-track"><div class="rarity-fill" style="width:10%"></div></div>
                            <span class="rarity-pct">10%</span>
                        </div>
                        <div class="rarity-item">
                            <span class="rarity-label">Rare</span>
                            <div class="rarity-track"><div class="rarity-fill rarity-fill--rare" style="width:40%"></div></div>
                            <span class="rarity-pct">40%</span>
                        </div>
                        <div class="rarity-item">
                            <span class="rarity-label">Ultra + Legendary</span>
                            <div class="rarity-track"><div class="rarity-fill rarity-fill--legend" style="width:50%"></div></div>
                            <span class="rarity-pct">50%</span>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- end boxes-list -->
    </div>
</section>

<!-- ═══ FAQ ═══ -->
<section class="section faq-section">
    <div class="container">
        <div class="section-header">
            <p class="section-eyebrow">Questions</p>
            <h2 class="section-title">Frequently Asked</h2>
        </div>
        <div class="faq-list">
            <div class="faq-item">
                <button class="faq-q">Are the products genuine? <span class="faq-icon">+</span></button>
                <div class="faq-a">Yes. Every product is 100% genuine from verified Nepal market suppliers. No imitations, no rebranded fakes.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q">Can I choose what I get? <span class="faq-icon">+</span></button>
                <div class="faq-a">No — that's the mystery! The weighted probability engine selects your products based on your box tier. Higher tier = better odds of premium products.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q">How does delivery work? <span class="faq-icon">+</span></button>
                <div class="faq-a">We deliver across Nepal. You'll receive WhatsApp updates at every stage — confirmed, shipped, and delivered with proof photo.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q">What payment methods are accepted? <span class="faq-icon">+</span></button>
                <div class="faq-a">We accept eSewa and Fonepay. Both are instant and secure.</div>
            </div>
            <div class="faq-item">
                <button class="faq-q">Can I return or exchange products? <span class="faq-icon">+</span></button>
                <div class="faq-a">All sales are final due to the mystery nature of the platform. However, if a product arrives damaged or defective, contact us immediately via WhatsApp.</div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../components/footer.php'; ?>