<?php
// ─────────────────────────────────────────
//  K HO K — Fonepay Payment Init
//  api/payment/fonepay_init.php
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
$prn         = $orderRef; // Payment Reference Number
$remarks     = 'K HO K Mystery Box - ' . $orderRef;
$returnUrl   = PAYMENT_SUCCESS_URL . '?method=fonepay&ref=' . urlencode($orderRef);
$cancelUrl   = PAYMENT_FAILURE_URL . '?method=fonepay&ref=' . urlencode($orderRef);

// Generate Fonepay HMAC signature
// Format: merchantCode,prn,amount,returnUrl,cancelUrl
$dataToHash  = FONEPAY_MERCHANT_ID . ',' . $prn . ',' . $amount . ',' . $returnUrl . ',' . $cancelUrl;
$hashValue   = strtoupper(hash_hmac('sha512', $dataToHash, FONEPAY_SECRET_KEY));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to Fonepay — K HO K</title>
    <style>
        body { background:#050505;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;flex-direction:column;gap:1rem; }
        .spinner { width:40px;height:40px;border:3px solid #262626;border-top-color:#A855F7;border-radius:50%;animation:spin .8s linear infinite; }
        @keyframes spin { to{transform:rotate(360deg)} }
        p { color:#A1A1AA;font-size:.9rem; }
    </style>
</head>
<body>
    <div class="spinner"></div>
    <p>Redirecting to Fonepay secure payment...</p>

    <!-- Fonepay Payment Form — auto submits -->
    <form id="fonepayForm" action="<?= FONEPAY_GATEWAY_URL ?>" method="POST" style="display:none">
        <input type="hidden" name="PID"        value="<?= FONEPAY_MERCHANT_ID ?>">
        <input type="hidden" name="MD"         value="P"> <!-- P = production, T = test -->
        <input type="hidden" name="AMT"        value="<?= $amount ?>">
        <input type="hidden" name="CRN"        value="NPR">
        <input type="hidden" name="DT"         value="<?= date('m/d/Y') ?>">
        <input type="hidden" name="R1"         value="<?= $remarks ?>">
        <input type="hidden" name="R2"         value="<?= $orderRef ?>">
        <input type="hidden" name="RU"         value="<?= $returnUrl ?>">
        <input type="hidden" name="PRN"        value="<?= $prn ?>">
        <input type="hidden" name="DV"         value="<?= $hashValue ?>">
    </form>

    <script>
        setTimeout(() => document.getElementById('fonepayForm').submit(), 1000);
    </script>
</body>
</html>