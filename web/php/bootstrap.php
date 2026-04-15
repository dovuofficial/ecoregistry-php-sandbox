<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

function loadEnv(): array {
    // Try .env.ini first (deployed), fall back to .env (local dev)
    $base = __DIR__ . '/../../';
    $path = file_exists($base . '.env.ini') ? $base . '.env.ini' : $base . '.env';
    return parse_ini_file($path) ?: [];
}

function buildEco(array $env, string $account = 'general'): \Ecoregistry\EcoRegistry {
    $prefix = $account === 'user' ? 'USER_' : 'GENERAL_';
    $tokenApiExchanges = $env[$prefix . 'TOKEN_API_EXCHANGES'] ?? null;

    return new \Ecoregistry\EcoRegistry(\Ecoregistry\Config::fromArray([
        'base_url'              => $env['UAT_BASE_URL'],
        'email'                 => $env[$prefix . 'EMAIL'] ?? $env['UAT_EMAIL'],
        'api_key'               => $env[$prefix . 'API_KEY'] ?? $env['UAT_API_KEY'],
        'exchange_username'     => $env['UAT_EXCHANGE_USERNAME'],
        'exchange_password'     => $env['UAT_EXCHANGE_PASSWORD'],
        'exchange_name'         => $env['UAT_EXCHANGE_NAME'],
        'exchange_user_api_key' => $tokenApiExchanges ?: null,
        'platform_token'        => $env['PLATFORM_TOKEN'] ?? null,
    ]));
}

function jsonOut(mixed $data): void {
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function errorOut(string $message, mixed $detail = null): void {
    echo json_encode(['error' => $message, 'detail' => $detail]);
    exit(1);
}

// Read account from first CLI argument, default 'general'
$account = $argv[1] ?? 'general';
$env = loadEnv();
