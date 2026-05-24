<?php
// pages/track.php — K HO K Order Tracking v2
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/payment.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();

$pageTitle = 'Track Order';
$pageCSS   = 'track.css';
$pageJS    = 'track.js';

$orderRef = sanitize($_GET['ref'] ?? '');

require_once __DIR__ . '/../components/header.php';
?>

<section class="track-section">
    <div class="track-orb"></div>
    <div class="container">

        <!-- Search -->
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

        <!-- Loading -->
        <div class="track-loading" id="trackLoading" style="display:none">
            <div class="track-spinner"></div>
            <p>Searching for your order...</p>
        </div>

        <!-- Empty -->
        <div class="track-empty" id="trackEmpty" style="display:none">
            <div class="track-empty-icon">🔍</div>
            <p>No order found with that reference.<br>Please check and try again.</p>
        </div>

        <!-- Result -->
        <div id="trackResult" style="display:none">

            <!-- Order info card -->
            <div class="track-order-info glass-card" id="trackOrderCard"></div>

            <!-- Stepper -->
            <div class="track-stepper-card glass-card">
                <h3 class="track-card-title">Delivery Progress</h3>
                <div class="stepper">

                    <div class="stepper-step" id="step1">
                        <div class="stepper-icon-wrap">
                            <div class="stepper-icon">📦</div>
                            <div class="stepper-line"></div>
                        </div>
                        <div class="stepper-content">
                            <p class="stepper-label">Order Confirmed</p>
                            <p class="stepper-desc">Warehouse received your order</p>
                        </div>
                    </div>

                    <div class="stepper-step" id="step2">
                        <div class="stepper-icon-wrap">
                            <div class="stepper-icon">🚚</div>
                            <div class="stepper-line"></div>
                        </div>
                        <div class="stepper-content">
                            <p class="stepper-label">Out for Delivery</p>
                            <p class="stepper-desc">Your mystery box is on the way</p>
                        </div>
                    </div>

                    <div class="stepper-step" id="step3">
                        <div class="stepper-icon-wrap">
                            <div class="stepper-icon">✅</div>
                            <div class="stepper-line stepper-line--last"></div>
                        </div>
                        <div class="stepper-content">
                            <p class="stepper-label">Delivered</p>
                            <p class="stepper-desc">Package delivered successfully</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Delivery proof -->
            <div class="track-proof glass-card" id="trackProof" style="display:none">
                <h3 class="track-card-title">📸 Delivery Proof</h3>
                <img id="proofImg" src="" alt="Delivery proof" class="proof-img">
                <p id="proofNote" class="proof-note"></p>
            </div>

            <!-- WhatsApp contact -->
            <div class="track-wa glass-card" id="trackWa">
                <div class="wa-info">
                    <p class="wa-title">Need help with your order?</p>
                    <p class="wa-sub">Our team is available on WhatsApp</p>
                </div>
                <a id="waLink" href="#" target="_blank" class="btn-wa">
                    💬 Contact via WhatsApp
                </a>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../components/footer.php'; ?>