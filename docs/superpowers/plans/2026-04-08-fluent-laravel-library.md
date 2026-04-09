# Fluent Laravel Library Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor the EcoRegistry PHP sandbox into a fluent, documented PHP library with Laravel service provider integration.

**Architecture:** A thin fluent layer on top of the existing `ApiClient`. Each API section (Account, Platform, Front, Exchange, Marketplace) becomes a service class with typed methods. A central `EcoRegistry` client manages auth (auto-refreshing JWT), config, and service access. Laravel integration via service provider, config file, and facade — but the library works standalone too.

**Tech Stack:** PHP 8.1+, Laravel 10+ (optional), PHPUnit 11, cURL

---

## File Structure

```
src/
  EcoRegistry.php                    # Main client — fluent entry point
  Config.php                         # Value object holding all credentials/URLs
  Auth/
    TokenManager.php                 # JWT caching + auto-refresh
  Services/
    AccountService.php               # account.auth, account.positions
    PlatformService.php              # platform.* endpoints (x-api-key auth)
    FrontService.php                 # front.* endpoints (JWT auth, different base URL)
    ExchangeService.php              # exchange.* endpoints
    MarketplaceService.php           # marketplace.* endpoints
  Http/
    ApiClient.php                    # (existing, minor modification)
  Laravel/
    EcoRegistryServiceProvider.php   # Service provider
    EcoRegistryFacade.php            # Facade
config/
  ecoregistry.php                    # Laravel config (publishable)
tests/
  Unit/
    ConfigTest.php
    TokenManagerTest.php
    EcoRegistryTest.php
  Integration/
    AccountServiceTest.php
    PlatformServiceTest.php
    FrontServiceTest.php
```

Endpoint definition files (`endpoints/`) are **removed**. The fluent service classes replace them — paths are now constants inside each service class, making them discoverable via IDE autocomplete and docblocks.

---

### Task 1: Config Value Object

**Files:**
- Create: `src/Config.php`
- Test: `tests/Unit/ConfigTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Unit/ConfigTest.php
declare(strict_types=1);

namespace Ecoregistry\Tests\Unit;

use Ecoregistry\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function test_creates_from_array(): void
    {
        $config = Config::fromArray([
            'base_url' => 'https://api-external.ecoregistry.io/api',
            'front_url' => 'https://api-front.ecoregistry.io',
            'email' => 'matt@dovu.earth',
            'api_key' => 'abc123',
            'platform_token' => 'def456',
        ]);

        $this->assertSame('https://api-external.ecoregistry.io/api', $config->baseUrl);
        $this->assertSame('https://api-front.ecoregistry.io', $config->frontUrl);
        $this->assertSame('matt@dovu.earth', $config->email);
        $this->assertSame('abc123', $config->apiKey);
        $this->assertSame('def456', $config->platformToken);
    }

    public function test_defaults_for_optional_fields(): void
    {
        $config = Config::fromArray([
            'email' => 'matt@dovu.earth',
            'api_key' => 'abc123',
        ]);

        $this->assertSame('https://api-external.ecoregistry.io/api', $config->baseUrl);
        $this->assertSame('https://api-front.ecoregistry.io', $config->frontUrl);
        $this->assertNull($config->platformToken);
        $this->assertNull($config->exchangeUsername);
        $this->assertNull($config->exchangePassword);
        $this->assertNull($config->exchangeName);
    }

    public function test_throws_on_missing_email(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Config::fromArray(['api_key' => 'abc123']);
    }

    public function test_throws_on_missing_api_key(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Config::fromArray(['email' => 'matt@dovu.earth']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Unit/ConfigTest.php -v`
Expected: FAIL — class `Ecoregistry\Config` not found

- [ ] **Step 3: Write the implementation**

```php
<?php
// src/Config.php
declare(strict_types=1);

namespace Ecoregistry;

final class Config
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $frontUrl,
        public readonly string $email,
        public readonly string $apiKey,
        public readonly ?string $platformToken = null,
        public readonly ?string $exchangeUsername = null,
        public readonly ?string $exchangePassword = null,
        public readonly ?string $exchangeName = null,
        public readonly ?string $marketplaceName = null,
        public readonly ?string $marketplacePassword = null,
    ) {
    }

    public static function fromArray(array $values): self
    {
        if (!isset($values['email'])) {
            throw new \InvalidArgumentException('Config requires "email"');
        }
        if (!isset($values['api_key'])) {
            throw new \InvalidArgumentException('Config requires "api_key"');
        }

        return new self(
            baseUrl: $values['base_url'] ?? 'https://api-external.ecoregistry.io/api',
            frontUrl: $values['front_url'] ?? 'https://api-front.ecoregistry.io',
            email: $values['email'],
            apiKey: $values['api_key'],
            platformToken: $values['platform_token'] ?? null,
            exchangeUsername: $values['exchange_username'] ?? null,
            exchangePassword: $values['exchange_password'] ?? null,
            exchangeName: $values['exchange_name'] ?? null,
            marketplaceName: $values['marketplace_name'] ?? null,
            marketplacePassword: $values['marketplace_password'] ?? null,
        );
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/phpunit tests/Unit/ConfigTest.php -v`
Expected: OK (4 tests)

- [ ] **Step 5: Commit**

```bash
git add src/Config.php tests/Unit/ConfigTest.php
git commit -m "feat: add Config value object with factory and validation"
```

---

### Task 2: TokenManager — JWT Caching + Auto-Refresh

