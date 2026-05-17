<?php
// admin/pages/products.php — v3 with buying price + accounting integration
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();
requireAdmin();

$pageTitle = 'Admin — Products';
$db = getDB();

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $productId   = (int)$_POST['product_id'];
    $addStock    = (int)$_POST['add_stock'];      // units being added
    $buyingPrice = (float)$_POST['buying_price']; // price admin paid per unit
    $rarity      = sanitize($_POST['rarity'] ?? '');

    if ($addStock > 0) {
        $db->prepare('
            UPDATE products
            SET stock = stock + ?,
                buying_price = ?,
                rarity = ?
            WHERE id = ?
        ')->execute([$addStock, $buyingPrice, $rarity, $productId]);

        // Calculate investment added
        $investment = $addStock * $buyingPrice;
        setFlash('success', "Added $addStock units. Investment added: " . formatPrice($investment));
    } else {
        setFlash('error', 'Units to add must be greater than 0.');
    }
    header('Location: ' . APP_URL . '/admin/products');
    exit;
}

// Handle toggle active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    $productId = (int)$_POST['product_id'];
    $db->prepare('UPDATE products SET is_active = NOT is_active WHERE id = ?')->execute([$productId]);
    header('Location: ' . APP_URL . '/admin/products');
    exit;
}

// Handle add new product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name        = sanitize($_POST['name']        ?? '');
    $brand       = sanitize($_POST['brand']       ?? '');
    $category    = sanitize($_POST['category']    ?? '');
    $price       = (float)($_POST['price']        ?? 0);
    $buyingPrice = (float)($_POST['buying_price'] ?? 0);
    $rarity      = sanitize($_POST['rarity']      ?? 'common');
    $weight      = (float)($_POST['weight']       ?? 5);
    $stock       = (int)($_POST['stock']          ?? 0);
    $boxIds      = $_POST['box_ids'] ?? [];

    if ($name && $price > 0) {
        $db->prepare('
            INSERT INTO products (name,brand,category,price,buying_price,rarity,weight,stock)
            VALUES (?,?,?,?,?,?,?,?)
        ')->execute([$name,$brand,$category,$price,$buyingPrice,$rarity,$weight,$stock]);
        $newId = $db->lastInsertId();

        foreach ($boxIds as $boxId) {
            $db->prepare('INSERT IGNORE INTO box_product_pool (box_id,product_id) VALUES (?,?)')
               ->execute([(int)$boxId, $newId]);
        }
        $investment = $stock * $buyingPrice;
        setFlash('success', "Product '$name' added. Initial investment: " . formatPrice($investment));
    } else {
        setFlash('error', 'Name and price are required.');
    }
    header('Location: ' . APP_URL . '/admin/products');
    exit;
}

