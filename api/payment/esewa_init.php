<?php
// ─────────────────────────────────────────
//  K HO K — eSewa Payment Init
//  api/payment/esewa_init.php
// ─────────────────────────────────────────

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/payment.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();

$orderRef = sanitize($_GET['order'] ?? '');

if (empty($orderRef)) {
    redirect('/boxes');
}

// Fetch order
$db   = getDB();
$stmt = $db->prepare('SELECT * FROM orders WHERE order_ref = ? AND payment_status = "pending"');
$stmt->execute([$orderRef]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found or already paid.');
    redirect('/boxes');
}

$amount      = number_format($order['total_amount'], 2, '.', '');
$taxAmount   = '0';
$totalAmount = $amount;
$productCode = ESEWA_MERCHANT_ID;
$transactionUuid = $orderRef . '-' . time();

// Generate HMAC signature for eSewa v2
// Format: total_amount,transaction_uuid,product_code
$message   = "total_amount=$totalAmount,transaction_uuid=$transactionUuid,product_code=$productCode";
$signature = base64_encode(hash_hmac('sha256', $message, ESEWA_SECRET_KEY, true));

// Store transaction UUID in DB for verification later
$db->prepare('UPDATE orders SET notes = CONCAT(COALESCE(notes,""), ?) WHERE order_ref = ?')
   ->execute(["|esewa_uuid:$transactionUuid", $orderRef]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to eSewa — K HO K</title>
    <style>
        body { background:#050505;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;flex-direction:column;gap:1rem; }
        .spinner { width:40px;height:40px;border:3px solid #262626;border-top-color:#A855F7;border-radius:50%;animation:spin .8s linear infinite; }
        @keyframes spin { to{transform:rotate(360deg)} }
        p { color:#A1A1AA;font-size:.9rem; }
    </style>
</head>
<body>
    <div class="spinner"></div>
    <p>Redirecting to eSewa secure payment...</p>

    <!-- eSewa v2 Payment Form — auto submits -->
    <form id="esewaForm" action="<?= ESEWA_GATEWAY_URL ?>" method="POST" style="display:none">
        <input type="hidden" name="amount"            value="<?= $amount ?>">
        <input type="hidden" name="tax_amount"        value="<?= $taxAmount ?>">
        <input type="hidden" name="total_amount"      value="<?= $totalAmount ?>">
        <input type="hidden" name="transaction_uuid"  value="<?= $transactionUuid ?>">
        <input type="hidden" name="product_code"      value="<?= $productCode ?>">
        <input type="hidden" name="product_service_charge"  value="0">
        <input type="hidden" name="product_delivery_charge" value="0">
        <input type="hidden" name="success_url" value="http://localhost/khok/esewa_return.php?ref=<?= urlencode($orderRef) ?>">
        <input type="hidden" name="failure_url"       value="<?= PAYMENT_FAILURE_URL ?>?method=esewa&ref=<?= urlencode($orderRef) ?>">
        <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
        <input type="hidden" name="signature"         value="<?= $signature ?>">
    </form>

    <script>
        // Auto-submit after 1 second
        setTimeout(() => document.getElementById('esewaForm').submit(), 1000);
    </script>
</body>
</html>