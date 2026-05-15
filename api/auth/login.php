<?php
// ─────────────────────────────────────────
//  K HO K — Login API
//  api/auth/login.php
// ─────────────────────────────────────────

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

header('Content-Type: application/json');
startSession();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Already logged in
if (isLoggedIn()) {
    jsonResponse(['success' => true, 'redirect' => APP_URL . '/']);
}

// ── Collect inputs ──
$email    = strtolower(sanitize($_POST['email']    ?? ''));
$password = $_POST['password'] ?? '';

// ── Basic validation ──
if (empty($email) || empty($password)) {
    jsonResponse(['success' => false, 'message' => 'Email and password are required.']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Invalid email address.']);
}

// ── Look up user ──
$db   = getDB();
$stmt = $db->prepare('SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

// ── Verify password (timing-safe) ──
if (!$user || !password_verify($password, $user['password_hash'])) {
    // Same message for both cases — don't reveal which is wrong
    jsonResponse(['success' => false, 'message' => 'Invalid email or password.']);
}

// ── Rehash if needed (future-proofing) ──
if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
    $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
       ->execute([$newHash, $user['id']]);
}

// ── Create session ──
session_regenerate_id(true); // prevent session fixation
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_name']  = $user['full_name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['role']       = $user['role'];

// ── Redirect based on role ──
$redirect = $user['role'] === 'admin'
    ? APP_URL . '/admin'
    : APP_URL . '/';

jsonResponse([
    'success'  => true,
    'message'  => 'Welcome back, ' . $user['full_name'] . '!',
    'redirect' => $redirect
]);