<?php
/**
 * VirtualHub Pro — ReallySimpleSocial SMM API Wrapper
 * API: https://reallysimplesocial.com/api/v2
 * Method: POST
 */
class SmmApi {

    private string $apiKey;
    private string $apiUrl;
    private int    $exchangeRate = 1600;
    private float  $markup;

    public function __construct() {
        $this->apiKey       = defined('SMM_API_KEY')     ? SMM_API_KEY     : '';
        $this->apiUrl       = defined('SMM_API_URL')     ? SMM_API_URL     : 'https://reallysimplesocial.com/api/v2';
        $this->markup       = defined('SMM_MARKUP')      ? (float)SMM_MARKUP : 1.5;
        if (defined('SMS_EXCHANGE_RATE')) $this->exchangeRate = (int)SMS_EXCHANGE_RATE;
    }

    // ── Get all services ──────────────────────────────────────────────────
    // Returns array grouped by category, with NGN price applied
    public function getServices(): array {
        $raw = $this->call(['action' => 'services']);
        if (!is_array($raw)) return [];

        $grouped = [];
        foreach ($raw as $svc) {
            $cat  = $svc['category'] ?? 'Other';
            $rate = (float)($svc['rate'] ?? 0);

            // rate is per 1000 in USD — convert to NGN with markup
            $rateNgn = ceil(($rate * $this->exchangeRate * $this->markup) / 1000 * 1000) / 1000;

            $grouped[$cat][] = [
                'service'  => (int)$svc['service'],
                'name'     => $svc['name']     ?? '',
                'type'     => $svc['type']     ?? 'Default',
                'rate_usd' => $rate,
                'rate_ngn' => round($rateNgn, 4),  // per 1000
                'min'      => (int)($svc['min'] ?? 0),
                'max'      => (int)($svc['max'] ?? 0),
                'refill'   => (bool)($svc['refill'] ?? false),
                'cancel'   => (bool)($svc['cancel'] ?? false),
            ];
        }
        return $grouped;
    }

    // ── Place an order ────────────────────────────────────────────────────
    // Returns ['order_id' => int] or ['error' => string]
    public function placeOrder(int $serviceId, string $link, int $quantity): array {
        $res = $this->call([
            'action'   => 'add',
            'service'  => $serviceId,
            'link'     => $link,
            'quantity' => $quantity,
        ]);

        if (isset($res['order'])) {
            return ['order_id' => (int)$res['order']];
        }
        $err = $res['error'] ?? 'Failed to place order.';
        return ['error' => $err];
    }

    // ── Get order status ──────────────────────────────────────────────────
    public function getOrderStatus(int $orderId): array {
        $res = $this->call([
            'action' => 'status',
            'order'  => $orderId,
        ]);
        if (isset($res['error'])) return ['error' => $res['error']];
        return [
            'status'      => $res['status']      ?? 'Unknown',
            'remains'     => (int)($res['remains']     ?? 0),
            'start_count' => (int)($res['start_count'] ?? 0),
            'charge'      => (float)($res['charge']    ?? 0),
            'currency'    => $res['currency']    ?? 'USD',
        ];
    }

    // ── Get API balance ───────────────────────────────────────────────────
    public function getBalance(): array {
        $res = $this->call(['action' => 'balance']);
        return [
            'balance'  => (float)($res['balance']  ?? 0),
            'currency' => $res['currency'] ?? 'USD',
        ];
    }

    // ── Calculate NGN cost for a specific quantity ────────────────────────
    // rate_ngn is per 1000, quantity is the order amount
    public function calculateCost(float $rateNgn, int $quantity): int {
        return (int)ceil(($rateNgn / 1000) * $quantity);
    }

    // ── Platform icons/colors (for UI) ───────────────────────────────────
    public static function platformMeta(string $categoryOrName): array {
        $name = strtolower($categoryOrName);
        if (str_contains($name, 'instagram'))  return ['fab fa-instagram', '#e1306c', '#fde8ef'];
        if (str_contains($name, 'tiktok'))     return ['fab fa-tiktok',    '#010101', '#f0f0f0'];
        if (str_contains($name, 'facebook'))   return ['fab fa-facebook',  '#1877f2', '#e7f0fd'];
        if (str_contains($name, 'twitter') || str_contains($name, 'x '))
                                               return ['fab fa-twitter',   '#1da1f2', '#e8f5fe'];
        if (str_contains($name, 'youtube'))    return ['fab fa-youtube',   '#ff0000', '#ffe8e8'];
        if (str_contains($name, 'telegram'))   return ['fab fa-telegram',  '#2ca5e0', '#e8f7fd'];
        if (str_contains($name, 'spotify'))    return ['fab fa-spotify',   '#1db954', '#e6f9ed'];
        if (str_contains($name, 'snapchat'))   return ['fab fa-snapchat',  '#f7b731', '#fffde7'];
        if (str_contains($name, 'linkedin'))   return ['fab fa-linkedin',  '#0a66c2', '#e8f1fb'];
        if (str_contains($name, 'whatsapp'))   return ['fab fa-whatsapp',  '#25d366', '#e8faf0'];
        if (str_contains($name, 'google'))     return ['fab fa-google',    '#ea4335', '#fdecea'];
        if (str_contains($name, 'twitch'))     return ['fab fa-twitch',    '#9146ff', '#f0ebff'];
        return ['fas fa-share-nodes', '#1a8cff', '#e8f4ff'];
    }

    // ── Private POST helper ───────────────────────────────────────────────
    private function call(array $params): mixed {
        $params['key'] = $this->apiKey;

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'VirtualHubPro/1.0',
        ]);
        $result = curl_exec($ch);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($err) { error_log("SmmApi cURL error: $err"); return null; }
        $decoded = json_decode($result, true);
        return $decoded ?? null;
    }
}