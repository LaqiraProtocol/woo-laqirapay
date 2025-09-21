<?php
namespace Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use LaqiraPay\Domain\Models\LogRecord;
use LaqiraPay\Domain\Repositories\LogRepository;
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../FakeWpdb.php';

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data) { return json_encode($data); }
}

class LogRepositoryTest extends TestCase
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
        Functions\when('current_time')->alias(fn() => '2024-01-01 00:00:00');
    }

    protected function tearDown(): void
    {
        if ($this->monkeyActive) {
            Monkey\tearDown();
        }
        parent::tearDown();
    }

    public function test_insert_and_search(): void
    {
        $wpdb = new \FakeWpdb();
        $repo = new LogRepository($wpdb);

        $id1 = $repo->insert(new LogRecord(100, 'type1', ['event' => 'one']));
        $id2 = $repo->insert(new LogRecord(200, 'type2', ['event' => 'two']));

        $results = $repo->search(0, 10);

        $this->assertCount(2, $results);
        $this->assertSame($id1, $results[0]->getId());
        $this->assertSame('type1', $results[0]->getType());
        $this->assertSame($id2, $results[1]->getId());
        $this->assertSame('two', $results[1]->getContext()['event']);
    }
}
