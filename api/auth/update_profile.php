<?php
// ─────────────────────────────────────────
//  K HO K — Update Profile API
//  api/auth/update_profile.php
// ─────────────────────────────────────────

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

header('Content-Type: application/json');
startSession();
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$fullName = sanitize($_POST['full_name'] ?? '');
$phone    = sanitize($_POST['phone']     ?? '');
$city     = sanitize($_POST['city']      ?? '');
$address  = sanitize($_POST['address']   ?? '');

// ── Validation ──
$errors = [];

// Name: letters, spaces, hyphens, apostrophes only — NO numbers
if (!preg_match('/^[a-zA-Z\s\'\-\.]{2,100}$/', $fullName)) {
    $errors[] = 'Name must contain only letters (no numbers).';
}

// Phone: exactly 10 digits, starts with 97 or 98
if (!preg_match('/^(97|98)\d{8}$/', $phone)) {
    $errors[] = 'Phone must be 10 digits starting with 97 or 98.';
}

if (empty($city))    $errors[] = 'City is required.';
if (empty($address)) $errors[] = 'Address is required.';

if (!empty($errors)) {
    jsonResponse(['success' => false, 'message' => implode(' ', $errors)]);
}

try {
    $db = getDB();
    $db->prepare('
        UPDATE users SET full_name = ?, phone = ?, city = ?, address = ?
        WHERE id = ?
    ')->execute([$fullName, $phone, $city, $address, $_SESSION['user_id']]);

    // Update session name
    $_SESSION['user_name'] = $fullName;

    jsonResponse(['success' => true, 'message' => 'Profile updated successfully!']);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Update failed. Please try again.'], 500);
}