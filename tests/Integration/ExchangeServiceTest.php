<?php

declare(strict_types=1);

namespace Ecoregistry\Tests\Integration;

use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests against the EcoRegistry UAT/dev environment.
 * Tests the Exchange API with real exchange credentials.
 */
final class ExchangeServiceTest extends TestCase
{
    private static array $dotenv;
    private static EcoRegistry $eco;

    public static function setUpBeforeClass(): void
    {
        self::$dotenv = parse_ini_file(__DIR__ . '/../../.env') ?: [];

        self::$eco = new EcoRegistry(Config::fromArray([
            'base_url' => self::$dotenv['UAT_BASE_URL'],
            'email' => self::$dotenv['UAT_EMAIL'],
            'api_key' => self::$dotenv['UAT_API_KEY'],
            'exchange_username' => self::$dotenv['UAT_EXCHANGE_USERNAME'],
            'exchange_password' => self::$dotenv['UAT_EXCHANGE_PASSWORD'],
            'exchange_name' => self::$dotenv['UAT_EXCHANGE_NAME'],
        ]));
    }

    public function test_exchange_auth(): void
    {
        $exchange = self::$eco->exchange()->auth();
        $this->assertNotNull($exchange);
    }

    /** @depends test_exchange_auth */
    public function test_exchange_projects(): void
    {
        $exchange = self::$eco->exchange()->auth();
        $result = $exchange->projects();

        fwrite(STDERR, "\n── exchange.projects ──\n");
        fwrite(STDERR, "Count: " . (is_array($result) ? count($result) : 'N/A') . "\n");
        $preview = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (strlen($preview) > 1000) $preview = substr($preview, 0, 1000) . "\n...";
        fwrite(STDERR, $preview . "\n");

        $this->assertIsArray($result);
    }

    /** @depends test_exchange_auth */
    public function test_exchange_companies(): void
    {
        $exchange = self::$eco->exchange()->auth();
        $result = $exchange->companies();

        fwrite(STDERR, "\n── exchange.companies ──\n");
        fwrite(STDERR, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n");

        $this->assertIsArray($result);
    }

    /** @depends test_exchange_auth */
    public function test_exchange_positions(): void
    {
        $exchange = self::$eco->exchange()->auth();
        $result = $exchange->positions();

        fwrite(STDERR, "\n── exchange.positions ──\n");
        fwrite(STDERR, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n");

        $this->assertIsArray($result);
    }
}
