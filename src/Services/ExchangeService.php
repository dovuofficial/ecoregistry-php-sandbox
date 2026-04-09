<?php
declare(strict_types=1);

namespace Ecoregistry\Services;

use Ecoregistry\Http\ApiClient;

/**
 * Exchange Connection API — credit transfers, retirements, locking.
 *
 * Requires DOVU to be registered as an exchange with EcoRegistry.
 * Contact contacto@ecoregistry.io to request exchange credentials.
 *
 * @see https://ecoregistry.gitbook.io/ecoregistry-documentation/getting-started/exchanges-connection/using-the-endpoints/admin-endpoints
 * @see https://ecoregistry.gitbook.io/ecoregistry-documentation/getting-started/exchanges-connection/using-the-endpoints/user-endpoints
 */
final class ExchangeService
{
    private ?string $adminToken = null;

    public function __construct(
        private ApiClient $client,
        private ?string $username = null,
        private ?string $password = null,
        private ?string $exchangeName = null,
    ) {
    }

    /** Authenticate and get admin token (valid ~5 minutes). Must be called before other methods. */
    public function auth(): self
    {
        $this->requireCredentials();
        $response = $this->client->request('POST', '/api-exchange-v2/v2/auth', [], [
            'user_name' => $this->username,
            'password' => $this->password,
            'name_exchange' => $this->exchangeName,
        ], ['platform: ecoregistry']);

        if ($response['status'] !== 200 || !isset($response['data']['token'])) {
            throw new \RuntimeException('Exchange auth failed: ' . json_encode($response['data']));
        }

        $this->adminToken = $response['data']['token'];
        return $this;
    }

    /** Get all available projects. */
    public function projects(string $lang = 'en'): array
    {
        return $this->adminGet('/api-exchange-v2/v2/get-all-project', $lang);
    }

    /** Get specific project by ID. */
    public function project(int $projectId, string $lang = 'en'): array
    {
        return $this->adminPost('/api-exchange-v2/v2/get-project', ['project_id' => $projectId], $lang);
    }

    /** Get active account holders on the exchange. */
    public function companies(string $lang = 'en'): array
    {
        return $this->adminGet('/api-exchange-v2/v2/get-companies', $lang);
    }

    /** Get all company balances linked to the exchange. */
    public function positions(string $lang = 'en'): array
    {
        return $this->adminGet('/api-exchange-v2/v2/get-all-positions', $lang);
    }

    /** Lock credits inside the exchange. */
    public function lock(string $serial, int $quantity, string $lang = 'en'): array
    {
        return $this->userPost('/api-exchange-v2/v2/lock-serial', [
            'serial' => $serial, 'quantity' => $quantity,
        ], $lang);
    }

    /** Unlock credits inside the exchange. */
    public function unlock(string $serial, int $quantity, string $lang = 'en'): array
    {
        return $this->userPost('/api-exchange-v2/v2/unlock-serial', [
            'serial' => $serial, 'quantity' => $quantity,
        ], $lang);
    }

    /** Transfer credits between accounts linked to the exchange. */
    public function transfer(string $companyId, string $serial, int $quantity, string $lang = 'en'): array
    {
        return $this->userPost('/api-exchange-v2/v2/transfer-between', [
            'company_id' => $companyId, 'serial' => $serial,
            'quantity' => $quantity, 'asset_type' => 'carbon offset',
        ], $lang);
    }

    /** Retire credits in tonnes. Returns retirement PDF URL. */
    public function retire(array $retirementData, string $lang = 'en'): array
    {
        return $this->userPost('/api-exchange-v2/v2/retirement', $retirementData, $lang);
    }

    /** Retire credits in kilograms. Returns retirement PDF URL. */
    public function retireKg(array $retirementData, string $lang = 'en'): array
    {
        return $this->userPost('/api-exchange-v2/v2/retirement-kg', $retirementData, $lang);
    }

    /** Transfer credits back to EcoRegistry (burn in exchange). */
    public function transferToEcoregistry(string $serial, int $quantity, string $lang = 'en'): array
    {
        return $this->userPost('/api-exchange-v2/v2/transfer-to-ecoregistry', [
            'serial' => $serial, 'quantity' => $quantity,
            'name_exchange' => $this->exchangeName,
        ], $lang);
    }

    private function adminHeaders(string $lang): array
    {
        $this->requireToken();
        return ['platform: ecoregistry', 'x-api-key-admin: ' . $this->adminToken, 'lng: ' . $lang];
    }

    private function userHeaders(string $lang): array { return $this->adminHeaders($lang); }

    private function adminGet(string $path, string $lang): array
    {
        return $this->client->request('GET', $path, [], null, $this->adminHeaders($lang))['data'];
    }

    private function adminPost(string $path, array $body, string $lang): array
    {
        return $this->client->request('POST', $path, [], $body, $this->adminHeaders($lang))['data'];
    }

    private function userPost(string $path, array $body, string $lang): array
    {
        return $this->client->request('POST', $path, [], $body, $this->userHeaders($lang))['data'];
    }

    private function requireCredentials(): void
    {
        if (!$this->username || !$this->password || !$this->exchangeName) {
            throw new \RuntimeException(
                'Exchange credentials not configured. Contact contacto@ecoregistry.io to register as an exchange.'
            );
        }
    }

    private function requireToken(): void
    {
        if (!$this->adminToken) {
            throw new \RuntimeException('Call auth() first to obtain an exchange admin token.');
        }
    }
}
