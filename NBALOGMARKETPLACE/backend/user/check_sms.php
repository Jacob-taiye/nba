<?php
/**
 * POST /backend/user/check_sms.php
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
$stmt = $pdo->prepare('SELECT * FROM virtual_numbers WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$numberId, $uid]);
$rec = $stmt->fetch();

if (!$rec) jsonResponse(false, 'Number not found.');

// Already finalized — return stored result
if (in_array($rec['status'], ['received','cancelled','expired'])) {
    jsonResponse(true, 'OK', ['status' => $rec['status'], 'sms_code' => $rec['sms_code']]);
}

// Check expired
if (strtotime($rec['expires_at']) < time()) {
    $pdo->prepare("UPDATE virtual_numbers SET status='expired' WHERE id=?")->execute([$numberId]);
    issueRefund($pdo, $uid, (float)$rec['price'], $numberId, 'Virtual number expired — no SMS received');
    jsonResponse(true, 'OK', ['status' => 'expired', 'sms_code' => null]);
}

// Poll API
$sms    = new SmsActivate();
$result = $sms->checkSms($rec['activation_id']);

if ($result['status'] === 'received') {
    $code = $result['code'];
    $pdo->prepare("UPDATE virtual_numbers SET status='received', sms_code=? WHERE id=?")
        ->execute([$code, $numberId]);
    $sms->finishNumber($rec['activation_id']);
    jsonResponse(true, 'SMS received!', ['status' => 'received', 'sms_code' => $code]);
}

if ($result['status'] === 'cancelled') {
    $pdo->prepare("UPDATE virtual_numbers SET status='cancelled' WHERE id=?")->execute([$numberId]);
    jsonResponse(true, 'OK', ['status' => 'cancelled', 'sms_code' => null]);
}

// Still waiting
jsonResponse(true, 'OK', ['status' => 'waiting', 'sms_code' => null]);

function issueRefund(PDO $pdo, int $uid, float $amount, int $numberId, string $reason): void {
    try {
        $pdo->beginTransaction();
        $s = $pdo->prepare('SELECT balance FROM users WHERE id = ? FOR UPDATE');
        $s->execute([$uid]);
        $bal    = (float)$s->fetchColumn();
        $newBal = $bal + $amount;
        $pdo->prepare('UPDATE users SET balance = ? WHERE id = ?')->execute([$newBal, $uid]);
        $pdo->prepare(
            'INSERT INTO transactions (user_id, type, item_type, item_id, amount, balance_before, balance_after, status, note)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([$uid, 'refund', 'virtual_number', $numberId, $amount, $bal, $newBal, 'success', $reason]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Refund error: ' . $e->getMessage());
    }
}