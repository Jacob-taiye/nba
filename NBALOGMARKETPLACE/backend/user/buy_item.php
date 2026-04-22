<?php
/**
 * VirtualHub Pro — Buy Item API
 * POST /backend/user/buy_item.php
 * Body: { item_type, item_id }
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/purchase_handler.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$itemType = trim($data['item_type'] ?? '');
$itemId   = (int)($data['item_id'] ?? 0);
$uid      = $currentUser['id'];

$allowed = ['format', 'tool', 'working_picture', 'social_login'];
if (!in_array($itemType, $allowed) || $itemId <= 0) {
    jsonResponse(false, 'Invalid request.');
}

$pdo = getDB();

// ── Check not already purchased (for formats/tools/pictures) ──
if (in_array($itemType, ['format', 'tool', 'working_picture'])) {
    $check = $pdo->prepare(
        'SELECT id FROM purchases WHERE user_id=? AND item_type=? AND item_id=? LIMIT 1'
    );
    $check->execute([$uid, $itemType, $itemId]);
    $existing = $check->fetch();
    if ($existing) {
        // Already bought — return stored secret from purchases table
        $row = $pdo->prepare('SELECT item_title, secret_content FROM purchases WHERE user_id=? AND item_type=? AND item_id=? ORDER BY created_at DESC LIMIT 1');
        $row->execute([$uid, $itemType, $itemId]);
        $purchase = $row->fetch();
        if ($purchase) {
            jsonResponse(true, 'Already purchased.', [
                'secret'       => $purchase['secret_content'],
                'title'        => $purchase['item_title'],
                'already_owned'=> true,
                'new_balance'  => formatMoney((float)$currentUser['balance']),
            ]);
        }
        jsonResponse(false, 'Purchase record not found.');
    }
}

// ── Fetch item details ────────────────────────────────────
switch ($itemType) {
    case 'format':
        $stmt = $pdo->prepare('SELECT * FROM formats WHERE id=? AND is_active=1 LIMIT 1');
        break;
    case 'tool':
        $stmt = $pdo->prepare('SELECT * FROM tools WHERE id=? AND is_active=1 LIMIT 1');
        break;
    case 'working_picture':
        $stmt = $pdo->prepare('SELECT * FROM working_pictures WHERE id=? AND is_active=1 LIMIT 1');
        break;
    case 'social_login':
        $stmt = $pdo->prepare('SELECT * FROM social_logins WHERE id=? AND is_sold=0 LIMIT 1');
        break;
}
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    jsonResponse(false, 'Item not available or already sold.');
}

$price  = (float)$item['price'];
$secret = $itemType === 'social_login' ? $item['credentials'] : $item['link'];

// ── Process purchase (stores secret for future retrieval) ─
$result = processPurchase(
    $pdo, $uid, $price, $itemType, $itemId,
    $item['title'],   // note
    $item['title'],   // itemTitle
    $secret           // secretContent — saved to purchases table
);

if (!$result['success']) {
    jsonResponse(false, $result['message']);
}

// ── For social_login: mark as sold ───────────────────────
if ($itemType === 'social_login') {
    $pdo->prepare('UPDATE social_logins SET is_sold=1 WHERE id=?')->execute([$itemId]);
}

jsonResponse(true, 'Purchase successful!', [
    'secret'      => $secret,
    'new_balance' => formatMoney($result['new_balance']),
    'title'       => $item['title'],
]);