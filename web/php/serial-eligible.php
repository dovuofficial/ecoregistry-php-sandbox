<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$serial = $argv[2] ?? '';
if (!$serial) {
    errorOut('serial required as second argument');
}

try {
    $eco = buildEco($env, $account);
    $client = new \Ecoregistry\Http\ApiClient($env['UAT_BASE_URL']);

    $authResponse = $client->request('POST', '/api-exchange-v2/v2/auth', [], [
        'user_name'     => $env['UAT_EXCHANGE_USERNAME'],
        'password'      => $env['UAT_EXCHANGE_PASSWORD'],
        'name_exchange' => $env['UAT_EXCHANGE_NAME'],
    ], ['platform: ecoregistry']);

    $adminToken = $authResponse['data']['token_admin'] ?? $authResponse['data']['token'] ?? null;

    $response = $client->request('POST', '/api-exchange-v2/v2/serial-eligible', [], [
        'serial' => $serial,
    ], [
        'platform: ecoregistry',
        'x-api-key-admin: ' . $adminToken,
        'lng: en',
    ]);

    jsonOut($response['data']);
} catch (\Throwable $e) {
    errorOut($e->getMessage());
}
