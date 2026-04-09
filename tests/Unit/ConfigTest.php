<?php
declare(strict_types=1);

namespace Ecoregistry\Tests\Unit;

use Ecoregistry\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function test_creates_from_array(): void
    {
        $config = Config::fromArray([
            'base_url' => 'https://api-external.ecoregistry.io/api',
            'front_url' => 'https://api-front.ecoregistry.io',
            'email' => 'matt@dovu.earth',
            'api_key' => 'abc123',
            'platform_token' => 'def456',
        ]);

        $this->assertSame('https://api-external.ecoregistry.io/api', $config->baseUrl);
        $this->assertSame('https://api-front.ecoregistry.io', $config->frontUrl);
        $this->assertSame('matt@dovu.earth', $config->email);
        $this->assertSame('abc123', $config->apiKey);
        $this->assertSame('def456', $config->platformToken);
    }

    public function test_defaults_for_optional_fields(): void
    {
        $config = Config::fromArray([
            'email' => 'matt@dovu.earth',
            'api_key' => 'abc123',
        ]);

        $this->assertSame('https://api-external.ecoregistry.io/api', $config->baseUrl);
        $this->assertSame('https://api-front.ecoregistry.io', $config->frontUrl);
        $this->assertNull($config->platformToken);
        $this->assertNull($config->exchangeUsername);
        $this->assertNull($config->exchangePassword);
        $this->assertNull($config->exchangeName);
    }

    public function test_throws_on_missing_email(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Config::fromArray(['api_key' => 'abc123']);
    }

    public function test_throws_on_missing_api_key(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Config::fromArray(['email' => 'matt@dovu.earth']);
    }
}
