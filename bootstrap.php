<?php
/**
 * composer dump-autoload
 *
 * see https://getcomposer.org/doc/01-basic-usage.md#autoloading
 */
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

use PHPAP21\Ap21SDK as Ap21SDK;
use PHPAP21\Log as Log;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
Log::debug("env", $_ENV);

$config = array(
    'ApiUrl'       => $_ENV['ApiUrl'],
    'ApiUser'      => $_ENV['ApiUser'],
    'ApiPassword'  => $_ENV['ApiPassword'],
    'CountryCode'  => $_ENV['CountryCode']
);

// Create the ap21 client object
$ap21 = new Ap21SDK($config);

//$products = $ap21->Products->get();
//Log::info("products", $products);

try {
    $info = $ap21->Info->get();
    Log::info("info", [$info]);
}
catch(Exception $ex) {
    Log::error($ex->getMessage());
}
