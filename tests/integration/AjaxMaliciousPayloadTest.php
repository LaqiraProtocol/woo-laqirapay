<?php

namespace {
    if (!class_exists('WP_Error')) {
        class WP_Error
        {
            public array $errors = [];
            public array $error_data = [];

            public function __construct($code = '', $message = '', $data = null)
            {
                if ($code !== '') {
                    $this->errors[$code] = [$message];
                    if ($data !== null) {
                        $this->error_data[$code] = $data;
                    }
                }
            }
        }
    }
}

namespace Tests\Integration {

use Brain\Monkey;
use LaqiraPay\Helpers\JwtHelper;
use PHPUnit\Framework\TestCase;
use function Brain\Monkey\Functions\when;

class AjaxMaliciousPayloadTest extends TestCase
{
    private array $options = [];
    private ?array $lastError = null;
    private ?array $lastSuccess = null;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        $this->options = [
            'laqirapay_jwt_secret' => 'test-secret',
            'laqirapay_api_key'    => 'provider-address',
        ];
        $this->lastError   = null;
        $this->lastSuccess = null;

        when('add_action')->justReturn(true);

        if (!function_exists('laqira_payment_create_tx_hash')) {
            require __DIR__ . '/../../app/Http/Controllers/Ajax/LegacyAjax.php';
        }

        when('wp_verify_nonce')->justReturn(true);
        when('wp_unslash')->alias(fn($value) => $value);
        when('sanitize_text_field')->alias(function ($value) {
            return trim(strip_tags((string) $value));
        });
        when('sanitize_textarea_field')->alias(function ($value) {
            return trim(strip_tags((string) $value));
        });
        when('is_wp_error')->alias(fn($thing) => $thing instanceof \WP_Error);
        when('get_option')->alias(function ($key, $default = null) {
            return $this->options[$key] ?? $default;
        });
        when('update_option')->alias(function ($key, $value) {
            $this->options[$key] = $value;
            return true;
        });
        when('wp_send_json_error')->alias(function ($data) {
            $this->lastError = $data;
        });
        when('wp_send_json_success')->alias(function ($data) {
            $this->lastSuccess = $data;
        });
        when('get_woocommerce_currency')->justReturn('USD');
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        $_POST   = [];
        $_COOKIE = [];
        unset($GLOBALS['woocommerce']);
        parent::tearDown();
    }

    private function issueJwt(): void
    {
        $helper = new JwtHelper();
        $token  = $helper->createAccessToken($this->options['laqirapay_api_key']);
        $_COOKIE['laqira_jwt'] = $token['token'];
    }

    public function test_create_tx_hash_rejects_malicious_hash(): void
    {
        $this->issueJwt();

        $payload = [
            'orderID'                 => '123',
            'slippage'                => '1.5',
            'tx_hash'                 => '0x12345<script>alert(1)</script>',
            'txStatus'                => 'pending',
            'firstTX_log'             => 'Log entry',
            'siteAdminAddressWallet'  => '0x' . str_repeat('1', 40),
            'userWallet'              => '0x' . str_repeat('2', 40),
            'reqHash'                 => '0x' . str_repeat('3', 64),
            'price'                   => '100.50',
            'asset'                   => '0x' . str_repeat('4', 40),
            'assetName'               => 'USDT',
            'assetAmount'             => '200.0',
            'exchangeRate'            => '1.1',
            'payment_type'            => 'Direct',
            'network_rpc'             => 'https://rpc.example.com',
            'network_explorer'        => 'https://explorer.example.com',
        ];

        $_POST = [
            'security'   => 'nonce',
            'laqiradata' => json_encode($payload, JSON_THROW_ON_ERROR),
        ];

        laqira_payment_create_tx_hash();

        $this->assertNotNull($this->lastError, 'Expected JSON error response for invalid transaction hash.');
        $this->assertSame('error', $this->lastError['result']);
        $this->assertSame('Invalid transaction hash.', $this->lastError['error']);
        $this->assertNull($this->lastSuccess);
    }

    public function test_payment_confirmation_rejects_malicious_wallet(): void
    {
        $this->issueJwt();

        $payload = [
            'orderID'                => '321',
            'slippage'               => '0.5',
            'tx_hash'                => '0x' . str_repeat('a', 64),
            'siteAdminAddressWallet' => '0x' . str_repeat('1', 40),
            'userWallet'             => '<script>alert(1)</script>',
            'reqHash'                => '0x' . str_repeat('b', 64),
            'price'                  => '50.00',
            'asset'                  => '0x' . str_repeat('4', 40),
            'assetName'              => 'USDC',
            'assetAmount'            => '10.0',
            'payment_type'           => 'InApp',
            'network_rpc'            => 'https://rpc.example.com',
        ];

        $_POST = [
            'security'   => 'nonce',
            'laqiradata' => json_encode($payload, JSON_THROW_ON_ERROR),
        ];

        laqira_payment_confirmation();

        $this->assertNotNull($this->lastError, 'Expected JSON error response for malicious wallet address.');
        $this->assertSame('error', $this->lastError['result']);
        $this->assertSame('Invalid customer wallet address.', $this->lastError['error']);
        $this->assertNull($this->lastSuccess);
    }
}

}
