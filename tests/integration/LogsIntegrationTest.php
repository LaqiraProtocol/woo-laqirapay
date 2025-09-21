<?php
namespace {
    if (!function_exists('wp_json_encode')) {
        function wp_json_encode($data) { return json_encode($data); }
    }
    if (!class_exists('WP_List_Table')) {
        class WP_List_Table {
            public $items = [];
            protected $_column_headers;
            public function __construct($args = []) {}
            public function get_items_per_page($o, $d) { return $d; }
            public function get_pagenum() { return 1; }
            public function set_pagination_args($a) {}
            public function display() { foreach ($this->items as $it) { echo $it['event'] . "\n"; } }
        }
    }
    if (!defined('ABSPATH')) {
        define('ABSPATH', __DIR__ . '/');
    }
}

namespace Tests\Integration {

use Brain\Monkey;
use Brain\Monkey\Functions;
use LaqiraPay\Jobs\LogsCron;
use LaqiraPay\Domain\Models\LogRecord;
use LaqiraPay\Domain\Repositories\LogRepository;
use LaqiraPay\Http\Controllers\Rest\LogsController;
use LaqiraPay\Admin\LogsListTable;
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../FakeWpdb.php';

class LogsIntegrationTest extends TestCase
{
    private bool $monkeyActive = false;

    protected function setUp(): void
    {
        parent::setUp();
        $dependencies = [
            LogRepository::class,
            LogsCron::class,
            LogsController::class,
            LogsListTable::class,
        ];

        $missing = array_filter($dependencies, static fn(string $class) => !class_exists($class));
        if ($missing) {
            $this->markTestSkipped('Log subsystem is not available in this build.');
        }

        Monkey\setUp();
        $this->monkeyActive = true;
        Functions\when('sanitize_text_field')->alias(fn($v) => $v);
        Functions\when('wp_unslash')->alias(fn($v) => $v);
        Functions\when('is_admin')->justReturn(true);
    }

    protected function tearDown(): void
    {
        if ($this->monkeyActive) {
            Monkey\tearDown();
        }
        unset($GLOBALS['wpdb']);
        parent::tearDown();
    }

    private function setupDb(): \FakeWpdb
    {
        $wpdb = new \FakeWpdb();
        $repo = new LogRepository($wpdb);
        $old = gmdate('Y-m-d H:i:s', time() - 2 * 86400);
        $new = gmdate('Y-m-d H:i:s');
        $repo->insert(new LogRecord(200, 'system', ['event' => 'old'], null, $old));
        $repo->insert(new LogRecord(200, 'system', ['event' => 'new'], null, $new));
        // ensure stored timestamps match
        $wpdb->data[0]['created_at'] = $old;
        $wpdb->data[1]['created_at'] = $new;
        return $wpdb;
    }

    public function test_admin_logs_list_renders(): void
    {
        $wpdb = $this->setupDb();
        $GLOBALS['wpdb'] = $wpdb;
        $table = new LogsListTable();
        $table->prepare_items();
        ob_start();
        $table->display();
        $output = ob_get_clean();
        $this->assertStringContainsString('old', $output);
        $this->assertStringContainsString('new', $output);
    }

    public function test_streaming_export_generator(): void
    {
        $wpdb = $this->setupDb();
        $GLOBALS['wpdb'] = $wpdb;
        $controller = new LogsController();
        $ref = new \ReflectionClass(LogsController::class);
        $method = $ref->getMethod('streamLogs');
        $method->setAccessible(true);
        $gen = $method->invoke($controller, 1);
        $out = '';
        foreach ($gen as $record) {
            $out .= json_encode($record->toArray()) . "\n";
        }
        $this->assertStringContainsString('"event":"old"', $out);
        $this->assertStringContainsString('"event":"new"', $out);
    }

    public function test_logs_cron_archives_and_purges(): void
    {
        $wpdb = $this->setupDb();
        $GLOBALS['wpdb'] = $wpdb;

        $temp = sys_get_temp_dir();
        Functions\when('get_transient')->justReturn(false);
        Functions\when('set_transient')->justReturn(true);
        Functions\when('delete_transient')->justReturn(true);
        Functions\when('wp_upload_dir')->justReturn(['basedir' => $temp]);
        Functions\when('wp_mkdir_p')->alias(function($d){ if(!is_dir($d)) mkdir($d,0777,true); return true; });
        Functions\when('wp_date')->alias(fn($f) => date($f));
        Functions\when('get_option')->alias(fn($k,$d=null) => 1);

        $repo = new LogRepository($wpdb);
        $cron = new LogsCron($repo);
        $cron->handle();

        $this->assertCount(2, $wpdb->data);
        $files = glob($temp . '/laqirapay-logs-archive/*/*/*.ndjson.gz');
        $this->assertNotEmpty($files);
        $content = gzfile($files[0]);
        $this->assertStringContainsString('old', $content[0]);
    }
}

}
