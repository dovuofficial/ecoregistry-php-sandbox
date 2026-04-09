<?php
declare(strict_types=1);

namespace Ecoregistry\Tests\Unit;

use Ecoregistry\Auth\TokenManager;
use Ecoregistry\Http\ApiClient;
use Ecoregistry\Services\AccountService;
use PHPUnit\Framework\TestCase;

final class AccountServiceTest extends TestCase
{
    private ApiClient $client;
    private TokenManager $tokens;
    private AccountService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ApiClient::class);
        $this->tokens = $this->createMock(TokenManager::class);
        $this->tokens->method('getToken')->willReturn('test-jwt');
        $this->service = new AccountService($this->client, $this->tokens);
    }

    public function test_positions_calls_correct_endpoint(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/api-account/v1/positions',
                [],
                null,
                [
                    'platform: ecoregistry',
                    'Authorization: Bearer test-jwt',
                    'lng: en',
                ]
            )
            ->willReturn(['status' => 200, 'data' => ['status' => 1, 'projects' => []]]);

        $result = $this->service->positions();
        $this->assertSame(['status' => 1, 'projects' => []], $result);
    }

    public function test_positions_with_language(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/api-account/v1/positions',
                [],
                null,
                [
                    'platform: ecoregistry',
                    'Authorization: Bearer test-jwt',
                    'lng: es',
                ]
            )
            ->willReturn(['status' => 200, 'data' => ['status' => 1, 'projects' => []]]);

        $result = $this->service->positions('es');
        $this->assertSame(['status' => 1, 'projects' => []], $result);
    }
}
