<?php

use LaqiraPay\Helpers\WooCommerceHelper;
use LaqiraPay\Helpers\JwtHelper;
use LaqiraPay\Services\BlockchainService;
use LaqiraPay\Services\TransactionDecoder;
use LaqiraPay\Domain\Services\LaqiraLogger;
use kornrunner\Keccak;
use function LaqiraPay\Support\are_cart_and_order_items_equal;
use function LaqiraPay\Support\find_order_by_tx_hash;
use function LaqiraPay\Support\format_date;

if ( ! function_exists( 'laqirapay_sanitize_simple_text' ) ) {
	function laqirapay_sanitize_simple_text( $value ): string {
		if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
				$value = (string) $value;
		}

		if ( ! is_scalar( $value ) ) {
				return '';
		}

			$value = (string) $value;

		if ( function_exists( 'sanitize_text_field' ) ) {
				return sanitize_text_field( $value );
		}

			$value = strip_tags( $value );
			if ($value === null) {
   			 $value = '';
			}


			return trim( preg_replace( '/[\r\n\t\0\x0B]+/', ' ', $value ) );
	}
}

if ( ! function_exists( 'laqirapay_sanitize_textarea' ) ) {
	function laqirapay_sanitize_textarea( $value ): string {
		if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
				$value = (string) $value;
		}

		if ( ! is_scalar( $value ) ) {
				return '';
		}

		if ( function_exists( 'sanitize_textarea_field' ) ) {
				return sanitize_textarea_field( $value );
		}

			return laqirapay_sanitize_simple_text( $value );
	}
}

if ( ! function_exists( 'laqirapay_sanitize_positive_int' ) ) {
	function laqirapay_sanitize_positive_int( $value ): ?int {
		if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
				$value = (string) $value;
		}

		if ( is_string( $value ) ) {
				$value = trim( $value );
		}

		if ( $value === '' || $value === null ) {
				return null;
		}

		if ( is_int( $value ) && $value > 0 ) {
				return $value;
		}

			$stringValue = (string) $value;

		if ( ! ctype_digit( $stringValue ) ) {
				return null;
		}

			$intValue = (int) $stringValue;

			return $intValue > 0 ? $intValue : null;
	}
}

if ( ! function_exists( 'laqirapay_sanitize_decimal_string' ) ) {
	function laqirapay_sanitize_decimal_string( $value, bool $allow_zero = true ): ?string {
		if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
				$value = (string) $value;
		}

		if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
				return null;
		}

			$value = trim( (string) $value );

		if ( $value === '' ) {
				return null;
		}

		if ( ! preg_match( '/^\d+(\.\d+)?$/', $value ) ) {
				return null;
		}

		if ( ! $allow_zero && (float) $value <= 0 ) {
				return null;
		}

			return $value;
	}
}

if ( ! function_exists( 'laqirapay_sanitize_eth_address' ) ) {
	function laqirapay_sanitize_eth_address( $value ): ?string {
		if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
				$value = (string) $value;
		}

		if ( ! is_string( $value ) ) {
				return null;
		}

			$value = strtolower( trim( $value ) );

		if ( strpos( $value, '0x' ) === 0 ) {
				$value = substr( $value, 2 );
		}

		if ( strlen( $value ) !== 40 || ! ctype_xdigit( $value ) ) {
				return null;
		}

			return '0x' . $value;
	}
}

if ( ! function_exists( 'laqirapay_sanitize_tx_hash' ) ) {
	function laqirapay_sanitize_tx_hash( $value ): ?string {
		if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
				$value = (string) $value;
		}

		if ( ! is_string( $value ) ) {
				return null;
		}

			$value = strtolower( trim( $value ) );

		if ( strpos( $value, '0x' ) === 0 ) {
				$value = substr( $value, 2 );
		}

		if ( strlen( $value ) !== 64 || ! ctype_xdigit( $value ) ) {
				return null;
		}

			return '0x' . $value;
	}
}

if ( ! function_exists( 'laqirapay_sanitize_req_hash' ) ) {
	function laqirapay_sanitize_req_hash( $value ): ?string {
		if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
				$value = (string) $value;
		}

		if ( ! is_string( $value ) ) {
				return null;
		}

			$value = strtolower( trim( $value ) );

		if ( strpos( $value, '0x' ) === 0 ) {
				$value = substr( $value, 2 );
		}

		if ( strlen( $value ) !== 64 || ! ctype_xdigit( $value ) ) {
				return null;
		}

			return $value;
	}
}

if ( ! function_exists( 'laqirapay_admin_result_allowed_html' ) ) {
	function laqirapay_admin_result_allowed_html(): array {
			return array(
				'div'  => array(
					'class' => true,
				),
				'span' => array(
					'class' => true,
					'style' => true,
				),
				'h3'   => array(
					'class' => true,
				),
				'p'    => array(
					'class' => true,
				),
			);
	}
}

if ( ! function_exists( 'laqirapay_filter_input' ) ) {
	function laqirapay_filter_input( int $type, string $key, int $filter = FILTER_UNSAFE_RAW ) {
			$value = filter_input( $type, $key, $filter );

		if ( $value !== null && $value !== false ) {
				return $value;
		}

		if ( $value === false ) {
				return false;
		}

		switch ( $type ) {
			case INPUT_GET:
					$source = $_GET ?? array();
				break;
			case INPUT_POST:
					$source = $_POST ?? array();
				break;
			case INPUT_COOKIE:
					$source = $_COOKIE ?? array();
				break;
			case INPUT_SERVER:
					$source = $_SERVER ?? array();
				break;
			case INPUT_ENV:
					$source = $_ENV ?? array();
				break;
			default:
					$source = null;
				break;
		}

		if ( ! is_array( $source ) || ! array_key_exists( $key, $source ) ) {
				return null;
		}

			$raw = $source[ $key ];

		if ( is_array( $raw ) ) {
				return null;
		}

		if ( $filter === FILTER_UNSAFE_RAW ) {
				return $raw;
		}

			$filtered = filter_var( $raw, $filter );

			return $filtered === false ? false : $filtered;
	}
}

if ( ! function_exists( 'laqirapay_get_sanitized_wp_error_message' ) ) {
	function laqirapay_get_sanitized_wp_error_message( $maybe_error, string $fallback = '' ): ?string {
		if ( ! function_exists( 'is_wp_error' ) || ! is_wp_error( $maybe_error ) ) {
				return null;
		}

			$message   = $maybe_error->get_error_message();
			$sanitized = laqirapay_sanitize_textarea( $message );

		if ( $sanitized === '' ) {
				$sanitized = $fallback !== '' ? $fallback : esc_html__( 'An unexpected error occurred. Please try again later.', 'laqirapay' );
		}

			return $sanitized;
	}
}

if ( ! function_exists( 'laqirapay_is_successful_transaction_status' ) ) {
		/**
		 * Determine whether a transaction status value represents a successful outcome.
		 *
		 * @param mixed $status Raw status value returned from the blockchain RPC response.
		 *
		 * @return bool True when the provided status indicates a successful transaction.
		 */
	function laqirapay_is_successful_transaction_status( $status ): bool {
		if ( is_object( $status ) && method_exists( $status, '__toString' ) ) {
				$status = (string) $status;
		}

		if ( is_bool( $status ) ) {
				return $status === true;
		}

		if ( is_int( $status ) ) {
				return $status === 1;
		}

		if ( ! is_string( $status ) ) {
				return false;
		}

			$normalized = strtolower( trim( $status ) );

		if ( $normalized === '' ) {
				return false;
		}

		if ( strpos( $normalized, '0x' ) === 0 ) {
				$hex = substr( $normalized, 2 );

			if ( $hex === '' || ! ctype_xdigit( $hex ) ) {
					return false;
			}

				return intval( $hex, 16 ) === 1;
		}

		if ( ctype_digit( $normalized ) ) {
				return (int) $normalized === 1;
		}

			return false;
	}
}

if ( ! function_exists( 'laqirapay_sanitize_url_field' ) ) {
	function laqirapay_sanitize_url_field( $value ): ?string {
		if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
				$value = (string) $value;
		}

		if ( ! is_string( $value ) ) {
				return null;
		}

			$value = trim( $value );

		if ( $value === '' ) {
				return null;
		}

			$sanitized = filter_var( $value, FILTER_VALIDATE_URL );

		if ( $sanitized === false ) {
				return null;
		}

			$scheme = strtolower( (string) parse_url( $sanitized, PHP_URL_SCHEME ) );

		if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
				return null;
		}

			return $sanitized;
	}
}

if ( ! function_exists( 'laqirapay_sanitize_transaction_payload' ) ) {
	function laqirapay_sanitize_transaction_payload( array $payload, array $required_keys = array() ): array {
			$spec = array(
				'orderID'                => array(
					'output_key' => 'order_id',
					'type'       => 'int',
					'label'      => 'order ID',
				),
				'slippage'               => array(
					'output_key' => 'slippage',
					'type'       => 'decimal',
					'label'      => 'slippage',
					'options'    => array( 'allow_zero' => true ),
					'default'    => '0',
				),
				'tx_hash'                => array(
					'output_key' => 'tx_hash',
					'type'       => 'tx_hash',
					'label'      => 'transaction hash',
				),
				'txStatus'               => array(
					'output_key' => 'tx_status',
					'type'       => 'text',
					'label'      => 'transaction status',
					'default'    => '',
				),
				'firstTX_log'            => array(
					'output_key' => 'tx_log',
					'type'       => 'text',
					'label'      => 'transaction log',
					'default'    => '',
					'options'    => array( 'allow_empty' => true ),
				),
				'siteAdminAddressWallet' => array(
					'output_key' => 'site_admin_address_wallet',
					'type'       => 'eth_address',
					'label'      => 'admin wallet address',
				),
				'userWallet'             => array(
					'output_key' => 'user_wallet',
					'type'       => 'eth_address',
					'label'      => 'customer wallet address',
				),
				'reqHash'                => array(
					'output_key' => 'req_hash',
					'type'       => 'req_hash',
					'label'      => 'request hash',
				),
				'price'                  => array(
					'output_key' => 'price',
					'type'       => 'decimal',
					'label'      => 'price',
					'options'    => array( 'allow_zero' => true ),
				),
				'asset'                  => array(
					'output_key' => 'asset',
					'type'       => 'eth_address',
					'label'      => 'asset address',
				),
				'assetName'              => array(
					'output_key' => 'asset_name',
					'type'       => 'text',
					'label'      => 'asset name',
				),
				'assetAmount'            => array(
					'output_key' => 'asset_amount',
					'type'       => 'decimal',
					'label'      => 'asset amount',
					'options'    => array( 'allow_zero' => true ),
				),
				'exchangeRate'           => array(
					'output_key' => 'exchange_rate',
					'type'       => 'decimal',
					'label'      => 'exchange rate',
					'options'    => array( 'allow_zero' => false ),
					'default'    => '0',
				),
				'payment_type'           => array(
					'output_key' => 'payment_type',
					'type'       => 'text',
					'label'      => 'payment type',
				),
				'network_rpc'            => array(
					'output_key' => 'network_rpc',
					'type'       => 'url',
					'label'      => 'network RPC URL',
				),
				'network_explorer'       => array(
					'output_key' => 'network_explorer',
					'type'       => 'url',
					'label'      => 'network explorer URL',
					'default'    => '',
					'options'    => array( 'allow_empty' => true ),
				),
			);

			$sanitized = array();

			foreach ( $spec as $key => $info ) {
					$outputKey  = $info['output_key'];
					$default    = $info['default'] ?? null;
					$allowEmpty = ! empty( $info['options']['allow_empty'] );
					$required   = in_array( $key, $required_keys, true );
					$hasValue   = array_key_exists( $key, $payload );

				if ( ! $hasValue ) {
					if ( $required ) {
						$label = esc_html__( $info['label'], 'laqirapay' );

						return array( 'error' => sprintf( esc_html__( 'Missing required %s.', 'laqirapay' ), $label ) );
					}

					if ( $default !== null ) {
							$sanitized[ $outputKey ] = $default;
					} elseif ( $allowEmpty ) {
							$sanitized[ $outputKey ] = '';
					}

						continue;
				}

					$rawValue = $payload[ $key ];
					$value    = null;

				switch ( $info['type'] ) {
					case 'int':
							$value = laqirapay_sanitize_positive_int( $rawValue );
						break;
					case 'decimal':
							$allowZero = $info['options']['allow_zero'] ?? true;
								$value = laqirapay_sanitize_decimal_string( $rawValue, $allowZero );
						break;
					case 'eth_address':
							$value = laqirapay_sanitize_eth_address( $rawValue );
						break;
					case 'tx_hash':
							$value = laqirapay_sanitize_tx_hash( $rawValue );
						break;
					case 'req_hash':
							$value = laqirapay_sanitize_req_hash( $rawValue );
						break;
					case 'url':
							$value = laqirapay_sanitize_url_field( $rawValue );
						break;
					case 'text':
					default:
							$value = laqirapay_sanitize_simple_text( $rawValue );
						break;
				}

				if ( $value === null || ( $value === '' && ! $allowEmpty ) ) {
					if ( $required ) {
							$label = esc_html__( $info['label'], 'laqirapay' );

							return array( 'error' => sprintf( esc_html__( 'Invalid %s.', 'laqirapay' ), $label ) );
					}

					if ( $default !== null ) {
							$sanitized[ $outputKey ] = $default;
					} elseif ( $allowEmpty ) {
							$sanitized[ $outputKey ] = '';
					}

						continue;
				}

					$sanitized[ $outputKey ] = $value;
			}

			foreach ( $required_keys as $key ) {
				if ( ! isset( $spec[ $key ] ) ) {
						continue;
				}

					$outputKey = $spec[ $key ]['output_key'];

				if ( ! array_key_exists( $outputKey, $sanitized ) ) {
					if ( array_key_exists( 'default', $spec[ $key ] ) ) {
							$sanitized[ $outputKey ] = $spec[ $key ]['default'];
					} elseif ( ! empty( $spec[ $key ]['options']['allow_empty'] ) ) {
							$sanitized[ $outputKey ] = '';
					}
				}
			}

			return array( 'data' => $sanitized );
	}
}


