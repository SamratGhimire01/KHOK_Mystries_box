<?php
// admin/pages/orders.php — K HO K Admin Orders v2
// Click order row → expands to show products inside + delivery update
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();
requireAdmin();

$pageTitle = 'Admin — Orders';
$db = getDB();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId   = (int)$_POST['order_id'];
    $newStatus = sanitize($_POST['order_status']);

    // Get current status — enforce forward-only
    $current = $db->prepare('SELECT order_status FROM orders WHERE id = ?');
    $current->execute([$orderId]);
    $currentStatus = $current->fetchColumn();

    $flow    = ['placed'=>0,'confirmed'=>1,'packed'=>2,'shipped'=>3,'delivered'=>4,'cancelled'=>5];
    $allowed = ['placed','confirmed','packed','shipped','delivered','cancelled'];

    // Can only go forward (or cancel from any non-delivered state)
    $canUpdate = in_array($newStatus, $allowed) && (
        $newStatus === 'cancelled' ||
        ($flow[$newStatus] ?? -1) > ($flow[$currentStatus] ?? -1)
    );

    if ($canUpdate) {
        $db->prepare('UPDATE orders SET order_status=? WHERE id=?')
           ->execute([$newStatus, $orderId]);

        // Sync delivery tracking
        $trackStatus = match($newStatus) {
            'shipped'   => 'in_transit',
            'delivered' => 'delivered',
            default     => 'confirmed'
        };
        $db->prepare('UPDATE delivery_tracking SET status=? WHERE order_id=?')
           ->execute([$trackStatus, $orderId]);
        if ($newStatus === 'delivered') {
            $db->prepare('UPDATE delivery_tracking SET delivered_at=NOW() WHERE order_id=?')
               ->execute([$orderId]);
        }
        setFlash('success', 'Order status updated to: ' . $newStatus);
    } else {
        setFlash('error', 'Cannot go back to a previous status.');
    }
    header('Location: ' . APP_URL . '/admin/orders');
    exit;
}

