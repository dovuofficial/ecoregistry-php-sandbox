<?php

return [
    [
        'name' => 'marketplace.auth',
        'method' => 'POST',
        'path' => '/marketplace/v1/authAdmin',
        'description' => 'Get marketplace admin token (valid ~5 minutes).',
    ],
    [
        'name' => 'marketplace.retirement',
        'method' => 'POST',
        'path' => '/marketplace/v1/retirement',
        'description' => 'Retire carbon credits from inventory.',
    ],
    [
        'name' => 'marketplace.certification_pdf',
        'method' => 'GET',
        'path' => '/marketplace/v1/emit-certification-pdf/{transactionID}',
        'description' => 'Get the retirement certificate PDF URL for a transaction.',
    ],
    [
        'name' => 'marketplace.active_projects',
        'method' => 'GET',
        'path' => '/marketplace/{idMarketplace}/projectActives',
        'description' => 'Get projects and balances in marketplace.',
    ],
    [
        'name' => 'marketplace.countries',
        'method' => 'GET',
        'path' => '/marketplace/v1/get-countries',
        'description' => 'List countries in the registry.',
    ],
    [
        'name' => 'marketplace.type_documents',
        'method' => 'GET',
        'path' => '/marketplace/v1/type-documents',
        'description' => 'List document types in the registry.',
    ],
    [
        'name' => 'marketplace.reason_using',
        'method' => 'GET',
        'path' => '/marketplace/v1/reason-using',
        'description' => 'Get carbon offset usage reasons with IDs.',
    ],
    [
        'name' => 'marketplace.serial_eligible',
        'method' => 'POST',
        'path' => '/marketplace/v1/serial-eligible',
        'description' => 'Check which offset usage reasons apply to a serial.',
    ],
];
