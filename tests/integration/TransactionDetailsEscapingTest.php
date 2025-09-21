<?php

namespace Tests\Integration;

use Brain\Monkey;
use LaqiraPay\Helpers\JwtHelper;
use LaqiraPay\Helpers\WooCommerceHelper;
use LaqiraPay\Hooks\ExtrasService;
use Mockery;
use PHPUnit\Framework\TestCase;
use function Brain\Monkey\Functions\when;

class TransactionDetailsEscapingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        when('esc_url')->alias(static function ($url) {
            $url = trim((string) $url);
            if ($url === '' || preg_match('#^javascript:#i', $url)) {
                return '';
            }

            return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        });
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        Mockery::close();
        parent::tearDown();
    }

    private function createExtrasService(): ExtrasService
    {
        $wooCommerceHelper = Mockery::mock(WooCommerceHelper::class);
        $jwtHelper = Mockery::mock(JwtHelper::class);

        return new ExtrasService($wooCommerceHelper, $jwtHelper);
    }

    public function test_render_tx_details_escapes_malicious_meta(): void
    {
        $order = new class {
            private array $meta = [
                'TokenName'        => 'USDT<script>alert(1)</script>',
                'exchange_rate'    => '1<script>alert(2)</script>',
                'TokenAmount'      => '10<script>alert(3)</script>',
                'tx_hash'          => '0x123<script>alert(4)</script>',
                'network_explorer' => 'https://explorer.example.com',
            ];

            public function get_meta($key)
            {
                return $this->meta[$key] ?? '';
            }
        };

        $service = $this->createExtrasService();
        $method = new \ReflectionMethod(ExtrasService::class, 'render_tx_details');
        $method->setAccessible(true);

        $html = $method->invoke($service, $order);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('USDT&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringContainsString('1&lt;script&gt;alert(2)&lt;/script&gt;', $html);
        $this->assertStringContainsString('10&lt;script&gt;alert(3)&lt;/script&gt;', $html);
        $this->assertStringContainsString('0x123&lt;script&gt;alert(4)&lt;/script&gt;', $html);
        $this->assertStringContainsString('https://explorer.example.com/tx/0x123%3Cscript%3Ealert%284%29%3C%2Fscript%3E', $html);
    }

    public function test_email_order_meta_fields_escape_values(): void
    {
        $order = new class {
            private array $meta = [
                'TokenName'        => 'USDC<script>alert(5)</script>',
                'exchange_rate'    => '2<script>alert(6)</script>',
                'TokenAmount'      => '20<script>alert(7)</script>',
                'tx_hash'          => '0xabc<script>alert(8)</script>',
                'network_explorer' => 'https://explorer.example.com',
            ];

            public function get_meta($key)
            {
                return $this->meta[$key] ?? '';
            }

            public function get_payment_method(): string
            {
                return 'WC_laqirapay';
            }
        };

        $service = $this->createExtrasService();

        $fields = $service->woocommerce_email_order_meta_fields([], false, $order);

        $this->assertArrayHasKey('TokenName', $fields);
        $this->assertArrayHasKey('exchange_rate', $fields);
        $this->assertArrayHasKey('TokenAmount', $fields);
        $this->assertArrayHasKey('tx_hash', $fields);

        foreach ($fields as $field) {
            $this->assertStringNotContainsString('<script>', $field['value']);
        }

        $this->assertStringContainsString('USDC&lt;script&gt;alert(5)&lt;/script&gt;', $fields['TokenName']['value']);
        $this->assertStringContainsString('https://explorer.example.com/tx/0xabc%3Cscript%3Ealert%288%29%3C%2Fscript%3E', $fields['tx_hash']['value']);
        $this->assertStringContainsString('0xabc&lt;script&gt;alert(8)&lt;/script&gt;', $fields['tx_hash']['value']);
    }
}
