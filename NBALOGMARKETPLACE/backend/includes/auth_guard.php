<?php
/**
 * VirtualHub Pro — Session Auth Guard
 * Include at the TOP of every protected user page:
 *   require_once __DIR__ . '/../../backend/includes/auth_guard.php';
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated as a user
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: /NBALOGMARKETPLACE/frontend/pages/login.php');
    exit;
}

require_once __DIR__ . '/db.php';

// Fetch fresh user data from DB (balance may have changed)
$pdo  = getDB();
$stmt = $pdo->prepare(
    'SELECT id, first_name, last_name, username, email, phone,
            balance, profile_image
     FROM users WHERE id = ? AND is_active = 1 LIMIT 1'
);
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    // User deleted or deactivated
    session_destroy();
    header('Location: /NBALOGMARKETPLACE/frontend/pages/login.php?msg=account_inactive');
    exit;
}

// Keep session name in sync
$_SESSION['user_name'] = $currentUser['first_name'] . ' ' . $currentUser['last_name'];

/**
 * Helper: returns the user's initials for avatar placeholder
 */
if (!function_exists('userInitials')) {
    function userInitials(array $user): string {
        return strtoupper(
            substr($user['first_name'], 0, 1) .
            substr($user['last_name'],  0, 1)
        );
    }
}

if (!function_exists('formatMoney')) {
    function formatMoney(float $amount): string {
        return '₦' . number_format($amount, 2);
    }
}