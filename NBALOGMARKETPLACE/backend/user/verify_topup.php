<?php
/**
 * NBALOGMARKETPLACE — Verify Top Up
 * POST /backend/user/verify_topup.php
 * Called after Flutterwave callback with transaction_id
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$flwTxId = (int)($data['transaction_id'] ?? 0);
$txRef   = trim($data['tx_ref'] ?? '');
$amount  = (float)($data['amount'] ?? 0);

if (!$flwTxId || !$txRef || $amount < 100) {
    jsonResponse(false, 'Invalid verification data.');
}

$pdo = getDB();
$uid = $currentUser['id'];

// ── Check payment record exists and is still pending ──────
$stmt = $pdo->prepare(
    'SELECT * FROM payments WHERE tx_ref = ? AND user_id = ? AND status = ? LIMIT 1'
);
$stmt->execute([$txRef, $uid, 'pending']);
$payment = $stmt->fetch();

if (!$payment) {
    jsonResponse(false, 'Payment record not found or already processed.');
}

// ── Verify with Flutterwave API ───────────────────────────
$flwSecret = defined('FLUTTERWAVE_SECRET_KEY') ? FLUTTERWAVE_SECRET_KEY : '';

$ch = curl_init("https://api.flutterwave.com/v3/transactions/{$flwTxId}/verify");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $flwSecret,
        'Content-Type: application/json',
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$response = curl_exec($ch);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr || !$response) {
    error_log('Flutterwave verify curl error: ' . $curlErr);
    jsonResponse(false, 'Could not reach payment gateway. Please contact support.');
}

$flwData = json_decode($response, true);

// ── Validate response ─────────────────────────────────────
if (
    !isset($flwData['status']) ||
    $flwData['status'] !== 'success' ||
    $flwData['data']['status'] !== 'successful' ||
    $flwData['data']['tx_ref'] !== $txRef ||
    (float)$flwData['data']['amount'] < $amount ||
    $flwData['data']['currency'] !== 'NGN'
) {
    // Mark payment as failed
    $pdo->prepare('UPDATE payments SET status=?, flw_ref=? WHERE tx_ref=?')
        ->execute(['failed', $flwData['data']['flw_ref'] ?? '', $txRef]);

    jsonResponse(false, 'Payment verification failed. If you were charged, please contact support with ref: ' . $txRef);
}

$flwRef = $flwData['data']['flw_ref'];

// ── Credit user wallet (atomic transaction) ───────────────
try {
    $pdo->beginTransaction();

    // Lock user row
    $userRow = $pdo->prepare('SELECT balance FROM users WHERE id = ? FOR UPDATE');
    $userRow->execute([$uid]);
    $balBefore = (float)$userRow->fetchColumn();
    $balAfter  = $balBefore + $amount;

    // Update balance
    $pdo->prepare('UPDATE users SET balance = ? WHERE id = ?')
        ->execute([$balAfter, $uid]);

    // Mark payment successful
    $pdo->prepare(
        'UPDATE payments SET status=?, flw_ref=?, verified_at=NOW() WHERE tx_ref=?'
    )->execute(['successful', $flwRef, $txRef]);

    // Insert transaction record
    $pdo->prepare(
        'INSERT INTO transactions
         (user_id, type, amount, balance_before, balance_after, reference, status, note)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $uid, 'topup', $amount, $balBefore, $balAfter,
        $txRef, 'success', 'Flutterwave top up'
    ]);

    $pdo->commit();

    // Update session
    $_SESSION['user_balance'] = $balAfter;

    jsonResponse(true, 'Wallet credited successfully!', [
        'new_balance'  => formatMoney($balAfter),
        'amount_added' => formatMoney($amount),
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Topup credit error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to credit wallet. Please contact support with ref: ' . $txRef);
}