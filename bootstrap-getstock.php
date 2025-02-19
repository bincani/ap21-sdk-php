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

    $cnt = 0;

    $productId = 39015;
    $storeId = 5418;

    $totalRecordCnt = 0;
    $totalSkuCnt = 0;
    $totalStock = 0;
    $styles = [];
    $storeData = [];

    if ($storeId) {
        $freestock = $ap21->Freestock->AllStyles($storeId)->get();
    }
    else {
        $freestock = $ap21->Freestock->AllStyles->get();
    }

    foreach($freestock as $styleId => $style) {
        echo sprintf("%s - %s\n", $styleId, print_r($style, true));
        if ($productId && $productId != $styleId) {
            continue;
        }
        echo sprintf("%s - %s\n", $styleId, print_r($style, true));

        foreach($style['colours'] as $colourId => $colour) {
            //echo sprintf("%s - colour: %s\n", $colourId, print_r($colour, true));
            //echo sprintf("skus.count: %d\n", count($colour['skus']));
            foreach($colour['skus'] as $skuId => $sku) {
                //echo sprintf("%s - sku: %s\n", $skuId, print_r($sku, true));
                //echo sprintf("stores.count: %d\n", count($sku['stores']));
                foreach($sku['stores'] as $storeId => $store) {
                    //echo sprintf("%s - store: %s\n", $storeId, print_r($store, true));
                    $totalRecordCnt++;
                    // check store name
                    if (!array_key_exists($store['store'], $storeData)) {
                        $storeData[$store['store']] = [
                            "totalStyles"   => 0,
                            "totalSkuCnt"   => 0,
                            "totalStock"    => 0,
                            "styles"        => [],
                            "skus"          => []
                        ];
                    }
                    // add stock styles and skus
                    $storeData[$store['store']]["totalStock"] += $store['freestock'];
                    if (!in_array($style['id'], $storeData[$store['store']]['styles'])) {
                        $storeData[$store['store']]['styles'][] = $style['id'];
                    }
                    if (!in_array($skuId, $storeData[$store['store']]['skus'])) {
                        $storeData[$store['store']]['skus'][] = $skuId;
                    }
                    /*
                    if (preg_match("/X05|X06/i", $store['store'])) {
                        //echo sprintf("%s - %s\n", $styleId, print_r($style, true));
                        $totalStock += $store['freestock'];
                        $totalSkuCnt++;
                        if (!in_array($style['id'], $styles)) {
                            $styles[] = $style['id'];
                        }
                    }
                    */
                }
            }
        }
        //echo sprintf("%s - %s\n", $styleId, $style['freestock']);
    }
    foreach($storeData as $store => $data) {
        echo sprintf("# %s\n", $store);
        echo sprintf(" styles : %d\n", count($data['styles']));
        echo sprintf(" skus   : %d\n", count($data['skus']));
        echo sprintf(" stock  : %d\n", $data['totalStock']);
    }
    /*
    echo sprintf("totalCnt: %d\n", $totalRecordCnt);
    echo sprintf("totalStyles: %d\n", count($styles));
    echo sprintf("totalSkuCnt: %d\n", $totalSkuCnt);
    echo sprintf("totalStock: %d\n", $totalStock );
    */

    /**
     * get all freestock - with paging
     */
    /*
    $freestock = [];
    $cnt = 0;
    $limit = 10;
    $startPage = 1;
    $pageRows = 10;
    $maxPages = 10;
    do {
        $cnt++;
        $urlParams = [
            //'CustomData' => "true"
            "ExtendedRefs" => "true",
            "startRow"  => $startPage,
            "pageRows"  => $pageRows,
            "limit"     => $limit
        ];
        $freestockApi = $ap21->Freestock->AllStyles();
        $freestock = array_merge($freestock, $freestockApi->get($urlParams));
        echo sprintf("=========> pages %d of %d (%d|%d)\n", $cnt, $maxPages, count($freestock), $freestockApi->getTotalProducts());
        $startPage += $pageRows;
    } while($cnt < $maxPages);
    $cnt = 0;
    foreach($freestock as $styleId => $style) {
        echo sprintf("%04d. %s - %s\n", ++$cnt, $styleId, $style['freestock']);
    }
    */

    /**
     * get all products - with paging
     */
    /*
    $products = [];
    $cnt = 0;
    $limit = 10;
    $startPage = 1;
    $pageRows = 10;
    $maxPages = 1;
    do {
        $cnt++;
        $urlParams = [
            //'CustomData' => "true"
            "ExtendedRefs" => "true",
            "startRow"  => $startPage,
            "pageRows"  => $pageRows,
            "limit"     => $limit
        ];
        $productApi = $ap21->Product();
        $products = array_merge($products, $productApi->get($urlParams));
        echo sprintf("=========> pages %d of %d (%d|%d)\n", $cnt, $maxPages, count($products), $productApi->getTotalProducts());
        $startPage += $pageRows;
    } while($cnt < $maxPages);

    foreach($products as $product) {
        echo sprintf("%s,%s\n", $product['id'], $product['code']);
    }
    */

    //echo sprintf("products: %s", print_r($products, true));
    //echo sprintf("totalPages: %d\n", $totalPages);

    /*
    //echo sprintf("products: %s", print_r($products, true));
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