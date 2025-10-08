<?php
namespace LaqiraPay\Domain\Services;

/**
 * Service for logging application events, integrated with WooCommerce Logger.
 */
class LaqiraLogger {

	private $logger;
	private array $sensitiveKeys = array(
		'authorization',
		'api_key',
		'api_key',
		'jwt',
		'token',
		'password',
		'secret',
		'cid',
	);

	public function __construct() {
		// Ensure WooCommerce and WC_Logger are available
		if ( ! class_exists( '\WC_Logger' ) || ! function_exists( 'wc_get_order' ) ) {
			$this->logger = null;
			return;
		}

		// Use get_logger if available (WooCommerce 7.0+), fallback to new WC_Logger
		if ( method_exists( '\WC_Logger', 'get_logger' ) ) {
			$this->logger = \WC_Logger::get_logger( 'laqira_pay' );
		} else {
			$this->logger = new \WC_Logger();
		}
	}

	/**
	 * Magic handler for instance calls to log().
	 */
	public function __call( string $name, array $arguments ): void {
		if ( $name === 'log' && $this->logger ) {
			$this->write( ...$arguments );
		}
	}

	/**
	 * Magic handler for static calls to log().
	 */
	public static function __callStatic( string $name, array $arguments ): void {
		if ( $name === 'log' ) {
			$logger = new self();
			$logger->write( ...$arguments );
		}
	}

	/**
	 * Actual logging implementation using WC_Logger.
	 */
	private function write( int $level, string $type, string $event, array $context = array(), string $message = '' ): void {
		// Check if logger is initialized
		if ( ! $this->logger ) {
			return;
		}

		// Check if logging is enabled
		$loggingEnabled = (bool) get_option( 'laqirapay_log_enabled', true );
		if ( ! $loggingEnabled ) {
			return;
		}

		if ( $message !== '' ) {
				$message = $this->sanitizeLogValue( $message );
		}

				// Enrich and mask context
				$context = $this->enrichContext( $context, $event, $message );
				$context = $this->maskSensitive( $context );

		// Map custom level to WC_Logger PSR-3 levels
		$wcLevel               = $this->mapLevel( $level );
		$fullMessage           = sprintf( '[%s] %s: %s', $type, $event, $message ?: 'No message' );
		$context['created_at'] = function_exists( 'current_time' ) ? current_time( 'mysql', 1 ) : gmdate( 'Y-m-d H:i:s' );

		// Add source for older WooCommerce versions
		if ( ! method_exists( '\WC_Logger', 'get_logger' ) ) {
			$context['source'] = 'laqira_pay';
		}

		// Log to WC_Logger
		$this->logger->log( $wcLevel, $fullMessage, $context );
	}

	/**
	 * Map custom numeric levels to WC_Logger PSR-3 levels.
	 */
	private function mapLevel( int $level ): string {
		$map = array(
			100 => 'debug',
			200 => 'info',
			250 => 'notice',
			300 => 'warning',
			400 => 'error',
			500 => 'critical',
			600 => 'alert',
			700 => 'emergency',
		);
		return $map[ $level ] ?? 'info';
	}

