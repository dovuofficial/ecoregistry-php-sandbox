<?php

return [
    [
        'name' => 'documents.list',
        'method' => 'GET',
        'path' => '/documents',
        'description' => 'List uploaded documents.',
    ],
    [
        'name' => 'documents.get',
        'method' => 'GET',
        'path' => '/documents/{documentId}',
        'description' => 'Fetch a document metadata entry.',
    ],
    [
        'name' => 'documents.upload',
        'method' => 'POST',
        'path' => '/documents',
        'description' => 'Upload a supporting document.',
    ],
];
