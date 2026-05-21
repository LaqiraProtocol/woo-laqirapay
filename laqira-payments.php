<?php
/**
 * Plugin Name:       Laqira Payments for WooCommerce
 * Plugin URI:        https://laqirahub.com
 * Description:       LaqiraPayments: Fully Decentralized Asset-Agnostic MultiNetwork Payment Gateway for WooCommerce
 * Version:           0.9.37
 * Author:            Laqira Protocol
 * Author URI:        https://laqira.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       laqira-payments
 * Domain Path:       /languages
 * Tested up to:      7.0
 * Requires at least: 6.3
 * Requires PHP:      8.1
 * WC requires at least: 8.2
 * WC tested up to:   10.6.2
 *
 * @package LaqiraPayments
 */

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use LaqiraPayments\Bootstrap;
use LaqiraPayments\Core\Installer;
use LaqiraPayments\Support\LaqiraPaymentsUninstaller;

// Define plugin constants.
const LAQIRAPAYMENTS_VERSION     = '0.9.37';
const LAQIRAPAYMENTS_PLUGIN_NAME = 'laqira-payments';
const LAQIRAPAYMENTS_PLUGIN_FILE = __FILE__;
if ( ! defined( 'LAQIRAPAYMENTS_PLUGIN_DIR' ) ) {
		define( 'LAQIRAPAYMENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'LAQIRAPAYMENTS_PLUGIN_BASENAME' ) ) {
		define( 'LAQIRAPAYMENTS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
const LAQIRAPAYMENTS_SETTINGS_PAGE = 'laqira-payments-settings';

/**
 * Add action links (Settings, Support) in Plugins list row.
 *
 * @param array $actions Default plugin links.
 *
 * @return array Merged action links.
 */
function laqira_payments_action_links( array $actions ): array {
	$custom_actions = array(
		'settings' => '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'page' => LAQIRAPAYMENTS_SETTINGS_PAGE ), admin_url( 'admin.php' ) ), 'laqira_payments_settings_access' ) ) . '">' . esc_html__( 'Settings', 'laqira-payments' ) . '</a>',
		'support'  => '<a href="' . esc_url( 'https://laqirahub.com/laqira-pay/introduction' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Support', 'laqira-payments' ) . '</a>',
	);

	return array_merge( $custom_actions, $actions );
}
add_filter( 'plugin_action_links_' . LAQIRAPAYMENTS_PLUGIN_BASENAME, 'laqira_payments_action_links' );

/**
 * Load composer dependencies safely.
 *
 * @return bool True if autoload file exists and is loaded, false otherwise.
 */
function laqira_payments_load_composer(): bool {
		static $notice_registered = false;

		$autoload_path = LAQIRAPAYMENTS_PLUGIN_DIR . 'vendor/autoload.php';
	if ( file_exists( $autoload_path ) ) {
			require_once $autoload_path;

			return true;
	}

	if ( ! $notice_registered ) {
			add_action(
				'admin_notices',
				function () {
					echo '<div class="notice notice-error"><p>' .
					esc_html__( 'LaqiraPayments: Composer autoload file is missing. Please run `composer install`.', 'laqira-payments' ) .
					'</p></div>';
				}
			);
			$notice_registered = true;
	}

		return false;
}

$laqira_payments_autoloader_loaded = laqira_payments_load_composer();

if ( $laqira_payments_autoloader_loaded ) {
		register_activation_hook( __FILE__, array( Installer::class, 'activate' ) );
		register_deactivation_hook( __FILE__, array( Installer::class, 'deactivate' ) );
}

/**
 * Handle plugin uninstall.
 */
function laqira_payments_uninstall(): void {
	if ( ! class_exists( '\\LaqiraPayments\\Support\\LaqiraPaymentsUninstaller' ) ) {
		return;
	}
	LaqiraPaymentsUninstaller::uninstall();
}
register_uninstall_hook( __FILE__, 'laqira_payments_uninstall' );

/**
 * Check environment requirements (WordPress, PHP, WooCommerce).
 *
 * @return bool True if requirements are met, false otherwise.
 */
function laqira_payments_check_requirements(): bool {
	$errors = array();

	if ( version_compare( get_bloginfo( 'version' ), '6.3', '<' ) ) {
		$errors[] = esc_html__( 'LaqiraPayments requires WordPress version 6.3 or higher.', 'laqira-payments' );
	}

	if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
		$errors[] = esc_html__( 'LaqiraPayments requires PHP version 8.1 or higher.', 'laqira-payments' );
	}

	$woocommerce_version = get_option( 'woocommerce_version', '0' );
	if ( ! class_exists( 'WooCommerce' ) || version_compare( $woocommerce_version, '8.2', '<' ) ) {
		$errors[] = esc_html__( 'LaqiraPayments requires WooCommerce version 8.2 or higher.', 'laqira-payments' );
	}

	if ( ! class_exists( 'WC_Logger' ) ) {
		$errors[] = esc_html__( 'LaqiraPayments requires WooCommerce Logger to be available.', 'laqira-payments' );
	}

	if ( ! laqira_payments_load_composer() ) {
		$errors[] = esc_html__( 'LaqiraPayments: Missing Composer dependencies.', 'laqira-payments' );
	}

	if ( ! empty( $errors ) && is_admin() && ! wp_doing_ajax() && ! defined( 'WP_CLI' ) ) {
		add_action(
			'admin_notices',
			function () use ( $errors ) {
				echo '<div class="notice notice-error"><p>' . implode( '<br>', array_map( 'esc_html', $errors ) ) . '</p></div>';
				if ( current_user_can( 'activate_plugins' ) ) {
					deactivate_plugins( LAQIRAPAYMENTS_PLUGIN_BASENAME );
				}
			}
		);
		return false;
	}

	return true;
}
add_action( 'plugins_loaded', 'laqira_payments_check_requirements', 5 );


/**
 * Declare WooCommerce compatibility features.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__ );
			FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__ );
		}
	}
);


/**
 * Main plugin execution.
 */
function laqira_payments_run(): void {
	if ( ! laqira_payments_check_requirements() ) {
		return;
	}
	$legacy_ajax = LAQIRAPAYMENTS_PLUGIN_DIR . 'app/Http/Controllers/Ajax/LegacyAjax.php';
	if ( file_exists( $legacy_ajax ) ) {
		require_once $legacy_ajax;
	}
	if ( class_exists( 'LaqiraPaymentsMain' ) ) {
		$plugin = new LaqiraPaymentsMain();
		$plugin->run();
	} else {
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-error"><p>' .
				esc_html__( 'LaqiraPayments: Main plugin class is missing.', 'laqira-payments' ) .
				'</p></div>';
			}
		);
	}
}

if ( $laqira_payments_autoloader_loaded ) {
		new Bootstrap();
}
