<?php
declare(strict_types=1);

namespace Ecoregistry\Services;

use Ecoregistry\Http\ApiClient;

/**
 * Marketplace API — credit retirement and project listing for marketplace integrations.
 *
 * Requires marketplace onboarding with EcoRegistry.
 *
 * @see https://ecoregistry.gitbook.io/ecoregistry-documentation/getting-started/marketplace/using-the-endpoints
 */
final class MarketplaceService
{
    private ?string $adminToken = null;

    public function __construct(
        private ApiClient $client,
        private ?string $marketplaceName = null,
        private ?string $password = null,
    ) {
    }

    /** Authenticate and get admin token (valid ~5 minutes). */
    public function auth(): self
    {
        if (!$this->marketplaceName || !$this->password) {
            throw new \RuntimeException('Marketplace credentials not configured.');
        }

        $response = $this->client->request('POST', '/marketplace/v1/authAdmin', [], [
            'marketplace_name' => $this->marketplaceName,
            'password' => $this->password,
        ], ['platform: ecoregistry']);

        if ($response['status'] !== 200 || !isset($response['data']['token'])) {
            throw new \RuntimeException('Marketplace auth failed: ' . json_encode($response['data']));
        }

        $this->adminToken = $response['data']['token'];
        return $this;
    }

    /** Retire carbon credits. Returns PDF URL and transaction ID. */
    public function retire(array $retirementData, string $lang = 'en'): array
    {
        return $this->post('/marketplace/v1/retirement', $retirementData, $lang);
    }

    /** Get retirement certificate PDF URL for a transaction. */
    public function certificationPdf(string $transactionId, string $lang = 'en'): array
    {
        return $this->get("/marketplace/v1/emit-certification-pdf/{$transactionId}", $lang);
    }

    /** Get active projects and balances in a marketplace. */
    public function activeProjects(int $marketplaceId, string $lang = 'en'): array
    {
        return $this->get("/marketplace/{$marketplaceId}/projectActives", $lang);
    }

    /** List countries in the registry. */
    public function countries(string $lang = 'en'): array
    {
        return $this->get('/marketplace/v1/get-countries', $lang);
    }

    /** List document types in the registry. */
    public function documentTypes(string $lang = 'en'): array
    {
        return $this->get('/marketplace/v1/type-documents', $lang);
    }

    /** Get carbon offset usage reasons. */
    public function reasonsForUse(string $lang = 'en'): array
    {
        return $this->get('/marketplace/v1/reason-using', $lang);
    }

    /** Check which offset usage reasons apply to a serial. */
    public function serialEligibility(string $serial, string $lang = 'en'): array
    {
        return $this->post('/marketplace/v1/serial-eligible', ['serial' => $serial], $lang);
    }

    private function headers(string $lang): array
    {
        if (!$this->adminToken) {
            throw new \RuntimeException('Call auth() first.');
        }
        return ['platform: ecoregistry', 'x-api-key-admin: ' . $this->adminToken, 'lng: ' . $lang];
    }

    private function get(string $path, string $lang): array
    {
        return $this->client->request('GET', $path, [], null, $this->headers($lang))['data'];
    }

    private function post(string $path, array $body, string $lang): array
    {
        return $this->client->request('POST', $path, [], $body, $this->headers($lang))['data'];
    }
}
