<?php
/**
 * VirtualHub Pro — Purchase Handler
 * Shared logic for all item purchases
 * Include in purchase endpoints, do not call directly
 */

/**
 * Deduct balance and record transaction atomically.
 * Returns ['success'=>true, 'new_balance'=>float] or throws Exception
 */
function processPurchase(
    PDO    $pdo,
    int    $userId,
    float  $price,
    string $itemType,
    int    $itemId,
    string $note          = '',
    string $itemTitle     = '',
    string $secretContent = ''
): array {
    $pdo->beginTransaction();
    try {
        // Lock user row and get current balance
        $stmt = $pdo->prepare('SELECT balance FROM users WHERE id = ? FOR UPDATE');
        $stmt->execute([$userId]);
        $balance = (float)$stmt->fetchColumn();

        if ($balance < $price) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Insufficient balance. Please top up your wallet.'];
        }

        $newBalance = $balance - $price;

        // Deduct balance
        $pdo->prepare('UPDATE users SET balance = ? WHERE id = ?')
            ->execute([$newBalance, $userId]);

        // Record transaction
        $pdo->prepare(
            'INSERT INTO transactions
             (user_id, type, item_type, item_id, amount, balance_before, balance_after, status, note)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([$userId, 'purchase', $itemType, $itemId, $price, $balance, $newBalance, 'success', $note]);

        $txId = (int)$pdo->lastInsertId();

        // Record purchase with secret content stored for future retrieval
        $pdo->prepare(
            'INSERT INTO purchases (user_id, item_type, item_id, item_title, transaction_id, amount_paid, secret_content)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([$userId, $itemType, $itemId, $itemTitle, $txId, $price, $secretContent]);

        $pdo->commit();
        return ['success' => true, 'new_balance' => $newBalance, 'tx_id' => $txId];

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Purchase error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Purchase failed. Please try again.'];
    }
}