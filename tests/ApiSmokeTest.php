<?php

declare(strict_types=1);

use Ecoregistry\EcoregistryApi;
use PHPUnit\Framework\TestCase;

/**
 * Smoke tests against the EcoRegistry APIs.
 * Verifies connectivity, auth, project ingestion, and platform data.
 */
final class ApiSmokeTest extends TestCase
{
    private const FRONT_BASE = 'https://api-front.ecoregistry.io';

    private static array $dotenv;
    private static EcoregistryApi $api;
    private static string $accountToken = '';

    public static function setUpBeforeClass(): void
    {
        self::$dotenv = parse_ini_file(__DIR__ . '/../.env') ?: [];
        self::$api = new EcoregistryApi(
            baseUrl: self::$dotenv['ECOREGISTRY_BASE_URL'],
            apiSecret: null,
            endpointPath: __DIR__ . '/../endpoints'
        );
    }

    // ── Account Information API ──────────────────────────────────────

    public function test_account_auth_returns_jwt(): void
    {
        $response = self::$api->endpoint('account.auth')->call([], [], [
            'email' => self::$dotenv['AUTH_EMAIL'],
            'apiKey' => self::$dotenv['TOKEN_API_EXCHANGES'],
        ], ['platform: ecoregistry']);

        $this->assertEquals(200, $response['status']);
        $this->assertNotEmpty($response['data']['token'] ?? '');

        self::$accountToken = $response['data']['token'];
    }

    /** @depends test_account_auth_returns_jwt */
    public function test_account_positions(): void
    {
        $response = self::$api->endpoint('account.positions')->call([], [], null, [
            'platform: ecoregistry',
            'Authorization: Bearer ' . self::$accountToken,
            'lng: en',
        ]);

        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('data', $response);
    }

    // ── Platform Information API ─────────────────────────────────────

    public function test_platform_projects(): void
    {
        $response = self::$api->endpoint('platform.projects')->call(
            [], [], null, $this->platformHeaders()
        );

        $this->assertEquals(200, $response['status']);
        $projects = $response['data']['project'] ?? [];
        $this->assertNotEmpty($projects);
    }

    public function test_platform_project_info_savimbo(): void
    {
        $response = self::$api->endpoint('platform.project_info')->call(
            ['projectID' => 224], [], null, $this->platformHeaders()
        );

        $this->assertEquals(200, $response['status']);
        $project = $response['data']['project'] ?? $response['data'] ?? [];
        $this->assertEquals('Savimbo Biodiversity Putumayo', $project['name'] ?? '');
        $this->assertEquals('Savimbo Inc.', $project['owner'] ?? '');
    }

    public function test_platform_sectors(): void
    {
        $response = self::$api->endpoint('platform.sectors')->call(
            [], [], null, $this->platformHeaders()
        );

        $this->assertEquals(200, $response['status']);
    }

    public function test_platform_industries(): void
    {
        $response = self::$api->endpoint('platform.industries')->call(
            [], [], null, $this->platformHeaders()
        );

        $this->assertEquals(200, $response['status']);
    }

    public function test_platform_withdrawals(): void
    {
        $response = self::$api->endpoint('platform.withdrawals')->call(
            [], [], null, $this->platformHeaders()
        );

        $this->assertEquals(200, $response['status']);
        $this->assertNotEmpty($response['data']['withdrawals'] ?? []);
    }

    // ── Frontend API — project ingestion ─────────────────────────────

    /** @depends test_account_auth_returns_jwt */
    public function test_front_lists_public_projects(): void
    {
        $response = $this->frontGet('/platform/project/public');

        $this->assertEquals(200, $response['status']);
        $projects = $response['data']['projects'] ?? [];
        $this->assertGreaterThan(100, count($projects));
    }

    /** @depends test_account_auth_returns_jwt */
    public function test_front_savimbo_detail(): void
    {
        $response = $this->frontGet('/platform/project/public/224');

        $this->assertEquals(200, $response['status']);
        $project = $response['data']['project'] ?? [];
        $this->assertEquals('Savimbo Biodiversity Putumayo', $project['name'] ?? '');
    }

    // ── Exchange & Marketplace reachability ──────────────────────────

    public function test_exchange_auth_endpoint_reachable(): void
    {
        $response = self::$api->endpoint('exchange.auth')->call([], [], [
            'user_name' => 'probe',
            'password' => 'probe',
            'name_exchange' => 'probe',
        ], ['platform: ecoregistry']);

        $this->assertContains($response['status'], [400, 401]);
    }

    public function test_marketplace_auth_endpoint_reachable(): void
    {
        $response = self::$api->endpoint('marketplace.auth')->call([], [], [
            'marketplace_name' => 'probe',
            'username' => 'probe',
            'password' => 'probe',
        ], ['platform: ecoregistry']);

        $this->assertContains($response['status'], [400, 401]);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function platformHeaders(): array
    {
        return [
            'platform: ecoregistry',
            'x-api-key: ' . self::$dotenv['PLATFORM_TOKEN'],
            'lng: en',
        ];
    }

    private function frontGet(string $path): array
    {
        $curl = curl_init(self::FRONT_BASE . $path);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'platform: ecoregistry',
                'lng: en',
                'Authorization: Bearer ' . self::$accountToken,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $body = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return [
            'status' => $status,
            'data' => json_decode($body, true) ?? $body,
        ];
    }
}
