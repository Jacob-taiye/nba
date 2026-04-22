<?php
/**
 * VirtualHub Pro — JejeLayeLogs SMS Proxy Wrapper
 * Based on working implementation from sms_proxy.php
 *
 * API: https://verify.jejelayelogs.com.ng/smsproxy/api_b.php
 * Short service codes: wa, fb, tg, go, ig, tw, nf, mc, ds, tk, yl, uk, am
 * Price response format: $data[$country][$service_code]['cost'] / ['count']
 */

class SmsActivate {

    private string $apiKey;
    private string $baseUrl;
    private int    $exchangeRate = 1600;
    private float  $profitMargin = 1.25;

    // Short codes → display names (matches working proxy exactly)
    public static array $serviceNames = [
        'wa' => 'WhatsApp',
        'fb' => 'Facebook',
        'tg' => 'Telegram',
        'go' => 'Google/Gmail',
        'ig' => 'Instagram',
        'tw' => 'Twitter (X)',
        'nf' => 'Netflix',
        'mc' => 'MasterCard',
        'ds' => 'Discord',
        'tk' => 'TikTok',
        'yl' => 'Yalla',
        'uk' => 'Uber',
        'am' => 'Amazon',
    ];

    // Short codes → Font Awesome icon + brand color
    public static array $serviceIcons = [
        'wa' => ['fab fa-whatsapp',  '#25d366'],
        'fb' => ['fab fa-facebook',  '#1877f2'],
        'tg' => ['fab fa-telegram',  '#2ca5e0'],
        'go' => ['fab fa-google',    '#ea4335'],
        'ig' => ['fab fa-instagram', '#e1306c'],
        'tw' => ['fab fa-twitter',   '#1da1f2'],
        'nf' => ['fab fa-netflix',   '#e50914'],
        'mc' => ['fas fa-credit-card','#eb001b'],
        'ds' => ['fab fa-discord',   '#5865f2'],
        'tk' => ['fab fa-tiktok',    '#010101'],
        'yl' => ['fas fa-comments',  '#f7b731'],
        'uk' => ['fab fa-uber',      '#000000'],
        'am' => ['fab fa-amazon',    '#ff9900'],
    ];

    public function __construct() {
        $this->apiKey  = defined('SMSPROXY_API_KEY')  ? SMSPROXY_API_KEY  : '';
        $this->baseUrl = defined('SMSPROXY_BASE_URL')  ? SMSPROXY_BASE_URL : 'https://verify.jejelayelogs.com.ng/smsproxy/api_b.php';
        if (defined('SMS_EXCHANGE_RATE')) $this->exchangeRate = (int)SMS_EXCHANGE_RATE;
        if (defined('SMS_PROFIT_MARGIN')) $this->profitMargin = (float)SMS_PROFIT_MARGIN;
    }

