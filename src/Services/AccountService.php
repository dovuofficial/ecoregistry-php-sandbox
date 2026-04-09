<?php
declare(strict_types=1);

namespace Ecoregistry\Services;

use Ecoregistry\Auth\TokenManager;
use Ecoregistry\Http\ApiClient;

/**
 * Account Information API — balances and positions.
 *
 * @see https://ecoregistry.gitbook.io/ecoregistry-documentation/getting-started/account-information-api/account-endpoints
 */
final class AccountService
{
    public function __construct(
        private ApiClient $client,
        private TokenManager $tokens,
    ) {
    }

    /**
     * Get account balances (positions) including sub-accounts.
     *
     * @param string $lang Language code ('en' or 'es')
     * @return array Response data with 'status' and 'projects' keys
     */
    public function positions(string $lang = 'en'): array
    {
        return $this->authedRequest('GET', '/api-account/v1/positions', lang: $lang);
    }

    private function authedRequest(
        string $method,
        string $path,
        array $query = [],
        ?array $body = null,
        string $lang = 'en',
    ): array {
        $response = $this->client->request($method, $path, $query, $body, [
            'platform: ecoregistry',
            'Authorization: Bearer ' . $this->tokens->getToken(),
            'lng: ' . $lang,
        ]);

        return $response['data'];
    }
}
