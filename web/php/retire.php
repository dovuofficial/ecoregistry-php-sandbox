<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$input = json_decode(file_get_contents('php://stdin'), true);
if (!$input) {
    errorOut('JSON input required on stdin');
}

try {
    $eco = buildEco($env, $account);
    $result = $eco->exchange()->auth()->retirement()
        ->serial($input['serial'])
        ->quantity((int)$input['quantity'])
        ->reason((int)$input['reasonId'])
        ->endUser(
            name: $input['endUser']['name'] ?? 'DOVU Market',
            countryId: (int)($input['endUser']['countryId'] ?? 230),
            documentTypeId: (int)($input['endUser']['documentTypeId'] ?? 1),
            documentNumber: $input['endUser']['documentNumber'] ?? '',
        )
        ->observation($input['observation'] ?? '')
        ->execute();

    jsonOut(['success' => true, 'result' => $result]);
} catch (\Throwable $e) {
    errorOut($e->getMessage());
}
