<?php
// admin/pages/order_detail.php — K HO K Admin Order Detail
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();
requireAdmin();

$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) { redirect('/admin/orders'); }

$db   = getDB();
$order = $db->prepare('
    SELECT o.*, b.name AS box_name, b.slug AS box_slug,
           u.full_name AS user_name, u.email AS user_email
    FROM orders o
    JOIN boxes b ON b.id = o.box_id
    LEFT JOIN users u ON u.id = o.user_id
    WHERE o.id = ?
');
$order->execute([$orderId]);
$order = $order->fetch();
if (!$order) { redirect('/admin/orders'); }

// Order items (what was inside the box)
$items = $db->prepare('
    SELECT p.name, p.brand, p.category, p.price, p.rarity, oi.quantity
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
');
$items->execute([$orderId]);
$orderItems = $items->fetchAll();

// Delivery tracking
$tracking = $db->prepare('SELECT * FROM delivery_tracking WHERE order_id = ?');
$tracking->execute([$orderId]);
$tracking = $tracking->fetch();

$pageTitle = 'Order ' . $order['order_ref'];
require_once __DIR__ . '/../../components/admin_header.php';
?>
<div class="admin-layout">
<?php require_once __DIR__ . '/../../components/admin_sidebar.php'; ?>
<main class="admin-main">

    <div class="admin-topbar">
        <div>
            <a href="<?= APP_URL ?>/admin/orders" class="back-link">← Back to Orders</a>
            <h1 class="admin-page-title"><?= e($order['order_ref']) ?></h1>
        </div>
        <!-- WhatsApp contact button -->
        <a href="https://wa.me/977<?= e($order['phone']) ?>?text=<?= urlencode('Hi ' . $order['customer_name'] . '! Regarding your K HO K order ' . $order['order_ref'] . ':') ?>"
           target="_blank" class="btn-wa-admin">
            💬 WhatsApp Customer
        </a>
    </div>

    <div class="order-detail-grid">

        <!-- LEFT: Customer + Order info -->
        <div class="od-left">

            <!-- Customer info -->
            <div class="glass-card od-card">
                <h3 class="od-card-title">Customer Details</h3>
                <div class="od-row"><span>Name</span><strong><?= e($order['customer_name']) ?></strong></div>
                <div class="od-row"><span>Phone</span>
                    <a href="https://wa.me/977<?= e($order['phone']) ?>" target="_blank" class="wa-link">
                        <?= e($order['phone']) ?>
                    </a>
                </div>
                <?php if ($order['user_email']): ?>
                <div class="od-row"><span>Email</span><strong><?= e($order['user_email']) ?></strong></div>
                <?php endif; ?>
                <div class="od-row"><span>City</span><strong><?= e($order['city']) ?></strong></div>
                <div class="od-row"><span>Address</span><strong><?= e($order['address']) ?></strong></div>
                <?php if ($order['notes']): ?>
                <div class="od-row"><span>Notes</span><strong><?= e($order['notes']) ?></strong></div>
                <?php endif; ?>
            </div>

            <!-- Order info -->
            <div class="glass-card od-card">
                <h3 class="od-card-title">Order Info</h3>
                <div class="od-row"><span>Box</span><strong><?= e($order['box_name']) ?></strong></div>
                <div class="od-row"><span>Amount</span><strong><?= formatPrice($order['total_amount']) ?></strong></div>
                <div class="od-row"><span>Payment</span>
                    <span class="pay-badge pay-badge--<?= e($order['payment_status']) ?>">
                        <?= ucfirst($order['payment_method']) ?> — <?= ucfirst($order['payment_status']) ?>
                    </span>
                </div>
                <div class="od-row"><span>Order Status</span>
                    <span class="status-badge status-badge--<?= e($order['order_status']) ?>">
                        <?= ucfirst($order['order_status']) ?>
                    </span>
                </div>
                <div class="od-row"><span>Placed</span>
                    <strong><?= date('d M Y H:i', strtotime($order['created_at'])) ?></strong>
                </div>
            </div>

            <!-- Delivery info -->
            <?php if ($tracking): ?>
            <div class="glass-card od-card">
                <h3 class="od-card-title">Delivery Status</h3>
                <div class="od-row"><span>Status</span>
                    <span class="delivery-status-badge delivery-status-badge--<?= e($tracking['status']) ?>">
                        <?= ucfirst(str_replace('_',' ',$tracking['status'])) ?>
                    </span>
                </div>
                <?php if ($tracking['estimated_date']): ?>
                <div class="od-row"><span>Est. Delivery</span>
                    <strong><?= date('d M Y', strtotime($tracking['estimated_date'])) ?></strong>
                </div>
                <?php endif; ?>
                <?php if ($tracking['delivered_at']): ?>
                <div class="od-row"><span>Delivered At</span>
                    <strong style="color:var(--success)"><?= date('d M Y H:i', strtotime($tracking['delivered_at'])) ?></strong>
                </div>
                <?php endif; ?>
                <?php if ($tracking['delivery_note']): ?>
                <div class="od-row"><span>Note</span><strong><?= e($tracking['delivery_note']) ?></strong></div>
                <?php endif; ?>
                <?php if ($tracking['proof_image']): ?>
                <div class="od-row">
                    <span>Proof</span>
                    <a href="<?= APP_URL ?>/uploads/delivery_proof/<?= e($tracking['proof_image']) ?>"
                       target="_blank" class="proof-link">📸 View Photo</a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Products inside box -->
        <div class="od-right">
            <div class="glass-card od-card">
                <h3 class="od-card-title">📦 Products Inside This Box</h3>
                <p class="od-pack-note">Pack these items for the customer:</p>
                <?php if (empty($orderItems)): ?>
                <p style="color:var(--text-muted);font-size:.875rem">
                    Products not yet assigned.
                </p>
                <?php else: ?>
                <div class="pack-list">
                    <?php foreach ($orderItems as $item): ?>
                    <div class="pack-item">
                        <div class="pack-item-check">☐</div>
                        <div class="pack-item-info">
                            <p class="pack-item-name"><?= e($item['name']) ?></p>
                            <p class="pack-item-meta">
                                <?= e($item['brand']) ?> &nbsp;·&nbsp;
                                <?= e($item['category']) ?> &nbsp;·&nbsp;
                                <?= formatPrice($item['price']) ?>
                            </p>
                        </div>
                        <span class="rarity-pill rarity-pill--<?= e($item['rarity']) ?>">
                            <?= ucfirst(str_replace('_',' ',$item['rarity'])) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="pack-total">
                    <span>Total items: <?= count($orderItems) ?></span>
                    <span>Retail value: <?= formatPrice(array_sum(array_column($orderItems, 'price'))) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</main>
</div>
<?php require_once __DIR__ . '/../../components/admin_footer.php'; ?>