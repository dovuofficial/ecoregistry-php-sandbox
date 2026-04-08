# ecoregistry-php-sandbox

PHP API client for the EcoRegistry carbon credit registry. Built to power [DOVU Market](https://app.dovu.market/) — ingesting project data from EcoRegistry and (once exchange access is granted) retiring credits on behalf of buyers.

## Setup

```bash
composer install
cp .env.example .env  # then fill in your credentials
```

### `.env` structure

```
ECOREGISTRY_BASE_URL="https://api-external.ecoregistry.io/api"
ECOREGISTRY_ID="ECOxC_..."
TOKEN_API_EXCHANGES="your-exchange-token"
AUTH_EMAIL="you@example.com"
AUTH_PASSWORD="your-password"
PLATFORM_TOKEN="your-platform-api-token"
```

- `TOKEN_API_EXCHANGES` — found on EcoRegistry dashboard under My Company > Connectivity
- `PLATFORM_TOKEN` — obtained via the platform registration endpoint (emailed to you)

## How it works

### Architecture

```
EcoregistryApi
  └─ EndpointRegistry (loads endpoints/*.php definitions)
       └─ Endpoint (calls ApiClient with path param substitution)
            └─ ApiClient (cURL wrapper)
```

`Endpoint::call($params, $query, $body, $headers)` — `$params` substitutes path variables like `{projectID}`.

### Three APIs, two base URLs

| API | Base URL | Auth | Use |
|-----|----------|------|-----|
| Account | api-external.ecoregistry.io | email + apiKey → JWT (5 min) | Auth, account balances |
| Platform | api-external.ecoregistry.io | `x-api-key` header | Projects, serials, sectors, retirements |
| Frontend | api-front.ecoregistry.io | Bearer JWT | Project list + detail (same data as web app) |
| Exchange | api-external.ecoregistry.io | Exchange credentials | Transfers, retirements, locking (blocked — needs registration) |
| Marketplace | api-external.ecoregistry.io | Marketplace credentials | Retirements, active projects (blocked — needs onboarding) |

### Auth flow

```php
$dotenv = parse_ini_file(__DIR__ . '/.env');

$api = new EcoregistryApi(
    baseUrl: $dotenv['ECOREGISTRY_BASE_URL'],
    apiSecret: null,
    endpointPath: __DIR__ . '/endpoints'
);

// 1. Get a short-lived JWT (5 minutes)
$auth = $api->endpoint('account.auth')->call([], [], [
    'email' => $dotenv['AUTH_EMAIL'],
    'apiKey' => $dotenv['TOKEN_API_EXCHANGES'],  // camelCase apiKey, not apikey
], ['platform: ecoregistry']);

$jwt = $auth['data']['token'];

// 2. Use JWT for account endpoints
$positions = $api->endpoint('account.positions')->call([], [], null, [
    'platform: ecoregistry',
    'Authorization: Bearer ' . $jwt,
    'lng: en',
]);

// 3. Use PLATFORM_TOKEN for platform endpoints (no JWT needed)
$projects = $api->endpoint('platform.projects')->call([], [], null, [
    'platform: ecoregistry',
    'x-api-key: ' . $dotenv['PLATFORM_TOKEN'],
    'lng: en',
]);

// 4. Get detail for a specific project
$savimbo = $api->endpoint('platform.project_info')->call(
    ['projectID' => 224], [], null, [
        'platform: ecoregistry',
        'x-api-key: ' . $dotenv['PLATFORM_TOKEN'],
        'lng: en',
    ]
);
```

## Endpoint definitions

Each file in `endpoints/` maps to an API section from the [EcoRegistry documentation](https://ecoregistry.gitbook.io/ecoregistry-documentation).

```
endpoints/
  auth.php              # POST /api-account/v1/auth
  account.php           # GET  /api-account/v1/positions
  platform.php          # /api-public/v1/* — projects, sectors, industries, withdrawals
  front.php             # api-front.ecoregistry.io — project list + detail
  exchange_admin.php    # /api-exchange-v2/v2/* — admin endpoints (12)
  exchange_user.php     # /api-exchange-v2/v2/* — user endpoints (11)
  marketplace.php       # /marketplace/v1/* — marketplace endpoints (8)
```

### Key endpoints available now

| Endpoint | What it returns |
|----------|----------------|
| `account.auth` | JWT token |
| `account.positions` | Credit balances for your account |
| `platform.projects` | All projects with serials, owners, locations |
| `platform.project_info` | Full detail for a project by ID |
| `platform.sectors` | All project sectors |
| `platform.industries` | Beneficiary industries |
| `platform.withdrawals` | Full retirement history across the registry |
| `front.projects` | Project list (via frontend API) |
| `front.project` | Project detail by ID or code (e.g. `224` or `CDB-1`) |

## Discoveries

### Auth quirks
- **Production only** — credentials work against `api-external.ecoregistry.io`, not the dev URL
- **`apiKey` is camelCase** — sending `apikey` (lowercase) returns 400; `apiKey` returns 401/200
- The `TOKEN_API_EXCHANGES` from the dashboard is both the company `hashId` and the value used as `apiKey` in the auth body

### Frontend API
- `api-front.ecoregistry.io` is the internal API the web app at `app.ecoregistry.io` uses
- Works with the account JWT from `account.auth`
- `GET /platform/project/public` — returns all 231+ projects
- `GET /platform/project/public/{id}` — full detail (accepts numeric ID or code like `CDB-1`)
- Richer project detail than the platform API (includes media, DMRV links, certification timeline)

### What's blocked
- **Exchange API** — requires DOVU to be registered as an exchange with EcoRegistry (manual process, email `contacto@ecoregistry.io`)
- **Marketplace API** — requires marketplace onboarding
- **Credit operations** — DOVU's account holds zero credits; need exchange registration so Savimbo can transfer credits to us

## Running tests

```bash
vendor/bin/phpunit tests/ApiSmokeTest.php
```

Smoke tests hit the live production API. They verify:
- Account auth returns a valid JWT
- Account positions responds
- Platform API returns projects, Savimbo detail, sectors, industries, withdrawals
- Frontend API lists projects and finds Savimbo (CDB-1)
- Exchange and marketplace endpoints are reachable

## Docs

- `docs/example-listing-savimbo-CDB-1.md` — full structured data for Savimbo as an example of available listing granularity
- `docs/email-ecoregistry-exchange-request.md` — draft email requesting exchange registration
- `docs/internal-ecoregistry-integration-status.md` — team status summary

## Examples

```bash
php examples/auth_token.php         # authenticate and print JWT
php examples/account_positions.php  # authenticate then fetch balances
```
