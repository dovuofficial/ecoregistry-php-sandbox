# dovu/ecoregistry-php

Fluent PHP client for the [EcoRegistry](https://www.ecoregistry.io/) carbon credit registry API. Built for [DOVU Market](https://app.dovu.market/).

Works standalone or with Laravel (auto-discovery).

## Installation

```bash
composer require dovu/ecoregistry-php
```

## Quick Start

```php
use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;

$eco = new EcoRegistry(Config::fromArray([
    'email' => 'you@example.com',
    'api_key' => 'your-exchange-token',
    'platform_token' => 'your-platform-token',
]));

// List all projects
$projects = $eco->platform()->projects();

// Get Savimbo detail
$savimbo = $eco->platform()->project(224);

// Get project by code (via frontend API)
$detail = $eco->front()->project('CDB-1');

// Account balances
$positions = $eco->account()->positions();
```

## Laravel Setup

The package auto-discovers. Publish the config:

```bash
php artisan vendor:publish --tag=ecoregistry-config
```

Add to your `.env`:

```
ECOREGISTRY_EMAIL=you@example.com
ECOREGISTRY_API_KEY=your-exchange-token
ECOREGISTRY_PLATFORM_TOKEN=your-platform-token
```

Then use the facade or inject:

```php
// Facade
use EcoRegistry;

$projects = EcoRegistry::platform()->projects();
$savimbo = EcoRegistry::front()->project('CDB-1');

// Dependency injection
public function index(Ecoregistry\EcoRegistry $eco)
{
    return $eco->platform()->project(224);
}
```

## API Reference

### `$eco->account()`

Account Information API. Requires `email` + `api_key`.

| Method | Description |
|--------|-------------|
| `positions($lang = 'en')` | Account balances and credit positions |

### `$eco->platform()`

Platform Information API. Requires `platform_token`.

| Method | Description |
|--------|-------------|
| `projects($lang)` | All projects with serials, owners, locations |
| `project(int $id, $lang)` | Project detail by numeric ID |
| `shapes(int $projectId, $lang)` | Cartographic data URL for a project |
| `withdrawals($lang)` | All retirements across the registry |
| `sectors($lang)` | Project sectors |
| `industries($lang)` | Beneficiary industries |

### `$eco->front()`

Frontend API (`api-front.ecoregistry.io`). Richer detail than Platform API — includes media, DMRV, certification timeline.

| Method | Description |
|--------|-------------|
| `projects($lang)` | All public projects |
| `project(int\|string $id, $lang)` | Project detail by ID (`224`) or code (`'CDB-1'`) |

### `$eco->exchange()`

Exchange API. Requires exchange registration with EcoRegistry (`contacto@ecoregistry.io`).

| Method | Description |
|--------|-------------|
| `auth()` | Authenticate (call first) |
| `projects($lang)` | All projects |
| `project(int $id, $lang)` | Project by ID |
| `companies($lang)` | Account holders on the exchange |
| `positions($lang)` | All company balances |
| `retirement()` | Fluent retirement builder (see below) |
| `lock($serial, $qty, $lang)` | Lock credits |
| `unlock($serial, $qty, $lang)` | Unlock credits |
| `transfer($companyId, $serial, $qty, $lang)` | Transfer between accounts |
| `retire(array $data, $lang)` | Retire credits (tonnes) — returns PDF |
| `retireKg(array $data, $lang)` | Retire credits (kg) — returns PDF |
| `transferToEcoregistry($serial, $qty, $lang)` | Transfer back to EcoRegistry |

#### Fluent Retirement

```php
$eco->exchange()->auth()->retirement()
    ->serial('CDC_18_5_11_321_14_XX_XA_CO_1_1_2021')
    ->quantity(10)
    ->voluntaryCompensation()    // or ->corsia(), ->colombianCarbonTax(), ->reason($id)
    ->endUser(
        name: 'Buyer Name',
        countryId: 230,          // UK
        documentTypeId: 1,       // ID
        documentNumber: '12345',
    )
    ->observation('Retired via DOVU Market')
    ->execute();
```

### `$eco->marketplace()`

Marketplace API. Requires marketplace onboarding with EcoRegistry.

| Method | Description |
|--------|-------------|
| `auth()` | Authenticate (call first) |
| `retire(array $data, $lang)` | Retire credits — returns PDF + transaction ID |
| `certificationPdf($txId, $lang)` | Retirement certificate PDF URL |
| `activeProjects(int $mpId, $lang)` | Projects in marketplace |
| `countries($lang)` | Countries |
| `documentTypes($lang)` | Document types |
| `reasonsForUse($lang)` | Offset usage reasons |
| `serialEligibility($serial, $lang)` | Eligible reasons for a serial |

## Configuration

| Key | Env Var | Required | Default |
|-----|---------|----------|---------|
| `base_url` | `ECOREGISTRY_BASE_URL` | No | `https://api-external.ecoregistry.io/api` |
| `front_url` | `ECOREGISTRY_FRONT_URL` | No | `https://api-front.ecoregistry.io` |
| `email` | `ECOREGISTRY_EMAIL` | Yes | — |
| `api_key` | `ECOREGISTRY_API_KEY` | Yes | — |
| `platform_token` | `ECOREGISTRY_PLATFORM_TOKEN` | For platform() | — |
| `exchange_username` | `ECOREGISTRY_EXCHANGE_USERNAME` | For exchange() | — |
| `exchange_password` | `ECOREGISTRY_EXCHANGE_PASSWORD` | For exchange() | — |
| `exchange_name` | `ECOREGISTRY_EXCHANGE_NAME` | For exchange() | — |
| `exchange_user_api_key` | `ECOREGISTRY_EXCHANGE_USER_API_KEY` | For exchange user ops | — |
| `marketplace_name` | `ECOREGISTRY_MARKETPLACE_NAME` | For marketplace() | — |
| `marketplace_password` | `ECOREGISTRY_MARKETPLACE_PASSWORD` | For marketplace() | — |

The `api_key` is the "Token API Exchanges" from your EcoRegistry dashboard (My Company > Connectivity).

## Auth

Account auth happens automatically — the client fetches a 5-minute JWT on first use and caches it. The Platform API uses a static token (`x-api-key` header), no JWT needed. Exchange and Marketplace APIs have their own auth flows via `auth()`.

## Status

| API | Production | UAT/Dev |
|-----|-----------|---------|
| Account (auth, positions) | Working | Working |
| Platform (projects, sectors, industries, withdrawals) | Working | — |
| Frontend (project list, detail) | Working | — |
| Exchange admin (auth, projects, companies, positions) | Pending credentials | Working |
| Exchange user (retirement, lock, transfer) | Pending credentials | Pending — user endpoint auth issue under investigation with EcoRegistry |
| Marketplace | Pending onboarding | — |

## Tests

```bash
# Unit tests (mocked, no API calls)
vendor/bin/phpunit --testsuite Unit

# Integration tests (hits live production API, needs .env)
vendor/bin/phpunit --testsuite Integration

# All
vendor/bin/phpunit
```