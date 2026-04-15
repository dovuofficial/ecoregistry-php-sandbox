<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$projectId = (int)($argv[2] ?? 0);
if ($projectId < 1) {
    errorOut('project_id required as second argument');
}

try {
    $eco = buildEco($env, $account);
    $exchange = $eco->exchange()->auth();
    $project = $exchange->project($projectId);
    jsonOut($project);
} catch (\Throwable $e) {
    errorOut($e->getMessage());
}
