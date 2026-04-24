<?php
/**
 * VirtualHub Pro — Database Configuration
 * ─────────────────────────────────────────
 * Place this file in: /backend/includes/db.php
 *
 * SETUP INSTRUCTIONS:
 * 1. Create a MySQL database named: virtualhub_pro
 * 2. Update DB_USER and DB_PASS below with your MySQL credentials
 * 3. Run the SQL in /backend/includes/schema.sql to create tables
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'virtualhub_pro');
define('DB_USER', 'root');        // ← Change this
define('DB_PASS', '');            // ← Change this
define('DB_CHARSET', 'utf8mb4');

// Site-wide settings
define('SITE_NAME', 'VirtualHub Pro');
define('SITE_URL', 'http://localhost/NBALOGMARKETPLACE');  // ← Change to your domain
define('FLUTTERWAVE_PUBLIC_KEY', '');   // ← Add your key
define('FLUTTERWAVE_SECRET_KEY', '');   // ← Add your key
define('WHATSAPP_NUMBER', '2348000000000');  // ← Your WhatsApp number (no +)
define('SMSPROXY_API_KEY',   '');
define('SMS_EXCHANGE_RATE',  1600);   // USD to NGN rate — update regularly
define('SMS_PROFIT_MARGIN',  1.25);   // 1.25 = 25% markup (matches your working proxy)

// Social Media Boosting (ReallySimpleSocial)
define('SMM_API_KEY', '');
define('SMM_API_URL', 'https://reallysimplesocial.com/api/v2');
define('SMM_MARKUP',  1.5);  // 50% markup over their USD rate → NGN

/**
 * Returns a PDO database connection.
 * Uses singleton pattern — only one connection per request.
 */
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_FOUND_ROWS   => true,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error but don't expose DB details to end user
            error_log('DB Connection Error: ' . $e->getMessage());
            http_response_code(500);
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please try again later.'
            ]));
        }
    }

    return $pdo;
}

/**
 * Sends a JSON response and exits.
 */
function jsonResponse(bool $success, string $message, array $extra = []): void {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

/**
 * Sanitize string input
 */
if (!function_exists('clean')) {
    function clean(string $val): string {
        return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Format currency
 */
if (!function_exists('formatMoney')) {
    function formatMoney(float $amount): string {
        return '₦' . number_format($amount, 2);
    }
}
