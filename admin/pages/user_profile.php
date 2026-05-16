<?php
// admin/pages/user_profile.php — K HO K Admin User Profile
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();
requireAdmin();

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) { redirect('/admin/users'); }

$db   = getDB();
$user = $db->prepare('SELECT * FROM users WHERE id = ?');
$user->execute([$userId]);
$user = $user->fetch();
if (!$user) { redirect('/admin/users'); }

// Handle delete account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if ($userId !== (int)$_SESSION['user_id']) {
        $db->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
        setFlash('success', 'User account deleted.');
        redirect('/admin/users');
    }
}

// User orders
$orders = $db->prepare('
    SELECT o.*, b.name AS box_name
    FROM orders o JOIN boxes b ON b.id = o.box_id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
');
$orders->execute([$userId]);
$userOrders = $orders->fetchAll();

$pageTitle = 'User — ' . $user['full_name'];
require_once __DIR__ . '/../../components/admin_header.php';
?>
<div class="admin-layout">
<?php require_once __DIR__ . '/../../components/admin_sidebar.php'; ?>
<main class="admin-main">

    <div class="admin-topbar">
        <div>
            <a href="<?= APP_URL ?>/admin/users" class="back-link">← Back to Users</a>
            <h1 class="admin-page-title"><?= e($user['full_name']) ?></h1>
        </div>
        <?php if ($userId !== (int)$_SESSION['user_id']): ?>
        <form method="POST" onsubmit="return confirm('Delete this account permanently?')">
            <button type="submit" name="delete_user" class="btn-danger">
                🗑 Delete Account
            </button>
        </form>
        <?php endif; ?>
    </div>

    <div class="order-detail-grid">
        <div class="od-left">
            <div class="glass-card od-card">
                <h3 class="od-card-title">Profile Info</h3>
                <div class="od-row"><span>Full Name</span><strong><?= e($user['full_name']) ?></strong></div>
                <div class="od-row"><span>Email</span><strong><?= e($user['email']) ?></strong></div>
                <div class="od-row"><span>Phone</span>
                    <a href="https://wa.me/977<?= e($user['phone']) ?>" target="_blank" class="wa-link">
                        <?= e($user['phone']) ?>
                    </a>
                </div>
                <div class="od-row"><span>City</span><strong><?= e($user['city'] ?: '—') ?></strong></div>
                <div class="od-row"><span>Address</span><strong><?= e($user['address'] ?: '—') ?></strong></div>
                <div class="od-row"><span>Role</span>
                    <span class="status-badge status-badge--shipped"><?= ucfirst($user['role']) ?></span>
                </div>
                <div class="od-row"><span>Joined</span>
                    <strong><?= date('d M Y', strtotime($user['created_at'])) ?></strong>
                </div>
            </div>

            <div class="glass-card od-card">
                <h3 class="od-card-title">Stats</h3>
                <div class="od-row"><span>Total Orders</span><strong><?= count($userOrders) ?></strong></div>
                <div class="od-row"><span>Total Spent</span>
                    <strong style="color:var(--accent)">
                        <?= formatPrice(array_sum(array_column($userOrders, 'total_amount'))) ?>
                    </strong>
                </div>
            </div>
        </div>

        <div class="od-right">
            <div class="glass-card od-card">
                <h3 class="od-card-title">Order History</h3>
                <?php if (empty($userOrders)): ?>
                <p style="color:var(--text-muted);font-size:.875rem">No orders yet.</p>
                <?php else: ?>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Box</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($userOrders as $o): ?>
                        <tr>
                            <td class="order-ref-cell"><?= e($o['order_ref']) ?></td>
                            <td><?= e($o['box_name']) ?></td>
                            <td><?= formatPrice($o['total_amount']) ?></td>
                            <td><span class="status-badge status-badge--<?= e($o['order_status']) ?>">
                                <?= ucfirst($o['order_status']) ?>
                            </span></td>
                            <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                            <td>
                                <a href="<?= APP_URL ?>/admin/orders/detail?id=<?= $o['id'] ?>"
                                   class="tbl-action">View →</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
</div>
<?php require_once __DIR__ . '/../../components/admin_footer.php'; ?>