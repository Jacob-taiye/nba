<?php
/**
 * GET /backend/user/get_number_prices.php?service=wa&country=1
 * Returns NGN price for a specific service/country
 * Price is already calculated by the wrapper (rate × margin)
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/sms_activate.php';

$service = trim($_GET['service'] ?? 'wa');
$country = (int)($_GET['country'] ?? 1);  // default = Ukraine (cheapest, most available)

$sms    = new SmsActivate();
$result = $sms->getPrice($service, $country);

if (!$result || !$result['available']) {
    jsonResponse(false, 'No numbers available for this service/country.', [
        'available' => false,
        'count'     => 0,
    ]);
}

jsonResponse(true, 'OK', [
    'price_ngn' => $result['price_ngn'],
    'price_usd' => $result['price_usd'],
    'count'     => $result['count'],
    'available' => true,
]);