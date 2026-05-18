<?php
// admin/pages/products.php — v4 — market value model
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();
requireAdmin();

$pageTitle = 'Admin — Products';
$db = getDB();

// Handle stock update + market value update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $productId   = (int)$_POST['product_id'];
    $addStock    = (int)$_POST['add_stock'];
    $marketValue = (float)$_POST['market_value'];
    $rarity      = sanitize($_POST['rarity'] ?? '');

    $db->prepare('
        UPDATE products
        SET stock        = stock + ?,
            market_value = ?,
            rarity       = ?
        WHERE id = ?
    ')->execute([$addStock, $marketValue, $rarity, $productId]);

    setFlash('success', "Updated. Added $addStock units. Market value set to " . formatPrice($marketValue));
    header('Location: ' . APP_URL . '/admin/products');
    exit;
}

// Handle toggle reward active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_reward'])) {
    $productId = (int)$_POST['product_id'];
    $db->prepare('UPDATE products SET is_active_reward = NOT is_active_reward WHERE id = ?')
       ->execute([$productId]);
    header('Location: ' . APP_URL . '/admin/products');
    exit;
}

// Handle add new product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name        = sanitize($_POST['name']         ?? '');
    $brand       = sanitize($_POST['brand']        ?? '');
    $category    = sanitize($_POST['category']     ?? '');
    $marketValue = (float)($_POST['market_value']  ?? 0);
    $rarity      = sanitize($_POST['rarity']       ?? 'common');
    $weight      = (float)($_POST['weight']        ?? 5);
    $stock       = (int)($_POST['stock']           ?? 0);
    $boxIds      = $_POST['box_ids'] ?? [];

    if ($name && $marketValue > 0) {
        // price column = market_value for consistency
        $db->prepare('
            INSERT INTO products (name,brand,category,price,market_value,rarity,weight,stock)
            VALUES (?,?,?,?,?,?,?,?)
        ')->execute([$name,$brand,$category,$marketValue,$marketValue,$rarity,$weight,$stock]);
        $newId = $db->lastInsertId();

        foreach ($boxIds as $boxId) {
            $db->prepare('INSERT IGNORE INTO box_product_pool (box_id,product_id) VALUES (?,?)')
               ->execute([(int)$boxId, $newId]);
        }
        setFlash('success', "Product '$name' added.");
    } else {
        setFlash('error', 'Name and market value are required.');
    }
    header('Location: ' . APP_URL . '/admin/products');
    exit;
}

