<?php
namespace {
    if (!defined('ABSPATH')) {
        define('ABSPATH', __DIR__ . '/');
    }
    if (!class_exists('WP_List_Table')) {
        class WP_List_Table {
            public $items = [];
            protected $_column_headers;
            public function __construct($args = []) {}
            public function get_items_per_page($o, $d) { return $d; }
            public function get_pagenum() { return 1; }
            public function set_pagination_args($a) {}
        }
    }
}

namespace Tests\Unit {

use LaqiraPay\Admin\LogsListTable;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../FakeWpdb.php';

class LogsListTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(LogsListTable::class)) {
            $this->markTestSkipped('Log list table class is not available in this build.');
        }
        $_REQUEST = [];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['wpdb']);
        parent::tearDown();
    }

    public function test_prepare_without_filters_uses_raw_query(): void
    {
        $db = new class extends \FakeWpdb {
            public int $prepare_calls = 0;
            public function prepare($query, ...$args) {
                $this->prepare_calls++;
                return parent::prepare($query, ...$args);
            }
        };

        $db->data = [
            ['created_at' => '2024-01-01 00:00:00', 'level' => 100, 'type' => 'system', 'context' => json_encode(['event' => 'a'])],
        ];

        $GLOBALS['wpdb'] = $db;

        $table = new LogsListTable();
        $table->prepare_items();

        $this->assertSame(0, $db->prepare_calls);
        $this->assertCount(1, $table->items);
    }
}

}
