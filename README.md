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

You can pass additional curl configuration to `Ap21SDK`

```php
$config = array(
    'ApiUrl'       => 'https://api21.end.pount/',
    'ApiUser'      => 'apiuser',
    'ApiPassword'  => 'password',
    'CountryCode'  => 'code',
    'Curl' => array(
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true
    )
);

PHPAP21\Ap21SDK::config($config);
```
## Usage

You can use PHPAP21 in a pretty simple object oriented way.

#### Configure Ap21SDK

```php
$config = array(
    'ApiUrl'       => 'https://api21.end.pount/',
    'ApiUser'      => 'apiuser',
    'ApiPassword'  => 'password',
    'CountryCode'  => 'code',
);

PHPAP21\Ap21SDK::config($config);
```

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

## Reference
- [Ap21 API Reference](doc/Retail API Guide - latest.pdf)
- [seldaek/monolog](https://github.com/seldaek/monolog)
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)
