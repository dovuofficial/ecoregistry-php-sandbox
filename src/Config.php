<?php
declare(strict_types=1);

namespace Ecoregistry;

final class Config
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $frontUrl,
        public readonly string $email,
        public readonly string $apiKey,
        public readonly ?string $platformToken = null,
        public readonly ?string $exchangeUsername = null,
        public readonly ?string $exchangePassword = null,
        public readonly ?string $exchangeName = null,
        public readonly ?string $marketplaceName = null,
        public readonly ?string $marketplacePassword = null,
    ) {
    }

    public static function fromArray(array $values): self
    {
        if (!isset($values['email'])) {
            throw new \InvalidArgumentException('Config requires "email"');
        }
        if (!isset($values['api_key'])) {
            throw new \InvalidArgumentException('Config requires "api_key"');
        }

        return new self(
            baseUrl: $values['base_url'] ?? 'https://api-external.ecoregistry.io/api',
            frontUrl: $values['front_url'] ?? 'https://api-front.ecoregistry.io',
            email: $values['email'],
            apiKey: $values['api_key'],
            platformToken: $values['platform_token'] ?? null,
            exchangeUsername: $values['exchange_username'] ?? null,
            exchangePassword: $values['exchange_password'] ?? null,
            exchangeName: $values['exchange_name'] ?? null,
            marketplaceName: $values['marketplace_name'] ?? null,
            marketplacePassword: $values['marketplace_password'] ?? null,
        );
    }
}
