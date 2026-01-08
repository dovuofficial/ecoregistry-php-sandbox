<?php

declare(strict_types=1);

namespace Ecoregistry\Endpoints;

use Ecoregistry\Http\ApiClient;

final class EndpointRegistry
{
    /** @var array<string, Endpoint> */
    private array $endpoints = [];

    public function __construct(ApiClient $client, string $endpointPath)
    {
        foreach ($this->loadDefinitions($endpointPath) as $definition) {
            $this->endpoints[$definition['name']] = new Endpoint(
                $client,
                $definition['name'],
                $definition['method'],
                $definition['path'],
                $definition['description'] ?? null
            );
        }
    }

    public function get(string $name): Endpoint
    {
        if (!isset($this->endpoints[$name])) {
            throw new \InvalidArgumentException("Unknown endpoint: {$name}");
        }

        return $this->endpoints[$name];
    }

    /** @return array<string, Endpoint> */
    public function all(): array
    {
        return $this->endpoints;
    }

    /** @return array<int, array{name: string, method: string, path: string, description?: string}> */
    private function loadDefinitions(string $endpointPath): array
    {
        if (!is_dir($endpointPath)) {
            throw new \RuntimeException("Endpoint directory not found: {$endpointPath}");
        }

        $definitions = [];
        $files = glob(rtrim($endpointPath, '/') . '/*.php') ?: [];
        foreach ($files as $file) {
            $data = include $file;
            if (!is_array($data)) {
                throw new \RuntimeException("Endpoint file must return an array: {$file}");
            }
            foreach ($data as $definition) {
                if (!isset($definition['name'], $definition['method'], $definition['path'])) {
                    throw new \RuntimeException("Endpoint definition missing required keys in {$file}");
                }
                $definitions[] = $definition;
            }
        }

        return $definitions;
    }
}
