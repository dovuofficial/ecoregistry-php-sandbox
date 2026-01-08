<?php

return [
    [
        'name' => 'projects.list',
        'method' => 'GET',
        'path' => '/projects',
        'description' => 'List registered projects.',
    ],
    [
        'name' => 'projects.get',
        'method' => 'GET',
        'path' => '/projects/{projectId}',
        'description' => 'Fetch a project by ID.',
    ],
    [
        'name' => 'projects.create',
        'method' => 'POST',
        'path' => '/projects',
        'description' => 'Register a new project.',
    ],
    [
        'name' => 'projects.update',
        'method' => 'PUT',
        'path' => '/projects/{projectId}',
        'description' => 'Update a project record.',
    ],
];
