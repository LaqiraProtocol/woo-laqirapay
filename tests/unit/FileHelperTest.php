<?php

namespace Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use LaqiraPay\Helpers\FileHelper;
use PHPUnit\Framework\TestCase;

class FileHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_get_contents_secure_enforces_ssl_verification_by_default(): void
    {
        $capturedArgs = null;

        Functions\when('apply_filters')->alias(fn($hook, $value, ...$args) => $value);
        Functions\when('is_wp_error')->alias(fn() => false);
        Functions\when('wp_remote_retrieve_response_code')->alias(fn($response) => $response['response']['code'] ?? null);
        Functions\when('wp_remote_retrieve_body')->alias(fn($response) => $response['body'] ?? '');

        Functions\when('wp_remote_get')->alias(function ($url, $args) use (&$capturedArgs) {
            $capturedArgs = $args;

            return [
                'response' => ['code' => 200],
                'body'     => 'payload',
            ];
        });

        $result = FileHelper::get_contents_secure('https://example.com');

        $this->assertSame('payload', $result);
        $this->assertIsArray($capturedArgs);
        $this->assertArrayHasKey('sslverify', $capturedArgs);
        $this->assertTrue($capturedArgs['sslverify']);
    }
}

