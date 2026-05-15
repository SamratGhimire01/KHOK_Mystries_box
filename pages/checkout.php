<?php
// pages/checkout.php — K HO K Checkout
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();

// Get box from query string
$boxSlug  = sanitize($_GET['box'] ?? '');
$boxes    = BOXES;
$box      = $boxes[$boxSlug] ?? null;

// Default to shadow if invalid
if (!$box) { $boxSlug = 'shadow'; $box = $boxes['shadow']; }

$pageTitle = 'Checkout — ' . $box['name'];
$pageCSS   = 'checkout.css';
$pageJS    = 'checkout.js';

// Pre-fill if logged in
$user = [];
if (isLoggedIn()) {
    $db   = getDB();
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch() ?: [];
}

require_once __DIR__ . '/../components/header.php';
?>

<section class="checkout-section">
    <div class="container">
        <div class="checkout-grid">

            <!-- LEFT: Form -->
            <div class="checkout-form-wrap">
                <h1 class="checkout-title">Complete Your Order</h1>
                <p class="checkout-sub">Fill in your details and proceed to payment.</p>

                <form class="checkout-form" id="checkoutForm">
                    <input type="hidden" name="box_slug" value="<?= e($boxSlug) ?>">

                    <!-- Personal details -->
                    <div class="form-section">
                        <h3 class="form-section-title">Personal Details</h3>
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input class="form-input" type="text" name="customer_name"
                                   value="<?= e($user['full_name'] ?? '') ?>"
                                   placeholder="Your full name" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Phone / WhatsApp</label>
                                <input class="form-input" type="tel" name="phone"
                                       value="<?= e($user['phone'] ?? '') ?>"
                                       placeholder="98XXXXXXXX" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input class="form-input" type="email" name="email"
                                       value="<?= e($user['email'] ?? '') ?>"
                                       placeholder="you@email.com">
                            </div>
                        </div>
                    </div>

                    <!-- Delivery details -->
                    <div class="form-section">
                        <h3 class="form-section-title">Delivery Address</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <select class="form-input form-select" name="city" required>
                                    <option value="" disabled <?= empty($user['city']) ? 'selected' : '' ?>>Select city</option>
                                    <?php foreach (['Kathmandu','Lalitpur','Bhaktapur','Pokhara','Biratnagar','Birgunj','Butwal','Nepalgunj','Dharan','Other'] as $city): ?>
                                    <option <?= ($user['city'] ?? '') === $city ? 'selected' : '' ?>><?= $city ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Street / Area</label>
                                <input class="form-input" type="text" name="address"
                                       value="<?= e($user['address'] ?? '') ?>"
                                       placeholder="Street, area, landmark" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Delivery Notes (optional)</label>
                            <textarea class="form-input form-textarea" name="notes"
                                      placeholder="Any special instructions for delivery..."></textarea>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="form-section">
                        <h3 class="form-section-title">Payment Method</h3>
                        <div class="payment-options">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="esewa" checked>
                                <div class="payment-card">
                                    <span class="payment-icon">💚</span>
                                    <span class="payment-name">eSewa</span>
                                </div>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="fonepay">
                                <div class="payment-card">
                                    <span class="payment-icon">🔵</span>
                                    <span class="payment-name">Fonepay</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div id="checkoutError" class="form-error" style="display:none"></div>

                    <button type="submit" class="btn-primary btn-full btn-checkout" id="checkoutBtn">
                        <span class="btn-text">Proceed to Payment — <?= formatPrice($box['price']) ?></span>
                        <span class="btn-loader" style="display:none">Processing...</span>
                    </button>
                </form>
            </div>

            <!-- RIGHT: Order Summary -->
            <div class="order-summary">
                <div class="os-card glass-card">
                    <h3 class="os-title">Order Summary</h3>

                    <!-- Box display -->
                    <div class="os-box <?= $boxSlug === 'god' ? 'os-box--god' : '' ?>">
                        <div class="os-box-qmark"><?= $boxSlug === 'god' ? '👑' : '?' ?></div>
                        <div class="os-box-info">
                            <p class="os-box-name"><?= e($box['name']) ?></p>
                            <p class="os-box-tagline"><?= e($box['tagline']) ?></p>
                        </div>
                    </div>

                    <div class="os-divider"></div>

                    <div class="os-detail">
                        <span>Products inside</span>
                        <span><?= $box['min'] ?>–<?= $box['max'] ?> items</span>
                    </div>
                    <div class="os-detail">
                        <span>Delivery</span>
                        <span class="os-free">FREE</span>
                    </div>
                    <div class="os-detail">
                        <span>WhatsApp tracking</span>
                        <span>✓ Included</span>
                    </div>

                    <div class="os-divider"></div>

                    <div class="os-total">
                        <span>Total</span>
                        <span class="os-total-price"><?= formatPrice($box['price']) ?></span>
                    </div>

                    <!-- Box switcher -->
                    <div class="os-switch">
                        <p class="os-switch-label">Change box tier:</p>
                        <div class="os-switch-grid">
                            <?php foreach ($boxes as $slug => $b): ?>
                            <a href="<?= APP_URL ?>/checkout?box=<?= $slug ?>"
                               class="os-switch-btn <?= $slug === $boxSlug ? 'os-switch-btn--active' : '' ?> <?= $slug === 'god' ? 'os-switch-btn--god' : '' ?>">
                                <?= e($b['name']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Trust badges -->
                <div class="trust-badges">
                    <div class="trust-badge">🔒 Secure Payment</div>
                    <div class="trust-badge">🚀 Fast Delivery</div>
                    <div class="trust-badge">✅ 100% Genuine</div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../components/footer.php'; ?>