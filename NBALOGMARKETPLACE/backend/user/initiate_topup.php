<?php
/**
 * NBALOGMARKETPLACE — Initiate Top Up
 * POST /backend/user/initiate_topup.php
 * Called before launching Flutterwave popup
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$amount = (float)($data['amount'] ?? 0);
$txRef  = trim($data['tx_ref'] ?? '');

if ($amount < 100 || $amount > 500000) {
    jsonResponse(false, 'Invalid amount.');
}
if (!$txRef || !preg_match('/^VH-[\d]+-[A-Z0-9]+$/', $txRef)) {
    jsonResponse(false, 'Invalid transaction reference.');
}

$pdo = getDB();
$uid = $currentUser['id'];

// Check tx_ref not already used
$check = $pdo->prepare('SELECT id FROM payments WHERE tx_ref = ? LIMIT 1');
$check->execute([$txRef]);
if ($check->fetch()) {
    jsonResponse(false, 'Duplicate transaction reference.');
}

// Insert pending payment record
$ins = $pdo->prepare(
    'INSERT INTO payments (user_id, flw_ref, tx_ref, amount, currency, status)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$ins->execute([$uid, '', $txRef, $amount, 'NGN', 'pending']);

jsonResponse(true, 'Payment initiated.', ['tx_ref' => $txRef]);