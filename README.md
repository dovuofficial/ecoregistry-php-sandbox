# ecoregistry-php-sandbox

Basic PHP API client scaffold for the Ecoregistry API. This project is organized so that each GitBook documentation page can map cleanly to a PHP file in the `endpoints/` directory, making it easy to find, edit, or extend specific endpoints.

## Quick start

```bash
composer install
```

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Ecoregistry\EcoregistryApi;

$api = new EcoregistryApi(
    baseUrl: 'https://api.ecoregistry.example',
    apiSecret: getenv('ECOREGISTRY_API_SECRET') ?: null,
    endpointPath: __DIR__ . '/endpoints'
);

$response = $api->endpoint('projects.list')->call(['page' => 1]);
var_dump($response);
```

## Where to put your API secret

Set your API secret as an environment variable (recommended) so it never appears in source control:

```bash
export ECOREGISTRY_API_SECRET="your-secret"
```

If you prefer a local file, place it in a `.env` file that is excluded from Git. Then load it before constructing `EcoregistryApi`.

## Auth token example

An example script for authenticating and getting a token lives in `examples/auth_token.php`.

```bash
php examples/auth_token.php
```

```php
$response = $api->endpoint('auth.login')->call([], [
    'email' => 'you@example.com',
    'password' => 'your-password',
]);
```

## Endpoint layout (one file per GitBook page)

Each file in `endpoints/` returns an array of endpoint definitions. This keeps a 1:1 mapping between documentation pages and PHP files. When you add or update endpoints from the GitBook documentation, edit the matching file or create a new one.

```
endpoints/
  account.php
  auth.php
  organizations.php
  projects.php
  credits.php
  transactions.php
  documents.php
  methodologies.php
```

### Endpoint definition format

```php
return [
    [
        'name' => 'projects.list',
        'method' => 'GET',
        'path' => '/projects',
        'description' => 'List registered projects.',
    ],
];
```

### Calling a specific endpoint

```php
$project = $api->endpoint('projects.get')->call([], null, [
    'X-Request-ID: demo-1234',
]);
```

## Next steps

1. Open the GitBook documentation and verify the endpoint paths and HTTP methods.
2. Update the matching file inside `endpoints/` so each page is represented.
3. Add new files in `endpoints/` if the documentation introduces new groups.

## Troubleshooting

- **Unknown endpoint**: Make sure the name exists in one of the `endpoints/*.php` files.
- **Unauthorized**: Confirm that `ECOREGISTRY_API_SECRET` is set and that your account has access.
