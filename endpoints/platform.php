<?php

return [
    [
        'name' => 'platform.register',
        'method' => 'POST',
        'path' => '/api-public/v1/register',
        'description' => 'Generate a public API token.',
    ],
    [
        'name' => 'platform.projects',
        'method' => 'GET',
        'path' => '/api-public/v1/projects',
        'description' => 'List all publicly available projects.',
    ],
    [
        'name' => 'platform.project_info',
        'method' => 'GET',
        'path' => '/api-public/v1/project-info/{projectID}',
        'description' => 'Get detailed info for a specific project including media.',
    ],
    [
        'name' => 'platform.shapes',
        'method' => 'GET',
        'path' => '/api-public/v1/shapes-project/{projectID}',
        'description' => 'Get cartographic/geographic data URL for a project.',
    ],
    [
        'name' => 'platform.withdrawals',
        'method' => 'GET',
        'path' => '/api-public/v1/withdrawals',
        'description' => 'List all carbon credit retirements in the registry.',
    ],
    [
        'name' => 'platform.sectors',
        'method' => 'GET',
        'path' => '/api-public/v1/get-sectors',
        'description' => 'List all active project sectors.',
    ],
    [
        'name' => 'platform.industries',
        'method' => 'GET',
        'path' => '/api-public/v1/get-industries',
        'description' => 'List all beneficiary industries eligible for carbon credit programs.',
    ],
];
