<?php
/**
 * JWT helper utilities.
 *
 * @package LaqiraPay\Helpers
 */

namespace LaqiraPay\Helpers;

use LaqiraPay\Domain\Services\LaqiraLogger;
use LaqiraPay\Services\BlockchainService;
use WP_Error;

/**
 * Helper utilities for creating and verifying JWT tokens.
 */
class JwtHelper {


	/**
	 * Generates a JWT access token for a user.
	 *
	 * @param int $user_id User ID the token is generated for.
	 * @return array {
	 *     Access token data.
	 *
	 *     @type string $token Encoded JWT string.
	 *     @type string $expires_datetime Expiration datetime in `Y-m-d H:i:s` format.
	 *     @type int $expires_in Token lifetime in seconds.
	 * }
	 */
	public function create_access_token( $user_id ) {
		$secret = $this->laqirapay_get_jwt_secret();
		$now    = time();
		$exp    = $now + 3600;

		$header = array(
			'typ' => 'JWT',
			'alg' => LAQIRAPAY_JWT_ALG,
		);

		$payload = array(
			'iat' => $now,
			'exp' => $exp,
			'sub' => (string) $user_id,
		);

		$header_encoded  = $this->base64url_encode( wp_json_encode( $header ) );
		$payload_encoded = $this->base64url_encode( wp_json_encode( $payload ) );
		$data            = "$header_encoded.$payload_encoded";

		$signature = hash_hmac(
			LAQIRAPAY_JWT_ALG_SIGNATURE,
			$data,
			$secret,
			true
		);

		$signature_encoded = $this->base64url_encode( $signature );
		$token             = "$data.$signature_encoded";

		return array(
			'token'            => $token,
			'expires_datetime' => gmdate( 'Y-m-d H:i:s', $exp ),
			'expires_in'       => 3600,
		);
	}


	/**
	 * Retrieves or generates the JWT secret key.
	 *
	 * @return string JWT secret key stored in WordPress options.
	 */
	public function laqirapay_get_jwt_secret() {
		$key = get_option( 'laqirapay_jwt_secret' );
		if ( ! $key ) {
			$key = bin2hex( random_bytes( 32 ) );
			update_option( 'laqirapay_jwt_secret', $key );
		}

		return $key;
	}

	/**
	 * Encodes data in URL-safe base64 format.
	 *
	 * @param string $data Raw binary string to encode.
	 * @return string URL-safe base64-encoded string.
	 */
	private function base64url_encode( $data ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Required for JWT compact serialization.
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}

	/**
	 * Verifies the JWT contained in request headers or cookies.
	 *
	 * @param mixed $headers Request headers array or object providing the token value.
	 * @return string Verification result: 'verified', 'failed', or 'invalid'.
	 */
	public function verify_header( $headers ) {
		$token = $this->sanitize_token( $headers );

		if ( '' === $token ) {
			if ( isset( $_COOKIE['laqira_jwt'] ) ) {
				$cookie_token = sanitize_text_field(
					wp_unslash( $_COOKIE['laqira_jwt'] )
				);
				$token        = $this->sanitize_token( $cookie_token );
			}
		}

		if ( '' === $token ) {
			LaqiraLogger::log( 300, 'security', 'jwt_missing' );

			return 'invalid';
		}

		$result_header = $this->verify_jwt( $token );
		$blockchain    = new BlockchainService();

		if ( ! is_wp_error( $result_header ) ) {
			$subject = isset( $result_header->sub )
				? (string) $result_header->sub : '';
			if (
				'' !== $subject
				&& $blockchain->getProviderLocal() === $subject
			) {
				// Token subject must match provider address to pass verification.
				LaqiraLogger::log( 200, 'security', 'jwt_verified' );

				return 'verified';
			}
		}

		LaqiraLogger::log( 300, 'security', 'jwt_failed' );

		return 'failed';
	}

	/**
	 * Normalizes and sanitizes a token value.
	 *
	 * @param mixed $token Token value to sanitize.
	 * @return string Sanitized token string.
	 */
	private function sanitize_token( $token ): string {
		if ( is_object( $token ) && method_exists( $token, '__toString' ) ) {
			$token = (string) $token;
		} elseif ( ! is_scalar( $token ) ) {
			return '';
		}

		$token = (string) $token;

		if ( function_exists( 'wp_unslash' ) ) {
			$token = wp_unslash( $token );
		}

		if ( function_exists( 'sanitize_text_field' ) ) {
			$token = sanitize_text_field( $token );
		} else {
			if ($token === null) {
   			 $token = '';
			}
			$token = preg_replace( '/[^A-Za-z0-9\-\._]/', '', $token ) ?? '';
		}

		return trim( $token );
	}

	/**
	 * Validates the given JWT token.
	 *
	 * @param string $token Encoded JWT string.
	 * @return WP_Error|object Decoded payload on success or WP_Error on failure.
	 */
	public function verify_jwt( $token ) {
		$parts = explode( '.', $token );
		if (
			3 !== count( $parts )
		) {
			// JWT must have header, payload, and signature.
			LaqiraLogger::log( 400, 'security', 'jwt_format_error' );

			return new WP_Error(
				'jwt_format_error',
				esc_html__( 'JWT token format is invalid', 'laqirapay' ),
				array( 'status' => 401 )
			);
		}

		list( $header64, $payload64, $signature64 ) = $parts;
		$data                                       = "$header64.$payload64";
		$expected_signature                         = $this->base64url_encode(
			hash_hmac(
				LAQIRAPAY_JWT_ALG_SIGNATURE,
				$data,
				$this->laqirapay_get_jwt_secret(),
				true
			)
		);

		if (
			! hash_equals(
				$expected_signature,
				$signature64
			)
		) {
			// Constant-time compare prevents timing attacks.
			LaqiraLogger::log( 400, 'security', 'jwt_invalid_signature' );

			return new WP_Error(
				'jwt_invalid_signature',
				esc_html__( 'Signature mismatch', 'laqirapay' ),
				array( 'status' => 401 )
			);
		}

		$payload = json_decode( $this->base64url_decode( $payload64 ) );
		if ( ! isset( $payload->exp ) || time() > $payload->exp ) {
			LaqiraLogger::log( 300, 'security', 'jwt_expired' );

			return new WP_Error(
				'jwt_expired',
				esc_html__( 'JWT token has expired', 'laqirapay' ),
				array( 'status' => 401 )
			);
		}

		LaqiraLogger::log( 200, 'security', 'jwt_payload_valid' );

		return $payload;
	}

	/**
	 * Decodes URL-safe base64-encoded data.
	 *
	 * @param string $data Base64url encoded string.
	 * @return string|false Decoded binary string or false on failure.
	 */
	private function base64url_decode( $data ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Required for JWT compact serialization.
		return base64_decode( strtr( $data, '-_', '+/' ) );
	}
}
