# Testing

This project uses [PHPUnit 10](https://phpunit.de/) for testing. Tests run against a live AP21 API instance — there are no mocks.

## Prerequisites

- PHP with the curl extension enabled
- Composer dependencies installed: `composer install`
- A valid AP21 API environment (see [Environment Setup](#environment-setup))

## Environment Setup

Tests load credentials from a `.env.test` file in the project root. Create it by copying the example:

```bash
cp .env.example .env.test
```

Then fill in your API credentials:

```
ApiUrl=https://your-api-endpoint/RetailAPI/
ApiUser=your_api_user
ApiPassword=your_api_password
CountryCode=AU
```

The bootstrap (`tests/bootstrap.php`) loads `.env.test` using `Dotenv\Dotenv` and makes the values available via `getenv()`.

## Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run a single test file
./vendor/bin/phpunit tests/ProductTest.php

# Run a specific test method
./vendor/bin/phpunit --filter testGetByStyle

# Run with verbose output
./vendor/bin/phpunit --testdox
```

## Configuration

PHPUnit is configured in `phpunit.xml`:

- **Test directory**: `./tests/`
- **Source directory**: `lib/` (for code coverage)
- **Bootstrap**: `tests/bootstrap.php`
- **Timezone**: UTC

## Test Architecture

### Base Classes

All tests extend one of two base classes:

#### `TestResource`

The root base class (`tests/TestResource.php`). Handles SDK setup and teardown:

- `setUpBeforeClass()` — reads API credentials from environment variables and calls `Ap21SDK::config()`
- `tearDownAfterClass()` — cleans up the static SDK instance

Extend this class for custom tests where you need full control over test methods.

#### `TestSimpleResource`

Extends `TestResource` (`tests/TestSimpleResource.php`). Provides generic CRUD test methods for resources:

- `testPost()` — creates a resource using `$postArray`
- `testGet()` — fetches the resource collection
- `testGetSelf($id)` — fetches a single resource by ID (depends on `testPost`)
- `testPut($id)` — updates a resource using `$putArray` (depends on `testPost`)
- `testDelete($id)` — deletes a resource (depends on `testPost`)
- `testPostError()` — posts invalid data using `$errorPostArray`, expects `ApiException`

Subclasses configure behavior through three properties:

```php
class ColourTest extends TestSimpleResource
{
    public $postArray = [];       // Empty = testPost is skipped
    public $errorPostArray = [];  // Empty = testPostError is skipped
    public $putArray = [];        // Empty = testPut is skipped
}
```

The resource name is auto-detected from the test class name (e.g., `ColourTest` → `Colour`).

### Test Patterns

Tests fall into three categories:

**1. Simple read-only resources** — extend `TestSimpleResource` with empty arrays to skip write operations:

```php
// ColourTest.php, SizeTest.php, StoreTest.php, ReferenceTypeTest.php
class ColourTest extends TestSimpleResource
{
    public $postArray = [];
    public $errorPostArray = [];
    public $putArray = [];
}
```

**2. Custom resource tests** — extend `TestResource` for resources that need specific setup or non-standard access patterns:

```php
// FreestockTest.php — needs a product ID from a prior API call
class FreestockTest extends TestResource
{
    protected static $productId;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $products = self::$ap21->Product->get();
        if (!empty($products)) {
            $first = reset($products);
            static::$productId = $first['id'];
        }
    }

    public function testGetByStyle()
    {
        // uses static::$productId ...
    }
}
```

**3. Error validation tests** — extend `TestResource` and verify that invalid inputs trigger `ApiException`:

```php
// PersonOrdersTest.php — tests child resource error handling
class PersonOrdersTest extends TestResource
{
    public function testPostOrderError()
    {
        $this->expectException('PHPAP21\\Exception\\ApiException');
        static::$ap21->Person(1)->Orders->post(['invalid' => 'data']);
    }
}
```

### Handling API Errors in Tests

Since tests run against a live API, some resources may not have data available. Use a try/catch to accept `ApiException` as a valid outcome:

```php
try {
    $result = static::$ap21->Freestock->AllStyles->get();
    $this->assertIsArray($result);
} catch (Exception\ApiException $e) {
    // No data available — still a passing test
    $this->assertInstanceOf(Exception\ApiException::class, $e);
}
```

## Test Coverage Map

| Test File | Resource(s) | Type |
|-----------|-------------|------|
| `ColourTest.php` | Colour | SimpleResource (GET) |
| `SizeTest.php` | Size | SimpleResource (GET) |
| `StoreTest.php` | Store | SimpleResource (GET) |
| `ReferenceTypeTest.php` | ReferenceType | SimpleResource (GET) |
| `ReferenceTest.php` | Reference | Custom (lookup by type) |
| `ProductTest.php` | Product | SimpleResource + custom PUT |
| `ProductFuturePriceTest.php` | Product/FuturePrice | Custom (child resource) |
| `ProductCustomDataTemplateTest.php` | Product/CustomDataTemplate | Custom (child resource) |
| `ProductColourReferenceTest.php` | ProductColourReference | Custom (GET) |
| `FreestockTest.php` | Freestock, Freestock/AllStyles | Custom (needs product ID) |
| `StockChangedTest.php` | StockChanged | Custom (timestamp filter) |
| `PersonTest.php` | Person | Error validation (POST) |
| `PersonOrdersTest.php` | Person/Orders | Error validation (POST) |
| `PersonOrdersReturnsTest.php` | Person/Orders/Returns | Error validation (GET) |
| `PersonShipmentsTest.php` | Person/Shipments | Error validation (GET) |
| `RetailTransactionsTest.php` | RetailTransactions | Error validation (GET) |
| `OrderTest.php` | Order | Error validation (GET) |
| `ShipmentTest.php` | Shipment | Error validation (GET) |
| `InfoTest.php` | Info | Custom (validates response keys) |

## Writing New Tests

1. Create a file in `tests/` named `{Resource}Test.php`
2. Use namespace `PHPAP21`
3. Extend `TestSimpleResource` for standard CRUD resources, or `TestResource` for custom tests
4. Access the SDK via `static::$ap21`
5. Resources are accessed using magic methods: `static::$ap21->Resource->get()` or `static::$ap21->Resource($id)->get()`
6. Child resources chain from parents: `static::$ap21->Person($id)->Orders->get()`
7. Run `composer dump-autoload` if you add a new class under `lib/`

### Example: Adding a Test for a New Resource

```php
<?php

namespace PHPAP21;

class VoucherTest extends TestResource
{
    public function testGetVoucher()
    {
        try {
            $result = static::$ap21->Voucher('VOUCHER-CODE')->get();
            $this->assertIsArray($result);
        } catch (Exception\ApiException $e) {
            $this->assertInstanceOf(Exception\ApiException::class, $e);
        }
    }
}
```

## Important Notes

- Tests hit the live API — be mindful of rate limits (0.5s between calls by default)
- POST/PUT tests that create or modify data will affect the target environment
- Some tests use invalid IDs (e.g., `999999`) intentionally to trigger and verify error handling
- Large collections (Person, Product) can be slow to fetch; tests avoid full collection GETs where possible
