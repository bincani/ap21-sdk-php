<?php
/**
 * php -f dumpStyles.php
 *
# get all styles from ap21

php -f dumpStyles.php > 20221109_x02_ap21_styles.txt &
php -f dumpStyles.php > 20221109_x03_ap21_styles.txt &
php -f dumpStyles.php > 20221109_x04_ap21_styles.txt &
php -f dumpStyles.php > 20221109_x05_ap21_styles.txt &
php -f dumpStyles.php > 20221109_x06_ap21_styles.txt &
php -f dumpStyles.php > 20221110_x08_ap21_styles.txt &
 */

require_once __DIR__ . './../vendor/autoload.php';

use Dotenv\Dotenv;

use PHPAP21\Ap21SDK as Ap21SDK;
use PHPAP21\Log as Log;

// load .env config
$dotenv = Dotenv::createImmutable(__DIR__ . './../');
$dotenv->safeLoad();

Log::debug("env", $_ENV);

$config = array(
    'ApiUrl'       => $_ENV['ApiUrl'],
    'ApiUser'      => $_ENV['ApiUser'],
    'ApiPassword'  => $_ENV['ApiPassword'],
    'CountryCode'  => $_ENV['CountryCode'],
    'useCache'     => false
);

// Create the ap21 client object
$ap21 = new Ap21SDK($config);

try {
    $products = $ap21->Product()->get([
        'CustomData' => "true",
        "ExtendedRefs" => "false"
    ]);
    Log::debug("products", [count($products)]);
    //echo sprintf("products: %s", print_r($products, true));
    echo sprintf("ap21_product_id,sku\r\n");
    foreach($products as $product) {
        //echo sprintf("product: %s", print_r($product, true));
        if (array_key_exists('code', $product) && $product['code']) {
            echo sprintf("%s,%s\r\n", $product['id'], $product['code']);
        }
    }
}
catch(Exception $ex) {
    Log::error($ex->getMessage());
}
