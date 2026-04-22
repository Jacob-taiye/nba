<?php
/**
 * GET /backend/user/check_boost.php?boost_id=X
 * Polls SMM panel for latest order status and updates DB
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/smm_api.php';

$boostId = (int)($_GET['boost_id'] ?? 0);
$uid     = $currentUser['id'];

if (!$boostId) jsonResponse(false, 'Invalid request.');

$pdo  = getDB();
$stmt = $pdo->prepare('SELECT * FROM boost_orders WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$boostId, $uid]);
$order = $stmt->fetch();

if (!$order) jsonResponse(false, 'Order not found.');

// Already in a terminal state — return stored
$terminal = ['completed', 'partial', 'cancelled', 'failed'];
if (in_array($order['status'], $terminal)) {
    jsonResponse(true, 'OK', [
        'status'      => $order['status'],
        'start_count' => $order['start_count'],
        'remains'     => $order['remains'],
    ]);
}

// Fetch latest from SMM API
$smm    = new SmmApi();
$result = $smm->getOrderStatus((int)$order['smm_order_id']);

if (isset($result['error'])) {
    jsonResponse(true, 'OK', ['status' => $order['status'], 'remains' => $order['remains']]);
}

// Normalize status
$statusMap = [
    'Pending'     => 'pending',
    'In progress' => 'in_progress',
    'Processing'  => 'in_progress',
    'Active'      => 'in_progress',
    'Completed'   => 'completed',
    'Partial'     => 'partial',
    'Cancelled'   => 'cancelled',
    'Canceled'    => 'cancelled',
    'Failed'      => 'failed',
];
$newStatus = $statusMap[$result['status']] ?? $order['status'];

$pdo->prepare(
    'UPDATE boost_orders SET status=?, start_count=?, remains=?, updated_at=NOW() WHERE id=?'
)->execute([$newStatus, $result['start_count'], $result['remains'], $boostId]);

jsonResponse(true, 'OK', [
    'status'      => $newStatus,
    'start_count' => $result['start_count'],
    'remains'     => $result['remains'],
]);