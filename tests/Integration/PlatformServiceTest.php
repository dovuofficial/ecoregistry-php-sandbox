<?php
declare(strict_types=1);

namespace Ecoregistry\Tests\Integration;

use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;
use PHPUnit\Framework\TestCase;

final class PlatformServiceTest extends TestCase
{
    private static EcoRegistry $eco;

    public static function setUpBeforeClass(): void
    {
        $dotenv = parse_ini_file(__DIR__ . '/../../.env') ?: [];
        self::$eco = new EcoRegistry(Config::fromArray([
            'base_url' => $dotenv['ECOREGISTRY_BASE_URL'],
            'email' => $dotenv['AUTH_EMAIL'],
            'api_key' => $dotenv['TOKEN_API_EXCHANGES'],
            'platform_token' => $dotenv['PLATFORM_TOKEN'],
        ]));
    }

    public function test_projects_returns_list(): void
    {
        $result = self::$eco->platform()->projects();
        $this->assertArrayHasKey('project', $result);
        $this->assertNotEmpty($result['project']);
    }

    public function test_project_savimbo(): void
    {
        $result = self::$eco->platform()->project(224);
        $this->assertEquals('Savimbo Biodiversity Putumayo', $result['project']['name']);
        $this->assertEquals('Savimbo Inc.', $result['project']['owner']);
    }

    public function test_sectors(): void
    {
        $result = self::$eco->platform()->sectors();
        $this->assertArrayHasKey('sectors', $result);
        $this->assertNotEmpty($result['sectors']);
    }

    public function test_industries(): void
    {
        $result = self::$eco->platform()->industries();
        $this->assertNotEmpty($result);
    }

    public function test_withdrawals(): void
    {
        $result = self::$eco->platform()->withdrawals();
        $this->assertArrayHasKey('withdrawals', $result);
        $this->assertNotEmpty($result['withdrawals']);
    }
}