$products = $db->query('
    SELECT p.*,
           COUNT(oi.id) AS units_sold
    FROM products p
    LEFT JOIN order_items oi ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY p.stock ASC, p.weight DESC
')->fetchAll();

$boxes    = $db->query('SELECT * FROM boxes ORDER BY price ASC')->fetchAll();
$lowCount = $db->query('SELECT COUNT(*) FROM products WHERE stock <= 3 AND is_active=1')->fetchColumn();

// Quick accounting summary for top
$totalInvestment = $db->query('SELECT COALESCE(SUM(buying_price * stock),0) FROM products WHERE is_active=1')->fetchColumn();
$totalRevenue    = $db->query('SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status="paid"')->fetchColumn();

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
                <?php if ($lowCount > 0): ?>
                &nbsp;·&nbsp;<span style="color:var(--error)">⚠️ <?= $lowCount ?> low stock</span>
                <?php endif; ?>
                &nbsp;·&nbsp;
                <span style="color:var(--text-muted)">
                    Stock investment: <strong style="color:var(--accent)"><?= formatPrice($totalInvestment) ?></strong>
                </span>
            </p>
        </div>
        <div style="display:flex;gap:.65rem">
            <a href="<?= APP_URL ?>/admin/accounting" class="btn-outline">📊 View Accounting</a>
            <button class="btn-primary" onclick="toggleSection('addNewForm')">+ Add New Product</button>
        </div>
    </div>

    <!-- ── UPDATE EXISTING PRODUCT ── -->
    <div class="glass-card admin-form-card">
        <h3 class="chart-title">Update Product Stock</h3>
        <p style="color:var(--text-muted);font-size:.8rem;margin-bottom:1.1rem">
            Select product → enter units to add + buying price → investment auto-calculates.
        </p>

        <div class="form-group" style="margin-bottom:1rem">
            <label class="form-label">Select Product</label>
            <select class="form-input form-select" id="productSelector" onchange="fillProduct(this)">
                <option value="">— Choose a product —</option>
                <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>"
                        data-name="<?= e($p['name']) ?>"
                        data-brand="<?= e($p['brand']) ?>"
                        data-price="<?= $p['price'] ?>"
                        data-buying="<?= $p['buying_price'] ?>"
                        data-rarity="<?= e($p['rarity']) ?>"
                        data-stock="<?= $p['stock'] ?>"
                        data-sold="<?= $p['units_sold'] ?>"
                        data-active="<?= $p['is_active'] ?>"
                        <?= $p['stock'] <= 3 ? 'class="low-stock-option"':'' ?>>
                    <?= e($p['name']) ?> (Stock: <?= $p['stock'] ?><?= $p['stock']<=3?' ⚠️':'' ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="productInfo" style="display:none">
            <!-- Read-only info -->
            <div class="product-info-grid">
                <div class="pi-item"><span class="pi-label">Name</span><span class="pi-value" id="piName">—</span></div>
                <div class="pi-item"><span class="pi-label">Brand</span><span class="pi-value" id="piBrand">—</span></div>
                <div class="pi-item"><span class="pi-label">Selling Price</span><span class="pi-value" id="piPrice">—</span></div>
                <div class="pi-item"><span class="pi-label">Current Stock</span><span class="pi-value" id="piStock">—</span></div>
                <div class="pi-item"><span class="pi-label">Units Sold</span><span class="pi-value" id="piSold">—</span></div>
                <div class="pi-item"><span class="pi-label">Status</span><span class="pi-value" id="piActive">—</span></div>
            </div>

            <form method="POST" action="<?= APP_URL ?>/admin/products" class="update-stock-form">
                <input type="hidden" name="product_id" id="updateProductId">
                <div class="update-row-v2">
                    <div class="form-group">
                        <label class="form-label">Units to Add *</label>
                        <input class="form-input" type="number" name="add_stock"
                               id="updateAddStock" min="1" placeholder="e.g. 3"
                               oninput="calcInvestment()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Your Buying Price (Rs.) *</label>
                        <input class="form-input" type="number" name="buying_price"
                               id="updateBuyingPrice" min="0" step="0.01" placeholder="e.g. 14000"
                               oninput="calcInvestment()">
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
                    <div class="form-group">
                        <label class="form-label">Investment Preview</label>
                        <div class="investment-preview" id="investmentPreview">
                            Enter units &amp; buying price
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:.75rem;align-items:center;margin-top:1rem">
                    <button type="submit" name="update_stock" class="btn-primary update-save-btn">
                        💾 Add Stock &amp; Save
                    </button>
                    <form method="POST" action="<?= APP_URL ?>/admin/products" style="margin:0">
                        <input type="hidden" name="product_id" id="toggleProductId">
                        <button type="submit" name="toggle_active" class="btn-outline btn-sm" id="toggleActiveBtn">
                            Toggle Active/Hidden
                        </button>
                    </form>
                </div>
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
                    <label class="form-label">Selling Price (Rs.) *</label>
                    <input class="form-input" type="number" name="price" placeholder="18000" required min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Your Buying Price (Rs.)</label>
                    <input class="form-input" type="number" name="buying_price" placeholder="14000" min="0" step="0.01">
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
                    <label class="form-label">Weight (probability)</label>
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
            <span style="color:var(--text-muted);font-size:.76rem;font-weight:400;margin-left:.5rem">
                — Low stock first · Click product name for quick edit
            </span>
        </h3>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Brand</th>
                        <th>Buying Price</th>
                        <th>Selling Price</th>
                        <th>Profit/Unit</th>
                        <th>Rarity</th>
                        <th>Stock</th>
                        <th>Sold</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p):
                    $profit = $p['price'] - $p['buying_price'];
                ?>
                <tr class="<?= !$p['is_active'] ? 'row-inactive':'' ?> <?= $p['stock']<=3 ? 'row-lowstock':'' ?>"
                    style="cursor:pointer"
                    onclick="document.getElementById('productSelector').value='<?= $p['id'] ?>';fillProductById(<?= $p['id'] ?>)">
                    <td>
                        <strong><?= e($p['name']) ?></strong>
                        <?php if ($p['stock']==0): ?><span class="out-of-stock-badge">OUT</span>
                        <?php elseif ($p['stock']<=3): ?><span class="low-stock-badge">LOW</span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($p['brand']) ?></td>
                    <td style="color:var(--error)">
                        <?= $p['buying_price'] > 0 ? formatPrice($p['buying_price']) : '<span style="color:var(--warning);font-size:.75rem">Not set</span>' ?>
                    </td>
                    <td style="color:var(--success)"><?= formatPrice($p['price']) ?></td>
                    <td style="color:<?= $profit>=0?'var(--success)':'var(--error)' ?>;font-weight:600">
                        <?= formatPrice($profit) ?>
                    </td>
                    <td><span class="rarity-pill rarity-pill--<?= e($p['rarity']) ?>"><?= ucfirst(str_replace('_',' ',$p['rarity'])) ?></span></td>
                    <td style="color:<?= $p['stock']==0?'var(--error)':($p['stock']<=3?'var(--warning)':'var(--success)') ?>;font-weight:600">
                        <?= $p['stock'] ?>
                    </td>
                    <td><?= $p['units_sold'] ?></td>
                    <td>
                        <span class="status-badge <?= $p['is_active']?'status-badge--delivered':'status-badge--cancelled' ?>">
                            <?= $p['is_active']?'Active':'Hidden' ?>
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
// Store all product data for quick lookup
const PRODUCTS = <?= json_encode(array_column($products, null, 'id')) ?>;

function fillProductById(id) {
    const sel = document.getElementById('productSelector');
    sel.value = id;
    fillProduct(sel);
    // Scroll to update form
    document.querySelector('.admin-form-card').scrollIntoView({behavior:'smooth'});
}

function fillProduct(sel) {
    const id   = sel.value;
    const info = document.getElementById('productInfo');
    if (!id) { info.style.display='none'; return; }

    const p = PRODUCTS[id];
    if (!p) return;

    document.getElementById('piName').textContent    = p.name;
    document.getElementById('piBrand').textContent   = p.brand;
    document.getElementById('piPrice').textContent   = 'Rs. ' + Number(p.price).toLocaleString();
    document.getElementById('piStock').textContent   = p.stock;
    document.getElementById('piSold').textContent    = p.units_sold;
    document.getElementById('piActive').textContent  = p.is_active == 1 ? '✅ Active' : '🔴 Hidden';

    document.getElementById('updateProductId').value  = p.id;
    document.getElementById('updateBuyingPrice').value = p.buying_price > 0 ? p.buying_price : '';
    document.getElementById('updateRarity').value     = p.rarity;
    document.getElementById('toggleProductId').value  = p.id;
    document.getElementById('toggleActiveBtn').textContent =
        p.is_active == 1 ? '🔴 Set Hidden' : '✅ Set Active';

    document.getElementById('updateAddStock').value = '';
    document.getElementById('investmentPreview').textContent = 'Enter units & buying price';

    info.style.display = 'block';
}

function calcInvestment() {
    const units  = parseFloat(document.getElementById('updateAddStock').value)  || 0;
    const price  = parseFloat(document.getElementById('updateBuyingPrice').value) || 0;
    const total  = units * price;
    const prev   = document.getElementById('investmentPreview');

    if (units > 0 && price > 0) {
        prev.textContent = `Rs. ${total.toLocaleString()} investment (${units} × Rs. ${price.toLocaleString()})`;
        prev.style.color = 'var(--accent)';
    } else {
        prev.textContent = 'Enter units & buying price';
        prev.style.color = '';
    }
}

function toggleSection(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php require_once __DIR__ . '/../../components/admin_footer.php'; ?>