<?php
namespace Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use LaqiraPay\Domain\Models\LogRecord;
use LaqiraPay\Domain\Repositories\LogRepository;
use LaqiraPay\Domain\Services\LaqiraLogger;
use PHPUnit\Framework\TestCase;

class LaqiraLoggerTest extends TestCase
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
    }

    protected function tearDown(): void
    {
        if ($this->monkeyActive) {
            Monkey\tearDown();
        }
        parent::tearDown();
    }

    public function test_log_masks_sensitive_and_enriches_context(): void
    {
        // Mock WordPress option retrieval
        Functions\when('get_option')->alias(function ($key, $default = null) {
            $map = [
                'laqirapay_log_min_level' => 0,
                'laqirapay_log_sampling'  => 1,
                'laqirapay_log_hash_ip'   => true,
                'laqirapay_log_front'     => true,
            ];
            return $map[$key] ?? $default;
        });

        // Mock is_admin()
        Functions\expect('is_admin')->andReturn(true);

        // Mock current user
        $user          = new \stdClass();
        $user->ID      = 10;
        $user->user_email = 'user@example.com';
        Functions\expect('wp_get_current_user')->andReturn($user);

        // Prepare request variables
        $_SERVER['REMOTE_ADDR']     = '1.2.3.4';
        $_SERVER['HTTP_X_REQUEST_ID'] = 'req-1';

        $repo = $this->createMock(LogRepository::class);
        $repo->expects($this->once())
            ->method('insert')
            ->with($this->callback(function (LogRecord $record) {
                $context = $record->getContext();
                return isset($context['user'])
                    && $context['authorization'] === '***'
                    && $context['ip'] === hash('sha256', '1.2.3.4')
                    && $context['request_id'] === 'req-1'
                    && $context['event'] === 'sample';
            }));

        $logger = new LaqiraLogger($repo);
        $logger->log(200, 'system', 'sample', ['authorization' => 'Bearer 123']);
        $this->assertTrue(true);
    }
}
