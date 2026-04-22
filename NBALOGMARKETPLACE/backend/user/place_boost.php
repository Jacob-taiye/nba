<?php
/**
 * POST /backend/user/place_boost.php
 * Body: { service_id, service_name, category, link, quantity, cost }
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/smm_api.php';

$d           = json_decode(file_get_contents('php://input'), true);
$serviceId   = (int)   ($d['service_id']   ?? 0);
$serviceName = trim    ($d['service_name'] ?? '');
$category    = trim    ($d['category']     ?? '');
$link        = trim    ($d['link']         ?? '');
$quantity    = (int)   ($d['quantity']     ?? 0);
$cost        = (float) ($d['cost']         ?? 0);
$uid         = $currentUser['id'];

// Validate
if (!$serviceId || !$link || $quantity <= 0 || $cost <= 0) {
    jsonResponse(false, 'Please fill in all fields.');
}
if (!filter_var($link, FILTER_VALIDATE_URL)) {
    jsonResponse(false, 'Please enter a valid URL.');
}

// Balance check
if ((float)$currentUser['balance'] < $cost) {
    jsonResponse(false, 'Insufficient balance. Please top up your wallet.');
}

// Place order on SMM panel
$smm    = new SmmApi();
$result = $smm->placeOrder($serviceId, $link, $quantity);

if (isset($result['error'])) {
    jsonResponse(false, 'Order failed: ' . $result['error']);
}

$smmOrderId = $result['order_id'];

// Atomic DB transaction
$pdo = getDB();
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT balance FROM users WHERE id = ? FOR UPDATE');
    $stmt->execute([$uid]);
    $balance = (float)$stmt->fetchColumn();

    if ($balance < $cost) {
        $pdo->rollBack();
        jsonResponse(false, 'Insufficient balance.');
    }

    $newBalance = $balance - $cost;
    $pdo->prepare('UPDATE users SET balance = ? WHERE id = ?')
        ->execute([$newBalance, $uid]);

    // Transaction record
    $pdo->prepare(
        'INSERT INTO transactions (user_id, type, item_type, amount, balance_before, balance_after, status, note)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([$uid, 'purchase', 'boost', $cost, $balance, $newBalance, 'success',
                "$serviceName × " . number_format($quantity)]);

    $txId = (int)$pdo->lastInsertId();

    // Boost order record
    $pdo->prepare(
        'INSERT INTO boost_orders
         (user_id, transaction_id, smm_order_id, service_id, service_name, category, link, quantity, amount_paid, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([$uid, $txId, $smmOrderId, $serviceId, $serviceName, $category, $link, $quantity, $cost, 'pending']);

    $boostId = (int)$pdo->lastInsertId();
    $pdo->commit();

    jsonResponse(true, 'Order placed successfully!', [
        'boost_id'    => $boostId,
        'smm_order_id'=> $smmOrderId,
        'new_balance' => formatMoney($newBalance),
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('place_boost error: ' . $e->getMessage());
    jsonResponse(false, 'Order was placed but recording failed. Contact support with order ID: ' . $smmOrderId);
}