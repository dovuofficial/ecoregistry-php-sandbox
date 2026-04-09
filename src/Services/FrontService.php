<?php
declare(strict_types=1);

namespace Ecoregistry\Services;

use Ecoregistry\Auth\TokenManager;
use Ecoregistry\Http\ApiClient;

/**
 * Frontend API (api-front.ecoregistry.io) — richer project data than Platform API.
 *
 * Returns project detail with media, DMRV links, certification timeline, ratings.
 * Uses a separate base URL and account JWT auth.
 */
final class FrontService
{
    public function __construct(
        private ApiClient $client,
        private TokenManager $tokens,
    ) {
    }

    /** List all public projects in the registry. */
    public function projects(string $lang = 'en'): array
    {
        return $this->get('/platform/project/public', $lang);
    }

    /**
     * Get full project detail by numeric ID or project code.
     *
     * @param int|string $id Numeric ID (224) or code ('CDB-1')
     */
    public function project(int|string $id, string $lang = 'en'): array
    {
        return $this->get("/platform/project/public/{$id}", $lang);
    }

    private function get(string $path, string $lang): array
    {
        $response = $this->client->request('GET', $path, [], null, [
            'platform: ecoregistry',
            'Authorization: Bearer ' . $this->tokens->getToken(),
            'lng: ' . $lang,
        ]);

        return $response['data'];
    }
}
