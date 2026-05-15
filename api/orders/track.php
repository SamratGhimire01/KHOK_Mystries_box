<?php
// ─────────────────────────────────────────
//  K HO K — Order Tracking API
//  api/orders/track.php
// ─────────────────────────────────────────

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../core/helpers.php';

header('Content-Type: application/json');

$ref = sanitize($_GET['ref'] ?? '');

if (empty($ref)) {
    jsonResponse(['success' => false, 'message' => 'Order reference required.']);
}

$db   = getDB();

// Fetch order with box name
$stmt = $db->prepare('
    SELECT o.*, b.name AS box_name, b.slug AS box_slug
    FROM orders o
    JOIN boxes b ON o.box_id = b.id
    WHERE o.order_ref = ?
');
$stmt->execute([$ref]);
$order = $stmt->fetch();

if (!$order) {
    jsonResponse(['success' => false, 'message' => 'Order not found.']);
}

// Fetch tracking
$track = $db->prepare('SELECT * FROM delivery_tracking WHERE order_id = ?');
$track->execute([$order['id']]);
$tracking = $track->fetch();

// Fetch order items
$items = $db->prepare('
    SELECT p.name, p.brand, p.rarity, p.image
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
');
$items->execute([$order['id']]);
$orderItems = $items->fetchAll();

// Only reveal items if delivered
$revealItems = $order['order_status'] === 'delivered' ? $orderItems : [];

jsonResponse([
    'success'  => true,
    'order'    => [
        'order_ref'      => $order['order_ref'],
        'customer_name'  => $order['customer_name'],
        'phone'          => $order['phone'],
        'city'           => $order['city'],
        'box_name'       => $order['box_name'],
        'box_slug'       => $order['box_slug'],
        'total_amount'   => $order['total_amount'],
        'payment_method' => $order['payment_method'],
        'payment_status' => $order['payment_status'],
        'order_status'   => $order['order_status'],
        'created_at'     => date('d M Y', strtotime($order['created_at'])),
    ],
    'tracking' => $tracking ? [
        'status'        => $tracking['status'],
        'proof_image'   => $tracking['proof_image'],
        'delivery_note' => $tracking['delivery_note'],
        'estimated_date'=> $tracking['estimated_date']
            ? date('d M Y', strtotime($tracking['estimated_date']))
            : null,
        'delivered_at'  => $tracking['delivered_at']
            ? date('d M Y H:i', strtotime($tracking['delivered_at']))
            : null,
    ] : null,
    'items' => $revealItems,
]);