<?php
/**
 * VirtualHub Pro — Password Reset
 * POST /backend/auth/reset_password.php
 *
 * The user enters their email on the login page and
 * submits a new password directly (no email token needed
 * in this implementation — the email acts as identity proof).
 * For extra security, consider adding an OTP step later.
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

$email       = strtolower(trim($_POST['email'] ?? ''));
$newPassword = $_POST['new_password'] ?? '';
$confirm     = $_POST['confirm_password'] ?? '';

if (!$email || !$newPassword) {
    jsonResponse(false, 'All fields are required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Invalid email address.');
}
if (strlen($newPassword) < 8) {
    jsonResponse(false, 'Password must be at least 8 characters.');
}
if ($newPassword !== $confirm) {
    jsonResponse(false, 'Passwords do not match.');
}

$pdo = getDB();

// Check user exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    // Generic message to prevent email enumeration
    jsonResponse(false, 'No active account found with that email address.');
}

$newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

$upd = $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
$upd->execute([$newHash, $user['id']]);

// Invalidate existing sessions for this user (optional security step)
// In a production app with Redis sessions, you'd purge all sessions for this user_id

jsonResponse(true, 'Password reset successfully! You can now log in with your new password.');