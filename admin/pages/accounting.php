<?php
// admin/pages/accounting.php — K HO K Accounting v3
// Model: Box Revenue vs Distributed Product Market Value = Profit/Loss
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();
requireAdmin();

$pageTitle = 'Admin — Accounting';
$db = getDB();

// ── CORE FINANCIALS ──────────────────────────────────────────

// Total revenue from box sales (paid orders only)
$totalRevenue = (float)$db->query('
    SELECT COALESCE(SUM(total_amount), 0)
    FROM orders
    WHERE payment_status = "paid"
')->fetchColumn();

// Total market value of all products distributed (sent to customers)
// = SUM of market_value for every product in every paid order
$totalDistributed = (float)$db->query('
    SELECT COALESCE(SUM(p.market_value), 0)
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN products p ON p.id = oi.product_id
    WHERE o.payment_status = "paid"
')->fetchColumn();

// Net profit = Revenue - Distributed product worth
$netProfit   = $totalRevenue - $totalDistributed;
$isProfit    = $netProfit >= 0;
$profitMargin = $totalRevenue > 0
    ? abs($netProfit / $totalRevenue) * 100 : 0;

// Total orders
$totalOrders = (int)$db->query('
    SELECT COUNT(*) FROM orders WHERE payment_status = "paid"
')->fetchColumn();

// Average box value sold
$avgBoxValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

// ── STOCK OVERVIEW ──────────────────────────────────────────

// Current inventory worth (unsold stock × market value)
$stockWorth = (float)$db->query('
    SELECT COALESCE(SUM(market_value * stock), 0)
    FROM products WHERE is_active = 1
')->fetchColumn();

$totalStock = (int)$db->query('
    SELECT COALESCE(SUM(stock), 0) FROM products WHERE is_active = 1
')->fetchColumn();

$totalSold = (int)$db->query('
    SELECT COUNT(*) FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE o.payment_status = "paid"
')->fetchColumn();

// ── PER BOX BREAKDOWN ───────────────────────────────────────

$boxBreakdown = $db->query('
    SELECT
        b.name, b.slug, b.price AS box_price,
        COUNT(DISTINCT o.id)             AS orders_count,
        COALESCE(SUM(o.total_amount), 0) AS box_revenue,
        COALESCE(SUM(p.market_value), 0) AS product_worth_distributed
    FROM boxes b
    LEFT JOIN orders o ON o.box_id = b.id AND o.payment_status = "paid"
    LEFT JOIN order_items oi ON oi.order_id = o.id
    LEFT JOIN products p ON p.id = oi.product_id
    GROUP BY b.id, b.name, b.slug, b.price
    ORDER BY SUM(o.total_amount) DESC
')->fetchAll();
// ── PRODUCT DISTRIBUTION TABLE ──────────────────────────────

$productStats = $db->query('
    SELECT
        p.name, p.brand, p.rarity,
        p.market_value,
        p.stock          AS current_stock,
        p.is_active_reward,
        COALESCE(sold.units_sold, 0)                             AS units_sold,
        COALESCE(sold.units_sold, 0) * p.market_value            AS total_worth_distributed
    FROM products p
    LEFT JOIN (
        SELECT oi.product_id, COUNT(*) AS units_sold
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        WHERE o.payment_status = "paid"
        GROUP BY oi.product_id
    ) sold ON sold.product_id = p.id
    WHERE p.is_active = 1
    ORDER BY total_worth_distributed DESC
')->fetchAll();

// ── PREDICTIVE ANALYTICS (Next 30 days) ─────────────────────

// Average daily orders (last 30 days)
$last30Stats = $db->query('
    SELECT
        COUNT(*) AS total_orders,
        COALESCE(SUM(total_amount), 0) AS total_revenue
    FROM orders
    WHERE payment_status = "paid"
      AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
')->fetch();

$last30Orders  = (int)$last30Stats['total_orders'];
$last30Revenue = (float)$last30Stats['total_revenue'];
$dailyOrders   = $last30Orders / 30;
$dailyRevenue  = $last30Revenue / 30;

// Average product worth per order distributed
$avgProductWorthPerOrder = $totalOrders > 0
    ? $totalDistributed / $totalOrders : 0;

// Forecast next 30 days
$forecastOrders   = round($dailyOrders * 30);
$forecastRevenue  = $dailyRevenue * 30;
$forecastCost     = $avgProductWorthPerOrder * $forecastOrders;
$forecastProfit   = $forecastRevenue - $forecastCost;
$forecastIsProfit = $forecastProfit >= 0;

// Stock runway — how many more orders can current stock support
$avgItemsPerOrder = $totalOrders > 0 ? $totalSold / $totalOrders : 2;
$stockRunway = $avgItemsPerOrder > 0
    ? floor($totalStock / $avgItemsPerOrder) : 0;

// Daily revenue trend (last 14 days for sparkline)
$dailyTrend = $db->query('
    SELECT
        DATE(created_at) AS day,
        COUNT(*) AS orders,
        COALESCE(SUM(total_amount), 0) AS revenue
    FROM orders
    WHERE payment_status = "paid"
      AND created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
')->fetchAll();

require_once __DIR__ . '/../../components/admin_header.php';
?>
<div class="admin-layout">
<?php require_once __DIR__ . '/../../components/admin_sidebar.php'; ?>
<main class="admin-main">

<div class="admin-topbar">
    <div>
        <h1 class="admin-page-title">📊 Accounting & Financials</h1>
        <p class="admin-page-sub">Revenue vs distributed product worth · 30-day forecast</p>
    </div>
</div>

<!-- ══════════════════════════════════════
     SECTION 1: EARNINGS TRACKER
══════════════════════════════════════ -->
<div class="acc-section-label">💰 Earnings Tracker</div>

<div class="acc-earnings-hero glass-card">
    <div class="aeh-formula">
        <div class="aeh-block aeh-block--revenue">
            <p class="aeh-label">Total Box Revenue</p>
            <p class="aeh-value"><?= formatPrice($totalRevenue) ?></p>
            <p class="aeh-sub"><?= $totalOrders ?> paid orders</p>
        </div>
        <div class="aeh-op">−</div>
        <div class="aeh-block aeh-block--cost">
            <p class="aeh-label">Product Worth Distributed</p>
            <p class="aeh-value"><?= formatPrice($totalDistributed) ?></p>
            <p class="aeh-sub"><?= $totalSold ?> products sent out</p>
        </div>
        <div class="aeh-op">=</div>
        <div class="aeh-block <?= $isProfit ? 'aeh-block--profit' : 'aeh-block--loss' ?>">
            <p class="aeh-label"><?= $isProfit ? '✅ Net Profit' : '⚠️ Net Loss' ?></p>
            <p class="aeh-value"><?= formatPrice(abs($netProfit)) ?></p>
            <p class="aeh-sub"><?= number_format($profitMargin, 1) ?>% <?= $isProfit ? 'profit margin' : 'loss rate' ?></p>
        </div>
    </div>

    <?php if (!$isProfit): ?>
    <div class="aeh-warning">
        ⚠️ You are currently distributing more product value than you are collecting in revenue.
        Consider increasing box prices or adjusting rarity weights.
    </div>
    <?php endif; ?>
</div>

<!-- Stat cards row -->
<div class="acc-stat-row">
    <div class="acc-stat-card">
        <p class="acc-stat-label">Avg Box Value Sold</p>
        <p class="acc-stat-value"><?= formatPrice($avgBoxValue) ?></p>
    </div>
    <div class="acc-stat-card">
        <p class="acc-stat-label">Avg Product Worth / Order</p>
        <p class="acc-stat-value"><?= formatPrice($avgProductWorthPerOrder) ?></p>
    </div>
    <div class="acc-stat-card">
        <p class="acc-stat-label">Stock on Hand Worth</p>
        <p class="acc-stat-value"><?= formatPrice($stockWorth) ?></p>
    </div>
    <div class="acc-stat-card">
        <p class="acc-stat-label">Stock Runway</p>
        <p class="acc-stat-value"><?= $stockRunway ?> orders</p>
        <p class="acc-stat-sub">before restock needed</p>
    </div>
</div>

<!-- ══════════════════════════════════════
     SECTION 2: BOX BREAKDOWN
══════════════════════════════════════ -->
<div class="acc-section-label">📦 Revenue vs Cost by Box Tier</div>

<div class="glass-card admin-form-card">
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Box</th>
                    <th>Orders</th>
                    <th>Box Revenue</th>
                    <th>Product Worth Sent</th>
                    <th>Net</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($boxBreakdown as $b):
                $boxNet      = $b['box_revenue'] - $b['product_worth_distributed'];
                $boxIsProfit = $boxNet >= 0;
            ?>
            <tr>
                <td><strong><?= e($b['name']) ?></strong><br>
                    <span style="font-size:.72rem;color:var(--text-muted)"><?= formatPrice($b['box_price']) ?>/box</span>
                </td>
                <td><?= $b['orders_count'] ?></td>
                <td style="color:var(--success);font-weight:600"><?= formatPrice($b['box_revenue']) ?></td>
                <td style="color:var(--error)"><?= formatPrice($b['product_worth_distributed']) ?></td>
                <td style="color:<?= $boxIsProfit?'var(--success)':'var(--error)' ?>;font-weight:700">
                    <?= $boxIsProfit ? '+' : '-' ?><?= formatPrice(abs($boxNet)) ?>
                </td>
                <td>
                    <span class="status-badge <?= $boxIsProfit?'status-badge--delivered':'status-badge--cancelled' ?>">
                        <?= $boxIsProfit ? 'Profitable' : 'Loss' ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ══════════════════════════════════════
     SECTION 3: PREDICTIVE ANALYTICS
══════════════════════════════════════ -->
<div class="acc-section-label">🔮 30-Day Forecast</div>

<div class="acc-forecast-grid">

    <div class="acc-forecast-card glass-card">
        <div class="afc-header">
            <span class="afc-icon">📈</span>
            <span class="afc-title">Projected Revenue</span>
        </div>
        <p class="afc-value" style="color:var(--success)"><?= formatPrice($forecastRevenue) ?></p>
        <p class="afc-desc">Based on <?= number_format($dailyRevenue > 0 ? $dailyRevenue : 0, 0) ?> Rs/day average from last 30 days</p>
    </div>

    <div class="acc-forecast-card glass-card">
        <div class="afc-header">
            <span class="afc-icon">📦</span>
            <span class="afc-title">Projected Orders</span>
        </div>
        <p class="afc-value" style="color:var(--accent)"><?= $forecastOrders ?></p>
        <p class="afc-desc">~<?= number_format($dailyOrders, 1) ?> orders/day based on recent activity</p>
    </div>

    <div class="acc-forecast-card glass-card">
        <div class="afc-header">
            <span class="afc-icon">💸</span>
            <span class="afc-title">Projected Product Cost</span>
        </div>
        <p class="afc-value" style="color:var(--error)"><?= formatPrice($forecastCost) ?></p>
        <p class="afc-desc">Avg <?= formatPrice($avgProductWorthPerOrder) ?> product worth per order</p>
    </div>

    <div class="acc-forecast-card glass-card <?= $forecastIsProfit ? 'afc--profit' : 'afc--loss' ?>">
        <div class="afc-header">
            <span class="afc-icon"><?= $forecastIsProfit ? '✅' : '⚠️' ?></span>
            <span class="afc-title">Projected <?= $forecastIsProfit ? 'Profit' : 'Loss' ?></span>
        </div>
        <p class="afc-value" style="color:<?= $forecastIsProfit?'var(--success)':'var(--error)' ?>">
            <?= formatPrice(abs($forecastProfit)) ?>
        </p>
        <p class="afc-desc">
            <?= $forecastIsProfit
                ? 'Business is financially viable at current rates.'
                : 'Warning: Projected to lose money. Review box prices or rarity weights.' ?>
        </p>
    </div>

</div>

<!-- Stock warning -->
<?php if ($stockRunway < 30): ?>
<div class="acc-stock-warning glass-card">
    ⚠️ <strong>Stock Alert:</strong> At current order rates, your stock will run out in approximately
    <strong><?= $stockRunway ?> orders</strong>. Restock soon to avoid unfulfilled orders.
</div>
<?php endif; ?>

<?php if ($last30Orders == 0): ?>
<div class="acc-no-data glass-card">
    📊 No paid orders in the last 30 days. Forecast will appear once orders come in.
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════
     SECTION 4: PRODUCT DISTRIBUTION
══════════════════════════════════════ -->
<div class="acc-section-label">🎁 Product Distribution</div>

<div class="admin-table-card glass-card">
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Brand</th>
                    <th>Rarity</th>
                    <th>Market Value</th>
                    <th>Units Sold</th>
                    <th>Total Worth Distributed</th>
                    <th>Stock Left</th>
                    <th>Reward Active</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($productStats as $p): ?>
            <tr>
                <td><strong><?= e($p['name']) ?></strong></td>
                <td><?= e($p['brand']) ?></td>
                <td><span class="rarity-pill rarity-pill--<?= e($p['rarity']) ?>">
                    <?= ucfirst(str_replace('_',' ',$p['rarity'])) ?>
                </span></td>
                <td style="font-weight:600"><?= formatPrice($p['market_value']) ?></td>
                <td style="text-align:center;font-weight:600"><?= $p['units_sold'] ?></td>
                <td style="color:var(--error);font-weight:600">
                    <?= $p['units_sold'] > 0 ? formatPrice($p['total_worth_distributed']) : '<span style="color:var(--text-muted)">—</span>' ?>
                </td>
                <td>
                    <span style="color:<?= $p['current_stock']==0?'var(--error)':($p['current_stock']<=3?'var(--warning)':'var(--success)') ?>;font-weight:600">
                        <?= $p['current_stock'] ?>
                    </span>
                    <?php if ($p['current_stock']==0): ?><span class="out-of-stock-badge">OUT</span>
                    <?php elseif ($p['current_stock']<=3): ?><span class="low-stock-badge">LOW</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-badge <?= $p['is_active_reward']?'status-badge--delivered':'status-badge--cancelled' ?>">
                        <?= $p['is_active_reward']?'Active':'Paused' ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="border-top:2px solid var(--border)">
                    <td colspan="4" style="font-weight:700;padding:.85rem 1rem">TOTALS</td>
                    <td style="font-weight:700;text-align:center"><?= $totalSold ?></td>
                    <td style="color:var(--error);font-weight:800"><?= formatPrice($totalDistributed) ?></td>
                    <td style="font-weight:700"><?= $totalStock ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

</main>
</div>

<style>
/* Accounting v3 styles */
.acc-section-label {
    font-size:.72rem;font-weight:700;text-transform:uppercase;
    letter-spacing:.12em;color:var(--accent);
    margin:1.75rem 0 .85rem;padding-left:.25rem;
}

/* Earnings hero */
.acc-earnings-hero { padding:2rem; }
.aeh-formula {
    display:flex;align-items:center;
    gap:1.5rem;flex-wrap:wrap;
    margin-bottom:1rem;
}
.aeh-block {
    flex:1;min-width:180px;
    padding:1.25rem 1.5rem;
    border-radius:var(--radius-md);
    border:1px solid var(--border);
    text-align:center;
}
.aeh-block--revenue { border-color:rgba(16,185,129,.3);background:rgba(16,185,129,.06); }
.aeh-block--cost    { border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.06); }
.aeh-block--profit  { border-color:rgba(16,185,129,.4);background:rgba(16,185,129,.1); }
.aeh-block--loss    { border-color:rgba(239,68,68,.4);background:rgba(239,68,68,.1); }

.aeh-label { font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:.5rem; }
.aeh-value {
    font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;
    margin-bottom:.3rem;
}
.aeh-block--revenue .aeh-value { color:var(--success); }
.aeh-block--cost    .aeh-value { color:var(--error); }
.aeh-block--profit  .aeh-value { color:var(--success); }
.aeh-block--loss    .aeh-value { color:var(--error); }
.aeh-sub { font-size:.75rem;color:var(--text-muted); }

.aeh-op {
    font-size:2rem;font-weight:800;color:var(--text-muted);
    flex-shrink:0;
}
.aeh-warning {
    background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.3);
    border-radius:var(--radius-sm);padding:.85rem 1.1rem;
    font-size:.845rem;color:var(--error);margin-top:1rem;
}

/* Stat row */
.acc-stat-row {
    display:grid;grid-template-columns:repeat(4,1fr);
    gap:1rem;margin-bottom:1.5rem;
}
.acc-stat-card {
    background:var(--bg-card);border:1px solid var(--border);
    border-radius:var(--radius-md);padding:1.1rem 1.25rem;
}
.acc-stat-label { font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:.4rem; }
.acc-stat-value { font-family:'Syne',sans-serif;font-size:1rem;font-weight:800;color:var(--accent); }
.acc-stat-sub   { font-size:.68rem;color:var(--text-muted);margin-top:.2rem; }

/* Forecast grid */
.acc-forecast-grid {
    display:grid;grid-template-columns:repeat(4,1fr);
    gap:1rem;margin-bottom:1rem;
}
.acc-forecast-card { padding:1.35rem; }
.afc--profit { border-color:rgba(16,185,129,.3);background:rgba(16,185,129,.04); }
.afc--loss   { border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.04); }

.afc-header { display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem; }
.afc-icon   { font-size:1.25rem; }
.afc-title  { font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted); }
.afc-value  { font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;margin-bottom:.5rem; }
.afc-desc   { font-size:.75rem;color:var(--text-muted);line-height:1.5; }

.acc-stock-warning,.acc-no-data {
    padding:1rem 1.5rem;margin-bottom:1rem;
    font-size:.875rem;border-color:rgba(245,158,11,.3);
    background:rgba(245,158,11,.06);color:#FBBF24;
}
.acc-no-data { border-color:var(--border);background:var(--bg-card);color:var(--text-muted); }

@media(max-width:1100px) {
    .acc-forecast-grid { grid-template-columns:repeat(2,1fr); }
    .acc-stat-row      { grid-template-columns:repeat(2,1fr); }
}
@media(max-width:700px) {
    .aeh-formula       { flex-direction:column; }
    .acc-forecast-grid { grid-template-columns:1fr; }
    .acc-stat-row      { grid-template-columns:1fr 1fr; }
}
</style>

<?php require_once __DIR__ . '/../../components/admin_footer.php'; ?>