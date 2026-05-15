<?php
// ─────────────────────────────────────────
//  K HO K — Register API
//  api/auth/register.php
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

// ── Collect and sanitize inputs ──
$fullName  = sanitize($_POST['full_name']        ?? '');
$email     = strtolower(sanitize($_POST['email'] ?? ''));
$phone     = sanitize($_POST['phone']            ?? '');
$password  = $_POST['password']                  ?? '';
$confirm   = $_POST['confirm_password']          ?? '';
$city      = sanitize($_POST['city']             ?? '');
$address   = sanitize($_POST['address']          ?? '');

// ── Validation ──
$errors = [];

if (strlen($fullName) < 2)
    $errors[] = 'Full name must be at least 2 characters.';

if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $errors[] = 'Please enter a valid email address.';

if (!preg_match('/^[0-9]{10}$/', $phone))
    $errors[] = 'Phone must be 10 digits (e.g. 9812345678).';

if (strlen($password) < 8)
    $errors[] = 'Password must be at least 8 characters.';

if ($password !== $confirm)
    $errors[] = 'Passwords do not match.';

if (!empty($errors)) {
    jsonResponse(['success' => false, 'message' => implode(' ', $errors)]);
}

// ── Check duplicate email ──
$db   = getDB();
$stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);

if ($stmt->fetch()) {
    jsonResponse(['success' => false, 'message' => 'An account with this email already exists.']);
}

// ── Hash password with bcrypt ──
$passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// ── Insert user ──
try {
    $insert = $db->prepare('
        INSERT INTO users (full_name, email, password_hash, phone, city, address, role)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $insert->execute([
        $fullName,
        $email,
        $passwordHash,
        $phone,
        $city,
        $address,
        'customer'
    ]);

    $userId = $db->lastInsertId();

    // ── Create session ──
    $_SESSION['user_id']   = $userId;
    $_SESSION['user_name'] = $fullName;
    $_SESSION['user_email']= $email;
    $_SESSION['role']      = 'customer';

    jsonResponse([
        'success'  => true,
        'message'  => 'Account created successfully!',
        'redirect' => APP_URL . '/'
    ]);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Registration failed. Please try again.'], 500);
}