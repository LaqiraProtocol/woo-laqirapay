<?php

namespace LaqiraPay\Support;

/**
 * Define constants for LaqiraPay plugin.
 *
 * WordPress functions may not be available during early autoloading,
 * so fall back to empty strings when they are missing.
 */

if (function_exists('get_option')) {
    define('CONTRACT_ADDRESS', get_option('laqirapay_main_contract'));
    define('RPC_URL', get_option('laqirapay_main_rpc_url'));
} else {
    define('CONTRACT_ADDRESS', '');
    define('RPC_URL', '');
}

if (function_exists('plugins_url')) {
    define('LAQIRA_PLUGINS_URL', plugins_url('laqirapay/'));
} else {
    define('LAQIRA_PLUGINS_URL', '');
}

define('LAQIRAPAY_TOKEN_BYTE_LENGTH', 32);
define('LAQIRAPAY_JWT_ALG', 'HS256');
define('LAQIRAPAY_JWT_ALG_SIGNATURE', 'sha256');

/**
 * Fetch remote JSON securely with timeout and SSL verification.
 *
 * @param string $url     Remote URL.
 * @param int    $timeout Timeout in seconds.
 * @return array<string,mixed> Decoded JSON data or an empty array on failure.
 */
function http_get_json(string $url, int $timeout = 10): array
{
    $response = wp_remote_get(
        $url,
        [
            'timeout'     => $timeout,
            'sslverify'   => true,
            'redirection' => 3,
        ]
    );
    if (is_wp_error($response)) {
        return [];
    }
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    return is_array($data) ? $data : [];
}

/**
 * Detect whether the current request is being served over HTTPS.
 *
 * WordPress exposes is_ssl() which also accounts for proxies. When it is not
 * available (such as in CLI contexts) fall back to common server variables so
 * the caller can still determine if a secure cookie should be required.
 */
function laqirapay_is_secure_request(): bool
{
    if (function_exists('is_ssl')) {
        return is_ssl();
    }

    $https = $_SERVER['HTTPS'] ?? '';
    if ($https && strtolower((string) $https) !== 'off') {
        return true;
    }

    $scheme = $_SERVER['REQUEST_SCHEME'] ?? '';
    if (strtolower((string) $scheme) === 'https') {
        return true;
    }

    $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    if (strtolower((string) $forwardedProto) === 'https') {
        return true;
    }

    $port = $_SERVER['SERVER_PORT'] ?? '';
    return (string) $port === '443';
}

/**
 * Build the options array for the LaqiraPay JWT cookie.
 */
function laqirapay_cookie_options(int $expires): array
{
    return [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => laqirapay_is_secure_request(),
        'httponly' => true,
        'samesite' => 'Strict',
    ];
}

/**
 * Compare cart items with order items.
 *
 * @param array      $cart_items Current cart items.
 * @param \WC_Order $order      Order instance.
 * @return bool True if equal, false otherwise.
 */
function are_cart_and_order_items_equal(array $cart_items, \WC_Order $order): bool
{
    $order_items = $order->get_items();
    if (count($cart_items) !== count($order_items)) {
        return false;
    }
    $cart_products = [];
    foreach ($cart_items as $item) {
        $cart_products[$item['product_id']] = $item['quantity'];
    }
    foreach ($order_items as $item) {
        $product_id = $item->get_product_id();
        $quantity   = $item->get_quantity();
        if (!isset($cart_products[$product_id]) || $cart_products[$product_id] != $quantity) {
            return false;
        }
        unset($cart_products[$product_id]);
    }
    return empty($cart_products);
}

/**
 * Find WooCommerce order by transaction hash.
 *
 * @param string $tx_hash Transaction hash to search.
 * @return int|null Order ID or null if not found.
 */
function find_order_by_tx_hash(string $tx_hash): ?int
{
    $orders = wc_get_orders([
        'meta_key'   => 'tx_hash',
        'meta_value' => $tx_hash,
        'return'     => 'ids',
    ]);
    return !empty($orders) ? (int) $orders[0] : null;
}

/**
 * Format date and human readable diff.
 *
 * @param string|\WC_DateTime $date_string Date string or object.
 * @return string
 */
function format_date($date_string): string
{
    $date = $date_string instanceof \WC_DateTime ? $date_string->getTimestamp() : strtotime((string) $date_string);
    if ($date === false) {
        return '';
    }
    $dt  = new \DateTime('@' . $date);
    $now = new \DateTime('now', $dt->getTimezone());
    $interval = $now->diff($dt);
    if ($interval->d > 0) {
        $diff = $interval->d . ' days ago';
    } elseif ($interval->h > 0) {
        $diff = $interval->h . ' hrs ago';
    } elseif ($interval->i > 0) {
        $diff = $interval->i . ' mins ago';
    } else {
        $diff = $interval->s . ' secs ago';
    }
    $formatted = gmdate('M-d-Y h:i:s A', $date);
    return $diff . ' (' . $formatted . ')';
}