add_action( 'wp_ajax_laqirapay_update_cart_data', 'laqirapay_update_cart_data' );

add_action( 'wp_ajax_nopriv_laqirapay_update_cart_data', 'laqirapay_update_cart_data' );

/**
 * Update cart totals using the saved exchange rate and respond with JSON.
 *
 * @return void
 */
function laqirapay_update_cart_data(): void {
	if ( ! check_ajax_referer( 'laqira_nonce', 'security', false ) ) {
			LaqiraLogger::log( 300, 'ajax', 'legacy_update_cart_data_invalid_nonce' );
			wp_send_json_error( array( 'message' => esc_html__( 'nonce Error!!!', 'laqirapay' ) ) );
			return;
	}

		// Get the current total amount in the WooCommerce cart.
		$cart_total = ( new WooCommerceHelper() )->getTotal();

	// Retrieve WooCommerce currencies and the current currency.
	$currencies       = get_woocommerce_currencies();
	$current_currency = get_woocommerce_currency();

	// Check if the current currency is not USD; if so, apply exchange rate conversion.
	if ( $current_currency != 'USD' ) {
		// Retrieve the saved exchange rate for the current currency.
		$saved_exchange_rate = get_option( 'laqirapay_exchange_rate_' . $current_currency, '' );

		// If no saved exchange rate exists, default to 1.
		if ( ! $saved_exchange_rate ) {
			$saved_exchange_rate = 1;
		}

		// Calculate the final amount in USD using the exchange rate and format it.
		$final_amount           = $cart_total / $saved_exchange_rate;
		$final_amount_formatted = number_format( $final_amount, 2 );
	} else {
		// For USD, no conversion is needed, so set exchange rate to 1.
		$saved_exchange_rate = 1;

		// Final amount remains the same as the cart total.
		$final_amount           = $cart_total / $saved_exchange_rate;
		$final_amount_formatted = $final_amount;
	}

		// Return the original and converted cart totals in a JSON response.
		LaqiraLogger::log(
			200,
			'ajax',
			'legacy_update_cart_data',
			array(
				'original'  => $cart_total,
				'converted' => $final_amount_formatted,
			)
		);
		wp_send_json_success(
			array(
				'originalOrderAmount' => $cart_total,
				'cartTotal'           => $final_amount_formatted,
			)
		);
}


add_action( 'wp_ajax_laqira_get_order_for_laqira_pay', 'laqira_get_order_for_laqira_pay' );
add_action( 'wp_ajax_nopriv_laqira_get_order_for_laqira_pay', 'laqira_get_order_for_laqira_pay' );

/**
 * Create or update an order for LaqiraPay checkout.
 *
 * @return void
 */
function laqira_get_order_for_laqira_pay() {
	try {
				// Check Ajax nonce.
				$nonce_raw = laqirapay_filter_input( INPUT_POST, 'security' );
				$nonce     = is_string( $nonce_raw ) ? sanitize_text_field( wp_unslash( $nonce_raw ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'laqira_nonce' ) ) {
				LaqiraLogger::log( 300, 'security', 'get_order_invalid_nonce' );
				wp_send_json_error(
					array(
						'result' => 'failed',
						'error'  => esc_html__( 'nonce Error!!!', 'laqirapay' ),
					)
				);
				return;
		}

				$jwt_cookie_raw = laqirapay_filter_input( INPUT_COOKIE, 'laqira_jwt' );
				$jwt_cookie     = '';
		if ( is_string( $jwt_cookie_raw ) && $jwt_cookie_raw !== '' ) {
				$jwt_cookie = sanitize_text_field( wp_unslash( $jwt_cookie_raw ) );
		}

		if ( $jwt_cookie === '' || ( new JwtHelper() )->verify_header( $jwt_cookie ) !== 'verified' ) {
				LaqiraLogger::log( 300, 'security', 'get_order_unauthorized' );
				wp_send_json_error(
					array(
						'result' => 'error',
						'error'  => esc_html__( 'Your request was not Authorized. Please refresh the checkout page again', 'laqirapay' ),
					)
				);
				return;
		}

		global $woocommerce;

				// Receive Customer Information (if any)

				$customer_raw  = laqirapay_filter_input( INPUT_POST, 'customer' );
				$customer_json = '';
		if ( is_string( $customer_raw ) && $customer_raw !== '' ) {
			if ( function_exists( 'wp_unslash' ) ) {
						$customer_json = wp_unslash( $customer_raw );
			} else {
							$customer_json = stripslashes( $customer_raw );
			}
		}

				$customer_data = $customer_json !== '' ? json_decode( $customer_json, true ) : array();

		if ( ! is_array( $customer_data ) ) {
				LaqiraLogger::log( 300, 'ajax', 'get_order_invalid_customer_payload' );
				$customer_data = array();
		} elseif ( function_exists( 'wc_clean' ) ) {
				$customer_data = wc_clean( $customer_data );
		} else {
				array_walk_recursive(
					$customer_data,
					static function ( &$value ): void {
								$value = sanitize_text_field( (string) $value );
					}
				);
		}

		$last_order_id = $woocommerce->session->get( 'last_order_id' );
		$old_order     = wc_get_order( $last_order_id );
		$current_cart  = $woocommerce->cart->get_cart();
		$order_status  = '';

		// Auxiliary function to compare the shopping cart with the order

		if ( $old_order ) {
			if (
						$last_order_id &&
						$old_order &&
						$old_order->get_status() === 'pending' &&
						are_cart_and_order_items_equal( $current_cart, $old_order )
				) {
						// Cart unchanged; reuse existing pending order

					$order        = $old_order;
					$order_status = ' Updated.';
			} else {
				// Shopping cart has changed or there is no previous order, make a new order

				$order        = wc_create_order();
				$order_status = ' Created.';
				$woocommerce->session->set( 'last_order_id', $order->get_id() );
			}
		} else {
			$order        = wc_create_order();
			$order_status = ' Created.';
			$woocommerce->session->set( 'last_order_id', $order->get_id() );
		}

		// Remove previous products and add current products

		foreach ( $order->get_items() as $item_id => $item ) {
				$order->remove_item( $item_id );
		}
		foreach ( $current_cart as $cart_item_key => $cart_item ) {
				$product  = $cart_item['data'];
				$quantity = $cart_item['quantity'];
				$order->add_product( $product, $quantity );
		}

		// Set user information (if login is login)

		if ( get_option( 'laqirapay_only_logged_in_user' ) != 0 && is_user_logged_in() ) {
			$order->set_customer_id( get_current_user_id() );
		}

		// Updating the sum of the order

		$order->calculate_totals();

		// Set the status and order notes

		$order->update_status( 'wc-pending', esc_html__( 'Payment is awaited.', 'laqirapay' ) );
		$order->add_order_note( esc_html__( 'Customer has chosen LaqiraPay Wallet payment method, payment is pending.', 'laqirapay' ) );

		// Set customer information (if existing)

		if ( ! empty( $customer_data ) ) {
				$order_comment = $customer_data['order_comments'] ?? '';
				$order->set_customer_note( laqirapay_sanitize_textarea( $order_comment ) );
				$billing_address  = array();
				$shipping_address = array();
			foreach ( $customer_data as $key => $value ) {
				if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
					$value = (string) $value;
				}

				if ( ! is_scalar( $value ) ) {
					continue;
				}

						$sanitized_value = laqirapay_sanitize_simple_text( $value );

				if ( str_contains( $key, 'billing_' ) ) {
						$billing_address[ $key ] = $sanitized_value;
					if ( method_exists( $order, "set_{$key}" ) ) {
							$order->{"set_{$key}"}( $sanitized_value );
					}
				} elseif ( str_contains( $key, 'shipping_' ) ) {
						$shipping_address[ $key ] = $sanitized_value;
					if ( method_exists( $order, "set_{$key}" ) ) {
									$order->{"set_{$key}"}( $sanitized_value );
					}
				} elseif ( str_contains( $key, 'wc_order_attribution' ) ) {
						$order->update_meta_data( '_' . $key, $sanitized_value );
				}
			}
			if ( ! empty( $billing_address ) ) {
				$order->set_address( $billing_address, 'billing' );
				$order->set_address( $shipping_address, 'shipping' );
			}
		}

		$order->set_payment_method( 'WC_laqirapay' );
		$order->set_created_via( 'checkout' );
		$order->save();

		$current_currency    = get_woocommerce_currency();
		$saved_exchange_rate = get_option( 'laqirapay_exchange_rate_' . $current_currency, '' );

		if ( $order->get_id() ) {
				LaqiraLogger::log(
					200,
					'ajax',
					'get_order_success',
					array(
						'order_id'              => $order->get_id(),
						'exchange_rate'         => $saved_exchange_rate,
						'order_original_amount' => $order->get_total(),
						'order_amount'          => number_format( $order->get_total() / $saved_exchange_rate, 2 ),
						'order_status'          => $order_status,
						'last_order_id'         => $last_order_id,
					)
				);
				wp_send_json_success(
					array(
						'result'                => 'success',
						'order_id'              => $order->get_id(),
						'exchange_rate'         => $saved_exchange_rate,
						'order_original_amount' => $order->get_total(),
						'order_amount'          => number_format( $order->get_total() / $saved_exchange_rate, 2 ),
						'order_status'          => $order_status,
						'last_order_id'         => $last_order_id,

					)
				);
		} else {
				LaqiraLogger::log( 400, 'ajax', 'get_order_failed' );
				wp_send_json_error(
					array(
						'result' => 'failed',
						'error'  => esc_html__( 'create or update order not successful. please try again...', 'laqirapay' ),
					)
				);
		}
	} catch ( Exception $e ) {
			$error_message = laqirapay_sanitize_textarea( $e->getMessage() );
		if ( $error_message === '' ) {
				$error_message = esc_html__( 'An unexpected error occurred. Please try again later.', 'laqirapay' );
		}
			wp_send_json_error(
				array(
					'result' => 'failed',
					'error'  => $error_message,
				)
			);
	}
}

// ----------------------------------------------------------------------------------
/**
 * Laqira payment create transaction hash function.
 *
 * @return void, but send JSON response.
 */
