<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

try {
    $eco = buildEco($env, $account);
    $exchange = $eco->exchange()->auth();
    $projects = $exchange->projects();
    jsonOut(['status' => true, 'projects' => $projects]);
} catch (\Throwable $e) {
    errorOut($e->getMessage());
}
