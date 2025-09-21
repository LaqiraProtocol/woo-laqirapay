<?php

namespace LaqiraPay\Jobs;

use LaqiraPay\Services\ContractService;
use LaqiraPay\Services\BlockchainService;
use LaqiraPay\Domain\Services\LaqiraLogger;

/**
 * Cron job to refresh Web3 related caches.
 */
class Web3CacheCron
{
    private ContractService $contractService;
    private BlockchainService $blockchainService;

    public function __construct(?ContractService $contractService = null, ?BlockchainService $blockchainService = null)
    {
        $this->contractService   = $contractService ?: new ContractService();
        $this->blockchainService = $blockchainService ?: new BlockchainService($this->contractService);
    }

    public function handle(): void
    {
        try {
            // Ensure fresh CID and related data are cached.
            delete_transient('laqirapay_cid_cached');
            $cid = $this->contractService->getCid();
            if ($cid) {
                $this->blockchainService->getRemoteJsonCid($cid);
            }
            $this->blockchainService->getNetworks();
            $this->blockchainService->showNetworks();
            $this->blockchainService->getNetworksAssets();
            $this->blockchainService->getStableCoins();
            LaqiraLogger::log(200, 'cron', 'web3_cache_refreshed');
        } catch (\Throwable $e) {
            LaqiraLogger::log(400, 'cron', 'web3_cache_refresh_failed', [], $e->getMessage());
        }
    }
}
