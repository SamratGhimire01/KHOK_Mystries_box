<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/payment.php';
require_once __DIR__ . '/core/helpers.php';

startSession();

// Log everything for debugging
file_put_contents('/tmp/esewa_return.txt',
    date('H:i:s') . ' GET:' . json_encode($_GET) . "\n", FILE_APPEND);

// Get ref — clean it from any appended data
$rawRef  = $_GET['ref'] ?? '';
$orderRef = sanitize(explode('?', $rawRef)[0]);

// Get data param
$encodedData  = $_GET['data'] ?? '';
$responseData = [];
$status       = '';

if (!empty($encodedData)) {
    $decoded      = base64_decode($encodedData);
    $responseData = json_decode($decoded, true) ?? [];
    $status       = $responseData['status'] ?? '';
}

if (empty($orderRef)) {
    header('Location: ' . APP_URL . '/payment/failed');
    exit;
}

$db    = getDB();
$order = $db->prepare('SELECT * FROM orders WHERE order_ref = ?');
$order->execute([$orderRef]);
$order = $order->fetch();

if (!$order) {
    header('Location: ' . APP_URL . '/payment/failed');
    exit;
}

// TEST MODE: always succeed
// LIVE MODE: check status === 'COMPLETE'
$success = (ESEWA_MODE === 'test') ? true : ($status === 'COMPLETE');

if ($success) {
    $db->prepare('
        UPDATE orders
        SET payment_status = "paid", order_status = "confirmed"
        WHERE order_ref = ? AND payment_status = "pending"
    ')->execute([$orderRef]);

    $db->prepare('
        UPDATE delivery_tracking
        SET status = "confirmed"
        WHERE order_id = ?
    ')->execute([$order['id']]);

    header('Location: ' . APP_URL . '/payment/success?ref=' . urlencode($orderRef));
    exit;
} else {
    $db->prepare('UPDATE orders SET payment_status = "failed" WHERE order_ref = ?')
       ->execute([$orderRef]);
    header('Location: ' . APP_URL . '/payment/failed?ref=' . urlencode($orderRef));
    exit;
}