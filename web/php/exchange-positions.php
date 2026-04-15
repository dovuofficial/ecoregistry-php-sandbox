<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

try {
    if ($account === 'user') {
        // User accounts: use the Account API which returns per-account positions
        $eco = buildEco($env, $account);
        $accountPositions = $eco->account()->positions();

        // Normalize to same shape as exchange positions
        $prefix = 'USER_';
        $companyId = $env[$prefix . 'COMPANY_ID'] ?? 'unknown';
        $serials = [];
        foreach ($accountPositions['projects'] ?? [] as $p) {
            $serials[] = [
                'serial' => $p['serial'],
                'quantity' => $p['quantity'],
                'quantity_lock' => $p['quantity_block'] ?? 0,
                'projectName' => $p['projectName'] ?? null,
                'vintage' => $p['year'] ?? null,
            ];
        }
        jsonOut([
            'status' => true,
            'balance' => [
                ['company_id' => $companyId, 'serials' => $serials],
            ],
        ]);
    } else {
        // General account: use exchange admin API
        $eco = buildEco($env, $account);
        $exchange = $eco->exchange()->auth();
        $positions = $exchange->positions();

        $prefix = 'GENERAL_';
        $companyId = $env[$prefix . 'COMPANY_ID'] ?? null;
        if ($companyId && isset($positions['balance'])) {
            $positions['balance'] = array_values(array_filter(
                $positions['balance'],
                fn($b) => $b['company_id'] === $companyId
            ));
        }
        jsonOut($positions);
    }
} catch (\Throwable $e) {
    errorOut($e->getMessage());
}
