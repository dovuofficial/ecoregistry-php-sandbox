<?php
declare(strict_types=1);

namespace Ecoregistry\Tests\Unit;

use Ecoregistry\Auth\TokenManager;
use Ecoregistry\Http\ApiClient;
use Ecoregistry\Services\FrontService;
use PHPUnit\Framework\TestCase;

final class FrontServiceTest extends TestCase
{
    private ApiClient $client;
    private TokenManager $tokens;
    private FrontService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ApiClient::class);
        $this->tokens = $this->createMock(TokenManager::class);
        $this->tokens->method('getToken')->willReturn('test-jwt');
        $this->service = new FrontService($this->client, $this->tokens);
    }

    public function test_projects_calls_correct_endpoint(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/platform/project/public', [], null, [
                'platform: ecoregistry',
                'Authorization: Bearer test-jwt',
                'lng: en',
            ])
            ->willReturn(['status' => 200, 'data' => ['status' => 1, 'projects' => []]]);

        $result = $this->service->projects();
        $this->assertSame(['status' => 1, 'projects' => []], $result);
    }

    public function test_project_by_id(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/platform/project/public/224', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['project' => ['name' => 'Savimbo']]]);

        $result = $this->service->project(224);
        $this->assertSame(['project' => ['name' => 'Savimbo']], $result);
    }

    public function test_project_by_code(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/platform/project/public/CDB-1', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['project' => ['code' => 'CDB-1']]]);

        $result = $this->service->project('CDB-1');
        $this->assertSame(['project' => ['code' => 'CDB-1']], $result);
    }
}
