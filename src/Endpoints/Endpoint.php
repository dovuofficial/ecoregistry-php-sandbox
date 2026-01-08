<?php

declare(strict_types=1);

namespace Ecoregistry\Endpoints;

use Ecoregistry\Http\ApiClient;

final class Endpoint
{
    public function __construct(
        private ApiClient $client,
        private string $name,
        private string $method,
        private string $path,
        private ?string $description = null
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function call(array $query = [], ?array $body = null, array $headers = []): array
    {
        return $this->client->request($this->method, $this->path, $query, $body, $headers);
    }
}
