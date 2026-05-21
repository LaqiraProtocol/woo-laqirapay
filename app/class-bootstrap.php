<?php
/**
 * Plugin bootstrapper.
 *
 * @package LaqiraPayments
 */

namespace LaqiraPayments;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



use LaqiraPayments\Core\I18n;
use LaqiraPayments\Domain\Services\LaqiraLogger;
use LaqiraPayments\Jobs\Web3CacheCron;

/**
 * Boots plugin services and scheduled jobs.
 */
class Bootstrap {

	private const ACTIVATION_LOG_OPTION = 'laqira_payments_activation_log_pending';

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
		add_action( 'init', array( $this, 'maybe_log_pending_activation' ), 15 );

		//I18n::load();

		if ( ! wp_next_scheduled( 'laqira_payments_web3_cache_cron_hourly' ) ) {
			wp_schedule_event( time(), 'hourly', 'laqira_payments_web3_cache_cron_hourly' );
		}

		$web3_cron = new Web3CacheCron();
		add_action( 'laqira_payments_web3_cache_cron_hourly', array( $web3_cron, 'handle' ) );

		if ( function_exists( 'laqira_payments_run' ) ) {
			laqira_payments_run();
		}
	}

	/**
	 * Write the activation log once the request reaches init.
	 */
	public function maybe_log_pending_activation(): void {
		$pending = get_option( self::ACTIVATION_LOG_OPTION );
		if ( false === $pending ) {
			return;
		}

		delete_option( self::ACTIVATION_LOG_OPTION );
		LaqiraLogger::log( 200, 'system', 'plugin_activated' );
	}
}
