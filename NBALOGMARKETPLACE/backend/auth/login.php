<?php
/**
 * VirtualHub Pro — Login Handler
 * POST /backend/auth/login.php
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

$email      = strtolower(trim($_POST['email'] ?? ''));
$password   = $_POST['password'] ?? '';
$loginType  = clean($_POST['login_type'] ?? 'user');

if (!$email || !$password) {
    jsonResponse(false, 'Email and password are required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address.');
}

$pdo = getDB();

// ── Brute Force Protection: Rate Limiting ─────────────────
// Simple approach: check failed attempts in last 15 min
$attemptKey = 'login_attempt_' . md5($email);
if (!isset($_SESSION[$attemptKey])) {
    $_SESSION[$attemptKey] = ['count' => 0, 'last' => time()];
}
$att = &$_SESSION[$attemptKey];
if ($att['count'] >= 5 && (time() - $att['last']) < 900) {
    $wait = ceil((900 - (time() - $att['last'])) / 60);
    jsonResponse(false, "Too many failed attempts. Please wait {$wait} minute(s) before trying again.");
}
if ((time() - $att['last']) >= 900) {
    $att = ['count' => 0, 'last' => time()];
}

// ── Query the right table ─────────────────────────────────
if ($loginType === 'admin') {
    $stmt = $pdo->prepare('SELECT id, name, email, password_hash, is_active FROM admins WHERE email = ? LIMIT 1');
} else {
    $stmt = $pdo->prepare('SELECT id, first_name, last_name, email, username, password_hash, is_active, balance, profile_image FROM users WHERE email = ? LIMIT 1');
}
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    $att['count']++;
    $att['last'] = time();
    jsonResponse(false, 'Incorrect email or password. Please try again.');
}

if (!$user['is_active']) {
    jsonResponse(false, 'Your account has been suspended. Please contact support.');
}

// ── Rehash if needed ──────────────────────────────────────
if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
    $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $table   = $loginType === 'admin' ? 'admins' : 'users';
    $upd = $pdo->prepare("UPDATE {$table} SET password_hash = ? WHERE id = ?");
    $upd->execute([$newHash, $user['id']]);
}

// ── Reset failed attempts ─────────────────────────────────
$att = ['count' => 0, 'last' => time()];

// ── Set Session ───────────────────────────────────────────
session_regenerate_id(true);

if ($loginType === 'admin') {
    $_SESSION['admin_id']   = $user['id'];
    $_SESSION['admin_name'] = $user['name'];
    $_SESSION['admin_email']= $user['email'];
    $_SESSION['role']       = 'admin';
    $redirect = '/NBALOGMARKETPLACE/frontend/pages/admin/dashboard.php';
} else {
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['role']       = 'user';
    $redirect = '/NBALOGMARKETPLACE/frontend/pages/user/dashboard.php';
}

jsonResponse(true, 'Login successful!', ['redirect' => $redirect]);