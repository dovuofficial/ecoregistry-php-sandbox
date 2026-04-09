<?php
declare(strict_types=1);

namespace Ecoregistry\Laravel;

use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;
use Illuminate\Support\ServiceProvider;

class EcoRegistryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/ecoregistry.php', 'ecoregistry');

        $this->app->singleton(EcoRegistry::class, function ($app) {
            return new EcoRegistry(Config::fromArray($app['config']['ecoregistry']));
        });

        $this->app->alias(EcoRegistry::class, 'ecoregistry');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/ecoregistry.php' => config_path('ecoregistry.php'),
        ], 'ecoregistry-config');
    }
}
