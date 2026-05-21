<?php

namespace LaqiraPayments\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



use LaqiraPayments\Services\BlockchainService;

class WooCommerceHelper {

	public function getTotal() {
		if ( $this->isActive() ) {
			$order = WC()->cart;
			if ( ! is_null( $order ) ) {
				return WC()->cart->get_total( 'edit' );
			}
			return 0;
		}
		return 0;
	}

	public function isActive(): bool {
		if ( ! function_exists( 'is_plugin_active' ) && is_readable( ABSPATH . 'wp-admin/includes/plugin.php' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return true;
		}

		return class_exists( 'WooCommerce' );
	}

	public function getWcpi() {
		$blockchain = new BlockchainService();
		$data       = $blockchain->getRemoteJsonCid( $blockchain->getCid() );
		if ( is_array( $data ) && isset( $data['wcpi'] ) ) { // Retrieve wcpi index from remote config.
			return $data['wcpi'];
		}
		return null;
	}
}
