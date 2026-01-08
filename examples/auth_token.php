<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Ecoregistry\EcoregistryApi;

$api = new EcoregistryApi(
    baseUrl: 'https://api.ecoregistry.example',
    apiSecret: getenv('ECOREGISTRY_API_SECRET') ?: null,
    endpointPath: __DIR__ . '/../endpoints'
);

$response = $api->endpoint('auth.login')->call([], [
    'email' => 'you@example.com',
    'password' => 'your-password',
]);

var_dump($response);
