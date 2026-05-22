<?php
file_put_contents('/tmp/esewa_debug.txt', date('H:i:s') . ' ' . json_encode($_GET) . "\n", FILE_APPEND);

// ─────────────────────────────────────────
//  K HO K — eSewa Payment Verify
//  api/payment/esewa_verify.php
//  Called by success_url redirect from eSewa
// ─────────────────────────────────────────

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/payment.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();

// eSewa sends base64 encoded data in GET param
$encodedData = $_GET['data'] ?? '';

// Extract ref from URL path — handles both ?ref= and path-based
$orderRef = sanitize($_GET['ref'] ?? '');
if (empty($orderRef)) {
    // Try extracting from REQUEST_URI path
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $parts = explode('/', trim($path, '/'));
    $orderRef = sanitize(end($parts));
}

// Clean ref — remove any appended ?data= if present
if (strpos($orderRef, '?') !== false) {
    $orderRef = sanitize(explode('?', $orderRef)[0]);
}

// Decode eSewa data
if (!empty($encodedData)) {
    $decodedData  = base64_decode($encodedData);
    $responseData = json_decode($decodedData, true);
    $status       = $responseData['status'] ?? '';
} else {
    $status = '';
}

// TEST MODE — mark as paid directly
if (ESEWA_MODE === 'test' && !empty($orderRef)) {
    $db = getDB();
    $db->prepare('UPDATE orders SET payment_status="paid", order_status="confirmed" WHERE order_ref=? AND payment_status="pending"')->execute([$orderRef]);
    $db->prepare('UPDATE delivery_tracking SET status="confirmed" WHERE order_id=(SELECT id FROM orders WHERE order_ref=?)')->execute([$orderRef]);
    redirect('/payment/success?ref=' . urlencode($orderRef));
}

if (empty($encodedData) || empty($orderRef)) {
    setFlash('error', 'Invalid payment response.');
    redirect('/payment/failed');
}

// Decode eSewa response
$decodedData = base64_decode($encodedData);
$responseData = json_decode($decodedData, true);

if (!$responseData) {
    setFlash('error', 'Could not decode payment response.');
    redirect('/payment/failed');
}

$status          = $responseData['status']           ?? '';
$transactionUuid = $responseData['transaction_uuid'] ?? '';
$totalAmount     = $responseData['total_amount']     ?? 0;
$productCode     = $responseData['product_code']     ?? '';
$refId           = $responseData['transaction_code'] ?? '';

// Verify signature
$message          = "transaction_code=$refId,status=$status,total_amount=$totalAmount,transaction_uuid=$transactionUuid,product_code=$productCode,signed_field_names=transaction_code,status,total_amount,transaction_uuid,product_code,signed_field_names";
$expectedSignature = base64_encode(hash_hmac('sha256', $message, ESEWA_SECRET_KEY, true));
$receivedSignature = $responseData['signature'] ?? '';

// Fetch order
$db   = getDB();
$stmt = $db->prepare('SELECT * FROM orders WHERE order_ref = ?');
$stmt->execute([$orderRef]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found.');
    redirect('/payment/failed');
}

// Verify with eSewa API
$verifyParams = http_build_query([
    'product_code'     => ESEWA_MERCHANT_ID,
    'total_amount'     => $order['total_amount'],
    'transaction_uuid' => $transactionUuid,
]);

$verifyResponse = @file_get_contents(ESEWA_VERIFY_URL . '?' . $verifyParams);
$verifyData     = $verifyResponse ? json_decode($verifyResponse, true) : null;

$isVerified = (
    $status === 'COMPLETE' &&
    $expectedSignature === $receivedSignature &&
    ($verifyData['status'] ?? '') === 'COMPLETE'
);

// TEST MODE override — always verify in test
// On localhost, skip API verification entirely
if (ESEWA_MODE === 'test') {
    $isVerified = true;
}

if ($isVerified) {
    // Update order to paid
    $db->prepare('
        UPDATE orders
        SET payment_status = "paid",
            order_status   = "confirmed"
        WHERE order_ref = ?
          AND payment_status = "pending"
    ')->execute([$orderRef]);

    // Record payment
    $db->prepare('
        INSERT INTO payments (order_id, payment_method, amount, payment_status)
        VALUES (?, "esewa", ?, "paid")
        ON DUPLICATE KEY UPDATE payment_status = "paid"
    ')->execute([$order['id'], $order['total_amount']]);

    // Update delivery tracking
    $db->prepare('
        UPDATE delivery_tracking SET status = "confirmed"
        WHERE order_id = ?
    ')->execute([$order['id']]);

    // Store ref ID
    $db->prepare('UPDATE orders SET notes = CONCAT(COALESCE(notes,""), ?) WHERE order_ref = ?')
       ->execute(["|esewa_ref:$refId", $orderRef]);

    // Redirect to success
    redirect('/payment/success?ref=' . urlencode($orderRef));

} else {
    // Mark as failed
    $db->prepare('UPDATE orders SET payment_status = "failed" WHERE order_ref = ?')
       ->execute([$orderRef]);

    setFlash('error', 'Payment verification failed. Please contact support.');
    redirect('/payment/failed?ref=' . urlencode($orderRef));
}