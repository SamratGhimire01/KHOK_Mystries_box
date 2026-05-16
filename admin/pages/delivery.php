<?php
// admin/pages/delivery.php — K HO K Admin Delivery
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();
requireAdmin();

$pageTitle = 'Admin — Delivery';
$db = getDB();

// Handle delivery status update + proof upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_delivery'])) {
    $orderId     = (int)$_POST['order_id'];
    $status      = sanitize($_POST['delivery_status']);
    $note        = sanitize($_POST['delivery_note'] ?? '');
    $allowed     = ['confirmed', 'in_transit', 'delivered'];

    if (in_array($status, $allowed)) {
        $proofImage = null;

        // Handle proof photo upload
        if (!empty($_FILES['proof_image']['name'])) {
            $uploadDir = __DIR__ . '/../../uploads/delivery_proof/';
            $ext       = strtolower(pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg','jpeg','png','webp'];
            if (in_array($ext, $allowed_ext) && $_FILES['proof_image']['size'] < 5000000) {
                $filename   = 'proof_' . $orderId . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['proof_image']['tmp_name'], $uploadDir . $filename);
                $proofImage = $filename;
            }
        }

        // Update delivery_tracking
        $sql    = 'UPDATE delivery_tracking SET status=?, delivery_note=?';
        $params = [$status, $note];
        if ($proofImage) { $sql .= ', proof_image=?'; $params[] = $proofImage; }
        if ($status === 'delivered') { $sql .= ', delivered_at=NOW()'; }
        $sql .= ' WHERE order_id=?';
        $params[] = $orderId;
        $db->prepare($sql)->execute($params);

        // Sync order status
        $orderStatus = match($status) {
            'in_transit' => 'shipped',
            'delivered'  => 'delivered',
            default      => 'confirmed'
        };
        $db->prepare('UPDATE orders SET order_status=? WHERE id=?')
           ->execute([$orderStatus, $orderId]);

        setFlash('success', 'Delivery status updated successfully.');
    }
    header('Location: ' . APP_URL . '/admin/delivery');
    exit;
}

// Filters
$statusFilter = sanitize($_GET['status'] ?? '');
$where  = $statusFilter ? 'WHERE dt.status = ?' : '';
$params = $statusFilter ? [$statusFilter] : [];

$deliveries = $db->prepare("
    SELECT o.id AS order_id, o.order_ref, o.customer_name, o.phone,
           o.city, o.address, o.total_amount, o.order_status,
           b.name AS box_name,
           dt.status AS delivery_status, dt.proof_image,
           dt.delivery_note, dt.estimated_date, dt.delivered_at,
           dt.updated_at
    FROM delivery_tracking dt
    JOIN orders o ON o.id = dt.order_id
    JOIN boxes b  ON b.id = o.box_id
    $where
    ORDER BY dt.updated_at DESC
");
$deliveries->execute($params);
$deliveryList = $deliveries->fetchAll();

require_once __DIR__ . '/../../components/admin_header.php';
?>
<div class="admin-layout">
<?php require_once __DIR__ . '/../../components/admin_sidebar.php'; ?>
<main class="admin-main">

    <div class="admin-topbar">
        <div>
            <h1 class="admin-page-title">Delivery Management</h1>
            <p class="admin-page-sub"><?= count($deliveryList) ?> deliveries</p>
        </div>
    </div>

    <!-- Filter tabs -->
    <div class="delivery-tabs">
        <?php
        $tabs = ['' => 'All', 'confirmed' => 'Confirmed', 'in_transit' => 'In Transit', 'delivered' => 'Delivered'];
        foreach ($tabs as $val => $label):
        ?>
        <a href="?status=<?= $val ?>"
           class="delivery-tab <?= $statusFilter === $val ? 'delivery-tab--active' : '' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Delivery cards -->
    <div class="delivery-list">
    <?php if (empty($deliveryList)): ?>
        <div class="glass-card" style="padding:2rem;text-align:center;color:var(--text-muted)">
            No deliveries found.
        </div>
    <?php else: ?>
    <?php foreach ($deliveryList as $d): ?>
    <div class="delivery-card glass-card delivery-card--<?= e($d['delivery_status']) ?>">
        <div class="dc-top">
            <div class="dc-info">
                <p class="dc-ref"><?= e($d['order_ref']) ?></p>
                <p class="dc-name"><?= e($d['customer_name']) ?></p>
                <p class="dc-meta">
                    📦 <?= e($d['box_name']) ?> &nbsp;|&nbsp;
                    💰 <?= formatPrice($d['total_amount']) ?> &nbsp;|&nbsp;
                    📍 <?= e($d['city']) ?>, <?= e($d['address']) ?>
                </p>
                <p class="dc-meta">
                    📱
                    <a href="https://wa.me/977<?= e($d['phone']) ?>?text=<?= urlencode('Hi ' . $d['customer_name'] . '! Your K HO K order ' . $d['order_ref'] . ' update:') ?>"
                       target="_blank" class="wa-link">
                        <?= e($d['phone']) ?> — WhatsApp
                    </a>
                </p>
                <?php if ($d['delivery_note']): ?>
                <p class="dc-note">📝 <?= e($d['delivery_note']) ?></p>
                <?php endif; ?>
            </div>
            <div class="dc-status">
                <span class="delivery-status-badge delivery-status-badge--<?= e($d['delivery_status']) ?>">
                    <?= ucfirst(str_replace('_', ' ', $d['delivery_status'])) ?>
                </span>
                <?php if ($d['estimated_date']): ?>
                <p class="dc-eta">Est: <?= date('d M Y', strtotime($d['estimated_date'])) ?></p>
                <?php endif; ?>
                <?php if ($d['delivered_at']): ?>
                <p class="dc-eta" style="color:var(--success)">
                    Delivered: <?= date('d M Y H:i', strtotime($d['delivered_at'])) ?>
                </p>
                <?php endif; ?>
                <?php if ($d['proof_image']): ?>
                <a href="<?= APP_URL ?>/uploads/delivery_proof/<?= e($d['proof_image']) ?>"
                   target="_blank" class="proof-link">📸 View Proof</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Update form -->
        <div class="dc-update">
            <form method="POST" action="<?= APP_URL ?>/admin/delivery"
                  enctype="multipart/form-data" class="dc-form">
                <input type="hidden" name="order_id" value="<?= $d['order_id'] ?>">
                <select class="form-input form-select" name="delivery_status">
                    <option value="confirmed"  <?= $d['delivery_status']==='confirmed'  ? 'selected':'' ?>>Confirmed</option>
                    <option value="in_transit" <?= $d['delivery_status']==='in_transit' ? 'selected':'' ?>>In Transit</option>
                    <option value="delivered"  <?= $d['delivery_status']==='delivered'  ? 'selected':'' ?>>Delivered</option>
                </select>
                <input class="form-input" type="text" name="delivery_note"
                       placeholder="Delivery note..." value="<?= e($d['delivery_note']) ?>">
                <label class="proof-upload-label">
                    📸 Proof Photo
                    <input type="file" name="proof_image" accept="image/*" class="proof-upload-input">
                </label>
                <button type="submit" name="update_delivery" class="btn-primary dc-save-btn">
                    Update
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
    </div>

</main>
</div>
<?php require_once __DIR__ . '/../../components/admin_footer.php'; ?>