**Files:**
- Create: `src/Auth/TokenManager.php`
- Test: `tests/Unit/TokenManagerTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Unit/TokenManagerTest.php
declare(strict_types=1);

namespace Ecoregistry\Tests\Unit;

use Ecoregistry\Auth\TokenManager;
use Ecoregistry\Http\ApiClient;
use PHPUnit\Framework\TestCase;

final class TokenManagerTest extends TestCase
{
    public function test_fetches_token_on_first_call(): void
    {
        $client = $this->createMock(ApiClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/api-account/v1/auth', [], [
                'email' => 'test@example.com',
                'apiKey' => 'secret',
            ], ['platform: ecoregistry'])
            ->willReturn([
                'status' => 200,
                'data' => ['token' => 'jwt.token.here'],
            ]);

        $manager = new TokenManager($client, 'test@example.com', 'secret');
        $token = $manager->getToken();

        $this->assertSame('jwt.token.here', $token);
    }

    public function test_caches_token_on_subsequent_calls(): void
    {
        $client = $this->createMock(ApiClient::class);
        $client->expects($this->once()) // only one HTTP call
            ->method('request')
            ->willReturn([
                'status' => 200,
                'data' => ['token' => 'jwt.token.here'],
            ]);

        $manager = new TokenManager($client, 'test@example.com', 'secret');
        $manager->getToken();
        $token = $manager->getToken();

        $this->assertSame('jwt.token.here', $token);
    }

    public function test_throws_on_auth_failure(): void
    {
        $client = $this->createMock(ApiClient::class);
        $client->method('request')->willReturn([
            'status' => 401,
            'data' => ['error' => 'Unauthorized'],
        ]);

        $manager = new TokenManager($client, 'test@example.com', 'wrong');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Auth failed');
        $manager->getToken();
    }

    public function test_refresh_forces_new_token(): void
    {
        $client = $this->createMock(ApiClient::class);
        $client->expects($this->exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls(
                ['status' => 200, 'data' => ['token' => 'first']],
                ['status' => 200, 'data' => ['token' => 'second']],
            );

        $manager = new TokenManager($client, 'test@example.com', 'secret');
        $this->assertSame('first', $manager->getToken());

        $manager->refresh();
        $this->assertSame('second', $manager->getToken());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Unit/TokenManagerTest.php -v`
Expected: FAIL — class `Ecoregistry\Auth\TokenManager` not found

- [ ] **Step 3: Write the implementation**

```php
<?php
// src/Auth/TokenManager.php
declare(strict_types=1);

namespace Ecoregistry\Auth;

use Ecoregistry\Http\ApiClient;

final class TokenManager
{
    private ?string $token = null;

    public function __construct(
        private ApiClient $client,
        private string $email,
        private string $apiKey,
    ) {
    }

    public function getToken(): string
    {
        if ($this->token === null) {
            $this->token = $this->fetchToken();
        }

        return $this->token;
    }

    public function refresh(): void
    {
        $this->token = null;
    }

    private function fetchToken(): string
    {
        $response = $this->client->request(
            'POST',
            '/api-account/v1/auth',
            [],
            ['email' => $this->email, 'apiKey' => $this->apiKey],
            ['platform: ecoregistry']
        );

        if ($response['status'] !== 200 || !isset($response['data']['token'])) {
            throw new \RuntimeException(
                'Auth failed: ' . json_encode($response['data'] ?? $response)
            );
        }

        return $response['data']['token'];
    }
}
```

- [ ] **Step 4: Update `ApiClient::request` to be mockable** — change visibility from implicit-final to allow mocking

The `ApiClient` class is `final`. For mocking to work, we need to either remove `final` or extract an interface. Simplest: remove `final` from `ApiClient`.

Edit `src/Http/ApiClient.php` line 7: change `final class ApiClient` to `class ApiClient`.

- [ ] **Step 5: Run tests to verify they pass**

Run: `vendor/bin/phpunit tests/Unit/TokenManagerTest.php -v`
Expected: OK (4 tests)

- [ ] **Step 6: Commit**

```bash
git add src/Auth/TokenManager.php tests/Unit/TokenManagerTest.php src/Http/ApiClient.php
git commit -m "feat: add TokenManager with JWT caching and auto-refresh"
```

---

### Task 3: AccountService

**Files:**
- Create: `src/Services/AccountService.php`
- Test: `tests/Unit/AccountServiceTest.php` (unit with mocked client)

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Unit/AccountServiceTest.php
declare(strict_types=1);

namespace Ecoregistry\Tests\Unit;

use Ecoregistry\Auth\TokenManager;
use Ecoregistry\Http\ApiClient;
use Ecoregistry\Services\AccountService;
use PHPUnit\Framework\TestCase;

final class AccountServiceTest extends TestCase
{
    private ApiClient $client;
    private TokenManager $tokens;
    private AccountService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ApiClient::class);
        $this->tokens = $this->createMock(TokenManager::class);
        $this->tokens->method('getToken')->willReturn('test-jwt');
        $this->service = new AccountService($this->client, $this->tokens);
    }

    public function test_positions_calls_correct_endpoint(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/api-account/v1/positions',
                [],
                null,
                [
                    'platform: ecoregistry',
                    'Authorization: Bearer test-jwt',
                    'lng: en',
                ]
            )
            ->willReturn(['status' => 200, 'data' => ['status' => 1, 'projects' => []]]);

        $result = $this->service->positions();
        $this->assertSame(['status' => 1, 'projects' => []], $result);
    }

    public function test_positions_with_language(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/api-account/v1/positions',
                [],
                null,
                [
                    'platform: ecoregistry',
                    'Authorization: Bearer test-jwt',
                    'lng: es',
                ]
            )
            ->willReturn(['status' => 200, 'data' => ['status' => 1, 'projects' => []]]);

        $result = $this->service->positions('es');
        $this->assertSame(['status' => 1, 'projects' => []], $result);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Unit/AccountServiceTest.php -v`
Expected: FAIL — class not found

- [ ] **Step 3: Write the implementation**

```php
<?php
// src/Services/AccountService.php
declare(strict_types=1);

namespace Ecoregistry\Services;

use Ecoregistry\Auth\TokenManager;
use Ecoregistry\Http\ApiClient;