// Filters
$statusFilter = sanitize($_GET['status'] ?? '');
$search       = sanitize($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 20;
$offset       = ($page - 1) * $perPage;

$where  = [];
$params = [];
if ($statusFilter) { $where[] = 'o.order_status = ?'; $params[] = $statusFilter; }
if ($search)       { $where[] = '(o.order_ref LIKE ? OR o.customer_name LIKE ? OR o.phone LIKE ?)'; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$totalStmt = $db->prepare("SELECT COUNT(*) FROM orders o $whereSQL");
$totalStmt->execute($params);
$totalCount = $totalStmt->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

$orderStmt = $db->prepare("
    SELECT o.*, b.name AS box_name, b.slug AS box_slug,
           dt.status AS delivery_status
    FROM orders o
    JOIN boxes b ON o.box_id = b.id
    LEFT JOIN delivery_tracking dt ON dt.order_id = o.id
    $whereSQL
    ORDER BY o.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$orderStmt->execute($params);
$orderList = $orderStmt->fetchAll();

// Pre-fetch all order items for visible orders
$orderIds   = array_column($orderList, 'id');
$itemsByOrder = [];
if (!empty($orderIds)) {
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $itemStmt = $db->prepare("
        SELECT oi.order_id, p.name, p.brand, p.category, p.price, p.rarity
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id IN ($placeholders)
    ");
    $itemStmt->execute($orderIds);
    foreach ($itemStmt->fetchAll() as $item) {
        $itemsByOrder[$item['order_id']][] = $item;
    }
}

// Status flow for forward-only dropdown
$statusFlow = ['placed','confirmed','packed','shipped','delivered'];

require_once __DIR__ . '/../../components/admin_header.php';
?>
<div class="admin-layout">
<?php require_once __DIR__ . '/../../components/admin_sidebar.php'; ?>
<main class="admin-main">

    <div class="admin-topbar">
        <div>
            <h1 class="admin-page-title">Orders</h1>
            <p class="admin-page-sub"><?= number_format($totalCount) ?> total orders — click any row to see products</p>
        </div>
    </div>

    <!-- Status filter tabs -->
    <div class="delivery-tabs" style="margin-bottom:1rem">
        <?php
        $tabs = ['' => 'All', 'placed' => 'Placed', 'confirmed' => 'Confirmed', 'packed' => 'Packed', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];
        foreach ($tabs as $val => $label):
        ?>
        <a href="?status=<?= $val ?>&search=<?= e($search) ?>"
           class="delivery-tab <?= $statusFilter === $val ? 'delivery-tab--active' : '' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Search -->
    <div class="admin-filters glass-card">
        <form method="GET" action="<?= APP_URL ?>/admin/orders" class="filters-form">
            <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
            <input class="form-input" type="text" name="search"
                   placeholder="Search ref, name, phone..."
                   value="<?= e($search) ?>">
            <button type="submit" class="btn-primary">Search</button>
            <a href="<?= APP_URL ?>/admin/orders" class="btn-outline">Reset</a>
        </form>
    </div>

    <!-- Orders — expandable rows -->
    <div class="orders-expand-list">
    <?php if (empty($orderList)): ?>
        <div class="glass-card" style="padding:2rem;text-align:center;color:var(--text-muted)">No orders found.</div>
    <?php else: ?>
    <?php foreach ($orderList as $o):
        $items   = $itemsByOrder[$o['id']] ?? [];
        $flowIdx = array_search($o['order_status'], $statusFlow);
        $nextStatuses = array_slice($statusFlow, $flowIdx + 1);
    ?>
    <!-- ORDER ROW -->
    <div class="order-expand-card glass-card" id="order-<?= $o['id'] ?>">

        <!-- Header (always visible — click to expand) -->
        <div class="oec-header" onclick="toggleOrder(<?= $o['id'] ?>)">
            <div class="oec-left">
                <span class="oec-ref"><?= e($o['order_ref']) ?></span>
                <span class="oec-name"><?= e($o['customer_name']) ?></span>
                <span class="oec-box"><?= e($o['box_name']) ?></span>
            </div>
            <div class="oec-mid">
                <span class="oec-price"><?= formatPrice($o['total_amount']) ?></span>
                <a href="https://wa.me/977<?= e($o['phone']) ?>" target="_blank"
                   onclick="event.stopPropagation()"
                   class="oec-phone">💬 <?= e($o['phone']) ?></a>
                <span class="oec-city">📍 <?= e($o['city']) ?></span>
            </div>
            <div class="oec-right">
                <span class="status-badge status-badge--<?= e($o['order_status']) ?>">
                    <?= ucfirst($o['order_status']) ?>
                </span>
                <span class="pay-badge pay-badge--<?= e($o['payment_status']) ?>">
                    <?= ucfirst($o['payment_status']) ?>
                </span>
                <span class="oec-date"><?= date('d M Y', strtotime($o['created_at'])) ?></span>
                <span class="oec-toggle-icon" id="icon-<?= $o['id'] ?>">▼</span>
            </div>
        </div>

        <!-- Expanded panel -->
        <div class="oec-body" id="body-<?= $o['id'] ?>" style="display:none">

            <div class="oec-body-grid">

                <!-- Products inside box -->
                <div class="oec-products">
                    <h4 class="oec-section-title">📦 Pack These Products</h4>
                    <?php if (empty($items)): ?>
                    <p style="color:var(--text-muted);font-size:.82rem">Products not yet assigned.</p>
                    <?php else: ?>
                    <div class="pack-list">
                        <?php foreach ($items as $item): ?>
                        <div class="pack-item" onclick="toggleCheck(this)">
                            <div class="pack-item-check">☐</div>
                            <div class="pack-item-info">
                                <p class="pack-item-name"><?= e($item['name']) ?></p>
                                <p class="pack-item-meta">
                                    <?= e($item['brand']) ?> · <?= e($item['category']) ?> · <?= formatPrice($item['price']) ?>
                                </p>
                            </div>
                            <span class="rarity-pill rarity-pill--<?= e($item['rarity']) ?>">
                                <?= ucfirst(str_replace('_',' ',$item['rarity'])) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="pack-total">
                        <span><?= count($items) ?> items</span>
                        <span>Retail: <?= formatPrice(array_sum(array_column($items, 'price'))) ?></span>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Delivery address + status update -->
                <div class="oec-delivery">
                    <h4 class="oec-section-title">🚚 Delivery Details</h4>
                    <div class="oec-address-box">
                        <p><strong><?= e($o['customer_name']) ?></strong></p>
                        <p><?= e($o['address']) ?>, <?= e($o['city']) ?></p>
                        <p style="color:var(--text-muted);font-size:.78rem;margin-top:.3rem">
                            Notes: <?= e($o['notes'] ?: 'None') ?>
                        </p>
                    </div>

                    <?php if ($o['order_status'] !== 'delivered' && $o['order_status'] !== 'cancelled'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/orders" class="oec-status-form">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <label class="form-label">Move to next status:</label>
                        <div class="oec-status-buttons">
                            <?php foreach ($nextStatuses as $ns): ?>
                            <button type="submit" name="order_status" value="<?= $ns ?>"
                                    class="oec-status-btn oec-status-btn--<?= $ns ?>">
                                <?= ucfirst($ns) ?> →
                            </button>
                            <?php endforeach; ?>
                            <?php if ($o['order_status'] !== 'cancelled'): ?>
                            <button type="submit" name="order_status" value="cancelled"
                                    class="oec-status-btn oec-status-btn--cancel"
                                    onclick="return confirm('Cancel this order?')">
                                ✕ Cancel
                            </button>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="update_status" value="1">
                    </form>
                    <?php else: ?>
                    <div class="oec-final-status">
                        <?= $o['order_status'] === 'delivered'
                            ? '<span style="color:var(--success)">✅ Order delivered — no further updates</span>'
                            : '<span style="color:var(--error)">✕ Order cancelled</span>' ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination" style="margin-top:1.25rem">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>&status=<?= e($statusFilter) ?>&search=<?= e($search) ?>"
           class="page-btn <?= $i === $page ? 'page-btn--active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</main>
</div>

<script>
function toggleOrder(id) {
    const body = document.getElementById('body-' + id);
    const icon = document.getElementById('icon-' + id);
    const isOpen = body.style.display !== 'none';
    body.style.display = isOpen ? 'none' : 'block';
    icon.textContent   = isOpen ? '▼' : '▲';
}

function toggleCheck(el) {
    el.classList.toggle('checked');
    el.querySelector('.pack-item-check').textContent =
        el.classList.contains('checked') ? '☑' : '☐';
}
</script>

<?php require_once __DIR__ . '/../../components/admin_footer.php'; ?>