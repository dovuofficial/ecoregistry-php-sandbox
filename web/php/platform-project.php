<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$projectId = (int)($argv[2] ?? 0);
if ($projectId < 1) {
    errorOut('project_id required as second argument');
}

$platformToken = $env['PLATFORM_TOKEN'] ?? '';
if (!$platformToken) {
    errorOut('PLATFORM_TOKEN not set in .env');
}

try {
    $client = new \Ecoregistry\Http\ApiClient('https://api-external.ecoregistry.io/api');
    $response = $client->request('GET', "/api-public/v1/project-info/{$projectId}", [], null, [
        'platform: ecoregistry',
        'x-api-key: ' . $platformToken,
        'lng: en',
    ]);
    jsonOut($response['data']);
} catch (\Throwable $e) {
    errorOut($e->getMessage());
}