/**
 * Account Information API — balances and positions.
 *
 * @see https://ecoregistry.gitbook.io/ecoregistry-documentation/getting-started/account-information-api/account-endpoints
 */
final class AccountService
{
    public function __construct(
        private ApiClient $client,
        private TokenManager $tokens,
    ) {
    }

    /**
     * Get account balances (positions) including sub-accounts.
     *
     * @param string $lang Language code ('en' or 'es')
     * @return array Response data with 'status' and 'projects' keys
     */
    public function positions(string $lang = 'en'): array
    {
        return $this->authedRequest('GET', '/api-account/v1/positions', lang: $lang);
    }

    private function authedRequest(
        string $method,
        string $path,
        array $query = [],
        ?array $body = null,
        string $lang = 'en',
    ): array {
        $response = $this->client->request($method, $path, $query, $body, [
            'platform: ecoregistry',
            'Authorization: Bearer ' . $this->tokens->getToken(),
            'lng: ' . $lang,
        ]);

        return $response['data'];
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/phpunit tests/Unit/AccountServiceTest.php -v`
Expected: OK (2 tests)

- [ ] **Step 5: Commit**

```bash
git add src/Services/AccountService.php tests/Unit/AccountServiceTest.php
git commit -m "feat: add AccountService with positions endpoint"
```

---

### Task 4: PlatformService

**Files:**
- Create: `src/Services/PlatformService.php`
- Test: `tests/Unit/PlatformServiceTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Unit/PlatformServiceTest.php
declare(strict_types=1);

namespace Ecoregistry\Tests\Unit;

use Ecoregistry\Http\ApiClient;
use Ecoregistry\Services\PlatformService;
use PHPUnit\Framework\TestCase;

final class PlatformServiceTest extends TestCase
{
    private ApiClient $client;
    private PlatformService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ApiClient::class);
        $this->service = new PlatformService($this->client, 'platform-token-123');
    }

