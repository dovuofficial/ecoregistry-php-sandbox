<?php

return [
    [
        'name' => 'auth.login',
        'method' => 'POST',
        'path' => '/auth/login',
        'description' => 'Authenticate and retrieve an access token.',
    ],
    [
        'name' => 'auth.refresh',
        'method' => 'POST',
        'path' => '/auth/refresh',
        'description' => 'Refresh an expired access token.',
    ],
    [
        'name' => 'auth.me',
        'method' => 'GET',
        'path' => '/auth/me',
        'description' => 'Fetch the currently authenticated user.',
    ],
];
