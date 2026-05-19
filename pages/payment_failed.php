<?php
// pages/payment_failed.php — K HO K Payment Failed
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/payment.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();

$orderRef = sanitize($_GET['ref'] ?? '');
$method   = sanitize($_GET['method'] ?? '');

// Build retry URL
$retryUrl = $orderRef
    ? APP_URL . '/api/payment/' . ($method ?: 'esewa') . '_init.php?order=' . urlencode($orderRef)
    : APP_URL . '/boxes';

$pageTitle = 'Payment Failed';
$pageCSS   = 'payment.css';
require_once __DIR__ . '/../components/header.php';
?>

<section class="payment-section">
    <div class="payment-orb payment-orb--fail"></div>
    <div class="container">
        <div class="payment-card glass-card">

            <div class="payment-icon payment-icon--fail">❌</div>
            <h1 class="payment-title">Payment Failed</h1>
            <p class="payment-sub">
                Something went wrong with your payment. Your order has not been charged.
            </p>

            <?php if ($orderRef): ?>
            <div class="payment-order-box">
                <div class="pob-row">
                    <span>Order Reference</span>
                    <strong><?= e($orderRef) ?></strong>
                </div>
                <div class="pob-row">
                    <span>Status</span>
                    <strong style="color:var(--error)">Payment Failed</strong>
                </div>
            </div>
            <?php endif; ?>

            <p class="payment-help">
                Your mystery box selection is still saved. You can retry the payment below
                or contact us on WhatsApp for help.
            </p>

            <div class="payment-actions">
                <a href="<?= $retryUrl ?>" class="btn-primary">🔄 Retry Payment</a>
                <a href="<?= APP_URL ?>/boxes" class="btn-outline">← Choose Another Box</a>
                <a href="https://wa.me/<?= WHATSAPP_BUSINESS_NUMBER ?>?text=<?= urlencode('Hi K HO K! I had a payment issue with order: ' . $orderRef) ?>"
                   target="_blank" class="btn-wa-success">
                    💬 Contact Support
                </a>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../components/footer.php'; ?>