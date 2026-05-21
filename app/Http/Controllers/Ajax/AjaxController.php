<?php

namespace LaqiraPayments\Http\Controllers\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



use LaqiraPayments\Domain\Models\Transaction;
use LaqiraPayments\Domain\Services\LaqiraLogger;

/**
 * Handles AJAX requests for cart and transaction verification.
 */
class AjaxController {

	/**
	 * Verify transaction nonce and return a JSON response.
	 *
	 * @return void
	 */
	public static function verifyTransaction(): void {
		$transaction = Transaction::fromRequest();

		if ( ! $transaction->getNonce() || ! wp_verify_nonce( $transaction->getNonce(), 'laqira_payments_verify_transaction' ) ) {
			// Reject requests without a valid nonce to prevent CSRF.
			LaqiraLogger::log( 300, 'ajax', 'verify_transaction_invalid_nonce' );
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid nonce', 'laqira-payments' ) ) );
		}

		LaqiraLogger::log( 200, 'ajax', 'verify_transaction_success' );
		wp_send_json_success( array( 'message' => esc_html__( 'Transaction verified', 'laqira-payments' ) ) );
	}

	/**
	 * Update the cart data and return totals in a JSON response.
	 *
	 * @return void
	 */
	public static function updateCartData(): void {
		$transaction = Transaction::fromRequest();

		if ( ! $transaction->getNonce() || ! wp_verify_nonce( $transaction->getNonce(), 'laqira_payments_update_cart_data' ) ) {
			// Nonce validation guards the cart endpoint.
			LaqiraLogger::log( 300, 'ajax', 'update_cart_data_invalid_nonce' );
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid nonce', 'laqira-payments' ) ) );
		}

		$cart_total = 0;
		if ( function_exists( 'WC' ) && WC()->cart ) {
			$cart_total = WC()->cart->total; // Pull total directly from WooCommerce cart.
		}

		LaqiraLogger::log( 200, 'ajax', 'update_cart_data_success', array( 'cart_total' => $cart_total ) );
		wp_send_json_success( array( 'cartTotal' => $cart_total ) );
	}
}
