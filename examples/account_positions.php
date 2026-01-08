<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Ecoregistry\EcoregistryApi;

$token = getenv('ECOREGISTRY_ACCOUNT_TOKEN') ?: '';
if ($token === '') {
    throw new RuntimeException('Set ECOREGISTRY_ACCOUNT_TOKEN before running this example.');
}

$api = new EcoregistryApi(
    baseUrl: 'https://api-ecoregistry-dev.ecoregistry.io/api',
    apiSecret: null,
    endpointPath: __DIR__ . '/../endpoints'
);

$response = $api->endpoint('account.positions')->call([], null, [
    'platform: ecoregistry',
    'Authorization: Bearer ' . $token,
    'lng: en',
]);

var_dump($response);
