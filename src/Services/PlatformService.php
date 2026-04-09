<?php
declare(strict_types=1);

namespace Ecoregistry\Services;

use Ecoregistry\Http\ApiClient;

/**
 * Platform Information API — public project data, sectors, industries, retirements.
 *
 * Uses a static platform token (x-api-key header), no JWT needed.
 *
 * @see https://ecoregistry.gitbook.io/ecoregistry-documentation/getting-started/platform-information-api/information-endpoints
 */
final class PlatformService
{
    public function __construct(
        private ApiClient $client,
        private string $platformToken,
    ) {
    }

    /** List all publicly available projects with serials and locations. */
    public function projects(string $lang = 'en'): array
    {
        return $this->get('/api-public/v1/projects', $lang);
    }

    /** Get detailed info for a specific project by numeric ID. */
    public function project(int $id, string $lang = 'en'): array
    {
        return $this->get("/api-public/v1/project-info/{$id}", $lang);
    }

    /** Get cartographic/geographic data URL for a project. */
    public function shapes(int $projectId, string $lang = 'en'): array
    {
        return $this->get("/api-public/v1/shapes-project/{$projectId}", $lang);
    }

    /** List all carbon credit retirements (withdrawals) in the registry. */
    public function withdrawals(string $lang = 'en'): array
    {
        return $this->get('/api-public/v1/withdrawals', $lang);
    }

    /** List all active project sectors. */
    public function sectors(string $lang = 'en'): array
    {
        return $this->get('/api-public/v1/get-sectors', $lang);
    }

    /** List all beneficiary industries eligible for carbon credit programs. */
    public function industries(string $lang = 'en'): array
    {
        return $this->get('/api-public/v1/get-industries', $lang);
    }

    private function get(string $path, string $lang): array
    {
        $response = $this->client->request('GET', $path, [], null, [
            'platform: ecoregistry',
            'x-api-key: ' . $this->platformToken,
            'lng: ' . $lang,
        ]);

        return $response['data'];
    }
}
