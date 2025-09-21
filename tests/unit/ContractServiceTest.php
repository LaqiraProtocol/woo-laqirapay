<?php
namespace Tests\Unit;

use Brain\Monkey;
use function Brain\Monkey\Functions\when;
use PHPUnit\Framework\TestCase;
use LaqiraPay\Services\ContractService;

class ContractServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        if (!defined('MINUTE_IN_SECONDS')) {
            define('MINUTE_IN_SECONDS', 60);
        }
        if (!defined('HOUR_IN_SECONDS')) {
            define('HOUR_IN_SECONDS', 3600);
        }
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_getCid_returns_cached_value()
    {
        when('get_transient')->justReturn('cached_cid');
        $service = new class extends ContractService {
            public bool $called = false;
            public function fetchCidFromContract(\Web3\Contract $contract): string
            {
                $this->called = true;
                return 'live_cid';
            }
        };
        $this->assertSame('cached_cid', $service->getCid());
        $this->assertFalse($service->called);
    }
}
