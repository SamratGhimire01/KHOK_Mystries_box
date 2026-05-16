<?php
// admin/pages/users.php — K HO K Admin Users v2
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();
requireAdmin();

$pageTitle = 'Admin — Users';
$db = getDB();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = (int)$_POST['user_id'];
    if ($userId !== (int)$_SESSION['user_id']) {
        $db->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
        setFlash('success', 'User account deleted.');
    }
    header('Location: ' . APP_URL . '/admin/users');
    exit;
}

$search = sanitize($_GET['search'] ?? '');
$where  = $search ? 'WHERE u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?' : '';
$params = $search ? ["%$search%", "%$search%", "%$search%"] : [];

$users = $db->prepare("
    SELECT u.*,
           COUNT(o.id) AS order_count,
           COALESCE(SUM(o.total_amount),0) AS total_spent
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    $where
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users->execute($params);
$userList = $users->fetchAll();

require_once __DIR__ . '/../../components/admin_header.php';
?>
<div class="admin-layout">
<?php require_once __DIR__ . '/../../components/admin_sidebar.php'; ?>
<main class="admin-main">

    <div class="admin-topbar">
        <div>
            <h1 class="admin-page-title">Users</h1>
            <p class="admin-page-sub"><?= count($userList) ?> users registered</p>
        </div>
    </div>

    <div class="admin-filters glass-card">
        <form method="GET" action="<?= APP_URL ?>/admin/users" class="filters-form">
            <input class="form-input" type="text" name="search"
                   placeholder="Search name, email, phone..."
                   value="<?= e($search) ?>">
            <button type="submit" class="btn-primary">Search</button>
            <a href="<?= APP_URL ?>/admin/users" class="btn-outline">Reset</a>
        </form>
    </div>

    <div class="admin-table-card glass-card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($userList as $u): ?>
                <tr>
                    <td><strong><?= e($u['full_name']) ?></strong></td>
                    <td><?= e($u['email']) ?></td>
                    <td>
                        <a href="https://wa.me/977<?= e($u['phone']) ?>" target="_blank"
                           style="color:var(--success)"><?= e($u['phone']) ?></a>
                    </td>
                    <td><?= e($u['city'] ?: '—') ?></td>
                    <td><?= $u['order_count'] ?></td>
                    <td><?= formatPrice($u['total_spent']) ?></td>
                    <td>
                        <span class="status-badge <?= $u['role']==='admin' ? 'status-badge--packed' : ($u['role']==='delivery' ? 'status-badge--confirmed' : 'status-badge--shipped') ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td class="actions-cell">
                        <a href="<?= APP_URL ?>/admin/users/profile?id=<?= $u['id'] ?>"
                           class="tbl-action">View Profile</a>
                        <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                        &nbsp;
                        <form method="POST" style="display:inline"
                              onsubmit="return confirm('Delete <?= e($u['full_name']) ?>\'s account permanently?')">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <button type="submit" name="delete_user"
                                    style="color:var(--error);background:none;border:none;cursor:pointer;font-size:.78rem;font-weight:600">
                                Delete
                            </button>
                        </form>
                        <?php else: ?>
                        <span style="color:var(--text-muted);font-size:.72rem">(you)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>
</div>
<?php require_once __DIR__ . '/../../components/admin_footer.php'; ?>