<?php
// pages/track.php — K HO K Order Tracking
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();

$pageTitle = 'Track Order';
$pageCSS   = 'track.css';
$pageJS    = 'track.js';

// If order ref passed in URL, auto-search
$orderRef = sanitize($_GET['ref'] ?? '');

require_once __DIR__ . '/../components/header.php';
?>

<section class="track-section">
    <div class="track-orb"></div>
    <div class="container">

        <!-- Search bar -->
        <div class="track-header">
            <p class="section-eyebrow">Live Tracking</p>
            <h1 class="track-title">Track Your Order</h1>
            <p class="track-sub">Enter your order reference to see real-time delivery status.</p>

            <form class="track-search" id="trackSearchForm">
                <input class="track-input" type="text" id="orderRefInput"
                       name="ref" placeholder="e.g. KHK-A1B2C3-20260514"
                       value="<?= e($orderRef) ?>" required>
                <button type="submit" class="btn-primary track-search-btn">Track →</button>
            </form>
        </div>

        <!-- Results panel (hidden until searched) -->
        <div class="track-result" id="trackResult" style="display:none">

            <!-- Order info -->
            <div class="track-order-card glass-card" id="trackOrderCard"></div>

            <!-- Stepper -->
            <div class="track-stepper glass-card">
                <div class="stepper">

                    <div class="stepper-step" id="step1">
                        <div class="stepper-icon">📦</div>
                        <div class="stepper-line"></div>
                        <div class="stepper-label">
                            <strong>Order Confirmed</strong>
                            <span>Warehouse received your order</span>
                        </div>
                    </div>

                    <div class="stepper-step" id="step2">
                        <div class="stepper-icon">🚚</div>
                        <div class="stepper-line"></div>
                        <div class="stepper-label">
                            <strong>Out for Delivery</strong>
                            <span>Your mystery box is on the way</span>
                        </div>
                    </div>

                    <div class="stepper-step" id="step3">
                        <div class="stepper-icon">✅</div>
                        <div class="stepper-line stepper-line--last"></div>
                        <div class="stepper-label">
                            <strong>Delivered</strong>
                            <span>Package delivered successfully</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Delivery proof -->
            <div class="track-proof glass-card" id="trackProof" style="display:none">
                <h3>📸 Delivery Proof</h3>
                <img id="proofImg" src="" alt="Delivery proof photo" class="proof-img">
                <p id="proofNote" class="proof-note"></p>
            </div>

            <!-- WhatsApp contact -->
            <div class="track-wa glass-card" id="trackWa" style="display:none">
                <p>Need help with your order?</p>
                <a id="waLink" href="#" target="_blank" class="btn-wa">
                    💬 Contact via WhatsApp
                </a>
            </div>

        </div>

        <!-- Empty state -->
        <div class="track-empty" id="trackEmpty" style="display:none">
            <div class="track-empty-icon">🔍</div>
            <p>No order found with that reference.<br>Please check and try again.</p>
        </div>

        <!-- Loading state -->
        <div class="track-loading" id="trackLoading" style="display:none">
            <div class="track-spinner"></div>
            <p>Searching for your order...</p>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/../components/footer.php'; ?>