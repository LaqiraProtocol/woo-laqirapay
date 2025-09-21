<?php
namespace Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use LaqiraPay\Domain\Repositories\LogRepository;
use LaqiraPay\Domain\Services\LaqiraLogger;
use PHPUnit\Framework\TestCase;

class MaskHelperTest extends TestCase
{
    private bool $monkeyActive = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(LogRepository::class)) {
            $this->markTestSkipped('Log repository classes are not available in this build.');
        }

        Monkey\setUp();
        $this->monkeyActive = true;
        if (!function_exists('wp_json_encode')) {
            function wp_json_encode($data) { return json_encode($data); }
        }
    }

    protected function tearDown(): void
    {
        if ($this->monkeyActive) {
            Monkey\tearDown();
        }
        parent::tearDown();
    }

    private function logger(): LaqiraLogger
    {
        $repo = $this->createMock(LogRepository::class);
        return new LaqiraLogger($repo);
    }

    public function test_mask_sensitive_recursively_masks_keys(): void
    {
        $logger = $this->logger();
        $ref = new \ReflectionClass(LaqiraLogger::class);
        $method = $ref->getMethod('maskSensitive');
        $method->setAccessible(true);

        $input = [
            'password' => 'secret',
            'nested' => [
                'token' => 'abc',
                'inner' => ['api_key' => '123', 'other' => 'ok']
            ]
        ];
        $output = $method->invoke($logger, $input);

        $this->assertSame('***', $output['password']);
        $this->assertSame('***', $output['nested']['token']);
        $this->assertSame('***', $output['nested']['inner']['api_key']);
        $this->assertSame('ok', $output['nested']['inner']['other']);
    }

    public function test_enrich_context_hashes_ip_conditionally(): void
    {
        Functions\when('wp_get_current_user')->justReturn(null);
        $_SERVER['REMOTE_ADDR'] = '5.6.7.8';

        $logger = $this->logger();
        $ref = new \ReflectionClass(LaqiraLogger::class);
        $method = $ref->getMethod('enrichContext');
        $method->setAccessible(true);

        $hashed = $method->invoke($logger, [], 'evt', '', true);
        $plain  = $method->invoke($logger, [], 'evt', '', false);

        $this->assertSame(hash('sha256', '5.6.7.8'), $hashed['ip']);
        $this->assertSame('5.6.7.8', $plain['ip']);
    }
}
