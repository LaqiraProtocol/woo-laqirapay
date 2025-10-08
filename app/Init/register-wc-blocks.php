<?php
/**
 * Register WooCommerce Blocks payment gateway integration.
 *
 * This file ensures WC_laqirapay is loaded before WC_laqirapay_Block
 */

use LaqiraPay\Services\BlockchainService;
use LaqiraPay\Helpers\JwtHelper;
use LaqiraPay\Helpers\WooCommerceHelper;

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'add_action' ) ) {
	return;
}

function laqirapay_register_wc_block_support() {
	if ( class_exists( 'Automattic\\WooCommerce\\Blocks\\Payments\\Integrations\\AbstractPaymentMethodType' ) ) {
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function ( $registry ) {
				$registry->register( new WC_laqirapay_Block( new WooCommerceHelper(), new JwtHelper(), new BlockchainService() ) );
			}
		);
	}
}

add_action( 'woocommerce_blocks_loaded', 'laqirapay_register_wc_block_support' );
