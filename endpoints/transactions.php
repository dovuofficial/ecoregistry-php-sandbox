<?php

return [
    [
        'name' => 'transactions.list',
        'method' => 'GET',
        'path' => '/transactions',
        'description' => 'List credit transactions.',
    ],
    [
        'name' => 'transactions.get',
        'method' => 'GET',
        'path' => '/transactions/{transactionId}',
        'description' => 'Fetch a transaction by ID.',
    ],
    [
        'name' => 'transactions.transfer',
        'method' => 'POST',
        'path' => '/transactions/transfer',
        'description' => 'Transfer credits between accounts.',
    ],
];
