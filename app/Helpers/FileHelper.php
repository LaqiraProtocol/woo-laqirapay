<?php

namespace LaqiraPay\Helpers;

class FileHelper
{
    public static function get_contents_secure(string $url): string
    {
        $args = [
            'timeout'   => 10,
            'sslverify' => false,
        ];

        $args = apply_filters('laqirapay_file_helper_request_args', $args, $url);

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) { // Bail out if the HTTP request failed.
            $message = method_exists($response, 'get_error_message')
                ? $response->get_error_message()
                : 'Unknown error';
            error_log(sprintf('[LaqiraPay] Failed to fetch %s: %s', $url, $message));

            return '';
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) { // Only process successful responses.
            error_log(sprintf('[LaqiraPay] Unexpected response code %s when fetching %s', (string) $code, $url));

            return '';
        }

        return (string) wp_remote_retrieve_body($response);
    }
}