add_action( 'wp_ajax_laqira_payment_create_tx_hash', 'laqira_payment_create_tx_hash' );
add_action( 'wp_ajax_nopriv_laqira_payment_create_tx_hash', 'laqira_payment_create_tx_hash' );
function laqira_payment_create_tx_hash(): void {
		// Check Ajax nonce.
		$nonce_raw = laqirapay_filter_input( INPUT_POST, 'security' );
		$nonce     = is_string( $nonce_raw ) ? sanitize_text_field( wp_unslash( $nonce_raw ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'laqira_nonce' ) ) {
			LaqiraLogger::log( 300, 'ajax', 'create_tx_hash_invalid_nonce' );
			wp_send_json_error(
				array(
					'result' => 'failed',
					'error'  => esc_html__( 'nonce Error!!!', 'laqirapay' ),
				)
			);
			return;
	}

		$jwt_cookie_raw = laqirapay_filter_input( INPUT_COOKIE, 'laqira_jwt' );
		$headers        = '';
	if ( is_string( $jwt_cookie_raw ) && $jwt_cookie_raw !== '' ) {
			$headers = sanitize_text_field( wp_unslash( $jwt_cookie_raw ) );
	}

	if ( $headers === '' || ( new JwtHelper() )->verify_header( $headers ) !== 'verified' ) {
			LaqiraLogger::log( 300, 'ajax', 'create_tx_hash_unauthorized' );
			wp_send_json_error(
				array(
					'result' => 'error',
					'error'  => esc_html__( 'Your request was not Authorized. Please refresh the checkout page again', 'laqirapay' ),
				)
			);
			return;
	}

		$laqira_data_raw = laqirapay_filter_input( INPUT_POST, 'laqiradata' );
	if ( ! is_string( $laqira_data_raw ) || $laqira_data_raw === '' ) {
			LaqiraLogger::log( 400, 'ajax', 'create_tx_hash_missing_payload' );
			wp_send_json_error(
				array(
					'result' => 'error',
					'error'  => esc_html__( 'Invalid transaction payload.', 'laqirapay' ),
				)
			);
			return;
	}

		$raw_payload = function_exists( 'wp_unslash' ) ? wp_unslash( $laqira_data_raw ) : stripslashes( $laqira_data_raw );
		$decoded     = json_decode( $raw_payload, true );

	if ( ! is_array( $decoded ) ) {
			LaqiraLogger::log( 400, 'ajax', 'create_tx_hash_invalid_json' );
			wp_send_json_error(
				array(
					'result' => 'error',
					'error'  => esc_html__( 'Invalid transaction payload.', 'laqirapay' ),
				)
			);
			return;
	}

	if ( function_exists( 'wc_clean' ) ) {
			$decoded = wc_clean( $decoded );
	} else {
			array_walk_recursive(
				$decoded,
				static function ( &$value ): void {
						$value = sanitize_text_field( (string) $value );
				}
			);
	}

		$required_fields = array(
			'orderID',
			'slippage',
			'tx_hash',
			'txStatus',
			'siteAdminAddressWallet',
			'userWallet',
			'reqHash',
			'price',
			'asset',
			'assetName',
			'assetAmount',
			'exchangeRate',
			'payment_type',
			'network_rpc',
		);

		$sanitized_result = laqirapay_sanitize_transaction_payload( $decoded, $required_fields );

		if ( isset( $sanitized_result['error'] ) ) {
				LaqiraLogger::log( 400, 'ajax', 'create_tx_hash_invalid_payload', array( 'error' => $sanitized_result['error'] ) );
				wp_send_json_error(
					array(
						'result' => 'error',
						'error'  => $sanitized_result['error'],
					)
				);
				return;
		}

		$payload = $sanitized_result['data'];

		$order_id                  = $payload['order_id'];
		$slippage                  = $payload['slippage'];
		$tx_hash                   = $payload['tx_hash'];
		$tx_status                 = $payload['tx_status'];
		$tx_log                    = $payload['tx_log'] ?? '';
		$site_admin_address_wallet = $payload['site_admin_address_wallet'];
		$user_wallet               = $payload['user_wallet'];
		$req_hash                  = $payload['req_hash'];
		$price                     = $payload['price'];
		$asset                     = $payload['asset'];
		$asset_name                = $payload['asset_name'];
		$asset_amount              = $payload['asset_amount'];
		$exchange_rate             = $payload['exchange_rate'];
		$payment_type              = $payload['payment_type'];
		$network_rpc               = $payload['network_rpc'];
		$network_explorer          = $payload['network_explorer'] ?? '';

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
				LaqiraLogger::log( 400, 'ajax', 'create_tx_hash_missing_order', array( 'order_id' => $order_id ) );
				wp_send_json_error(
					array(
						'result' => 'error',
						'error'  => esc_html__( 'Invalid order.', 'laqirapay' ),
					)
				);
				return;
		}

		$order->add_order_note(
			sprintf(
				'Order was Updated by %s method with Log %s',
				$payment_type,
				$tx_log
			)
		);
		$order->update_meta_data( 'tx_hash', $tx_hash );
		$order->update_meta_data( 'tx_status', $tx_status );
		$order->update_meta_data( 'AdminWalletAddress', $site_admin_address_wallet );
		$order->update_meta_data( 'CustomerWalletAddress', $user_wallet );
		$order->update_meta_data( 'reqHash', $req_hash );
		$order->update_meta_data( 'slippage', $slippage );
		$order->update_meta_data( 'TokenAddress', $asset );
		$order->update_meta_data( 'TokenName', $asset_name );
		$order->update_meta_data( 'TokenAmount', $asset_amount );
		$order->update_meta_data( 'exchange_rate', $exchange_rate );
		$order->update_meta_data( 'payment_type', $payment_type );
		$order->update_meta_data( 'network_rpc', $network_rpc );
		$order->update_meta_data( 'network_explorer', $network_explorer );
		$order->set_total( (float) $price );

		global $woocommerce;

		// Remove previous products from the order.
	foreach ( $order->get_items() as $item_id => $item ) {
			$order->remove_item( $item_id );
	}

		// Add cart products to the order.
	foreach ( $woocommerce->cart->get_cart() as $cart_item ) {
			$product  = $cart_item['data'];
			$quantity = $cart_item['quantity'];
			$order->add_product( $product, $quantity );
	}

		// Order total update.
		$order->calculate_totals();

		$order->save();

		LaqiraLogger::log(
			200,
			'ajax',
			'create_tx_hash_success',
			array(
				'order_id' => $order_id,
				'tx_hash'  => $tx_hash,
			)
		);
		wp_send_json_success( array( 'result' => 'success' ) );
}

/**
 * Laqira payment confirmation function.
 *
 * @return void, send $order->get_checkout_order_received_url with wp_send_json_success to redirect by JS.
 */
