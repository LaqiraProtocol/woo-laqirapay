<?php

namespace LaqiraPay\Helpers;

use LaqiraPay\Services\BlockchainService;
use WP_Error;
use LaqiraPay\Domain\Services\LaqiraLogger;

class JwtHelper
{
    public function laqirapayGetJwtSecret()
    {
        $key = get_option('laqirapay_jwt_secret');
        if (!$key) {
            $key = bin2hex(random_bytes(32));
            update_option('laqirapay_jwt_secret', $key);
        }
        return $key;
    }

    public function createAccessToken($user_id)
    {
        $secret = $this->laqirapayGetJwtSecret();
        $now = time();
        $exp = $now + 3600; // Token valid for 1 hour

        $header = [
            'typ' => 'JWT',
            'alg' => LAQIRAPAY_JWT_ALG,
        ];

        $payload = [
            'iat' => $now,
            'exp' => $exp,
            'sub' => (string) $user_id,
        ];

        $header_encoded = $this->base64urlEncode(json_encode($header));
        $payload_encoded = $this->base64urlEncode(json_encode($payload));
        $data = "$header_encoded.$payload_encoded";

        $signature = hash_hmac(
            LAQIRAPAY_JWT_ALG_SIGNATURE,
            $data,
            $secret,
            true
        );

        $signature_encoded = $this->base64urlEncode($signature);
        $token = "$data.$signature_encoded";

        return [
            'token' => $token,
            'expires_datetime' => date('Y-m-d H:i:s', $exp),
            'expires_in' => 3600,
        ];
    }

    public function verifyHeader($headers)
    {
        if (isset($_COOKIE['laqira_jwt'])) {
            $token = $_COOKIE['laqira_jwt'];
            $result_header = $this->verifyJwt($token);
            $blockchain = new BlockchainService();
            if (
                !is_wp_error($result_header) &&
                isset($result_header) &&
                $result_header->{'sub'} == $blockchain->getProviderLocal()
            ) {
                // Token subject must match provider address to pass verification.
                LaqiraLogger::log(200, 'security', 'jwt_verified');
                return 'verified';
            }
            LaqiraLogger::log(300, 'security', 'jwt_failed');
            return 'failed';
        }
        LaqiraLogger::log(300, 'security', 'jwt_missing');
        return 'invalid';
    }

    public function verifyJwt($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) { // JWT must have header, payload, and signature.
            LaqiraLogger::log(400, 'security', 'jwt_format_error');
            return new WP_Error('jwt_format_error', esc_html__('JWT token format is invalid', 'laqirapay'), ['status' => 401]);
        }

        [$header64, $payload64, $signature64] = $parts;
        $data = "$header64.$payload64";
        $expected_signature = $this->base64urlEncode(
            hash_hmac(LAQIRAPAY_JWT_ALG_SIGNATURE, $data, $this->laqirapayGetJwtSecret(), true)
        );

        if (!hash_equals($expected_signature, $signature64)) { // Constant-time compare prevents timing attacks.
            LaqiraLogger::log(400, 'security', 'jwt_invalid_signature');
            return new WP_Error('jwt_invalid_signature', esc_html__('Signature mismatch', 'laqirapay'), ['status' => 401]);
        }

        $payload = json_decode($this->base64urlDecode($payload64));
        if (!isset($payload->exp) || $payload->exp < time()) {
            LaqiraLogger::log(300, 'security', 'jwt_expired');
            return new WP_Error('jwt_expired', esc_html__('JWT token has expired', 'laqirapay'), ['status' => 401]);
        }

        LaqiraLogger::log(200, 'security', 'jwt_payload_valid');
        return $payload;
    }

    private function base64urlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64urlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
