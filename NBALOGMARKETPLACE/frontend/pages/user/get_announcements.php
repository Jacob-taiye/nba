<?php
/**
 * GET /backend/user/get_announcements.php
 * Returns all active announcements for the ticker
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$stmt = $pdo->query(
    "SELECT id, message, type FROM announcements WHERE is_active = 1 ORDER BY created_at DESC"
);
echo json_encode($stmt->fetchAll());