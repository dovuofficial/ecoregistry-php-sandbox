<?php
declare(strict_types=1);

namespace Ecoregistry\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Ecoregistry\Services\AccountService account()
 * @method static \Ecoregistry\Services\PlatformService platform()
 * @method static \Ecoregistry\Services\FrontService front()
 * @method static \Ecoregistry\Services\ExchangeService exchange()
 * @method static \Ecoregistry\Services\MarketplaceService marketplace()
 *
 * @see \Ecoregistry\EcoRegistry
 */
class EcoRegistryFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ecoregistry';
    }
}
