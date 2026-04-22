<?php
/**
 * GET /backend/user/get_smm_services.php
 * Returns all available SMM services grouped by category, with NGN prices
 * Cached in DB/file for 1 hour to avoid hammering the API
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/smm_api.php';

$cacheFile = sys_get_temp_dir() . '/vh_smm_services.json';
$cacheTtl  = 3600; // 1 hour

// Serve from cache if fresh
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    echo file_get_contents($cacheFile);
    exit;
}

// Fetch fresh from API
$smm      = new SmmApi();
$services = $smm->getServices();

if (empty($services)) {
    // Return cached even if stale rather than empty
    if (file_exists($cacheFile)) {
        echo file_get_contents($cacheFile);
    } else {
        echo json_encode([]);
    }
    exit;
}

$output = json_encode($services);
file_put_contents($cacheFile, $output);
echo $output;