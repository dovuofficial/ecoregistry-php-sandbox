<?php
declare(strict_types=1);

namespace Ecoregistry\Tests\Unit;

use Ecoregistry\Http\ApiClient;
use Ecoregistry\Services\PlatformService;
use PHPUnit\Framework\TestCase;

final class PlatformServiceTest extends TestCase
{
    private ApiClient $client;
    private PlatformService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ApiClient::class);
        $this->service = new PlatformService($this->client, 'platform-token-123');
    }

    public function test_projects_calls_correct_endpoint(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/api-public/v1/projects', [], null, [
                'platform: ecoregistry',
                'x-api-key: platform-token-123',
                'lng: en',
            ])
            ->willReturn(['status' => 200, 'data' => ['status' => true, 'project' => []]]);

        $result = $this->service->projects();
        $this->assertSame(['status' => true, 'project' => []], $result);
    }

    public function test_project_info_substitutes_id(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/api-public/v1/project-info/224', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['project' => ['name' => 'Savimbo']]]);

        $result = $this->service->project(224);
        $this->assertSame(['project' => ['name' => 'Savimbo']], $result);
    }

    public function test_sectors(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/api-public/v1/get-sectors', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['sectors' => []]]);

        $result = $this->service->sectors();
        $this->assertSame(['sectors' => []], $result);
    }

    public function test_industries(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/api-public/v1/get-industries', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['end_beneficiary_industries' => []]]);

        $result = $this->service->industries();
        $this->assertSame(['end_beneficiary_industries' => []], $result);
    }

    public function test_withdrawals(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/api-public/v1/withdrawals', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['withdrawals' => []]]);

        $result = $this->service->withdrawals();
        $this->assertSame(['withdrawals' => []], $result);
    }

    public function test_shapes(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/api-public/v1/shapes-project/224', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['url' => 'https://s3...']]);

        $result = $this->service->shapes(224);
        $this->assertSame(['url' => 'https://s3...'], $result);
    }
}
