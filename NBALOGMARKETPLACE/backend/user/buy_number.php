<?php
/**
 * POST /backend/user/buy_number.php
 * Body: { service_code, service_name, country_id, country_name, price }
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/sms_activate.php';

$d           = json_decode(file_get_contents('php://input'), true);
$serviceCode = trim($d['service_code'] ?? '');
$serviceName = trim($d['service_name'] ?? '');
$countryId   = (int)($d['country_id']  ?? 1);
$countryName = trim($d['country_name'] ?? '');
$price       = (float)($d['price']     ?? 0);
$uid         = $currentUser['id'];

if (!$serviceCode || !$serviceName || $price <= 0) {
    jsonResponse(false, 'Invalid request.');
}

// ── Check balance ─────────────────────────────────────────
if ((float)$currentUser['balance'] < $price) {
    jsonResponse(false, 'Insufficient balance. Please top up your wallet.');
}

// ── Call API ──────────────────────────────────────────────
$sms    = new SmsActivate();
$result = $sms->buyNumber($serviceCode, $countryId);

if (isset($result['error'])) {
    jsonResponse(false, $result['error']);
}

$number       = $result['number'];
$activationId = $result['id'];   // this is the order_id from the API
$expiresAt    = date('Y-m-d H:i:s', strtotime('+20 minutes'));  // 20 min like working proxy

// ── Atomic DB transaction ─────────────────────────────────
$pdo = getDB();
$pdo->beginTransaction();
try {
    // Lock and re-check balance
    $stmt = $pdo->prepare('SELECT balance FROM users WHERE id = ? FOR UPDATE');
    $stmt->execute([$uid]);
    $balance = (float)$stmt->fetchColumn();

    if ($balance < $price) {
        $pdo->rollBack();
        $sms->cancelNumber($activationId);
        jsonResponse(false, 'Insufficient balance.');
    }

    $newBalance = $balance - $price;
    $pdo->prepare('UPDATE users SET balance = ? WHERE id = ?')
        ->execute([$newBalance, $uid]);

    // Transaction record
    $pdo->prepare(
        'INSERT INTO transactions (user_id, type, item_type, amount, balance_before, balance_after, status, note)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([$uid, 'purchase', 'virtual_number', $price, $balance, $newBalance, 'success',
                "Virtual number: $serviceName ($countryName)"]);

    $txId = (int)$pdo->lastInsertId();

    // Save number
    $pdo->prepare(
        'INSERT INTO virtual_numbers
         (user_id, transaction_id, country, service, phone_number, activation_id, status, price, expires_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([$uid, $txId, $countryName, $serviceName, $number, $activationId, 'waiting', $price, $expiresAt]);

    $numberId = (int)$pdo->lastInsertId();
    $pdo->commit();

    jsonResponse(true, 'Number assigned!', [
        'number_id'   => $numberId,
        'phone_number'=> $number,
        'service'     => $serviceName,
        'country'     => $countryName,
        'expires_at'  => $expiresAt,
        'expires_ts'  => strtotime($expiresAt) * 1000,
        'new_balance' => formatMoney($newBalance),
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    $sms->cancelNumber($activationId);
    error_log('buy_number error: ' . $e->getMessage());
    jsonResponse(false, 'Purchase failed. Please try again.');
}