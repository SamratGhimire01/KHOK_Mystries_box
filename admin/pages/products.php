<?php
// admin/pages/products.php — K HO K Admin Products
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();
requireAdmin();

$pageTitle = 'Admin — Products';
$db = getDB();

// Handle add product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name     = sanitize($_POST['name']     ?? '');
    $brand    = sanitize($_POST['brand']    ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $price    = (float)($_POST['price']     ?? 0);
    $rarity   = sanitize($_POST['rarity']   ?? 'common');
    $weight   = (float)($_POST['weight']    ?? 1);
    $stock    = (int)($_POST['stock']       ?? 0);

    if ($name && $price > 0) {
        $db->prepare('INSERT INTO products (name,brand,category,price,rarity,weight,stock) VALUES (?,?,?,?,?,?,?)')
           ->execute([$name,$brand,$category,$price,$rarity,$weight,$stock]);
        setFlash('success', "Product '$name' added successfully.");
    } else {
        setFlash('error', 'Name and price are required.');
    }
    header('Location: ' . APP_URL . '/admin/products');
    exit;
}

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $productId = (int)$_POST['product_id'];
    $stock     = (int)$_POST['stock'];
    $db->prepare('UPDATE products SET stock = ? WHERE id = ?')->execute([$stock, $productId]);
    setFlash('success', 'Stock updated.');
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

$products = $db->query('SELECT * FROM products ORDER BY rarity, weight DESC')->fetchAll();

require_once __DIR__ . '/../../components/admin_header.php';
?>
<div class="admin-layout">
<?php require_once __DIR__ . '/../../components/admin_sidebar.php'; ?>
<main class="admin-main">

    <div class="admin-topbar">
        <div>
            <h1 class="admin-page-title">Products</h1>
            <p class="admin-page-sub"><?= count($products) ?> products in pool</p>
        </div>
        <button class="btn-primary" onclick="toggleAddForm()">+ Add Product</button>
    </div>

    <!-- Add product form -->
    <div id="addProductForm" class="glass-card admin-form-card" style="display:none">
        <h3 class="chart-title">Add New Product</h3>
        <form method="POST" action="<?= APP_URL ?>/admin/products">
            <div class="admin-form-grid">
                <div class="form-group">
                    <label class="form-label">Product Name *</label>
                    <input class="form-input" type="text" name="name" placeholder="e.g. Apple AirPods 3rd Gen" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Brand</label>
                    <input class="form-input" type="text" name="brand" placeholder="e.g. Apple">
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
                    <label class="form-label">Stock</label>
                    <input class="form-input" type="number" name="stock" value="10" min="0">
                </div>
            </div>
            <button type="submit" name="add_product" class="btn-primary">Add Product</button>
            <button type="button" class="btn-outline" onclick="toggleAddForm()" style="margin-left:.75rem">Cancel</button>
        </form>
    </div>

    <!-- Products table -->
    <div class="admin-table-card glass-card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Rarity</th>
                        <th>Weight</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                <tr class="<?= !$p['is_active'] ? 'row-inactive' : '' ?>">
                    <td><strong><?= e($p['name']) ?></strong></td>
                    <td><?= e($p['brand']) ?></td>
                    <td><?= e($p['category']) ?></td>
                    <td><?= formatPrice($p['price']) ?></td>
                    <td><span class="rarity-pill rarity-pill--<?= e($p['rarity']) ?>"><?= ucfirst(str_replace('_',' ',$p['rarity'])) ?></span></td>
                    <td><?= $p['weight'] ?></td>
                    <td>
                        <form method="POST" action="<?= APP_URL ?>/admin/products" class="inline-form">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input class="form-input inline-stock" type="number"
                                   name="stock" value="<?= $p['stock'] ?>" min="0" style="width:65px">
                            <button type="submit" name="update_stock" class="tbl-action-btn">Set</button>
                        </form>
                    </td>
                    <td>
                        <span class="status-badge <?= $p['is_active'] ? 'status-badge--delivered' : 'status-badge--cancelled' ?>">
                            <?= $p['is_active'] ? 'Active' : 'Hidden' ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="<?= APP_URL ?>/admin/products" class="inline-form">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <button type="submit" name="toggle_active" class="tbl-action">
                                <?= $p['is_active'] ? 'Hide' : 'Show' ?>
                            </button>
                        </form>
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
function toggleAddForm() {
    const f = document.getElementById('addProductForm');
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
}
</script>
<?php require_once __DIR__ . '/../../components/admin_footer.php'; ?>