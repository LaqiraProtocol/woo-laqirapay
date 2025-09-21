<?php
namespace Tests\Integration;

use LaqiraPay\Http\Controllers\Frontend\PaymentController;
use LaqiraPay\Helpers\JwtHelper;
use LaqiraPay\Helpers\WooCommerceHelper;
use LaqiraPay\Services\BlockchainService;
use LaqiraPay\Domain\Services\UtilityService;
use Brain\Monkey;
use Brain\Monkey\Functions;
use function Brain\Monkey\Functions\when;
use Mockery;
use PHPUnit\Framework\TestCase;

class PaymentControllerTest extends TestCase
{
    private array $tempFiles = [];

    private array $tempDirectories = [];

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        foreach (array_reverse($this->tempDirectories) as $directory) {
            if (is_dir($directory) && ! glob($directory . '/*')) {
                rmdir($directory);
            }
        }

        $this->tempFiles       = [];
        $this->tempDirectories = [];

        unset($GLOBALS['woocommerce']);

        Monkey\tearDown();
        Mockery::close();
        parent::tearDown();
    }

    private function ensureAssetManifest(): void
    {
        $assetPath = LAQIRAPAY_PLUGIN_DIR . '/build/laqiraPayMain.asset.php';
        $assetDir  = dirname($assetPath);
        if (! is_dir($assetDir)) {
            mkdir($assetDir, 0777, true);
            $this->tempDirectories[] = $assetDir;
        }
        if (! file_exists($assetPath)) {
            file_put_contents($assetPath, "<?php return ['dependencies' => ['jquery'], 'version' => '1.0'];");
            $this->tempFiles[] = $assetPath;
        }
    }

    private function createControllerForCookieTest(bool $secure): PaymentController
    {
        when('__')->returnArg();
        when('is_ssl')->justReturn($secure);
        when('current_time')->alias(static function ($type, $gmt = 0) {
            return 1700000000;
        });
        when('is_admin')->justReturn(false);
        when('admin_url')->alias(static function ($path = '', $scheme = 'admin') {
            return 'https://example.com/wp-admin/' . ltrim($path, '/');
        });
        when('wp_create_nonce')->justReturn('nonce-value');
        when('get_home_url')->justReturn('https://example.com');
        when('wc_get_page_id')->alias(static function ($page) {
            return $page === 'shop' ? 10 : 20;
        });
        when('get_permalink')->alias(static function ($id) {
            return 'https://example.com/page-' . $id;
        });
        when('get_woocommerce_currencies')->justReturn(['USD' => 'US Dollar']);
        when('get_woocommerce_currency')->justReturn('USD');
        when('wp_localize_script')->alias(static function (...$args) {
            return true;
        });
        when('wp_enqueue_script')->alias(static function (...$args) {
            return true;
        });
        when('wp_register_style')->alias(static function (...$args) {
            return true;
        });
        when('wp_enqueue_style')->alias(static function (...$args) {
            return true;
        });

        $settingsMock = Mockery::mock('alias:LaqiraPay\\Domain\\Models\\Settings');
        $settingsMock->shouldReceive('get')->andReturnUsing(static function ($key, $default = null) {
            if ($key === 'laqirapay_walletconnect_project_id') {
                return 'wallet-project';
            }
            if (str_starts_with($key, 'laqirapay_exchange_rate_')) {
                return '1';
            }

            return $default;
        });

        $translationsMock = Mockery::mock('alias:LaqiraPay\\Support\\LaqiraPayTranslations');
        $translationsMock->shouldReceive('get_translations')->andReturn(['pay' => 'Pay now']);

        $wooCommerceHelper = Mockery::mock(WooCommerceHelper::class);
        $wooCommerceHelper->shouldReceive('getWcpi')->andReturn('wcpi-token');

        $jwtHelper = Mockery::mock(JwtHelper::class);
        $jwtHelper->shouldReceive('createAccessToken')->andReturn(['token' => 'jwt-token']);

        $blockchainService = Mockery::mock(BlockchainService::class);
        $blockchainService->shouldReceive('getNetworks')->andReturn(['network']);
        $blockchainService->shouldReceive('getNetworksAssets')->andReturn(['asset']);
        $blockchainService->shouldReceive('getProviderLocal')->andReturn('0x0000000000000000000000000000000000000002');
        $blockchainService->shouldReceive('getStableCoins')->andReturn(['USDT']);

        $utilityService = Mockery::mock(UtilityService::class);
        $utilityService->shouldReceive('detectRtl')->andReturn(false);

        $cartMock = Mockery::mock();
        $cartMock->shouldReceive('get_total')->with('edit')->andReturn('100.00');
        $woocommerce = (object) ['cart' => $cartMock];
        $GLOBALS['woocommerce'] = $woocommerce;
        when('WC')->justReturn($woocommerce);

        $this->ensureAssetManifest();

        return new PaymentController($wooCommerceHelper, $jwtHelper, $blockchainService, $utilityService);
    }

    private function invokeEnqueueScripts(PaymentController $controller): void
    {
        $method = new \ReflectionMethod(PaymentController::class, 'enqueue_scripts');
        $method->setAccessible(true);
        $method->invoke($controller);
    }

    public function test_process_payment_success()
    {
        when('__')->returnArg();

        $order = Mockery::mock();
        $order->shouldReceive('update_status')->once();
        $order->shouldReceive('reduce_order_stock')->once();
        $order->shouldReceive('get_checkout_order_received_url')->andReturn('url');

        when('wc_get_order')->alias(function ($id) use ($order) {
            return $order;
        });

        $cart = Mockery::mock();
        $cart->shouldReceive('empty_cart')->once();
        when('WC')->justReturn((object) ['cart' => $cart]);

        $controller = new PaymentController();
        $result = $controller->process_payment(1);
        $this->assertEquals(['result' => 'success', 'redirect' => 'url'], $result);
    }

    public function test_process_payment_failure()
    {
        when('wc_get_order')->justReturn(false);
        $controller = new PaymentController();
        $result = $controller->process_payment(1);
        $this->assertEquals(['result' => 'failure'], $result);
    }

    public function test_payment_fields_renders_checkout_when_data_valid(): void
    {
        when('__')->returnArg();

        $settingsMock = Mockery::mock('alias:LaqiraPay\\Domain\\Models\\Settings');
        $settingsMock->shouldReceive('get')->andReturnUsing(function ($key, $default = null) {
            if ($key === 'laqirapay_only_logged_in_user') {
                return false;
            }
            if (str_starts_with($key, 'laqirapay_exchange_rate_')) {
                return '1';
            }
            if ($key === 'laqirapay_walletconnect_project_id') {
                return 'wallet-project';
            }

            return $default;
        });

        $translationsMock = Mockery::mock('alias:LaqiraPay\\Support\\LaqiraPayTranslations');
        $translationsMock->shouldReceive('get_translations')->andReturn(['pay' => 'Pay now']);

        $wooCommerceHelper = Mockery::mock(WooCommerceHelper::class);
        $wooCommerceHelper->shouldReceive('getWcpi')->andReturn('wcpi-token');

        $jwtHelper = Mockery::mock(JwtHelper::class);
        $jwtHelper->shouldReceive('createAccessToken')->andReturn(['token' => 'jwt-token']);

        $blockchainService = Mockery::mock(BlockchainService::class);
        $blockchainService->shouldReceive('getNetworks')->andReturn(['network']);
        $blockchainService->shouldReceive('getNetworksAssets')->andReturn(['asset']);
        $blockchainService->shouldReceive('getProvider')->andReturn('0x0000000000000000000000000000000000000001');
        $blockchainService->shouldReceive('getProviderLocal')->andReturn('0x0000000000000000000000000000000000000002');
        $blockchainService->shouldReceive('getStableCoins')->andReturn(['USDT']);

        $utilityService = Mockery::mock(UtilityService::class);
        $utilityService->shouldReceive('detectRtl')->andReturn(false);

        when('is_user_logged_in')->justReturn(true);
        when('current_time')->alias(static function ($type, $gmt = 0) {
            return 1700000000;
        });
        when('is_admin')->justReturn(false);
        when('admin_url')->alias(static function ($path = '', $scheme = 'admin') {
            return 'https://example.com/wp-admin/' . ltrim($path, '/');
        });
        when('wp_create_nonce')->justReturn('nonce-value');
        when('get_home_url')->justReturn('https://example.com');
        when('wc_get_page_id')->alias(static function ($page) {
            return $page === 'shop' ? 10 : 20;
        });
        when('get_permalink')->alias(static function ($id) {
            return 'https://example.com/page-' . $id;
        });
        when('get_woocommerce_currencies')->justReturn(['USD' => 'US Dollar']);
        when('get_woocommerce_currency')->justReturn('USD');

        $localized = [];
        when('wp_localize_script')->alias(static function ($handle, $object_name, $data) use (&$localized) {
            $localized[] = compact('handle', 'object_name', 'data');
            return true;
        });

        $enqueued = [];
        when('wp_enqueue_script')->alias(static function ($handle, $src = null, $deps = [], $ver = null, $in_footer = null) use (&$enqueued) {
            $enqueued[] = $handle;
            return true;
        });
        when('wp_register_style')->alias(static function (...$args) {
            return true;
        });
        when('wp_enqueue_style')->alias(static function (...$args) {
            return true;
        });

        $cartMock = Mockery::mock();
        $cartMock->shouldReceive('get_total')->with('edit')->andReturn('100.00');
        $woocommerce = (object) ['cart' => $cartMock];
        $GLOBALS['woocommerce'] = $woocommerce;
        when('WC')->justReturn($woocommerce);

        $this->ensureAssetManifest();

        $controller = new PaymentController($wooCommerceHelper, $jwtHelper, $blockchainService, $utilityService);

        ob_start();
        $controller->payment_fields();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('id="LaqirapayApp"', $output);

        $this->assertGreaterThanOrEqual(2, count($localized));
        $bootstrap = $localized[0];
        $this->assertSame('laqirapayJS', $bootstrap['handle']);
        $this->assertArrayHasKey('orderData', $bootstrap['data']);
        $this->assertIsArray($bootstrap['data']['orderData']);
        $this->assertSame('https://example.com/wp-admin/admin-ajax.php', $bootstrap['data']['orderData']['laqiraAajaxUrl']);
        $this->assertArrayHasKey('error', $bootstrap['data']['orderData']);
        $this->assertSame('', $bootstrap['data']['orderData']['error']);

        $this->assertContains('laqirapayJS', $enqueued);
        $this->assertContains('wclaqirapay-script', $enqueued);
    }

    public function test_payment_fields_displays_errors_when_data_invalid(): void
    {
        when('__')->returnArg();

        $settingsMock = Mockery::mock('alias:LaqiraPay\\Domain\\Models\\Settings');
        $settingsMock->shouldReceive('get')->andReturnUsing(function ($key, $default = null) {
            if ($key === 'laqirapay_only_logged_in_user') {
                return false;
            }

            return $default;
        });

        $wooCommerceHelper = Mockery::mock(WooCommerceHelper::class);
        $jwtHelper         = Mockery::mock(JwtHelper::class);
        $utilityService    = Mockery::mock(UtilityService::class);

        $blockchainService = Mockery::mock(BlockchainService::class);
        $blockchainService->shouldReceive('getNetworks')->andReturn([]);
        $blockchainService->shouldReceive('getNetworksAssets')->andReturn([]);
        $blockchainService->shouldReceive('getProvider')->andReturn('invalid');

        when('is_user_logged_in')->justReturn(true);
        when('current_time')->alias(static function ($type, $gmt = 0) {
            return 1700000000;
        });

        $registered = [];
        when('wp_register_script')->alias(static function ($handle, $src = null, $deps = [], $ver = null, $in_footer = null) use (&$registered) {
            $registered[] = $handle;
            return true;
        });

        $enqueued = [];
        when('wp_enqueue_script')->alias(static function ($handle, $src = null, $deps = [], $ver = null, $in_footer = null) use (&$enqueued) {
            $enqueued[] = $handle;
            return true;
        });

        $localized = [];
        when('wp_localize_script')->alias(static function ($handle, $object_name, $data) use (&$localized) {
            $localized[] = compact('handle', 'object_name', 'data');
            return true;
        });

        when('admin_url')->alias(static function ($path = '', $scheme = 'admin') {
            return 'https://example.com/wp-admin/' . ltrim($path, '/');
        });

        $controller = new PaymentController($wooCommerceHelper, $jwtHelper, $blockchainService, $utilityService);

        ob_start();
        $controller->payment_fields();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('LaqiraPay is temporarily unavailable', $output);

        $this->assertCount(1, $localized);
        $context = $localized[0]['data'];
        $this->assertArrayHasKey('orderData', $context);
        $this->assertArrayHasKey('error', $context['orderData']);
        $this->assertArrayHasKey('errors', $context['orderData']);
        $this->assertNotSame('', $context['orderData']['error']);
        $this->assertSame($context['orderData']['error'], strip_tags($context['orderData']['error']));
        $this->assertNotEmpty($context['orderData']['errors']);
        $this->assertSame($context['orderData']['error'], $context['orderData']['errors'][0]);
        $this->assertSame('https://example.com/wp-admin/admin-ajax.php', $context['orderData']['laqiraAajaxUrl']);

        $this->assertContains('laqirapayJS', $registered);
        $this->assertContains('laqirapayJS', $enqueued);
    }

    public function test_enqueue_scripts_sets_cookie_for_http_requests(): void
    {
        $controller = $this->createControllerForCookieTest(false);

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

        $this->invokeEnqueueScripts($controller);
        $this->addToAssertionCount(1);
    }

    public function test_enqueue_scripts_sets_cookie_for_https_requests(): void
    {
        $controller = $this->createControllerForCookieTest(true);

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

        $this->invokeEnqueueScripts($controller);
        $this->addToAssertionCount(1);
    }
}
