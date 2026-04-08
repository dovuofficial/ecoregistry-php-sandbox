<?php

return [
    [
        'name' => 'exchange.register',
        'method' => 'POST',
        'path' => '/api-exchange-v2/v2/register',
        'description' => 'Link an account between EcoRegistry and the exchange.',
    ],
    [
        'name' => 'exchange.close_account',
        'method' => 'PUT',
        'path' => '/api-exchange-v2/v2/register',
        'description' => 'Unlink an account; returns all credits to EcoRegistry.',
    ],
    [
        'name' => 'exchange.company_balance',
        'method' => 'POST',
        'path' => '/api-exchange-v2/v2/company-balance',
        'description' => 'Get balances for a specific company.',
    ],
    [
        'name' => 'exchange.company_history',
        'method' => 'POST',
        'path' => '/api-exchange-v2/v2/company-history',
        'description' => 'Get transfer history for a company inside the exchange.',
    ],
    [
        'name' => 'exchange.lock_serial',
        'method' => 'POST',
        'path' => '/api-exchange-v2/v2/lock-serial',
        'description' => 'Lock credits inside the exchange.',
    ],
    [
        'name' => 'exchange.unlock_serial',
        'method' => 'POST',
        'path' => '/api-exchange-v2/v2/unlock-serial',
        'description' => 'Unlock credits inside the exchange.',
    ],
    [
        'name' => 'exchange.transfer_between',
        'method' => 'POST',
        'path' => '/api-exchange-v2/v2/transfer-between',
        'description' => 'Transfer credits between accounts linked to the exchange.',
    ],
    [
        'name' => 'exchange.history_lock_unlock',
        'method' => 'POST',
        'path' => '/api-public-blockchains/v1/history-lock-unlock',
        'description' => 'Get lock/unlock history for a company.',
    ],
    [
        'name' => 'exchange.transfer_to_ecoregistry',
        'method' => 'POST',
        'path' => '/api-exchange-v2/v2/transfer-to-ecoregistry',
        'description' => 'Transfer credits back to EcoRegistry (burn in exchange).',
    ],
    [
        'name' => 'exchange.retirement',
        'method' => 'POST',
        'path' => '/api-exchange-v2/v2/retirement',
        'description' => 'Retire credits in tons; returns EcoRegistry retirement PDF.',
    ],
    [
        'name' => 'exchange.retirement_kg',
        'method' => 'POST',
        'path' => '/api-exchange-v2/v2/retirement-kg',
        'description' => 'Retire credits in kg; returns EcoRegistry retirement PDF.',
    ],
];
