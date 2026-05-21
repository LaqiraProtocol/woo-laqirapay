<?php

namespace LaqiraPayments\Support;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



/**
 * Fired during plugin uninstallation.
 *
 * This class defines all code necessary to run during the plugin's uninstallation.
 *
 * @since      0.1.0
 * @package    LaqiraPayments
 * @subpackage LaqiraPayments/includes
 * @author     Laqira Protocol <info@laqira.io>
 */

class LaqiraPaymentsUninstaller {


	/**
	 * Run during plugin uninstallation.
	 *
	 * @since    1.0.0
	 */
	public static function uninstall() {
		if ( get_option( 'laqira_payments_delete_data_uninstall' ) == 1 ) {
			self::laqira_payments_delete_transactions_table();
			self::laqira_payments_delete_recovery_order_page();
			delete_option( 'laqira_payments_order_recovery_status' );
			delete_option( 'laqira_payments_only_logged_in_user' );
			delete_option( 'laqira_payments_recovery_order_page_id' );
			delete_option( 'laqira_payments_api_key' );
			delete_option( 'laqira_payments_walletconnect_project_id' );
			delete_option( 'laqira_payments_delete_data_uninstall' );
		}
	}

	/**
	 * Deletes the Laqira transactions table from the WordPress database.
	 */
	private static function laqira_payments_delete_transactions_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'laqira_payments_transactions';
		if ( ! is_string( $table_name ) || '' === $table_name ) {
			return;
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange -- Optional uninstall cleanup when merchant enabled data deletion.
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Optional uninstall cleanup when merchant enabled data deletion.
			$wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table_name )
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange
	}

	/**
	 * Deletes the Recovery Order page.
	 */
	private static function laqira_payments_delete_recovery_order_page() {
		$page_id = get_option( 'laqira_payments_recovery_order_page_id' );
		if ( $page_id ) {
			wp_delete_post( $page_id, true );
		}
	}
}
