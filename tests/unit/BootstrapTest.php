<?php

namespace Tests\Unit {

use Brain\Monkey;
use Brain\Monkey\Functions;
use LaqiraPay\Bootstrap;
use Mockery;
use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public static bool $runCalled = false;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        Mockery::close();
        parent::tearDown();
    }

    public function test_boot_skips_activation_during_normal_request(): void
    {
        self::$runCalled = false;

        Mockery::mock('alias:LaqiraPay\\Core\\I18n')
            ->shouldReceive('load')
            ->once();

        Mockery::mock('alias:LaqiraPay\\Core\\Installer')
            ->shouldNotReceive('activate');

        $web3CronMock = Mockery::mock('overload:LaqiraPay\\Jobs\\Web3CacheCron');
        $web3CronMock->shouldReceive('__construct')->once()->withNoArgs();

        $registeredActions = [];
        Functions\when('add_action')->alias(function ($hook, $callback) use (&$registeredActions): void {
            $registeredActions[] = [$hook, $callback];
        });

        Functions\expect('wp_next_scheduled')
            ->once()
            ->with('laqirapay_web3_cache_cron_hourly')
            ->andReturn(false);

        Functions\expect('wp_schedule_event')
            ->once()
            ->with(Mockery::type('int'), 'hourly', 'laqirapay_web3_cache_cron_hourly');

        $bootstrap = new Bootstrap();
        $bootstrap->boot();

        $this->assertContains(
            'laqirapay_web3_cache_cron_hourly',
            array_column($registeredActions, 0),
            'Cron handler should be registered during boot.'
        );

        $this->assertTrue(self::$runCalled, 'run_laqirapay should be executed during boot.');
    }
}

}

namespace {
    if (!function_exists('run_laqirapay')) {
        function run_laqirapay(): void
        {
            \Tests\Unit\BootstrapTest::$runCalled = true;
        }
    }
}

