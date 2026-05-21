<?php
/**
 * Register WooCommerce Blocks payment gateway integration.
 *
 * This file ensures the LaqiraPayments block integration is loaded.
 */

use LaqiraPayments\Services\BlockchainService;
use LaqiraPayments\Helpers\JwtHelper;
use LaqiraPayments\Helpers\WooCommerceHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'add_action' ) ) {
	return;
}

function laqira_payments_register_wc_block_support() {
	require_once __DIR__ . '/../WooCommerce/class-laqira-payments-block.php';
	if ( class_exists( 'Automattic\\WooCommerce\\Blocks\\Payments\\Integrations\\AbstractPaymentMethodType' ) ) {
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function ( $registry ) {
				$registry->register( new \LaqiraPaymentsBlock( new WooCommerceHelper(), new JwtHelper(), new BlockchainService() ) );
			}
		);
	}
}

add_action( 'woocommerce_blocks_loaded', 'laqira_payments_register_wc_block_support' );
