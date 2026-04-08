<?php

return [
    [
        'name' => 'exchange.auth',
        'method' => 'POST',
        'path' => '/api-exchange-v2/v2/auth',
        'description' => 'Get exchange admin token (valid ~5 minutes).',
    ],
    [
        'name' => 'exchange.projects',
        'method' => 'GET',
        'path' => '/api-exchange-v2/v2/get-all-project',
        'description' => 'Get all available projects.',
    ],
    [
        'name' => 'exchange.project',
        'method' => 'POST',
        'path' => '/api-exchange-v2/v2/get-project',
        'description' => 'Get complete info for a specific project by ID.',
    ],
    [
        'name' => 'exchange.companies',
        'method' => 'GET',
        'path' => '/api-exchange-v2/v2/get-companies',
        'description' => 'Get active account holders on the exchange.',
    ],
    [
        'name' => 'exchange.positions',
        'method' => 'GET',
        'path' => '/api-exchange-v2/v2/get-all-positions',
        'description' => 'Get all company balances linked to the exchange.',
    ],
    [
        'name' => 'exchange.sectors',
        'method' => 'GET',
        'path' => '/api-exchange-v2/v2/get-sectors',
        'description' => 'Get all available sectors.',
    ],
    [
        'name' => 'exchange.industries',
        'method' => 'GET',
        'path' => '/api-exchange-v2/v2/get-industries',
        'description' => 'Get all available industries.',
    ],
    [
        'name' => 'exchange.countries',
        'method' => 'GET',
        'path' => '/api-exchange-v2/v2/get-countries',
        'description' => 'Get all countries.',
    ],
    [
        'name' => 'exchange.serial_eligible',
        'method' => 'POST',
        'path' => '/api-exchange-v2/v2/serial-eligible',
        'description' => 'Check eligibility for a specific serial.',
    ],
    [
        'name' => 'exchange.type_documents',
        'method' => 'GET',
        'path' => '/api-exchange-v2/v2/type-documents',
        'description' => 'Get document types accepted for retirements.',
    ],
    [
        'name' => 'exchange.reason_using',
        'method' => 'GET',
        'path' => '/api-exchange-v2/v2/reason-using',
        'description' => 'Get reasons for using carbon offsets.',
    ],
    [
        'name' => 'exchange.transaction_info',
        'method' => 'POST',
        'path' => '/api-exchange-v2/v2/transaction-info',
        'description' => 'Get details for a specific transaction.',
    ],
];
