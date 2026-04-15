<?php

declare(strict_types=1);

namespace Ecoregistry\Tests\Integration;

use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Test credit retirement on UAT environment.
 */
final class RetirementTest extends TestCase
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
            // user API key not needed — admin token is used for both headers
        ]));
    }

    public function test_retire_1_credit(): void
    {
        $result = self::$eco->exchange()->auth()->retirement()
            ->serial('CDC_18_5_11_321_14_XX_XA_CO_1_1_2021')
            ->quantity(1)
            ->voluntaryCompensation()
            ->endUser(
                name: 'DOVU Market',
                countryId: 230,
                documentTypeId: 1,
                documentNumber: '267167674',
            )
            ->observation('Test retirement via DOVU EcoRegistry PHP SDK')
            ->execute();

        fwrite(STDERR, "\n── RETIREMENT RESULT ──\n");
        fwrite(STDERR, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('transactionId', $result);
        $this->assertArrayHasKey('urlPDF', $result);
        $this->assertEquals(1, $result['data']['quantity'] ?? null);
    }
}
