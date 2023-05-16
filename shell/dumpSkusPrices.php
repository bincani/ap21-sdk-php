<?php
/**
# get all skus from ap21
php -f dumpSkusPrices.php > 20230510_goau_ap21_skus+prices.txt &

# get missing skus from orders
grep -i -v -f 20220804_x02_ap21_skus.txt 20220804_x02_order_skus.txt > 20220804_x02_missing_skus.txt
grep -i -v -f 20220804_x04_ap21_skus.txt 20220804_x04_order_skus.txt > 20220804_x04_missing_skus.txt
 */

require_once __DIR__ . '/../vendor/autoload.php';

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
    foreach($products as $product) {
        //echo sprintf("product: %s", print_r($product, true));
        foreach($product['children'] as $colCode => $skus) {
            foreach($skus as $sku => $productDetails) {
                $sku = strtolower($sku);
                echo sprintf("%s,%0.2f,%0.2f\r\n", $sku, $productDetails['originalPrice'], $productDetails['price']);
                break 2;
            }
        }
    }
}
catch(Exception $ex) {
    Log::error($ex->getMessage());
}
