<?php

namespace LaqiraPay\Support;

/**
 * Fired during plugin uninstallation.
 *
 * This class defines all code necessary to run during the plugin's uninstallation.
 *
 * @since      0.1.0
 * @package    LaqiraPay
 * @subpackage LaqiraPay/includes
 * @author     Laqira Protocol <info@laqira.io>
 */

class LaqiraPayUninstaller {


	/**
	 * Run during plugin uninstallation.
	 *
	 * @since    1.0.0
	 */
	public static function uninstall() {
		if ( get_option( 'laqirapay_delete_data_uninstall' ) == 1 ) {
			self::laqirapay_delete_transactions_table();
			self::laqirapay_delete_recovery_order_page();
			delete_option( 'laqirapay_order_recovery_status' );
			delete_option( 'laqirapay_only_logged_in_user' );
			delete_option( 'laqirapay_recovery_order_page_id' );
			delete_option( 'laqirapay_api_key' );
			delete_option( 'laqirapay_walletconnect_project_id' );
			delete_option( 'laqirapay_delete_data_uninstall' );
		}
	}

	/**
	 * Deletes the Laqira transactions table from the WordPress database.
	 */
	private static function laqirapay_delete_transactions_table() {
		global $wpdb;
		$table_name      = $wpdb->prefix . 'laqirapay_transactions';
		if ($table_name === null) {
   			 $table_name = '';
			}

		$sanitized_table = preg_replace( '/[^A-Za-z0-9_]/', '', (string) $table_name );

		if ( $sanitized_table === '' ) {
			return;
		}

		$sql = sprintf( 'DROP TABLE IF EXISTS `%s`', $sanitized_table );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is derived from $wpdb->prefix, sanitized above, and cannot be parameterized as a value.
		$wpdb->query( $sql );
	}

	/**
	 * Deletes the Recovery Order page.
	 */
	private static function laqirapay_delete_recovery_order_page() {
		$page_id = get_option( 'laqirapay_recovery_order_page_id' );
		if ( $page_id ) {
			wp_delete_post( $page_id, true );
		}
	}
}
