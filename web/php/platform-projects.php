<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$platformToken = $env['PLATFORM_TOKEN'] ?? '';
if (!$platformToken) {
    errorOut('PLATFORM_TOKEN not set in .env');
}

try {
    $client = new \Ecoregistry\Http\ApiClient('https://api-external.ecoregistry.io/api');
    $response = $client->request('GET', '/api-public/v1/projects', [], null, [
        'platform: ecoregistry',
        'x-api-key: ' . $platformToken,
        'lng: en',
    ]);
    jsonOut($response['data']);
} catch (\Throwable $e) {
    errorOut($e->getMessage());
}