    // ── Get available services for a country ──────────────────────────────
    // Returns array of services with NGN price, or empty array
    public function getServices(int $country = 1): array {
        $url      = "{$this->baseUrl}?api_key={$this->apiKey}&action=getPrices&country={$country}";
        $response = @file_get_contents($url);

        if (!$response) return [];

        $data = json_decode($response, true);
        if (!isset($data[$country])) return [];

        $services = [];
        foreach ($data[$country] as $code => $info) {
            if ((int)($info['count'] ?? 0) > 0) {
                $name    = self::$serviceNames[$code] ?? strtoupper($code);
                $icons   = self::$serviceIcons[$code] ?? ['fas fa-globe', '#6b8ba4'];
                $costNgn = (int)ceil(($info['cost'] * $this->exchangeRate) * $this->profitMargin);
                $services[] = [
                    'code'  => $code,
                    'name'  => $name,
                    'count' => (int)$info['count'],
                    'cost'  => $costNgn,   // already in NGN
                    'icon'  => $icons[0],
                    'color' => $icons[1],
                ];
            }
        }

        usort($services, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $services;
    }

    // ── Get price for one specific service ────────────────────────────────
    public function getPrice(string $service, int $country = 1): ?array {
        $url      = "{$this->baseUrl}?api_key={$this->apiKey}&action=getPrices&country={$country}";
        $response = @file_get_contents($url);
        if (!$response) return null;

        $data = json_decode($response, true);
        if (!isset($data[$country][$service])) return null;

        $info = $data[$country][$service];
        return [
            'price_usd' => (float)$info['cost'],
            'price_ngn' => (int)ceil(($info['cost'] * $this->exchangeRate) * $this->profitMargin),
            'count'     => (int)$info['count'],
            'available' => (int)$info['count'] > 0,
        ];
    }

    // ── Rent a Number ──────────────────────────────────────────────────────
    // Returns ['id' => string, 'number' => string] or ['error' => string]
    public function buyNumber(string $service, int $country = 1): array {
        $url = "{$this->baseUrl}?api_key={$this->apiKey}&action=getNumber&service={$service}&country={$country}";
        $res = @file_get_contents($url);

        if (!$res) return ['error' => 'Failed to connect to SMS provider. Please try again.'];

        $res = trim($res);

        // Check for known error strings first
        $errorMap = [
            'NO_MONEY'               => 'Service temporarily unavailable. Please contact support.',
            'NO_NUMBERS'             => 'No numbers available for this service right now. Try another country.',
            'WRONG_SERVICE'          => 'Invalid service selected.',
            'BAD_SERVICE'            => 'Service not supported.',
            'WRONG_COUNTRY'          => 'Invalid country selected.',
            'TOO_MANY_ACTIVE_SESSIONS' => 'Too many active sessions. Please wait a moment.',
            'ACCOUNT_INACTIVE'       => 'Service inactive. Please contact support.',
        ];
        if (isset($errorMap[$res])) return ['error' => $errorMap[$res]];

        // Try JSON format first (some APIs return JSON)
        $json = json_decode($res, true);
        if (isset($json['status']) && $json['status'] === 'success') {
            return ['id' => (string)$json['order_id'], 'number' => (string)$json['number']];
        }

        // Standard format: ACCESS_NUMBER:id:number
        if (str_starts_with($res, 'ACCESS_NUMBER:')) {
            $parts = explode(':', $res);
            if (count($parts) >= 3) {
                return ['id' => trim($parts[1]), 'number' => trim($parts[2])];
            }
        }

        return ['error' => "Unexpected API response: $res"];
    }

    // ── Poll for SMS ───────────────────────────────────────────────────────
    // Returns ['status' => 'waiting'|'received'|'cancelled', 'code' => string|null]
    public function checkSms(string $activationId): array {
        $url = "{$this->baseUrl}?api_key={$this->apiKey}&action=getStatus&id={$activationId}";
        $res = @file_get_contents($url);

        if (!$res) return ['status' => 'waiting', 'code' => null];

        $res = trim($res);

        if (str_starts_with($res, 'STATUS_OK:')) {
            return ['status' => 'received', 'code' => substr($res, 10)];
        }
        if ($res === 'STATUS_CANCEL') {
            return ['status' => 'cancelled', 'code' => null];
        }
        // STATUS_WAIT_CODE, STATUS_WAIT_RETRY, anything else = still waiting
        return ['status' => 'waiting', 'code' => null];
    }

    // ── Cancel Number (status=8) ───────────────────────────────────────────
    public function cancelNumber(string $activationId): bool {
        $url = "{$this->baseUrl}?api_key={$this->apiKey}&action=setStatus&id={$activationId}&status=8";
        $res = @file_get_contents($url);
        return $res && str_contains(trim($res), 'ACCESS_CANCEL');
    }

    // ── Complete Number (status=6) ─────────────────────────────────────────
    public function finishNumber(string $activationId): bool {
        $url = "{$this->baseUrl}?api_key={$this->apiKey}&action=setStatus&id={$activationId}&status=6";
        $res = @file_get_contents($url);
        return $res && str_contains(trim($res), 'ACCESS_READY');
    }

    // ── Country list (from their docs) ────────────────────────────────────
    public static function popularCountries(): array {
        return [
            ['name' => 'Ukraine',          'id' => 1],
            ['name' => 'Nigeria',          'id' => 19],
            ['name' => 'Ghana',            'id' => 38],
            ['name' => 'Kenya',            'id' => 8],
            ['name' => 'South Africa',     'id' => 31],
            ['name' => 'United Kingdom',   'id' => 16],
            ['name' => 'United States',    'id' => 187],
            ['name' => 'Russia',           'id' => 0],
            ['name' => 'Indonesia',        'id' => 6],
            ['name' => 'Philippines',      'id' => 4],
            ['name' => 'Malaysia',         'id' => 7],
            ['name' => 'Canada',           'id' => 36],
            ['name' => 'Germany',          'id' => 43],
            ['name' => 'France',           'id' => 78],
            ['name' => 'Brazil',           'id' => 73],
            ['name' => 'Pakistan',         'id' => 66],
            ['name' => 'Bangladesh',       'id' => 60],
            ['name' => 'Cambodia',         'id' => 24],
            ['name' => 'China',            'id' => 3],
            ['name' => 'Egypt',            'id' => 21],
            ['name' => 'Colombia',         'id' => 33],
            ['name' => 'Mexico',           'id' => 54],
            ['name' => 'India',            'id' => 22],
        ];
    }
}