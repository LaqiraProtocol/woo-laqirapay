<?php
require __DIR__ . '/../vendor/autoload.php';

if (!defined('LAQIRAPAY_JWT_ALG')) {
    define('LAQIRAPAY_JWT_ALG', 'HS256');
}
if (!defined('LAQIRAPAY_JWT_ALG_SIGNATURE')) {
    define('LAQIRAPAY_JWT_ALG_SIGNATURE', 'sha256');
}
if (!defined('LAQIRA_PLUGINS_URL')) {
    define('LAQIRA_PLUGINS_URL', 'http://example.com/');
}
if (!defined('LAQIRAPAY_PLUGIN_DIR')) {
    define('LAQIRAPAY_PLUGIN_DIR', dirname(__DIR__) . '/');
}
if (!defined('CONTRACT_ADDRESS')) {
    define('CONTRACT_ADDRESS', '0x0000000000000000000000000000000000000000');
}
if (!defined('RPC_URL')) {
    define('RPC_URL', 'http://localhost');
}
if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', '/tmp');
}

if (!function_exists('trailingslashit')) {
    function trailingslashit($string) {
        return rtrim($string, '/\\') . '/';
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html_e')) {
    function esc_html_e($text, $domain = 'default') {
        echo esc_html__($text, $domain);
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default')
    {
        return esc_attr($text);
    }
}

if (!function_exists('esc_attr_e')) {
    function esc_attr_e($text, $domain = 'default')
    {
        echo esc_attr__($text, $domain);
    }
}

