<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$transactionId = (int)($argv[2] ?? 0);
if ($transactionId < 1) {
    errorOut('transactionId required as second argument');
}

try {
    $client = new \Ecoregistry\Http\ApiClient($env['UAT_BASE_URL']);
    $response = $client->request('GET', '/marketplace/v1/emit-certification-pdf/' . $transactionId);
    jsonOut($response['data']);
} catch (\Throwable $e) {
    errorOut($e->getMessage());
}
