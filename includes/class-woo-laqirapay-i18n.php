<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://laqira.io
 * @since      0.1.0
 * @package    WooLaqiraPay
 * @subpackage WooLaqiraPay/includes
 * @author     Laqira Protocol <info@laqira.io>
 */
class WooLaqiraPayi18n {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function woo_laqirapay_textdomain() {
		load_plugin_textdomain(
			'woo-laqirapay',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages'
		);
	}
}
