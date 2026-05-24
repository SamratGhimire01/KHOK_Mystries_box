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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// ── Collect inputs ──
$fullName = sanitize($_POST['full_name']        ?? '');
$email    = strtolower(sanitize($_POST['email'] ?? ''));
$phone    = sanitize($_POST['phone']            ?? '');
$password = $_POST['password']                  ?? '';
$confirm  = $_POST['confirm_password']          ?? '';
$city     = sanitize($_POST['city']             ?? '');
$address  = sanitize($_POST['address']          ?? '');

// ── Validation ──
$errors = [];

// Name: letters only, no numbers
if (!preg_match('/^[a-zA-Z\s\'\-\.]{2,100}$/', $fullName))
    $errors[] = 'Name must contain only letters — no numbers allowed.';

// Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $errors[] = 'Please enter a valid email address.';

// Phone: exactly 10 digits, starts with 97 or 98
if (!preg_match('/^(97|98)\d{8}$/', $phone))
    $errors[] = 'Phone must be 10 digits starting with 97 or 98.';

// Password: min 8 chars, must have letter and number
if (strlen($password) < 8)
    $errors[] = 'Password must be at least 8 characters.';

if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password))
    $errors[] = 'Password must contain at least one letter and one number.';

// Confirm
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

// ── Hash password ──
$passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// ── Insert user ──
try {
    $db->prepare('
        INSERT INTO users (full_name, email, password_hash, phone, city, address, role)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ')->execute([$fullName, $email, $passwordHash, $phone, $city, $address, 'customer']);

    // NO session created — user must login manually
    jsonResponse([
        'success'  => true,
        'message'  => 'Account created successfully! Please login to continue.',
        'redirect' => APP_URL . '/login'
    ]);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Registration failed. Please try again.'], 500);
}