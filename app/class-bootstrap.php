<?php
/**
 * Plugin bootstrapper.
 *
 * @package LaqiraPay
 */

namespace LaqiraPay;

use LaqiraPay\Core\I18n;
use LaqiraPay\Jobs\Web3CacheCron;

/**
 * Boots plugin services and scheduled jobs.
 */
class Bootstrap {

	/**
	 * Register hooks on instantiation.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'boot' ) );
	}

	/**
	 * Handles plugin initialization logic.
	 */
	public function boot(): void {
		I18n::load();

		if ( ! wp_next_scheduled( 'laqirapay_web3_cache_cron_hourly' ) ) {
			wp_schedule_event( time(), 'hourly', 'laqirapay_web3_cache_cron_hourly' );
		}

		$web3_cron = new Web3CacheCron();
		add_action( 'laqirapay_web3_cache_cron_hourly', array( $web3_cron, 'handle' ) );

		if ( function_exists( 'run_laqirapay' ) ) {
			run_laqirapay();
		}
	}
}