add_action( 'wp_ajax_laqira_payment_confirmation', 'laqira_payment_confirmation' );
add_action( 'wp_ajax_nopriv_laqira_payment_confirmation', 'laqira_payment_confirmation' );
function laqira_payment_confirmation() {
		$nonce_raw = laqirapay_filter_input( INPUT_POST, 'security' );
		$nonce     = is_string( $nonce_raw ) ? sanitize_text_field( wp_unslash( $nonce_raw ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'laqira_nonce' ) ) {
			LaqiraLogger::log( 300, 'ajax', 'payment_confirmation_invalid_nonce' );
			wp_send_json_error(
				array(
					'result' => 'failed',
					'error'  => esc_html__( 'nonce Error!!!', 'laqirapay' ),
				)
			);
			return;
	}

		$jwt_cookie_raw = laqirapay_filter_input( INPUT_COOKIE, 'laqira_jwt' );
		$headers        = '';
	if ( is_string( $jwt_cookie_raw ) && $jwt_cookie_raw !== '' ) {
			$headers = sanitize_text_field( wp_unslash( $jwt_cookie_raw ) );
	}

	if ( $headers === '' || ( new JwtHelper() )->verify_header( $headers ) !== 'verified' ) {
			LaqiraLogger::log( 300, 'ajax', 'payment_confirmation_unauthorized' );
			wp_send_json_error(
				array(
					'result' => 'error',
					'error'  => esc_html__( 'Your request was not Authorized. Please refresh the checkout page again', 'laqirapay' ),
				)
			);
			return;
	}

		global $woocommerce;
		$current_currency    = get_woocommerce_currency();
		$saved_exchange_rate = get_option( 'laqirapay_exchange_rate_' . $current_currency, '' );
		$saved_exchange_rate = laqirapay_sanitize_decimal_string( $saved_exchange_rate, false ) ?? '1';

		$laqira_data_raw = laqirapay_filter_input( INPUT_POST, 'laqiradata' );
	if ( ! is_string( $laqira_data_raw ) || $laqira_data_raw === '' ) {
			LaqiraLogger::log( 400, 'ajax', 'payment_confirmation_missing_payload' );
			wp_send_json_error(
				array(
					'result' => 'error',
					'error'  => esc_html__( 'Invalid transaction payload.', 'laqirapay' ),
				)
			);
			return;
	}

		$raw_payload = function_exists( 'wp_unslash' ) ? wp_unslash( $laqira_data_raw ) : stripslashes( $laqira_data_raw );
		$decoded     = json_decode( $raw_payload, true );

	if ( ! is_array( $decoded ) ) {
			LaqiraLogger::log( 400, 'ajax', 'payment_confirmation_invalid_json' );
			wp_send_json_error(
				array(
					'result' => 'error',
					'error'  => esc_html__( 'Invalid transaction payload.', 'laqirapay' ),
				)
			);
			return;
	}

	if ( function_exists( 'wc_clean' ) ) {
			$decoded = wc_clean( $decoded );
	} else {
			array_walk_recursive(
				$decoded,
				static function ( &$value ): void {
						$value = sanitize_text_field( (string) $value );
				}
			);
	}

		$required_fields = array(
			'orderID',
			'slippage',
			'tx_hash',
			'siteAdminAddressWallet',
			'userWallet',
			'reqHash',
			'price',
			'asset',
			'assetName',
			'assetAmount',
			'payment_type',
			'network_rpc',
		);

		$sanitized_result = laqirapay_sanitize_transaction_payload( $decoded, $required_fields );

		if ( isset( $sanitized_result['error'] ) ) {
				LaqiraLogger::log( 400, 'ajax', 'payment_confirmation_invalid_payload', array( 'error' => $sanitized_result['error'] ) );
				wp_send_json_error(
					array(
						'result' => 'error',
						'error'  => $sanitized_result['error'],
					)
				);
				return;
		}

		$payload                   = $sanitized_result['data'];
		$order_id                  = $payload['order_id'];
		$slippage                  = $payload['slippage'];
		$tx_hash                   = $payload['tx_hash'];
		$site_admin_address_wallet = $payload['site_admin_address_wallet'];
		$user_wallet               = $payload['user_wallet'];
		$req_hash                  = $payload['req_hash'];
		$price                     = $payload['price'];
		$asset                     = $payload['asset'];
		$asset_name                = $payload['asset_name'];
		$asset_amount              = $payload['asset_amount'];
		$payment_type              = $payload['payment_type'];
		$network_rpc               = $payload['network_rpc'];

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
				LaqiraLogger::log( 400, 'ajax', 'payment_confirmation_missing_order', array( 'order_id' => $order_id ) );
				wp_send_json_error(
					array(
						'result' => 'error',
						'error'  => esc_html__( 'Invalid order.', 'laqirapay' ),
					)
				);
				return;
		}

		$stored_tx_hash_raw  = $order->get_meta( 'tx_hash' );
		$stored_req_hash_raw = $order->get_meta( 'reqHash' );

		$old_tx_hash  = $stored_tx_hash_raw;
		$old_req_hash = $stored_req_hash_raw;

		if ( is_string( $stored_tx_hash_raw ) ) {
				$normalized_stored_tx_hash = laqirapay_sanitize_tx_hash( $stored_tx_hash_raw );

			if ( $normalized_stored_tx_hash !== null ) {
					$old_tx_hash = $normalized_stored_tx_hash;
			}
		}

		if ( is_string( $stored_req_hash_raw ) ) {
				$normalized_stored_req_hash = laqirapay_sanitize_req_hash( $stored_req_hash_raw );

			if ( $normalized_stored_req_hash !== null ) {
					$old_req_hash = $normalized_stored_req_hash;
			}
		}

		if ( $old_tx_hash !== $tx_hash || $old_req_hash !== $req_hash ) {
				LaqiraLogger::log(
					400,
					'ajax',
					'payment_confirmation_mismatched_meta',
					array(
						'order_id'          => $order_id,
						'stored_tx_hash'    => $stored_tx_hash_raw,
						'stored_req_hash'   => $stored_req_hash_raw,
						'incoming_tx_hash'  => $tx_hash,
						'incoming_req_hash' => $req_hash,
					)
				);
				wp_send_json_error(
					array(
						'result' => 'error',
						'error'  => esc_html__( 'Submitted transaction data does not match the order.', 'laqirapay' ),
					)
				);
				return;
		}

		if ( $payment_type === 'Direct' ) {
				$tx_results = ( new BlockchainService() )->getTransactionInfo(
					$tx_hash,
					$network_rpc,
					function ( $transaction ) {
						return ( new TransactionDecoder() )->decodeTransactionDirect( $transaction->{'input'} );
					}
				);
		} else {
				$tx_results = ( new BlockchainService() )->getTransactionInfo(
					$tx_hash,
					$network_rpc,
					function ( $transaction ) {
						return ( new TransactionDecoder() )->decodeTransactionInApp( $transaction->{'input'} );
					}
				);
		}

		$tx_error_message = laqirapay_get_sanitized_wp_error_message(
			$tx_results,
			esc_html__( 'Unable to retrieve blockchain transaction details. Please try again later.', 'laqirapay' )
		);

	if ( $tx_error_message !== null ) {
			LaqiraLogger::log(
				400,
				'ajax',
				'payment_confirmation_blockchain_unavailable',
				array(
					'order_id' => $order_id,
					'tx_hash'  => $tx_hash,
					'error'    => $tx_error_message,
				)
			);
			wp_send_json_error(
				array(
					'result' => 'error',
					'error'  => $tx_error_message,
				)
			);
			return;
	}

	if (
				empty( $tx_results ) ||
				floatval( $slippage ) !== floatval( $tx_results['_slippage'] / 100 ) ||
				strtolower( $site_admin_address_wallet ) !== strtolower( $tx_results['_provider'] ) ||
				strtolower( $asset ) !== strtolower( $tx_results['_asset'] ) ||
				floatval( $price ) !== floatval( $tx_results['_price'] / 100 ) ||
				strtolower( '0x' . $req_hash ) !== strtolower( $tx_results['_reqHash'] )
		) {
			$order->update_status( 'wc-failed', '' );
			$order->add_order_note( esc_html__( 'Order not verified by blockchain.', 'laqirapay' ) );
			$order->save();
			LaqiraLogger::log(
				400,
				'ajax',
				'payment_confirmation_blockchain_failed',
				array(
					'order_id' => $order_id,
					'tx_hash'  => $tx_hash,
				)
			);
			wp_send_json_error(
				array(
					'result' => 'error',
					'error'  => esc_html__( 'Transaction verification failed.', 'laqirapay' ),
				)
			);
			return;
	}

		$order->update_meta_data( 'tx_hash', $tx_hash );
		$order->update_meta_data( 'tx_status', 'success' );
		$order->update_meta_data( 'AdminWalletAddress', $site_admin_address_wallet );
		$order->update_meta_data( 'CustomerWalletAddress', $user_wallet );
		$order->update_meta_data( 'reqHash', $req_hash );
		$order->update_meta_data( 'slippage', $slippage );
		$order->update_meta_data( 'TokenAddress', $asset );
		$order->update_meta_data( 'TokenName', $asset_name );
		$order->update_meta_data( 'TokenAmount', $asset_amount );
		$order->update_meta_data( 'payment_type', $payment_type );
		$order->update_meta_data( 'exchange_rate', $saved_exchange_rate );
		$order->update_meta_data( 'network_rpc', $network_rpc );
		$order->set_total( (float) $price );

		// Remove previous products from the order.
	foreach ( $order->get_items() as $item_id => $item ) {
			$order->remove_item( $item_id );
	}

		// Add cart products to the order.
	foreach ( $woocommerce->cart->get_cart() as $cart_item ) {
			$product  = $cart_item['data'];
			$quantity = $cart_item['quantity'];
			$order->add_product( $product, $quantity );
	}

		// Order total update.
		$order->calculate_totals();
		$payment_gateways = $woocommerce->payment_gateways->payment_gateways();
		$order->set_payment_method( $payment_gateways['WC_laqirapay'] );
		$order_status = get_option( 'laqirapay_order_recovery_status' );
		$order->update_status( $order_status, '' );
		$order->add_order_note( 'Order Update by ' . $payment_type . ' method with TxHash ' . $tx_hash );
		$order->save();

		global $wpdb;
		$table_name_laqira_transactions = esc_sql( $wpdb->prefix . 'laqirapay_transactions' );
		$order_id_int                   = (int) $order->get_id();

		$laqira_transactions = array(
			'wc_total_price' => $price,
			'wc_currency'    => get_woocommerce_currency(),
			'exchange_rate'  => $saved_exchange_rate,
			'wc_order_id'    => $order_id_int,
			'tx_hash'        => $tx_hash,
			'token_address'  => $asset,
			'token_name'     => $asset_name,
			'token_amount'   => $asset_amount,
			'req_hash'       => $req_hash,
			'tx_from'        => $user_wallet,
			'tx_to'          => $site_admin_address_wallet,
		);

		$existing_row_count = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(1) FROM %s WHERE wc_order_id = %d', 
				$table_name_laqira_transactions,
				$order_id_int 
			)
		);

	if ( (int) $existing_row_count > 0 ) {
			$wpdb->update(
				$table_name_laqira_transactions,
				$laqira_transactions,
				array( 'wc_order_id' => $order_id_int ),
				null,
				array( '%d' )
			);
	} else {
			$wpdb->insert( $table_name_laqira_transactions, $laqira_transactions );
	}

		LaqiraLogger::log(
			200,
			'ajax',
			'payment_confirmation_success',
			array(
				'order_id' => $order_id_int,
				'tx_hash'  => $tx_hash,
			)
		);
		wp_send_json_success(
			array(
				'result'   => 'success',
				'data'     => $tx_results,
				'redirect' => esc_url_raw( $order->get_checkout_order_received_url() ),
			)
		);
}


/**
 * This function is used to create Laqira payment data.
 *
 * @return void This function does not return anything, but sends data to JavaScript as JSON.
 */
add_action( 'wp_ajax_laqira_payment_data', 'laqira_payment_data' );
add_action( 'wp_ajax_nopriv_laqira_payment_data', 'laqira_payment_data' );
function laqira_payment_data() {
		// Check Ajax nonce.
		$nonce_raw = laqirapay_filter_input( INPUT_POST, 'security' );
		$nonce     = is_string( $nonce_raw ) ? sanitize_text_field( wp_unslash( $nonce_raw ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'laqira_nonce' ) ) {
			LaqiraLogger::log( 300, 'ajax', 'payment_data_invalid_nonce' );
			wp_send_json_error(
				array(
					'result' => 'failed',
					'error'  => esc_html__( 'nonce Error!!!', 'laqirapay' ),
				)
			);
			return;
	}

		$jwt_cookie_raw = laqirapay_filter_input( INPUT_COOKIE, 'laqira_jwt' );
		$headers        = '';
	if ( is_string( $jwt_cookie_raw ) && $jwt_cookie_raw !== '' ) {
			$headers = sanitize_text_field( wp_unslash( $jwt_cookie_raw ) );
	}

	if ( $headers === '' || ( new JwtHelper() )->verify_header( $headers ) !== 'verified' ) {
			LaqiraLogger::log( 300, 'ajax', 'payment_data_unauthorized' );
			wp_send_json_error(
				array(
					'result' => 'error',
					'error'  => esc_html__( 'Your request was not Authorized. Please refresh the checkout page again', 'laqirapay' ),
				)
			);
			return;
	}

		$order_id_raw = laqirapay_filter_input( INPUT_POST, 'orderID', FILTER_SANITIZE_NUMBER_INT );
		$order_id     = ( $order_id_raw !== null && $order_id_raw !== false ) ? (int) $order_id_raw : 0;

	if ( $order_id <= 0 ) {
			LaqiraLogger::log( 300, 'ajax', 'payment_data_invalid_order_id' );
			wp_send_json_error(
				array(
					'result' => 'error',
					'error'  => esc_html__( 'Invalid order ID provided.', 'laqirapay' ),
				)
			);
			return;
	}

		global $woocommerce;
			// $cart_total = $woocommerce->cart->get_total('edit');

			$key_encode          = get_option( 'laqirapay_api_key' );
			$current_currency    = get_woocommerce_currency();
			$order               = wc_get_order( intval( $order_id ) );
			$cart_total          = ( new WooCommerceHelper() )->getTotal();
			$saved_exchange_rate = get_option( 'laqirapay_exchange_rate_' . $current_currency, '' );
	if ( ! $saved_exchange_rate ) {
		$saved_exchange_rate = 1;
	}
			$final_amount           = $cart_total / $saved_exchange_rate;
			$final_amount_formatted = number_format( $final_amount, 2 );

			// Get Provider Address From API-KEY.
						$provider_address = str_replace( ' ', '', strtolower( ( new BlockchainService() )->getProviderLocal() ) );

			// Get Provider Address Value.
			$provider_address_value = str_replace( '0x', '', $provider_address );

			// Get Main Domain of this site.
			$main_domain = str_replace( ' ', '', strtolower( get_site_url() ) );

			// Concatenate ProviderAddress With Domain.
			$combined_string = $provider_address . $main_domain;

			// Calculate MD5 for Concatenated value.
			$md5_hash = md5( $combined_string );

			// Get first 12 characters of MD5 value (12 char of this + 20 Char for OrderID => 32 char ).
			$short_md5_hash = substr( $md5_hash, 0, 12 );

			// Create 20 character String with orderID (bigint(20)).
			$padded_hex_value_order_id = str_pad( ( $order_id ), 20, '0', STR_PAD_LEFT );

			$order = $padded_hex_value_order_id;

			// Get Order Total Price.

			$price = $final_amount_formatted;
			// Convert Order total Price to String with its decimal.
			$string_price = str_replace( '.', '', number_format( $price, 2, '.', '' ) );

			// Convert Price to Hex and pad it to 64 characters.
			$padded_string_price = str_pad( dechex( $string_price ), 64, '0', STR_PAD_LEFT );

			// Concatenate values to create first parameter for final Hash.
			$concat_md5hash_order = $short_md5_hash . $order;

			// Convert last Parameter of final hash to byte.
			$order_id_32 = bin2hex( $concat_md5hash_order );

			// Create final hash string.
			$final_concat = $provider_address_value . $padded_string_price . $order_id_32;

			// Hash Final value to send.
			$final_hash = Keccak::hash( hex2bin( $final_concat ), 256 );

						// Send key to decode values in JS.
		LaqiraLogger::log(
			200,
			'ajax',
			'payment_data_success',
			array(
				'order_id'   => $order_id,
				'final_hash' => $final_hash,
			)
		);
		wp_send_json_success(
			array(
				'result'              => 'success',
				'final_hash'          => $final_hash,
				'order_string_price'  => $string_price,
				'site_admin_provider' => $provider_address,
				'order_id_32'         => $order_id_32,
				'order_id'            => $order_id,
			)
		);
}

add_action( 'wp_ajax_laqirapay_view_confirmation_tx_hash', 'laqirapay_view_confirmation_tx_hash' );
add_action( 'wp_ajax_nopriv_laqirapay_view_confirmation_tx_hash', 'laqirapay_view_confirmation_tx_hash' );

/**
 * The function `laqirapay_view_confirmation_tx_hash` processes and displays transaction details and
 * order information based on input values.
 *
 * @return This function is responsible for viewing and confirming a transaction hash. It takes an
 * input value from a form submission, retrieves transaction information using different functions, and
 * then checks and displays various details related to the transaction and associated order.
 */
