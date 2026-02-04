# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP SDK for the AP21 retail/inventory management API, implementing the Retail API Guide - 2025.1. Provides an object-oriented interface to AP21's REST API with both JSON and XML resource support. Namespace: `PHPAP21`.

## Commands

```bash
# Install dependencies
composer install

# Run all tests
./vendor/bin/phpunit

# Run a single test file
./vendor/bin/phpunit tests/ProductTest.php

# Run a specific test method
./vendor/bin/phpunit --filter testMethodName

# Regenerate autoloader after adding classes
composer dump-autoload
```

## Architecture

### Resource Hierarchy

```
HTTPResourceInterface (interface)
    └── HTTPResource (abstract base, JSON resources)
            ├── Colour, Size, Store, Order, Reference, ReferenceType, etc.
            └── HTTPXMLResource (extends HTTPResource for XML resources)
                    ├── Product (custom pagination with startRow/pageRows)
                    ├── Person
                    └── Freestock
```

All resource classes live in `lib/`. Child resources (e.g., `Person/Orders`, `Product/FuturePrice`, `Freestock/AllStyles`) are in subdirectories matching their parent.

### Request Flow

Resources are accessed dynamically through `Ap21SDK` via `__call()` and `__get()` magic methods:

```php
$ap21 = new PHPAP21\Ap21SDK($config);
$ap21->Product($id)->get();  // __call() instantiates Product with ID
$ap21->Colour->get();        // __get() instantiates Colour
```

The HTTP call chain: `Resource` -> `HttpRequest[Json|Xml]` -> `CurlRequest` -> cURL

### JSON vs XML Resources

- **HTTPResource** (JSON): Standard REST with `application/json`, pagination via Link headers
- **HTTPXMLResource** (XML): Uses `application/xml`, pagination via `startRow`/`pageRows` parameters, response parsing via SimpleXML

### Key Design Patterns

- **Static configuration**: `Ap21SDK::$config` holds global SDK state, rate limit tracking, and API version
- **Rate limiting**: Enforced at 0.5s between API calls (configurable via `AllowedTimePerCall`), checked in `Ap21SDK::checkApiCallLimit()`
- **Authentication**: Basic Auth (`ApiUser`/`ApiPassword`) or token-based (`AccessToken` via `X-Ap21-Access-Token` header)
- **Logging**: `Log` class is a Monolog singleton with rotating file handlers; output goes to `log/` directory

### Exception Classes (in `lib/Exception/`)

- `SdkException` - SDK misuse (bad resource name, missing config)
- `ApiException` - API error responses
- `CurlException` - HTTP/cURL failures
- `ResourceRateLimitException` - 429 rate limit after retry exhaustion

### Autoloading

PSR-4: `PHPAP21\` maps to `lib/` (source) and `tests/` (dev). Resource class names match their filenames in `lib/`.

## Testing

PHPUnit 10 with bootstrap via `vendor/autoload.php`. Tests are in `tests/`. Configuration in `phpunit.xml`.

## Environment Configuration

Copy `.env.example` to `.env` with keys: `ApiUrl`, `ApiUser`, `ApiPassword`, `CountryCode`. Bootstrap files (`bootstrap*.php`) demonstrate SDK usage patterns.
