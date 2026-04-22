<?php
/**
 * POST /backend/user/cancel_number.php
 * Body: { number_id }
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/sms_activate.php';

$d        = json_decode(file_get_contents('php://input'), true);
$numberId = (int)($d['number_id'] ?? 0);
$uid      = $currentUser['id'];

if (!$numberId) jsonResponse(false, 'Invalid request.');

$pdo  = getDB();
$stmt = $pdo->prepare('SELECT * FROM virtual_numbers WHERE id = ? AND user_id = ? AND status = ? LIMIT 1');
$stmt->execute([$numberId, $uid, 'waiting']);
$rec = $stmt->fetch();

if (!$rec) jsonResponse(false, 'Number not found or already processed.');

// Cancel on API
$sms = new SmsActivate();
$sms->cancelNumber($rec['activation_id']);

// Update status
$pdo->prepare("UPDATE virtual_numbers SET status='cancelled' WHERE id=?")->execute([$numberId]);

// Refund atomically
$pdo->beginTransaction();
try {
    $s = $pdo->prepare('SELECT balance FROM users WHERE id = ? FOR UPDATE');
    $s->execute([$uid]);
    $bal    = (float)$s->fetchColumn();
    $newBal = $bal + (float)$rec['price'];
    $pdo->prepare('UPDATE users SET balance = ? WHERE id = ?')->execute([$newBal, $uid]);
    $pdo->prepare(
        'INSERT INTO transactions (user_id, type, item_type, item_id, amount, balance_before, balance_after, status, note)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([$uid, 'refund', 'virtual_number', $numberId, (float)$rec['price'],
                $bal, $newBal, 'success', 'Number cancelled by user']);
    $pdo->commit();
    jsonResponse(true, 'Cancelled and refunded.', ['new_balance' => formatMoney($newBal)]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('cancel refund error: ' . $e->getMessage());
    jsonResponse(false, 'Cancelled but refund failed. Contact support.');
}