<?php

namespace LaqiraPay;

use LaqiraPay\Core\I18n;
use LaqiraPay\Jobs\Web3CacheCron;


class Bootstrap
{
    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'boot'] );
    }

    public function boot(): void
    {
        I18n::load();

        if (!wp_next_scheduled('laqirapay_web3_cache_cron_hourly')) {
            wp_schedule_event(time(), 'hourly', 'laqirapay_web3_cache_cron_hourly');
        }

        $web3Cron = new Web3CacheCron();
        add_action('laqirapay_web3_cache_cron_hourly', [$web3Cron, 'handle']);

        if (function_exists('run_laqirapay')) {
            run_laqirapay();
        }
    }
}
