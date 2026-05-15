<?php
// pages/profile.php — K HO K Profile (editable)
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();
requireLogin();

$pageTitle = 'My Profile';
$pageCSS   = 'profile.css';
$pageJS    = 'profile.js';

$db   = getDB();
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user) { session_destroy(); redirect('/login'); }

$orders = $db->prepare('
    SELECT o.*, b.name AS box_name, b.slug AS box_slug,
           dt.status AS delivery_status
    FROM orders o
    JOIN boxes b ON o.box_id = b.id
    LEFT JOIN delivery_tracking dt ON dt.order_id = o.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC LIMIT 20
');
$orders->execute([$_SESSION['user_id']]);
$userOrders = $orders->fetchAll();

require_once __DIR__ . '/../components/header.php';
?>

<section class="profile-section">
  <div class="container">
    <div class="profile-grid">

      <!-- ── SIDEBAR ── -->
      <div class="profile-sidebar">

        <!-- Avatar card -->
        <div class="profile-card glass-card">
          <div class="profile-avatar">
            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
          </div>
          <h2 class="profile-name" id="displayName"><?= e($user['full_name']) ?></h2>
          <p class="profile-email"><?= e($user['email']) ?></p>
          <span class="profile-role profile-role--<?= e($user['role']) ?>">
            <?= strtoupper($user['role']) ?>
          </span>

          <div class="profile-actions">
            <button class="btn-primary btn-full" id="editToggleBtn" onclick="toggleEdit()">
              ✏️ Edit Profile
            </button>
            <a href="<?= APP_URL ?>/boxes" class="btn-outline btn-full">Open a Box</a>
            <a href="<?= APP_URL ?>/logout" class="btn-ghost btn-full">Logout</a>
          </div>
        </div>

        <!-- Stats -->
        <div class="profile-stats glass-card">
          <div class="pstat">
            <span class="pstat-number"><?= count($userOrders) ?></span>
            <span class="pstat-label">Orders</span>
          </div>
          <div class="pstat-divider"></div>
          <div class="pstat">
            <span class="pstat-number">
              <?= formatPrice(array_sum(array_column($userOrders, 'total_amount'))) ?>
            </span>
            <span class="pstat-label">Total Spent</span>
          </div>
        </div>
      </div>

      <!-- ── MAIN ── -->
      <div class="profile-main">

        <!-- VIEW mode -->
        <div id="viewMode">
          <h3 class="profile-section-title">Personal Details</h3>
          <div class="details-card glass-card">
            <div class="detail-row">
              <span class="detail-label">Full Name</span>
              <span class="detail-value" id="vName"><?= e($user['full_name']) ?></span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Phone</span>
              <span class="detail-value"><?= e($user['phone']) ?></span>
            </div>
            <div class="detail-row">
              <span class="detail-label">City</span>
              <span class="detail-value"><?= e($user['city'] ?: '—') ?></span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Address</span>
              <span class="detail-value"><?= e($user['address'] ?: '—') ?></span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Member Since</span>
              <span class="detail-value"><?= date('d M Y', strtotime($user['created_at'])) ?></span>
            </div>
          </div>
        </div>

        <!-- EDIT mode (hidden by default) -->
        <div id="editMode" style="display:none">
          <h3 class="profile-section-title">Edit Profile</h3>
          <div class="details-card glass-card">
            <form id="editForm">
              <div class="edit-row">
                <div class="form-group">
                  <label class="form-label">Full Name</label>
                  <input class="form-input" type="text" name="full_name"
                         id="editName" value="<?= e($user['full_name']) ?>"
                         placeholder="Letters only, no numbers" required>
                  <span class="field-hint">No numbers allowed</span>
                </div>
                <div class="form-group">
                  <label class="form-label">Phone / WhatsApp</label>
                  <input class="form-input" type="tel" name="phone"
                         id="editPhone" value="<?= e($user['phone']) ?>"
                         placeholder="97/98XXXXXXXX" required maxlength="10">
                  <span class="field-hint">10 digits, must start with 97 or 98</span>
                </div>
              </div>
              <div class="edit-row">
                <div class="form-group">
                  <label class="form-label">City</label>
                  <select class="form-input form-select" name="city" id="editCity">
                    <?php foreach (['Kathmandu','Lalitpur','Bhaktapur','Pokhara','Biratnagar','Birgunj','Butwal','Nepalgunj','Dharan','Other'] as $c): ?>
                    <option <?= $user['city'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Delivery Address</label>
                  <input class="form-input" type="text" name="address"
                         id="editAddress" value="<?= e($user['address']) ?>"
                         placeholder="Street / Area / Landmark">
                </div>
              </div>
              <div id="editError"   class="form-msg form-msg--error"   style="display:none"></div>
              <div id="editSuccess" class="form-msg form-msg--success" style="display:none"></div>
              <div class="edit-actions">
                <button type="submit" class="btn-primary" id="saveBtn">
                  <span class="btn-text">Save Changes</span>
                  <span class="btn-loader" style="display:none">Saving...</span>
                </button>
                <button type="button" class="btn-outline" onclick="toggleEdit()">Cancel</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Orders -->
        <h3 class="profile-section-title" style="margin-top:2rem">Order History</h3>

        <?php if (empty($userOrders)): ?>
        <div class="profile-empty glass-card">
          <div class="profile-empty-icon">📦</div>
          <p>No orders yet. Open your first mystery box!</p>
          <a href="<?= APP_URL ?>/boxes" class="btn-primary">Start the Mystery</a>
        </div>
        <?php else: ?>
        <div class="orders-list">
          <?php foreach ($userOrders as $order): ?>
          <div class="order-card glass-card">
            <div class="order-card-top">
              <div>
                <p class="order-ref"><?= e($order['order_ref']) ?></p>
                <p class="order-box"><?= e($order['box_name']) ?></p>
              </div>
              <div class="order-card-right">
                <span class="order-price"><?= formatPrice($order['total_amount']) ?></span>
                <span class="order-status order-status--<?= e($order['order_status']) ?>">
                  <?= ucfirst($order['order_status']) ?>
                </span>
              </div>
            </div>
            <div class="order-card-bottom">
              <span class="order-meta">💳 <?= strtoupper(e($order['payment_method'])) ?></span>
              <span class="order-meta">🚚 <?= ucfirst($order['delivery_status'] ?? 'pending') ?></span>
              <span class="order-meta">🗓 <?= date('d M Y', strtotime($order['created_at'])) ?></span>
              <a href="<?= APP_URL ?>/track?ref=<?= e($order['order_ref']) ?>"
                 class="order-track-link">Track →</a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

      </div><!-- end profile-main -->
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../components/footer.php'; ?>