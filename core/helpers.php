<?php
// ─────────────────────────────────────────
//  K HO K — Helper Functions
//  core/helpers.php
// ─────────────────────────────────────────

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function formatPrice(int|float $amount): string {
    return CURRENCY . ' ' . number_format($amount, 0, '.', ',');
}

function redirect(string $path): never {
    header('Location: ' . APP_URL . $path);
    exit;
}

function generateOrderId(): string {
    return 'KHK-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Ymd');
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}

function sanitize(string $input): string {
    return trim(strip_tags($input));
}

function jsonResponse(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
