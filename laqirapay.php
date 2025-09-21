<?php
/**
 * Plugin Name:       LaqiraPay
 * Plugin URI:        https://laqirahub.com
 * Description:       LaqiraPay: Fully Decentralized Asset-Agnostic MultiNetwork Payment Gateway for WooCommerce
 * Version:           0.9.7
 * Author:            Laqira Protocol
 * Author URI:        https://laqira.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       laqirapay
 * Domain Path:       /languages
 * Tested up to:      6.8.2
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * WC requires at least: 8.2
 * WC tested up to:   10.1.2
 */

defined('ABSPATH') || exit;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use LaqiraPay\Bootstrap;
use LaqiraPay\Core\Installer;
use LaqiraPay\Support\LaqiraPayUninstaller;

// Define plugin constants
const LAQIRAPAY_VERSION = '0.9.7';
if (!defined('LAQIRAPAY_PLUGIN_DIR')) {
        define('LAQIRAPAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('LAQIRAPAY_PLUGIN_BASENAME')) {
        define('LAQIRAPAY_PLUGIN_BASENAME', plugin_basename(__FILE__));
}
const LAQIRAPAY_SETTINGS_PAGE = 'laqirapay-settings';

/**
 * Add action links (Settings, Support) in Plugins list row.
 *
 * @param array $actions Default plugin links.
 *
 * @return array Merged action links.
 */
function laqirapay_action_links( array $actions): array {
	$custom_actions = array(
		'settings' => '<a href="' . esc_url( wp_nonce_url( add_query_arg( [ 'page' => LAQIRAPAY_SETTINGS_PAGE ], admin_url( 'admin.php' ) ), 'laqirapay_settings_access' ) ) . '">' . esc_html__( 'Settings', 'laqirapay' ) . '</a>',
		'support' => '<a href="' . esc_url( 'https://laqirahub.com/laqira-pay/introduction' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Support', 'laqirapay' ) . '</a>',
	);

	return array_merge($custom_actions, $actions);
}
add_filter('plugin_action_links_' . LAQIRAPAY_PLUGIN_BASENAME, 'laqirapay_action_links');

/**
 * Load composer dependencies safely.
 *
 * @return bool True if autoload file exists and is loaded, false otherwise.
 */
function laqirapay_load_composer(): bool {
        static $notice_registered = false;

        $autoload_path = LAQIRAPAY_PLUGIN_DIR . 'vendor/autoload.php';
        if (file_exists($autoload_path)) {
                require_once $autoload_path;

                return true;
        }

        if (!$notice_registered) {
                add_action('admin_notices', function () {
                        echo '<div class="notice notice-error"><p>' .
                             esc_html__('LaqiraPay: Composer autoload file is missing. Please run `composer install`.', 'laqirapay') .
                             '</p></div>';
                });
                $notice_registered = true;
        }

        return false;
}

$laqirapay_autoloader_loaded = laqirapay_load_composer();

if ($laqirapay_autoloader_loaded) {
        register_activation_hook(__FILE__, [Installer::class, 'activate']);
        register_deactivation_hook(__FILE__, [Installer::class, 'deactivate']);
}

/**
 * Handle plugin uninstall.
 */
function uninstall_laqirapay(): void {
	if ( ! class_exists( '\\LaqiraPay\\Support\\LaqiraPayUninstaller' ) ) {
		return;
	}
	LaqiraPayUninstaller::uninstall();
}
register_uninstall_hook(__FILE__, 'uninstall_laqirapay');

/**
 * Check environment requirements (WordPress, PHP, WooCommerce).
 *
 * @return bool True if requirements are met, false otherwise.
 */
function check_laqirapay_requirements(): bool {
	$errors = [];

	if (version_compare(get_bloginfo('version'), '6.3', '<')) {
		$errors[] = esc_html__('LaqiraPay requires WordPress version 6.3 or higher.', 'laqirapay');
	}

	if (version_compare(PHP_VERSION, '7.4', '<')) {
		$errors[] = esc_html__('LaqiraPay requires PHP version 7.4 or higher.', 'laqirapay');
	}

	$woocommerce_version = get_option('woocommerce_version', '0');
	if (!class_exists('WooCommerce') || version_compare($woocommerce_version, '8.2', '<')) {
		$errors[] = esc_html__('LaqiraPay requires WooCommerce version 8.2 or higher.', 'laqirapay');
	}

	if (!class_exists('WC_Logger')) {
		$errors[] = esc_html__('LaqiraPay requires WooCommerce Logger to be available.', 'laqirapay');
	}

	if (!laqirapay_load_composer()) {
		$errors[] = esc_html__('LaqiraPay: Missing Composer dependencies.', 'laqirapay');
	}

	if (!empty($errors) && is_admin() && !wp_doing_ajax() && !defined('WP_CLI')) {
		add_action('admin_notices', function () use ($errors) {
			echo '<div class="notice notice-error"><p>' . implode('<br>', array_map('esc_html', $errors)) . '</p></div>';
			if (current_user_can('activate_plugins')) {
				deactivate_plugins(LAQIRAPAY_PLUGIN_BASENAME);
			}
		});
		return false;
	}

	return true;
}
add_action('plugins_loaded', 'check_laqirapay_requirements', 5);

///**
// * Enable update checker if explicitly enabled via LAQIRAPAY_DEBUG.
// */
//if (LAQIRAPAY_ENABLE_UPDATE_CHECKER) {
//	function laqirapay_activate_update_checker(): void {
//		if (class_exists('\\LaqiraPay\\Support\\LaqiraPayUpdateChecker')) {
////			$update_checker = new LaqiraPayUpdateChecker();
//		}
//	}
//	add_action('plugins_loaded', callback: 'laqirapay_activate_update_checker' );
//}

/**
 * Declare WooCommerce compatibility features.
 */
add_action('before_woocommerce_init', function () {
	if (class_exists('\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil')) {
		FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__ );
		FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__ );
	}
});


/**
 * Main plugin execution.
 */
function run_laqirapay(): void {
	if (!check_laqirapay_requirements()) {
		return;
	}
	$legacy_ajax = LAQIRAPAY_PLUGIN_DIR . 'app/Http/Controllers/Ajax/LegacyAjax.php';
	if (file_exists($legacy_ajax)) {
		require_once $legacy_ajax;
	}
	if (class_exists('LaqiraPayMain')) {
		$plugin = new LaqiraPayMain();
		$plugin->run();
	} else {
		add_action('admin_notices', function () {
			echo '<div class="notice notice-error"><p>' .
			     esc_html__('LaqiraPay: Main plugin class is missing.', 'laqirapay') .
			     '</p></div>';
		});
	}
}

if ($laqirapay_autoloader_loaded) {
        new Bootstrap();
}
