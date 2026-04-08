<?php

declare(strict_types=1);

use Ecoregistry\EcoregistryApi;
use PHPUnit\Framework\TestCase;

/**
 * Try platform endpoints with every token/key we have.
 */
final class PlatformProbeTest extends TestCase
{
    private static array $dotenv;
    private static EcoregistryApi $api;
    private static string $accountJwt = '';

    public static function setUpBeforeClass(): void
    {
        self::$dotenv = parse_ini_file(__DIR__ . '/../.env') ?: [];
        self::$api = new EcoregistryApi(
            baseUrl: self::$dotenv['ECOREGISTRY_BASE_URL'],
            apiSecret: null,
            endpointPath: __DIR__ . '/../endpoints'
        );

        // Get fresh account JWT
        $auth = self::$api->endpoint('account.auth')->call([], [], [
            'email' => self::$dotenv['AUTH_EMAIL'],
            'apiKey' => self::$dotenv['TOKEN_API_EXCHANGES'],
        ], ['platform: ecoregistry']);

        self::$accountJwt = $auth['data']['token'] ?? '';
    }

    public function test_platform_projects_with_exchange_token(): void
    {
        $response = self::$api->endpoint('platform.projects')->call([], [], null, [
            'platform: ecoregistry',
            'x-api-key: ' . self::$dotenv['TOKEN_API_EXCHANGES'],
            'lng: en',
        ]);

        $this->log('platform.projects (exchange token as x-api-key)', $response);
        $this->assertIsArray($response);
    }

    public function test_platform_projects_with_jwt(): void
    {
        $response = self::$api->endpoint('platform.projects')->call([], [], null, [
            'platform: ecoregistry',
            'x-api-key: ' . self::$accountJwt,
            'lng: en',
        ]);

        $this->log('platform.projects (JWT as x-api-key)', $response);
        $this->assertIsArray($response);
    }

    public function test_platform_projects_with_bearer(): void
    {
        $response = self::$api->endpoint('platform.projects')->call([], [], null, [
            'platform: ecoregistry',
            'Authorization: Bearer ' . self::$accountJwt,
            'lng: en',
        ]);

        $this->log('platform.projects (JWT as Bearer)', $response);
        $this->assertIsArray($response);
    }

    public function test_platform_projects_with_both(): void
    {
        $response = self::$api->endpoint('platform.projects')->call([], [], null, [
            'platform: ecoregistry',
            'x-api-key: ' . self::$dotenv['TOKEN_API_EXCHANGES'],
            'Authorization: Bearer ' . self::$accountJwt,
            'lng: en',
        ]);

        $this->log('platform.projects (both token + JWT)', $response);
        $this->assertIsArray($response);
    }

    public function test_platform_sectors_with_exchange_token(): void
    {
        $response = self::$api->endpoint('platform.sectors')->call([], [], null, [
            'platform: ecoregistry',
            'x-api-key: ' . self::$dotenv['TOKEN_API_EXCHANGES'],
            'lng: en',
        ]);

        $this->log('platform.sectors (exchange token)', $response);
        $this->assertIsArray($response);
    }

    public function test_platform_withdrawals_with_exchange_token(): void
    {
        $response = self::$api->endpoint('platform.withdrawals')->call([], [], null, [
            'platform: ecoregistry',
            'x-api-key: ' . self::$dotenv['TOKEN_API_EXCHANGES'],
            'lng: en',
        ]);

        $this->log('platform.withdrawals (exchange token)', $response);
        $this->assertIsArray($response);
    }

    private function log(string $label, array $response): void
    {
        $status = $response['status'] ?? '?';
        $preview = json_encode($response['data'] ?? $response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (strlen($preview) > 1200) {
            $preview = substr($preview, 0, 1200) . "\n... (truncated)";
        }
        fwrite(STDERR, "\n── {$label} [HTTP {$status}] ──\n{$preview}\n");
    }
}
