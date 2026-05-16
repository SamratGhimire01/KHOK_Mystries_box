<?php
// admin/pages/products.php — K HO K Admin Products v2
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();
requireAdmin();

$pageTitle = 'Admin — Products';
$db = getDB();

// Handle stock update for existing product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $productId = (int)$_POST['product_id'];
    $stock     = (int)$_POST['stock'];
    $rarity    = sanitize($_POST['rarity'] ?? '');
    $db->prepare('UPDATE products SET stock=?, rarity=? WHERE id=?')
       ->execute([$stock, $rarity, $productId]);
    setFlash('success', 'Product updated.');
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
    $name     = sanitize($_POST['name']     ?? '');
    $brand    = sanitize($_POST['brand']    ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $price    = (float)($_POST['price']     ?? 0);
    $rarity   = sanitize($_POST['rarity']   ?? 'common');
    $weight   = (float)($_POST['weight']    ?? 5);
    $stock    = (int)($_POST['stock']       ?? 0);
    $boxIds   = $_POST['box_ids'] ?? [];

    if ($name && $price > 0) {
        $db->prepare('INSERT INTO products (name,brand,category,price,rarity,weight,stock) VALUES (?,?,?,?,?,?,?)')
           ->execute([$name,$brand,$category,$price,$rarity,$weight,$stock]);
        $newId = $db->lastInsertId();

        // Assign to selected boxes
        foreach ($boxIds as $boxId) {
            $db->prepare('INSERT IGNORE INTO box_product_pool (box_id,product_id) VALUES (?,?)')
               ->execute([(int)$boxId, $newId]);
        }
        setFlash('success', "Product '$name' added.");
    } else {
        setFlash('error', 'Name and price are required.');
    }
    header('Location: ' . APP_URL . '/admin/products');
    exit;
}

