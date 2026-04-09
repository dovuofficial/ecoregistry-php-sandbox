<?php
declare(strict_types=1);

namespace Ecoregistry\Tests\Unit;

use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;
use Ecoregistry\Services\AccountService;
use Ecoregistry\Services\ExchangeService;
use Ecoregistry\Services\FrontService;
use Ecoregistry\Services\MarketplaceService;
use Ecoregistry\Services\PlatformService;
use PHPUnit\Framework\TestCase;

final class EcoRegistryTest extends TestCase
{
    private EcoRegistry $eco;

    protected function setUp(): void
    {
        $config = Config::fromArray([
            'email' => 'test@example.com',
            'api_key' => 'test-key',
            'platform_token' => 'platform-token',
        ]);

        $this->eco = new EcoRegistry($config);
    }

    public function test_account_returns_account_service(): void
    {
        $this->assertInstanceOf(AccountService::class, $this->eco->account());
    }

    public function test_platform_returns_platform_service(): void
    {
        $this->assertInstanceOf(PlatformService::class, $this->eco->platform());
    }

    public function test_front_returns_front_service(): void
    {
        $this->assertInstanceOf(FrontService::class, $this->eco->front());
    }

    public function test_exchange_returns_exchange_service(): void
    {
        $this->assertInstanceOf(ExchangeService::class, $this->eco->exchange());
    }

    public function test_marketplace_returns_marketplace_service(): void
    {
        $this->assertInstanceOf(MarketplaceService::class, $this->eco->marketplace());
    }

    public function test_services_are_singletons(): void
    {
        $this->assertSame($this->eco->account(), $this->eco->account());
        $this->assertSame($this->eco->platform(), $this->eco->platform());
        $this->assertSame($this->eco->front(), $this->eco->front());
    }

    public function test_platform_throws_without_token(): void
    {
        $config = Config::fromArray([
            'email' => 'test@example.com',
            'api_key' => 'test-key',
        ]);
        $eco = new EcoRegistry($config);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('platform_token');
        $eco->platform();
    }
}
