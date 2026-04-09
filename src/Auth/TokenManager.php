<?php
declare(strict_types=1);

namespace Ecoregistry\Auth;

use Ecoregistry\Http\ApiClient;

final class TokenManager
{
    private ?string $token = null;

    public function __construct(
        private ApiClient $client,
        private string $email,
        private string $apiKey,
    ) {
    }

    public function getToken(): string
    {
        if ($this->token === null) {
            $this->token = $this->fetchToken();
        }

        return $this->token;
    }

    public function refresh(): void
    {
        $this->token = null;
    }

    private function fetchToken(): string
    {
        $response = $this->client->request(
            'POST',
            '/api-account/v1/auth',
            [],
            ['email' => $this->email, 'apiKey' => $this->apiKey],
            ['platform: ecoregistry']
        );

        if ($response['status'] !== 200 || !isset($response['data']['token'])) {
            throw new \RuntimeException(
                'Auth failed: ' . json_encode($response['data'] ?? $response)
            );
        }

        return $response['data']['token'];
    }
}
