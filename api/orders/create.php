<?php
// ─────────────────────────────────────────
//  K HO K — Create Order API
//  api/orders/create.php
// ─────────────────────────────────────────

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../api/engine/mystery_engine.php';

header('Content-Type: application/json');
startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// ── Inputs ──
$boxSlug       = sanitize($_POST['box_slug']       ?? '');
$customerName  = sanitize($_POST['customer_name']  ?? '');
$phone         = sanitize($_POST['phone']          ?? '');
$email         = sanitize($_POST['email']          ?? '');
$city          = sanitize($_POST['city']           ?? '');
$address       = sanitize($_POST['address']        ?? '');
$notes         = sanitize($_POST['notes']          ?? '');
$paymentMethod = sanitize($_POST['payment_method'] ?? 'esewa');

// ── Validation ──
$errors = [];

// Name: no numbers, min 2 chars
if (!preg_match('/^[a-zA-Z\s\'\-\.]{2,100}$/', $customerName)) {
    $errors[] = 'Name must be at least 2 characters and contain no numbers.';
}

// Phone: exactly 10 digits, starts with 97 or 98
if (!preg_match('/^(97|98)\d{8}$/', $phone)) {
    $errors[] = 'Phone must be 10 digits and start with 97 or 98.';
}

if (empty($city))    $errors[] = 'Please select your city.';
if (empty($address)) $errors[] = 'Please enter your delivery address.';

if (!in_array($paymentMethod, ['esewa', 'fonepay'])) {
    $errors[] = 'Invalid payment method.';
}

if (!empty($errors)) {
    jsonResponse(['success' => false, 'message' => implode(' ', $errors)]);
}

// ── Look up box ──
$db   = getDB();
$stmt = $db->prepare('SELECT * FROM boxes WHERE slug = ? AND is_active = 1');
$stmt->execute([$boxSlug]);
$box  = $stmt->fetch();

if (!$box) {
    jsonResponse(['success' => false, 'message' => 'Invalid box selected.']);
}

// ── Run mystery engine ──
$count    = getProductCount($box);
$products = selectMysteryProducts((int)$box['id'], $count);

if (empty($products)) {
    jsonResponse(['success' => false, 'message' => 'Sorry, this box is out of stock. Please try another.']);
}

// ── Create order in transaction ──
try {
    $db->beginTransaction();

    $orderRef = generateOrderId();
    $userId   = isLoggedIn() ? $_SESSION['user_id'] : null;

    // Insert order
    $stmt = $db->prepare('
        INSERT INTO orders
            (order_ref, user_id, box_id, customer_name, phone, address, city,
             total_amount, payment_method, payment_status, order_status, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $orderRef,
        $userId,
        $box['id'],
        $customerName,
        $phone,
        $address,
        $city,
        $box['price'],
        $paymentMethod,
        'pending',
        'placed',
        $notes
    ]);
    $orderId = $db->lastInsertId();

    // Insert order items (mystery products)
    $itemStmt = $db->prepare('
        INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, 1)
    ');
    foreach ($products as $product) {
        $itemStmt->execute([$orderId, $product['id']]);
    }

    // Create delivery tracking record
    $db->prepare('
        INSERT INTO delivery_tracking (order_id, status, estimated_date)
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 3 DAY))
    ')->execute([$orderId, 'confirmed']);

    // Deduct stock
    deductStock($products);

    $db->commit();

    // ── Redirect to payment ──
    $redirect = $paymentMethod === 'esewa'
        ? APP_URL . '/api/payment/esewa_init.php?order=' . urlencode($orderRef)
        : APP_URL . '/api/payment/fonepay_init.php?order=' . urlencode($orderRef);

    jsonResponse([
        'success'   => true,
        'order_ref' => $orderRef,
        'redirect'  => $redirect
    ]);

} catch (PDOException $e) {
    $db->rollBack();
    jsonResponse(['success' => false, 'message' => 'Order failed. Please try again.'], 500);
}