<?php

namespace LaqiraPayments\Helpers;

use LaqiraPayments\Domain\Services\LaqiraLogger;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



class FileHelper {

	public static function get_contents_secure( string $url ): string {
		$args = array(
			'timeout'   => 10,
			'sslverify' => true,
		);

		$args = apply_filters( 'laqira_payments_file_helper_request_args', $args, $url );

		$response = wp_remote_get( $url, $args );

			if ( is_wp_error( $response ) ) { // Bail out if the HTTP request failed.
				$message = method_exists( $response, 'get_error_message' )
					? $response->get_error_message()
					: 'Unknown error';
				LaqiraLogger::log( 400, 'http', 'file_helper_fetch_failed', array( 'url' => $url ), (string) $message );

				return '';
			}

			$code = wp_remote_retrieve_response_code( $response );
			if ( $code !== 200 ) { // Only process successful responses.
				LaqiraLogger::log( 300, 'http', 'file_helper_unexpected_status', array( 'url' => $url, 'status' => (int) $code ) );

				return '';
			}

		return (string) wp_remote_retrieve_body( $response );
	}
}
