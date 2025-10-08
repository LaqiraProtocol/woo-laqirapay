<?php
/**
 * Register the LaqiraPay gateway with WooCommerce.
 */

use LaqiraPay\WooCommerce\Gateway;

if ( defined( 'ABSPATH' ) && function_exists( 'add_filter' ) ) {
	/**
	 * Register LaqiraPay as a payment gateway.
	 *
	 * @param array $gateways Existing WooCommerce gateways.
	 * @return array Modified gateways including LaqiraPay gateway.
	 */
	function laqirapay_register_gateway( $gateways ) {
		$gateways[] = Gateway::class;
		return $gateways;
	}

	add_filter( 'woocommerce_payment_gateways', 'laqirapay_register_gateway' );
}
