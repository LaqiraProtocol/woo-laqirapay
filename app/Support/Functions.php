<?php

namespace LaqiraPayments\Support;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



/**
 * Define constants for LaqiraPayments plugin.
 *
 * WordPress functions may not be available during early autoloading,
 * so fall back to empty strings when they are missing.
 */

if ( function_exists( 'get_option' ) ) {
	define( 'LAQIRAPAYMENTS_MAIN_CONTRACT_ADDRESS', get_option( 'laqira_payments_main_contract' ) );
	define( 'LAQIRAPAYMENTS_MAIN_RPC_URL', get_option( 'laqira_payments_main_rpc_url' ) );
} else {
	define( 'LAQIRAPAYMENTS_MAIN_CONTRACT_ADDRESS', '' );
	define( 'LAQIRAPAYMENTS_MAIN_RPC_URL', '' );
}

if ( function_exists( 'plugins_url' ) && defined( 'LAQIRAPAYMENTS_PLUGIN_FILE' ) ) {
	define( 'LAQIRA_PLUGINS_URL', plugins_url( '/', LAQIRAPAYMENTS_PLUGIN_FILE ) );
} else {
	define( 'LAQIRA_PLUGINS_URL', '' );
}

define( 'LAQIRAPAYMENTS_TOKEN_BYTE_LENGTH', 32 );
define( 'LAQIRAPAYMENTS_JWT_ALG', 'HS256' );
define( 'LAQIRAPAYMENTS_JWT_ALG_SIGNATURE', 'sha256' );

/**
 * Fetch remote JSON securely with timeout and SSL verification.
 *
 * @param string $url     Remote URL.
 * @param int    $timeout Timeout in seconds.
 * @return array<string,mixed> Decoded JSON data or an empty array on failure.
 */
function laqira_payments_http_get_json( string $url, int $timeout = 10 ): array {
	$response = wp_remote_get(
		$url,
		array(
			'timeout'     => $timeout,
			'sslverify'   => true,
			'redirection' => 3,
		)
	);
	if ( is_wp_error( $response ) ) {
		return array();
	}
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );
	return is_array( $data ) ? $data : array();
}

/**
 * Retrieve a sanitized value from the $_SERVER superglobal.
 *
 * @param string $key Server key to fetch.
 * @return string
 */
function laqira_payments_server_value( string $key ): string {
	$allowed_keys = array(
		'HTTPS'                  => FILTER_UNSAFE_RAW,
		'REQUEST_SCHEME'         => FILTER_UNSAFE_RAW,
		'HTTP_X_FORWARDED_PROTO' => FILTER_UNSAFE_RAW,
		'SERVER_PORT'            => FILTER_UNSAFE_RAW,
		'REMOTE_ADDR'            => FILTER_UNSAFE_RAW,
	);

	if ( ! isset( $allowed_keys[ $key ] ) ) {
		return '';
	}

	$value = filter_input( INPUT_SERVER, $key, $allowed_keys[ $key ] );
	if ( $value === null || $value === false ) {
		return '';
	}

	if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
		$value = (string) $value;
	} elseif ( ! is_scalar( $value ) ) {
		return '';
	} else {
		$value = (string) $value;
	}

	if ( \function_exists( 'wp_unslash' ) ) {
		$value = \wp_unslash( $value );
	}

	if ( \function_exists( 'sanitize_text_field' ) && \function_exists( 'wp_check_invalid_utf8' ) ) {
		$value = \sanitize_text_field( $value );
	} else {
		$value = trim( wp_strip_all_tags( $value ) );
		$value = preg_replace( '/[\r\n\t\0\x0B]+/', '', $value );
	}

	return $value;
}

/**
 * Detect whether the current request is being served over HTTPS.
 *
 * WordPress exposes is_ssl() which also accounts for proxies. When it is not
 * available (such as in CLI contexts) fall back to common server variables so
 * the caller can still determine if a secure cookie should be required.
 */
function laqira_payments_is_secure_request(): bool {
	if ( function_exists( 'is_ssl' ) ) {
		return is_ssl();
	}

	$https = laqira_payments_server_value( 'HTTPS' );
	if ( $https !== '' && strtolower( $https ) !== 'off' ) {
		return true;
	}

	$scheme = laqira_payments_server_value( 'REQUEST_SCHEME' );
	if ( $scheme !== '' && strtolower( $scheme ) === 'https' ) {
		return true;
	}

	$forwardedProto = laqira_payments_server_value( 'HTTP_X_FORWARDED_PROTO' );
	if ( $forwardedProto !== '' && strtolower( $forwardedProto ) === 'https' ) {
		return true;
	}

	$port = laqira_payments_server_value( 'SERVER_PORT' );
	return $port === '443';
}

/**
 * Build the options array for the LaqiraPayments JWT cookie.
 */
function laqira_payments_cookie_options( int $expires ): array {
	return array(
		'expires'  => $expires,
		'path'     => '/',
		'secure'   => laqira_payments_is_secure_request(),
		'httponly' => true,
		'samesite' => 'Strict',
	);
}

/**
 * Compare cart items with order items.
 *
 * @param array     $cart_items Current cart items.
 * @param \WC_Order $order      Order instance.
 * @return bool True if equal, false otherwise.
 */
function laqira_payments_are_cart_and_order_items_equal( array $cart_items, \WC_Order $order ): bool {
	$order_items = $order->get_items();
	if ( count( $cart_items ) !== count( $order_items ) ) {
		return false;
	}
	$cart_products = array();
	foreach ( $cart_items as $item ) {
		$cart_products[ $item['product_id'] ] = $item['quantity'];
	}
	foreach ( $order_items as $item ) {
		$product_id = $item->get_product_id();
		$quantity   = $item->get_quantity();
		if ( ! isset( $cart_products[ $product_id ] ) || $cart_products[ $product_id ] != $quantity ) {
			return false;
		}
		unset( $cart_products[ $product_id ] );
	}
	return empty( $cart_products );
}

/**
 * Find WooCommerce order by transaction hash.
 *
 * @param string $tx_hash Transaction hash to search.
 * @return int|null Order ID or null if not found.
 */
function laqira_payments_find_order_by_tx_hash( string $tx_hash ): ?int {
	global $wpdb;

	$table_name = $wpdb->prefix . 'laqira_payments_transactions';
	$order_id   = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Lookup uses the plugin transaction table and returns a single order id.
		$wpdb->prepare(
			'SELECT wc_order_id FROM %i WHERE tx_hash = %s ORDER BY id DESC LIMIT 1',
			$table_name,
			$tx_hash
		)
	);

	return $order_id ? (int) $order_id : null;
}

/**
 * Format date and human readable diff.
 *
 * @param string|\WC_DateTime $date_string Date string or object.
 * @return string
 */
function laqira_payments_format_date( $date_string ): string {
	$date = $date_string instanceof \WC_DateTime ? $date_string->getTimestamp() : strtotime( (string) $date_string );
	if ( $date === false ) {
		return '';
	}
	$dt       = new \DateTime( '@' . $date );
	$now      = new \DateTime( 'now', $dt->getTimezone() );
	$interval = $now->diff( $dt );
	if ( $interval->d > 0 ) {
		$diff = $interval->d . ' days ago';
	} elseif ( $interval->h > 0 ) {
		$diff = $interval->h . ' hrs ago';
	} elseif ( $interval->i > 0 ) {
		$diff = $interval->i . ' mins ago';
	} else {
		$diff = $interval->s . ' secs ago';
	}
	$formatted = gmdate( 'M-d-Y h:i:s A', $date );
	return $diff . ' (' . $formatted . ')';
}
