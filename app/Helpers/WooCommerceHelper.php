<?php

namespace LaqiraPay\Helpers;

use LaqiraPay\Services\BlockchainService;

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
		$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';
		return in_array( $plugin_path, wp_get_active_and_valid_plugins() ) // Check single-site plugins.
			|| in_array( $plugin_path, wp_get_active_network_plugins() ); // Check network-wide plugins.
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
