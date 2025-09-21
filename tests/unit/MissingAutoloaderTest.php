<?php

namespace Tests\Unit {

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase;

class MissingAutoloaderTest extends TestCase
{
    private string $autoloadPath;

    private ?string $autoloadBackupPath = null;

    /**
     * @var array<int, array{0: string, 1: callable, 2?: array<int, mixed>}> Registered WordPress actions.
     */
    private array $registeredActions = [];

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        $this->registeredActions = [];
        $registeredActions =& $this->registeredActions;

        Functions\when('plugin_dir_path')->alias(static fn(string $file): string => dirname($file) . '/');
        Functions\when('plugin_basename')->alias(static fn(string $file): string => basename(dirname($file)) . '/' . basename($file));
        Functions\when('esc_url')->returnArg(1);
        Functions\when('wp_nonce_url')->alias(static fn(string $url, $action = -1) => $url);
        Functions\when('add_query_arg')->alias(static function ($args, string $url) {
            if (is_array($args)) {
                return $url . '?' . http_build_query($args);
            }

            return $url;
        });
        Functions\when('admin_url')->alias(static fn(string $path = ''): string => 'http://example.com/wp-admin/' . ltrim($path, '/'));
        Functions\when('add_filter')->justReturn(true);
        Functions\when('add_action')->alias(static function (string $hook, $callback, ...$args) use (&$registeredActions): void {
            $registeredActions[] = [$hook, $callback, $args];
        });
        Functions\when('current_user_can')->justReturn(true);
        Functions\when('is_admin')->justReturn(true);
        Functions\when('wp_doing_ajax')->justReturn(false);
        Functions\when('get_bloginfo')->alias(static fn($show) => '6.9');
        Functions\when('get_option')->alias(static fn($option, $default = false) => '8.4');
        Functions\when('deactivate_plugins')->justReturn(null);
        Functions\when('register_activation_hook')->justReturn(null);
        Functions\when('register_deactivation_hook')->justReturn(null);
        Functions\when('register_uninstall_hook')->justReturn(null);
    }

    protected function tearDown(): void
    {
        if ($this->autoloadBackupPath !== null && file_exists($this->autoloadBackupPath)) {
            rename($this->autoloadBackupPath, $this->autoloadPath);
        }

        Mockery::close();
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * @runInSeparateProcess
     */
    public function test_missing_autoloader_registers_admin_notice_and_skips_bootstrap(): void
    {
        if (!defined('ABSPATH')) {
            define('ABSPATH', sys_get_temp_dir() . '/wp-' . uniqid('', true));
        }

        $root = dirname(__DIR__, 2);
        $this->autoloadPath = $root . '/vendor/autoload.php';
        $backupPath = $this->autoloadPath . '.bak';

        if (file_exists($backupPath)) {
            $this->assertTrue(rename($backupPath, $this->autoloadPath), 'Failed to restore autoloader backup before test.');
        }

        $this->assertFileExists($this->autoloadPath, 'Composer autoload file must exist before simulation.');
        $this->assertTrue(rename($this->autoloadPath, $backupPath), 'Failed to rename autoload file for simulation.');
        $this->autoloadBackupPath = $backupPath;

        $bootstrapMock = Mockery::mock('overload:LaqiraPay\\Bootstrap');
        $bootstrapMock->shouldNotReceive('__construct');

        require_once $root . '/laqirapay.php';

        $this->assertFalse(laqirapay_load_composer(), 'Autoloader should report failure when file is missing.');

        $noticeHooks = array_values(array_filter(
            $this->registeredActions,
            static fn(array $action): bool => $action[0] === 'admin_notices'
        ));

        $this->assertNotEmpty($noticeHooks, 'Missing autoloader notice must be registered for administrators.');

        $output = '';
        foreach ($noticeHooks as $notice) {
            $this->assertIsCallable($notice[1]);
            ob_start();
            call_user_func($notice[1]);
            $output .= ob_get_clean();
        }

        $this->assertStringContainsString('Composer autoload file is missing', $output);
    }
}

}

namespace {
    if (!function_exists('esc_html')) {
        function esc_html($text)
        {
            return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
        }
    }
}