	/**
	 * Enrich log context with environment-specific details.
	 */
	private function enrichContext( array $context, string $event, string $message ): array {
		// Add WordPress user information
		if ( function_exists( 'wp_get_current_user' ) ) {
			$user = wp_get_current_user();
			if ( $user && isset( $user->ID ) && $user->ID ) {
				$context['user'] = $user->ID . ' - ' . $user->user_email;
			}
		}

		// Add WooCommerce order information
		$orderId = $this->extractOrderId( $context );
		if ( $orderId > 0 ) {
			$context['order_id'] = $orderId;
		}
		if ( $orderId > 0 && function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( $orderId );
			if ( $order ) {
				$context['order'] = array(
					'id'     => $order->get_id(),
					'status' => $order->get_status(),
					'total'  => $order->get_total(),
				);
			}
		}

		// Add IP address (always hashed for privacy)
		$ip = $this->getSanitizedIpAddress();
		if ( $ip !== '' ) {
			$context['ip'] = hash( 'sha256', $ip );
		}

		// Add request ID
		$requestId = $this->getSanitizedServerValue( 'HTTP_X_REQUEST_ID' );
		if ( $requestId === '' ) {
			$requestId = $this->getSanitizedServerValue( 'REQUEST_ID' );
		}
		if ( $requestId === '' && function_exists( 'wp_generate_uuid4' ) ) {
			$requestId = wp_generate_uuid4();
		}
		if ( $requestId !== '' ) {
			$context['request_id'] = $requestId;
		}

		// Add event and message
		$context['event'] = $event;
		if ( $message !== '' ) {
				$context['message'] = $this->sanitizeLogValue( $message );
		}

		// Add caller information
		$context['source'] = $this->detectSource();

		return $context;
	}
	/**
	 * Extract an order ID from the provided context or request data.
	 */
	private function extractOrderId( array $context ): int {
		if ( isset( $context['order_id'] ) ) {
			$orderId = $this->normalizeOrderId( $context['order_id'] );
			if ( $orderId > 0 ) {
				return $orderId;
			}
		}

		$sources = array(
			filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT ),
			filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT ),
		);

		foreach ( $sources as $source ) {
			if ( $source !== null && $source !== false && $source !== '' ) {
				$orderId = $this->normalizeOrderId( $source );
				if ( $orderId > 0 ) {
					return $orderId;
				}
			}
		}

				$requestOrderId = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		if ( $requestOrderId !== null && $requestOrderId !== false && $requestOrderId !== '' ) {
				$orderId = $this->normalizeOrderId( $requestOrderId );
			if ( $orderId > 0 ) {
						return $orderId;
			}
		}

		return 0;
	}

	/**
	 * Convert a raw order identifier into a positive integer.
	 *
	 * @param mixed $value Raw order identifier.
	 */
	private function normalizeOrderId( $value ): int {
		if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
			$value = (string) $value;
		} elseif ( ! is_scalar( $value ) ) {
			return 0;
		}

		$value = (string) $value;

		if ( function_exists( 'wp_unslash' ) ) {
			$value = wp_unslash( $value );
		}

		if ( function_exists( 'sanitize_text_field' ) ) {
			$value = sanitize_text_field( $value );
		} else {
			$value = trim( $value );
		}

		if ( function_exists( 'absint' ) ) {
			return absint( $value );
		}

		$intValue = (int) $value;
		return $intValue > 0 ? $intValue : 0;
	}

	/**
	 * Retrieve a sanitized value from the $_SERVER superglobal.
	 */
	private function getSanitizedServerValue( string $key ): string {
			$allowedKeys = array(
				'HTTP_X_REQUEST_ID' => FILTER_UNSAFE_RAW,
				'REQUEST_ID'        => FILTER_UNSAFE_RAW,
				'REMOTE_ADDR'       => FILTER_UNSAFE_RAW,
			);

			if ( ! isset( $allowedKeys[ $key ] ) ) {
					return '';
			}

			$value = filter_input( INPUT_SERVER, $key, $allowedKeys[ $key ] );
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

			if ( function_exists( 'wp_unslash' ) ) {
					$value = wp_unslash( $value );
			}

			if ( function_exists( 'sanitize_text_field' ) ) {
					$value = sanitize_text_field( $value );
			} else {
					$value = trim( $value );
			}

			return $value;
	}

	/**
	 * Retrieve and validate the requesting IP address.
	 */
	private function getSanitizedIpAddress(): string {
			$ip = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );
		if ( ! is_string( $ip ) || $ip === '' ) {
				return '';
		}

		if ( function_exists( 'wp_unslash' ) ) {
				$ip = wp_unslash( $ip );
		}

			$validated = filter_var( $ip, FILTER_VALIDATE_IP );
			return is_string( $validated ) ? $validated : '';
	}

	/**
	 * Detect the origin of the log call using a backtrace.
	 */
	private function detectSource(): string {
		$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		foreach ( $trace as $frame ) {
			if ( ( $frame['class'] ?? '' ) === self::class ) {
				continue;
			}
			$function = $frame['function'] ?? '';
			$class    = $frame['class'] ?? '';
			$file     = isset( $frame['file'] ) ? basename( $frame['file'] ) : '';
			$line     = $frame['line'] ?? '';
			$location = $file !== '' ? $file . ':' . $line : '';
			$callable = $class ? $class . '::' . $function : $function;
			return trim( $callable . ( $location ? ' (' . $location . ')' : '' ) );
		}
		return '';
	}
	/**
	 * Recursively mask sensitive keys within the context array.
	 */
	private function maskSensitive( array $context ): array {
		foreach ( $context as $key => $value ) {
			if ( is_array( $value ) ) {
				$context[ $key ] = $this->maskSensitive( $value );
				continue;
			}
			if ( in_array( strtolower( (string) $key ), $this->sensitiveKeys, true ) ) {
				$context[ $key ] = '***';
			}
		}
			return $context;
	}

	private function sanitizeLogValue( string $value ): string {
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
