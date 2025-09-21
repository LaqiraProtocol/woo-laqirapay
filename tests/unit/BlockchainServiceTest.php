<?php
namespace Tests\Unit;

use LaqiraPay\Services\BlockchainService;
use LaqiraPay\Services\ContractService;
use Brain\Monkey;
use function Brain\Monkey\Functions\when;
use PHPUnit\Framework\TestCase;

class BlockchainServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        if (!defined('HOUR_IN_SECONDS')) {
            define('HOUR_IN_SECONDS', 3600);
        }
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_get_provider_local_returns_option()
    {
        when('get_option')->justReturn('abc');
        $service = new BlockchainService();
        $this->assertEquals('abc', $service->getProviderLocal());
    }

    public function test_getNetworks_returns_cached_value()
    {
        when('get_transient')->justReturn(['cached_network']);
        $service = new BlockchainService(new ContractService());
        $this->assertSame(['cached_network'], $service->getNetworks());
    }
}
