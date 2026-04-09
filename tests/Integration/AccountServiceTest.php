<?php
declare(strict_types=1);

namespace Ecoregistry\Tests\Integration;

use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;
use PHPUnit\Framework\TestCase;

final class AccountServiceTest extends TestCase
{
    private static EcoRegistry $eco;

    public static function setUpBeforeClass(): void
    {
        $dotenv = parse_ini_file(__DIR__ . '/../../.env') ?: [];
        self::$eco = new EcoRegistry(Config::fromArray([
            'base_url' => $dotenv['ECOREGISTRY_BASE_URL'],
            'email' => $dotenv['AUTH_EMAIL'],
            'api_key' => $dotenv['TOKEN_API_EXCHANGES'],
            'platform_token' => $dotenv['PLATFORM_TOKEN'] ?? null,
        ]));
    }

    public function test_positions(): void
    {
        $result = self::$eco->account()->positions();
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals(1, $result['status']);
    }
}