    public function test_projects_calls_correct_endpoint(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/api-public/v1/projects', [], null, [
                'platform: ecoregistry',
                'x-api-key: platform-token-123',
                'lng: en',
            ])
            ->willReturn(['status' => 200, 'data' => ['status' => true, 'project' => []]]);

        $result = $this->service->projects();
        $this->assertSame(['status' => true, 'project' => []], $result);
    }

    public function test_project_info_substitutes_id(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/api-public/v1/project-info/224', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['project' => ['name' => 'Savimbo']]]);

        $result = $this->service->project(224);
        $this->assertSame(['project' => ['name' => 'Savimbo']], $result);
    }

    public function test_sectors(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/api-public/v1/get-sectors', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['sectors' => []]]);

        $result = $this->service->sectors();
        $this->assertSame(['sectors' => []], $result);
    }

    public function test_industries(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/api-public/v1/get-industries', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['end_beneficiary_industries' => []]]);

        $result = $this->service->industries();
        $this->assertSame(['end_beneficiary_industries' => []], $result);
    }

    public function test_withdrawals(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/api-public/v1/withdrawals', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['withdrawals' => []]]);

        $result = $this->service->withdrawals();
        $this->assertSame(['withdrawals' => []], $result);
    }

    public function test_shapes(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/api-public/v1/shapes-project/224', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['url' => 'https://s3...']]);

        $result = $this->service->shapes(224);
        $this->assertSame(['url' => 'https://s3...'], $result);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Unit/PlatformServiceTest.php -v`
Expected: FAIL — class not found

- [ ] **Step 3: Write the implementation**

```php
<?php
// src/Services/PlatformService.php
declare(strict_types=1);

namespace Ecoregistry\Services;

use Ecoregistry\Http\ApiClient;

/**
 * Platform Information API — public project data, sectors, industries, retirements.
 *
 * Uses a static platform token (x-api-key header), no JWT needed.
 *
 * @see https://ecoregistry.gitbook.io/ecoregistry-documentation/getting-started/platform-information-api/information-endpoints
 */
final class PlatformService
{
    public function __construct(
        private ApiClient $client,
        private string $platformToken,
    ) {
    }

    /** List all publicly available projects with serials and locations. */
    public function projects(string $lang = 'en'): array
    {
        return $this->get('/api-public/v1/projects', $lang);
    }

    /** Get detailed info for a specific project by numeric ID. */
    public function project(int $id, string $lang = 'en'): array
    {
        return $this->get("/api-public/v1/project-info/{$id}", $lang);
    }

    /** Get cartographic/geographic data URL for a project. */
    public function shapes(int $projectId, string $lang = 'en'): array
    {
        return $this->get("/api-public/v1/shapes-project/{$projectId}", $lang);
    }

    /** List all carbon credit retirements (withdrawals) in the registry. */
    public function withdrawals(string $lang = 'en'): array
    {
        return $this->get('/api-public/v1/withdrawals', $lang);
    }

    /** List all active project sectors. */
    public function sectors(string $lang = 'en'): array
    {
        return $this->get('/api-public/v1/get-sectors', $lang);
    }

    /** List all beneficiary industries eligible for carbon credit programs. */
    public function industries(string $lang = 'en'): array
    {
        return $this->get('/api-public/v1/get-industries', $lang);
    }

    private function get(string $path, string $lang): array
    {
        $response = $this->client->request('GET', $path, [], null, [
            'platform: ecoregistry',
            'x-api-key: ' . $this->platformToken,
            'lng: ' . $lang,
        ]);

        return $response['data'];
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/phpunit tests/Unit/PlatformServiceTest.php -v`
Expected: OK (6 tests)

- [ ] **Step 5: Commit**

```bash
git add src/Services/PlatformService.php tests/Unit/PlatformServiceTest.php
git commit -m "feat: add PlatformService with projects, sectors, industries, withdrawals"
```

---

### Task 5: FrontService

**Files:**
- Create: `src/Services/FrontService.php`
- Test: `tests/Unit/FrontServiceTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Unit/FrontServiceTest.php
declare(strict_types=1);

namespace Ecoregistry\Tests\Unit;

use Ecoregistry\Auth\TokenManager;
use Ecoregistry\Http\ApiClient;
use Ecoregistry\Services\FrontService;
use PHPUnit\Framework\TestCase;

final class FrontServiceTest extends TestCase
{
    private ApiClient $client;
    private TokenManager $tokens;
    private FrontService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ApiClient::class);
        $this->tokens = $this->createMock(TokenManager::class);
        $this->tokens->method('getToken')->willReturn('test-jwt');
        $this->service = new FrontService($this->client, $this->tokens);
    }

    public function test_projects_calls_correct_endpoint(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/platform/project/public', [], null, [
                'platform: ecoregistry',
                'Authorization: Bearer test-jwt',
                'lng: en',
            ])
            ->willReturn(['status' => 200, 'data' => ['status' => 1, 'projects' => []]]);

        $result = $this->service->projects();
        $this->assertSame(['status' => 1, 'projects' => []], $result);
    }

    public function test_project_by_id(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/platform/project/public/224', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['project' => ['name' => 'Savimbo']]]);

        $result = $this->service->project(224);
        $this->assertSame(['project' => ['name' => 'Savimbo']], $result);
    }

    public function test_project_by_code(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', '/platform/project/public/CDB-1', [], null, $this->anything())
            ->willReturn(['status' => 200, 'data' => ['project' => ['code' => 'CDB-1']]]);

        $result = $this->service->project('CDB-1');
        $this->assertSame(['project' => ['code' => 'CDB-1']], $result);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Unit/FrontServiceTest.php -v`
Expected: FAIL — class not found

- [ ] **Step 3: Write the implementation**

```php
<?php
// src/Services/FrontService.php
declare(strict_types=1);

namespace Ecoregistry\Services;

use Ecoregistry\Auth\TokenManager;
use Ecoregistry\Http\ApiClient;

/**
 * Frontend API (api-front.ecoregistry.io) — richer project data than Platform API.
 *
 * Returns project detail with media, DMRV links, certification timeline, ratings.
 * Uses a separate base URL and account JWT auth.
 */
final class FrontService
{
    public function __construct(
        private ApiClient $client,
        private TokenManager $tokens,
    ) {
    }

    /** List all public projects in the registry. */
    public function projects(string $lang = 'en'): array
    {
        return $this->get('/platform/project/public', $lang);
    }

    /**
     * Get full project detail by numeric ID or project code.
     *
     * @param int|string $id Numeric ID (224) or code ('CDB-1')
     */
    public function project(int|string $id, string $lang = 'en'): array
    {
        return $this->get("/platform/project/public/{$id}", $lang);
    }

    private function get(string $path, string $lang): array
    {
        $response = $this->client->request('GET', $path, [], null, [
            'platform: ecoregistry',
            'Authorization: Bearer ' . $this->tokens->getToken(),
            'lng: ' . $lang,
        ]);

        return $response['data'];
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/phpunit tests/Unit/FrontServiceTest.php -v`
Expected: OK (3 tests)

- [ ] **Step 5: Commit**

```bash
git add src/Services/FrontService.php tests/Unit/FrontServiceTest.php
git commit -m "feat: add FrontService with project list and detail"
```

---

### Task 6: ExchangeService + MarketplaceService (stubs)

**Files:**
- Create: `src/Services/ExchangeService.php`
- Create: `src/Services/MarketplaceService.php`

These are documented stubs — the methods exist with docblocks so they're discoverable, but they need exchange/marketplace credentials to actually work.

- [ ] **Step 1: Write ExchangeService**

```php
<?php
// src/Services/ExchangeService.php
declare(strict_types=1);

namespace Ecoregistry\Services;

use Ecoregistry\Http\ApiClient;

/**
 * Exchange Connection API — credit transfers, retirements, locking.
 *
 * Requires DOVU to be registered as an exchange with EcoRegistry.
 * Contact contacto@ecoregistry.io to request exchange credentials.
 *
 * @see https://ecoregistry.gitbook.io/ecoregistry-documentation/getting-started/exchanges-connection/using-the-endpoints/admin-endpoints
 * @see https://ecoregistry.gitbook.io/ecoregistry-documentation/getting-started/exchanges-connection/using-the-endpoints/user-endpoints
 */
final class ExchangeService
{
    private ?string $adminToken = null;

    public function __construct(
        private ApiClient $client,
        private ?string $username = null,
        private ?string $password = null,
        private ?string $exchangeName = null,
    ) {
    }

    /**
     * Authenticate and get admin token (valid ~5 minutes).
     * Must be called before other exchange methods.
     */
    public function auth(): self
    {
        $this->requireCredentials();

        $response = $this->client->request('POST', '/api-exchange-v2/v2/auth', [], [
            'user_name' => $this->username,
            'password' => $this->password,
            'name_exchange' => $this->exchangeName,
        ], ['platform: ecoregistry']);

        if ($response['status'] !== 200 || !isset($response['data']['token'])) {
            throw new \RuntimeException('Exchange auth failed: ' . json_encode($response['data']));
        }

        $this->adminToken = $response['data']['token'];
        return $this;
    }

    /** Get all available projects. */
    public function projects(string $lang = 'en'): array
    {
        return $this->adminGet('/api-exchange-v2/v2/get-all-project', $lang);
    }

    /** Get specific project by ID. */
    public function project(int $projectId, string $lang = 'en'): array
    {
        return $this->adminPost('/api-exchange-v2/v2/get-project', ['project_id' => $projectId], $lang);
    }

    /** Get active account holders on the exchange. */
    public function companies(string $lang = 'en'): array
    {
        return $this->adminGet('/api-exchange-v2/v2/get-companies', $lang);
    }

    /** Get all company balances linked to the exchange. */
    public function positions(string $lang = 'en'): array
    {
        return $this->adminGet('/api-exchange-v2/v2/get-all-positions', $lang);
    }

    /** Lock credits inside the exchange. */
    public function lock(string $serial, int $quantity, string $lang = 'en'): array
    {
        return $this->userPost('/api-exchange-v2/v2/lock-serial', [
            'serial' => $serial,
            'quantity' => $quantity,
        ], $lang);
    }

    /** Unlock credits inside the exchange. */
    public function unlock(string $serial, int $quantity, string $lang = 'en'): array
    {
        return $this->userPost('/api-exchange-v2/v2/unlock-serial', [
            'serial' => $serial,
            'quantity' => $quantity,
        ], $lang);
    }

    /** Transfer credits between accounts linked to the exchange. */
    public function transfer(string $companyId, string $serial, int $quantity, string $lang = 'en'): array
    {
        return $this->userPost('/api-exchange-v2/v2/transfer-between', [
            'company_id' => $companyId,
            'serial' => $serial,
            'quantity' => $quantity,
            'asset_type' => 'carbon offset',
        ], $lang);
    }

    /** Retire credits in tonnes. Returns retirement PDF URL. */
    public function retire(array $retirementData, string $lang = 'en'): array
    {
        return $this->userPost('/api-exchange-v2/v2/retirement', $retirementData, $lang);
    }

    /** Retire credits in kilograms. Returns retirement PDF URL. */
    public function retireKg(array $retirementData, string $lang = 'en'): array
    {
        return $this->userPost('/api-exchange-v2/v2/retirement-kg', $retirementData, $lang);
    }

    /** Transfer credits back to EcoRegistry (burn in exchange). */
    public function transferToEcoregistry(string $serial, int $quantity, string $lang = 'en'): array
    {
        return $this->userPost('/api-exchange-v2/v2/transfer-to-ecoregistry', [
            'serial' => $serial,
            'quantity' => $quantity,
            'name_exchange' => $this->exchangeName,
        ], $lang);
    }

    private function adminHeaders(string $lang): array
    {
        $this->requireToken();
        return [
            'platform: ecoregistry',
            'x-api-key-admin: ' . $this->adminToken,
            'lng: ' . $lang,
        ];
    }

    private function userHeaders(string $lang): array
    {
        // User endpoints need both admin key and user key
        // User key comes from the linked account — for now same as admin
        return $this->adminHeaders($lang);
    }

    private function adminGet(string $path, string $lang): array
    {
        return $this->client->request('GET', $path, [], null, $this->adminHeaders($lang))['data'];
    }

    private function adminPost(string $path, array $body, string $lang): array
    {
        return $this->client->request('POST', $path, [], $body, $this->adminHeaders($lang))['data'];
    }

    private function userPost(string $path, array $body, string $lang): array
    {
        return $this->client->request('POST', $path, [], $body, $this->userHeaders($lang))['data'];
    }

    private function requireCredentials(): void
    {
        if (!$this->username || !$this->password || !$this->exchangeName) {
            throw new \RuntimeException(
                'Exchange credentials not configured. Contact contacto@ecoregistry.io to register as an exchange.'
            );
        }
    }

    private function requireToken(): void
    {
        if (!$this->adminToken) {
            throw new \RuntimeException('Call auth() first to obtain an exchange admin token.');
        }
    }
}
```

- [ ] **Step 2: Write MarketplaceService**

```php
<?php
// src/Services/MarketplaceService.php
declare(strict_types=1);

namespace Ecoregistry\Services;

use Ecoregistry\Http\ApiClient;

/**
 * Marketplace API — credit retirement and project listing for marketplace integrations.
 *
 * Requires marketplace onboarding with EcoRegistry.
 *
 * @see https://ecoregistry.gitbook.io/ecoregistry-documentation/getting-started/marketplace/using-the-endpoints
 */
final class MarketplaceService
{
    private ?string $adminToken = null;

    public function __construct(
        private ApiClient $client,
        private ?string $marketplaceName = null,
        private ?string $password = null,
    ) {
    }

    /** Authenticate and get admin token (valid ~5 minutes). */
    public function auth(): self
    {
        if (!$this->marketplaceName || !$this->password) {
            throw new \RuntimeException('Marketplace credentials not configured.');
        }

        $response = $this->client->request('POST', '/marketplace/v1/authAdmin', [], [
            'marketplace_name' => $this->marketplaceName,
            'password' => $this->password,
        ], ['platform: ecoregistry']);

        if ($response['status'] !== 200 || !isset($response['data']['token'])) {
            throw new \RuntimeException('Marketplace auth failed: ' . json_encode($response['data']));
        }

        $this->adminToken = $response['data']['token'];
        return $this;
    }

    /** Retire carbon credits. Returns PDF URL and transaction ID. */
    public function retire(array $retirementData, string $lang = 'en'): array
    {
        return $this->post('/marketplace/v1/retirement', $retirementData, $lang);
    }

    /** Get retirement certificate PDF URL for a transaction. */
    public function certificationPdf(string $transactionId, string $lang = 'en'): array
    {
        return $this->get("/marketplace/v1/emit-certification-pdf/{$transactionId}", $lang);
    }

    /** Get active projects and balances in a marketplace. */
    public function activeProjects(int $marketplaceId, string $lang = 'en'): array
    {
        return $this->get("/marketplace/{$marketplaceId}/projectActives", $lang);
    }

    /** List countries in the registry. */
    public function countries(string $lang = 'en'): array
    {
        return $this->get('/marketplace/v1/get-countries', $lang);
    }

    /** List document types in the registry. */
    public function documentTypes(string $lang = 'en'): array
    {
        return $this->get('/marketplace/v1/type-documents', $lang);
    }

    /** Get carbon offset usage reasons. */
    public function reasonsForUse(string $lang = 'en'): array
    {
        return $this->get('/marketplace/v1/reason-using', $lang);
    }

    /** Check which offset usage reasons apply to a serial. */
    public function serialEligibility(string $serial, string $lang = 'en'): array
    {
        return $this->post('/marketplace/v1/serial-eligible', ['serial' => $serial], $lang);
    }

    private function headers(string $lang): array
    {
        if (!$this->adminToken) {
            throw new \RuntimeException('Call auth() first.');
        }

        return [
            'platform: ecoregistry',
            'x-api-key-admin: ' . $this->adminToken,
            'lng: ' . $lang,
        ];
    }

    private function get(string $path, string $lang): array
    {
        return $this->client->request('GET', $path, [], null, $this->headers($lang))['data'];
    }

    private function post(string $path, array $body, string $lang): array
    {
        return $this->client->request('POST', $path, [], $body, $this->headers($lang))['data'];
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add src/Services/ExchangeService.php src/Services/MarketplaceService.php
git commit -m "feat: add ExchangeService and MarketplaceService with documented methods"
```

---

### Task 7: EcoRegistry Main Client

**Files:**
- Create: `src/EcoRegistry.php` (new file replacing old `EcoregistryApi.php`)
- Test: `tests/Unit/EcoRegistryTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Unit/EcoRegistryTest.php
declare(strict_types=1);

namespace Ecoregistry\Tests\Unit;

use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;
use Ecoregistry\Services\AccountService;
use Ecoregistry\Services\ExchangeService;
use Ecoregistry\Services\FrontService;
use Ecoregistry\Services\MarketplaceService;
use Ecoregistry\Services\PlatformService;
use PHPUnit\Framework\TestCase;

final class EcoRegistryTest extends TestCase
{
    private EcoRegistry $eco;

    protected function setUp(): void
    {
        $config = Config::fromArray([
            'email' => 'test@example.com',
            'api_key' => 'test-key',
            'platform_token' => 'platform-token',
        ]);

        $this->eco = new EcoRegistry($config);
    }

    public function test_account_returns_account_service(): void
    {
        $this->assertInstanceOf(AccountService::class, $this->eco->account());
    }

    public function test_platform_returns_platform_service(): void
    {
        $this->assertInstanceOf(PlatformService::class, $this->eco->platform());
    }

    public function test_front_returns_front_service(): void
    {
        $this->assertInstanceOf(FrontService::class, $this->eco->front());
    }

    public function test_exchange_returns_exchange_service(): void
    {
        $this->assertInstanceOf(ExchangeService::class, $this->eco->exchange());
    }

    public function test_marketplace_returns_marketplace_service(): void
    {
        $this->assertInstanceOf(MarketplaceService::class, $this->eco->marketplace());
    }

    public function test_services_are_singletons(): void
    {
        $this->assertSame($this->eco->account(), $this->eco->account());
        $this->assertSame($this->eco->platform(), $this->eco->platform());
        $this->assertSame($this->eco->front(), $this->eco->front());
    }

    public function test_platform_throws_without_token(): void
    {
        $config = Config::fromArray([
            'email' => 'test@example.com',
            'api_key' => 'test-key',
        ]);
        $eco = new EcoRegistry($config);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('platform_token');
        $eco->platform();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Unit/EcoRegistryTest.php -v`
Expected: FAIL — class not found

- [ ] **Step 3: Write the implementation**

```php
<?php
// src/EcoRegistry.php
declare(strict_types=1);

namespace Ecoregistry;

use Ecoregistry\Auth\TokenManager;
use Ecoregistry\Http\ApiClient;
use Ecoregistry\Services\AccountService;
use Ecoregistry\Services\ExchangeService;
use Ecoregistry\Services\FrontService;
use Ecoregistry\Services\MarketplaceService;
use Ecoregistry\Services\PlatformService;

/**
 * EcoRegistry API client — fluent interface for all EcoRegistry APIs.
 *
 * Usage:
 *   $eco = new EcoRegistry(Config::fromArray([...]))
 *   $eco->platform()->projects();
 *   $eco->front()->project('CDB-1');
 *   $eco->account()->positions();
 */
final class EcoRegistry
{
    private ApiClient $client;
    private ApiClient $frontClient;
    private TokenManager $tokens;
    private Config $config;

    private ?AccountService $account = null;
    private ?PlatformService $platform = null;
    private ?FrontService $front = null;
    private ?ExchangeService $exchange = null;
    private ?MarketplaceService $marketplace = null;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->client = new ApiClient($config->baseUrl);
        $this->frontClient = new ApiClient($config->frontUrl);
        $this->tokens = new TokenManager($this->client, $config->email, $config->apiKey);
    }

    /** Account Information API — balances and positions. */
    public function account(): AccountService
    {
        return $this->account ??= new AccountService($this->client, $this->tokens);
    }

    /**
     * Platform Information API — public project data, sectors, industries, retirements.
     *
     * @throws \RuntimeException if platform_token is not configured
     */
    public function platform(): PlatformService
    {
        if ($this->config->platformToken === null) {
            throw new \RuntimeException(
                'Platform API requires "platform_token" in config. '
                . 'Obtain one via the platform registration endpoint.'
            );
        }

        return $this->platform ??= new PlatformService($this->client, $this->config->platformToken);
    }

    /** Frontend API — richer project detail with media and DMRV. */
    public function front(): FrontService
    {
        return $this->front ??= new FrontService($this->frontClient, $this->tokens);
    }

    /** Exchange API — credit transfers, retirements, locking (requires exchange registration). */
    public function exchange(): ExchangeService
    {
        return $this->exchange ??= new ExchangeService(
            $this->client,
            $this->config->exchangeUsername,
            $this->config->exchangePassword,
            $this->config->exchangeName,
        );
    }

    /** Marketplace API — credit retirement for marketplace integrations. */
    public function marketplace(): MarketplaceService
    {
        return $this->marketplace ??= new MarketplaceService(
            $this->client,
            $this->config->marketplaceName,
            $this->config->marketplacePassword,
        );
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/phpunit tests/Unit/EcoRegistryTest.php -v`
Expected: OK (7 tests)

- [ ] **Step 5: Commit**

```bash
git add src/EcoRegistry.php tests/Unit/EcoRegistryTest.php
git commit -m "feat: add EcoRegistry main client with fluent service access"
```

---

### Task 8: Laravel Integration

**Files:**
- Create: `src/Laravel/EcoRegistryServiceProvider.php`
- Create: `src/Laravel/EcoRegistryFacade.php`
- Create: `config/ecoregistry.php`
- Modify: `composer.json` — add Laravel auto-discovery

- [ ] **Step 1: Write the Laravel config file**

```php
<?php
// config/ecoregistry.php
return [
    'base_url' => env('ECOREGISTRY_BASE_URL', 'https://api-external.ecoregistry.io/api'),
    'front_url' => env('ECOREGISTRY_FRONT_URL', 'https://api-front.ecoregistry.io'),

    'email' => env('ECOREGISTRY_EMAIL'),
    'api_key' => env('ECOREGISTRY_API_KEY'),
    'platform_token' => env('ECOREGISTRY_PLATFORM_TOKEN'),

    // Exchange credentials (provided by EcoRegistry after exchange registration)
    'exchange_username' => env('ECOREGISTRY_EXCHANGE_USERNAME'),
    'exchange_password' => env('ECOREGISTRY_EXCHANGE_PASSWORD'),
    'exchange_name' => env('ECOREGISTRY_EXCHANGE_NAME'),

    // Marketplace credentials (provided by EcoRegistry after onboarding)
    'marketplace_name' => env('ECOREGISTRY_MARKETPLACE_NAME'),
    'marketplace_password' => env('ECOREGISTRY_MARKETPLACE_PASSWORD'),
];
```

- [ ] **Step 2: Write the service provider**

```php
<?php
// src/Laravel/EcoRegistryServiceProvider.php
declare(strict_types=1);

namespace Ecoregistry\Laravel;

use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;
use Illuminate\Support\ServiceProvider;

class EcoRegistryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/ecoregistry.php', 'ecoregistry');

        $this->app->singleton(EcoRegistry::class, function ($app) {
            return new EcoRegistry(Config::fromArray($app['config']['ecoregistry']));
        });

        $this->app->alias(EcoRegistry::class, 'ecoregistry');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/ecoregistry.php' => config_path('ecoregistry.php'),
        ], 'ecoregistry-config');
    }
}
```

- [ ] **Step 3: Write the facade**

```php
<?php
// src/Laravel/EcoRegistryFacade.php
declare(strict_types=1);

namespace Ecoregistry\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Ecoregistry\Services\AccountService account()
 * @method static \Ecoregistry\Services\PlatformService platform()
 * @method static \Ecoregistry\Services\FrontService front()
 * @method static \Ecoregistry\Services\ExchangeService exchange()
 * @method static \Ecoregistry\Services\MarketplaceService marketplace()
 *
 * @see \Ecoregistry\EcoRegistry
 */
class EcoRegistryFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ecoregistry';
    }
}
```

- [ ] **Step 4: Update composer.json for Laravel auto-discovery and new namespace**

Replace the full `composer.json`:

```json
{
    "name": "dovu/ecoregistry-php",
    "description": "Fluent PHP client for the EcoRegistry carbon credit registry API",
    "type": "library",
    "license": "MIT",
    "autoload": {
        "psr-4": {
            "Ecoregistry\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Ecoregistry\\Tests\\": "tests/"
        }
    },
    "require": {
        "php": ">=8.1",
        "ext-curl": "*",
        "ext-json": "*"
    },
    "require-dev": {
        "phpunit/phpunit": "^11.0"
    },
    "extra": {
        "laravel": {
            "providers": [
                "Ecoregistry\\Laravel\\EcoRegistryServiceProvider"
            ],
            "aliases": {
                "EcoRegistry": "Ecoregistry\\Laravel\\EcoRegistryFacade"
            }
        }
    }
}
```

- [ ] **Step 5: Run `composer dump-autoload`**

Run: `composer dump-autoload`

- [ ] **Step 6: Run all unit tests**

Run: `vendor/bin/phpunit tests/Unit/ -v`
Expected: All pass

- [ ] **Step 7: Commit**

```bash
git add src/Laravel/ config/ecoregistry.php composer.json
git commit -m "feat: add Laravel service provider, facade, and config"
```

---

### Task 9: Integration Tests

**Files:**
- Create: `tests/Integration/AccountServiceTest.php`
- Create: `tests/Integration/PlatformServiceTest.php`
- Create: `tests/Integration/FrontServiceTest.php`
- Modify: `phpunit.xml` — add unit + integration suites

- [ ] **Step 1: Update phpunit.xml**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

- [ ] **Step 2: Write integration tests**

```php
<?php
// tests/Integration/AccountServiceTest.php
declare(strict_types=1);

namespace Ecoregistry\Tests\Integration;

use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;
use PHPUnit\Framework\TestCase;

final class AccountServiceTest extends TestCase
{
    private static EcoRegistry $eco;

    public static function setUpBeforeClass(): void
    {
        $dotenv = parse_ini_file(__DIR__ . '/../../.env') ?: [];
        self::$eco = new EcoRegistry(Config::fromArray([
            'base_url' => $dotenv['ECOREGISTRY_BASE_URL'],
            'email' => $dotenv['AUTH_EMAIL'],
            'api_key' => $dotenv['TOKEN_API_EXCHANGES'],
            'platform_token' => $dotenv['PLATFORM_TOKEN'] ?? null,
        ]));
    }

    public function test_positions(): void
    {
        $result = self::$eco->account()->positions();
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals(1, $result['status']);
    }
}
```

```php
<?php
// tests/Integration/PlatformServiceTest.php
declare(strict_types=1);

namespace Ecoregistry\Tests\Integration;

use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;
use PHPUnit\Framework\TestCase;

final class PlatformServiceTest extends TestCase
{
    private static EcoRegistry $eco;

    public static function setUpBeforeClass(): void
    {
        $dotenv = parse_ini_file(__DIR__ . '/../../.env') ?: [];
        self::$eco = new EcoRegistry(Config::fromArray([
            'base_url' => $dotenv['ECOREGISTRY_BASE_URL'],
            'email' => $dotenv['AUTH_EMAIL'],
            'api_key' => $dotenv['TOKEN_API_EXCHANGES'],
            'platform_token' => $dotenv['PLATFORM_TOKEN'],
        ]));
    }

    public function test_projects_returns_list(): void
    {
        $result = self::$eco->platform()->projects();
        $this->assertArrayHasKey('project', $result);
        $this->assertNotEmpty($result['project']);
    }

    public function test_project_savimbo(): void
    {
        $result = self::$eco->platform()->project(224);
        $this->assertEquals('Savimbo Biodiversity Putumayo', $result['project']['name']);
        $this->assertEquals('Savimbo Inc.', $result['project']['owner']);
    }

    public function test_sectors(): void
    {
        $result = self::$eco->platform()->sectors();
        $this->assertArrayHasKey('sectors', $result);
        $this->assertNotEmpty($result['sectors']);
    }

    public function test_industries(): void
    {
        $result = self::$eco->platform()->industries();
        $this->assertNotEmpty($result);
    }

    public function test_withdrawals(): void
    {
        $result = self::$eco->platform()->withdrawals();
        $this->assertArrayHasKey('withdrawals', $result);
        $this->assertNotEmpty($result['withdrawals']);
    }
}
```

```php
<?php
// tests/Integration/FrontServiceTest.php
declare(strict_types=1);

namespace Ecoregistry\Tests\Integration;

use Ecoregistry\Config;
use Ecoregistry\EcoRegistry;
use PHPUnit\Framework\TestCase;

final class FrontServiceTest extends TestCase
{
    private static EcoRegistry $eco;

    public static function setUpBeforeClass(): void
    {
        $dotenv = parse_ini_file(__DIR__ . '/../../.env') ?: [];
        self::$eco = new EcoRegistry(Config::fromArray([
            'base_url' => $dotenv['ECOREGISTRY_BASE_URL'],
            'email' => $dotenv['AUTH_EMAIL'],
            'api_key' => $dotenv['TOKEN_API_EXCHANGES'],
        ]));
    }

    public function test_projects_returns_many(): void
    {
        $result = self::$eco->front()->projects();
        $this->assertArrayHasKey('projects', $result);
        $this->assertGreaterThan(100, count($result['projects']));
    }

    public function test_project_by_id(): void
    {
        $result = self::$eco->front()->project(224);
        $this->assertEquals('Savimbo Biodiversity Putumayo', $result['project']['name']);
    }

    public function test_project_by_code(): void
    {
        $result = self::$eco->front()->project('CDB-1');
        $this->assertEquals('Savimbo Biodiversity Putumayo', $result['project']['name']);
    }
}
```

- [ ] **Step 3: Run unit tests**

Run: `vendor/bin/phpunit tests/Unit/ -v`
Expected: All pass

- [ ] **Step 4: Run integration tests**

Run: `vendor/bin/phpunit tests/Integration/ -v`
Expected: All pass (hits live API)

- [ ] **Step 5: Commit**

```bash
git add tests/Integration/ phpunit.xml
git commit -m "feat: add integration tests for Account, Platform, and Front services"
```

---

### Task 10: Cleanup and README

**Files:**
- Delete: `src/EcoregistryApi.php` (replaced by `EcoRegistry.php`)
- Delete: `src/Endpoints/Endpoint.php` (no longer needed)
- Delete: `src/Endpoints/EndpointRegistry.php` (no longer needed)
- Delete: `endpoints/` directory (paths now in service classes)
- Delete: `tests/ApiSmokeTest.php` (replaced by integration tests)
- Delete: `tests/PlatformProbeTest.php` (replaced by integration tests)
- Delete: `examples/` directory (replaced by README examples)
- Modify: `README.md`

- [ ] **Step 1: Remove old files**

```bash
rm -rf src/EcoregistryApi.php src/Endpoints/ endpoints/ examples/
rm tests/ApiSmokeTest.php tests/PlatformProbeTest.php
```

- [ ] **Step 2: Run all tests to confirm nothing broke**

Run: `vendor/bin/phpunit -v`
Expected: All unit + integration tests pass

- [ ] **Step 3: Update README.md**

Replace `README.md` with updated documentation showing the fluent interface, Laravel setup, standalone usage, all available methods per service, and configuration.

Key sections:
- Installation (`composer require dovu/ecoregistry-php`)
- Laravel Setup (auto-discovery, publish config, .env vars)
- Standalone Usage (Config::fromArray + new EcoRegistry)
- API Reference: account(), platform(), front(), exchange(), marketplace() with all methods
- Configuration reference

- [ ] **Step 4: Run `composer dump-autoload` to confirm autoloading**

Run: `composer dump-autoload`

- [ ] **Step 5: Final test run**

Run: `vendor/bin/phpunit -v`
Expected: All pass

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "refactor: remove old endpoint system, update README for fluent API"
```

---

Plan complete and saved to `docs/superpowers/plans/2026-04-08-fluent-laravel-library.md`. Two execution options:

**1. Subagent-Driven (recommended)** — I dispatch a fresh subagent per task, review between tasks, fast iteration

**2. Inline Execution** — Execute tasks in this session using executing-plans, batch execution with checkpoints

Which approach?