<?php
/**
 * GET /backend/user/get_services.php?country=1
 * Returns all available services + NGN prices for a country
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/sms_activate.php';

$country  = (int)($_GET['country'] ?? 1);
$sms      = new SmsActivate();
$services = $sms->getServices($country);

// Always return array (empty if none available)
echo json_encode($services);