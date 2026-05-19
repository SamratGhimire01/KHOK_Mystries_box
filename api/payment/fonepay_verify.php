<?php
// ─────────────────────────────────────────
//  K HO K — Fonepay Payment Verify
//  api/payment/fonepay_verify.php
// ─────────────────────────────────────────

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/payment.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();

$orderRef   = sanitize($_GET['ref']  ?? '');
$prn        = sanitize($_GET['PRN']  ?? $orderRef);
$pid        = sanitize($_GET['PID']  ?? '');
$ps         = sanitize($_GET['PS']   ?? ''); // Payment Status: S=success, F=failed, C=cancelled
$rc         = sanitize($_GET['RC']   ?? ''); // Response code
$uid        = sanitize($_GET['UID']  ?? ''); // Unique ID from Fonepay
$bc         = sanitize($_GET['BC']   ?? ''); // Bank code
$ini        = sanitize($_GET['INI']  ?? ''); // 
$p1         = sanitize($_GET['P1']   ?? '');
$p2         = sanitize($_GET['P2']   ?? '');
$p3         = sanitize($_GET['P3']   ?? '');
$p4         = sanitize($_GET['P4']   ?? '');
$p5         = sanitize($_GET['P5']   ?? '');
$dv         = sanitize($_GET['DV']   ?? ''); // Hash to verify

if (empty($orderRef)) {
    redirect('/payment/failed');
}

// Fetch order
$db   = getDB();
$stmt = $db->prepare('SELECT * FROM orders WHERE order_ref = ?');
$stmt->execute([$orderRef]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found.');
    redirect('/payment/failed');
}

// Verify Fonepay hash
// Format: PID,PS,RC,UID,BC,INI,P1,P2,P3,P4,P5,PRN,DV (excluding DV from hash)
$dataToHash       = FONEPAY_MERCHANT_ID . ',' . $ps . ',' . $rc . ',' . $uid . ',' . $bc . ',' . $ini . ',' . $p1 . ',' . $p2 . ',' . $p3 . ',' . $p4 . ',' . $p5 . ',' . $prn;
$expectedHash     = strtoupper(hash_hmac('sha512', $dataToHash, FONEPAY_SECRET_KEY));
$hashValid        = ($expectedHash === strtoupper($dv));

// TEST MODE — always pass
if (FONEPAY_MODE === 'test') {
    $hashValid = true;
    $ps        = 'S'; // simulate success
}

$isSuccess = $hashValid && $ps === 'S';

if ($isSuccess) {
    $db->prepare('
        UPDATE orders
        SET payment_status = "paid",
            order_status   = "confirmed"
        WHERE order_ref = ?
          AND payment_status = "pending"
    ')->execute([$orderRef]);

    $db->prepare('
        INSERT INTO payments (order_id, payment_method, amount, payment_status)
        VALUES (?, "fonepay", ?, "paid")
        ON DUPLICATE KEY UPDATE payment_status = "paid"
    ')->execute([$order['id'], $order['total_amount']]);

    $db->prepare('
        UPDATE delivery_tracking SET status = "confirmed"
        WHERE order_id = ?
    ')->execute([$order['id']]);

    redirect('/payment/success?ref=' . urlencode($orderRef));

} else {
    $db->prepare('UPDATE orders SET payment_status = "failed" WHERE order_ref = ?')
       ->execute([$orderRef]);

    setFlash('error', 'Payment failed or was cancelled.');
    redirect('/payment/failed?ref=' . urlencode($orderRef));
}