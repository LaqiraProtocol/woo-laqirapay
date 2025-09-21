<?php
namespace Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use LaqiraPay\Jobs\LogsCron;
use LaqiraPay\Domain\Repositories\LogRepository;
use Mockery;
use PHPUnit\Framework\TestCase;

class LogsCronTest extends TestCase
{
    private bool $monkeyActive = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(LogsCron::class) || !class_exists(LogRepository::class)) {
            $this->markTestSkipped('Log cron dependencies are not available in this build.');
        }

        Monkey\setUp();
        $this->monkeyActive = true;
    }

    protected function tearDown(): void
    {
        if ($this->monkeyActive) {
            Monkey\tearDown();
        }
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_archives_and_purges_logs(): void
    {
        Functions\expect('get_transient')->once()->with('_lqr_logs_cron_lock')->andReturn(false);
        Functions\expect('set_transient')->once()->with('_lqr_logs_cron_lock', 1, Mockery::type('int'))->andReturnTrue();
        Functions\expect('wp_upload_dir')->andReturn(['basedir' => '/tmp']);
        Functions\expect('wp_mkdir_p')->andReturnTrue();
        Functions\when('wp_date')->alias(fn($format) => date($format));
        Functions\expect('get_option')->once()->with('laqirapay_log_retention_days', 30)->andReturn(30);
        Functions\expect('delete_transient')->once()->with('_lqr_logs_cron_lock');

        $repo = $this->createMock(LogRepository::class);
        $repo->expects($this->once())->method('archiveOlderThanToNdjsonGzip')->willReturn(true);
        $repo->expects($this->once())->method('purgeOlderThan');

        $cron = new LogsCron($repo);
        $cron->handle();
    }
}