function laqirapay_view_confirmation_tx_hash() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'laqirapay_view_confirmation_tx_hash' ) ) {
					LaqiraLogger::log( 300, 'ajax', 'view_confirmation_tx_hash_invalid_nonce' );
					wp_send_json_error(
						array(
							'result' => 'failed',
							'error'  => esc_html__( 'nonce Error!!!', 'laqirapay' ),
						)
					);
					return;
	}
	if ( isset( $_POST['input_value'] ) ) {
			$allowed_tags         = laqirapay_get_order_confirmation_allowed_tags();
			$confirmation_payload = null;
			$input_value          = sanitize_text_field( $_POST['input_value'] );
			$tx_hash_to_find      = $input_value;
			$order_id             = find_order_by_tx_hash( $tx_hash_to_find );
			ob_start();
		if ( $order_id ) {
				$order       = wc_get_order( $order_id );
				$network_rpc = $order->get_meta( 'network_rpc' );

				$tx_results_direct = ( new BlockchainService() )->getTransactionInfo(
					$input_value,
					$network_rpc,
					function ( $transaction ) {
						return ( new TransactionDecoder() )->decodeTransactionDirect( $transaction->{'input'} );
					}
				);

				$tx_results_inapp = ( new BlockchainService() )->getTransactionInfo(
					$input_value,
					$network_rpc,
					function ( $transaction ) {
						return ( new TransactionDecoder() )->decodeTransactionInApp( $transaction->{'input'} );
					}
				);

				$tx_results_receipt = ( new BlockchainService() )->getTransactionRec(
					$input_value,
					$network_rpc,
					function ( $transaction ) {
						return ( $transaction );
					}
				);

			if ( is_wp_error( $tx_results_direct ) ) {
				$tx_results_direct = null;
			}

			if ( is_wp_error( $tx_results_inapp ) ) {
				$tx_results_inapp = null;
			}

			if ( is_wp_error( $tx_results_receipt ) ) {
				$tx_results_receipt = array();
			} else {
				$tx_results_receipt = (array) $tx_results_receipt;
			}

				$slippage_form_tx                = 0.0;
				$provider_address_from_tx        = '';
				$asset_address_from_tx           = '';
				$price_from_tx                   = 0.0;
				$req_hash_from_tx                = '';
				$user_wallet_address_form_tx     = '';
				$main_laqirapay_contract_from_tx = '';
				$transaction_status_from_tx      = '';

			if ( isset( $tx_results_direct ) && is_array( $tx_results_direct ) && count( $tx_results_direct ) > 0 ) {
				$slippage_form_tx         = floatval( $tx_results_direct['_slippage'] / 100 );
				$provider_address_from_tx = $tx_results_direct['_provider'];
				$asset_address_from_tx    = $tx_results_direct['_asset'];
				$price_from_tx            = floatval( $tx_results_direct['_price'] / 100 );
				$req_hash_from_tx         = $tx_results_direct['_reqHash'];
			}

			if ( isset( $tx_results_inapp ) && is_array( $tx_results_inapp ) && count( $tx_results_inapp ) > 0 ) {
				$slippage_form_tx         = floatval( $tx_results_inapp['_slippage'] / 100 );
				$provider_address_from_tx = $tx_results_inapp['_provider'];
				$asset_address_from_tx    = $tx_results_inapp['_asset'];
				$price_from_tx            = floatval( $tx_results_inapp['_price'] / 100 );
				$req_hash_from_tx         = $tx_results_inapp['_reqHash'];
			}

			if ( isset( $tx_results_receipt ) && is_array( $tx_results_receipt ) && count( $tx_results_receipt ) > 0 ) {
				$user_wallet_address_form_tx     = $tx_results_receipt['from'];
				$main_laqirapay_contract_from_tx = $tx_results_receipt['to'];
				$transaction_status_from_tx      = $tx_results_receipt['status'];
			}

				$original_provider = ( new BlockchainService() )->getProviderLocal();
			echo '<div id="lqr-recover-order-result" class="info-box">';
			if ( strtolower( $original_provider ) == strtolower( $provider_address_from_tx ) ) {
						echo wp_kses_post( '<p><span class="dashicons dashicons-yes-alt" style="color:green;"></span>Provider Address confirmed</p>' );

				if ( laqirapay_is_successful_transaction_status( $transaction_status_from_tx ) ) {
					echo wp_kses_post( '<p><span class="dashicons dashicons-yes-alt" style="color:green;"></span>The transaction status is compelete on blockchain</p>' );
					if ( $order_id ) {
										echo wp_kses_post( '<p><span class="dashicons dashicons-yes-alt" style="color:green;"></span>An order found</p>' );
						// $order                 = wc_get_order( intval( $order_id ) );
						$order_recovery_status = get_option( 'laqirapay_order_recovery_status' );
						$order_status          = 'wc-' . $order->get_status();
						if ( ( $order_status != 'wc-completed' ) && ( $order_status != $order_recovery_status ) ) {

							$order_data_provider_address    = $order->get_meta( 'AdminWalletAddress' );
							$order_data_user_wallet_address = $order->get_meta( 'CustomerWalletAddress' );

							$order_data_slippage = $order->get_meta( 'slippage' );
							$order_data_req_hash = $order->get_meta( 'reqHash' );
							$order_data_asset    = $order->get_meta( 'TokenAddress' );

							if (
							( strtolower( $order_data_provider_address ) === strtolower( $original_provider ) )
							&& ( floatval( $order_data_slippage ) === floatval( $slippage_form_tx ) )
							&& ( strtolower( $order_data_asset ) === strtolower( $asset_address_from_tx ) )
							&& ( strtolower( '0x' . $order_data_req_hash ) === strtolower( $req_hash_from_tx ) )
							&& ( strtolower( $order_data_user_wallet_address ) === strtolower( $user_wallet_address_form_tx ) )
							) {

															$order_registration_message = sprintf(
																/* translators: %s: WooCommerce order ID. */
																__( 'Order #%s has been registered with this transaction hash. Order and transaction details are below.', 'laqirapay' ),
																laqirapay_sanitize_simple_text( $order_id )
															);
															printf(
																'<p><span class="dashicons dashicons-yes-alt" style="color:green;"></span>%s</p>',
																esc_html( $order_registration_message )
															);

															echo wp_kses_post( '<hr><h4>Order Details:</h4>' );
															$output  = '<table class="laqirapay-table">';
															$output .= '<tr><th>Title </th><th>Value</th></tr>';

															$output .= '<tr>';
															$output .= '<td><strong>Order Status</strong></td>';
															$output .= '<td>' . esc_html( $order->get_status() ) . '</td>';
															$output .= '</tr>';

															$output .= '<tr>';
															$output .= '<td><strong>Order Create Date</strong></td>';
															$output .= '<td>' . esc_html( format_date( $order->get_date_created() ) ) . '</td>';
															$output .= '</tr>';

															$output .= '<tr>';
															$output .= '<td><strong>Order Modified Date</strong></td>';
															$output .= '<td>' . esc_html( format_date( $order->get_date_modified() ) ) . '</td>';
															$output .= '</tr>';

															$output         .= '<tr>';
															$output         .= '<td><strong>Order Total Amount</strong></td>';
															$formatted_total = sprintf( '%s %s', $order->get_total(), $order->get_currency() );
															$output         .= '<td>' . esc_html( $formatted_total ) . '</td>';
															$output         .= '</tr>';

															$output .= '</table>';

																echo wp_kses_post( $output );

								if ( current_user_can( 'administrator' ) || get_current_user_id() == $order->get_user_id() ) {
																		echo wp_kses_post( '<br><h4>Order Items:</h4>' );
									$order_items_output  = '<table class="laqirapay-table">';
									$order_items_output .= '<tr><th>Product ID </th><th>Name</th><th>Quantity</th><th>SubTotal</th></tr>';
									foreach ( $order->get_items() as $item_id => $item ) {
										$product             = $item->get_product();
										$order_items_output .= '<tr>';
																			$order_items_output .= '<td>' . esc_html( $item->get_product_id() ) . '</td>';
																			$order_items_output .= '<td>' . esc_html( $product->get_name() ) . '</td>';
																			$order_items_output .= '<td>' . esc_html( $item->get_quantity() ) . '</td>';
																			$order_items_output .= '<td>' . wp_kses_post( wc_price( $item->get_subtotal() ) ) . '</td>';
																			$order_items_output .= '</tr>';
									}
																	$order_items_output .= '</table>';
																		echo wp_kses_post( $order_items_output );
								}

															echo wp_kses_post( '<hr><h4>Transaction Details:</h4>' );
															$output  = '<table class="laqirapay-table">';
															$output .= '<tr><th>Title </th><th>Value</th></tr>';

															$output .= '<tr>';
															$output .= '<td><strong>Transaction Hash</strong></td>';
															$output .= '<td>' . esc_html( $input_value ) . '</td>';
															$output .= '</tr>';

															$output .= '<tr>';
															$output .= '<td><strong>From</strong></td>';
															$output .= '<td>' . esc_html( $user_wallet_address_form_tx ) . '</td>';
															$output .= '</tr>';

															$output .= '<tr>';
															$output .= '<td><strong>To</strong></td>';
															$output .= '<td>' . esc_html( $main_laqirapay_contract_from_tx ) . '</td>';
															$output .= '</tr>';

															$output .= '<tr>';
															$output .= '<td><strong>Provider</strong></td>';
															$output .= '<td>' . esc_html( $provider_address_from_tx ) . '</td>';
															$output .= '</tr>';

															$output .= '<tr>';
															$output .= '<td><strong>Request Hash</strong></td>';
															$output .= '<td>' . esc_html( $req_hash_from_tx ) . '</td>';
															$output .= '</tr>';

															$output         .= '<tr>';
															$output         .= '<td><strong>Order Amount</strong></td>';
															$formatted_price = sprintf( '%s $', number_format( (float) $price_from_tx, 2 ) );
															$output         .= '<td>' . esc_html( $formatted_price ) . '</td>';
															$output         .= '</tr>';

															$output .= '</table>';

															echo wp_kses_post( $output . '</div>' );

																															$confirmation_payload = do_compelete_order( $order_id );
							} else {
														echo wp_kses_post( '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>Your data not confirmed</p>' );
							}
						} else {
											echo wp_kses_post( '<p><span class="dashicons dashicons-info" style="color:orange;"></span>The order is already stable and does not require further confirmation</p>' );
						}
					} else {
									echo wp_kses_post( '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>No order found with this Transaction hash</p>' );
					}
				} else {
								echo wp_kses_post( '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>The transaction status is not Completed on Blockchain</p>' );
				}
			} else {
				$provider_debug = sprintf(
					'%s#%s',
					strtolower( (string) $original_provider ),
					strtolower( (string) $provider_address_from_tx )
				);
				$message        = sprintf(
						/* translators: %s: provider address debug information. */
					__( 'Provider Address not Confirmed %s', 'laqirapay' ),
					$provider_debug
				);
				printf(
					'<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>%s</p>',
					esc_html( $message )
				);
			}
											echo '</div>';

											LaqiraLogger::log(
												200,
												'ajax',
												'view_confirmation_tx_hash_rendered',
												array(
													'order_id' => $order_id,
													'tx_hash'  => $input_value,
												)
											);
		}
			$html_output = ob_get_clean();

			$response = array(
				'result' => 'success',
				'html'   => wp_kses( $html_output, $allowed_tags ),
			);

			if ( $confirmation_payload ) {
					$response['confirmation'] = $confirmation_payload;
			}

			wp_send_json_success( $response );
	} else {
			wp_send_json_error(
				array(
					'result'  => 'failed',
					'message' => esc_html__( 'Transaction hash is required.', 'laqirapay' ),
				)
			);
	}
}


