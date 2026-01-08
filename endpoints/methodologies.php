<?php

return [
    [
        'name' => 'methodologies.list',
        'method' => 'GET',
        'path' => '/methodologies',
        'description' => 'List available methodologies.',
    ],
    [
        'name' => 'methodologies.get',
        'method' => 'GET',
        'path' => '/methodologies/{methodologyId}',
        'description' => 'Fetch methodology details.',
    ],
];
