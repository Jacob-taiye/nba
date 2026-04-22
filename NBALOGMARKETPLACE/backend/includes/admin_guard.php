<?php
/**
 * VirtualHub Pro — Admin Auth Guard
 * Include at the TOP of every admin page
 */
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /NBALOGMARKETPLACE/frontend/pages/login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$pdo = getDB();
$stmt = $pdo->prepare('SELECT id, name, email FROM admins WHERE id = ? AND is_active = 1 LIMIT 1');
$stmt->execute([$_SESSION['admin_id']]);
$currentAdmin = $stmt->fetch();

if (!$currentAdmin) {
    session_destroy();
    header('Location: /NBALOGMARKETPLACE/frontend/pages/login.php');
    exit;
}

if (!function_exists('formatMoney')) {
    function formatMoney(float $amount): string {
        return '₦' . number_format($amount, 2);
    }
}
if (!function_exists('clean')) {
    function clean(string $v): string {
        return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('adminInitials')) {
    function adminInitials(array $admin): string {
        $parts = explode(' ', $admin['name']);
        return strtoupper(substr($parts[0],0,1) . (isset($parts[1]) ? substr($parts[1],0,1) : ''));
    }
}