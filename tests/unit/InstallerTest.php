<?php
namespace Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use LaqiraPay\Core\Installer;
use Mockery;
use PHPUnit\Framework\TestCase;

class InstallerTest extends TestCase
{
    private string $upgradeBase;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        if (!defined('ABSPATH')) {
            $dir = sys_get_temp_dir() . '/wp-' . uniqid();
            define('ABSPATH', $dir . '/');
        }
        $this->upgradeBase = ABSPATH . 'wp-admin';
        $upgrade = $this->upgradeBase . '/includes/upgrade.php';
        mkdir(dirname($upgrade), 0777, true);
        file_put_contents($upgrade, '<?php');

        Functions\when('get_option')->returnArg(1);
        Functions\when('is_admin')->justReturn(true);
        Functions\when('current_time')->alias(fn() => '2024-01-01 00:00:00');
        Functions\when('update_option')->justReturn(true);
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        Mockery::close();
        if (isset($this->upgradeBase)) {
            $upgrade = $this->upgradeBase . '/includes/upgrade.php';
            if (file_exists($upgrade)) {
                unlink($upgrade);
            }
            if (is_dir($this->upgradeBase . '/includes')) {
                rmdir($this->upgradeBase . '/includes');
            }
            if (is_dir($this->upgradeBase)) {
                rmdir($this->upgradeBase);
            }
        }
        parent::tearDown();
    }

    public function test_activate_creates_tables_and_options(): void
    {
        global $wpdb;
        $wpdb = new \FakeWpdb();

        Functions\expect('dbDelta')->once()->with(Mockery::on(fn($sql) => str_contains($sql, 'laqirapay_logs')));

        Installer::activate();
        $this->assertTrue(true);
    }

    public function test_deactivate_unschedules_cron_events(): void
    {
        Functions\expect('wp_clear_scheduled_hook')->once()->with('laqirapay_logs_cron_daily');
        Functions\expect('flush_rewrite_rules')->once();

        Installer::deactivate();
        $this->assertTrue(true);
    }
}
