<?php
// admin/pages/dashboard.php — K HO K Admin Dashboard
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();
requireAdmin();

$pageTitle = 'Admin Dashboard';

$db = getDB();

// ── Stats ──
$totalOrders   = $db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalRevenue  = $db->query('SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status="paid"')->fetchColumn();
$totalUsers    = $db->query('SELECT COUNT(*) FROM users WHERE role="customer"')->fetchColumn();
$pendingOrders = $db->query('SELECT COUNT(*) FROM orders WHERE order_status IN ("placed","confirmed","packed")')->fetchColumn();
$totalProducts = $db->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn();
$lowStock      = $db->query('SELECT COUNT(*) FROM products WHERE stock <= 3 AND is_active=1')->fetchColumn();

// ── Recent orders ──
$recentOrders = $db->query('
    SELECT o.*, b.name AS box_name
    FROM orders o JOIN boxes b ON o.box_id = b.id
    ORDER BY o.created_at DESC LIMIT 8
')->fetchAll();

// ── Orders by box (for pie chart) ──
$boxStats = $db->query('
    SELECT b.name, COUNT(o.id) AS total
    FROM orders o JOIN boxes b ON o.box_id = b.id
    GROUP BY b.id ORDER BY total DESC
')->fetchAll();

// ── Revenue last 7 days (for line chart) ──
$revenueByDay = $db->query('
    SELECT DATE(created_at) AS day,
           COALESCE(SUM(total_amount),0) AS revenue
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
      AND payment_status = "paid"
    GROUP BY DATE(created_at)
    ORDER BY day ASC
')->fetchAll();

// ── Orders by status (for bar chart) ──
$statusStats = $db->query('
    SELECT order_status, COUNT(*) AS total
    FROM orders GROUP BY order_status
')->fetchAll();

require_once __DIR__ . '/../../components/admin_header.php';
?>

<div class="admin-layout">

  <!-- ── SIDEBAR ── -->
  <?php require_once __DIR__ . '/../../components/admin_sidebar.php'; ?>

  <!-- ── MAIN CONTENT ── -->
  <main class="admin-main">

    <div class="admin-topbar">
      <div>
        <h1 class="admin-page-title">Dashboard</h1>
        <p class="admin-page-sub">Welcome back, <?= e($_SESSION['user_name']) ?> 👑</p>
      </div>
      <div class="admin-topbar-actions">
        <span class="admin-date"><?= date('d M Y') ?></span>
      </div>
    </div>

    <!-- ── STAT CARDS ── -->
    <div class="stat-cards">
      <div class="stat-card">
        <div class="stat-card-icon" style="background:rgba(168,85,247,.15)">📦</div>
        <div>
          <p class="stat-card-value"><?= number_format($totalOrders) ?></p>
          <p class="stat-card-label">Total Orders</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon" style="background:rgba(16,185,129,.15)">💰</div>
        <div>
          <p class="stat-card-value"><?= formatPrice($totalRevenue) ?></p>
          <p class="stat-card-label">Revenue</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon" style="background:rgba(59,130,246,.15)">👥</div>
        <div>
          <p class="stat-card-value"><?= number_format($totalUsers) ?></p>
          <p class="stat-card-label">Customers</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon" style="background:rgba(245,158,11,.15)">⏳</div>
        <div>
          <p class="stat-card-value"><?= number_format($pendingOrders) ?></p>
          <p class="stat-card-label">Pending Orders</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon" style="background:rgba(16,185,129,.15)">🎁</div>
        <div>
          <p class="stat-card-value"><?= number_format($totalProducts) ?></p>
          <p class="stat-card-label">Active Products</p>
        </div>
      </div>
      <div class="stat-card <?= $lowStock > 0 ? 'stat-card--warn' : '' ?>">
        <div class="stat-card-icon" style="background:rgba(239,68,68,.15)">⚠️</div>
        <div>
          <p class="stat-card-value"><?= number_format($lowStock) ?></p>
          <p class="stat-card-label">Low Stock</p>
        </div>
      </div>
    </div>

    <!-- ── CHARTS ROW ── -->
    <div class="charts-row">

      <!-- Line chart: Revenue last 7 days -->
      <div class="chart-card glass-card">
        <h3 class="chart-title">Revenue — Last 7 Days</h3>
        <canvas id="revenueChart" height="200"></canvas>
      </div>

      <!-- Pie chart: Orders by box -->
      <div class="chart-card glass-card">
        <h3 class="chart-title">Orders by Box</h3>
        <canvas id="boxPieChart" height="200"></canvas>
      </div>

    </div>

    <!-- Bar chart: Orders by status -->
    <div class="chart-card chart-card--full glass-card">
      <h3 class="chart-title">Orders by Status</h3>
      <canvas id="statusBarChart" height="100"></canvas>
    </div>

    <!-- ── RECENT ORDERS TABLE ── -->
    <div class="admin-table-card glass-card">
      <div class="admin-table-header">
        <h3 class="chart-title">Recent Orders</h3>
        <a href="<?= APP_URL ?>/admin/orders" class="btn-outline btn-sm">View All →</a>
      </div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Order Ref</th>
              <th>Customer</th>
              <th>Box</th>
              <th>Amount</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentOrders as $o): ?>
            <tr>
              <td class="order-ref-cell"><?= e($o['order_ref']) ?></td>
              <td><?= e($o['customer_name']) ?></td>
              <td><?= e($o['box_name']) ?></td>
              <td><?= formatPrice($o['total_amount']) ?></td>
              <td><span class="pay-badge pay-badge--<?= e($o['payment_status']) ?>"><?= ucfirst($o['payment_status']) ?></span></td>
              <td><span class="status-badge status-badge--<?= e($o['order_status']) ?>"><?= ucfirst($o['order_status']) ?></span></td>
              <td><?= date('d M', strtotime($o['created_at'])) ?></td>
              <td>
                <a href="<?= APP_URL ?>/admin/orders?id=<?= $o['id'] ?>" class="tbl-action">View</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<!-- Chart.js data passed to JS -->
<script>
window.KHOK_CHARTS = {
    revenue: {
        labels: <?= json_encode(array_column($revenueByDay, 'day')) ?>,
        data:   <?= json_encode(array_map('floatval', array_column($revenueByDay, 'revenue'))) ?>
    },
    boxes: {
        labels: <?= json_encode(array_column($boxStats, 'name')) ?>,
        data:   <?= json_encode(array_map('intval', array_column($boxStats, 'total'))) ?>
    },
    status: {
        labels: <?= json_encode(array_column($statusStats, 'order_status')) ?>,
        data:   <?= json_encode(array_map('intval', array_column($statusStats, 'total'))) ?>
    }
};
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?= APP_URL ?>/public/js/admin_dashboard.js"></script>

<?php require_once __DIR__ . '/../../components/admin_footer.php'; ?>s