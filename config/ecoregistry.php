<?php
return [
    'base_url' => env('ECOREGISTRY_BASE_URL', 'https://api-external.ecoregistry.io/api'),
    'front_url' => env('ECOREGISTRY_FRONT_URL', 'https://api-front.ecoregistry.io'),

    'email' => env('ECOREGISTRY_EMAIL'),
    'api_key' => env('ECOREGISTRY_API_KEY'),
    'platform_token' => env('ECOREGISTRY_PLATFORM_TOKEN'),

    // Exchange credentials (provided by EcoRegistry after exchange registration)
    'exchange_username' => env('ECOREGISTRY_EXCHANGE_USERNAME'),
    'exchange_password' => env('ECOREGISTRY_EXCHANGE_PASSWORD'),
    'exchange_name' => env('ECOREGISTRY_EXCHANGE_NAME'),

    // Marketplace credentials (provided by EcoRegistry after onboarding)
    'marketplace_name' => env('ECOREGISTRY_MARKETPLACE_NAME'),
    'marketplace_password' => env('ECOREGISTRY_MARKETPLACE_PASSWORD'),
];
