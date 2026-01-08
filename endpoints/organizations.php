<?php

return [
    [
        'name' => 'organizations.list',
        'method' => 'GET',
        'path' => '/organizations',
        'description' => 'List organizations in the registry.',
    ],
    [
        'name' => 'organizations.get',
        'method' => 'GET',
        'path' => '/organizations/{organizationId}',
        'description' => 'Fetch details for a specific organization.',
    ],
    [
        'name' => 'organizations.create',
        'method' => 'POST',
        'path' => '/organizations',
        'description' => 'Create a new organization.',
    ],
    [
        'name' => 'organizations.update',
        'method' => 'PUT',
        'path' => '/organizations/{organizationId}',
        'description' => 'Update an organization profile.',
    ],
];
