<?php
declare(strict_types=1);

namespace Ecoregistry\Tests\Unit;

use Ecoregistry\Auth\TokenManager;
use Ecoregistry\Http\ApiClient;
use PHPUnit\Framework\TestCase;

final class TokenManagerTest extends TestCase
{
    public function test_fetches_token_on_first_call(): void
    {
        $client = $this->createMock(ApiClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/api-account/v1/auth', [], [
                'email' => 'test@example.com',
                'apiKey' => 'secret',
            ], ['platform: ecoregistry'])
            ->willReturn([
                'status' => 200,
                'data' => ['token' => 'jwt.token.here'],
            ]);

        $manager = new TokenManager($client, 'test@example.com', 'secret');
        $token = $manager->getToken();

        $this->assertSame('jwt.token.here', $token);
    }

    public function test_caches_token_on_subsequent_calls(): void
    {
        $client = $this->createMock(ApiClient::class);
        $client->expects($this->once())
            ->method('request')
            ->willReturn([
                'status' => 200,
                'data' => ['token' => 'jwt.token.here'],
            ]);

        $manager = new TokenManager($client, 'test@example.com', 'secret');
        $manager->getToken();
        $token = $manager->getToken();

        $this->assertSame('jwt.token.here', $token);
    }

    public function test_throws_on_auth_failure(): void
    {
        $client = $this->createMock(ApiClient::class);
        $client->method('request')->willReturn([
            'status' => 401,
            'data' => ['error' => 'Unauthorized'],
        ]);

        $manager = new TokenManager($client, 'test@example.com', 'wrong');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Auth failed');
        $manager->getToken();
    }

    public function test_refresh_forces_new_token(): void
    {
        $client = $this->createMock(ApiClient::class);
        $client->expects($this->exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls(
                ['status' => 200, 'data' => ['token' => 'first']],
                ['status' => 200, 'data' => ['token' => 'second']],
            );

        $manager = new TokenManager($client, 'test@example.com', 'secret');
        $this->assertSame('first', $manager->getToken());

        $manager->refresh();
        $this->assertSame('second', $manager->getToken());
    }
}
