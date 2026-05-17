<?php
// admin/pages/accounting.php — K HO K Accounting v2
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();
requireAdmin();

$pageTitle = 'Admin — Accounting';
$db = getDB();

// ── Per product: units sold, revenue earned, cost of goods sold
$productStats = $db->query('
    SELECT
        p.id,
        p.name,
        p.brand,
        p.rarity,
        p.price          AS selling_price,
        p.buying_price,
        p.stock          AS current_stock,
        COALESCE(sold.units_sold, 0)                                      AS units_sold,
        COALESCE(sold.units_sold, 0) * p.buying_price                     AS total_invested,
        COALESCE(sold.units_sold, 0) * p.price                            AS total_revenue,
        COALESCE(sold.units_sold, 0) * (p.price - p.buying_price)         AS total_profit,
        p.stock * p.buying_price                                           AS stock_value
    FROM products p
    LEFT JOIN (
        SELECT oi.product_id, COUNT(oi.id) AS units_sold
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        WHERE o.payment_status = "paid"
        GROUP BY oi.product_id
    ) sold ON sold.product_id = p.id
    WHERE p.is_active = 1
    ORDER BY total_profit DESC
')->fetchAll();

// ── Grand totals
$grandInvested   = array_sum(array_column($productStats, 'total_invested'));
$grandRevenue    = array_sum(array_column($productStats, 'total_revenue'));
$grandProfit     = array_sum(array_column($productStats, 'total_profit'));
$grandUnitsSold  = array_sum(array_column($productStats, 'units_sold'));
$grandStockValue = array_sum(array_column($productStats, 'stock_value'));
$profitMargin    = $grandRevenue > 0 ? ($grandProfit / $grandRevenue) * 100 : 0;

// ── Revenue by box tier
$revenueByBox = $db->query('
    SELECT b.name, b.slug, COUNT(o.id) AS orders,
           COALESCE(SUM(o.total_amount), 0) AS total_revenue,
           b.price AS box_price
    FROM orders o
    JOIN boxes b ON b.id = o.box_id
    WHERE o.payment_status = "paid"
    GROUP BY b.id, b.name, b.slug, b.price
    ORDER BY SUM(o.total_amount) DESC
')->fetchAll();

require_once __DIR__ . '/../../components/admin_header.php';
?>
<div class="admin-layout">
<?php require_once __DIR__ . '/../../components/admin_sidebar.php'; ?>
<main class="admin-main">

    <div class="admin-topbar">
        <div>
            <h1 class="admin-page-title">📊 Accounting</h1>
            <p class="admin-page-sub">Your investment, revenue and profit — all in one place</p>
        </div>
        <a href="<?= APP_URL ?>/admin/products" class="btn-outline btn-sm">← Back to Products</a>
    </div>

    <!-- ── SUMMARY CARDS ── -->
    <div class="acc-summary-grid">

        <div class="acc-card acc-card--invest">
            <div class="acc-card-icon">💸</div>
            <div class="acc-card-body">
                <p class="acc-card-label">Total Invested (COGS)</p>
                <p class="acc-card-value"><?= formatPrice($grandInvested) ?></p>
                <p class="acc-card-sub">Money spent buying sold products</p>
            </div>
        </div>

        <div class="acc-card acc-card--revenue">
            <div class="acc-card-icon">💰</div>
            <div class="acc-card-body">
                <p class="acc-card-label">Total Revenue</p>
                <p class="acc-card-value"><?= formatPrice($grandRevenue) ?></p>
                <p class="acc-card-sub">Money received from customers</p>
            </div>
        </div>

        <div class="acc-card <?= $grandProfit >= 0 ? 'acc-card--profit' : 'acc-card--loss' ?>">
            <div class="acc-card-icon"><?= $grandProfit >= 0 ? '📈' : '📉' ?></div>
            <div class="acc-card-body">
                <p class="acc-card-label">Net Profit</p>
                <p class="acc-card-value"><?= formatPrice($grandProfit) ?></p>
                <p class="acc-card-sub">Revenue minus cost of goods</p>
            </div>
        </div>

        <div class="acc-card acc-card--margin">
            <div class="acc-card-icon">🎯</div>
            <div class="acc-card-body">
                <p class="acc-card-label">Profit Margin</p>
                <p class="acc-card-value"><?= number_format($profitMargin, 1) ?>%</p>
                <p class="acc-card-sub">Of every Rs.100 earned</p>
            </div>
        </div>

        <div class="acc-card acc-card--stock">
            <div class="acc-card-icon">🏪</div>
            <div class="acc-card-body">
                <p class="acc-card-label">Stock on Hand Value</p>
                <p class="acc-card-value"><?= formatPrice($grandStockValue) ?></p>
                <p class="acc-card-sub">Money tied up in unsold inventory</p>
            </div>
        </div>

        <div class="acc-card acc-card--units">
            <div class="acc-card-icon">📦</div>
            <div class="acc-card-body">
                <p class="acc-card-label">Units Sold</p>
                <p class="acc-card-value"><?= number_format($grandUnitsSold) ?></p>
                <p class="acc-card-sub">Total products shipped to customers</p>
            </div>
        </div>

    </div>

    <!-- ── PROFIT FORMULA EXPLAINER ── -->
    <div class="glass-card acc-formula">
        <p class="acc-formula-title">How profit is calculated</p>
        <div class="acc-formula-row">
            <span class="acc-formula-item acc-formula-item--revenue">Revenue = Units Sold × Selling Price</span>
            <span class="acc-formula-op">−</span>
            <span class="acc-formula-item acc-formula-item--cost">Cost = Units Sold × Buying Price</span>
            <span class="acc-formula-op">=</span>
            <span class="acc-formula-item acc-formula-item--profit">Profit</span>
        </div>
        <p class="acc-formula-example">
            Example: Sold 3 earbuds × Rs.2,899 selling price − 3 × Rs.2,000 buying price = Rs.2,697 profit
        </p>
    </div>

    <!-- ── REVENUE BY BOX ── -->
    <?php if (!empty($revenueByBox)): ?>
    <div class="glass-card admin-form-card">
        <h3 class="chart-title">Revenue by Box Tier</h3>
        <div class="acc-box-grid">
            <?php foreach ($revenueByBox as $r):
                $color = match($r['slug']) {
                    'shadow'  => '#71717A',
                    'core'    => '#3B82F6',
                    'pulse'   => '#10B981',
                    'elite'   => '#A855F7',
                    'phantom' => '#EC4899',
                    'god'     => '#F59E0B',
                    default   => '#A855F7'
                };
            ?>
            <div class="acc-box-card" style="border-color:<?= $color ?>20;background:<?= $color ?>08">
                <p class="acc-box-name" style="color:<?= $color ?>"><?= e($r['name']) ?></p>
                <p class="acc-box-revenue"><?= formatPrice($r['total_revenue']) ?></p>
                <p class="acc-box-orders"><?= $r['orders'] ?> order<?= $r['orders'] != 1 ? 's':'' ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── PRODUCT BREAKDOWN ── -->
    <div class="admin-table-card glass-card">
        <div class="admin-table-header">
            <div>
                <h3 class="chart-title" style="margin-bottom:.2rem">Product Profit Breakdown</h3>
                <p style="font-size:.78rem;color:var(--text-muted)">
                    Buying price = what you paid · Selling price = what customer paid · Only counts paid orders
                </p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Brand</th>
                        <th>Buying Price<br><span style="font-weight:400;color:var(--text-muted)">(per unit)</span></th>
                        <th>Selling Price<br><span style="font-weight:400;color:var(--text-muted)">(per unit)</span></th>
                        <th>Profit/Unit</th>
                        <th>Units Sold</th>
                        <th>Total Invested<br><span style="font-weight:400;color:var(--text-muted)">(sold units)</span></th>
                        <th>Total Revenue</th>
                        <th>Total Profit</th>
                        <th>Stock Left</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($productStats as $p):
                    $profitPerUnit = $p['selling_price'] - $p['buying_price'];
                    $margin = $p['selling_price'] > 0
                        ? ($profitPerUnit / $p['selling_price']) * 100 : 0;
                    $noBuyingPrice = $p['buying_price'] <= 0;
                ?>
                <tr class="<?= $noBuyingPrice ? 'row-warn' : '' ?>">
                    <td>
                        <strong><?= e($p['name']) ?></strong>
                        <span class="rarity-pill rarity-pill--<?= e($p['rarity']) ?>" style="display:block;margin-top:.2rem">
                            <?= ucfirst(str_replace('_',' ',$p['rarity'])) ?>
                        </span>
                    </td>
                    <td><?= e($p['brand']) ?></td>

                    <!-- Buying price -->
                    <td>
                        <?php if ($noBuyingPrice): ?>
                        <span class="acc-not-set">⚠️ Not set</span>
                        <?php else: ?>
                        <span style="color:var(--error);font-weight:600"><?= formatPrice($p['buying_price']) ?></span>
                        <?php endif; ?>
                    </td>

                    <!-- Selling price -->
                    <td style="color:var(--success);font-weight:600"><?= formatPrice($p['selling_price']) ?></td>

                    <!-- Profit per unit -->
                    <td>
                        <?php if ($noBuyingPrice): ?>
                        <span class="acc-not-set">—</span>
                        <?php else: ?>
                        <span style="color:<?= $profitPerUnit>=0?'var(--success)':'var(--error)' ?>;font-weight:700">
                            <?= formatPrice($profitPerUnit) ?>
                        </span>
                        <span style="font-size:.7rem;color:var(--text-muted);display:block">
                            <?= number_format($margin,1) ?>% margin
                        </span>
                        <?php endif; ?>
                    </td>

                    <!-- Units sold -->
                    <td style="font-weight:600;text-align:center">
                        <?= $p['units_sold'] ?>
                    </td>

                    <!-- Total invested -->
                    <td style="color:var(--error)">
                        <?= $noBuyingPrice ? '<span class="acc-not-set">—</span>' : formatPrice($p['total_invested']) ?>
                    </td>

                    <!-- Total revenue -->
                    <td style="color:var(--success)">
                        <?= $p['units_sold'] > 0 ? formatPrice($p['total_revenue']) : '<span style="color:var(--text-muted)">Rs. 0</span>' ?>
                    </td>

                    <!-- Total profit -->
                    <td>
                        <?php if ($noBuyingPrice): ?>
                        <span class="acc-not-set">—</span>
                        <?php elseif ($p['units_sold'] == 0): ?>
                        <span style="color:var(--text-muted)">Rs. 0</span>
                        <?php else: ?>
                        <strong style="color:<?= $p['total_profit']>=0?'var(--success)':'var(--error)' ?>;font-size:.95rem">
                            <?= formatPrice($p['total_profit']) ?>
                        </strong>
                        <?php endif; ?>
                    </td>

                    <!-- Stock left -->
                    <td>
                        <span style="color:<?= $p['current_stock']==0?'var(--error)':($p['current_stock']<=3?'var(--warning)':'var(--success)') ?>;font-weight:600">
                            <?= $p['current_stock'] ?>
                        </span>
                        <?php if ($p['current_stock'] == 0): ?>
                        <span class="out-of-stock-badge">OUT</span>
                        <?php elseif ($p['current_stock'] <= 3): ?>
                        <span class="low-stock-badge">LOW</span>
                        <?php endif; ?>
                        <?php if (!$noBuyingPrice): ?>
                        <span style="font-size:.68rem;color:var(--text-muted);display:block">
                            = <?= formatPrice($p['stock_value']) ?>
                        </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>

                <!-- TOTALS ROW -->
                <tfoot>
                    <tr class="acc-totals-row">
                        <td colspan="6" style="font-weight:700;font-size:.9rem;padding:.9rem 1rem">
                            TOTALS (<?= $grandUnitsSold ?> units sold)
                        </td>
                        <td style="color:var(--error);font-weight:700"><?= formatPrice($grandInvested) ?></td>
                        <td style="color:var(--success);font-weight:700"><?= formatPrice($grandRevenue) ?></td>
                        <td style="color:<?= $grandProfit>=0?'var(--success)':'var(--error)' ?>;font-weight:800;font-size:1.05rem">
                            <?= formatPrice($grandProfit) ?>
                        </td>
                        <td style="color:var(--text-muted);font-weight:600"><?= formatPrice($grandStockValue) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</main>
</div>

<style>
/* Accounting specific styles */
.acc-summary-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:1rem;
    margin-bottom:1.5rem;
}
.acc-card {
    display:flex;align-items:flex-start;gap:1rem;
    padding:1.25rem;border-radius:var(--radius-md);
    border:1px solid var(--border);
    background:var(--bg-card);
    transition:transform .2s;
}
.acc-card:hover { transform:translateY(-2px); }
.acc-card-icon  { font-size:1.75rem;flex-shrink:0; }
.acc-card-label { font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:.3rem; }
.acc-card-value { font-family:'Syne',sans-serif;font-size:1.2rem;font-weight:800;margin-bottom:.2rem; }
.acc-card-sub   { font-size:.72rem;color:var(--text-muted); }
.acc-card--invest  { border-color:rgba(239,68,68,.2); }
.acc-card--invest .acc-card-value { color:var(--error); }
.acc-card--revenue { border-color:rgba(16,185,129,.2); }
.acc-card--revenue .acc-card-value { color:var(--success); }
.acc-card--profit  { border-color:rgba(16,185,129,.2); }
.acc-card--profit .acc-card-value  { color:var(--success); }
.acc-card--loss    { border-color:rgba(239,68,68,.2); }
.acc-card--loss .acc-card-value    { color:var(--error); }
.acc-card--margin  { border-color:rgba(168,85,247,.2); }
.acc-card--margin .acc-card-value  { color:var(--accent); }
.acc-card--stock   { border-color:rgba(245,158,11,.2); }
.acc-card--stock .acc-card-value   { color:#F59E0B; }
.acc-card--units   { border-color:rgba(59,130,246,.2); }
.acc-card--units .acc-card-value   { color:#60A5FA; }

/* Formula explainer */
.acc-formula { padding:1.25rem 1.5rem;margin-bottom:1.25rem; }
.acc-formula-title { font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:.85rem; }
.acc-formula-row   { display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:.65rem; }
.acc-formula-item  { padding:.4rem .85rem;border-radius:var(--radius-sm);font-size:.82rem;font-weight:600; }
.acc-formula-item--revenue { background:rgba(16,185,129,.1);color:var(--success);border:1px solid rgba(16,185,129,.2); }
.acc-formula-item--cost    { background:rgba(239,68,68,.1);color:var(--error);border:1px solid rgba(239,68,68,.2); }
.acc-formula-item--profit  { background:rgba(168,85,247,.1);color:var(--accent);border:1px solid var(--border-accent); }
.acc-formula-op    { font-size:1.2rem;font-weight:700;color:var(--text-muted); }
.acc-formula-example { font-size:.78rem;color:var(--text-muted);font-style:italic; }

/* Box grid */
.acc-box-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.85rem;margin-top:.75rem; }
.acc-box-card { padding:1rem;border-radius:var(--radius-sm);border:1px solid var(--border);text-align:center; }
.acc-box-name    { font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem; }
.acc-box-revenue { font-family:'Syne',sans-serif;font-size:1rem;font-weight:800;margin-bottom:.2rem; }
.acc-box-orders  { font-size:.72rem;color:var(--text-muted); }

/* Table extras */
.acc-not-set    { color:var(--warning);font-size:.78rem;font-weight:600; }
.row-warn td:first-child { border-left:2px solid var(--warning); }
.acc-totals-row { border-top:2px solid var(--border); }
.acc-totals-row td { padding:.9rem 1rem;background:rgba(168,85,247,.03); }

@media(max-width:900px) {
    .acc-summary-grid { grid-template-columns:repeat(2,1fr); }
}
@media(max-width:600px) {
    .acc-summary-grid { grid-template-columns:1fr; }
}
</style>

<?php require_once __DIR__ . '/../../components/admin_footer.php'; ?>