// Get all products sorted: low stock first, then by rarity weight
$products = $db->query('
    SELECT p.*, GROUP_CONCAT(bpp.box_id) AS box_ids
    FROM products p
    LEFT JOIN box_product_pool bpp ON bpp.product_id = p.id
    GROUP BY p.id
    ORDER BY p.stock ASC, p.weight DESC
')->fetchAll();

$boxes    = $db->query('SELECT * FROM boxes ORDER BY price ASC')->fetchAll();
$lowCount = $db->query('SELECT COUNT(*) FROM products WHERE stock <= 3 AND is_active=1')->fetchColumn();

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
                &nbsp;·&nbsp;
                <span style="color:var(--error)">⚠️ <?= $lowCount ?> low stock</span>
                <?php endif; ?>
            </p>
        </div>
        <button class="btn-primary" onclick="toggleSection('addNewForm')">+ Add New Product</button>
    </div>

    <!-- ── UPDATE EXISTING PRODUCT ── -->
    <div class="glass-card admin-form-card">
        <h3 class="chart-title">Update Existing Product Stock &amp; Rarity</h3>
        <p style="color:var(--text-muted);font-size:.82rem;margin-bottom:1.25rem">
            Select a product to auto-fill its details, then update stock or rarity.
        </p>

        <!-- Product selector -->
        <div class="form-group" style="margin-bottom:1rem">
            <label class="form-label">Select Product</label>
            <select class="form-input form-select" id="productSelector" onchange="fillProduct(this)">
                <option value="">— Choose a product —</option>
                <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>"
                        data-name="<?= e($p['name']) ?>"
                        data-brand="<?= e($p['brand']) ?>"
                        data-category="<?= e($p['category']) ?>"
                        data-price="<?= $p['price'] ?>"
                        data-rarity="<?= e($p['rarity']) ?>"
                        data-weight="<?= $p['weight'] ?>"
                        data-stock="<?= $p['stock'] ?>"
                        data-active="<?= $p['is_active'] ?>"
                        <?= $p['stock'] <= 3 ? 'class="low-stock-option"' : '' ?>>
                    <?= e($p['name']) ?>
                    (Stock: <?= $p['stock'] ?>)
                    <?= $p['stock'] <= 3 ? '⚠️' : '' ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Auto-filled info (read only) -->
        <div id="productInfo" style="display:none">
            <div class="product-info-grid">
                <div class="pi-item"><span class="pi-label">Name</span><span class="pi-value" id="piName">—</span></div>
                <div class="pi-item"><span class="pi-label">Brand</span><span class="pi-value" id="piBrand">—</span></div>
                <div class="pi-item"><span class="pi-label">Category</span><span class="pi-value" id="piCategory">—</span></div>
                <div class="pi-item"><span class="pi-label">Unit Price</span><span class="pi-value" id="piPrice">—</span></div>
                <div class="pi-item"><span class="pi-label">Weight</span><span class="pi-value" id="piWeight">—</span></div>
                <div class="pi-item"><span class="pi-label">Status</span><span class="pi-value" id="piActive">—</span></div>
            </div>

            <form method="POST" action="<?= APP_URL ?>/admin/products" class="update-stock-form">
                <input type="hidden" name="product_id" id="updateProductId">
                <div class="update-row">
                    <div class="form-group">
                        <label class="form-label">Stock</label>
                        <input class="form-input" type="number" name="stock" id="updateStock" min="0" required>
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
                        <button type="submit" name="update_stock" class="btn-primary update-save-btn">
                            💾 Save Changes
                        </button>
                    </div>
                </div>
            </form>

            <form method="POST" action="<?= APP_URL ?>/admin/products" style="margin-top:.75rem">
                <input type="hidden" name="product_id" id="toggleProductId">
                <button type="submit" name="toggle_active" class="btn-outline btn-sm" id="toggleActiveBtn">
                    Toggle Active/Hidden
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
                    <label class="form-label">Price (Rs.) *</label>
                    <input class="form-input" type="number" name="price" placeholder="18000" required min="1">
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
            <button type="submit" name="add_product" class="btn-primary">Add Product</button>
            <button type="button" class="btn-outline" onclick="toggleSection('addNewForm')" style="margin-left:.75rem">Cancel</button>
        </form>
    </div>

    <!-- ── PRODUCTS TABLE ── -->
    <div class="admin-table-card glass-card">
        <h3 class="chart-title" style="margin-bottom:1rem">
            All Products
            <span style="color:var(--text-muted);font-size:.78rem;font-weight:400;margin-left:.5rem">
                — Low stock shown first
            </span>
        </h3>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Rarity</th>
                        <th>Weight</th>
                        <th>Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                <tr class="<?= !$p['is_active'] ? 'row-inactive' : '' ?> <?= $p['stock'] <= 3 ? 'row-lowstock' : '' ?>">
                    <td>
                        <strong><?= e($p['name']) ?></strong>
                        <?php if ($p['stock'] == 0): ?>
                        <span class="out-of-stock-badge">OUT</span>
                        <?php elseif ($p['stock'] <= 3): ?>
                        <span class="low-stock-badge">LOW</span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($p['brand']) ?></td>
                    <td><?= formatPrice($p['price']) ?></td>
                    <td><span class="rarity-pill rarity-pill--<?= e($p['rarity']) ?>"><?= ucfirst(str_replace('_',' ',$p['rarity'])) ?></span></td>
                    <td><?= $p['weight'] ?></td>
                    <td>
                        <span style="color:<?= $p['stock'] == 0 ? 'var(--error)' : ($p['stock'] <= 3 ? 'var(--warning)' : 'var(--success)') ?>;font-weight:600">
                            <?= $p['stock'] ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?= $p['is_active'] ? 'status-badge--delivered' : 'status-badge--cancelled' ?>">
                            <?= $p['is_active'] ? 'Active' : 'Hidden' ?>
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
function fillProduct(sel) {
    const opt = sel.options[sel.selectedIndex];
    const info = document.getElementById('productInfo');
    if (!opt.value) { info.style.display='none'; return; }

    document.getElementById('piName').textContent     = opt.dataset.name;
    document.getElementById('piBrand').textContent    = opt.dataset.brand;
    document.getElementById('piCategory').textContent = opt.dataset.category;
    document.getElementById('piPrice').textContent    = 'Rs. ' + Number(opt.dataset.price).toLocaleString();
    document.getElementById('piWeight').textContent   = opt.dataset.weight;
    document.getElementById('piActive').textContent   = opt.dataset.active === '1' ? '✅ Active' : '🔴 Hidden';

    document.getElementById('updateProductId').value  = opt.value;
    document.getElementById('updateStock').value      = opt.dataset.stock;
    document.getElementById('updateRarity').value     = opt.dataset.rarity;
    document.getElementById('toggleProductId').value  = opt.value;
    document.getElementById('toggleActiveBtn').textContent =
        opt.dataset.active === '1' ? '🔴 Set Hidden' : '✅ Set Active';

    info.style.display = 'block';
}

function toggleSection(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
<?php require_once __DIR__ . '/../../components/admin_footer.php'; ?>