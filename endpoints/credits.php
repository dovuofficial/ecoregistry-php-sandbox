<?php

return [
    [
        'name' => 'credits.list',
        'method' => 'GET',
        'path' => '/credits',
        'description' => 'List issued credits.',
    ],
    [
        'name' => 'credits.get',
        'method' => 'GET',
        'path' => '/credits/{creditId}',
        'description' => 'Fetch credit details.',
    ],
    [
        'name' => 'credits.issue',
        'method' => 'POST',
        'path' => '/credits/issue',
        'description' => 'Issue new credits against a project.',
    ],
    [
        'name' => 'credits.retire',
        'method' => 'POST',
        'path' => '/credits/{creditId}/retire',
        'description' => 'Retire credits for a beneficiary.',
    ],
];
