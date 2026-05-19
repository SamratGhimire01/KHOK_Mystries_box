<?php
// pages/payment_success.php — K HO K Payment Success
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/payment.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();

$orderRef = sanitize($_GET['ref'] ?? '');
$order    = null;
$items    = [];

if ($orderRef) {
    $db   = getDB();
    $stmt = $db->prepare('
        SELECT o.*, b.name AS box_name, b.slug AS box_slug
        FROM orders o JOIN boxes b ON b.id = o.box_id
        WHERE o.order_ref = ?
    ');
    $stmt->execute([$orderRef]);
    $order = $stmt->fetch();

    if ($order) {
        // Fetch order items
        $itemStmt = $db->prepare('
            SELECT p.name, p.brand, p.rarity
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = ?
        ');
        $itemStmt->execute([$order['id']]);
        $items = $itemStmt->fetchAll();
    }
}

// Build WhatsApp message for customer notification
$waMessage = '';
if ($order) {
    $waMessage = urlencode(
        "🎉 *K HO K Order Confirmed!*\n\n" .
        "Order Ref: *{$order['order_ref']}*\n" .
        "Box: *{$order['box_name']}*\n" .
        "Amount: *Rs. " . number_format($order['total_amount']) . "*\n" .
        "Status: *Confirmed ✅*\n\n" .
        "Track your order at: " . APP_URL . "/track?ref={$order['order_ref']}\n\n" .
        "Thank you for choosing K HO K! 📦"
    );
}

$waLink = 'https://wa.me/' . WHATSAPP_BUSINESS_NUMBER . '?text=' . $waMessage;

$pageTitle = 'Order Confirmed!';
$pageCSS   = 'payment.css';
require_once __DIR__ . '/../components/header.php';
?>

<section class="payment-section">
    <div class="payment-orb payment-orb--success"></div>
    <div class="container">
        <div class="payment-card glass-card">

            <!-- Success icon -->
            <div class="payment-icon payment-icon--success">✅</div>
            <h1 class="payment-title">Payment Confirmed!</h1>
            <p class="payment-sub">Your mystery box is being prepared right now.</p>

            <?php if ($order): ?>

            <!-- Order summary -->
            <div class="payment-order-box">
                <div class="pob-row">
                    <span>Order Reference</span>
                    <strong><?= e($order['order_ref']) ?></strong>
                </div>
                <div class="pob-row">
                    <span>Box</span>
                    <strong><?= e($order['box_name']) ?></strong>
                </div>
                <div class="pob-row">
                    <span>Amount Paid</span>
                    <strong style="color:var(--success)"><?= formatPrice($order['total_amount']) ?></strong>
                </div>
                <div class="pob-row">
                    <span>Payment Method</span>
                    <strong><?= strtoupper(e($order['payment_method'])) ?></strong>
                </div>
                <div class="pob-row">
                    <span>Delivery to</span>
                    <strong><?= e($order['city']) ?> — <?= e($order['address']) ?></strong>
                </div>
            </div>

            <!-- Delivery steps -->
            <div class="payment-steps">
                <div class="ps-step ps-step--done">
                    <div class="ps-icon">✅</div>
                    <p>Order Confirmed</p>
                </div>
                <div class="ps-connector"></div>
                <div class="ps-step">
                    <div class="ps-icon">📦</div>
                    <p>Packing</p>
                </div>
                <div class="ps-connector"></div>
                <div class="ps-step">
                    <div class="ps-icon">🚚</div>
                    <p>Shipped</p>
                </div>
                <div class="ps-connector"></div>
                <div class="ps-step">
                    <div class="ps-icon">🎉</div>
                    <p>Delivered</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="payment-actions">
                <a href="<?= APP_URL ?>/track?ref=<?= e($order['order_ref']) ?>"
                   class="btn-primary">
                    📍 Track My Order
                </a>
                <a href="<?= $waLink ?>" target="_blank" class="btn-wa-success">
                    💬 Get Updates on WhatsApp
                </a>
            </div>

            <?php else: ?>
            <div class="payment-actions">
                <a href="<?= APP_URL ?>/" class="btn-primary">Go Home</a>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../components/footer.php'; ?>