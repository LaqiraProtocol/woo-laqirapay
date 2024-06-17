<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Woo LaqiraPay
 * Plugin URI:        https://Laqirahub.com
 * Description:       LaqiraPay : The First Ever Fully Decentralized Asset-Agnostic MultiNetwork Payment Gateway for woocommerce
 * Version:           0.3.3
 * Author:            Laqira Protocol
 * Author URI:        https://laqira.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-laqirapay
 * Domain Path:       /languages
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * WC requires at least: 8.2
 * WC tested up to: 8.7
 */


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
function woo_laqirapay_action_links($actions)
    {
        $custom_actions = array(
            'Settings'  => sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=woo-laqirapay-settings' ), __( 'Settings', 'woo-laqirapay' ) ),
            'support'   => sprintf( '<a href="%s" target="_blank">%s</a>', 'https://laqirahub.com/laqira-pay/introduction', __( 'Support', 'woo-laqirapay' ) ),
        );

        // add the links to the front of the actions list
        return array_merge( $custom_actions, $actions );
    }

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'woo_laqirapay_action_links');


// Include the web3.php library (adjust the path as needed).
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

/**
 * Currently plugin version.
 * Start at version 0.1.0 and use SemVer - https://semver.org
 */
define( 'WOO_LAQIRAPAY_VERSION', '0.3.3' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-laqirapay-activator.php
 */
function activate_woo_laqirapay() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-woo-laqirapay-activator.php';
	WooLaqiraPayActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-laqirapay-deactivator.php
 */
function deactivate_woo_laqirapay() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-laqirapay-deactivator.php';
	WooLaqiraPayDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_laqirapay' );
register_deactivation_hook( __FILE__, 'deactivate_woo_laqirapay' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-laqirapay.php';

/**
 * Checks the requirements for WooLaqiraPay plugin.
 *
 * @return void
 */
function check_woo_laqirapay_requirements() {
    // Check WordPress version
    if (version_compare(get_bloginfo('version'), '6.2', '<')) {
        // Display error message as admin notice
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . __('WooLaqiraPay requires WordPress version 6.2 or higher.', 'woo-laqirapay') . '</p></div>';
        });
        return  false;
    }

    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        // Display error message as admin notice
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . __('WooLaqiraPay requires PHP version 7.4 or higher.', 'woo-laqirapay') . '</p></div>';
        });
        return false;
    }

    // Check if WooCommerce is installed and active
    if (!class_exists('WooCommerce') || version_compare(WC()->version, '8.2', '<')) {
        // Display error message as admin notice
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . __('WooLaqiraPay requires WooCommerce version 8.2 or higher.', 'woo-laqirapay') . '</p></div>';
        });
        return false;
    }
	
}
add_action('plugins_loaded', 'check_woo_laqirapay_requirements');

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_laqirapay() {

	$plugin = new WooLaqiraPAY();
	$plugin->run();

}
run_woo_laqirapay();
