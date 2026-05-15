<?php
// ─────────────────────────────────────────
//  K HO K — Mystery Box Engine
//  api/engine/mystery_engine.php
// ─────────────────────────────────────────

require_once __DIR__ . '/../../config/db.php';

/**
 * Weighted random selection of products for a box
 *
 * @param int $boxId     — box ID from DB
 * @param int $count     — how many products to pick
 * @return array         — selected product rows
 */
function selectMysteryProducts(int $boxId, int $count): array {
    $db = getDB();

    // Get all eligible products for this box with stock > 0
    $stmt = $db->prepare('
        SELECT p.*
        FROM products p
        JOIN box_product_pool bpp ON bpp.product_id = p.id
        WHERE bpp.box_id = ?
          AND p.stock > 0
          AND p.is_active = 1
        ORDER BY p.weight DESC
    ');
    $stmt->execute([$boxId]);
    $pool = $stmt->fetchAll();

    if (empty($pool)) return [];

    $selected = [];
    $usedIds  = [];

    for ($i = 0; $i < $count; $i++) {
        // Remove already selected products from pool
        $available = array_filter($pool, fn($p) => !in_array($p['id'], $usedIds));
        if (empty($available)) break;

        $product = weightedRandom(array_values($available));
        if ($product) {
            $selected[] = $product;
            $usedIds[]  = $product['id'];
        }
    }

    return $selected;
}

/**
 * Weighted random pick from product array
 * Uses weight column — higher weight = more likely
 */
function weightedRandom(array $products): ?array {
    $totalWeight = array_sum(array_column($products, 'weight'));
    if ($totalWeight <= 0) return $products[0] ?? null;

    $rand = (float)(mt_rand() / mt_getrandmax()) * $totalWeight;
    $cumulative = 0;

    foreach ($products as $product) {
        $cumulative += (float)$product['weight'];
        if ($rand <= $cumulative) {
            return $product;
        }
    }

    return $products[array_key_last($products)];
}

/**
 * Deduct stock after order confirmed
 */
function deductStock(array $products): void {
    $db = getDB();
    $stmt = $db->prepare('UPDATE products SET stock = stock - 1 WHERE id = ? AND stock > 0');
    foreach ($products as $product) {
        $stmt->execute([$product['id']]);
    }
}

/**
 * Determine product count based on box min/max
 */
function getProductCount(array $box): int {
    return rand((int)$box['min_products'], (int)$box['max_products']);
}