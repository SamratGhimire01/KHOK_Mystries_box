<?php
// admin/pages/orders.php — K HO K Admin Orders
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
    $allowed   = ['placed','confirmed','packed','shipped','delivered','cancelled'];
    if (in_array($newStatus, $allowed)) {
        $db->prepare('UPDATE orders SET order_status = ? WHERE id = ?')
           ->execute([$newStatus, $orderId]);
        // Update delivery tracking status too
        $trackStatus = match($newStatus) {
            'shipped','delivered' => $newStatus === 'delivered' ? 'delivered' : 'in_transit',
            default => 'confirmed'
        };
        $db->prepare('UPDATE delivery_tracking SET status = ? WHERE order_id = ?')
           ->execute([$trackStatus, $orderId]);
        if ($newStatus === 'delivered') {
            $db->prepare('UPDATE delivery_tracking SET delivered_at = NOW() WHERE order_id = ?')
               ->execute([$orderId]);
        }
        setFlash('success', 'Order status updated.');
    }
    header('Location: ' . APP_URL . '/admin/orders');
    exit;
}

// Filters
$statusFilter = sanitize($_GET['status'] ?? '');
$search       = sanitize($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 15;
$offset       = ($page - 1) * $perPage;

$where  = [];
$params = [];
if ($statusFilter) { $where[] = 'o.order_status = ?'; $params[] = $statusFilter; }
if ($search)       { $where[] = '(o.order_ref LIKE ? OR o.customer_name LIKE ? OR o.phone LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = $db->prepare("SELECT COUNT(*) FROM orders o $whereSQL");
$total->execute($params);
$totalCount = $total->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

$orders = $db->prepare("
    SELECT o.*, b.name AS box_name
    FROM orders o JOIN boxes b ON o.box_id = b.id
    $whereSQL
    ORDER BY o.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$orders->execute($params);
$orderList = $orders->fetchAll();

require_once __DIR__ . '/../../components/admin_header.php';
?>
<div class="admin-layout">
<?php require_once __DIR__ . '/../../components/admin_sidebar.php'; ?>
<main class="admin-main">

    <div class="admin-topbar">
        <div>
            <h1 class="admin-page-title">Orders</h1>
            <p class="admin-page-sub"><?= number_format($totalCount) ?> total orders</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="admin-filters glass-card">
        <form method="GET" action="<?= APP_URL ?>/admin/orders" class="filters-form">
            <input class="form-input" type="text" name="search"
                   placeholder="Search order ref, name, phone..."
                   value="<?= e($search) ?>">
            <select class="form-input form-select" name="status">
                <option value="">All Statuses</option>
                <?php foreach (['placed','confirmed','packed','shipped','delivered','cancelled'] as $s): ?>
                <option <?= $statusFilter === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-primary">Filter</button>
            <a href="<?= APP_URL ?>/admin/orders" class="btn-outline">Reset</a>
        </form>
    </div>

    <!-- Orders table -->
    <div class="admin-table-card glass-card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order Ref</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Box</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($orderList)): ?>
                    <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:2rem">No orders found.</td></tr>
                <?php else: ?>
                <?php foreach ($orderList as $o): ?>
                <tr>
                    <td class="order-ref-cell"><?= e($o['order_ref']) ?></td>
                    <td><?= e($o['customer_name']) ?></td>
                    <td>
                        <a href="https://wa.me/977<?= e($o['phone']) ?>" target="_blank"
                           style="color:var(--success)">
                            <?= e($o['phone']) ?>
                        </a>
                    </td>
                    <td><?= e($o['box_name']) ?></td>
                    <td><?= formatPrice($o['total_amount']) ?></td>
                    <td><span class="pay-badge pay-badge--<?= e($o['payment_status']) ?>"><?= ucfirst($o['payment_status']) ?></span></td>
                    <td><span class="status-badge status-badge--<?= e($o['order_status']) ?>"><?= ucfirst($o['order_status']) ?></span></td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td>
                        <form method="POST" action="<?= APP_URL ?>/admin/orders" class="inline-form">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select class="form-input form-select inline-select" name="order_status">
                                <?php foreach (['placed','confirmed','packed','shipped','delivered','cancelled'] as $s): ?>
                                <option <?= $o['order_status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="tbl-action-btn">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= e($statusFilter) ?>&search=<?= e($search) ?>"
               class="page-btn <?= $i === $page ? 'page-btn--active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

</main>
</div>
<?php require_once __DIR__ . '/../../components/admin_footer.php'; ?>