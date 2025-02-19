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
    'CountryCode'  => $_ENV['CountryCode']
    //'useCache'     => true
);

// Create the ap21 client object
$ap21 = new Ap21SDK($config);

try {
    /**
     * API info
     */
    $info = $ap21->Info->get();
    Log::info("info", [$info]);

    /**
     * get all products - with paging
     */
    $products = [];
    $pageCnt    = 0;
    $startPage  = 1;     // always start on page one
    $pageRows   = 5000;  // pageRows = number of products
    $limit      = 5000;  // limit products per request (otherwise it will get all products each iteration)
    $totalPages;
    do {
        $pageCnt++;
        $urlParams = [
            //'CustomData' => "true"
            //"ExtendedRefs" => "true",
            "startRow"  => $startPage,
            "pageRows"  => $pageRows,
            "limit"     => $limit
        ];
        $productApi = $ap21->Product();
        $newProducts = $productApi->get($urlParams);
        //echo sprintf("newProducts.count: %d\n", count($newProducts));
        $products = array_merge($products, $newProducts);
        $totalPages = $productApi->getTotalPages();
        echo sprintf("=========> pages %d of %d (%d|%d)\n", $pageCnt, $totalPages, count($products), $productApi->getTotalProducts());
        $startPage += $pageRows;
        /*
        if ($limit != 0 && count($products) >= $limit) {
            echo sprintf("limit %d reached!\n", $limit);
            break;
        }
        */
    }
    while($totalPages != 0 && $pageCnt < $totalPages);

    echo sprintf("products.count: %d\n", count($products));
    foreach($products as $product) {
        echo sprintf("%s,%s,%s\n", $product['id'], $product['code'], $product['name']);
    }

    //echo sprintf("products: %s", print_r($products, true));
    //echo sprintf("totalPages: %d\n", $totalPages);
    /*
    $brands = [];
    foreach($products as $product) {
        echo sprintf("%s,%s\n", $product['id'], $product['code']);
        echo sprintf("product: %s", print_r($product, true));

        $brand = substr($product['code'], 0, 1);
        if (!array_key_exists($brand, $brands)) {
            $brands[$brand] = [
                'enabled' => 0,
                'disabled' => 0
            ];
        }
        if (array_key_exists('customData', $product) && !empty($product['customData'])) {
            echo sprintf("product['customData']: %s", print_r($product['customData'], true));
            if (array_key_exists('Web Data', $product['customData'])) {
                //echo sprintf("product['customData']['Web Data']: %s\n", print_r($keys, true));
                $keys = array_keys($product['customData']['Web Data']);
                if (array_key_exists('Magento Attribute Set Name', $product['customData']['Web Data'])) {
                    $attSetName = $product['customData']['Web Data']['Magento Attribute Set Name'];
                    //echo sprintf("product['customData']: %s\n", $attSetName);
                }
                if (array_key_exists('Images', $product['customData']['Web Data'])) {
                    $images = $product['customData']['Web Data']['Images'];
                    //echo sprintf("product['customData']['Web Data']['Images']: %s\n", $images);
                    $json = json_decode($images);
                    //echo sprintf("product['customData']['Web Data']['Images']: %s\n", print_r($json, true));
                }
            }
        }
        if (array_key_exists('references', $product) && !empty($product['references'])) {
            //echo sprintf("product['references']: %s", print_r($product['references'], true));
            if ($product['references'][1521]['key']) {
                $brands[$brand]['enabled']++;
            }
            else {
                $brands[$brand]['disabled']++;
            }
        }
        if (array_key_exists('children', $product) && !empty($product['children'])) {
            foreach($product['children'] as $child) {
                echo sprintf("child: %s", print_r($child, true));
            }
        }
    }
    Log::info("products", [count($products)]);
    Log::info("apienabled", [$brands]);
    */
}
catch(Exception $ex) {
    Log::error($ex->getMessage());
}
