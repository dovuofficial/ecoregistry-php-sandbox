<?php
declare(strict_types=1);

namespace Ecoregistry\Tests\Integration;

use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;
use PHPUnit\Framework\TestCase;

final class FrontServiceTest extends TestCase
{
    private static EcoRegistry $eco;

    public static function setUpBeforeClass(): void
    {
        $dotenv = parse_ini_file(__DIR__ . '/../../.env') ?: [];
        self::$eco = new EcoRegistry(Config::fromArray([
            'base_url' => $dotenv['ECOREGISTRY_BASE_URL'],
            'email' => $dotenv['AUTH_EMAIL'],
            'api_key' => $dotenv['TOKEN_API_EXCHANGES'],
        ]));
    }

    public function test_projects_returns_many(): void
    {
        $result = self::$eco->front()->projects();
        $this->assertArrayHasKey('projects', $result);
        $this->assertGreaterThan(100, count($result['projects']));
    }

    public function test_project_by_id(): void
    {
        $result = self::$eco->front()->project(224);
        $this->assertEquals('Savimbo Biodiversity Putumayo', $result['project']['name']);
    }

    public function test_project_by_code(): void
    {
        $result = self::$eco->front()->project('CDB-1');
        $this->assertEquals('Savimbo Biodiversity Putumayo', $result['project']['name']);
    }
}
