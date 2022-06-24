<?php
/**
 * composer dump-autoload
 *
 * php -f bootstrap.php
 * see https://getcomposer.org/doc/01-basic-usage.md#autoloading
 */
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

use PHPAP21\Ap21SDK as Ap21SDK;
use PHPAP21\Log as Log;

// load .env config
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
Log::debug("env", $_ENV);

$config = array(
    'ApiUrl'       => $_ENV['ApiUrl'],
    'ApiUser'      => $_ENV['ApiUser'],
    'ApiPassword'  => $_ENV['ApiPassword'],
    'CountryCode'  => $_ENV['CountryCode'],
    'useCache'     => true
);

// Create the ap21 client object
$ap21 = new Ap21SDK($config);

try {
    $info = $ap21->Info->get();
    Log::info("info", [$info]);

    // products
    /*
    $id = 1344;
    $product = $ap21->Product($id)->get();
    Log::debug("product", [count($product)]);
    //echo sprintf(print_r($product, true));

    $products = $ap21->Product()->get();
    Log::debug("products", [count($products)]);
    //echo sprintf(print_r($products, true));
    */

    // persons

    $id = 1145;
    $person = $ap21->Person($id)->get();
    Log::debug("person", [count($person)]);
    echo sprintf(print_r($person, true));

    $transactions = $ap21->Person($id)->RetailTransactions->get();

    /*
    $people = $ap21->Person()->get();
    Log::debug("people", [count($people)]);
    echo sprintf(print_r($people, true));
    */

}
catch(Exception $ex) {
    Log::error($ex->getMessage());
}
