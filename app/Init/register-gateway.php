<?php
/**
 * Register the LaqiraPayments gateway with WooCommerce.
 */

use LaqiraPayments\WooCommerce\Gateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'ABSPATH' ) && function_exists( 'add_filter' ) ) {
	/**
	 * Register LaqiraPayments as a payment gateway.
	 *
	 * @param array $gateways Existing WooCommerce gateways.
	 * @return array Modified gateways including LaqiraPayments gateway.
	 */
	function laqira_payments_register_gateway( $gateways ) {
		$gateways[] = Gateway::class;
		return $gateways;
	}

	add_filter( 'woocommerce_payment_gateways', 'laqira_payments_register_gateway' );
}
