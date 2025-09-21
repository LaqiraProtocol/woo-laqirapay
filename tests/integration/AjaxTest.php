<?php
namespace Tests\Integration;

use Brain\Monkey;
use function Brain\Monkey\Functions\when;
use PHPUnit\Framework\TestCase;

class AjaxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        when('add_action')->justReturn(true);
        if (!function_exists('laqirapay_update_cart_data')) {
            require __DIR__ . '/../../app/Http/Controllers/Ajax/LegacyAjax.php';
        }
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_laqirapay_update_cart_data()
    {
        when('check_ajax_referer')->justReturn(true);
        when('get_woocommerce_currencies')->justReturn(['USD' => 'USD']);
        when('get_woocommerce_currency')->justReturn('USD');
        when('wp_get_active_and_valid_plugins')->justReturn([]);
        when('wp_get_active_network_plugins')->justReturn([]);
        $captured = null;
        when('wp_send_json_success')->alias(function ($data) use (&$captured) { $captured = $data; });

        laqirapay_update_cart_data();
        $this->assertEquals(['originalOrderAmount' => 0, 'cartTotal' => 0], $captured);
    }
}