add_action( 'wp_ajax_laqirapay_view_confirmation_tx_hash_admin', 'laqirapay_view_confirmation_tx_hash_admin' );
function laqirapay_view_confirmation_tx_hash_admin() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'laqirapay_view_confirmation_tx_hash_admin' ) ) {
					LaqiraLogger::log( 300, 'ajax', 'view_confirmation_tx_hash_admin_invalid_nonce' );
					wp_send_json_error(
						array(
							'result' => 'failed',
							'error'  => esc_html__( 'nonce Error!!!', 'laqirapay' ),
						)
					);
					return;
	}
	if ( isset( $_POST['input_value'] ) ) {
			$allowed_tags         = laqirapay_get_order_confirmation_allowed_tags();
			$confirmation_payload = null;
			$input_value          = sanitize_text_field( $_POST['input_value'] );
			$order_id             = intval( $_POST['order_id'] );
			$order                = wc_get_order( intval( $order_id ) );
			$network_rpc          = $order->get_meta( 'network_rpc' );
			ob_start();

		$tx_results_direct = ( new BlockchainService() )->getTransactionInfo(
			$input_value,
			$network_rpc,
			function ( $transaction ) {
				return ( new TransactionDecoder() )->decodeTransactionDirect( $transaction->{'input'} );
			}
		);

		$tx_results_inapp = ( new BlockchainService() )->getTransactionInfo(
			$input_value,
			$network_rpc,
			function ( $transaction ) {
				return ( new TransactionDecoder() )->decodeTransactionInApp( $transaction->{'input'} );
			}
		);

		$tx_results_receipt = ( new BlockchainService() )->getTransactionRec(
			$input_value,
			$network_rpc,
			function ( $transaction ) {
				return ( $transaction );
			}
		);

		if ( is_wp_error( $tx_results_direct ) ) {
			$tx_results_direct = null;
		}

		if ( is_wp_error( $tx_results_inapp ) ) {
			$tx_results_inapp = null;
		}

		if ( is_wp_error( $tx_results_receipt ) ) {
			$tx_results_receipt = array();
		} else {
			$tx_results_receipt = (array) $tx_results_receipt;
		}

		$slippage_form_tx                = 0.0;
		$provider_address_from_tx        = '';
		$asset_address_from_tx           = '';
		$price_from_tx                   = 0.0;
		$req_hash_from_tx                = '';
		$user_wallet_address_form_tx     = '';
		$main_laqirapay_contract_from_tx = '';
		$transaction_status_from_tx      = '';

		if ( isset( $tx_results_direct ) && is_array( $tx_results_direct ) && count( $tx_results_direct ) > 0 ) {
			$slippage_form_tx         = floatval( $tx_results_direct['_slippage'] / 100 );
			$provider_address_from_tx = $tx_results_direct['_provider'];
			$asset_address_from_tx    = $tx_results_direct['_asset'];
			$price_from_tx            = floatval( $tx_results_direct['_price'] / 100 );
			$req_hash_from_tx         = $tx_results_direct['_reqHash'];
		}

		if ( isset( $tx_results_inapp ) && is_array( $tx_results_inapp ) && count( $tx_results_inapp ) > 0 ) {
			$slippage_form_tx         = floatval( $tx_results_inapp['_slippage'] / 100 );
			$provider_address_from_tx = $tx_results_inapp['_provider'];
			$asset_address_from_tx    = $tx_results_inapp['_asset'];
			$price_from_tx            = floatval( $tx_results_inapp['_price'] / 100 );
			$req_hash_from_tx         = $tx_results_inapp['_reqHash'];
		}

		if ( isset( $tx_results_receipt ) && is_array( $tx_results_receipt ) && count( $tx_results_receipt ) > 0 ) {
			$user_wallet_address_form_tx     = $tx_results_receipt['from'];
			$main_laqirapay_contract_from_tx = $tx_results_receipt['to'];
			$transaction_status_from_tx      = $tx_results_receipt['status'];
		}

		$original_provider = ( new BlockchainService() )->getProviderLocal();
		echo '<div id="lqr-recover-order-result" class="info-box">';
		if ( strtolower( $original_provider ) == strtolower( $provider_address_from_tx ) ) {
			echo wp_kses_post( '<p><span class="dashicons dashicons-yes-alt" style="color:green;"></span>Provider Address confirmed</p>' );

			if ( laqirapay_is_successful_transaction_status( $transaction_status_from_tx ) ) {
				echo wp_kses_post( '<p><span class="dashicons dashicons-yes-alt" style="color:green;"></span>The transaction status is compelete on blockchain</p>' );
				if ( $order_id ) {
							echo wp_kses_post( '<p><span class="dashicons dashicons-yes-alt" style="color:green;"></span>An order found</p>' );

					$order_recovery_status = get_option( 'laqirapay_order_recovery_status' );
					$order_status          = 'wc-' . $order->get_status();
					if ( ( $order_status != 'wc-completed' ) && ( $order_status != $order_recovery_status ) ) {

						$order_data_provider_address    = $order->get_meta( 'AdminWalletAddress' );
						$order_data_user_wallet_address = $order->get_meta( 'CustomerWalletAddress' );

						$order_data_slippage = $order->get_meta( 'slippage' );
						$order_data_req_hash = $order->get_meta( 'reqHash' );
						$order_data_asset    = $order->get_meta( 'TokenAddress' );

						if (
						( strtolower( $order_data_provider_address ) === strtolower( $original_provider ) )
						&& ( floatval( $order_data_slippage ) === floatval( $slippage_form_tx ) )
						&& ( strtolower( $order_data_asset ) === strtolower( $asset_address_from_tx ) )
						&& ( strtolower( '0x' . $order_data_req_hash ) === strtolower( $req_hash_from_tx ) )
						&& ( strtolower( $order_data_user_wallet_address ) === strtolower( $user_wallet_address_form_tx ) )
						) {

												$order_registration_message = sprintf(
													/* translators: %s: WooCommerce order ID. */
													__( 'Order #%s has been registered with this transaction hash. Order and transaction details are below.', 'laqirapay' ),
													laqirapay_sanitize_simple_text( $order_id )
												);
												printf(
													'<p><span class="dashicons dashicons-yes-alt" style="color:green;"></span>%s</p>',
													esc_html( $order_registration_message )
												);

													echo wp_kses_post( '<hr><h4>Order Details:</h4>' );
												$output  = '<table class="laqirapay-table">';
												$output .= '<tr><th>Title </th><th>Value</th></tr>';

												$output .= '<tr>';
												$output .= '<td><strong>Order Status</strong></td>';
												$output .= '<td>' . esc_html( $order->get_status() ) . '</td>';
												$output .= '</tr>';

												$output .= '<tr>';
												$output .= '<td><strong>Order Create Date</strong></td>';
												$output .= '<td>' . esc_html( format_date( $order->get_date_created() ) ) . '</td>';
												$output .= '</tr>';

												$output .= '<tr>';
												$output .= '<td><strong>Order Modified Date</strong></td>';
												$output .= '<td>' . esc_html( format_date( $order->get_date_modified() ) ) . '</td>';
												$output .= '</tr>';

												$output         .= '<tr>';
												$output         .= '<td><strong>Order Total Amount</strong></td>';
												$formatted_total = sprintf( '%s %s', $order->get_total(), $order->get_currency() );
												$output         .= '<td>' . esc_html( $formatted_total ) . '</td>';
												$output         .= '</tr>';

												$output .= '</table>';

													echo wp_kses_post( $output );

							if ( current_user_can( 'administrator' ) || get_current_user_id() == $order->get_user_id() ) {
															echo wp_kses_post( '<br><h4>Order Items:</h4>' );
								$order_items_output  = '<table class="laqirapay-table">';
								$order_items_output .= '<tr><th>Product ID </th><th>Name</th><th>Quantity</th><th>SubTotal</th></tr>';
								foreach ( $order->get_items() as $item_id => $item ) {
									$product             = $item->get_product();
									$order_items_output .= '<tr>';
																$order_items_output .= '<td>' . esc_html( $item->get_product_id() ) . '</td>';
																$order_items_output .= '<td>' . esc_html( $product->get_name() ) . '</td>';
																$order_items_output .= '<td>' . esc_html( $item->get_quantity() ) . '</td>';
																$order_items_output .= '<td>' . wp_kses_post( wc_price( $item->get_subtotal() ) ) . '</td>';
																$order_items_output .= '</tr>';
								}
														$order_items_output .= '</table>';
															echo wp_kses_post( $order_items_output );
							}

													echo wp_kses_post( '<hr><h4>Transaction Details:</h4>' );
							$output  = '<table class="laqirapay-table">';
							$output .= '<tr><th>Title </th><th>Value</th></tr>';

							$output                     .= '<tr>';
							$output                     .= '<td><strong>Transaction Hash</strong></td>';
												$output .= '<td>' . esc_html( $input_value ) . '</td>';
												$output .= '</tr>';

												$output .= '<tr>';
												$output .= '<td><strong>From</strong></td>';
												$output .= '<td>' . esc_html( $user_wallet_address_form_tx ) . '</td>';
												$output .= '</tr>';

												$output .= '<tr>';
												$output .= '<td><strong>To</strong></td>';
												$output .= '<td>' . esc_html( $main_laqirapay_contract_from_tx ) . '</td>';
												$output .= '</tr>';

												$output .= '<tr>';
												$output .= '<td><strong>Provider</strong></td>';
												$output .= '<td>' . esc_html( $provider_address_from_tx ) . '</td>';
												$output .= '</tr>';

												$output .= '<tr>';
												$output .= '<td><strong>Request Hash</strong></td>';
												$output .= '<td>' . esc_html( $req_hash_from_tx ) . '</td>';
												$output .= '</tr>';

												$output         .= '<tr>';
												$output         .= '<td><strong>Order Amount</strong></td>';
												$formatted_price = sprintf( '%s $', number_format( (float) $price_from_tx, 2 ) );
												$output         .= '<td>' . esc_html( $formatted_price ) . '</td>';
												$output         .= '</tr>';

							$output .= '</table>';

													echo wp_kses_post( $output . '</div>' );

																											$confirmation_payload = do_compelete_order_failed( $order_id, $input_value );
						} else {
													echo wp_kses_post( '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>Your data not confirmed</p>' );
						}
					} else {
									echo wp_kses_post( '<p><span class="dashicons dashicons-info" style="color:orange;"></span>The order is already stable and does not require further confirmation</p>' );
					}
				} else {
							echo wp_kses_post( '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>No order found with this Transaction hash</p>' );
				}
			} else {
					echo wp_kses_post( '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>The transaction status is not Completed on Blockchain</p>' );
			}
		} else {
			$provider_debug = sprintf(
				'%s#%s',
				strtolower( (string) $original_provider ),
				strtolower( (string) $provider_address_from_tx )
			);
			$message        = sprintf(
					/* translators: %s: provider address debug information. */
				__( 'Provider Address not Confirmed %s', 'laqirapay' ),
				$provider_debug
			);
			printf(
				'<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>%s</p>',
				esc_html( $message )
			);
		}
		echo '</div>';
					LaqiraLogger::log(
						200,
						'ajax',
						'view_confirmation_tx_hash_admin_rendered',
						array(
							'order_id' => $order_id,
							'tx_hash'  => $input_value,
						)
					);
			$html_output = ob_get_clean();

			$response = array(
				'result' => 'success',
				'html'   => wp_kses( $html_output, $allowed_tags ),
			);

			if ( $confirmation_payload ) {
					$response['confirmation'] = $confirmation_payload;
			}

			wp_send_json_success( $response );
	} else {
			wp_send_json_error(
				array(
					'result'  => 'failed',
					'message' => esc_html__( 'Transaction hash is required.', 'laqirapay' ),
				)
			);
	}
}


