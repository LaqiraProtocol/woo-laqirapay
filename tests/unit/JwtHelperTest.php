<?php
namespace Tests\Unit;

use LaqiraPay\Helpers\JwtHelper;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class TestJwtHelper extends JwtHelper {
    public function laqirapayGetJwtSecret()
    {
        return 'test-secret';
    }
}

class JwtHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        unset($GLOBALS['wpdb']);
        Functions\when('get_option')->alias(fn($key, $default = null) => $default);
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_create_and_verify_token()
    {
        $helper = new TestJwtHelper();
        $result = $helper->createAccessToken(123);
        $this->assertArrayHasKey('token', $result);
        $payload = $helper->verifyJwt($result['token']);
        $this->assertEquals('123', $payload->sub);
    }
}
