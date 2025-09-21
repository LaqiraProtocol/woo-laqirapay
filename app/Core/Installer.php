<?php

namespace LaqiraPay\Core;

use LaqiraPay\Domain\Services\LaqiraLogger;

class Installer
{

	public static function activate()
	{

		self::laqira_create_transactions_table();
		self::laqirapay_create_recovery_order_page();
		if (get_option('laqirapay_order_recovery_status') === false) {
			update_option('laqirapay_order_recovery_status', 'wc-completed');
		}
		if (get_option('laqirapay_only_logged_in_user') === false) {
			update_option('laqirapay_only_logged_in_user', 'checked');
		}
		LaqiraLogger::log(200, 'system', 'plugin_activated');
	}

	/**
	 * Creates a custom page for order recovery with shortcode [lqr_recovery].
	 */
	private static function laqirapay_create_recovery_order_page()
	{
		// Define the page title, content, and other parameters
		$page_title = 'Recovery Order';
		$page_content = '[lqr_recovery]';
		$page_template = ''; // Optional: specify a custom template file

		// Check if the page already exists
		$pages = get_posts([
			'post_type' => 'page',
			'post_status' => 'publish',
			'title' => $page_title,
			'numberposts' => 1,
		]);

		if (empty($pages)) {
			// Create the page
			$page_id = wp_insert_post([
				'post_title'     => $page_title,
				'post_content'   => $page_content,
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => 1,
				'post_template'  => $page_template,
			]);

			// Store the page ID in the options table for future reference
			if ($page_id && !is_wp_error($page_id)) {
				update_option('laqirapay_recovery_order_page_id', $page_id);
			}
		}
	}

	/**
	 * Creates the Laqira transactions table in the WordPress database.
	 */
	private static function laqira_create_transactions_table()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'laqirapay_transactions';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            wc_total_price decimal(10,2) DEFAULT NULL,
            wc_currency varchar(3) DEFAULT NULL,
            wc_created_date datetime DEFAULT NULL,
            wc_confirmed_date datetime DEFAULT NULL,
            token_address varchar(42) DEFAULT NULL,
            token_name varchar(10) DEFAULT NULL,
            token_amount varchar(10) DEFAULT NULL,
            exchange_rate bigint(20) DEFAULT NULL,
            wc_order_id bigint(20) DEFAULT NULL,
            tx_hash varchar(66) DEFAULT NULL,
            req_hash varchar(66) DEFAULT NULL,
            tx_log longtext DEFAULT NULL,
            tx_from varchar(42) DEFAULT NULL,
            tx_to varchar(42) DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

    public static function deactivate(): void
    {
        wp_clear_scheduled_hook('laqirapay_web3_cache_cron_hourly');
        flush_rewrite_rules();
        LaqiraLogger::log(200, 'system', 'plugin_deactivated');
    }
}
