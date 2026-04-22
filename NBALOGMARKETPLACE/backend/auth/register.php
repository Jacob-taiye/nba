<?php
/**
 * VirtualHub Pro — User Registration
 * POST /backend/auth/register.php
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

// ── Collect & Sanitize Inputs ──────────────────────────────
$firstName = clean($_POST['first_name'] ?? '');
$lastName  = clean($_POST['last_name']  ?? '');
$username  = clean($_POST['username']   ?? '');
$email     = strtolower(trim($_POST['email'] ?? ''));
$phone     = clean($_POST['phone'] ?? '');
$password  = $_POST['password'] ?? '';
$confirm   = $_POST['confirm_password'] ?? '';

// ── Validate ───────────────────────────────────────────────
if (!$firstName || !$lastName || !$username || !$email || !$password) {
    jsonResponse(false, 'All required fields must be filled.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address.');
}
if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    jsonResponse(false, 'Username must be at least 3 characters and contain only letters, numbers and underscores.');
}
if (strlen($password) < 8) {
    jsonResponse(false, 'Password must be at least 8 characters long.');
}
if ($password !== $confirm) {
    jsonResponse(false, 'Passwords do not match.');
}

$pdo = getDB();

// ── Check for existing email / username ────────────────────
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
$stmt->execute([$email, $username]);
$existing = $stmt->fetch();

if ($existing) {
    // Determine which field is taken
    $emailStmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $emailStmt->execute([$email]);
    if ($emailStmt->fetch()) {
        jsonResponse(false, 'An account with this email already exists. Please sign in.');
    }
    jsonResponse(false, 'This username is already taken. Please choose another.');
}

// ── Hash Password & Insert ─────────────────────────────────
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $insert = $pdo->prepare(
        'INSERT INTO users (first_name, last_name, username, email, phone, password_hash)
         VALUES (:fn, :ln, :un, :em, :ph, :pw)'
    );
    $insert->execute([
        ':fn' => $firstName,
        ':ln' => $lastName,
        ':un' => $username,
        ':em' => $email,
        ':ph' => $phone,
        ':pw' => $hash,
    ]);
    $userId = $pdo->lastInsertId();
} catch (PDOException $e) {
    error_log('Register error: ' . $e->getMessage());
    jsonResponse(false, 'Registration failed. Please try again.');
}

// ── Start Session ──────────────────────────────────────────
$_SESSION['user_id']   = $userId;
$_SESSION['user_name'] = $firstName . ' ' . $lastName;
$_SESSION['user_email']= $email;
$_SESSION['role']      = 'user';
session_regenerate_id(true);

jsonResponse(true, 'Account created successfully!', [
    'redirect' => '/NBALOGMARKETPLACE/frontend/pages/user/dashboard.php'
]);