$products = $db->query('
    SELECT p.*,
           COALESCE(sold.units_sold, 0) AS units_sold
    FROM products p
    LEFT JOIN (
        SELECT oi.product_id, COUNT(*) AS units_sold
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        WHERE o.payment_status = "paid"
        GROUP BY oi.product_id
    ) sold ON sold.product_id = p.id
    WHERE p.is_active = 1
    ORDER BY p.stock ASC, p.weight DESC
')->fetchAll();

$boxes    = $db->query('SELECT * FROM boxes ORDER BY price ASC')->fetchAll();
$lowCount = (int)$db->query('SELECT COUNT(*) FROM products WHERE stock <= 3 AND is_active=1')->fetchColumn();
$outCount = (int)$db->query('SELECT COUNT(*) FROM products WHERE stock = 0 AND is_active=1')->fetchColumn();

require_once __DIR__ . '/../../components/admin_header.php';
?>
<div class="admin-layout">
<?php require_once __DIR__ . '/../../components/admin_sidebar.php'; ?>
<main class="admin-main">

<div class="admin-topbar">
    <div>
        <h1 class="admin-page-title">Products</h1>
        <p class="admin-page-sub">
            <?= count($products) ?> products
            <?php if ($outCount > 0): ?>
            &nbsp;·&nbsp;<span style="color:var(--error)">🔴 <?= $outCount ?> out of stock</span>
            <?php endif; ?>
            <?php if ($lowCount > 0): ?>
            &nbsp;·&nbsp;<span style="color:var(--warning)">⚠️ <?= $lowCount ?> low stock</span>
            <?php endif; ?>
        </p>
    </div>
    <div style="display:flex;gap:.65rem">
        <a href="<?= APP_URL ?>/admin/accounting" class="btn-outline btn-sm">📊 Accounting</a>
        <button class="btn-primary" onclick="toggleSection('addNewForm')">+ Add New Product</button>
    </div>
</div>

<!-- ── UPDATE EXISTING ── -->
<div class="glass-card admin-form-card">
    <h3 class="chart-title">Update Product</h3>
    <p style="color:var(--text-muted);font-size:.8rem;margin-bottom:1.1rem">
        Select a product to update its stock, market value, or rarity.
        Click any row in the table below to auto-select.
    </p>

    <div class="form-group" style="margin-bottom:1rem">
        <label class="form-label">Select Product</label>
        <select class="form-input form-select" id="productSelector" onchange="fillProduct(this)">
            <option value="">— Choose a product —</option>
            <?php foreach ($products as $p): ?>
            <option value="<?= $p['id'] ?>"
                    data-name="<?= e($p['name']) ?>"
                    data-brand="<?= e($p['brand']) ?>"
                    data-market="<?= $p['market_value'] ?>"
                    data-rarity="<?= e($p['rarity']) ?>"
                    data-stock="<?= $p['stock'] ?>"
                    data-sold="<?= $p['units_sold'] ?>"
                    data-reward="<?= $p['is_active_reward'] ?>"
                    <?= $p['stock'] <= 3 ? 'class="low-stock-option"' : '' ?>>
                <?= e($p['name']) ?>
                (Stock: <?= $p['stock'] ?><?= $p['stock'] <= 3 ? ' ⚠️' : '' ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="productInfo" style="display:none">
        <!-- Read-only info panel -->
        <div class="product-info-grid">
            <div class="pi-item"><span class="pi-label">Name</span><span class="pi-value" id="piName">—</span></div>
            <div class="pi-item"><span class="pi-label">Brand</span><span class="pi-value" id="piBrand">—</span></div>
            <div class="pi-item"><span class="pi-label">Current Stock</span><span class="pi-value" id="piStock">—</span></div>
            <div class="pi-item"><span class="pi-label">Units Sold</span><span class="pi-value" id="piSold">—</span></div>
            <div class="pi-item"><span class="pi-label">Market Value</span><span class="pi-value" id="piMarket">—</span></div>
            <div class="pi-item"><span class="pi-label">Reward Status</span><span class="pi-value" id="piReward">—</span></div>
        </div>

        <!-- Update form -->
        <form method="POST" action="<?= APP_URL ?>/admin/products">
            <input type="hidden" name="product_id" id="updateProductId">
            <div class="update-row-v2">
                <div class="form-group">
                    <label class="form-label">Units to Add</label>
                    <input class="form-input" type="number" name="add_stock"
                           id="updateAddStock" min="0" value="0" placeholder="0">
                    <span style="font-size:.7rem;color:var(--text-muted)">Enter 0 to keep current stock</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Market Value (Rs.)</label>
                    <input class="form-input" type="number" name="market_value"
                           id="updateMarketValue" min="0" step="1"
                           placeholder="e.g. 18000">
                    <span style="font-size:.7rem;color:var(--text-muted)">Update if price has changed</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Rarity</label>
                    <select class="form-input form-select" name="rarity" id="updateRarity">
                        <option value="common">Common</option>
                        <option value="rare">Rare</option>
                        <option value="ultra_rare">Ultra Rare</option>
                        <option value="legendary">Legendary</option>
                    </select>
                </div>
                <div class="form-group update-btn-group">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" name="update_product" class="btn-primary update-save-btn">
                        💾 Save Changes
                    </button>
                </div>
            </div>
        </form>

        <!-- Toggle reward -->
        <form method="POST" action="<?= APP_URL ?>/admin/products" style="margin-top:.75rem">
            <input type="hidden" name="product_id" id="toggleProductId">
            <button type="submit" name="toggle_reward" class="btn-outline btn-sm" id="toggleRewardBtn">
                Toggle Reward Active/Paused
            </button>
        </form>
    </div>
</div>

<!-- ── ADD NEW PRODUCT ── -->
<div id="addNewForm" class="glass-card admin-form-card" style="display:none">
    <h3 class="chart-title">Add New Product</h3>
    <form method="POST" action="<?= APP_URL ?>/admin/products">
        <div class="admin-form-grid">
            <div class="form-group">
                <label class="form-label">Product Name *</label>
                <input class="form-input" type="text" name="name" placeholder="e.g. Sony WH-1000XM5" required>
            </div>
            <div class="form-group">
                <label class="form-label">Brand</label>
                <input class="form-input" type="text" name="brand" placeholder="e.g. Sony">
            </div>
            <div class="form-group">
                <label class="form-label">Category</label>
                <input class="form-input" type="text" name="category" placeholder="e.g. Audio">
            </div>
            <div class="form-group">
                <label class="form-label">Market Value (Rs.) *</label>
                <input class="form-input" type="number" name="market_value"
                       placeholder="e.g. 18000" required min="1">
                <span style="font-size:.72rem;color:var(--text-muted)">What this product is worth in the market</span>
            </div>
            <div class="form-group">
                <label class="form-label">Rarity</label>
                <select class="form-input form-select" name="rarity">
                    <option value="common">Common</option>
                    <option value="rare">Rare</option>
                    <option value="ultra_rare">Ultra Rare</option>
                    <option value="legendary">Legendary</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Weight (drop probability)</label>
                <input class="form-input" type="number" name="weight" step="0.1" value="5" min="0.01">
            </div>
            <div class="form-group">
                <label class="form-label">Initial Stock</label>
                <input class="form-input" type="number" name="stock" value="10" min="0">
            </div>
        </div>
        <div class="form-group" style="margin-bottom:1rem">
            <label class="form-label">Add to Boxes</label>
            <div class="box-checkboxes">
                <?php foreach ($boxes as $box): ?>
                <label class="box-checkbox-label">
                    <input type="checkbox" name="box_ids[]" value="<?= $box['id'] ?>">
                    <?= e($box['name']) ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="display:flex;gap:.75rem">
            <button type="submit" name="add_product" class="btn-primary">Add Product</button>
            <button type="button" class="btn-outline" onclick="toggleSection('addNewForm')">Cancel</button>
        </div>
    </form>
</div>

<!-- ── PRODUCTS TABLE ── -->
<div class="admin-table-card glass-card">
    <h3 class="chart-title">
        All Products
        <span style="color:var(--text-muted);font-size:.74rem;font-weight:400;margin-left:.5rem">
            — Click any row to quick-select above
        </span>
    </h3>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Brand</th>
                    <th>Rarity</th>
                    <th>Market Value</th>
                    <th>Stock</th>
                    <th>Sold</th>
                    <th>Reward</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
            <tr class="<?= $p['stock'] <= 3 ? 'row-lowstock' : '' ?>"
                style="cursor:pointer"
                onclick="selectProduct(<?= $p['id'] ?>)">
                <td>
                    <strong><?= e($p['name']) ?></strong>
                    <?php if ($p['stock'] == 0): ?><span class="out-of-stock-badge">OUT</span>
                    <?php elseif ($p['stock'] <= 3): ?><span class="low-stock-badge">LOW</span>
                    <?php endif; ?>
                </td>
                <td><?= e($p['brand']) ?></td>
                <td><span class="rarity-pill rarity-pill--<?= e($p['rarity']) ?>">
                    <?= ucfirst(str_replace('_',' ',$p['rarity'])) ?>
                </span></td>
                <td style="font-weight:600"><?= formatPrice($p['market_value']) ?></td>
                <td style="color:<?= $p['stock']==0?'var(--error)':($p['stock']<=3?'var(--warning)':'var(--success)') ?>;font-weight:700">
                    <?= $p['stock'] ?>
                </td>
                <td><?= $p['units_sold'] ?></td>
                <td>
                    <span class="status-badge <?= $p['is_active_reward']?'status-badge--delivered':'status-badge--cancelled' ?>">
                        <?= $p['is_active_reward'] ? '✓ Active' : '✗ Paused' ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
</div>

<script>
const PRODUCTS = <?= json_encode(array_column($products, null, 'id')) ?>;

function selectProduct(id) {
    const sel = document.getElementById('productSelector');
    sel.value = id;
    fillProduct(sel);
    document.querySelector('.admin-form-card').scrollIntoView({behavior:'smooth',block:'start'});
}

function fillProduct(sel) {
    const id   = sel.value;
    const info = document.getElementById('productInfo');
    if (!id) { info.style.display = 'none'; return; }

    const p = PRODUCTS[id];
    if (!p) return;

    document.getElementById('piName').textContent   = p.name;
    document.getElementById('piBrand').textContent  = p.brand;
    document.getElementById('piStock').textContent  = p.stock + ' units';
    document.getElementById('piSold').textContent   = p.units_sold + ' units';
    document.getElementById('piMarket').textContent = 'Rs. ' + Number(p.market_value).toLocaleString();
    document.getElementById('piReward').textContent = p.is_active_reward == 1 ? '✅ Active' : '🔴 Paused';

    document.getElementById('updateProductId').value  = p.id;
    document.getElementById('updateMarketValue').value = p.market_value;
    document.getElementById('updateRarity').value     = p.rarity;
    document.getElementById('updateAddStock').value   = 0;
    document.getElementById('toggleProductId').value  = p.id;
    document.getElementById('toggleRewardBtn').textContent =
        p.is_active_reward == 1 ? '🔴 Pause Reward' : '✅ Activate Reward';

    info.style.display = 'block';
}

function toggleSection(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php require_once __DIR__ . '/../../components/admin_footer.php'; ?>