add_action( 'wp_ajax_laqirapay_confirm_tx_hash_in_user_panel', 'laqirapay_confirm_tx_hash_in_user_panel' );
function laqirapay_confirm_tx_hash_in_user_panel() {
	try {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'laqira_nonce_confirm_tx_hash_in_user_panel' ) ) {
				LaqiraLogger::log( 300, 'ajax', 'confirm_tx_hash_in_user_panel_invalid_nonce' );
				wp_send_json_error(
					array(
						'result'  => 'error',
						'message' => '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>' . esc_html__( 'Error on security Process', 'laqirapay' ) . '</p>',
					)
				);
		}

		if ( isset( $_POST['input_value'] ) ) {
			global $wpdb;
			$input_value = sanitize_text_field( $_POST['input_value'] );
			$order_id    = intval( $_POST['order_id'] );
			$order       = wc_get_order( intval( $order_id ) );
			$network_rpc = $order->get_meta( 'network_rpc' );

						$tx_results_direct = ( new BlockchainService() )->getTransactionInfo(
							$input_value,
							$network_rpc,
							function ( $transaction ) {
								return ( new TransactionDecoder() )->decodeTransactionDirect( $transaction->{'input'} );
							}
						);

						$tx_results_inapp = ( new BlockchainService() )->getTransactionInfo(
							$input_value,
							$network_rpc,
							function ( $transaction ) {
								return ( new TransactionDecoder() )->decodeTransactionInApp( $transaction->{'input'} );
							}
						);

						$tx_results_receipt = ( new BlockchainService() )->getTransactionRec(
							$input_value,
							$network_rpc,
							function ( $transaction ) {
								return ( $transaction );
							}
						);

			if ( is_wp_error( $tx_results_direct ) ) {
				$tx_results_direct = null;
			}

			if ( is_wp_error( $tx_results_inapp ) ) {
				$tx_results_inapp = null;
			}

			if ( is_wp_error( $tx_results_receipt ) ) {
				$tx_results_receipt = array();
			} else {
				$tx_results_receipt = (array) $tx_results_receipt;
			}

			$slippage_form_tx                = 0.0;
			$provider_address_from_tx        = '';
			$asset_address_from_tx           = '';
			$price_from_tx                   = 0.0;
			$req_hash_from_tx                = '';
			$user_wallet_address_form_tx     = '';
			$main_laqirapay_contract_from_tx = '';
			$transaction_status_from_tx      = '';

			if ( isset( $tx_results_direct ) && is_array( $tx_results_direct ) && count( $tx_results_direct ) > 0 ) {
				$slippage_form_tx         = floatval( $tx_results_direct['_slippage'] / 100 );
				$provider_address_from_tx = $tx_results_direct['_provider'];
				$asset_address_from_tx    = $tx_results_direct['_asset'];
				$price_from_tx            = floatval( $tx_results_direct['_price'] / 100 );
				$req_hash_from_tx         = $tx_results_direct['_reqHash'];
			}

			if ( isset( $tx_results_inapp ) && is_array( $tx_results_inapp ) && count( $tx_results_inapp ) > 0 ) {
				$slippage_form_tx         = floatval( $tx_results_inapp['_slippage'] / 100 );
				$provider_address_from_tx = $tx_results_inapp['_provider'];
				$asset_address_from_tx    = $tx_results_inapp['_asset'];
				$price_from_tx            = floatval( $tx_results_inapp['_price'] / 100 );
				$req_hash_from_tx         = $tx_results_inapp['_reqHash'];
			}

			if ( isset( $tx_results_receipt ) && is_array( $tx_results_receipt ) && count( $tx_results_receipt ) > 0 ) {
				$user_wallet_address_form_tx     = $tx_results_receipt['from'];
				$main_laqirapay_contract_from_tx = $tx_results_receipt['to'];
				$transaction_status_from_tx      = $tx_results_receipt['status'];
			}

						$original_provider = ( new BlockchainService() )->getProviderLocal();
			if ( strtolower( $original_provider ) == ( $provider_address_from_tx ) ) {
				if ( laqirapay_is_successful_transaction_status( $transaction_status_from_tx ) ) {
					if ( $order_id ) {

						$order_recovery_status = get_option( 'laqirapay_order_recovery_status' );
						$order_status          = 'wc-' . $order->get_status();
						if ( ( $order_status != 'wc-completed' ) && ( $order_status != $order_recovery_status ) ) {

							$order_data_provider_address    = $order->get_meta( 'AdminWalletAddress' );
							$order_data_user_wallet_address = $order->get_meta( 'CustomerWalletAddress' );

							$order_data_slippage = $order->get_meta( 'slippage' );
							$order_data_req_hash = $order->get_meta( 'reqHash' );
							$order_data_asset    = $order->get_meta( 'TokenAddress' );

							if (
								( strtolower( $order_data_provider_address ) === strtolower( $original_provider ) )
								&& ( floatval( $order_data_slippage ) === floatval( $slippage_form_tx ) )
								&& ( strtolower( $order_data_asset ) === strtolower( $asset_address_from_tx ) )
								&& ( strtolower( '0x' . $order_data_req_hash ) === strtolower( $req_hash_from_tx ) )
								&& ( strtolower( $order_data_user_wallet_address ) === strtolower( $user_wallet_address_form_tx ) )
							) {

								// wp_send_json_success( [
								// '1.$order_data_provider_address---:'=>strtolower($order_data_provider_address),
								// '1.$original_provider-------------:'=>strtolower($original_provider),
								// '1.'=>'---------------------------------------------------------------',
								// '2.$order_data_slippage-----------:'=>floatval($order_data_slippage),
								// '2.$slippage_form_tx--------------:'=>floatval($slippage_form_tx),
								// '2.'=>'---------------------------------------------------------------',
								// '3.$order_data_asset--------------:'=>strtolower($order_data_asset),
								// '3.$asset_address_from_tx---------:'=>strtolower($asset_address_from_tx),
								// '3.'=>'---------------------------------------------------------------',
								// '4.$order_data_req_hash-----------:'=>strtolower('0x'.$order_data_req_hash),
								// '4.$req_hash_from_tx--------------:'=>strtolower($req_hash_from_tx),
								// '4.'=>'---------------------------------------------------------------',
								// '5.$order_data_user_wallet_address--:'=>strtolower($order_data_user_wallet_address),
								// '5,$user_wallet_address_form_tx-----:'=>strtolower($user_wallet_address_form_tx),
								// ] );

								$tx_hash               = $input_value;
								$order                 = wc_get_order( intval( $order_id ) );
								$order_recovery_status = get_option( 'laqirapay_order_recovery_status' );
								$order->update_meta_data( 'tx_hash', $tx_hash );
								$order->update_meta_data( 'tx_status', 'success' );
								$order->update_status( $order_recovery_status, esc_html__( 'Order updated by TX hash confirmation method', 'laqirapay' ) );
								$order->add_order_note( esc_html__( 'Order updated by TX hash confirmation method ', 'laqirapay' ) );
								$order->save();

								$table_name_laqira_transactions                      = $wpdb->prefix . 'laqirapay_transactions';
																$order_id_int        = (int) $order->get_id();
																$laqira_transactions = array(
																	'wc_total_price' => $order->get_total(),
																	'wc_currency'    => $order->get_currency(),
																	'wc_order_id'    => $order_id_int,
																	'exchange_rate'  => $order->get_meta( 'exchange_rate' ),
																	'tx_hash'        => $order->get_meta( 'tx_hash' ),
																	'token_address'  => $order->get_meta( 'TokenAddress' ),
																	'token_name'     => $order->get_meta( 'TokenName' ),
																	'token_amount'   => $order->get_meta( 'TokenAmount' ),
																	'req_hash'       => $order->get_meta( 'reqHash' ),
																	'tx_from'        => $order->get_meta( 'CustomerWalletAddress' ),
																	'tx_to'          => $order->get_meta( 'AdminWalletAddress' ),
																);

																$existing_row_count = $wpdb->get_var(
																	$wpdb->prepare(
																		'SELECT COUNT(1) FROM %s WHERE wc_order_id = %%d',
																		$table_name_laqira_transactions
																	),
																	$order_id_int
																);

								if ( (int) $existing_row_count > 0 ) {
										$wpdb->update(
											$table_name_laqira_transactions,
											$laqira_transactions,
											array( 'wc_order_id' => $order_id_int ),
											null,
											array( '%d' )
										);
								} else {
										$wpdb->insert( $table_name_laqira_transactions, $laqira_transactions );
								}

																LaqiraLogger::log(
																	200,
																	'ajax',
																	'confirm_tx_hash_in_user_panel_success',
																	array(
																		'order_id' => $order_id_int,
																		'tx_hash'  => $tx_hash,
																	)
																);
																wp_send_json_success(
																	array(
																		'result'   => 'success',
																		'redirect' => esc_url_raw( $order->get_checkout_order_received_url() ),

																	)
																);
							} else {
									LaqiraLogger::log( 400, 'ajax', 'confirm_tx_hash_in_user_panel_data_not_confirmed' );
									wp_send_json_error(
										array(
											'result'  => 'error',
											'message' => '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>' . esc_html__( 'Your data not confirmed', 'laqirapay' ) . '</p>',
										)
									);
							}
						} else {
								LaqiraLogger::log( 400, 'ajax', 'confirm_tx_hash_in_user_panel_order_stable' );
								wp_send_json_error(
									array(
										'result'  => 'error',
										'message' => '<p><span class="dashicons dashicons-info" style="color:orange;"></span>' . esc_html__( 'The order is already stable and does not require further confirmation', 'laqirapay' ) . '</p>',
									)
								);
						}
					} else {
										LaqiraLogger::log( 400, 'ajax', 'confirm_tx_hash_in_user_panel_order_not_found' );
										wp_send_json_error(
											array(
												'result'  => 'error',
												'message' => '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>' . esc_html__( 'No order found with this Transaction hash', 'laqirapay' ) . '</p>',
											)
										);
					}
				} else {
						LaqiraLogger::log( 400, 'ajax', 'confirm_tx_hash_in_user_panel_tx_not_completed' );
						wp_send_json_error(
							array(
								'result'  => 'error',
								'message' => '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>' . esc_html__( 'The transaction status is not Completed on Blockchain', 'laqirapay' ) . '</p>',
							)
						);
				}
			} else {
					LaqiraLogger::log( 400, 'ajax', 'confirm_tx_hash_in_user_panel_provider_not_confirmed' );
					wp_send_json_error(
						array(
							'result'  => 'error',
							'message' => '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>' . esc_html__( 'Provider Address not Confirmed', 'laqirapay' ) . '</p>',
						)
					);
			}
						echo '</div>';
		}
				wp_die();
	} catch ( Exception $e ) {
			$sanitized_exception_message = laqirapay_sanitize_textarea( $e->getMessage() );
		if ( $sanitized_exception_message === '' ) {
				$sanitized_exception_message = esc_html__( 'An unexpected error occurred. Please try again later.', 'laqirapay' );
		}
			LaqiraLogger::log(
				400,
				'ajax',
				'confirm_tx_hash_in_user_panel_exception',
				array(
					'message' => $sanitized_exception_message,
				)
			);
			wp_send_json_error(
				array(
					'result'  => 'error',
					'message' => '<p><span class="dashicons dashicons-dismiss" style="color:red;"></span>' . esc_html__( 'Error on Confirmatin Process', 'laqirapay' ) . '</p>',
				)
			);
	}
}

add_action( 'wp_ajax_laqirapay_do_confim_tx_hash', 'laqirapay_do_confim_tx_hash' );
add_action( 'wp_ajax_nopriv_laqirapay_do_confim_tx_hash', 'laqirapay_do_confim_tx_hash' );

/**
 * The function `laqirapay_do_confim_tx_hash` processes and confirms transactions for orders in
 * WooCommerce using a custom payment gateway.
 */
function laqirapay_do_confim_tx_hash() {
		global $woocommerce;
		// check_ajax_referer('laqirapay_do_confim_tx_hash', 'nonce');
		$order_id = isset( $_POST['orderID'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['orderID'] ) ) ) : 0;
	if ( $order_id ) {
			$order            = wc_get_order( intval( $order_id ) );
			$payment_gateways = $woocommerce->payment_gateways->payment_gateways();
		$order->set_payment_method( $payment_gateways['WC_laqirapay'] );
		$order_recovery_status = get_option( 'laqirapay_order_recovery_status' );
		$order->update_status( $order_recovery_status, esc_html__( 'Order updated by TX hash confirmation method', 'laqirapay' ) );
		$order->add_order_note( esc_html__( 'Order updated by TX hash confirmation method ', 'laqirapay' ) );
		$order->save();

		global $wpdb;
		$table_name_laqira_transactions = $wpdb->prefix . 'laqirapay_transactions';
		$order_id_int                   = (int) $order->get_id();
		$laqira_transactions            = array(
			'wc_total_price' => $order->get_total(),
			'wc_currency'    => $order->get_currency(),
			'wc_order_id'    => $order_id_int,
			'exchange_rate'  => $order->get_meta( 'exchange_rate' ),
			'tx_hash'        => $order->get_meta( 'tx_hash' ),
			'token_address'  => $order->get_meta( 'TokenAddress' ),
			'token_name'     => $order->get_meta( 'TokenName' ),
			'token_amount'   => $order->get_meta( 'TokenAmount' ),
			'req_hash'       => $order->get_meta( 'reqHash' ),
			'tx_from'        => $order->get_meta( 'CustomerWalletAddress' ),
			'tx_to'          => $order->get_meta( 'AdminWalletAddress' ),
		);

		$existing_row_count = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(1) FROM %s WHERE wc_order_id = %%d',
				$table_name_laqira_transactions
			),
			$order_id_int
		);

		if ( (int) $existing_row_count > 0 ) {
			$wpdb->update(
				$table_name_laqira_transactions,
				$laqira_transactions,
				array( 'wc_order_id' => $order_id_int ),
				null,
				array( '%d' )
			);
		} else {
			$wpdb->insert( $table_name_laqira_transactions, $laqira_transactions );
		}

		$html = '
        <div class="info-box-center">
        <span class="dashicons dashicons-yes-alt" style="color:green;"></span>
        <h3>Order and Transaction confirmed and updated successfully.</h3>
        </div>
        ';
		// echo "Your Order and Transaction confirmed and updated successfully.";

		LaqiraLogger::log(
			200,
			'ajax',
			'do_confim_tx_hash_success',
			array(
				'order_id' => $order_id_int,
			)
		);
			wp_send_json_success(
				array(
					'result'       => 'success',
					'redirect'     => esc_url_raw( $order->get_checkout_order_received_url() ),
					'admin_result' => wp_kses( $html, laqirapay_admin_result_allowed_html() ),
				)
			);
	} else {
			LaqiraLogger::log( 400, 'ajax', 'do_confim_tx_hash_error' );
			wp_send_json_error(
				array(
					'result'  => 'failed',
					'message' => esc_html__( 'Order and Transaction not confirmed.', 'laqirapay' ),
				)
			);
	}
		wp_die();
}

