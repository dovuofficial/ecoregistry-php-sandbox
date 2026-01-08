<?php

declare(strict_types=1);

namespace Ecoregistry;

use Ecoregistry\Endpoints\Endpoint;
use Ecoregistry\Endpoints\EndpointRegistry;
use Ecoregistry\Http\ApiClient;

final class EcoregistryApi
{
    private EndpointRegistry $registry;

    public function __construct(string $baseUrl, ?string $apiSecret, string $endpointPath)
    {
        $client = new ApiClient($baseUrl, $apiSecret);
        $this->registry = new EndpointRegistry($client, $endpointPath);
    }

    public function endpoint(string $name): Endpoint
    {
        return $this->registry->get($name);
    }

    /** @return array<string, Endpoint> */
    public function endpoints(): array
    {
        return $this->registry->all();
    }

    public function __get(string $name): Endpoint
    {
        return $this->endpoint($name);
    }
}
