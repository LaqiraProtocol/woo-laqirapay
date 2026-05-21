<?php

namespace LaqiraPayments\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


use LaqiraPayments\Domain\Services\LaqiraLogger;


class Installer {


	public static function activate() {

		self::laqira_create_transactions_table();
		self::laqira_payments_create_recovery_order_page();
		if ( get_option( 'laqira_payments_order_recovery_status' ) === false ) {
			update_option( 'laqira_payments_order_recovery_status', 'wc-completed' );
		}
		if ( get_option( 'laqira_payments_only_logged_in_user' ) === false ) {
			update_option( 'laqira_payments_only_logged_in_user', 'checked' );
		}
		update_option( 'laqira_payments_activation_log_pending', time() );
	}

	/**
	 * Creates or updates the recovery page to use the plugin-prefixed shortcode.
	 */
	private static function laqira_payments_create_recovery_order_page() {
		$shortcode_tag = '[laqira_payments_recovery]';
		// Define the page title, content, and other parameters
		$page_title    = 'Recovery Order';
		$page_content  = $shortcode_tag;
		$page_template = ''; // Optional: specify a custom template file

		// Check if the page already exists
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'title'       => $page_title,
				'numberposts' => 1,
			)
		);

		if ( empty( $pages ) ) {
			// Create the page
			$page_id = wp_insert_post(
				array(
					'post_title'    => $page_title,
					'post_content'  => $page_content,
					'post_status'   => 'publish',
					'post_type'     => 'page',
					'post_author'   => 1,
					'post_template' => $page_template,
				)
			);

			// Store the page ID in the options table for future reference
			if ( $page_id && ! is_wp_error( $page_id ) ) {
				update_option( 'laqira_payments_recovery_order_page_id', $page_id );
			}
			return;
		}

		$page = $pages[0];
		if ( ! isset( $page->ID ) ) {
			return;
		}

		$current_content = isset( $page->post_content ) ? (string) $page->post_content : '';
		$updated_content = str_replace( '[lqr_recovery]', $shortcode_tag, $current_content );

		if ( $updated_content === '' ) {
			$updated_content = $page_content;
		}

		if ( $updated_content !== $current_content ) {
			wp_update_post(
				array(
					'ID'           => (int) $page->ID,
					'post_content' => $updated_content,
				)
			);
		}
	}

	/**
	 * Creates the Laqira transactions table in the WordPress database.
	 */
	private static function laqira_create_transactions_table() {
		global $wpdb;
		$table_name      = $wpdb->prefix . 'laqira_payments_transactions';
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

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'laqira_payments_web3_cache_cron_hourly' );
		flush_rewrite_rules();
		LaqiraLogger::log( 200, 'system', 'plugin_deactivated' );
	}
}
