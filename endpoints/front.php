<?php

/**
 * Frontend API endpoints (api-front.ecoregistry.io).
 * Used by the EcoRegistry web app; works with account JWT auth.
 */
return [
    [
        'name' => 'front.projects',
        'method' => 'GET',
        'path' => '/platform/project/public',
        'description' => 'List all public projects in the registry.',
    ],
    [
        'name' => 'front.project',
        'method' => 'GET',
        'path' => '/platform/project/public/{projectId}',
        'description' => 'Get full project detail by ID or code (e.g. 224 or CDB-1).',
    ],
];
