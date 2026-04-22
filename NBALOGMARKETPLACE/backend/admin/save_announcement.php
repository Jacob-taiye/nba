<?php
/**
 * POST /backend/admin/save_announcement.php
 * POST /backend/admin/delete_announcement.php
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin_guard.php';

$action = $_GET['action'] ?? 'save';
$d      = json_decode(file_get_contents('php://input'), true);

if ($action === 'delete') {
    $id = (int)($d['id'] ?? 0);
    if (!$id) jsonResponse(false, 'Invalid ID.');
    $pdo->prepare("DELETE FROM announcements WHERE id = ?")->execute([$id]);
    jsonResponse(true, 'Announcement deleted.');
}

if ($action === 'toggle') {
    $id     = (int)($d['id'] ?? 0);
    $active = (int)($d['is_active'] ?? 0);
    if (!$id) jsonResponse(false, 'Invalid ID.');
    $pdo->prepare("UPDATE announcements SET is_active = ? WHERE id = ?")->execute([$active, $id]);
    jsonResponse(true, $active ? 'Announcement activated.' : 'Announcement hidden.');
}

// Save (add or edit)
$id      = (int)($d['id']      ?? 0);
$message = trim($d['message']  ?? '');
$type    = clean($d['type']    ?? 'info');

if (!$message) jsonResponse(false, 'Message cannot be empty.');
if (!in_array($type, ['info','warning','success','danger'])) $type = 'info';

if ($id) {
    $pdo->prepare("UPDATE announcements SET message=?, type=? WHERE id=?")
        ->execute([$message, $type, $id]);
    jsonResponse(true, 'Announcement updated.');
} else {
    $pdo->prepare("INSERT INTO announcements (message, type, created_by) VALUES (?,?,?)")
        ->execute([$message, $type, $currentAdmin['id']]);
    jsonResponse(true, 'Announcement created.', ['id' => (int)$pdo->lastInsertId()]);
}