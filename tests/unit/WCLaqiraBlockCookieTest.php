<?php

namespace Automattic\WooCommerce\Blocks\Payments\Integrations;

if (!class_exists(AbstractPaymentMethodType::class)) {
    abstract class AbstractPaymentMethodType {}
}

namespace Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use LaqiraPay\Domain\Services\UtilityService;
use LaqiraPay\Helpers\JwtHelper;
use LaqiraPay\Helpers\WooCommerceHelper;
use LaqiraPay\Services\BlockchainService;
use Mockery;
use PHPUnit\Framework\TestCase;

class WCLaqiraBlockCookieTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        unset($_COOKIE['laqira_jwt']);
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        Mockery::close();
        parent::tearDown();
        unset($_COOKIE['laqira_jwt']);
    }

    /**
     * Create a block instance configured for either HTTP or HTTPS requests.
     */
    private function createBlockForScheme(bool $secure): \WC_laqirapay_Block
    {
        Functions\when('add_action')->justReturn(true);
        Functions\when('is_checkout')->justReturn(true);
        Functions\when('is_wc_endpoint_url')->justReturn(false);
        Functions\when('is_ssl')->justReturn($secure);

        $wooCommerceHelper = Mockery::mock(WooCommerceHelper::class);

        $jwtHelper = Mockery::mock(JwtHelper::class);
        $jwtHelper->shouldReceive('createAccessToken')->andReturn(['token' => 'jwt-token']);

        $blockchainService = Mockery::mock(BlockchainService::class);
        $blockchainService->shouldReceive('getProviderLocal')->andReturn('0x0000000000000000000000000000000000000002');

        $utilityService = Mockery::mock(UtilityService::class);

        return new \WC_laqirapay_Block($wooCommerceHelper, $jwtHelper, $blockchainService, $utilityService);
    }

    public function test_sets_cookie_without_secure_flag_on_http_requests(): void
    {
        $block = $this->createBlockForScheme(false);

        Functions\expect('setcookie')
            ->once()
            ->with(
                'laqira_jwt',
                'jwt-token',
                Mockery::on(static function ($options) {
                    return is_array($options)
                        && $options['secure'] === false
                        && $options['httponly'] === true
                        && $options['samesite'] === 'Strict'
                        && ($options['path'] ?? null) === '/';
                })
            )
            ->andReturnTrue();

        $block->set_laqira_jwt_cookie();
        $this->addToAssertionCount(1);
    }

    public function test_sets_cookie_with_secure_flag_on_https_requests(): void
    {
        $block = $this->createBlockForScheme(true);

        Functions\expect('setcookie')
            ->once()
            ->with(
                'laqira_jwt',
                'jwt-token',
                Mockery::on(static function ($options) {
                    return is_array($options)
                        && $options['secure'] === true
                        && $options['httponly'] === true
                        && $options['samesite'] === 'Strict'
                        && ($options['path'] ?? null) === '/';
                })
            )
            ->andReturnTrue();

        $block->set_laqira_jwt_cookie();
        $this->addToAssertionCount(1);
    }
}
