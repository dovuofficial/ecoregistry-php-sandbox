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
    baseUrl: 'https://api-ecoregistry-dev.ecoregistry.io/api',
    apiSecret: null,
    endpointPath: __DIR__ . '/endpoints'
);

$response = $api->endpoint('account.positions')->call([], null, [
    'platform: ecoregistry',
    'Authorization: Bearer ' . getenv('ECOREGISTRY_ACCOUNT_TOKEN'),
    'lng: en',
]);
var_dump($response);
```

## Where to put your API secret

The Account endpoints use an API key for authentication. Store it as an environment variable:

```bash
export ECOREGISTRY_ACCOUNT_APIKEY="your-api-key"
```

Use the API key to request a short-lived account token, then store the token as:

```bash
export ECOREGISTRY_ACCOUNT_TOKEN="your-token"
```

## Auth token example (account endpoints)

An example script for authenticating and getting an account token lives in `examples/auth_token.php`.

```bash
php examples/auth_token.php
```

```php
$response = $api->endpoint('account.auth')->call([], [
    'email' => 'you@example.com',
    'apikey' => getenv('ECOREGISTRY_ACCOUNT_APIKEY'),
], [
    'platform: ecoregistry',
]);
```

## Account balances example

Fetch account balances (positions) with `examples/account_positions.php` after you have a token:

```bash
php examples/account_positions.php
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
        'name' => 'account.auth',
        'method' => 'POST',
        'path' => '/api-account/v1/auth',
        'description' => 'Get the admin token (valid for ~5 minutes).',
    ],
];
```

### Calling a specific endpoint

```php
$positions = $api->endpoint('account.positions')->call([], null, [
    'platform: ecoregistry',
    'Authorization: Bearer ' . getenv('ECOREGISTRY_ACCOUNT_TOKEN'),
    'lng: en',
]);
```

## Next steps

1. Open the GitBook documentation and verify the endpoint paths and HTTP methods.
2. Update the matching file inside `endpoints/` so each page is represented.
3. Add new files in `endpoints/` if the documentation introduces new groups.

## Troubleshooting

- **Unknown endpoint**: Make sure the name exists in one of the `endpoints/*.php` files.
- **Unauthorized**: Confirm that your account API key is correct and the token is not expired.
