<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$transactionId = (int)($argv[2] ?? 0);
if ($transactionId < 1) {
    errorOut('transactionId required as second argument');
}

try {
    $client = new \Ecoregistry\Http\ApiClient($env['UAT_BASE_URL']);

    // Get exchange admin token for auth
    $authResponse = $client->request('POST', '/api-exchange-v2/v2/auth', [], [
        'user_name'     => $env['UAT_EXCHANGE_USERNAME'],
        'password'      => $env['UAT_EXCHANGE_PASSWORD'],
        'name_exchange' => $env['UAT_EXCHANGE_NAME'],
    ], ['platform: ecoregistry']);
    $adminToken = $authResponse['data']['token_admin'] ?? $authResponse['data']['token'] ?? null;

    $response = $client->request('GET', '/marketplace/v1/emit-certification-pdf/' . $transactionId, [], null, [
        'platform: ecoregistry',
        'x-api-key-admin: ' . $adminToken,
    ]);
    jsonOut($response['data']);
} catch (\Throwable $e) {
    errorOut($e->getMessage());
}
