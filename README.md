# PHP Ap21 SDK

PHP Ap21 SDK is a simple SDK implementation of Ap21 API. It helps accessing the API in an object oriented way.

## Installation
Install with Composer
```shell
composer require bincani/ap21-sdk-php
```

### Requirements

Uses curl extension for handling http calls. So you need to have the curl extension installed and enabled with PHP.

>However if you prefer to use any other available package library for handling HTTP calls, you can easily do so by modifying 1 line in each of the `get()`, `post()`, `put()`, `delete()` methods in `PHPAP21\HttpRequest` class.

## Configuration

### Required Parameters

| Parameter | Description |
|-----------|-------------|
| `ApiUrl` | Base URL of the Ap21 API endpoint |
| `ApiUser` | API username for Basic Auth |
| `ApiPassword` | API password for Basic Auth |
| `CountryCode` | Country code for API requests |

### Optional Parameters

| Parameter | Description | Default |
|-----------|-------------|---------|
| `AccessToken` | Alternative to ApiUser/ApiPassword, uses `X-Ap21-Access-Token` header | - |
| `AllowedTimePerCall` | Minimum seconds between API calls | `0.5` |
| `Curl` | Array of additional curl options | `[]` |

### Basic Configuration

```php
$config = array(
    'ApiUrl'       => 'https://api.example.com/RetailAPI/',
    'ApiUser'      => 'apiuser',
    'ApiPassword'  => 'password',
    'CountryCode'  => 'AU',
);

PHPAP21\Ap21SDK::config($config);
```

### Configuration with Curl Options

```php
$config = array(
    'ApiUrl'       => 'https://api.example.com/RetailAPI/',
    'ApiUser'      => 'apiuser',
    'ApiPassword'  => 'password',
    'CountryCode'  => 'AU',
    'Curl' => array(
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true
    )
);

PHPAP21\Ap21SDK::config($config);
```

## Usage

You can use PHPAP21 in a pretty simple object oriented way.

#### Get the Ap21SDK Object

```php
$ap21 = new PHPAP21\Ap21SDK;
```

You can provide the configuration as a parameter while instantiating the object (if you didn't configure already by calling `config()` method)

```php
$ap21 = new PHPAP21\Ap21SDK($config);
```

##### Now you can do `get()`, `post()`, `put()`, `delete()` calling the resources in the object oriented way. All resources are named as same as it is named in Ap21 API reference. (See the resource map below.)
> All the requests returns an array (which can be a single resource array or an array of multiple resources) if succeeded. When no result is expected (for example a DELETE request), an empty array will be returned.

- Get all product list (GET request)

```php
$products = $ap21->Product->get();
```

- Get any specific product with ID (GET request)

```php
$productID = 23564666666;
$product = $ap21->Product($productID)->get();
```

- Get all the styles and freestock
```php
$styles = $ap21->Freestock()->get();
```

### Available Resources

| Resource | Description |
|----------|-------------|
| `Colour` | Colour definitions |
| `Freestock` | Free stock / inventory levels |
| `Info` | API information |
| `Order` | Orders |
| `Person` | Customer / person records |
| `Product` | Products |
| `ProductColourReference` | Product colour references |
| `Reference` | References |
| `ReferenceType` | Reference types |
| `Size` | Size definitions |
| `StockChanged` | Stock change events |
| `Store` | Store locations |

### Error Handling

The SDK throws the following exceptions:

| Exception | When |
|-----------|------|
| `PHPAP21\Exception\SdkException` | Invalid resource name, missing configuration, or SDK misuse |
| `PHPAP21\Exception\ApiException` | API returns an error response |
| `PHPAP21\Exception\CurlException` | HTTP request failure or unexpected HTTP status code |
| `PHPAP21\Exception\ResourceRateLimitException` | API rate limit (429) exceeded after retries |

```php
try {
    $products = $ap21->Product->get();
} catch (PHPAP21\Exception\ApiException $e) {
    // Handle API error
} catch (PHPAP21\Exception\CurlException $e) {
    // Handle HTTP error
}
```

## Reference
- [Ap21 API Reference](doc/Retail%20API%20Guide%20-%20latest.pdf)
- [seldaek/monolog](https://github.com/seldaek/monolog)
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)
