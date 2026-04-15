<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

try {
    $eco = buildEco($env, $account);
    $exchange = $eco->exchange()->auth();
    $positions = $exchange->positions();
    jsonOut($positions);
} catch (\Throwable $e) {
    errorOut($e->getMessage());
}
