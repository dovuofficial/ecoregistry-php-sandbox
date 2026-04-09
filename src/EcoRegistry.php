<?php
declare(strict_types=1);

namespace Ecoregistry;

use Ecoregistry\Auth\TokenManager;
use Ecoregistry\Http\ApiClient;
use Ecoregistry\Services\AccountService;
use Ecoregistry\Services\ExchangeService;
use Ecoregistry\Services\FrontService;
use Ecoregistry\Services\MarketplaceService;
use Ecoregistry\Services\PlatformService;

/**
 * EcoRegistry API client — fluent interface for all EcoRegistry APIs.
 *
 * Usage:
 *   $eco = new EcoRegistry(Config::fromArray([...]))
 *   $eco->platform()->projects();
 *   $eco->front()->project('CDB-1');
 *   $eco->account()->positions();
 */
final class EcoRegistry
{
    private ApiClient $client;
    private ApiClient $frontClient;
    private TokenManager $tokens;
    private Config $config;

    private ?AccountService $account = null;
    private ?PlatformService $platform = null;
    private ?FrontService $front = null;
    private ?ExchangeService $exchange = null;
    private ?MarketplaceService $marketplace = null;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->client = new ApiClient($config->baseUrl);
        $this->frontClient = new ApiClient($config->frontUrl);
        $this->tokens = new TokenManager($this->client, $config->email, $config->apiKey);
    }

    /** Account Information API — balances and positions. */
    public function account(): AccountService
    {
        return $this->account ??= new AccountService($this->client, $this->tokens);
    }

    /**
     * Platform Information API — public project data, sectors, industries, retirements.
     *
     * @throws \RuntimeException if platform_token is not configured
     */
    public function platform(): PlatformService
    {
        if ($this->config->platformToken === null) {
            throw new \RuntimeException(
                'Platform API requires "platform_token" in config. '
                . 'Obtain one via the platform registration endpoint.'
            );
        }

        return $this->platform ??= new PlatformService($this->client, $this->config->platformToken);
    }

    /** Frontend API — richer project detail with media and DMRV. */
    public function front(): FrontService
    {
        return $this->front ??= new FrontService($this->frontClient, $this->tokens);
    }

    /** Exchange API — credit transfers, retirements, locking (requires exchange registration). */
    public function exchange(): ExchangeService
    {
        return $this->exchange ??= new ExchangeService(
            $this->client,
            $this->config->exchangeUsername,
            $this->config->exchangePassword,
            $this->config->exchangeName,
            $this->config->exchangeUserApiKey,
        );
    }

    /** Marketplace API — credit retirement for marketplace integrations. */
    public function marketplace(): MarketplaceService
    {
        return $this->marketplace ??= new MarketplaceService(
            $this->client,
            $this->config->marketplaceName,
            $this->config->marketplacePassword,
        );
    }
}
