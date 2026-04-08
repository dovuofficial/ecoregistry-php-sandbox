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

    public function call(array $params = [], array $query = [], ?array $body = null, array $headers = []): array
    {
        $path = $this->path;
        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', (string) $value, $path);
        }

        return $this->client->request($this->method, $path, $query, $body, $headers);
    }
}
