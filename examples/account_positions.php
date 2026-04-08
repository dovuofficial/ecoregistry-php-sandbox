<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Ecoregistry\EcoregistryApi;

$dotenv = parse_ini_file(__DIR__ . '/../.env');

$api = new EcoregistryApi(
    baseUrl: $dotenv['ECOREGISTRY_BASE_URL'],
    apiSecret: null,
    endpointPath: __DIR__ . '/../endpoints'
);

// Authenticate to get a short-lived JWT
$authResponse = $api->endpoint('account.auth')->call([], [], [
    'email' => $dotenv['AUTH_EMAIL'],
    'apiKey' => $dotenv['TOKEN_API_EXCHANGES'],
], [
    'platform: ecoregistry',
]);

$token = $authResponse['data']['token'] ?? '';
if ($token === '') {
    throw new RuntimeException('Auth failed: ' . json_encode($authResponse));
}

$response = $api->endpoint('account.positions')->call([], [], null, [
    'platform: ecoregistry',
    'Authorization: Bearer ' . $token,
    'lng: en',
]);

var_dump($response);
