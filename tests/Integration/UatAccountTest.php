<?php

declare(strict_types=1);

namespace Ecoregistry\Tests\Integration;

use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Test account auth and positions on the UAT environment.
 */
final class UatAccountTest extends TestCase
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
        ]));
    }

    public function test_account_auth_on_uat(): void
    {
        $positions = self::$eco->account()->positions();

        fwrite(STDERR, "\n── UAT account.positions ──\n");
        fwrite(STDERR, json_encode($positions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n");

        $this->assertIsArray($positions);
    }
}