/**
 * Retrieves allowed HTML tags for order confirmation markup.
 *
 * @return array<string, array<string, bool|string>>
 */
function laqirapay_get_order_confirmation_allowed_tags() {
		$allowed_tags = wp_kses_allowed_html( 'post' );

		$allowed_tags['script'] = array(
			'type' => true,
		);

		if ( ! isset( $allowed_tags['input'] ) ) {
				$allowed_tags['input'] = array();
		}
		$allowed_tags['input']['type']  = true;
		$allowed_tags['input']['id']    = true;
		$allowed_tags['input']['name']  = true;
		$allowed_tags['input']['value'] = true;

		if ( ! isset( $allowed_tags['button'] ) ) {
				$allowed_tags['button'] = array();
		}
		$allowed_tags['button']['class'] = true;
		$allowed_tags['button']['type']  = true;
		$allowed_tags['button']['id']    = true;

		if ( ! isset( $allowed_tags['div'] ) ) {
				$allowed_tags['div'] = array();
		}
		$allowed_tags['div']['id']    = true;
		$allowed_tags['div']['class'] = true;
		$allowed_tags['div']['style'] = true;

		if ( ! isset( $allowed_tags['img'] ) ) {
				$allowed_tags['img'] = array();
		}
		$allowed_tags['img']['class']  = true;
		$allowed_tags['img']['width']  = true;
		$allowed_tags['img']['height'] = true;
		$allowed_tags['img']['src']    = true;
		$allowed_tags['img']['alt']    = true;

		if ( ! isset( $allowed_tags['form'] ) ) {
				$allowed_tags['form'] = array();
		}
		$allowed_tags['form']['action'] = true;
		$allowed_tags['form']['method'] = true;
		$allowed_tags['form']['id']     = true;
		$allowed_tags['form']['class']  = true;

		return $allowed_tags;
}

/**
 * Builds the order confirmation payload used by the transaction recovery UI.
 *
 * @param int|string $order_id WooCommerce order ID.
 *
 * @return array<string,mixed> {
 *     @type string $order_id Sanitized order identifier.
 *     @type string $action   AJAX action hook used to confirm the order.
 *     @type array  $request  Default request payload for the confirmation action.
 *     @type string $markup   Sanitized HTML markup for the confirmation controls.
 * }
 */
function do_compelete_order( $order_id ) {
				$sanitized_order_id = laqirapay_sanitize_positive_int( $order_id );
	if ( $sanitized_order_id === null ) {
					$sanitized_order_id = '';
	} else {
					$sanitized_order_id = (string) $sanitized_order_id;
	}

				$loading_src = LAQIRA_PLUGINS_URL . 'assets/img/loading.svg';

				$markup = sprintf(
					'<div class="laqirapay-confirmation-actions">'
						. '<input type="hidden" id="order_id_input" name="order_id_input" value="%1$s" />'
						. '<button class="button save_order button-primary laqirapay-confirm-button" type="button" id="do-confirm-button">%2$s</button>'
						. '<div id="laqirapay-after-confirmation-action"></div>'
						. '<div style="text-align:center;" id="loading-indicator-bottom">'
						. '<img class="loading" width="24" height="24" src="%3$s" alt="%4$s" />'
						. '</div>'
						. '</div>',
					esc_attr( $sanitized_order_id ),
					esc_html__( 'Confirm Order', 'laqirapay' ),
					esc_url( $loading_src ),
					esc_attr__( 'Loading', 'laqirapay' )
				);

				return array(
					'order_id' => $sanitized_order_id,
					'action'   => 'laqirapay_do_confim_tx_hash',
					'request'  => array(
						'action'  => 'laqirapay_do_confim_tx_hash',
						'orderID' => $sanitized_order_id,
					),
					'markup'   => wp_kses( $markup, laqirapay_get_order_confirmation_allowed_tags() ),
				);
}


/**
 * Builds the confirmation payload for failed transaction recovery attempts.
 *
 * @param int|string $order_id WooCommerce order ID.
 * @param string     $tx_hash  Transaction hash submitted by the administrator.
 *
 * @return array<string,mixed> {
 *     @type string $order_id Sanitized order identifier.
 *     @type string $tx_hash  Sanitized transaction hash.
 *     @type string $action   AJAX action hook used to confirm the order.
 *     @type array  $request  Default request payload for the confirmation action.
 *     @type string $markup   Sanitized HTML markup for the confirmation controls.
 * }
 */
function do_compelete_order_failed( $order_id, $tx_hash ) {
				$sanitized_order_id = laqirapay_sanitize_positive_int( $order_id );
	if ( $sanitized_order_id === null ) {
					$sanitized_order_id = 0;
	}

				$sanitized_tx_hash = laqirapay_sanitize_tx_hash( $tx_hash );
	if ( $sanitized_tx_hash === null ) {
					$sanitized_tx_hash = laqirapay_sanitize_simple_text( $tx_hash );
	}
				$nonce_value = wp_create_nonce( 'laqirapay_do_confim_tx_hash_for_faild_transaction' );
				$loading_src = LAQIRA_PLUGINS_URL . 'assets/img/loading.svg';

				$markup = sprintf(
					'<div class="laqirapay-confirmation-actions">'
						. '<input type="hidden" id="order_id_input" name="order_id_input" value="%1$s" />'
						. '<input type="hidden" id="tx_hash_input" name="tx_hash_input" value="%2$s" />'
						. '<input type="hidden" id="nonce_input" name="nonce_input" value="%3$s" />'
						. '<button class="button save_order button-primary laqirapay-confirm-button" type="button" id="do-confirm-button">%4$s</button>'
						. '<div id="laqirapay-after-confirmation-action"></div>'
						. '<div style="text-align:center;" id="loading-indicator-bottom">'
						. '<img class="loading" width="24" height="24" src="%5$s" alt="%6$s" />'
						. '</div>'
						. '</div>',
					esc_attr( (string) $sanitized_order_id ),
					esc_attr( (string) $sanitized_tx_hash ),
					esc_attr( (string) $nonce_value ),
					esc_html__( 'Confirm Order', 'laqirapay' ),
					esc_url( $loading_src ),
					esc_attr__( 'Loading', 'laqirapay' )
				);

				return array(
					'order_id' => (string) $sanitized_order_id,
					'tx_hash'  => (string) $sanitized_tx_hash,
					'action'   => 'laqirapay_do_confim_tx_hash_for_faild_transaction',
					'request'  => array(
						'action'  => 'laqirapay_do_confim_tx_hash_for_faild_transaction',
						'orderID' => (string) $sanitized_order_id,
						'txHash'  => (string) $sanitized_tx_hash,
						'nonce'   => (string) $nonce_value,
					),
					'markup'   => wp_kses( $markup, laqirapay_get_order_confirmation_allowed_tags() ),
				);
}

add_action( 'wp_ajax_laqirapay_do_confim_tx_hash_for_faild_transaction', 'laqirapay_do_confim_tx_hash_for_faild_transaction' );
add_action( 'wp_ajax_nopriv_laqirapay_do_confim_tx_hash_for_faild_transaction', 'laqirapay_do_confim_tx_hash_for_faild_transaction' );

function laqirapay_do_confim_tx_hash_for_faild_transaction() {
		global $woocommerce;
	if ( ! check_ajax_referer( 'laqirapay_do_confim_tx_hash_for_faild_transaction', 'nonce', false ) ) {
			LaqiraLogger::log( 300, 'ajax', 'do_confim_tx_hash_for_faild_transaction_invalid_nonce' );
			wp_send_json_error(
				array(
					'result'  => 'failed',
					'message' => esc_html__( 'nonce Error!!!', 'laqirapay' ),
				)
			);
			return;
	}

	if ( isset( $_POST['orderID'] ) ) {
			$order_id         = intval( sanitize_text_field( wp_unslash( $_POST['orderID'] ) ) );
			$tx_hash          = isset( $_POST['txHash'] ) ? sanitize_text_field( wp_unslash( $_POST['txHash'] ) ) : '';
			$order            = wc_get_order( intval( $order_id ) );
			$payment_gateways = $woocommerce->payment_gateways->payment_gateways();
		$order->set_payment_method( $payment_gateways['WC_laqirapay'] );
		$order_recovery_status = get_option( 'laqirapay_order_recovery_status' );
		$order->update_meta_data( 'tx_hash', $tx_hash );
		$order->update_meta_data( 'tx_status', 'success' );
		$order->update_status( $order_recovery_status, esc_html__( 'Order updated by TX hash confirmation method', 'laqirapay' ) );
		$order->add_order_note( esc_html__( 'Order updated by TX hash confirmation method ', 'laqirapay' ) );
		$order->save();

		global $wpdb;
		$table_name_laqira_transactions = $wpdb->prefix . 'laqirapay_transactions';
		$order_id_int                   = (int) $order->get_id();
		$laqira_transactions            = array(
			'wc_total_price' => $order->get_total(),
			'wc_currency'    => $order->get_currency(),
			'wc_order_id'    => $order_id_int,
			'exchange_rate'  => $order->get_meta( 'exchange_rate' ),
			'tx_hash'        => $order->get_meta( 'tx_hash' ),
			'token_address'  => $order->get_meta( 'TokenAddress' ),
			'token_name'     => $order->get_meta( 'TokenName' ),
			'token_amount'   => $order->get_meta( 'TokenAmount' ),
			'req_hash'       => $order->get_meta( 'reqHash' ),
			'tx_from'        => $order->get_meta( 'CustomerWalletAddress' ),
			'tx_to'          => $order->get_meta( 'AdminWalletAddress' ),
		);

		$existing_row_count = $wpdb->get_var(
			$wpdb->prepare(
			// Table name uses $wpdb->prefix with a known suffix and is trusted.
				'SELECT COUNT(1) FROM %s WHERE wc_order_id = %%d',
				$table_name_laqira_transactions
			),
			$order_id_int
		);

		if ( (int) $existing_row_count > 0 ) {
			$wpdb->update(
				$table_name_laqira_transactions,
				$laqira_transactions,
				array( 'wc_order_id' => $order_id_int ),
				null,
				array( '%d' )
			);
		} else {
			$wpdb->insert( $table_name_laqira_transactions, $laqira_transactions );
		}

		$html = '
        <div class="info-box-center">
        <span class="dashicons dashicons-yes-alt" style="color:green;"></span>
        <h3>Order and Transaction confirmed and updated successfully.</h3>
        </div>
        ';
		// echo "Your Order and Transaction confirmed and updated successfully.";

		LaqiraLogger::log(
			200,
			'ajax',
			'do_confim_tx_hash_for_faild_transaction_success',
			array(
				'order_id' => $order_id_int,
				'tx_hash'  => $tx_hash,
			)
		);
			wp_send_json_success(
				array(
					'result'       => 'success',
					'redirect'     => esc_url_raw( $order->get_checkout_order_received_url() ),
					'admin_result' => wp_kses( $html, laqirapay_admin_result_allowed_html() ),
				)
			);
	} else {
			LaqiraLogger::log( 400, 'ajax', 'do_confim_tx_hash_for_faild_transaction_error' );
			wp_send_json_error(
				array(
					'result'  => 'failed',
					'message' => esc_html__( 'Order and Transaction not confirmed.', 'laqirapay' ),
				)
			);
	}
		wp_die();
}
